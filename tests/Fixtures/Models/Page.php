<?php

namespace NovaThinKit\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NovaThinKit\FeatureImage\FeatureImageManager;
use NovaThinKit\FeatureImage\ImageManager;
use NovaThinKit\Tests\Fixtures\Factories\PageFactory;

class Page extends Model implements \NovaThinKit\FeatureImage\Models\WithFeatureImage
{
    use \NovaThinKit\FeatureImage\Models\HasFeatureImage;
    use HasFactory;

    protected $guarded = [];

    // Optionally you can change default storage directory
    public function featureImageManagerDirectory(): string
    {
        return 'foo-page/' . $this->getKey();
    }


    public function featureImageKey(?string $tag = null)
    {
        if ($tag === 'fooBar') {
            return 'baz_image';
        }

        return 'image';
    }

    protected function createFeatureImageManager(?string $tag = null): ImageManager
    {
        if (!$this->featureImageManager) {
            $this->featureImageManager = FeatureImageManager::fromConfig([
                'disk'                 => 'feature-images',
                'immutableExtensions'  => [ '.svg', '.gif' ],
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
                'column' => 'baz_image',
            ]);
        }

        if ($tag === 'fooBar') {
            $this->featureImageManager->disk = 'baz';
        }

        return $this->featureImageManager;
    }

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }
}
