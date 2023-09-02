<?php

namespace NovaThinKit\Tests\Nova\Actions;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaThinKit\Nova\Actions\SendResetPasswordEmail;
use NovaThinKit\Tests\Fixtures\Models\Contact;
use NovaThinKit\Tests\Fixtures\Models\User;
use NovaThinKit\Tests\TestCase;
use ThinkStudio\HtmlField\Html;

class SendResetPasswordEmailTest extends TestCase
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

        $action = $response->json('actions')[1];

        $contactResource = new \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact($contact);
        /** @var SendResetPasswordEmail $resourceAction */
        $resourceAction = $contactResource->actions(app(NovaRequest::class))[1];

        $this->assertEquals($resourceAction->uriKey(), $action['uriKey']);
        $this->assertEquals($resourceAction->name(), $action['name']);
        $this->assertEquals('Baz', $action['name']);
        $this->assertEquals('Qux quix', $action['confirmText']);

        $this->assertIsArray($action['fields']);
        $this->assertCount(1, $action['fields']);
        $this->assertEquals('html-field', $action['fields'][0]['component']);

        $fields = $resourceAction->fields(app(NovaRequest::class));
        /** @var Html $htmlFiled */
        $htmlFiled = $fields[0];
        $htmlFiled->resolve($contact);
        $this->assertEquals($action['confirmText'], $htmlFiled->value);
        $this->assertEquals('Qux quix', $htmlFiled->value);
    }

    /** @test */
    public function handle()
    {
        Notification::fake();

        Contact::factory()->count(12)->create();
        $contact = Contact::factory()->create();

        $uriKey          = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();
        $contactResource = new \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact($contact);
        /** @var SendResetPasswordEmail $resourceAction */
        $resourceAction = $contactResource->actions(app(NovaRequest::class))[1];

        Notification::assertNothingSent();
        $response = $this->post("nova-api/{$uriKey}/action?action={$resourceAction->uriKey()}", [
            'resources' => $contact->getKey(),
        ]);
        Notification::assertSentTo(
            [$contact],
            ResetPassword::class
        );

        $this->assertEquals('We have emailed your password reset link.', $response->json('message'));
    }

    /** @test */
    public function handle_incorrect_model()
    {
        Notification::fake();

        app('config')->set('auth.providers.contacts.model', \Illuminate\Foundation\Auth\User::class);

        Contact::factory()->count(12)->create();
        $contact = Contact::factory()->create();

        $uriKey          = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();
        $contactResource = new \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact($contact);
        /** @var SendResetPasswordEmail $resourceAction */
        $resourceAction = $contactResource->actions(app(NovaRequest::class))[1];

        Notification::assertNothingSent();
        $response = $this->post("nova-api/{$uriKey}/action?action={$resourceAction->uriKey()}", [
            'resources' => $contact->getKey(),
        ]);
        Notification::assertNothingSent();

        $this->assertEquals('We can\'t find a user with that email address.', $response->json('danger'));
    }

    /** @test */
    public function handle_no_model()
    {
        Notification::fake();

        app('config')->set('auth.providers.contacts.model', \Illuminate\Foundation\Auth\User::class);

        Contact::factory()->count(12)->create();
        $contact = Contact::factory()->create();

        $contactResource = new \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact($contact);
        /** @var SendResetPasswordEmail $resourceAction */
        $resourceAction = $contactResource->actions(app(NovaRequest::class))[1];

        Notification::assertNothingSent();
        /** @var ActionResponse $result */
        $result = $resourceAction->handle(new ActionFields(collect(), collect()), collect());
        Notification::assertNothingSent();

        $this->assertEquals('Something went wrong! User not found.', $result->jsonSerialize()['danger']);
    }
}
