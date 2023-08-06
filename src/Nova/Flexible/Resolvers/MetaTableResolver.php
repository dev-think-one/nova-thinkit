<?php

namespace NovaThinKit\Nova\Flexible\Resolvers;

use Illuminate\Support\Collection;
use NovaFlexibleContent\Layouts\Collections\GroupsCollection;
use NovaFlexibleContent\Layouts\Collections\LayoutsCollection;
use NovaFlexibleContent\Value\Resolver;

class MetaTableResolver implements Resolver
{
    protected string $relationship;
    protected string $keyName;
    protected string $dataKeyName;

    /**
     * @param string $relationship
     */
    public function __construct(string $relationship, string $keyName = 'key', string $dataKeyName = 'data')
    {
        $this->relationship = $relationship;
        $this->keyName      = $keyName;
        $this->dataKeyName  = $dataKeyName;
    }

    /**
     * @inerhitDoc
     */
    public function get(mixed $resource, string $attribute, LayoutsCollection $groups): GroupsCollection
    {
        $meta = $resource->{$this->relationship}()->where($this->keyName, $attribute)->first();
        if ($meta) {
            $value = static::extractValueFromResource($meta, $this->dataKeyName);

            return GroupsCollection::make($value)->map(function ($item) use ($groups) {
                $layout = $groups->find($item->layout);

                if (!$layout) {
                    throw new \Exception("Layout [{$item->layout}] not found");
                }

                return $layout->duplicate($item->key, (array)$item->attributes);
            })->filter()->values();
        }

        return GroupsCollection::make([]);
    }

    /**
     * @param $resource
     * @param string $attribute
     * @return array
     */
    public static function extractValueFromResource($resource, string $attribute = 'data'): array
    {
        $value = data_get($resource, str_replace('->', '.', $attribute)) ?? [];

        if ($value instanceof Collection) {
            $value = $value->toArray();
        } elseif (is_string($value)) {
            $value = json_decode($value, true) ?? [];
        }

        // Fail silently in case data is invalid
        if (!is_array($value)) {
            return [];
        }

        return array_filter(array_map(function ($item) {
            $layoutValue = new LayoutValue(!is_array($item) ? (array)$item : $item);
            if($layoutValue->layout && $layoutValue->key && $layoutValue->attributes) {
                return $layoutValue;
            }

            return null;
        }, $value));
    }

    /**
     * @inerhitDoc
     */
    public function set(mixed $resource, string $attribute, GroupsCollection $groups): string
    {
        $resource->{$this->relationship}()->updateOrCreate(
            [$this->keyName => $attribute],
            [$this->dataKeyName => $groups->map(function ($group) {
                return [
                    'layout'     => $group->name(),
                    'key'        => $group->key(),
                    'attributes' => $group->getAttributes(),
                ];
            })]
        );

        return '';
    }
}
