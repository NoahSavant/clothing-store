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

    public function deletePageBlock($blockIds, $pageId) {
        return $this->model::where('page_id', $pageId)->whereIn('block_id', $blockIds)->delete();
    }

    public function updateIndex($blockId, $pageId, $index) {
        $currentBlock = $this->model::where('page_id', $pageId)->where('block_id', $blockId)->first();
        $swapBlock = $this->model::where('page_id', $pageId)->where('index', $index)->first();
        if(!$currentBlock) {
            return false;
        } elseif ($swapBlock) {
            $swapBlock->index = $currentBlock->index;
            $swapBlock->save(); 
        }

        $currentBlock->index = $index;
        $currentBlock->save();

        return true;
    }
}
