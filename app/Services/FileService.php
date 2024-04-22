<?php

namespace App\Services;

use App\Constants\FileConstants\FileType;
use App\Constants\UserConstants\UserStatus;
use App\Http\Resources\AddressInformation;
use App\Models\File;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FileService extends BaseService
{
    public function __construct(File $file)
    {
        $this->model = $file;
    }

    public function get($input)
    {
        $search = $input['$search'] ?? '';
        $user = auth()->user();
        $query = $this->model->where('user_id', $user->id)->search($search);
        $data = $this->getAll($input, $query);
        $data['items'] = AddressInformation::collection($data['items']);

        return $data;
    }

    public function upload($data) {
        $result = Cloudinary::uploadFile($data->file('file')->getRealPath(), [
            'folder' => 'files',
        ]);

        $url = $result->getSecurePath();

        if(!$url) {
            return [
                'errorMessage' => 'Upload image fail'
            ];
        }

        $name = null;

        if($data->has('name')) {
            if($this->isExisted($data->get('name'))) {
                $name = $data->get('name') . '_' . time();
            } else {
                $name = $data->get('name');
            }
        } else {
            $name = 'file_' . time();
        }

        $result = parent::create(array_merge($data->all(), [
            'name' => $name,
            'url' => $url,
            'type' => FileType::getFileType($data->file('file')->getMimeType())
        ]));

        if(!$result) {
            return [
                'errorMessage' => 'Store file fail'
            ];
        }

        return [
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
