<?php

namespace App\Services;
use App\Models\PageBlock;

class PageBlockService extends BaseService
{
    public function __construct(PageBlock $pageBlock)
    {
        $this->model = $pageBlock;
    }

    public function getLastIndex($pageId)
    {
        return $this->model->where('page_id', $pageId)->max('index') ?? 0;
    }
}
