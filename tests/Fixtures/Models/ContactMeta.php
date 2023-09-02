<?php

namespace NovaThinKit\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NovaThinKit\Tests\Fixtures\Factories\ContactMetaFactory;

class ContactMeta extends Model
{
    use HasFactory;

    protected $table = 'contacts_meta';

    protected $guarded = [];

    protected static function newFactory(): ContactMetaFactory
    {
        return ContactMetaFactory::new();
    }
}
