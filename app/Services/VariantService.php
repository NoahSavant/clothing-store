<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;

class VariantService extends BaseService
{
    public function __construct(Variant $variant)
    {
        $this->model = $variant;
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $getAll = $input['all'] ?? false;

        $query = $this->model->search($search);
        $data = $this->getAll($input, $query, $getAll);

        return $data;
    }

    public function createVariant($productId, $data) {
        $variantData = [
            'product_id' => $productId,
            'size' => $data['size'],
            'color' => $data['color'],
            'status' => $data['status'],
            'original_price' => $data['original_price'],
            'price' => $data['price'],
            'stock' => $data['stock'],
        ];

        $image_url = "https://res.cloudinary.com/dvcdmxgyk/image/upload/v1718962708/files/mcouvshn7gcajzyudvqv.jpg";

        if (isset($data['image'])) {
            // Assuming you have a function to handle variant image upload
            $result = $this->uploadFile($data['image'], 'variant_' . $data['size'] . '_' . $data['color'], FileCategory::VARIANT);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $image_url = $result['data']['url'];
        }



        $result = $this->create([
            'product_id' => $productId,
            'size' => $data['size'],
            'color' => $data['color'],
            'status' => $data['status'],
            'original_price' => $data['original_price'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'image_url' => $image_url
        ]);

        return [
            'errorMessage' => $result ? null : 'Create variant fail',
            'data' => $result
        ];
    }

    public function update($id, $data)
    {
        $category = $this->getFirst($id);
        if (!$category) {
            return [
                'errorMessage' => 'Category not found'
            ];
        }

        if ($this->isExisted($data['name'], $id)) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $image_url = $category->image_url;
        if ($data->hasFile('image')) {
            $result = $this->uploadFile($data->file('image'), 'category_' . $data->get('name'), FileCategory::CATEGORY);

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
            'errorMessage' => $result ? null : 'Update category fail',
            'data' => $result ? $category : null
        ];
    }

    public function delete($ids)
    {
        Product::whereIn('category_id', $ids)->update(['category_id' => null]);

        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete category failed'
            ];
        }

        return $result;
    }

    public function isExisted($name, $id = null)
    {
        if ($id) {

            return $this->model->where('name', $name)->whereNot('id', $id)->exists();
        }
        return $this->model->where('name', $name)->exists();
    }
}
