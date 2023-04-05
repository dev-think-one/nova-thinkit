<?php

namespace NovaThinKit\Nova\Flexible\Resolvers;

use Illuminate\Support\Arr;

class LayoutValue implements \Stringable
{
    public function __construct(
        protected array $data
    ) {
    }

    public function __get(string $name)
    {
        return Arr::get($this->data, $name);
    }

    public function attribute(string $key)
    {
        return Arr::get($this->data, "attributes.{$key}");
    }

    public function __toString(): string
    {
        return json_encode($this->data);
    }
}
