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

    public function attribute(string $key): mixed
    {
        return Arr::get($this->data, "attributes.{$key}");
    }

    public function layoutsAttribute(string $key): array
    {
        $value = $this->attribute($key);
        if($value && is_array($value)) {
            return array_filter(array_map(function ($item) {
                $layoutValue = new LayoutValue(!is_array($item) ? (array)$item : $item);
                if($layoutValue->layout && $layoutValue->key && $layoutValue->attributes) {
                    return $layoutValue;
                }

                return null;
            }, $value));
        }

        return [];
    }

    public function __toString(): string
    {
        return json_encode($this->data);
    }
}
