<?php

namespace NovaThinKit\Tests\Nova\Filters;

use Laravel\Nova\Http\Requests\NovaRequest;
use NovaThinKit\Nova\Filters\BelongsToManyFilter;
use NovaThinKit\Tests\Fixtures\Models\Post;
use NovaThinKit\Tests\Fixtures\Models\Tag;
use NovaThinKit\Tests\Fixtures\Models\User;
use NovaThinKit\Tests\TestCase;

class BelongsToManyFilterTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $this->actingAs($this->admin);
    }

    /** @test */
    public function get_options()
    {
        $tag1 = Tag::factory()
            ->has(Post::factory()->count(5))
            ->create();
        $tag2 = Tag::factory()
            ->has(Post::factory()->count(12))
            ->create();
        $tag3 = Tag::factory()
            ->has(Post::factory()->count(33))
            ->create();

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Post::uriKey();

        $response = $this->get("nova-api/{$uriKey}/filters");

        $this->assertEquals('By tag', $response->json('1.name'));
        $this->assertCount(3, $response->json('1.options'));
        $this->assertEquals($tag1->name, $response->json('1.options.0.label'));
        $this->assertEquals($tag2->name, $response->json('1.options.1.label'));
        $this->assertEquals($tag3->name, $response->json('1.options.2.label'));
    }

    /** @test */
    public function filter()
    {
        $tag1 = Tag::factory()
            ->has(Post::factory()->count(5))
            ->create();
        $tag2 = Tag::factory()
            ->has(Post::factory()->count(12))
            ->create();
        $tag3 = Tag::factory()
            ->has(Post::factory()->count(33))
            ->create();

        $filter =  BelongsToManyFilter::make('tags')
            ->setTitleKeyName('name');

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            $tag3->getKey() => true,
        ]);
        $this->assertEquals(33, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            $tag1->getKey() => true,
            $tag3->getKey() => true,
        ]);
        $this->assertEquals(38, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            $tag1->getKey() => true,
            $tag3->getKey() => true,
            $tag2->getKey() => true,
        ]);
        $this->assertEquals(50, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            $tag1->getKey() => false,
            $tag3->getKey() => false,
            $tag2->getKey() => false,
        ]);
        $this->assertEquals(50, $query->count());
    }
}
