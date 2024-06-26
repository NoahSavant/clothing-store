<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\Address;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class ProductService extends BaseService
{
    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $tags = $input['tags'] ?? [];
        $status = $input['status'] ?? null;
        $collections = $input['collections'] ??  [];

        $query = $this->model->search($search, $tags, $status, $collections);
        $data = $this->getAll($input, $query);
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
            $result = $this->uploadFile($data->file('image'), 'category_' . $data->get('name'), FileCategory::CATEGORY);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $image_url =  $result['data']['url'];
        }

        $result = parent::create(array_merge($data->all(), [
            'image_url' => $image_url
        ]));

        return [
            'errorMessage' => $result ? null : 'Create category fail',
            'data' => $result
        ];
    }

    public function update($ids, $data) {
        $invalidIds = $this->invalidItems($ids);

        if(!empty($invalidIds)) {
            return [
                'errorMessage' => 'Not found address id ' . implode(', ', $invalidIds)
            ];
        }

        if (isset ($data['name']) and $data['name']) {
            if ($this->isExisted($data['name'])) {
                return [
                    'errorMessage' => 'This name is existed'
                ];
            }

            if (count($ids) > 1) {
                return [
                    'errorMessage' => 'Can not set the same name for multi address'
                ];
            }
        }

        $result = parent::update($ids, $data);

        if ($result and isset ($data['default']) and $data['default']) {
            unset($data['default']);
            $this->updateDefaultAddress(end($ids));
        }

        if(!$result) {
            return [
                'errorMessage' => 'Update address fail'
            ];
        }

        return $result;
    }

    public function delete($ids)
    {
        $invalidIds = $this->invalidItems($ids);

        if (!empty ($invalidIds)) {
            return [
                'errorMessage' => 'Not found address id ' . implode(', ', $invalidIds)
            ];
        }

        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete address fail'
            ];
        }

        $this->updateDefaultAddress(null);

        return $result;
    }

    public function updateDefaultAddress($id)
    {
        $userId = auth()->user()->id;

        $this->makeTransaction(function () use ($userId, $id) {
            $result = $this->model
                ->where('user_id', $userId)
                ->where('default', true)
                ->update(['default' => false]);

            if($result == 0 and $id == null) {
                $this->model
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->limit(1)
                    ->update(['default' => true]);
            } else {
                $this->model
                    ->where('user_id', $userId)
                    ->where('id', $id)
                    ->update(['default' => true]);
            }

            return true;
        }, function () {
            return false;
        });
    }

    public function isExisted($name) {
        return $this->model->where('name', $name)->exists();
    }

    public function invalidItems($ids) {
        $addresses = $this->getCollections(auth()->user()->addresses);
        return array_diff($ids, $addresses);
    }
}
