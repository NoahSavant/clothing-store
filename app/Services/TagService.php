<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Constants\TagConstants\TagParent;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Http\Resources\TagResource;
use App\Models\Address;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\UsedTag;
use App\Models\User;

class TagService extends BaseService
{
    public function __construct(Tag $tag)
    {
        $this->model = $tag;
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $getAll = $input['all'] ?? false;

        $query = $this->model->search($search);
        $data = $this->getAll($input, $query, $getAll);

        return $data;
    }

    public function create($data) {
        if(isset($data['id'])) {
            $morphType = TagParent::getTagParent($data['parent_type']);
            if ($this->isRelationshipExisted($data['id'], $morphType, $data['parent_id'])) {
                return [
                    'errorMessage' => 'This tag has been attached'
                ];
            }

            $result = UsedTag::create([
                'tag_id' => $data['id'],
                'tagmorph_id' => $data['parent_id'],
                'tagmorph_type' => $morphType
            ]);

            return [
                'errorMessage' => $result ? null : 'Connect tag fail',
                'data' => $result
            ];
        }

        if($this->isExisted($data['name'])) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $result = parent::create($data->all());

        return [
            'errorMessage' => $result ? null : 'Create tag fail',
            'data' => $result
        ];
    }

    public function update($id, $data) {
        if ($this->isExisted($data['name'], $id)) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $result = parent::update([$id], [
            'name' => $data['name'],
            'color' => $data['color']
        ]);

        if(!$result) {
            return [
                'errorMessage' => 'Update tag fail'
            ];
        }

        return $result;
    }

    public function delete($ids)
    {
        $tags = $this->model->whereIn('id', $ids)->get();
        $usedTagIds = [];
        foreach($tags as $tag) {
            array_merge($usedTagIds, $this->getCollections($tag->usedTags));
        }

        $result = empty($usedTagIds) ? true : $this->deleteRelationShip($usedTagIds);

        if (isset($result['errorMessage'])) {
            return $result;
        }

        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete tag(s) fail'
            ];
        }

        return $result;
    }

    public function deleteRelationShip($ids) {
        $result = UsedTag::destroy($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete tag(s) attach fail'
            ];
        }

        return $result;
    }

    public function isExisted($name, $id=null)
    {
        if($id) {
            return $this->model->where('name', $name)->whereNot('id', $id)->exists();
        }
        return $this->model->where('name', $name)->exists();
    }

    public function isRelationshipExisted($tagId, $parentType, $parentId)
    {
        return UsedTag::where('id', $tagId)->where('tagmorph_type', TagParent::getTagParent($parentType))->where('tagmorph_id', $parentId)->exists();
    }
    
}
