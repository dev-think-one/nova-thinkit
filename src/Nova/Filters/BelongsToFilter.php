<?php

namespace NovaThinKit\Nova\Filters;

use Illuminate\Support\Str;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class BelongsToFilter extends BooleanFilter
{
    protected string $relationName;
    protected string $titleKeyName = 'name';

    public function __construct(string $relationName)
    {
        $this->relationName = $relationName;
        $this->name         = Str::ucfirst(Str::snake($this->relationName, ' '));
    }

    public function apply(NovaRequest $request, $query, $value)
    {
        $selected = array_filter($value);
        if (count($selected)) {
            /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo $relation */
            $relation = $query->getModel()->{$this->relationName}();

            return $query->whereIn($relation->getForeignKeyName(), array_keys($selected));
        }

        return $query;
    }

    public function options(NovaRequest $request)
    {
        /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo $relation */
        $relation = $request->model()->{$this->relationName}();

        return $relation->getRelated()::query()->get()->pluck($relation->getOwnerKeyName(), $this->titleKeyName)->all();
    }

    public function setTitleKeyName(string $titleKeyName): static
    {
        $this->titleKeyName = $titleKeyName;

        return $this;
    }

    public function setFilterName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
