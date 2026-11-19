<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ImageGalleryController;
use App\Http\Controllers\ImagePostController;
use App\Models\ImagePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::view('/', 'gallery')->name('gallery');

Route::get('/api/image-posts/latest', [ImageGalleryController::class, 'latest'])
    ->name('image-posts.latest');

Route::get('/register', [RegisterController::class, 'create'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'store'])
    ->name('register.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        if (! $request->session()->has('image_upload_token')) {
            $request->session()->put('image_upload_token', Str::random(64));
        }

        return view('dashboard', [
            'uploadToken' => $request->session()->get('image_upload_token'),
            'posts' => ImagePost::query()
                ->where('user_id', $request->user()->id)
                ->latest('id')
                ->get(),
        ]);
    })->name('dashboard');

    Route::get('/api/image-posts/mine', [ImageGalleryController::class, 'mine'])
        ->name('image-posts.mine');

    Route::get('/api/image-posts/others', [ImageGalleryController::class, 'others'])
        ->name('image-posts.others');

    Route::post('/api/image-posts', [ImagePostController::class, 'store'])
        ->name('image-posts.store');

    Route::patch('/image-posts/{imagePost}', [ImagePostController::class, 'update'])
        ->name('image-posts.update');

    Route::delete('/image-posts/{imagePost}', [ImagePostController::class, 'destroy'])
        ->name('image-posts.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'can:access-admin'])
    ->group(function (): void {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');
    });