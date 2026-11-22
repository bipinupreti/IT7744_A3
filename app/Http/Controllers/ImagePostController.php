<?php

namespace App\Http\Controllers;

use App\Models\ImagePost;
use Illuminate\Database\UniqueConstraintViolationException;
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
            'upload_request_id' => ['required', 'uuid'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $uploadRequestId = $validated['upload_request_id'];
        $userId = $request->user()->id;

        $matchingUpload = ImagePost::query()
            ->where('user_id', $userId)
            ->where('upload_request_id', $uploadRequestId);

        $existingPost = $matchingUpload->first();

        if ($existingPost !== null) {
            return $this->uploadResponse($existingPost, 200);
        }

        $path = $request->file('image')->store('image-posts', 'public');
        abort_if($path === false, 500, 'The image could not be saved.');

        $post = new ImagePost();
        $post->user_id = $userId;
        $post->upload_request_id = $uploadRequestId;
        $post->title = $validated['title'];
        $post->description = $validated['description'];
        $post->filter = $validated['filter'];
        $post->image_path = $path;

        try {
            $post->save();
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            if ($exception instanceof UniqueConstraintViolationException) {
                $existingPost = $matchingUpload->first();

                if ($existingPost !== null) {
                    return $this->uploadResponse($existingPost, 200);
                }
            }

            throw $exception;
        }

        return $this->uploadResponse($post, 201);
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

    public function destroy(
        Request $request,
        ImagePost $imagePost
    ): JsonResponse|RedirectResponse {
        $this->ensureOwner($request, $imagePost);

        $path = $imagePost->image_path;
        $imagePost->delete();

        $message = 'Image deleted.';

        try {
            if (! Storage::disk('public')->delete($path)) {
                throw new RuntimeException(
                    'Could not remove the file for image post ' . $imagePost->id
                );
            }
        } catch (Throwable $exception) {
            report($exception);
            $message = 'Post deleted, but its image file could not be removed.';
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('dashboard')
            ->with('image_status', $message);
    }

    private function uploadResponse(ImagePost $post, int $status): JsonResponse
    {
        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'filter' => $post->filter,
            'image_url' => asset('storage/' . $post->image_path),
            'delete_url' => route('image-posts.destroy', $post),
        ], $status);
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