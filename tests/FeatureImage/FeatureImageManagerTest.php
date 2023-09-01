<?php

namespace NovaThinKit\Tests\FeatureImage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NovaThinKit\FeatureImage\FeatureImageManager;
use NovaThinKit\Tests\Fixtures\Models\Page;
use NovaThinKit\Tests\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeatureImageManagerTest extends TestCase
{
    /** @test */
    public function default_path()
    {
        /** @var Page $page */
        $page = Page::factory()->create();

        /** @var FeatureImageManager $manager */
        $manager = $page->featureImageManager();

        $this->assertNull($manager->url());

        $manager->withDefaultPath('foo/bar.png');

        $this->assertEquals('/storage/foo/bar.png', $manager->url());

        $manager->withoutDefaultPath();

        $this->assertNull($manager->url());
    }

    /** @test */
    public function default_path_and_column_from_config()
    {
        /** @var FeatureImageManager $manager */
        $manager = FeatureImageManager::fromConfig([
            'default' => 'baz/bar/foo.png',
            'column'  => 'example_col',
        ]);

        $this->assertEquals('example_col', $manager->tag());
        $this->assertEquals('/storage/baz/bar/foo.png', $manager->url());

        $manager->setTag('bla_bla');
        $this->assertEquals('bla_bla', $manager->tag());
        $this->assertEquals('/storage/baz/bar/foo.png', $manager->url());

    }

    /** @test */
    public function exists_function()
    {
        /** @var Page $page */
        $page = Page::factory()->create();

        /** @var FeatureImageManager $manager */
        $manager = $page->featureImageManager('fooBar');

        $this->assertFalse($manager->exists());

        $page->{$page->featureImageKey('fooBar')} = 'foo/bar.png';

        $this->assertFalse($manager->exists());
        $this->assertFalse($manager->exists('thumb'));

        $page->{$page->featureImageKey('fooBar')} = $manager->storeUploaded(UploadedFile::fake()->image('image.jpg'));
        $page->save();

        $this->assertTrue($manager->exists());
        Storage::disk('baz')->assertExists($page->{$page->featureImageKey('fooBar')});
        $this->assertTrue($manager->exists('thumb'));
        // Invalid format anyway returns value
        $this->assertTrue($manager->exists('foo-bar-baz'));

    }

    /** @test */
    public function delete_direct_file()
    {
        /** @var Page $page */
        $page = Page::factory()->create();

        /** @var FeatureImageManager $manager */
        $manager = $page->featureImageManager('fooBar');

        $manager->storage()->put('foo/baz.png', 'test');

        $this->assertTrue($manager->storage()->exists('foo/baz.png'));
        Storage::disk('baz')->assertExists('foo/baz.png');

        $manager->delete('foo/baz.png');

        $this->assertFalse($manager->storage()->exists('foo/baz.png'));
        Storage::disk('baz')->assertMissing('foo/baz.png');
    }

    /** @test */
    public function store_delete_function()
    {
        /** @var Page $page */
        $page = Page::factory()->create();

        /** @var FeatureImageManager $manager */
        $manager = $page->featureImageManager('fooBar');

        $page->{$page->featureImageKey('fooBar')} = $manager->storeUploaded(UploadedFile::fake()->image('image.jpg'));
        $page->save();

        $this->assertTrue($manager->exists());
        $this->assertTrue($manager->exists(1200));

        $manager->delete();

        $this->assertFalse($manager->exists());
        $this->assertFalse($manager->exists(1200));
    }

    /** @test */
    public function url_function()
    {
        /** @var Page $page */
        $page = Page::factory()->create();

        /** @var FeatureImageManager $manager */
        $manager = $page->featureImageManager('fooBar');

        $page->{$page->featureImageKey('fooBar')} = $manager->storeUploaded(UploadedFile::fake()->image('image.jpg'));
        $page->save();

        $this->assertNotEmpty($manager->url());
        $this->assertEquals($manager->url(), $manager->url(1200));
    }

    /** @test */
    public function download_function()
    {
        /** @var Page $page */
        $page = Page::factory()->create();

        /** @var FeatureImageManager $manager */
        $manager = $page->featureImageManager('fooBar');

        $page->{$page->featureImageKey('fooBar')} = $manager->storeUploaded(UploadedFile::fake()->image('image.jpg'));
        $page->save();

        $this->assertInstanceOf(StreamedResponse::class, $manager->download());
        $this->assertInstanceOf(StreamedResponse::class, $manager->download(1200));
    }

    /** @test */
    public function get_model()
    {
        /** @var Page $page */
        $page = Page::factory()->create();

        /** @var FeatureImageManager $manager */
        $manager = $page->featureImageManager('fooBar');

        $page->{$page->featureImageKey('fooBar')} = $manager->storeUploaded(UploadedFile::fake()->image('image.jpg'));
        $page->save();

        $this->assertInstanceOf($page::class, $manager->getModel());
        $this->assertEquals($page->getKey(), $manager->getModel()->getKey());
    }
}
