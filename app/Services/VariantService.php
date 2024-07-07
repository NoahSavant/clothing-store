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

    public function get($productId, $input)
    {
        $getAll = $input['all'] ?? false;

        $query = $this->model->where('product_id', $productId)->with(['product_color', 'product_size']);
        $data = $this->getAll($input, $query, $getAll);

        return $data;
    }

    public function createVariant($productId, $data) {
        $result = $this->create([
            'product_id' => $productId,
            'product_size_id' => $data['product_size_id'],
            'product_color_id' => $data['product_color_id'],
            'status' => $data['status'],
            'original_price' => $data['original_price'],
            'price' => $data['price'],
            'stock' => $data['stock'],
        ]);

        return [
            'errorMessage' => $result ? null : 'Create variant fail',
            'data' => $result
        ];
    }

    public function update($id, $data)
    {
        $updateData = [
            'product_size_id' => $data['product_size_id'],
            'product_color_id' => $data['product_color_id'],
            'status' => $data['status'],
            'original_price' => $data['original_price'],
            'price' => $data['price'],
            'stock' => $data['stock']
        ];

        $result = parent::update([$id], $updateData);

        if($result) {
            return $result;
        }

        return [
            'errorMessage' => 'Update variant fail',
        ];
    }

    public function delete($ids)
    {
        if (empty($ids)) {
            return [
                'errorMessage' => 'No variant IDs provided.'
            ];
        }
    
        $firstVariant = $this->getFirst($ids[0]);

        if (!$firstVariant || !$firstVariant->product) {
            return [
                'errorMessage' => 'Variant or product not found.'
            ];
        }

        $variantsCount = Variant::where('product_id', $firstVariant->product_id)->count();

        if ($variantsCount <= count($ids)) {
            return [
                'errorMessage' => 'Cannot delete variant. Product must have at least one variant.'
            ];
        }

        $deleted = parent::delete($ids);

        if (!$deleted) {
            return [
                'errorMessage' => 'Delete variant failed'
            ];
        }

        return true;
    }


    public function isExisted($name, $id = null)
    {
        if ($id) {

            return $this->model->where('name', $name)->whereNot('id', $id)->exists();
        }
        return $this->model->where('name', $name)->exists();
    }
}
