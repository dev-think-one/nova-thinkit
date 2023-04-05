<?php


namespace NovaThinKit\FeatureImage\Models;

use Illuminate\Support\Str;
use NovaThinKit\FeatureImage\FeatureImageManager;
use NovaThinKit\FeatureImage\ImageManager;

trait HasFeatureImage
{
    protected ?ImageManager $featureImageManager = null;


    public function featureImageManager(?string $tag = null): ImageManager
    {
        if (!$this->featureImageManager) {
            $featureImageManagerConfig = property_exists($this, 'featureImageManagerConfig') ? $this->featureImageManagerConfig : 'nova-thinkit.feature-images.default';
            $this->featureImageManager = FeatureImageManager::fromConfig(config($featureImageManagerConfig));
        }

        return $this->featureImageManager
            ->setModel($this);
    }

    public function featureImageManagerDirectory(): string
    {
        $class = $this->getMorphClass();
        if (Str::contains($class, '\\')) {
            $path = explode('\\', $class);
            $path = array_pop($path);
        } else {
            $path = $class;
        }

        return base64_encode($path . '-' . $this->getKey());
    }
}
