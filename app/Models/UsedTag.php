<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsedTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tag_id',
        'tagmorph_id',
        'tagmorph_type',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function tagmorph()
    {
        return $this->morphTo();
    }
}
