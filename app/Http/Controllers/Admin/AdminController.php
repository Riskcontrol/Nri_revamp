<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DataInsights;
use App\Models\DataInsightsCategory; // Assuming you have this
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    // ===========================
    // USER MANAGEMENT
    // ===========================

    public function users()
    {
        // Fetch users, paginated
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function destroyUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }

    // ===========================
    // INSIGHT MANAGEMENT
    // ===========================

    public function insights()
    {
        $insights = DataInsights::with('category')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.insights.index', compact('insights'));
    }

    public function editInsight($id)
    {
        $insight = DataInsights::findOrFail($id);
        $categories = DataInsightsCategory::all(); // Populates dropdown

        return view('admin.insights.edit', compact('insight', 'categories'));
    }

    public function updateInsight(Request $request, $id)
    {
        $insight = DataInsights::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'state' => 'required|string',
            'description' => 'required|string',
            'content' => 'required',
            // Add validation for image if you allow updating it
        ]);

        $insight->update([
            'title' => $request->title,
            'state' => $request->state,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'content' => $request->content,
            'lastupdatedby' => auth()->id(),
        ]);

        return redirect()->route('admin.insights.index')->with('success', 'Insight updated successfully.');
    }

    public function destroyInsight($id)
    {
        $insight = DataInsights::findOrFail($id);

        // Optional: Delete associated image
        if ($insight->featureimage) {
            Storage::delete($insight->featureimage);
        }

        $insight->delete();
        return back()->with('success', 'Insight deleted successfully.');
    }
}
