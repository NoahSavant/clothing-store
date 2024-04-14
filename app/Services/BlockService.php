<?php

namespace App\Services;
use App\Constants\VariableConstants\VariableParentType;
use App\Http\Resources\BlockInformation;
use App\Models\Block;
use App\Models\Page;
use App\Models\PageBlock;

class BlockService extends BaseService
{
    protected $variableService;

    protected $pageBlockService;

    public function __construct(Block $block, VariableService $variableService, PageBlockService $pageBlockService)
    {
        $this->model = $block;
        $this->variableService = $variableService;
        $this->pageBlockService = $pageBlockService;
    }

    public function get($input)
    {
        $search = $input['$search'] ?? '';
        $query = $this->model->whereNull('block_id')->search($search);
        $data = $this->getAll($input, $query);

        return $data;
    }

    public function detail($block_id) {
        $block = $this->model->where('id', $block_id)->first();

        if (!$block) {
            return [
                'errorMessage' => 'Block not found'
            ];
        }

        return new BlockInformation($block);
    }

    public function createParent($data) {
        if($this->isExisted($data['name'])) {
            return [
                'errorMessage' => 'This block name is existed'
            ];
        }

        $result = parent::create($data);

        return [
            'errorMessage' => $result ? null : 'Create block fail',
            'data' => $result
        ];
    }

    public function createBlock($blockId, $pageId) {
        $parentBlock = $this->model->where('id', $blockId)->first();
        $page = Page::where('id', $pageId)->first();

        if (!$parentBlock) {
            return [
                'errorMessage' => 'Block not found'
            ];
        }

        if (!$page) {
            return [
                'errorMessage' => 'Page not found'
            ];
        }

        $block = $this->model->create([
            'name' => $parentBlock->name,
            'slug' => $parentBlock->slug,
            'block_id' => $blockId
        ]);

        if (!$block) {
            return [
                'errorMessage' => 'Create block fail'
            ];
        }

        foreach($parentBlock->variables as $variable) {
            $this->variableService->makeCopyVariables($variable, $block->id, VariableParentType::BLOCK);
        }

        PageBlock::create([
            'block_id' => $block->id,
            'page_id' => $pageId,
            'index' => $this->pageBlockService->getLastIndex($pageId) + 1
        ]);

        return new BlockInformation($block);
    }

    public function updateBlock($data, $pageId) {
        $parentId = null;

        if (isset($data['id'])) {
            $block = $this->model->where('id', $data['id'])->whereNotNull('block_id')->first();

            if (!$block) {
                return [
                    'errorMessage' => 'Block not found'
                ];
            }

            $this->pageBlockService->updateIndex($block->id, $pageId, $data['index']);

            $parentId = $data['id'];
        } else {
            $block = $this->createBlock($data['block_id'], $pageId);

            if (!$block) {
                return [
                    'errorMessage' => 'Block create fails'
                ];
            }

            $parentId = $block->id;
        }

        if ($data['variables'] != null) {
            foreach ($data['variables'] as $variableData) {
                $this->variableService->updateVariables($variableData, $parentId, VariableParentType::BLOCK);
            }
        }
    }

    public function delete($ids)
    {
        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete block fail'
            ];
        }

        return $result;
    }

    public function isExisted($name)
    {
        return $this->model->where('name', $name)->whereNull('block_id')->exists();
    }
}
