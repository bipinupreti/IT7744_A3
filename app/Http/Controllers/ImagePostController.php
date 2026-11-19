<?php

namespace App\Http\Controllers;

use App\Models\ImagePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'filter' => ['required', Rule::in(['none', 'grayscale', 'sepia'])],
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
}