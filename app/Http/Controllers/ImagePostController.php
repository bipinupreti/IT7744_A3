<?php

namespace App\Http\Controllers;

use App\Models\ImagePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class ImagePostController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $uploadToken = $request->session()->get('image_upload_token');

        abort_unless(
            is_string($uploadToken)
                && hash_equals($uploadToken, (string) $request->bearerToken()),
            403
        );

        $validated = $request->validate([
            ...$this->postRules(),
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $path = $request->file('image')->store('image-posts', 'public');
        abort_if($path === false, 500, 'The image could not be saved.');

        $post = new ImagePost();
        $post->user_id = $request->user()->id;
        $post->title = $validated['title'];
        $post->description = $validated['description'];
        $post->filter = $validated['filter'];
        $post->image_path = $path;

        try {
            $post->save();
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'filter' => $post->filter,
            'image_url' => asset('storage/' . $post->image_path),
        ], 201);
    }

    public function update(Request $request, ImagePost $imagePost): RedirectResponse
    {
        $this->ensureOwner($request, $imagePost);

        $validated = $request->validateWithBag('imagePostEdit', $this->postRules());

        $imagePost->title = $validated['title'];
        $imagePost->description = $validated['description'];
        $imagePost->filter = $validated['filter'];
        $imagePost->save();

        return redirect()->route('dashboard')
            ->with('image_status', 'Image updated.');
    }

    public function destroy(Request $request, ImagePost $imagePost): RedirectResponse
    {
        $this->ensureOwner($request, $imagePost);

        $path = $imagePost->image_path;
        $imagePost->delete();

        try {
            if (! Storage::disk('public')->delete($path)) {
                throw new RuntimeException(
                    'Could not remove the file for image post ' . $imagePost->id
                );
            }
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('dashboard')
                ->with('image_status', 'Post deleted, but its image file could not be removed.');
        }

        return redirect()->route('dashboard')
            ->with('image_status', 'Image deleted.');
    }

    private function ensureOwner(Request $request, ImagePost $imagePost): void
    {
        abort_unless((int) $imagePost->user_id === (int) $request->user()->id, 403);
    }

    private function postRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'filter' => ['required', Rule::in(['none', 'grayscale', 'sepia'])],
        ];
    }
}