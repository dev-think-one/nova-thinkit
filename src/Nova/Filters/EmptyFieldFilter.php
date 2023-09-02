<?php

namespace NovaThinKit\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class EmptyFieldFilter extends BooleanFilter
{
    protected string $columnName;

    public function __construct(string $columnName, string $name = null)
    {
        $this->setColumn($columnName);
        if ($name) {
            $this->setName($name);
        }
    }

    public function apply(NovaRequest $request, $query, $value)
    {
        if (($value['empty'] ?? false) && ($value['filled'] ?? false)) {
            return $query;
        }

        $relations  = explode('.', $this->columnName);
        $columnName = array_pop($relations);

        $callback = function ($query) use ($value, $columnName) {
            if ($value['empty']) {
                $query->whereNull($columnName)
                    ->orWhere($columnName, '');
            } elseif ($value['filled']) {
                $query->whereNotNull($columnName)
                    ->where($columnName, '<>', '');
            }
        };

        $this->recursiveCallback($query, $relations, $callback);

        return $query;
    }

    public function options(NovaRequest $request): array
    {
        return [
            trans('nova-thinkit::filter.nullable-field.empty')  => 'empty',
            trans('nova-thinkit::filter.nullable-field.filled') => 'filled',
        ];
    }


    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setColumn(string $columnName): self
    {
        $this->columnName = $columnName;

        return $this;
    }

    protected function recursiveCallback(Builder $query, array $relations, \Closure $callback): void
    {
        if (count($relations)) {
            $relation = array_shift($relations);
            $query->whereHas($relation, fn ($q) => $this->recursiveCallback($q, $relations, $callback))
                ->orDoesntHave($relation);
        } else {
            $callback($query);
        }
    }
}
