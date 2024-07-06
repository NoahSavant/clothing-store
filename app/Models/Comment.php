<?php

namespace App\Models;

use App\Constants\CommentConstants\CommentParent;
use App\Traits\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes, BaseModel;

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

    public function files():MorphMany
    {
        return $this->morphMany(File::class, 'filemorph');
    }

    public function scopeSearch($query, $search, $commentmorph_id, $commentmorph_type, $hide=false)
    {
        $query->with(['user']);

        $parent = CommentParent::getCommentParent($commentmorph_type);

        $query->where('commentmorph_id', $commentmorph_id)
            ->where('commentmorph_type', $parent);

        if ($commentmorph_type != CommentParent::COMMENT) {
            $query->addSelect([
                'rate' => function ($query) {
                    $query->selectRaw('IFNULL((
                SELECT value FROM rates
                WHERE rates.ratemorph_id = commentmorph_id
                AND rates.ratemorph_type = commentmorph_type
                AND rates.user_id = comments.user_id
            ), NULL)');
                }
            ]);
        }

        if(!$hide) {
            $query->where('hide', $hide);
        }

        if ($search === '') {
            return $query;
        }

        $keywords = explode(',', $search);

        $query->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keywordWithoutAccent = $this->removeAccents(mb_strtolower(trim($keyword)));
                $query->orWhere(function ($query) use ($keywordWithoutAccent) {
                    $query->whereRaw('LOWER(UNACCENT(content)) LIKE ?', ["%$keywordWithoutAccent%"]);
                });
            }
        });

        return $query;
    }

    public function scopeSearchWithUser($query, $search, $commentmorph_id, $commentmorph_type, $user_id)
    {
        $query->with(['user']);

        $query->where('user_id', $user_id)->where('commentmorph_id', $commentmorph_id)
            ->where('commentmorph_type', CommentParent::getCommentParent($commentmorph_type));

        if ($commentmorph_type != CommentParent::COMMENT) {
            $query->addSelect([
                'rate' => function ($query) {
                    $query->selectRaw('IFNULL((
                SELECT value FROM rates
                WHERE rates.ratemorph_id = commentmorph_id
                AND rates.ratemorph_type = commentmorph_type
                AND rates.user_id = comments.user_id
            ), NULL)');
                }
            ]);
        }

        if ($search === '') {
            return $query;
        }

        $keywords = explode(',', $search);

        $query->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $keywordWithoutAccent = $this->removeAccents(mb_strtolower(trim($keyword)));
                    $query->orWhere(function ($query) use ($keywordWithoutAccent) {
                        $query->whereRaw('LOWER(UNACCENT(content)) LIKE ?', ["%$keywordWithoutAccent%"]);
                    });
                }
            });

        return $query;
    }

    public function scopeSearchWithOutUser($query, $search, $commentmorph_id, $commentmorph_type, $user_id)
    {
        $query->with(['user']);

        $query->whereNot('user_id', $user_id)->where('commentmorph_id', $commentmorph_id)
            ->where('commentmorph_type', CommentParent::getCommentParent($commentmorph_type));

        if ($commentmorph_type != CommentParent::COMMENT) {
            $query->addSelect([
                'rate' => function ($query) {
                    $query->selectRaw('IFNULL((
                SELECT value FROM rates
                WHERE rates.ratemorph_id = commentmorph_id
                AND rates.ratemorph_type = commentmorph_type
                AND rates.user_id = comments.user_id
            ), NULL)');
                }
            ]);
        }

        if ($search === '') {
            return $query;
        }

        $keywords = explode(',', $search);

        $query->where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keywordWithoutAccent = $this->removeAccents(mb_strtolower(trim($keyword)));
                $query->orWhere(function ($query) use ($keywordWithoutAccent) {
                    $query->whereRaw('LOWER(UNACCENT(content)) LIKE ?', ["%$keywordWithoutAccent%"]);
                });
            }
        });

        return $query;
    }
}
