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
public function actions(NovaRequest $request)
{
    return [
        (new \NovaThinKit\Nova\Actions\LoginToDifferentGuard(
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

#### Send reset password notification

```php
use NovaThinKit\Nova\Actions\LoginToDifferentGuard;

public function actions(NovaRequest $request)
{
    return [
    ( \NovaThinKit\Nova\Actions\SendResetPasswordEmail::make('contacts', __('Send reset password'), __('Are you sure you want to continue?')))
            // optional callback how to find correct user
            ->findIdUsing(fn (Contact $model) => Owner::query()->where('contact_id', $model->getKey())->first()?->getKey())
            // other default method actions...
            ->canRun(fn ($request, Contact $model) => $model->role === "owner"),
    ];
}
```

### Filters

#### Dynamic Boolean filter

```php
\NovaThinKit\Nova\Filters\DynamicBooleanFilter::make([
    'Active' => 'active',
    'Paused' => 'paused',
]  /* options */, 'status' /* column to filter */, 'Status'  /* title */),

// Useful with HumanReadable enums:
\NovaThinKit\Nova\Filters\DynamicBooleanFilter::make(array_flip(CompanyStatus::options()), 'status', 'Status'),
```

Use filter for relation. For example add filter to Author to filter Posts:

```php
\NovaThinKit\Nova\Filters\DynamicBooleanFilter::make(array_flip(CompanyStatus::options()), 'status', 'Posts Status')->forRelation('posts'),
```

#### Empty or null filed filter

```php
\NovaThinKit\Nova\Filters\EmptyFieldFilter::make('post_title' /* column to filter */, 'Title'  /* title */),
```

Use filter for relation. For example add filter to Author to filter Posts:

```php
\NovaThinKit\Nova\Filters\EmptyFieldFilter::make('posts.post_title', 'Posts Title'),
```

#### BelongsTo filter

Filter by related belongsTo relation.

```php
use NovaThinKit\Nova\Filters\BelongsToFilter;

public function filters(NovaRequest $request)
{
    return [
        // type - this is belongsTo() relation method name
        new BelongsToFilter('type'),
        // or
        (new BelongsToFilter('type'))->setTitleKeyName('title'),
        // or
        (new BelongsToFilter('type'))->setFilterName('Filter by type'),
    ];
}
```

#### BelongsToMany filter

Filter by related belongsToMany relation.

```php
public function filters(NovaRequest $request)
{
    return [
       \NovaThinKit\Nova\Filters\BelongsToManyFilter::make('tags')
                ->setTitleKeyName('name' /* label key name */)
                ->setFilterName('By tag'),
    ];
}
```

### Metadata table

#### MetaFieldUpdater

If you have standalone table what contains meta values. You can use `MetaFieldUpdater`
to quicker update these values from main resource:

```php
public function fields(NovaRequest $request)
{
    $metaFieldUpdater = new \NovaThinKit\Nova\Helpers\MetaFieldUpdater(
        'metaData' /* hasMany relation method name */,
        'key' /* key name in neta table */,
        'value' /* data name in neta table */
    );

    return [
        $metaFieldUpdater->field(
            Select::make('University', 'university')->options(University::options())
        ),
        // ALso works with flexible
        $metaFieldUpdater->field(
            Flexible::make('Ethos list', 'cf-numeric_list_with_team')
                ->limit(20)
                ->useLayout(EthosItemLayout::class),
        ),
    ];
}
```

### Feature image functionality

Add interface and trait to model

```php
class Page extends Model implements \NovaThinKit\FeatureImage\Models\WithFeatureImage
{
    use \NovaThinKit\FeatureImage\Models\HasFeatureImage;
    
    // Optionally you can change default storage directory
    public function featureImageManagerDirectory(): string
    {
        return 'page/' . $this->getKey();
    }
    
    // Optionally you can change default image-manager
    protected function createFeatureImageManager(?string $tag = null): ImageManager
    {
    if (!$this->featureImageManager) {
            $this->featureImageManager = FeatureImageManager::fromConfig([
                'disk'                 => 'feature-images',
                'immutableExtensions' => [ '.svg', '.gif' ],
                'original'             => [
                    'methods' => [
                        'fit'      => [ \Spatie\Image\Manipulations::FIT_CROP, 2800, 1800 ],
                        'optimize' => [],
                    ],
                    'srcset'  => '2800w',
                ],
                'deletedFormats'       => [],
                'formats'              => [
                    'thumb' => [
                        'methods' => [
                            'fit'      => [ \Spatie\Image\Manipulations::FIT_CONTAIN, 450, 300 ],
                            'optimize' => [],
                        ],
                        'srcset'  => '450w',
                    ],
                ],
            ]);
        }
        
        if($tag === 'fooBar') {
            $this->featureImageManager->disk = 'baz';
        }

        return $this->featureImageManager;
    }
}
```

Add trait to resource

```php
class Page extends Resource
{
    use \NovaThinKit\FeatureImage\Nova\HasFeatureImage;
    
    // ... other methods
    
    public function fields(NovaRequest $request)
    {
        return [
            // other fields
            $this->fieldFeatureImage(),
        ];
    }
}
```

### Page templates (Dynamic fields based on template field)

Create template fields wrapper

```php
namespace App\Nova\ResourceTemplates\Pages;

use NovaThinKit\Nova\Helpers\MetaFieldUpdater;
use NovaThinKit\Nova\ResourceTemplates\ResourceTemplate;

class HomePageTemplate extends ResourceTemplate
{
    public function fields(NovaRequest $request): array
    {
        $metaUpdater = new MetaFieldUpdater('meta', 'key', 'value');

        return [
            Text::make('Some Custom text', 'some_custom_text')
                ->hideWhenCreating()
                ->hideFromIndex()
                ->showOnPreview(),
            $metaUpdater->field(
                Text::make('Custom text', 'custom_text')
                    ->hideWhenCreating()
                    ->hideFromIndex()
                    ->showOnPreview(),
            ),
        ];
    }
}
```

Add mapping

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        \NovaThinKit\Nova\ResourceTemplates\TemplateFinder::templatesMap(Page::class, [
            'home'           => HomePageTemplate::class,
            'contact'        => ContactPageTemplate::class,
        ]);
    }
}
```

Finally, update resource

```php
class Page extends Resource
{
    use \NovaThinKit\Nova\ResourceTemplates\HasTemplate;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            ...$this->templateFields($request),
        ];
    }
}
```

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)






