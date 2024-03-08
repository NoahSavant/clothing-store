<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'url',
        'type',
        'category',
        'filemorph_id',
        'filemorph_type',
    ];

    public function filemorph()
    {
        return $this->morphTo();
    }
}
