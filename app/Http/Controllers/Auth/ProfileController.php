<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:100'],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $newPhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')
                ->store('profile-photos', 'public');

            if ($newPhotoPath === false) {
                return back()->withInput()->withErrors([
                    'profile_photo' => 'The picture could not be saved. Please try again.',
                ]);
            }
        }

        try {
            $oldPhotoPath = DB::transaction(function () use ($userId, $validated, $newPhotoPath) {
                $user = User::query()->lockForUpdate()->findOrFail($userId);

                $user->name = $validated['name'];
                $user->email = $validated['email'];
                $user->bio = $validated['bio'] ?? null;
                $user->location = $validated['location'] ?? null;

                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }

                $oldPhotoPath = null;

                if ($newPhotoPath !== null) {
                    $oldPhotoPath = $user->profile_photo_path;
                    $user->profile_photo_path = $newPhotoPath;
                }

                $user->save();

                return $oldPhotoPath;
            });
        } catch (Throwable $exception) {
            if ($newPhotoPath !== null) {
                $this->deletePhoto($newPhotoPath);
            }

            throw $exception;
        }

        if ($oldPhotoPath !== null) {
            $this->deletePhoto($oldPhotoPath);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Your profile was updated.');
    }

    private function deletePhoto(string $path): void
    {
        try {
            if (! Storage::disk('public')->delete($path)) {
                report(new RuntimeException('Could not remove profile photo: ' . $path));
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}