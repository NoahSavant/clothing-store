<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'collection_id',
        'product_id',
    ];

    public function collection(): BelongsTo {
        return $this->belongsTo(Collection::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
