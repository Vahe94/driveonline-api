<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Photo extends Model
{
    protected $fillable = ['url', 'post_id'];
    public $timestamps = false;

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => url('media/' . preg_replace('#/+#', '/', ltrim($value, '/'))),
        );
    }
}
