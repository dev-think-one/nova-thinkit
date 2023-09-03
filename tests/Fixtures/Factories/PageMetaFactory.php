<?php

namespace NovaThinKit\Tests\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaThinKit\Tests\Fixtures\Models\PageMeta;

/**
 * @extends Factory<PageMeta>
 */
class PageMetaFactory extends Factory
{
    protected $model = PageMeta::class;

    public function definition(): array
    {
        return [
            'key'      => $this->faker->word(),
            'value'    => $this->faker->words(),
        ];
    }
}
