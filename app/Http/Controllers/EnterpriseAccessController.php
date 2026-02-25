<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\EnterpriseAccessConfirmation;
use App\Mail\EnterpriseAccessAdminNotification;
use App\Models\EnterpriseAccessRequest;
use Illuminate\Http\Request;

class EnterpriseAccessController extends Controller
{
    public function create(Request $request)
    {
        return view('enterprise-access', [
            'source' => $request->query('source'),
            'risk'   => $request->query('risk'),
            'year'   => $request->query('year'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_type' => ['required', 'string', 'max:255'],
            'industry_sector'   => ['nullable', 'string', 'max:255'],
            'company_size'      => ['nullable', 'string', 'max:255'],

            'primary_use_case'       => ['required', 'string', 'max:255'],
            'primary_use_case_other' => ['nullable', 'string', 'max:255'],

            'geographic_focus'   => ['required', 'array', 'min:1'],
            'geographic_focus.*' => ['string', 'max:255'],

            'focus_states'       => ['nullable', 'array'],
            'focus_states.*'     => ['string', 'max:255'],

            'focus_sectors_regions' => ['nullable', 'string', 'max:2000'],
            'focus_cities_lgas'     => ['nullable', 'string', 'max:2000'],

            'features_of_interest'   => ['required', 'array', 'min:1'],
            'features_of_interest.*' => ['string', 'max:255'],

            'contact_name'             => ['required', 'string', 'max:255'],
            'contact_email'            => ['required', 'email', 'max:255'],
            'contact_phone'            => ['required', 'string', 'max:50'],
            'preferred_contact_method' => ['required', 'string', 'max:50'],

            'source_page'         => ['nullable', 'string', 'max:255'],
            'attempted_risk_type' => ['nullable', 'string', 'max:255'],
            'attempted_year'      => ['nullable', 'string', 'max:20'],
        ]);

        $statesText = (string) $request->input('focus_states_text', '');
        if (!empty($statesText) && empty($data['focus_states'])) {
            $data['focus_states'] = collect(explode(',', $statesText))
                ->map(fn($s) => trim($s))
                ->filter()
                ->values()
                ->all();
        }

        if (($data['organization_type'] ?? '') === 'Corporate/Private Sector' && empty($data['industry_sector'])) {
            return back()
                ->withErrors(['industry_sector' => 'Industry sector is required for corporate organizations.'])
                ->withInput();
        }

        if (($data['primary_use_case'] ?? '') === 'Other' && empty($data['primary_use_case_other'])) {
            return back()
                ->withErrors(['primary_use_case_other' => 'Please specify your primary use case.'])
                ->withInput();
        }

        $record = EnterpriseAccessRequest::create($data);

        $adminEmail = config('mail.enterprise_admin', config('mail.from.address'));

        try {
            /**
             * QUEUE both emails instead of sending synchronously.
             *
             * Previously ->send() blocked the user's browser for 2–10 seconds
             * waiting for Mailgun to accept the connection. If the SMTP port was
             * blocked (as you're experiencing), it threw an exception and the
             * user saw an error even though their record was saved.
             *
             * ->queue() writes the job to the `jobs` table in ~1ms, the user
             * gets their success redirect immediately, and the cron worker sends
             * both emails in the background within the next 60 seconds.
             */
            Mail::to($data['contact_email'])->queue(new EnterpriseAccessConfirmation($data));

            if ($adminEmail) {
                Mail::to($adminEmail)->queue(new EnterpriseAccessAdminNotification($data));
            }
        } catch (\Throwable $e) {
            // Log but don't crash — the record is saved; emails will retry via queue
            \Log::error('Enterprise access email queue failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('enterprise-access.create')
            ->with('success', 'Thanks — your request has been received. We\'ll reach out shortly.');
    }
}
