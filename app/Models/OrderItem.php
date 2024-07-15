<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'name',
        'description',
        'short_description',
        'size',
        'color',
        'original_price',
        'price',
        'amount',
        'image_url',
        'variant_return'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
