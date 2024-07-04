<?php

namespace App\Models;

use App\Constants\UserConstants\UserRole;
use App\Traits\BaseModel;
use Auth;
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

    protected $appends = ['average_rate', 'original_price', 'price', 'stock_limit'];

    public function collectionProducts(): HasMany
    {
        return $this->hasMany(CollectionProduct::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_products')->whereNull('collection_products.deleted_at');
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
        return $this->morphToMany(Tag::class, 'tagmorph', 'used_tags')->whereNull('used_tags.deleted_at');
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

    public function getStockLimitAttribute()
    {
        return $this->variants()->first()->stock_limit ?? false;
    }

    public function scopeSearch($query, $search, $tags = [], $status = null, $collectionIds = [], $minPrice = null, $maxPrice = null, $excludeCollectionId = null)
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

        if (!is_null($excludeCollectionId)) {
            $query->whereDoesntHave('collections', function ($query) use ($excludeCollectionId) {
                $query->where('collection_id', $excludeCollectionId);
            });
        }

        if (!is_null($minPrice) || !is_null($maxPrice)) {
            $query->whereHas('variants', function ($query) use ($minPrice, $maxPrice) {
                if (!is_null($minPrice)) {
                    $query->where('price', '>=', $minPrice);
                }
                if (!is_null($maxPrice)) {
                    $query->where('price', '<=', $maxPrice);
                }
            });
        }

        $query->withMark();

        if ($search === '') {
            return $query;
        }

        $keywords = explode(',', $search);

        $query->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keywordWithoutAccent = $this->removeAccents(mb_strtolower(trim($keyword)));
                $query->orWhere(function ($query) use ($keywordWithoutAccent) {
                    $query->whereRaw('LOWER(UNACCENT(name)) LIKE ?', ["%$keywordWithoutAccent%"])
                        ->orWhereRaw('unaccent(LOWER(note)) LIKE ?', ["%$keywordWithoutAccent%"])
                        ->orWhereRaw('unaccent(LOWER(short_description)) LIKE ?', ["%$keywordWithoutAccent%"]);
                });
            }
        });

        return $query;
    }

    public function scopeSingleProduct($query, $id)
    {
        $query->withMark();
        return $query->with([
            'tags',
            'category'
        ])->where('id', $id);
    }

    public function scopeWithMark($query)
    {
        if (!(Auth::check() && Auth::user()->role === UserRole::CUSTOMER)) {
           return $query;
        }

        $userId = Auth::user()->id;
        return $query->addSelect([
            'is_marked' => function ($query) use ($userId) {
                $query->selectRaw('CASE WHEN EXISTS (
                    SELECT 1 FROM marks
                    WHERE marks.product_id = products.id
                    AND marks.user_id = ?
                ) THEN 1 ELSE 0 END', [$userId]);
            }
        ]);
    }
}
