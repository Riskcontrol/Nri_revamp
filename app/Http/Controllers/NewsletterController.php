<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterConfirmation;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    /**
     * Handle the AJAX subscribe request from the footer.
     * Always returns JSON.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($request->email));

        $existing = NewsletterSubscriber::where('email', $email)->first();

        // Already confirmed — let the frontend know without leaking anything sensitive
        if ($existing && $existing->confirmed) {
            return response()->json(['status' => 'already_confirmed']);
        }

        // Create or refresh an unconfirmed record (covers re-submissions)
        $subscriber = NewsletterSubscriber::updateOrCreate(
            ['email' => $email],
            ['token' => NewsletterSubscriber::generateToken(), 'confirmed' => false]
        );

        Mail::to($subscriber->email)->send(new NewsletterConfirmation($subscriber));

        return response()->json(['status' => 'pending']);
    }

    /**
     * Confirm via the emailed token link.
     * Redirects to "/" with ?newsletter=confirmed so the footer modal picks it up.
     */
    public function confirm(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (! $subscriber) {
            abort(404, 'Invalid or expired confirmation link.');
        }

        if (! $subscriber->confirmed) {
            $subscriber->update([
                'confirmed'    => true,
                'confirmed_at' => now(),
            ]);
        }

        return redirect('/?newsletter=confirmed');
    }

    /**
     * Unsubscribe via the emailed token link.
     * Redirects to "/" with ?newsletter=unsubscribed so the footer modal picks it up.
     */
    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if ($subscriber) {
            $subscriber->delete();
        }

        return redirect('/?newsletter=unsubscribed');
    }
}
