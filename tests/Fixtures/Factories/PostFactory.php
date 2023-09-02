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
            'title'  => $this->faker->unique()->word(),
            'status' => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status' => 'published',
        ]);
    }

    public function content(?string $content = null): static
    {
        return $this->state([
            'content' => $content,
        ]);
    }
}
