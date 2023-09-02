<?php

namespace NovaThinKit\Tests\Nova\Filters;

use Laravel\Nova\Http\Requests\NovaRequest;
use NovaThinKit\Nova\Filters\EmptyFieldFilter;
use NovaThinKit\Tests\Fixtures\Models\Contact;
use NovaThinKit\Tests\Fixtures\Models\Post;
use NovaThinKit\Tests\TestCase;

class EmptyFieldFilterTest extends TestCase
{
    /** @test */
    public function configuration()
    {
        $filter = EmptyFieldFilter::make('content', 'Empty content');

        $this->assertIsArray($filter->options(app(NovaRequest::class)));
        $this->assertCount(2, $filter->options(app(NovaRequest::class)));
        $this->assertEquals('filled', $filter->options(app(NovaRequest::class))['Filled']);

    }

    /** @test */
    public function direct_filter()
    {
        Post::factory()->count(5)->create();
        Post::factory()->content('Example')->count(6)->create();
        Post::factory()->content('')->count(12)->create();
        Post::factory()->content('Baz')->count(4)->create();

        $filter = EmptyFieldFilter::make('content', 'Empty content');

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'empty'  => true,
            'filled' => false,
        ]);
        $this->assertEquals(17, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'empty'  => false,
            'filled' => true,
        ]);
        $this->assertEquals(10, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'empty'  => true,
            'filled' => true,
        ]);
        $this->assertEquals(27, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'empty'  => false,
            'filled' => false,
        ]);
        $this->assertEquals(27, $query->count());
    }

    /** @test */
    public function relation_filter()
    {
        Contact::factory()
            ->has(Post::factory()->count(5))
            ->has(Post::factory()->content('Example')->count(6))
            ->has(Post::factory()->content('')->count(12))
            ->create();
        Contact::factory()
            ->has(Post::factory()->content('Bar')->count(4))
            ->create();

        $filter = EmptyFieldFilter::make('posts.content');

        $query = Contact::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'empty' => true,
        ]);
        $this->assertEquals(2, $query->count());

        $query = Contact::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'empty'  => false,
            'filled' => true,
        ]);
        $this->assertEquals(1, $query->count());

        $query = Contact::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'empty'  => true,
            'filled' => true,
        ]);
        $this->assertEquals(2, $query->count());

        $query = Contact::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'empty'  => false,
            'filled' => false,
        ]);
        $this->assertEquals(2, $query->count());
    }
}
