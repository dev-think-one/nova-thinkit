<?php

namespace NovaThinKit\Tests\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaThinKit\Tests\Fixtures\Models\Contact;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{

    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'email'    => $this->faker->unique()->email(),
            'password' => bcrypt('secret'),
        ];
    }
}
