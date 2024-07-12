<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Constants\ProductConstants\ProductStatus;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\UsedTag;
use App\Models\User;
use App\Models\Variant;
use Psy\Readline\Hoa\Console;

class CartItemService extends BaseService
{
    public function __construct(CartItem $cartItem)
    {
        $this->model = $cartItem;
    }

    public function get($input)
    {
        $user = auth()->user();
        $all = $input['all'] ?? false;
        $query = $this->model->withProductInfo($user->id);
        $data = $this->getAll($input, $query, $all);
        return $data;
    }

    public function create($data) {
        $user = auth()->user();

        if(!$user) {
            return [
                'errorMessage' => 'Bạn cần đăng nhập để dùng giỏ hàng',
            ];
        }

        $variant = Variant::where('id', $data['variant_id'])->where('status', ProductStatus::OPEN)->first();

        if (!$variant) {
            return [
                'errorMessage' => 'Không tìm thấy sản phẩm',
            ];
        }

        $cartItem = $this->model->where('user_id', $user->id)->where('variant_id', $data['variant_id'])->first();

        if(!$cartItem) {
            if($variant->stock_limit && $variant->stock < $data['amount']) {
                return [
                    'errorMessage' => 'Sản phẩm vượt quá số lượng hiện có',
                ];
            }

            $result = parent::create([
                'user_id' => $user->id,
                'variant_id' => $data['variant_id'],
                'amount' => $data['amount']
            ]);

            if(!$result) {
                return [
                    'errorMessage' => 'Thêm sản phẩm vào giỏ hàng không thành công',
                ];
            }
        } else {
            if ($variant->stock_limit && $variant->stock < $data['amount'] + $cartItem->amount) {
                return [
                    'errorMessage' => 'Sản phẩm vượt quá số lượng hiện có',
                ];
            }

            $result = parent::update([$cartItem->id] ,[
                'amount' => (int)$data['amount'] + (int)$cartItem->amount
            ]);

            if (!$result) {
                return [
                    'errorMessage' => 'Thêm sản phẩm vào giỏ hàng không thành công',
                ];
            }
        }

        return [
            'successMessage' => 'Thêm sản phẩm vào giả hàng thành công',
            'data' => $result
        ];
    }

    public function update($id, $data) {
        $cartItem = $this->getFirst($id);

        if (!$cartItem) {
            return [
                'errorMessage' => 'Không tìm thấy sản phẩm trong giỏ hàng',
            ];
        }

        $result = parent::update([$id], [
                'amount' => (int) $data['amount']
            ]);

        if (!$result) {
            return [
                'errorMessage' => 'Cập nhật sản phẩm trong giỏ hàng không thành công',
            ];
        }

        return [
            'successMessage' => 'Cập nhật sản phẩm trong giỏ hàng thành công',
            'data' => $result
        ];
    }

    public function delete($ids)
    {
        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Xóa sản phẩm khỏi giỏ hàng thất bại'
            ];
        }

        return $result;
    }
}
