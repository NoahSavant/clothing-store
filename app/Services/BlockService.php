<?php

namespace App\Services;
use App\Models\Block;

class BlockService extends BaseService
{
    protected $variableService;

    public function __construct(Block $block, VariableService $variableService)
    {
        $this->model = $block;
        $this->variableService = $variableService;
    }

    public function get($input)
    {
        $search = $input['$search'] ?? '';
        $query = $this->model->whereNull('block_id')->search($search);
        $data = $this->getAll($input, $query);

        return $data;
    }

    public function detail($block_id) {
        $block = $this->model->where('id', $block_id)->first();

        if (!$block) {
            return [
                'errorMessage' => 'Block not found'
            ];
        }

        $variables = $block->variables(); 
    }

    public function create($data) {
        if($this->isExisted($data['name'])) {
            return [
                'errorMessage' => 'This name is existed'
            ];
        }

        $result = parent::create(array_merge($data, [
            'user_id' => auth()->user()->id
        ]));

        if($result and $data['default']) {
            $this->updateDefaultAddress($result->id);
        }

        return [
            'errorMessage' => $result ? null : 'Create address fail',
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

        if(isset($data['default']) and $data['default']) {
            unset($data['default']);
            $this->updateDefaultAddress(end($ids));
        }

        $result = parent::update($ids, $data);

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
