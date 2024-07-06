<?php

namespace App\Constants\CommentConstants;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Blog;

class CommentParent
{
    const PRODUCT = 0;

    const BLOG = 1;

    const COMMENT = 2;

    public static function getCommentParent($mime)
    {
        $parent = null;
        switch ($mime) {
            case Product::class:
                $parent = self::PRODUCT;
                break;
            case Blog::class:
                $parent = self::BLOG;
                break;
            case Comment::class:
                $parent = self::COMMENT;
                break;
            case self::PRODUCT:
                $parent = Product::class;
                break;
            case self::BLOG:
                $parent = Blog::class;
                break;
            case self::COMMENT:
                $parent = Comment::class;
                break;
            default:
                return "Unknown";
        }

        return $parent;
    }
}
