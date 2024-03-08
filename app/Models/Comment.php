<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'commentmorph_id',
        'commentmorph_type',
        'content',
        'hide',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentmorph()
    {
        return $this->morphTo();
    }

    public function files()
    {
        return $this->morphMany(File::class, 'filemorph');
    }

    public function rates()
    {
        return $this->morphMany(Rate::class, 'ratemorph');
    }
}
