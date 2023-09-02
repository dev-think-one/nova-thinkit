<?php

namespace NovaThinKit\Tests\Nova\Actions;

use Laravel\Nova\Http\Requests\NovaRequest;
use NovaThinKit\Nova\Actions\SendResetPasswordEmail;
use NovaThinKit\Tests\Fixtures\Models\Contact;
use NovaThinKit\Tests\Fixtures\Models\User;
use NovaThinKit\Tests\TestCase;

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
        $this->assertCount(2, $response->json('actions'));

        $action = $response->json('actions')[1];

        $contactResource = new \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact($contact);
        /** @var SendResetPasswordEmail $resourceAction */
        $resourceAction = $contactResource->actions(app(NovaRequest::class))[1];

        $this->assertEquals($resourceAction->uriKey(), $action['uriKey']);

        $this->assertIsArray($action['fields']);
        $this->assertCount(1, $action['fields']);
        $this->assertEquals('html-field', $action['fields'][0]['component']);

    }
}
