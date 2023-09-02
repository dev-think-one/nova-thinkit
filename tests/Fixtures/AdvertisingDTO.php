<?php

namespace NovaThinKit\Tests\Fixtures;

use NovaThinKit\FeatureImage\FeatureImageManager;
use NovaThinKit\FeatureImage\ImageManager;

class AdvertisingDTO implements \NovaThinKit\FeatureImage\Models\WithFeatureImage
{

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
}
