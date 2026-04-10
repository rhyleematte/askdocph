<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AdminProfileController extends Controller
{
    /**
     * Show the admin profile page.
     */
    public function show()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('admin'));
    }

    /**
     * Update the admin profile details.
     */
    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'gender' => 'nullable|in:male,female,other',
            'bday' => 'nullable|date|before:today',
        ]);

        $admin->update($validated);

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }
    /**
     * Update the admin profile photo.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $admin = Auth::guard('admin')->user();

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if it exists
            if ($admin->avatar_url && \Storage::disk('public')->exists($admin->avatar_url)) {
                \Storage::disk('public')->delete($admin->avatar_url);
            }

            // Store new photo
            $path = $request->file('profile_photo')->store('admins/avatars', 'public');
            $admin->update(['avatar_url' => $path]);
        }

        return redirect()->route('admin.profile')->with('success', 'Profile photo updated successfully.');
    }

    /**
     * Delete the admin profile photo.
     */
    public function deletePhoto()
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->avatar_url) {
            try {
                if (\Storage::disk('public')->exists($admin->avatar_url)) {
                    \Storage::disk('public')->delete($admin->avatar_url);
                }
            }
            catch (\Exception $e) {
            // Ignore missing file errors on disk
            }

            $admin->update(['avatar_url' => null]);
            return redirect()->route('admin.profile')->with('success', 'Profile photo removed successfully.');
        }

        return redirect()->route('admin.profile')->with('error', 'No profile photo found to remove.');
    }
}
