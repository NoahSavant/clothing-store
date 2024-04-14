<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'short_description',
        'brand_id',
        'category_id',
        'status',
        'image_url',
        'note',
    ];

    public function collectionProducts(): HasMany
    {
        return $this->hasMany(CollectionProduct::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentmorph');
    }

    public function rates()
    {
        return $this->morphMany(Rate::class, 'ratemorph');
    }

    public function files():MorphMany
    {
        return $this->morphMany(File::class, 'filemorph');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->morphMany(Tag::class, 'tagmorph');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }
}
