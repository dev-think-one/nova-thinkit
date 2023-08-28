<?php

namespace NovaThinKit\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NovaThinKit\Tests\Fixtures\Models\Page;
use NovaThinKit\Tests\Fixtures\Models\Post;
use NovaThinKit\Tests\Fixtures\Models\User;

class NovaFeatureImageApiTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $this->actingAs($this->admin);
    }

    /** @test */
    public function update_feature_image_default()
    {
        Post::factory()->count(12)->create();
        $post = Post::factory()->create();

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Post::uriKey();

        $response = $this->put("nova-api/{$uriKey}/{$post->getKey()}", [
            'title'     => 'Foo title',
            'image' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $this->assertEquals($post->getKey(), $response->json('resource.id'));
        $this->assertEquals('Foo title', $response->json('resource.title'));
        $this->assertStringStartsWith(base64_encode(class_basename($post) . '-' . $post->getKey()), $response->json('resource.image'));
        $this->assertStringEndsWith('.jpg', $response->json('resource.image'));

        $savedImage = $response->json('resource.image');
        Storage::disk('feature-images')->assertExists($savedImage);

        $post->refresh();

        $this->assertStringEndsWith($post->image, $response->json('resource.image'));

        // Upload again
        $response = $this->put("nova-api/{$uriKey}/{$post->getKey()}", [
            'title'     => 'Foo 2',
            'image' => UploadedFile::fake()->image('avatar2.jpg'),
        ]);
        $this->assertEquals($post->getKey(), $response->json('resource.id'));
        $this->assertEquals('Foo 2', $response->json('resource.title'));
        $this->assertStringStartsWith(base64_encode(class_basename($post) . '-' . $post->getKey()), $response->json('resource.image'));
        $this->assertStringEndsWith('.jpg', $response->json('resource.image'));

        $reSavedImage = $response->json('resource.image');
        Storage::disk('feature-images')->assertExists($reSavedImage);
        Storage::disk('feature-images')->assertMissing($savedImage);
    }

    /** @test */
    public function update_feature_image()
    {
        Page::factory()->count(12)->create();
        $page = Page::factory()->create();

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Page::uriKey();

        $response = $this->put("nova-api/{$uriKey}/{$page->getKey()}", [
            'title'     => 'Foo title',
            'baz_image' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $this->assertEquals($page->getKey(), $response->json('resource.id'));
        $this->assertEquals('Foo title', $response->json('resource.title'));
        $this->assertStringStartsWith("foo-page/{$page->getKey()}/", $response->json('resource.baz_image'));
        $this->assertStringEndsWith('.jpg', $response->json('resource.baz_image'));

        $savedImage = $response->json('resource.baz_image');
        Storage::disk('baz')->assertExists($savedImage);

        $page->refresh();

        $this->assertStringEndsWith($page->baz_image, $response->json('resource.baz_image'));

        // Upload again
        $response = $this->put("nova-api/{$uriKey}/{$page->getKey()}", [
            'title'     => 'Foo 2',
            'baz_image' => UploadedFile::fake()->image('avatar2.jpg'),
        ]);
        $this->assertEquals($page->getKey(), $response->json('resource.id'));
        $this->assertEquals('Foo 2', $response->json('resource.title'));
        $this->assertStringStartsWith("foo-page/{$page->getKey()}/", $response->json('resource.baz_image'));
        $this->assertStringEndsWith('.jpg', $response->json('resource.baz_image'));

        $reSavedImage = $response->json('resource.baz_image');
        Storage::disk('baz')->assertExists($reSavedImage);
        Storage::disk('baz')->assertMissing($savedImage);
    }
}
