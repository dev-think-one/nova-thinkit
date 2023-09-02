<?php

namespace NovaThinKit\Tests\Nova\Helpers;

use Illuminate\Support\Str;
use NovaFlexibleContent\Http\FlexibleAttribute;
use NovaThinKit\Tests\Fixtures\Models\Contact;
use NovaThinKit\Tests\Fixtures\Models\User;
use NovaThinKit\Tests\TestCase;

class MetaFieldUpdaterTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $this->actingAs($this->admin);
    }

    /** @test */
    public function update_meta_simple()
    {
        /** @var Contact $contact */
        $contact = Contact::factory()->create();

        $meta = $contact->meta()
            ->where('key', 'company_position')
            ->first();
        $this->assertNull($meta);

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();

        $response = $this->put("nova-api/{$uriKey}/{$contact->getKey()}", [
            'email'            => 'new@foo.bar',
            'company_position' => 'developer',
        ]);

        $response->assertSuccessful();

        $meta = $contact->meta()
            ->where('key', 'company_position')
            ->first();
        $this->assertNotNull($meta);
        $this->assertEquals('developer', $meta->value);


        $response = $this->get("nova-api/{$uriKey}/{$contact->getKey()}");

        $this->assertEquals('developer', $response->json('resource.fields.2.value'));
    }

    /** @test */
    public function update_meta_callback()
    {
        /** @var Contact $contact */
        $contact = Contact::factory()->create();

        $meta = $contact->meta()
            ->where('key', 'formatted_price')
            ->first();
        $this->assertNull($meta);

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();

        $response = $this->put("nova-api/{$uriKey}/{$contact->getKey()}", [
            'email' => 'new@foo.bar',
            'price' => 1.354,
        ]);

        $response->assertSuccessful();

        $meta = $contact->meta()
            ->where('key', 'formatted_price')
            ->first();
        $this->assertNotNull($meta);
        $this->assertEquals(135, $meta->value);


        $response = $this->get("nova-api/{$uriKey}/{$contact->getKey()}");

        $this->assertEquals(1.35, $response->json('resource.fields.3.value'));
    }

    /** @test */
    public function update_meta_array()
    {
        /** @var Contact $contact */
        $contact = Contact::factory()->create();

        $meta = $contact->meta()
            ->where('key', 'list')
            ->first();
        $this->assertNull($meta);

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();

        $response = $this->put("nova-api/{$uriKey}/{$contact->getKey()}", [
            'email' => 'new@foo.bar',
            'list'  => json_encode([
                'foo' => 'bar',
                'baz' => 'qux',
            ]),
        ]);

        $response->assertSuccessful();

        $meta = $contact->meta()
            ->where('key', 'list')
            ->first();
        $this->assertNotNull($meta);
        $this->assertEquals('{"foo":"bar","baz":"qux"}', $meta->value);

        $response = $this->get("nova-api/{$uriKey}/{$contact->getKey()}");
        $this->assertIsArray($response->json('resource.fields.4.value'));
        $this->assertCount(2, $response->json('resource.fields.4.value'));
        $this->assertEquals('bar', $response->json('resource.fields.4.value')['foo']);
        $this->assertEquals('qux', $response->json('resource.fields.4.value')['baz']);
    }

    /** @test */
    public function update_meta_raw_value()
    {
        /** @var Contact $contact */
        $contact = Contact::factory()->create();

        $meta = $contact->meta()
            ->where('key', 'code')
            ->first();
        $this->assertNull($meta);

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();

        $response = $this->put("nova-api/{$uriKey}/{$contact->getKey()}", [
            'email' => 'new@foo.bar',
            'code'  => json_encode([
                'foo' => 'bar',
                'baz' => 'qux',
            ]),
        ]);

        $response->assertSuccessful();

        $meta = $contact->meta()
            ->where('key', 'code')
            ->first();
        $this->assertNotNull($meta);
        $this->assertEquals('{"foo":"bar","baz":"qux"}', $meta->value);

        $response = $this->get("nova-api/{$uriKey}/{$contact->getKey()}");
        $this->assertIsString($response->json('resource.fields.5.value'));
        $this->assertEquals('{"foo":"bar","baz":"qux"}', $response->json('resource.fields.5.value'));
    }

    /** @test */
    public function update_meta_flexible()
    {
        /** @var Contact $contact */
        $contact = Contact::factory()->create();

        $meta = $contact->meta()
            ->where('key', 'content')
            ->first();
        $this->assertNull($meta);

        $uriKey = \NovaThinKit\Tests\Fixtures\Nova\Resources\Contact::uriKey();

        $groupKey    = 'c' . Str::random(15);

        $response = $this->put("nova-api/{$uriKey}/{$contact->getKey()}", [
            'email'                                         => 'new@foo.bar',
            FlexibleAttribute::REGISTER_FLEXIBLE_FIELD_NAME => json_encode(['content']),
            'content'                                       => [
                [
                    'layout'     => 'simple_number',
                    'key'        => $groupKey,
                    'collapsed'  => true,
                    'attributes' => [
                        'order' => 5,
                    ],
                ],
            ],
        ]);

        $response->assertSuccessful();

        $meta = $contact->meta()
              ->where('key', 'content')
              ->first();
        $this->assertNotNull($meta);
        $this->assertStringContainsString('"attributes":{"order":5}', $meta->value);

        $response = $this->get("nova-api/{$uriKey}/{$contact->getKey()}");
        $this->assertIsArray($response->json('resource.fields.6.value'));
        $this->assertEquals(5, $response->json('resource.fields.6.value.0.attributes.0.value'));
    }

}
