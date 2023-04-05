<?php

namespace NovaThinKit\Nova\Helpers;

use Laravel\Nova\Fields\Field;

class MetaFieldUpdater
{
    protected string $relationship;
    protected string $keyName;
    protected string $dataKeyName;

    /**
     * @param string $relationship
     * @param string $keyName
     * @param string $dataKeyName
     */
    public function __construct(string $relationship, string $keyName = 'key', string $dataKeyName = 'data')
    {
        $this->relationship = $relationship;
        $this->keyName      = $keyName;
        $this->dataKeyName  = $dataKeyName;
    }

    public function field(Field $field, ?\Closure $resolve = null, ?\Closure $fill = null, ?string $computedAttribute = null): Field
    {
        if (is_a($field, 'NovaFlexibleContent\Flexible', true)) {
            return $this->flexibleField($field);
        }
        $field
            ->resolveUsing(function ($value, $model, $attribute) use ($resolve, $computedAttribute) {
                $meta = $model->{$this->relationship}()->where('key', $computedAttribute ?? $attribute)->first();
                if ($meta) {
                    if ($resolve) {
                        return call_user_func($resolve, $meta, $value, $model, $attribute);
                    }
                    $decoded = json_decode($meta->{$this->dataKeyName}, true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }

                    return $meta->{$this->dataKeyName};
                }

                return null;
            })
            ->fillUsing(function ($request, $model, $attribute, $requestAttribute) use ($fill, $computedAttribute) {
                $meta = $model->fresh()->{$this->relationship}()->firstOrNew(
                    [$this->keyName => $computedAttribute ?? $attribute],
                    [$this->dataKeyName => null]
                );

                if ($fill) {
                    $value = $fill($meta, $request->$requestAttribute, $request, $model, $attribute, $requestAttribute);
                    $meta->fill([
                        $this->dataKeyName => is_string($value) ? $value : json_encode($value),
                    ]);
                } else {
                    $meta->fill([
                        $this->dataKeyName => $request->$requestAttribute,
                    ]);
                }

                $meta->save();
            });

        return $field;
    }

    public function flexibleField($field)
    {
        if (!class_exists('NovaFlexibleContent\Flexible')) {
            throw new \Exception('Please install nova-flexible-content package');
        }
        if (!is_a($field, 'NovaFlexibleContent\Flexible', true)) {
            throw new \Exception('Filed should be instance of NovaFlexibleContent\Flexible');
        }
        $field->setResolver(new \NovaThinKit\Nova\Flexible\Resolvers\MetaTableResolver($this->relationship, $this->keyName, $this->dataKeyName));

        return $field;
    }
}
