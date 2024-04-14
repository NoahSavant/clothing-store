<?php

namespace App\Services;
use App\Constants\UtilConstants\DataTypeConstant;
use App\Constants\VariableConstants\VariableParentType;
use App\Http\Resources\BlockInformation;
use App\Http\Resources\PageInformation;
use App\Models\Block;
use App\Models\Page;
use App\Models\PageBlock;

class PageService extends BaseService
{
    protected $blockService;

    protected $pageBlockService;

    public function __construct(Page $page, BlockService $blockService, PageBlockService $pageBlockService)
    {
        $this->model = $page;
        $this->blockService = $blockService;
        $this->pageBlockService = $pageBlockService;
    }

    public function get($input)
    {
        $search = $input['$search'] ?? '';
        $query = $this->model->search($search);
        $data = $this->getAll($input, $query);

        return $data;
    }

    public function detail($page_slug) {
        $page = $this->model->where('slug', $page_slug)->first();

        if (!$page) {
            return [
                'errorMessage' => 'page not found'
            ];
        }

        return new PageInformation($page);
    }

    public function create($data) {
        if($this->isExisted($data['name'])) {
            return [
                'errorMessage' => 'This page name is existed'
            ];
        }

        $result = parent::create([
            'name' => $data['name'],
            'slug' => $this->convertToSlug($data['name'])
        ]);

        return [
            'errorMessage' => $result ? null : 'Create page fail',
            'data' => $result
        ];
    }

    public function updatePage($data) {
        $page = $this->model->where('id', $data['id'])->first();

        if (!$page) {
            return [
                'errorMessage' => 'Page not found'
            ];
        }

        if ($this->model->where('slug', $this->convertToSlug($data['name']))->whereNot('id', $data['id'])->exists()) {
            return [
                'errorMessage' => 'This page name is existed'
            ];
        }

        $this->model
            ->where('id', $data['id'])
            ->update([
                'name' => $data['name'],
                'slug' => $this->convertToSlug($data['name']),
                'background' => $data['background'],
                'hide' => $data['hide'],
                'authen' => $data['authen']
            ]);

        $inputBlocks = $data['blocks'] ?? [];
        $currentBlocks = $page->blocks;

        $deleteBlockIds = array_diff($this->getCollections($currentBlocks), $this->getCollections($inputBlocks, 'id', DataTypeConstant::ARRAYS));

        $this->pageBlockService->deletePageBlock($deleteBlockIds, $data['id']);

        if ($data['blocks'] != null) {
            foreach ($data['blocks'] as $blockData) {
                $this->blockService->updateBlock($blockData, $data['id']);
            }
        }

        return $this->detail($this->convertToSlug($data['name']));
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
        return $this->model->where('slug', $this->convertToSlug($name))->exists();
    }
}
