# Laravel Nova think kit.

![Packagist License](https://img.shields.io/packagist/l/yaroslawww/nova-thinkit?color=%234dc71f)
[![Packagist Version](https://img.shields.io/packagist/v/yaroslawww/nova-thinkit)](https://packagist.org/packages/yaroslawww/nova-thinkit)
[![Total Downloads](https://img.shields.io/packagist/dt/yaroslawww/nova-thinkit)](https://packagist.org/packages/yaroslawww/nova-thinkit)
[![Build Status](https://scrutinizer-ci.com/g/yaroslawww/nova-thinkit/badges/build.png?b=main)](https://scrutinizer-ci.com/g/yaroslawww/nova-thinkit/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/yaroslawww/nova-thinkit/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/yaroslawww/nova-thinkit/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yaroslawww/nova-thinkit/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/yaroslawww/nova-thinkit/?branch=main)

Laravel Nova small kit for quicker development.

## Installation

You can install the package via composer:

```bash
composer require yaroslawww/nova-thinkit

# optional publish configs
php artisan vendor:publish --provider="NovaThinKit\ServiceProvider" --tag="config"
# optional publish translations
php artisan vendor:publish --provider="NovaThinKit\ServiceProvider" --tag="lang"
```

## Usage

### Actions

#### Login to different guard

```php
use NovaThinKit\Nova\Actions\LoginToDifferentGuard;

public function actions(NovaRequest $request)
{
    return [
        (new LoginToDifferentGuard(
            route('dashboard.overview'),
            'owners_web',
            __('Login to owner dashboard'),
            __('Are you sure you want to continue?'),
        ))
            // optional callback how to find correct user
            ->findIdUsing(fn (Contact $model) => Owner::query()->where('contact_id', $model->getKey())->first()?->getKey())
            // other default method actions...
            ->canRun(fn ($request, Contact $model) => $model->role === "owner"),
    ];
}
```

### Filters

#### BelongsTo filter

Filter by related belongsTo relation.

```php
use NovaThinKit\Nova\Filters\BelongsToFilter;

public function filters(NovaRequest $request)
{
    return [
        new BelongsToFilter('type'),
        // or
        (new BelongsToFilter('type'))->setTitleKeyName('title'),
        // or
        (new BelongsToFilter('type'))->setFilterName('Filter by type'),
    ];
}
```

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)






