<?php

namespace App\Services;

use App\Constants\DiscountConstants\DiscountStatus;
use App\Constants\DiscountConstants\DiscountType;
use App\Constants\FileConstants\FileCategory;
use App\Constants\UserConstants\UserRole;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\AttachDiscount;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use App\Models\UserDiscount;

class DiscountService extends BaseService
{
    public function __construct(Discount $discount)
    {
        $this->model = $discount;
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $getAll = $input['all'] ?? false;
        $subject = $input['subject'] ?? null;

        $user = auth()->user();
        $query = $this->model->search($search, $subject);
        if($user && $user->role == UserRole::CUSTOMER) {
            $query->where('status', DiscountStatus::OPEN)->where('type', DiscountType::PUBLIC);
        }
        $data = $this->getAll($input, $query, $getAll);

        return $data;
    }

    public function create($data) {
        if($this->isExisted($data['name'])) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $image_url = "https://res.cloudinary.com/dvcdmxgyk/image/upload/v1718962708/files/mcouvshn7gcajzyudvqv.jpg";

        if($data->hasFile('image')) {
            $result = $this->uploadFile($data->file('image'), 'category_' . $data->get('name'), FileCategory::DISCOUNT);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $image_url =  $result['data']['url'];
        }

        $result = parent::create(array_merge($data->all(), [
            'image_url' => $image_url
        ]));

        return [
            'errorMessage' => $result ? null : 'Create discount fail',
            'data' => $result
        ];
    }

    public function update($id, $data)
    {
        $discount = $this->getFirst($id);
        if (!$discount) {
            return [
                'errorMessage' => 'Category not found'
            ];
        }

        if ($this->isExisted($data['name'], $id)) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $image_url = $discount->image_url;
        if ($data->hasFile('image')) {
            $result = $this->uploadFile($data->file('image'), 'discount_' . $data->get('name'), FileCategory::CATEGORY);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $image_url = $result['data']['url'];
        }

        $updateData = [
            'name' => $data['name'],
            'type' => $data['type'],
            'subject' => $data['subject'],
            'condition' => $data['condition'],
            'value' => $data['value'],
            'max_price' => $data['max_price'],
            'min_price' => $data['min_price'],
            'status' => $data['status'],
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'],
            'image_url' => $image_url,
        ];

        $result = parent::update([$id], $updateData);

        return [
            'errorMessage' => $result ? null : 'Update category fail',
            'data' => $result ? $discount : null
        ];
    }

    public function delete($ids)
    {
        $discounts = $this->model->whereIn('id', $ids)->get();
        $attachDiscountIds = [];
        foreach ($discounts as $discount) {
            array_merge($attachDiscountIds, $this->getCollections($discount->userDiscounts));
        }

        $result = empty($attachDiscountIds) ? true : $this->deleteRelationShip($attachDiscountIds);

        if (isset($result['errorMessage'])) {
            return $result;
        }

        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete discount(s) fail'
            ];
        }

        return $result;
    }

    public function deleteRelationShip($ids)
    {
        $result = UserDiscount::destroy($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete user discounts fail'
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
