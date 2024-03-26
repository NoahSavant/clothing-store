<?php

namespace App\Services;
use App\Models\Variable;

class VariableService extends BaseService
{
    public function __construct(Variable $variable)
    {
        $this->model = $variable;
    }

    public function delete($ids)
    {
        $invalidIds = $this->invalidItems($ids);

        if (!empty ($invalidIds)) {
            return [
                'errorMessage' => 'Not found block id ' . implode(', ', $invalidIds)
            ];
        }

        $result = parent::delete($ids);
        

        if (!$result) {
            return [
                'errorMessage' => 'Delete block fail'
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
        return $this->model->where('user_id', auth()->user()->id)->where('name', $name)->exists();
    }

    public function invalidItems($ids) {
        $addresses = auth()->user()->addresses();

        return array_diff($ids, $addresses);
    }
}
