<?php

namespace NovaThinKit\Tests\FeatureImage;

use NovaThinKit\FeatureImage\FeatureImageManager;
use NovaThinKit\Tests\Fixtures\Models\Page;
use NovaThinKit\Tests\TestCase;

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
}
