<?php


namespace NovaThinKit\FeatureImage\Models;

use Illuminate\Support\Str;
use NovaThinKit\FeatureImage\FeatureImageManager;
use NovaThinKit\FeatureImage\ImageManager;

trait HasFeatureImage
{
    protected ?ImageManager $featureImageManager = null;

    protected function createFeatureImageManager(?string $tag = null): ImageManager
    {
        if (!$this->featureImageManager) {
            $featureImageManagerConfig = property_exists($this, 'featureImageManagerConfig') ? $this->featureImageManagerConfig : 'nova-thinkit.feature-images.default';
            $this->featureImageManager = FeatureImageManager::fromConfig(config($featureImageManagerConfig));
        }

        return $this->featureImageManager;
    }

    public function featureImageManager(?string $tag = null): ImageManager
    {
        return $this->createFeatureImageManager($tag)
            ->setTag($tag)
            ->setModel($this);
    }


    public function featureImageManagerDirectory(?string $tag = null): string
    {
        $class = $this->getMorphClass();

        $path = Str::contains($class, '\\') ? class_basename($class) : $class;

        return base64_encode($path . '-' . $this->getKey());
    }

    public function featureImageKey(?string $tag = null): string
    {
        return $tag ?: 'image';
    }
}
