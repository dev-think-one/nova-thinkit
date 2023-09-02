<?php

namespace NovaThinKit\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class BelongsToManyFilter extends BooleanFilter
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
            return $query->whereHas(
                $this->relationName,
                fn (Builder $q) => $q->whereKey(array_keys($selected))
            );
        }

        return $query;
    }

    public function options(NovaRequest $request)
    {
        /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation */
        $relation = $request->model()->{$this->relationName}();

        return $relation->getRelated()::query()->get()->pluck($relation->getRelatedKeyName(), $this->titleKeyName)->all();
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
