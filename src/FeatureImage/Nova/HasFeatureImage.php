<?php

namespace NovaThinKit\FeatureImage\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Image;
use NovaThinKit\FeatureImage\Models\WithFeatureImage;

trait HasFeatureImage
{
    public function fieldFeatureImage(string $title = 'Feature Image', ?string $tag = null): Image
    {
        $model = $this->model();

        $attr = method_exists($model, 'featureImageKey') ? $model->featureImageKey($tag) : 'image';

        return Image::make($title, $attr)
            ->rules(['mimes:jpeg,png', 'max:' . 1024 * 10])
            ->deletable(true)
            ->store(function (Request $request, WithFeatureImage $model, string $attribute) use ($tag) {
                return function () use ($request, $model, $attribute, $tag) {
                    if ($request->hasFile($attribute)) {
                        $model->$attribute = $model
                            ->featureImageManager($tag)
                            ->storeUploaded($request->file($attribute));
                        $model->save();
                    }
                };
            })
            ->delete(function (Request $request, WithFeatureImage $model) use ($attr, $tag) {
                if ($model->featureImageManager($tag)->delete()) {
                    $model->$attr = null;
                }

                return true;
            })
            ->preview(function ($value, $disk, WithFeatureImage $model) use ($tag) {
                return $model->featureImageManager($tag)->url('thumb');
            })
            ->thumbnail(function ($value, $disk, WithFeatureImage $model) use ($tag) {
                return $model->featureImageManager($tag)->url('thumb');
            })
            ->download(function ($request, $model, $disk, $path) use ($tag) {
                return $model->featureImageManager($tag)->download();
            })
            ->help('Image: jpg or png, ~1600x1200px. The script will automatically crop images');
    }
}
