<?php

namespace NovaThinKit\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Nova;

class DynamicBooleanFilter extends BooleanFilter
{
    protected string $fieldName;

    protected array $optionsData;

    /**
     * Filter not current entity but relation.
     *
     * @var string|null
     */
    protected ?string $forRelation = null;

    public function __construct(
        array   $optionsData,
        string  $fieldName = 'type',
        ?string $name = null,
    ) {
        $this->optionsData = $optionsData;
        $this->fieldName   = $fieldName;
        $this->name        = $name ?: Nova::humanize($fieldName);
    }

    public function apply(Request $request, $query, $value)
    {
        $values = array_keys(array_filter($value));
        if (count($values) > 0) {
            if ($this->forRelation) {
                return $query->whereHas(
                    $this->forRelation,
                    fn (Builder $q) => $q->whereIn($this->fieldName, $values)
                );
            }

            return $query->whereIn($this->fieldName, $values);
        }
    }

    public function options(Request $request)
    {
        return $this->optionsData;
    }

    public function key(): string
    {
        $key = parent::key();

        return Str::kebab("{$key}-{$this->fieldName}-{$this->name()}");
    }

    public function forRelation(?string $forRelation = null): static
    {
        $this->forRelation = $forRelation;

        return $this;
    }
}
