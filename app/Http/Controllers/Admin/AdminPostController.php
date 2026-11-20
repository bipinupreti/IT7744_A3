<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImagePost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminPostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => ImagePost::query()->latest('id')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', ['post' => new ImagePost()]);
    }

    public function store(Request $request): RedirectResponse
    {
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
            $this->removeImageFile($path);
            throw $exception;
        }

        return redirect()->route('admin.posts.index')
            ->with('status', 'Post created.');
    }

    public function edit(ImagePost $post): View
    {
        return view('admin.posts.form', ['post' => $post]);
    }

    public function update(Request $request, ImagePost $post): RedirectResponse
    {
        $validated = $request->validate($this->postRules());

        $post->title = $validated['title'];
        $post->description = $validated['description'];
        $post->filter = $validated['filter'];
        $post->save();

        return redirect()->route('admin.posts.index')
            ->with('status', 'Post updated.');
    }

    public function destroy(ImagePost $post): RedirectResponse
    {
        $path = $post->image_path;
        $post->delete();

        $message = $this->removeImageFile($path)
            ? 'Post deleted.'
            : 'Post deleted, but its image file could not be removed.';

        return redirect()->route('admin.posts.index')->with('status', $message);
    }

    private function postRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'filter' => ['required', Rule::in(['none', 'grayscale', 'sepia'])],
        ];
    }

    private function removeImageFile(string $path): bool
    {
        try {
            if (Storage::disk('public')->delete($path)) {
                return true;
            }

            report(new RuntimeException('Could not remove image file: ' . $path));
        } catch (Throwable $exception) {
            report($exception);
        }

        return false;
    }
}