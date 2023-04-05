<?php

namespace NovaThinKit\Nova\ResourceTemplates;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;

abstract class ResourceTemplate
{
    protected static string $name = '';

    protected Resource $resource;

    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    public static function name(): string
    {
        return static::$name ?: Nova::humanize(class_basename(static::class));
    }

    abstract public function fields(NovaRequest $request): array;
}
