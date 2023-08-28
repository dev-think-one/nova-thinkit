<?php

namespace NovaThinKit\Tests\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaThinKit\Tests\Fixtures\Models\Page;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{

    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->word(),
        ];
    }
}
