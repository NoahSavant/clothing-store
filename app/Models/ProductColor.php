<?php

namespace App\Models;

use App\Traits\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductColor extends Model
{
    use HasFactory, SoftDeletes, BaseModel;

    protected $fillable = [
        'product_id',
        'color',
        'image_url',
    ];

    public function scopeSearch($query, $search, $productId)
    {
        $query->where('product_id', $productId);

        if ($search === '') {
            return $query;
        }

        $keywords = explode(',', $search);

        $query->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keywordWithoutAccent = $this->removeAccents(mb_strtolower(trim($keyword)));
                $query->orWhere(function ($query) use ($keywordWithoutAccent) {
                    $query->whereRaw('LOWER(UNACCENT(color)) LIKE ?', ["%$keywordWithoutAccent%"]);
                });
            }
        });

        return $query;
    }
}