<?php

namespace NovaThinKit\Tests\Fixtures\Nova\Resources;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use NovaFlexibleContent\Flexible;
use NovaThinKit\Nova\Actions\LoginToDifferentGuard;
use NovaThinKit\Nova\Actions\SendResetPasswordEmail;
use NovaThinKit\Tests\Fixtures\Models\ContactMeta;
use NovaThinKit\Tests\Fixtures\Nova\Layouts\SimpleNumberLayout;

/**
 * @extends Resource<\NovaThinKit\Tests\Fixtures\Models\Contact>
 */
class Contact extends Resource
{
    use \NovaThinKit\FeatureImage\Nova\HasFeatureImage;

    public static $model = \NovaThinKit\Tests\Fixtures\Models\Contact::class;

    public static $title = 'title';

    public function fields(NovaRequest $request): array
    {
        $metaFieldUpdater = new \NovaThinKit\Nova\Helpers\MetaFieldUpdater('meta', 'key', 'value');

        return [
            Email::make('Email', 'email'),
            Password::make('Password'),
            $metaFieldUpdater->field(
                Text::make('Company position', 'company_position'),
            ),
            $metaFieldUpdater->field(
                Text::make('Price', 'price'),
                fn (ContactMeta $meta, $value, $model, $attribute)                              => round((((int)$meta->value) / 100), 2),
                fn (ContactMeta $meta, $value, $request, $model, $attribute, $requestAttribute) => (int)($value * 100),
                'formatted_price'
            ),
            $metaFieldUpdater->field(Text::make('List', 'list')),
            $metaFieldUpdater->field(Code::make('Code', 'code')),
            $metaFieldUpdater->field(
                Flexible::make('Content', 'content')
                    ->useLayout(SimpleNumberLayout::class)
            ),
        ];
    }

    public function actions(NovaRequest $request)
    {
        return [
            LoginToDifferentGuard::make('http://me.bar', 'contact_web', 'Foo', 'Bar baz'),
            SendResetPasswordEmail::make('contacts', 'Baz', 'Qux quix'),

            LoginToDifferentGuard::make('http://other.bar', 'contact_web')
                ->findIdUsing(function (Model $model) {
                    return $model->getKey();
                }),
        ];
    }
}
