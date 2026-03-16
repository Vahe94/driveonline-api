<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\SoftDeletes;
use phpDocumentor\Reflection\Types\Boolean;

class Post extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'title',
        'price',
        'payed',
        'status',
        'rejection_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'payed' => 'boolean',
        ];
    }

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

    #[Scope]
    protected function ofStatus(Builder $query, PostStatus $status): void
    {
        $query->where('status', $status->value);
    }

    public function details(): HasOne
    {
        return $this->hasOne(VehicleDetails::class);
    }
}
