<?php

namespace NovaThinKit\Tests\Fixtures\Nova\Resources;

use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use NovaThinKit\Nova\Actions\LoginToDifferentGuard;
use NovaThinKit\Nova\Actions\SendResetPasswordEmail;

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
        return [
            Email::make('Email', 'email'),
            Password::make('Password'),
        ];
    }

    public function actions(NovaRequest $request)
    {
        return [
            LoginToDifferentGuard::make('http://me.bar', 'web', 'Foo', 'Bar baz'),
            SendResetPasswordEmail::make('contacts', 'Baz', 'Qux quix'),
        ];
    }
}
