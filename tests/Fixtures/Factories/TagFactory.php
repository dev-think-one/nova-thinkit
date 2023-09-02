<?php

namespace NovaThinKit\Tests\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaThinKit\Tests\Fixtures\Models\Tag;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{

    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'name'  => $this->faker->unique()->word(),
        ];
    }
}
