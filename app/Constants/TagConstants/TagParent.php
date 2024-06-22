<?php

namespace App\Constants\TagConstants;
use App\Models\Product;
use App\Models\Blog;

class TagParent
{
    const PRODUCT = 0;

    const BLOG = 1;

    public static function getTagParent($mime)
    {
        $parent = null;
        switch ($mime) {
            case Product::class:
                $parent = self::PRODUCT;
                break;
            case Blog::class:
                $parent = self::BLOG;
                break;
            case self::PRODUCT:
                $parent = Product::class;
                break;
            case self::BLOG:
                $parent = Blog::class;
                break;
            default:
                return "Unknown";
        }

        return $parent;
    }
}
