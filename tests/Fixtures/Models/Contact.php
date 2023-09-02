<?php

namespace NovaThinKit\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use NovaThinKit\Tests\Fixtures\Factories\ContactFactory;

class Contact extends \Illuminate\Foundation\Auth\User implements \NovaThinKit\FeatureImage\Models\WithFeatureImage
{
    use \NovaThinKit\FeatureImage\Models\HasFeatureImage;
    use Notifiable;
    use HasFactory;

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'contact_id', 'id');
    }

    public function meta(): HasMany
    {
        return $this->hasMany(ContactMeta::class, 'contact_id', 'id');
    }

    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }
}
