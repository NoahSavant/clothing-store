<?php

namespace App\Models;

use App\Constants\UserConstants\UserRole;
use App\Traits\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Blog extends Model
{
    use HasFactory, SoftDeletes, BaseModel;

    protected $fillable = [
        'name',
        'user_id',
        'content',
        'status',
        'image_url',
        'short_description',
    ];

    protected $appends = ['average_rate'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentmorph');
    }

    public function rates()
    {
        return $this->morphMany(Rate::class, 'ratemorph');
    }

    public function tags()
    {
        return $this->morphMany(Tag::class, 'tagmorph')->whereNull('used_tags.deleted_at');
    }

    public function getAverageRateAttribute()
    {
        return $this->rates()->selectRaw('AVG(CAST(value AS FLOAT)) as average_rate')->pluck('average_rate')->first();
    }

    public function scopeSearch($query, $search, $tags = [], $status = null)
    {
        $query->with(['tags']);
        $query->withMark();

        if (!empty($tags)) {
            $query->whereHas('tags', function ($query) use ($tags) {
                $query->whereIn('tags.id', $tags)
                    ->where('tagmorph_type', self::class);
            });
        }

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        if ($search === '') {
            return $query;
        }

        $keywords = explode(',', $search);

        $query->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keywordWithoutAccent = $this->removeAccents(mb_strtolower(trim($keyword)));
                $query->orWhere(function ($query) use ($keywordWithoutAccent) {
                    $query->whereRaw('LOWER(UNACCENT(name)) LIKE ?', ["%$keywordWithoutAccent%"])
                        ->orWhereRaw('unaccent(LOWER(content)) LIKE ?', ["%$keywordWithoutAccent%"])
                        ->orWhereRaw('unaccent(LOWER(short_description)) LIKE ?', ["%$keywordWithoutAccent%"]);
                });
            }
        });

        return $query;
    }

    public function scopeSingleBlog($query, $id)
    {
        $query->withMark();
        return $query->with([
            'tags',
        ])->where('id', $id);
    }

    public function scopeWithMark($query)
    {
        if (!(Auth::check() && Auth::user()->role === UserRole::CUSTOMER)) {
            return $query;
        }

        $userId = Auth::user()->id;
        $morphType = self::class;

        return $query->addSelect([
            'is_marked' => function ($query) use ($userId, $morphType) {
                $query->selectRaw('CASE WHEN EXISTS (
                SELECT 1 FROM marks
                WHERE marks.markmorph_id = blogs.id
                AND marks.markmorph_type = ?
                AND marks.user_id = ?
            ) THEN 1 ELSE 0 END', [$morphType, $userId]);
            }
        ]);
    }
}
