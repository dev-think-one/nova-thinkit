<?php

namespace NovaThinKit\Tests\Nova\Actions;

use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaThinKit\Nova\Actions\LoginToDifferentGuard;
use NovaThinKit\Tests\Fixtures\Models\Contact;
use NovaThinKit\Tests\Fixtures\Models\User;
use NovaThinKit\Tests\TestCase;
use ThinkStudio\HtmlField\Html;

class LoginToDifferentGuardTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $this->actingAs($this->admin);
    }

    /** @test */
    public function show_fields()
    {
        Contact::factory()->count(12)->create();
        $contact = Contact::factory()->create();

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();

        $response = $this->get("nova-api/{$uriKey}/actions");

        $this->assertIsArray($response->json('actions'));
        $this->assertCount(3, $response->json('actions'));

        $action = $response->json('actions')[0];

        $contactResource = new \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact($contact);
        /** @var LoginToDifferentGuard $resourceAction */
        $resourceAction = $contactResource->actions(app(NovaRequest::class))[0];

        $this->assertEquals($resourceAction->uriKey(), $action['uriKey']);
        $this->assertEquals($resourceAction->name(), $action['name']);
        $this->assertEquals('Foo', $action['name']);
        $this->assertEquals('Bar baz', $action['confirmText']);

        $this->assertIsArray($action['fields']);
        $this->assertCount(2, $action['fields']);
        $this->assertEquals('html-field', $action['fields'][0]['component']);
        $this->assertEquals('boolean-field', $action['fields'][1]['component']);
        $this->assertEquals('remember_me', $action['fields'][1]['attribute']);

        $fields = $resourceAction->fields(app(NovaRequest::class));
        /** @var Html $htmlFiled */
        $htmlFiled = $fields[0];
        $htmlFiled->resolve($contact);
        $this->assertEquals($action['confirmText'], $htmlFiled->value);
        $this->assertEquals('Bar baz', $htmlFiled->value);
    }

    /** @test */
    public function handle()
    {
        Contact::factory()->count(12)->create();
        $contact = Contact::factory()->create();

        $uriKey          = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();
        $contactResource = new \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact($contact);
        /** @var LoginToDifferentGuard $resourceAction */
        $resourceAction = $contactResource->actions(app(NovaRequest::class))[0];

        $this->assertGuest('contact_web');
        $response = $this->post("nova-api/{$uriKey}/action?action={$resourceAction->uriKey()}", [
            'resources' => $contact->getKey(),
        ]);
        $this->assertAuthenticated('contact_web');

        $this->assertEquals('http://me.bar', $response->json('openInNewTab'));
    }

    /** @test */
    public function handle_callback_function()
    {
        Contact::factory()->count(12)->create();
        $contact = Contact::factory()->create();

        $uriKey          = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();
        $contactResource = new \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact($contact);
        /** @var LoginToDifferentGuard $resourceAction */
        $resourceAction = $contactResource->actions(app(NovaRequest::class))[2];

        $this->assertGuest('contact_web');
        $response = $this->post("nova-api/{$uriKey}/action?action={$resourceAction->uriKey()}", [
            'resources' => $contact->getKey(),
        ]);
        $this->assertAuthenticated('contact_web');

        $this->assertEquals('http://other.bar', $response->json('openInNewTab'));
    }

    /** @test */
    public function handle_no_model()
    {
        app('config')->set('auth.providers.contacts.model', \Illuminate\Foundation\Auth\User::class);

        Contact::factory()->count(12)->create();
        $contact = Contact::factory()->create();

        $contactResource = new \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact($contact);
        /** @var LoginToDifferentGuard $resourceAction */
        $resourceAction = $contactResource->actions(app(NovaRequest::class))[0];

        /** @var ActionResponse $result */
        $result = $resourceAction->handle(new ActionFields(collect(), collect()), collect());

        $this->assertEquals('Something went wrong! User not found.', $result->jsonSerialize()['danger']);
    }
}
