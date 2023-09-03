<?php

namespace NovaThinKit\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NovaThinKit\Tests\Fixtures\Factories\PageMetaFactory;

class PageMeta extends Model
{
    use HasFactory;

    protected $table = 'pages_meta';

    protected $guarded = [];

    protected static function newFactory(): PageMetaFactory
    {
        return PageMetaFactory::new();
    }
}
