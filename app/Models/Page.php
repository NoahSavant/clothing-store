<?php

namespace App\Models;

use App\Traits\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes, BaseModel;

    protected $fillable = [
        'name',
        'slug',
        'background',
        'hide',
        'authen',
    ];

    public function pageBlocks(): HasMany
    {
        return $this->hasMany(PageBlock::class);
    }

    public function blocks(): HasManyThrough
    {
        return $this->hasManyThrough(
            Block::class, 
            PageBlock::class,
            'page_id', 
            'id', 
            'id', 
            'block_id'
        )->select('blocks.*', 'page_blocks.index as index', 'page_blocks.hide as hide');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function scopeSearch($query, $search)
    {
        if ($search === '') {
            return $query;
        }

        $keywords = explode(',', $search);

        $query->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keywordWithoutAccent = $this->removeAccents(mb_strtolower(trim($keyword)));
                $query->orWhere(function ($query) use ($keywordWithoutAccent) {
                    $query->whereRaw('LOWER(UNACCENT(name)) LIKE ?', ["%$keywordWithoutAccent%"]);
                });
            }
        });

        return $query;
    }
}
