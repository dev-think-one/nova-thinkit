<?php


namespace NovaThinKit\FeatureImage\Models;

use NovaThinKit\FeatureImage\ImageManager;

/**
 * @implements  \Illuminate\Database\Eloquent\Model
 */
interface WithFeatureImage
{
    public function featureImageManager(?string $tag = null): ImageManager;
}
