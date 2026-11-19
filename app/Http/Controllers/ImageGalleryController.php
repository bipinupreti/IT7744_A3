<?php

namespace App\Http\Controllers;

use App\Models\ImagePost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageGalleryController extends Controller
{
    public function latest(): JsonResponse
    {
        return $this->galleryResponse(ImagePost::query());
    }

    public function mine(Request $request): JsonResponse
    {
        $posts = ImagePost::query()
            ->where('user_id', $request->user()->id);

        return $this->galleryResponse($posts);
    }

    public function others(Request $request): JsonResponse
    {
        $posts = ImagePost::query()
            ->where('user_id', '!=', $request->user()->id);

        return $this->galleryResponse($posts);
    }

    private function galleryResponse(Builder $posts): JsonResponse
    {
        $images = $posts->latest('id')
            ->limit(24)
            ->get()
            ->map(function (ImagePost $post): array {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'filter' => $post->filter,
                    'image_url' => asset('storage/' . $post->image_path),
                ];
            });

        return response()->json(['images' => $images]);
    }
}