<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Post extends Model
{
    protected $fillable = [
        'title',
        'price',
        'description',
        'user_id',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function mainPhoto(): HasOne
    {
        return $this->hasOne(Photo::class)->orderBy('id');
    }
}
