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

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'filemorph');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'commentmorph_id', 'id')
            ->where('commentmorph_type', Comment::class);
    }

    public function getRateAttribute($value)
    {
        return (float) $value;
    }

    public function scopeSearch($query, $search, $commentmorph_id, $commentmorph_type, $hide = false, $rate = null)
    {
        $query->with(['user', 'replies.user']);
        
        $parent = CommentParent::getCommentParent($commentmorph_type);

        $query->where('commentmorph_id', $commentmorph_id)
            ->where('commentmorph_type', $parent);

        $query->addSelect([
            'rate' => function ($query) {
                $query->selectRaw('COALESCE((
            SELECT CAST(value AS FLOAT) FROM rates
            WHERE rates.ratemorph_id = comments.commentmorph_id
            AND rates.ratemorph_type = comments.commentmorph_type
            AND rates.user_id = comments.user_id
            AND rates.deleted_at IS NULL
            LIMIT 1
        ), 0)');
            }
        ]);

        if (!$hide) {
            $query->where('hide', $hide);
        }

        if ($rate !== null) {
            $query->whereHas('rate', function ($query) use ($rate) {
                $query->where('value', $rate);
            });
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


    public function scopeSearchWithUser($query, $search, $commentmorph_id, $commentmorph_type, $user_id, $rate = null)
    {
        $query->with(['user', 'replies.user']);

        $query->where('user_id', $user_id)
            ->where('commentmorph_id', $commentmorph_id)
            ->where('commentmorph_type', CommentParent::getCommentParent($commentmorph_type));

        if ($rate !== null) {
            $query->whereHas('rate', function ($query) use ($rate) {
                $query->where('value', $rate);
            });
        }

        $query->addSelect([
            'rate' => function ($query) {
                $query->selectRaw('COALESCE((
            SELECT CAST(value AS FLOAT) FROM rates
            WHERE rates.ratemorph_id = comments.commentmorph_id
            AND rates.ratemorph_type = comments.commentmorph_type
            AND rates.user_id = comments.user_id
            AND rates.deleted_at IS NULL
            LIMIT 1
        ), 0)');
            }
        ]);

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


    public function scopeSearchWithOutUser($query, $search, $commentmorph_id, $commentmorph_type, $user_id, $rate = null)
    {
        $query->with(['user', 'replies.user']);

        $query
            ->where('commentmorph_id', $commentmorph_id)
            ->where('commentmorph_type', CommentParent::getCommentParent($commentmorph_type));

        $query->addSelect([
            'rate' => function ($query) {
                $query->selectRaw('COALESCE((
            SELECT CAST(value AS FLOAT) FROM rates
            WHERE rates.ratemorph_id = comments.commentmorph_id
            AND rates.ratemorph_type = comments.commentmorph_type
            AND rates.user_id = comments.user_id
            AND rates.deleted_at IS NULL
            LIMIT 1
        ), 0)');
            }
        ]);

        if ($rate !== null) {
            $query->whereHas('rate', function ($query) use ($rate) {
                $query->where('value', $rate);
            });
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
