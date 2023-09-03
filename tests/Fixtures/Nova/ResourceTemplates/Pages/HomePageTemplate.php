<?php

namespace NovaThinKit\Tests\Fixtures\Nova\ResourceTemplates\Pages;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaThinKit\Nova\Helpers\MetaFieldUpdater;
use NovaThinKit\Nova\ResourceTemplates\ResourceTemplate;

class HomePageTemplate extends ResourceTemplate
{
    public function fields(NovaRequest $request): array
    {
        $metaUpdater = new MetaFieldUpdater('meta', 'key', 'value');

        return [
            $metaUpdater->field(
                Text::make('Custom text', 'custom_text')
                    ->hideWhenCreating()
                    ->hideFromIndex()
                    ->showOnPreview(),
            ),
        ];
    }
}
