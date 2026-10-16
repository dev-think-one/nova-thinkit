## From v1 to v2

Package now uses `think.studio/laravel-simple-image-manager` v4 and [spatie/image v3](https://spatie.be/docs/image/v3),
requires PHP `^8.2` and Laravel 11 or 12 (Nova 4). Laravel 9 and 10 support dropped.

### Published config and custom feature image managers

`\Spatie\Image\Manipulations` class does not exist anymore. Update published `config/nova-thinkit.php` and every
`FeatureImageManager::fromConfig([...])` call: replace `Manipulations::FIT_*` constants with
the `\Spatie\Image\Enums\Fit` enum or its string value:

```php
// before
'fit' => [\Spatie\Image\Manipulations::FIT_CONTAIN, 450, 300],
// after
'fit' => [\Spatie\Image\Enums\Fit::Contain, 450, 300],
// or
'fit' => ['contain', 450, 300],
```

### Image driver

spatie/image v3 uses Imagick by default when it is installed. `FeatureImageManager` always uses GD unless you
set the `imageDriver` key (`gd`, `imagick` or `vips`) in the feature image config.

See also [laravel-simple-image-manager upgrade guide](https://github.com/dev-think-one/laravel-simple-image-manager/blob/main/UPGRADE.md).
