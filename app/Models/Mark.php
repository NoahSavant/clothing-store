<?php

namespace App\Models;

use App\Traits\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mark extends Model
{
    use HasFactory, SoftDeletes, BaseModel;

    protected $fillable = [
        'user_id',
        'markmorph_id',
        'markmorph_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markmorph()
    {
        return $this->morphTo();
    }
}
