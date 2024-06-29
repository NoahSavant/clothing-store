<?php

namespace App\Models;

use App\Traits\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, BaseModel;

    protected $fillable = [
        'name',
        'description',
        'short_description',
        'brand_id',
        'category_id',
        'status',
        'first_image_url',
        'second_image_url',
        'note',
    ];

    protected $appends = ['average_rate', 'original_price', 'price'];

    public function collectionProducts(): HasMany
    {
        return $this->hasMany(CollectionProduct::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_products');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentmorph');
    }

    public function rates(): MorphMany
    {
        return $this->morphMany(Rate::class, 'ratemorph');
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
        return $this->morphToMany(Tag::class, 'tagmorph', 'used_tags');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function getAverageRateAttribute()
    {
        return $this->rates()->selectRaw('AVG(CAST(value AS FLOAT)) as average_rate')->pluck('average_rate')->first();
    }

    public function getOriginalPriceAttribute()
    {
        return $this->variants()->first()->original_price ?? null;
    }

    public function getPriceAttribute()
    {
        return $this->variants()->first()->price ?? null;
    }

    public function scopeSearch($query, $search, $tags = [], $status = null, $collectionIds = [])
    {
        $query->with(['tags', 'category', 'collections']);

        if (!empty($tags)) {
            $query->whereHas('tags', function ($query) use ($tags) {
                $query->whereIn('tags.id', $tags)
                    ->where('tagmorph_type', self::class);
            });
        }

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        if (!empty($collectionIds)) {
            $query->whereHas('collections', function ($query) use ($collectionIds) {
                $query->whereIn('collection_id', $collectionIds);
            });
        }

        if ($search === '') {
            return $query;
        }

        $keywords = explode(',', $search);

        $query->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keywordWithoutAccent = $this->removeAccents(mb_strtolower(trim($keyword)));
                $query->orWhere(function ($query) use ($keywordWithoutAccent) {
                    $query->whereRaw('LOWER(UNACCENT(name)) LIKE ?', ["%$keywordWithoutAccent%"])
                        ->orWhereRaw('unaccent(LOWER(note)) LIKE ?', ["%$keywordWithoutAccent%"]);
                });
            }
        });
        
        return $query;
    }

    public function scopeSingleProduct($query, $id)
    {
        return $query->with([
            'tags',
        ])->where('id', $id);
    }
}
