<?php

namespace App\Services;
use App\Constants\VariableConstants\VariableParentType;
use App\Http\Resources\BlockInformation;
use App\Http\Resources\PageInformation;
use App\Models\Block;
use App\Models\Page;
use App\Models\PageBlock;

class PageService extends BaseService
{
    protected $blockService;

    public function __construct(Page $page, BlockService $blockService)
    {
        $this->model = $page;
        $this->blockService = $blockService;
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

    public function isPageUpdated($page, $data)
    {
        return $page->name == $data['name'] or $page->background == $data['background'] or $page->hide == $data['hide'] or $page->authen == $data['authen'];
    }

    public function updatePage($data) {
        $page = $this->model->where('id', $data['id'])->first();

        if (!$page) {
            return [
                'errorMessage' => 'Page not found'
            ];
        }

        if ($this->isPageUpdated($page, $data)) {
            if ($this->model->where('slug', $this->convertToSlug($data['name']))->whereNot('id', $data['id'])->exists()) {
                return [
                    'errorMessage' => 'This page name is existed'
                ];
            }

            $this->model
                ->where('id', $data['id'])
                ->update([
                    'key' => $data['key'],
                    'value' => $data['value'],
                    'type' => $data['type']
                ]);
        }

        $inputBlocks = $data['blocks'] ?? [];
        $currentBlocks = $page->blocks;

        $deleteBlockIds = array_diff($this->getCollections($currentBlocks), $this->getCollections($inputBlocks));

        $this->delete($deleteBlockIds);

        $variableId = $data['id'];

        if (isset($data['id'])) {
            
        } else {
            $variable = $this->create([
                'key' => $data['key'],
                'value' => $data['value'],
                'type' => $data['type'],
                'parent_id' => $parentId,
                'parent_type' => $parentType,
            ]);

            if (!$variable) {
                return [
                    'errorMessage' => 'Variable create fails'
                ];
            }

            $variableId = $variable->id;
        }

        if (in_array($data['type'], $this->parentVariables) and $data['variables'] != null) {
            foreach ($data['variables'] as $variableData) {
                $this->updateVariables($variableData, $variableId);
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
        return $this->model->where('slug', $this->convertToSlug($name))->exists();
    }
}
