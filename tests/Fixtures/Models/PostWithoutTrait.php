<?php

namespace NovaThinKit\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NovaThinKit\FeatureImage\FeatureImageManager;
use NovaThinKit\FeatureImage\ImageManager;
use NovaThinKit\Tests\Fixtures\Factories\PostFactory;

class PostWithoutTrait extends Model implements \NovaThinKit\FeatureImage\Models\WithFeatureImage
{
    use HasFactory;

    protected $table = 'posts';

    protected ?FeatureImageManager $featureImageManager = null;

    public function featureImageManager(?string $tag = null): ImageManager
    {
        if (!$this->featureImageManager) {
            $this->featureImageManager = FeatureImageManager::fromConfig([
                'disk'                 => 'feature-images',
                'immutableExtensions'  => [ '.svg', '.gif' ],
            ]);
        }

        return $this->featureImageManager->setModel($this);
    }

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }
}
