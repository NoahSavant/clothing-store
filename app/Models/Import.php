<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Import extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'note',
    ];

    public function importVariants(): HasMany
    {
        return $this->hasMany(ImportVariant::class);
    }
}
