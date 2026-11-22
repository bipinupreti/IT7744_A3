<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImagePublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_retrying_an_upload_creates_only_one_post_and_one_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $uploadToken = Str::random(64);
        $requestId = (string) Str::uuid();

        $this->actingAs($user)->withSession([
            'image_upload_token' => $uploadToken,
        ]);

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $uploadToken,
        ];

        $fields = [
            'title' => 'Test image',
            'description' => 'An image for the upload retry test.',
            'filter' => 'none',
            'upload_request_id' => $requestId,
        ];

        $firstResponse = $this->post(route('image-posts.store'), [
            ...$fields,
            'image' => $this->makePng(),
        ], $headers);

        $firstResponse->assertCreated();
        $postId = $firstResponse->json('id');
        $this->assertNotNull($postId);

        $secondResponse = $this->post(route('image-posts.store'), [
            ...$fields,
            'image' => $this->makePng(),
        ], $headers);

        $secondResponse->assertOk()->assertJsonPath('id', $postId);

        $this->assertDatabaseCount('image_posts', 1);
        $this->assertDatabaseHas('image_posts', [
            'id' => $postId,
            'user_id' => $user->id,
            'title' => $fields['title'],
        ]);

        $this->assertCount(1, Storage::disk('public')->allFiles('image-posts'));
    }

    private function makePng(): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+X2ZkAAAAASUVORK5CYII='
        );

        return UploadedFile::fake()->createWithContent('test.png', $png);
    }
}