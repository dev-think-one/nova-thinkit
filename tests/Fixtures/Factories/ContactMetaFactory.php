<?php

namespace NovaThinKit\Tests\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaThinKit\Tests\Fixtures\Models\ContactMeta;

/**
 * @extends Factory<ContactMeta>
 */
class ContactMetaFactory extends Factory
{

    protected $model = ContactMeta::class;

    public function definition(): array
    {
        return [
            'key'      => $this->faker->word(),
            'value'    => $this->faker->words(),
        ];
    }
}
