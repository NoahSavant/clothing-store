<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'block_id',
        'instant_name',
    ];

    public function block(): BelongsTo {
        return $this->belongsTo(Block::class);
    }

    public function blocks(): HasMany {
        return $this->hasMany(Block::class);
    }

    public function pageBlocks(): HasMany
    {
        return $this->hasMany(PageBlock::class);
    }

    public function variables()
    {
        return $this->morphMany(Variable::class, 'variablemorph');
    }
}
