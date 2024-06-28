<?php

namespace App\Models;

use App\Traits\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes, BaseModel;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'condition',
        'value',
        'max_price',
        'code',
        'status',
        'started_at',
        'ended_at',
        'image_url',
    ];

    public function userDiscounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public function attachDiscount(): HasMany {
        return $this->hasMany(AttachDiscount::class);
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
