<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key',
        'value',
        'type',
        'variablemorph_id',
        'variablemorph_type',
    ];

    public function variablemorph()
    {
        return $this->morphTo();
    }

    public function variables()
    {
        return $this->morphMany(Variable::class, 'variablemorph');
    }
}
