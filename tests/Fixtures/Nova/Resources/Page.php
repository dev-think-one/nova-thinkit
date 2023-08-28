<?php

namespace NovaThinKit\Tests\Fixtures\Nova\Resources;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

/**
 * @extends Resource<\NovaThinKit\Tests\Fixtures\Models\Page>
 */
class Page extends Resource
{
    use \NovaThinKit\FeatureImage\Nova\HasFeatureImage;

    public static $model = \NovaThinKit\Tests\Fixtures\Models\Page::class;

    public static $title = 'title';

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Title', 'title'),
            $this->fieldFeatureImage('Image', 'baz_image'),
        ];
    }
}
