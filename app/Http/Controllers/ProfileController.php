<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('auth.profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'current' => 'profile',
            'eyebrow' => 'Profile & preferences',
            'title' => 'Profile & contact details',
            'intro' => 'Use the same email you checkout with so every booking lands in this account.',
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile-photos', 'public');
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $validated['profile_picture'] = $path;
        }

        $first = $validated['first_name'] ?? $user->first_name;
        $last = $validated['last_name'] ?? $user->last_name;
        $validated['name'] = trim(sprintf('%s %s', $first, $last));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Handle asynchronous profile photo uploads.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'profile_picture' => ['required', File::image()->max(4096)],
        ]);

        $user = $request->user();

        if (!$request->hasFile('profile_picture')) {
            return response()->json([
                'message' => 'No photo received.',
            ], 422);
        }

        $path = $request->file('profile_picture')->store('profile-photos', 'public');

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->profile_picture = $path;
        $user->save();

        return response()->json([
            'message' => 'Profile photo updated.',
            'path' => $path,
            'url' => $this->profilePhotoUrl($path),
        ]);
    }

    protected function profilePhotoUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        $host = config('app.asset_url') ?: config('services.asset_host') ?: 'https://atease.weofferwellness.co.uk';
        $host = rtrim($host, '/');
        $storagePath = '/storage/'.ltrim($path, '/');
        return $host.$storagePath;
    }
}
