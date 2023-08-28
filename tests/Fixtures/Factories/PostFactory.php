<?php

namespace NovaThinKit\Tests\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaThinKit\Tests\Fixtures\Models\Post;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{

    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->word(),
        ];
    }
}
