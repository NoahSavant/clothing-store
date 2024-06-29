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

        $query = $this->model->where('product_id', $productId);
        $data = $this->getAll($input, $query, $getAll);

        return $data;
    }

    public function createVariant($productId, $data) {
        $image_url = "https://res.cloudinary.com/dvcdmxgyk/image/upload/v1718962708/files/mcouvshn7gcajzyudvqv.jpg";

        if (isset($data['image'])) {
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
        $updateData = [
            'size' => $data['size'],
            'color' => $data['color'],
            'status' => $data['status'],
            'original_price' => $data['original_price'],
            'price' => $data['price'],
            'stock' => $data['stock']
        ];

        if (isset($data['image'])) {
            $result = $this->uploadFile($data['image'], 'variant_' . $data['size'] . '_' . $data['color'], FileCategory::VARIANT);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $updateData['image_url'] = $result['data']['url'];
        }

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
