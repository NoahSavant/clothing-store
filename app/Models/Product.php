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

    protected $appends = ['average_rate', 'original_price', 'price', 'stock_limit', 'mark'];

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

    public function marks(): MorphMany
    {
        return $this->morphMany(Mark::class, 'markmorph');
    }

    public function getAverageRateAttribute()
    {
        return $this->rates()->selectRaw('AVG(CAST(value AS FLOAT)) as average_rate')->pluck('average_rate')->first();
    }

    public function getMarkAttribute()
    {
        if (!Auth::check() || Auth::user()->role !== UserRole::CUSTOMER) {
            return 0;
        }

        $userId = Auth::user()->id;
        $morphType = self::class;

        $isMarked = $this->marks()
            ->where('user_id', $userId)
            ->where('markmorph_type', $morphType)
            ->exists();

        return $isMarked ? 1 : 0;
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
        return $this->variants()->first()->stock_limit ?? 0;
    }

    public function scopeSearch($query, $search, $tags = [], $status = null, $collectionIds = [], $category = null, $minPrice = null, $maxPrice = null, $excludeCollectionId = null, $withVariant=null)
    {
        $query->with(['tags', 'category', 'collections']);

        if ($withVariant) {
            $query->whereHas('variants', function ($query) {
                $query->where('status', 0);
            });
        }
        
        if (!empty($tags)) {
            $query->whereHas('tags', function ($query) use ($tags) {
                $query->whereIn('tags.id', $tags)
                    ->where('tagmorph_type', self::class);
            });
        }

        if($category) {
            $query->where('category_id', $category);
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

    public function scopeSingleProduct($query, $id, $related = false)
    {
        $query->with(['tags', 'category']);

        if ($related) {
            $product = $this->findOrFail($id);
            $tagIds = $product->tags->pluck('id')->toArray();
            $categoryId = $product->category_id;

            $query->where(function ($query) use ($tagIds, $categoryId) {
                $query->whereHas('tags', function ($query) use ($tagIds) {
                    $query->whereIn('tags.id', $tagIds);
                })->orWhere('category_id', $categoryId);
            })->where('id', '!=', $id);
        } else {
            $query->where('id', $id);
        }

        return $query;
    }


    public function scopeWithMark($query)
    {
        if (!(Auth::check() && Auth::user()->role === UserRole::CUSTOMER)) {
            return $query;
        }

        $userId = Auth::user()->id;
        $morphType = self::class;

        return $query->addSelect([
            'is_marked' => function ($query) use ($userId, $morphType) {
                $query->selectRaw('CASE WHEN EXISTS (
                SELECT 1 FROM marks
                WHERE marks.markmorph_id = products.id
                AND marks.markmorph_type = ?
                AND marks.user_id = ?
            ) THEN 1 ELSE 0 END', [$morphType, $userId]);
            }
        ]);
    }

    public function scopeRecommendForUser($query, $userId = null, $productId = null)
    {
        // Initialize arrays to hold filtered product IDs
        $collaborativeProductIds = [];
        $contentProductIds = [];

        // Step 1: Get products in user's cart if userId is provided
        if ($userId) {
            $cartProductIds = CartItem::where('user_id', $userId)
                ->with('variant.product')
                ->get()
                ->pluck('variant.product.id')
                ->unique()
                ->toArray();

            // Step 2: Collaborative Filtering based on user's cart
            $similarUserIds = CartItem::whereIn('variant_id', function ($query) use ($cartProductIds) {
                $query->select('id')
                    ->from('variants')
                    ->whereIn('product_id', $cartProductIds);
            })->where('user_id', '!=', $userId)
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $collaborativeProductIds = CartItem::whereIn('user_id', $similarUserIds)
                ->with('variant.product')
                ->get()
                ->pluck('variant.product.id')
                ->unique()
                ->diff($cartProductIds)
                ->toArray();
        } else {
            // Step 3: If userId is not provided, fetch products from other users' carts
            $otherUserCartProductIds = CartItem::where('user_id', '!=', $userId)
                ->with('variant.product')
                ->get()
                ->pluck('variant.product.id')
                ->unique()
                ->toArray();

            $collaborativeProductIds = array_unique($otherUserCartProductIds);
        }

        // Step 4: Content-Based Filtering if productId is provided
        if ($productId) {
            $tags = Tag::whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId);
            })->pluck('id')->toArray();

            // Lấy danh sách các categories của sản phẩm hiện tại
            $categories = Product::where('id', $productId)
                ->pluck('category_id')->toArray();

            // Lấy danh sách các collections của sản phẩm hiện tại
            $collections = Collection::whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId);
            })->pluck('id')->toArray();

            // Tìm các sản phẩm khác có tags, categories, hoặc collections giống với sản phẩm hiện tại
            $contentProductIds = Product::whereNotIn('id', [$productId]) // Loại bỏ sản phẩm hiện tại
                ->where(function ($query) use ($tags, $categories, $collections) {
                    $query->whereIn('category_id', $categories)
                        ->orWhereHas('tags', function ($query) use ($tags) {
                            $query->whereIn('tags.id', $tags);
                        })
                        ->orWhereHas('collections', function ($query) use ($collections) {
                            $query->whereIn('collections.id', $collections);
                        });
                })
                ->pluck('id')
                ->toArray();
        }

        // Step 5: Combine Results and Score Products
        $combinedProductIds = array_unique(array_merge($collaborativeProductIds, $contentProductIds));

        // Bổ sung sản phẩm nếu số lượng sản phẩm hiện tại ít hơn 10
        if (count($combinedProductIds) < 10) {
            $currentCount = count($combinedProductIds);
            $additionalCount = max(10 - $currentCount, ceil(0.2 * $currentCount)); // Bổ sung thêm 20% hoặc đủ 10 sản phẩm

            $additionalProductIds = Product::whereNotIn('id', $combinedProductIds)
                ->inRandomOrder()
                ->take($additionalCount)
                ->pluck('id')
                ->toArray();

            // Loại bỏ các sản phẩm đã có trong kết quả hiện tại
            $additionalProductIds = array_diff($additionalProductIds, $combinedProductIds);

            // Kết hợp sản phẩm bổ sung với danh sách hiện tại
            $combinedProductIds = array_unique(array_merge($combinedProductIds, $additionalProductIds));
        }

        // Lấy 8 sản phẩm ngẫu nhiên từ danh sách cuối cùng
        $randomProductIds = Product::whereIn('id', $combinedProductIds)
            ->inRandomOrder()
            ->take(8)
            ->pluck('id')
            ->toArray();

        // Trả về các sản phẩm đã chọn ngẫu nhiên
        $products = Product::whereIn('id', $randomProductIds)
            ->with(['tags', 'category', 'collections'])
            ->get();

        return $products;
    }
}
