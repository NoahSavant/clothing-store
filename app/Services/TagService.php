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
            $result = UsedTag::create([
                'tag_id' => $data['id'],
                'tagmorph_id' => $data['parent_id'],
                'tagmorph_type' => TagParent::getTagParent($data['parent_type'])
            ]);

            return [
                'errorMessage' => $result ? null : 'Connect tag fail',
                'data' => $result
            ];
        }

        if($this->isExisted($data['name'], $data['parent_type'])) {
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
        if (isset($data['name']) and $data['name']) {
            if ($this->isExisted($data['name'], $data['parent_type'])) {
                return [
                    'errorMessage' => 'This name is existed'
                ];
            }
        }

        $result = parent::update([$id], $data);

        if(!$result) {
            return [
                'errorMessage' => 'Update tag fail'
            ];
        }

        return $result;
    }

    public function delete($ids)
    {
        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete address fail'
            ];
        }

        return $result;
    }

    public function isExisted($name, $parentType) {
        return $this->model->where('name', $name)->where('tagmorph_type', TagParent::getTagParent($parentType))->exists();
    }

    public function invalidItems($ids) {
        $addresses = $this->getCollections(auth()->user()->addresses);
        return array_diff($ids, $addresses);
    }
}
