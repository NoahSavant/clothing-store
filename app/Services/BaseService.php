<?php

namespace App\Services;

use App\Constants\AuthenConstants\EncryptionKey;
use App\Constants\UtilConstants\DataTypeConstant;
use App\Constants\UtilConstants\PaginationConstant;
use App\Jobs\SendMailQueue;
use App\Mail\SendMail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Hash;
use DB;
use Illuminate\Database\QueryException;

class BaseService
{
    public $model;

    public function create($data)
    {
        if (! $data) {
            return false;
        }

        return $this->model->create($data);
    }

    public function update($ids, $data)
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }

    public function delete($ids)
    {
        return $this->model->destroy($ids);
    }

    public function getFirst($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function getAll($input, $query = null)
    {
        if (! $query) {
            $query = $this->model->query();
        }
        $limit = $input['limit'] ?? PaginationConstant::LIMIT_RECORD;
        $column = $input['column'] ?? PaginationConstant::COLUMN_DEFAULT;
        $order = $input['order'] ?? PaginationConstant::ORDER_TYPE;

        $data = $query->orderBy($column, $order)->paginate($limit);

        return [
            'items' => $data->items(),
            'pagination' => $this->getPaginationData($data),
        ];
    }

    public function getPaginationData($data)
    {
        $pagination = [
            'perPage' => $data->perPage(),
            'currentPage' => $data->currentPage(),
            'lastPage' => $data->lastPage(),
            'totalRow' => $data->total(),
        ];

        return $pagination;
    }

    public function response($data, $status)
    {
        return response()->json($data, $status);
    }

    public function hash($data)
    {
        return Hash::make($data);
    }

    protected function encryptToken($data)
    {
        $key = Key::loadFromAsciiSafeString(EncryptionKey::REFRESH_KEY);
        $encryptedData = Crypto::encrypt(json_encode($data), $key);

        return $encryptedData;
    }

    protected function decryptToken($encryptedData)
    {
        $key = Key::loadFromAsciiSafeString(EncryptionKey::REFRESH_KEY);
        $decryptedData = Crypto::decrypt($encryptedData, $key);

        return json_decode($decryptedData, true);
    }

    protected function getCollections($data, $column = 'id', $type = DataTypeConstant::COLLECTIONS)
    {
        $items = [];
        if($type == DataTypeConstant::COLLECTIONS) {
            foreach ($data as $item) {
                if (isset($item->$column)) {
                    $items[] = $item->$column;
                }
            }
        } else {
            foreach ($data as $item) {
                if(isset($item[$column])) {
                    $items[] = $item[$column];
                }
            }
        }
        return $items;
    }

    protected function includesAll($firstArray, $secondArray)
    {
        foreach ($firstArray as $item) {
            if (! in_array($item, $secondArray)) {
                return false;
            }
        }

        return true;
    }

    public function getBy($column="id", $value)
    {
        return $this->model->where($column, $value)->first();
    }

    public function sendMail($subject, $view, $data, $email) {
        SendMailQueue::dispatch($email, new SendMail($subject, $view, $data));
    }

    public function makeTransaction(callable $tryFunction, callable $catchFunction) {
        try {
            DB::beginTransaction();
            $result = $tryFunction();
            DB::commit();
            return $result;
        } catch (QueryException $e) {
            DB::rollBack();
            return $catchFunction();
        }
    }

    function convertToSlug($string)
    {
        $slug = Str::slug($string, '-');
        return $slug;
    }
}
