<?php

namespace App\Services;

use App\Constants\FileConstants\FileCategory;
use App\Constants\UserConstants\UserRole;
use App\Constants\UserConstants\UserStatus;
use App\Models\User;

class UserService extends BaseService
{
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function isEmailExist($email)
    {
        return User::where('email', $email)->where('status', UserStatus::ACTIVE)->exists();
    }

    public function get($input)
    {
        $search = $input['search'] ?? '';
        $role = $input['role'] ?? null;
        $status = $input['status'] ?? null;

        $query = $this->model->search($search, $role, $status);
        $data = $this->getAll($input, $query);
        return $data;
    }

    public function getSingle($id, $request)
    {
        $user = $this->model->singleUser($id)->first();

        return $user;
    }

    public function create($data)
    {
        if ($this->isExisted($data['email'])) {
            return [
                'errorMessage' => 'This email is existed'
            ];
        }

        $image_url = "https://res.cloudinary.com/dvcdmxgyk/image/upload/v1718962708/files/mcouvshn7gcajzyudvqv.jpg";

        if ($data->hasFile('image')) {
            $result = $this->uploadFile($data->file('image'), 'user_' . $data->get('email'), FileCategory::USER);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $image_url = $result['data']['url'];
        }


        $user = parent::create([
            'email' => $data['email'],
            'username' => $data['username'],
            'role' => UserRole::STAFF,
            'status' => $data['status'],
            'image_url' => $image_url,
            'phonenumber' => $data['phonenumber'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'password' => $this->hash($data['password'])
        ]);

        if (!$user) {
            return [
                'errorMessage' => 'Create staff fail',
            ];
        }

        return [
            'successMessage' => 'Create staff successfully',
            'data' => $user
        ];
    }

    public function update($id, $data)
    {
        if ($this->isExisted($data['email'], $id)) {
            return [
                'errorMessage' => 'This email is existed'
            ];
        }

        $updateData = [
            'email' => $data['email'],
            'username' => $data['username'],
            'role' => UserRole::STAFF,
            'status' => $data['status'],
            'phonenumber' => $data['phonenumber'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
        ];

        if(isset($data['password'])) {
            $updateData['password'] = $this->hash($data['password']);
        }

        if ($data->hasFile('image')) {
            $result = $this->uploadFile($data->file('image'), 'user_' . $data->get('email'), FileCategory::USER);

            if (isset($result['errorMessage'])) {
                return $result;
            }

            $updateData['image_url'] = $result['data']['url'];
        }

        $user = parent::update([$id], $updateData);

        if (!$user) {
            return [
                'errorMessage' => 'Update staff fail',
            ];
        }

        return true;
    }

    public function delete($ids)
    {
        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete staff fail'
            ];
        }

        return $result;
    }

    public function isExisted($email, $id = null)
    {
        if ($id) {
            return $this->model->where('email', $email)->whereNot('id', $id)->exists();
        }
        return $this->model->where('email', $email)->exists();
    }
}
