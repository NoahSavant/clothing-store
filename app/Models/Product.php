<?php

namespace App\Models;

use App\Traits\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        return $this->morphToMany(Tag::class, 'tagmorph', 'used_tags');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function scopeSearch($query, $search, $tags = [], $status = null, $collectionIds = [])
    {
        // Eager load relationships to avoid N+1 query problem
        $query->with(['tags', 'brand', 'category', 'comments', 'files', 'variants', 'collectionProducts']);

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

        if (!empty($tags)) {
            $query->whereHas('tags', function ($query) use ($tags) {
                $query->whereIn('tags.id', $tags)
                    ->where('tagmorph_type', self::class); // Ensure correct polymorphic type
            });
        }

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        if (!empty($collectionIds)) {
            $query->whereHas('collectionProducts', function ($query) use ($collectionIds) {
                $query->whereIn('collection_id', $collectionIds);
            });
        }

        $query->withAverageRate();

        return $query;
    }

    public function scopeWithAverageRate($query)
    {
        $query->select('products.*')
            ->selectRaw('COALESCE(AVG(rates.value), 0) as average_rate')
            ->leftJoin('rates', function ($join) {
                $join->on('products.id', '=', 'rates.ratemorph_id')
                    ->where('rates.ratemorph_type', self::class);
            })
            ->groupBy('products.id');

        return $query;
    }
}
