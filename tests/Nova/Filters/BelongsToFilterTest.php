<?php

namespace NovaThinKit\Tests\Nova\Filters;

use Laravel\Nova\Http\Requests\NovaRequest;
use NovaThinKit\Nova\Filters\BelongsToFilter;
use NovaThinKit\Tests\Fixtures\Models\Contact;
use NovaThinKit\Tests\Fixtures\Models\Post;
use NovaThinKit\Tests\Fixtures\Models\User;
use NovaThinKit\Tests\TestCase;

class BelongsToFilterTest extends TestCase
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
        $contact1 = Contact::factory()
            ->has(Post::factory()->count(5))
            ->create();
        $contact2 = Contact::factory()
            ->has(Post::factory()->count(12))
            ->create();
        $contact3 = Contact::factory()
            ->has(Post::factory()->count(33))
            ->create();

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Post::uriKey();

        $response = $this->get("nova-api/{$uriKey}/filters");

        $this->assertEquals('By user', $response->json('0.name'));
        $this->assertCount(3, $response->json('0.options'));
        $this->assertEquals($contact1->email, $response->json('0.options.0.label'));
        $this->assertEquals($contact2->email, $response->json('0.options.1.label'));
        $this->assertEquals($contact3->email, $response->json('0.options.2.label'));
    }

    /** @test */
    public function filter()
    {
        $contact1 = Contact::factory()
            ->has(Post::factory()->count(5))
            ->create();
        $contact2 = Contact::factory()
            ->has(Post::factory()->count(12))
            ->create();
        $contact3 = Contact::factory()
            ->has(Post::factory()->count(33))
            ->create();

        $filter =  BelongsToFilter::make('contact')
            ->setTitleKeyName('email');

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            $contact3->getKey() => true,
        ]);
        $this->assertEquals(33, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            $contact1->getKey() => true,
            $contact3->getKey() => true,
        ]);
        $this->assertEquals(38, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            $contact1->getKey() => true,
            $contact3->getKey() => true,
            $contact2->getKey() => true,
        ]);
        $this->assertEquals(50, $query->count());

        $query = Post::query();
        $filter->apply(app(NovaRequest::class), $query, [
            $contact1->getKey() => false,
            $contact3->getKey() => false,
            $contact2->getKey() => false,
        ]);
        $this->assertEquals(50, $query->count());
    }
}
