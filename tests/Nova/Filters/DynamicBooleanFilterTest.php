<?php

namespace NovaThinKit\Tests\Nova\Filters;

use Laravel\Nova\Http\Requests\NovaRequest;
use NovaThinKit\Nova\Filters\DynamicBooleanFilter;
use NovaThinKit\Tests\Fixtures\Models\Contact;
use NovaThinKit\Tests\Fixtures\Models\Post;
use NovaThinKit\Tests\TestCase;

class DynamicBooleanFilterTest extends TestCase
{
    /** @test */
    public function configuration()
    {
        $filter = DynamicBooleanFilter::make([
            'Published' => 'published',
            'Draft'     => 'draft',
        ], 'status', 'Foo');

        $this->assertIsArray($filter->options(app(NovaRequest::class)));
        $this->assertCount(2, $filter->options(app(NovaRequest::class)));
        $this->assertEquals('draft', $filter->options(app(NovaRequest::class))['Draft']);

        $this->assertEquals('nova-thin-kit\-nova\-filters\-dynamic-boolean-filter-status--foo', $filter->key());
    }

    /** @test */
    public function direct_filter()
    {
        Post::factory()->count(5)->create();
        Post::factory()->published()->count(6)->create();
        Post::factory()->count(12)->create();
        Post::factory()->published()->count(4)->create();

        $filter = DynamicBooleanFilter::make([
            'Published' => 'published',
            'Draft'     => 'draft',
        ], 'status');

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'published' => true,
        ]);
        $this->assertEquals(10, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'published' => false,
            'draft'     => true,
        ]);
        $this->assertEquals(17, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'published' => true,
            'draft'     => true,
        ]);
        $this->assertEquals(27, $query->count());

        // If all file no apply filter.
        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'published' => false,
            'draft'     => false,
        ]);
        $this->assertEquals(27, $query->count());
    }

    /** @test */
    public function relation_filter()
    {
        Contact::factory()
            ->has(Post::factory()->count(5))
            ->has(Post::factory()->published()->count(6))
            ->has(Post::factory()->count(12))
            ->create();
        Contact::factory()
            ->has(Post::factory()->published()->count(4))
            ->create();

        $filter = DynamicBooleanFilter::make([
            'Published' => 'published',
            'Draft'     => 'draft',
        ], 'status')->forRelation('posts');

        $query = Contact::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'published' => true,
        ]);
        $this->assertEquals(2, $query->count());

        $query = Contact::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'published' => false,
            'draft'     => true,
        ]);
        $this->assertEquals(1, $query->count());

        $query = Contact::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'published' => true,
            'draft'     => true,
        ]);
        $this->assertEquals(2, $query->count());

        $query = Contact::query();
        $filter->apply(app(NovaRequest::class), $query, [
            'published' => false,
            'draft'     => false,
        ]);
        $this->assertEquals(2, $query->count());
    }
}
