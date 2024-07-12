<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'status',
        'original_price',
        'price',
        'stock',
        'image_url',
        'stock_limit',
        'product_color_id',
        'product_size_id',
    ];

    protected $appends = ['amount'];

    public function getAmountAttribute()
    {
        $user = auth()->user();

        if ($user) {
            $cartItem = $this->cartItems()->where('user_id', $user->id)->first();
            return $cartItem ? $cartItem->amount : 0;
        }

        return 0;
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function importVariants(): HasMany
    {
        return $this->hasMany(ImportVariant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function product_color(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function product_size(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class);
    }

    public function scopeFilterByStatus($query, $status = null)
    {
        if (!is_null($status)) {
            return $query->where('status', $status);
        }
        return $query;
    }
}
