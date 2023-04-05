<?php

namespace NovaThinKit\Nova\ResourceTemplates;

use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

trait HasTemplate
{
    public function templateKeyName(): string
    {
        return 'template';
    }
    protected function templateFields(NovaRequest $request): array
    {
        return [
            Select::make('Template', $this->templateKeyName())
                ->placeholder('Default')
                ->options(TemplateFinder::templatesNames(static::$model))
                ->displayUsingLabels()
                ->nullable(),
            ...$this->selectedTemplateFields($request),
        ];
    }

    protected function selectedTemplateFields(NovaRequest $request): array
    {
        if ($this->resource->{$this->templateKeyName()}) {
            return TemplateFinder::find($this->resource->{$this->templateKeyName()}, $this)?->fields($request) ?: [];
        }

        return [];
    }
}
