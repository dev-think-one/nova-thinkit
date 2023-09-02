<?php

namespace NovaThinKit\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use NovaThinKit\Tests\Fixtures\Factories\ContactFactory;

class Contact extends \Illuminate\Foundation\Auth\User implements \NovaThinKit\FeatureImage\Models\WithFeatureImage
{
    use \NovaThinKit\FeatureImage\Models\HasFeatureImage;
    use HasFactory;

    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }
}
