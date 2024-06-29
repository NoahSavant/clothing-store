<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Models\Collection;
use App\Models\CollectionProduct;
use App\Models\Product;
use DB;

class CollectionService extends BaseService
{
    public function __construct(Collection $collection)
    {
        $this->model = $collection;
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $getAll = $input['all'] ?? false;

        $query = $this->model->search($search);
        $data = $this->getAll($input, $query, $getAll);

        return $data;
    }

    public function create($data)
    {
        if ($this->isExisted($data['name'])) {
            return [
                'errorMessage' => 'This name is already taken'
            ];
        }

        $image_url = "https://example.com/default_image.jpg"; // Default image URL

        if ($data->hasFile('image')) {
            $result = $this->uploadFile($data->file('image'), 'collection_' . $data->get('name'), FileCategory::COLLECTION);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $image_url = $result['data']['url'];
        }

        $result = parent::create(array_merge($data->all(), [
            'image_url' => $image_url
        ]));

        return [
            'errorMessage' => $result ? null : 'Failed to create collection',
            'data' => $result
        ];
    }

    public function update($id, $data)
    {
        $collection = $this->getFirst($id);
        if (!$collection) {
            return [
                'errorMessage' => 'Collection not found'
            ];
        }

        if ($this->isExisted($data['name'], $id)) {
            return [
                'errorMessage' => 'This name is already taken'
            ];
        }

        $image_url = $collection->image_url;
        if ($data->hasFile('image')) {
            $result = $this->uploadFile($data->file('image'), 'collection_' . $data->get('name'), FileCategory::COLLECTION);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $image_url = $result['data']['url'];
        }

        $updateData = [
            'name' => $data['name'],
            'image_url' => $image_url
        ];

        $result = parent::update([$id], $updateData);

        return [
            'errorMessage' => $result ? null : 'Failed to update collection',
            'data' => $result ? $collection : null
        ];
    }

    public function updateProducts($id, $data)
    {
        $collection = $this->getFirst($id);

        if (!$collection) {
            return [
                'errorMessage' => 'Collection not found'
            ];
        }

        $productIds = $data['productIds'];
        $type = $data['type'];
        $currentProductIds = $this->getCollections($collection->products);

        if ($type === 'add') {
            $addProductIds = array_diff($productIds, $currentProductIds);
            foreach ($addProductIds as $productId) {
                CollectionProduct::create([
                    'collection_id' => $id,
                    'product_id' => $productId
                ]);
            }
        } elseif ($type === 'remove') {
            
            $removeProductIds = array_intersect($productIds, $currentProductIds);
            return $collection->products;
                
            if (!empty($removeProductIds)) {
                // Use a transaction for data consistency
                DB::beginTransaction();
                try {
                    CollectionProduct::where('collection_id', $id)
                        ->whereIn('product_id', $removeProductIds)
                        ->delete();

                    DB::commit();
                } catch (\Exception $e) {
                    // Rollback transaction on error
                    DB::rollback();
                    return [
                        'errorMessage' => 'Failed to remove products from collection'
                    ];
                }
            }
        }

        return true;
    }


    public function getSingle($id, $request)
    {
        $collection = $this->model->whereId($id)->first();

        return $collection;
    }

    public function delete($ids)
    {
        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Failed to delete collection'
            ];
        }

        return $result;
    }

    public function isExisted($name, $id = null)
    {
        if ($id) {
            return $this->model->where('name', $name)->where('id', '!=', $id)->exists();
        }
        return $this->model->where('name', $name)->exists();
    }
}
