<?php

namespace App\Models;

use App\Constants\CommentConstants\CommentParent;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'ratemorph_id',
        'ratemorph_type',
        'value'
    ];

    public function ratemorph()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAverageRate($query, $ratemorph_id, $ratemorph_type)
    {
        return $query->where('ratemorph_id', $ratemorph_id)
            ->where('ratemorph_type', CommentParent::getCommentParent($ratemorph_type))
            ->selectRaw('AVG(CAST(value AS FLOAT)) AS average');
    }
}
