<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Constants\OrderConstants\OrderPaymentMethod;
use App\Constants\OrderConstants\OrderStatus;
use App\Constants\UserConstants\UserRole;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\UsedTag;
use App\Models\User;
use App\Models\Variant;
use Carbon\Carbon;
use Psy\Readline\Hoa\Console;

class OrderService extends BaseService
{
    public function __construct(Order $order)
    {
        $this->model = $order;
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $status = $input['status'] ?? null;
        $payment_method = $input['payment_method'] ?? null;
        $user_id = $input['user_id'] ?? null;

        $query = $this->model->search($search, $user_id, $status, $payment_method);
        $data = $this->getAll($input, $query);
        return $data;
    }

    public function getSingle($id, $request)
    {
        $user = auth()->user();

        $order = $this->model->where('id', $id)->with(['orderItems', 'user'])->first();

        if (!$user || !$order) {
            return [false];
        }

        if ($user->role == UserRole::CUSTOMER && $order->user_id != $user->id) {
            return [false];
        }

        return $order;
    }

    public function orderCancel($id) {
        $order = $this->getFirst($id);

        $order->status = OrderStatus::CANCEL;
        $order->save();

        $orderItems = $order->orderItems;

        foreach($orderItems as $orderItem) {
            if($orderItem->variant_return) {
                $variant = Variant::where('id', $orderItem->variant_return)->first();
                if($variant) {
                    $variant->stock += $orderItem->amount;
                }
            }
        }
    }

    public function create($data) {
        $user = auth()->user();

        $order = parent::create([
            'user_id' => $user->id,
            'code' => $this->generateUniqueCode(),
            'total_price' => $data['total_price'],
            'transportation_method' => $data['transportation_method'],
            'product_price' => $data['product_price'],
            'transport_price' => $data['transport_price'],
            'paid' => 0,
            'product_discount' => $data['product_discount'],
            'transport_discount' => $data['transport_discount'],
            'payment_method' => $data['payment_method'],
            'status' => $data['payment_method'] == OrderPaymentMethod::BANK ? OrderStatus::PAYING : OrderStatus::PENDING,
            'staff_id' => null,
            'ended_at' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
            'phonenumber' => $data['phonenumber'],
            'address' => $data['address'],
            'note' => $data['note'],
            'address_link' => $data['address_link']
        ]);

        if(!$order) {
            return [
                'errorMessage' => 'Tạo đơn hàng thất bại',
            ];
        }

        foreach($data['orderItems'] as $orderI) {
            $cartItem = CartItem::where('id', $orderI['id'])->first();
            $variant = $cartItem->variant;
            $product = $variant->product;
            $variant_return = null;
            if ($variant->stock_limit) {
                $variant->stock -= $cartItem->amount;
                $variant_return = $variant->id;
                if ($variant->stock < 0) {
                    $variant->stock = 0;
                }

                $variant->save();
            }

            OrderItem::create([
                'order_id' => $order->id,
                'name' => $product->name,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'size' => $variant->product_size->size,
                'color' => $variant->product_color->color,
                'original_price' => $variant->original_price,
                'price' => $variant->price,
                'amount' => $cartItem->amount,
                'image_url' => $variant->product_color->image_url,
                'variant_return' => $variant_return,
                'product_id' => $product->id
            ]);
            
            $cartItem->delete();
        }

        $current_order = $this->model->where('id', $order->id)->with(['orderItems', 'user'])->first();
        $current_order->status = OrderStatus::getContent($current_order->status);
        
        $this->sendMail("Đặt hàng thành công", 'emails.order-detail', [
            'name' => $user->username,
            'order' => $current_order,
        ], $user->email);

        return [
            'successMessage' => 'Tạo đơn hàng thành công',
            'data' => $order
        ];
    }

    public function update($id, $data) {
        $order = $this->getFirst($id);

        if (!$order) {
            return [
                'errorMessage' => 'Không tìm thấy đơn hàng',
            ];
        }

        if ($order->status == OrderStatus::CANCEL || $order->status == OrderStatus::SUCCESS) {
            return [
                'errorMessage' => 'Đơn hàng này đã kết thúc',
            ];
        }

        if($data['status'] == OrderStatus::CANCEL) {
            $this->orderCancel($id);
            return true;
        }

        $order->status = $data['status'];
        $order->paid = $data['paid'];
        $order->save();

        return true;
    }

    public function delete($ids)
    {
        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete order fail'
            ];
        }

        return $result;
    }

    public function isExisted($name, $id=null) {
        if($id) {
            return $this->model->where('name', $name)->whereNot('id', $id)->exists();
        }
        return $this->model->where('name', $name)->exists();
    }
}
