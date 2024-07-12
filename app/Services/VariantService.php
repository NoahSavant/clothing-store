<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Constants\ProductConstants\ProductStatus;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use DB;

class VariantService extends BaseService
{
    public function __construct(Variant $variant)
    {
        $this->model = $variant;
    }

    public function get($productId, $input)
    {
        $getAll = $input['all'] ?? false;
        $status = $input['status'] ?? null;

        if($status != null) {
            $query = $this->model->where('product_id', $productId)->with(['product_color', 'product_size'])->where('status', $status);
        } else {
            $query = $this->model->where('product_id', $productId)->with(['product_color', 'product_size']);
        }

        $data = $this->getAll($input, $query, $getAll);

        return $data;
    }

    public function createVariant($productId, $data) {
        if($this->isExisted($data)) {
            return [
                'errorMessage' => 'Kích thước và màu đã tồn tại',
            ];
        }

        $result = $this->create([
            'product_id' => $productId,
            'product_size_id' => $data['product_size_id'],
            'product_color_id' => $data['product_color_id'],
            'status' => $data['status'],
            'original_price' => $data['original_price'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'stock_limit' => $data['stock_limit'],
        ]);

        return [
            'errorMessage' => $result ? null : 'Create variant fail',
            'data' => $result
        ];
    }

    public function update($id, $data)
    {
        if ($this->isExisted($data, $id)) {
            return [
                'errorMessage' => 'kích thước và màu đã tồn tại',
            ];
        }

        $updateData = [
            'product_size_id' => $data['product_size_id'],
            'product_color_id' => $data['product_color_id'],
            'status' => $data['status'],
            'original_price' => $data['original_price'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'stock_limit' => $data['stock_limit'],
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
                'errorMessage' => 'Không tìm thấy sản phẩm'
            ];
        }

        try {
            DB::beginTransaction();

            $deleted = parent::delete($ids);

            CartItem::whereIn('variant_id', $ids)->delete();

            if (!$deleted) {
                DB::rollBack();
                return [
                    'errorMessage' => 'Xóa sản phẩm thất bại'
                ];
            }

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'errorMessage' => $e->getMessage()
            ];
        }
    }


    public function isExisted($data, $id = null)
    {
        if ($id) {
            return $this->model->where('product_color_id', $data['product_color_id'])->where('product_size_id', $data['product_size_id'])->whereNot('id', $id)->exists();
        }
        return $this->model->where('product_color_id', $data['product_color_id'])->where('product_size_id', $data['product_size_id'])->exists();
    }
}
