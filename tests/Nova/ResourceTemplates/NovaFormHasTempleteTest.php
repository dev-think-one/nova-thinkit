<?php

namespace NovaThinKit\Tests\Nova\ResourceTemplates;

use NovaThinKit\Tests\Fixtures\Models\Page;
use NovaThinKit\Tests\Fixtures\Models\User;
use NovaThinKit\Tests\TestCase;

class NovaFormHasTempleteTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $this->actingAs($this->admin);
    }

    /** @test */
    public function get_form_without_template()
    {
        /** @var Page $page */
        $page = Page::factory()->template()->create();

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Page::uriKey();

        $response = $this->get("nova-api/{$uriKey}/{$page->getKey()}/update-fields", []);

        $response->assertSuccessful();

        $this->assertIsArray($response->json('fields'));
        $this->assertCount(3, $response->json('fields'));
        $this->assertCount(1, $response->json('fields.2.options'));
        $this->assertEquals('home', $response->json('fields.2.options.0.value'));
    }

    /** @test */
    public function get_form_with_template()
    {
        /** @var Page $page */
        $page = Page::factory()->template('home')->create();

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Page::uriKey();

        $response = $this->get("nova-api/{$uriKey}/{$page->getKey()}/update-fields", []);

        $response->assertSuccessful();

        $this->assertIsArray($response->json('fields'));
        $this->assertCount(4, $response->json('fields'));
        $this->assertCount(1, $response->json('fields.2.options'));
        $this->assertEquals('home', $response->json('fields.2.options.0.value'));

        $this->assertEquals('custom_text', $response->json('fields.3.attribute'));
        $this->assertEquals('Custom text', $response->json('fields.3.indexName'));
        $this->assertNull($response->json('fields.3.value'));


        $page->meta()->updateOrCreate(
            ['key' => 'custom_text'],
            ['value' => 'Boo Bar baz']
        );
        $page->refresh();

        $response = $this->get("nova-api/{$uriKey}/{$page->getKey()}/update-fields", []);

        $response->assertSuccessful();
        $this->assertEquals('Boo Bar baz', $response->json('fields.3.value'));
    }

    /** @test */
    public function get_form_with_wrong_template()
    {
        /** @var Page $page */
        $page = Page::factory()->template('xxx_foo')->create();

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Page::uriKey();

        $response = $this->get("nova-api/{$uriKey}/{$page->getKey()}/update-fields", []);

        $response->assertSuccessful();

        $this->assertIsArray($response->json('fields'));
        $this->assertCount(3, $response->json('fields'));
        $this->assertCount(1, $response->json('fields.2.options'));
        $this->assertEquals('home', $response->json('fields.2.options.0.value'));
    }

}
