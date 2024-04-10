<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'variant_id',
        'import_id',
        'amount',
        'unit_price',
        'note',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
