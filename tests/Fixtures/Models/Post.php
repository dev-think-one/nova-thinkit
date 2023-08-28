<?php

namespace NovaThinKit\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NovaThinKit\Tests\Fixtures\Factories\PostFactory;

class Post extends Model implements \NovaThinKit\FeatureImage\Models\WithFeatureImage
{
    use \NovaThinKit\FeatureImage\Models\HasFeatureImage;
    use HasFactory;

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }
}
