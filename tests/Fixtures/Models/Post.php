<?php

namespace NovaThinKit\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NovaThinKit\Tests\Fixtures\Factories\PostFactory;

class Post extends Model implements \NovaThinKit\FeatureImage\Models\WithFeatureImage
{
    use \NovaThinKit\FeatureImage\Models\HasFeatureImage;
    use HasFactory;

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }
}
