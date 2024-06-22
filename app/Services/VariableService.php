<?php

namespace App\Services;
use App\Constants\UtilConstants\DataTypeConstant;
use App\Constants\VariableConstants\VariableParentType;
use App\Constants\VariableConstants\VariableType;
use App\Models\Block;
use App\Models\Variable;

class VariableService extends BaseService
{
    public $parentVariables = [VariableType::OBJECT, VariableType::REPEATER, VariableType::SELECT];

    public function __construct(Variable $variable)
    {
        $this->model = $variable;
    }

    public function create($data)
    {
        $isParentExist = false;
        if ($data['parent_type'] == VariableParentType::BLOCK) {
            $isParentExist = $this->isBlockParentExist($data['parent_id']);
        } else {
            $isParentExist = $this->isVariableParentExist($data['parent_id']);
        }

        if (!$isParentExist) {
            return [
                'errorMessage' => 'Not found parent id with this type' 
            ];
        }

        $result = parent::create(array_merge($data, [
            'variablemorph_id' => $data['parent_id'],
            'variablemorph_type' => $data['parent_type'] == VariableParentType::BLOCK ? Block::class : Variable::class
        ]));

        return [
            'errorMessage' => $result ? null : 'Create variable fail',
            'data' => $result
        ];
    }

    public function makeCopyVariables($variable, $parentId, $parentType=VariableParentType::VARIABLE) {
        $newVariable = $this->model->create([
            "key" => $variable->key,
            "value" => $variable->value,
            "type" => $variable->type,
            'variablemorph_id' => $parentId,
            'variablemorph_type' => $parentType == VariableParentType::BLOCK ? Block::class : Variable::class
        ]);

        if (!$newVariable) {
            return [
                'errorMessage' => 'Create variable fail'
            ];
        }

        if (in_array($variable->type, $this->parentVariables)) {
            foreach($variable->variables as $item) {
                $this->makeCopyVariables($item, $newVariable->id);
            }
        }
    }

    public function isBlockParentExist($blockId) {
        return Block::where('id', $blockId)->exists();
    }

    public function isVariableParentExist($variableId)
    {
        return Variable::where('id', $variableId)->whereIn('type', $this->parentVariables)->exists();
    }

    public function delete($ids)
    {
        $result = parent::delete($ids);

        if (!$result) {
            return [
                'errorMessage' => 'Delete variable fail'
            ];
        }

        return $result;
    }

    public function isVariableUpdated($variable, $data) {
        return $variable->key == $data['key'] or $variable->value == $data['value'] or $variable->type == $data['type'];
    }

    public function isVariableDataValid($data) {
        return isset($data['key']) && isset($data['value']) && isset($data['type']);
    }

    public function updateVariables($data, $parentId, $parentType=VariableParentType::VARIABLE)
    {
        if(!$this->isVariableDataValid($data)) {
            return [
                'errorMessage' => 'Invalid variable data'
            ];
        }

        $variableId = null;

        if(isset($data['id'])) {
            $variable = $this->model->where('id', $data['id'])->first();

            if(!$variable) {
                return [
                    'errorMessage' => 'Variable not found'
                ];
            }

            if ($this->isVariableUpdated($variable, $data)) {
                $this->model
                    ->where('id', $data['id'])
                    ->update([
                        'key' => $data['key'],
                        'value' => $data['value'],
                        'type' => $data['type']
                    ]);
            }

            if (in_array($data['type'], $this->parentVariables)) {
                $inputVariables = $data['variables'] ?? [];
                $currentVariables = $variable->variables;

                $deleteVariableIds = array_diff($this->getCollections($currentVariables), $this->getCollections($inputVariables, 'id', DataTypeConstant::ARRAYS));
                $this->delete($deleteVariableIds);
            }

            $variableId = $data['id'];
        } else {
            $variable = $this->create([
                'key' => $data['key'],
                'value' => $data['value'],
                'type' => $data['type'],
                'parent_id' => $parentId,
                'parent_type' => $parentType,
            ])['data'];

            if (!$variable) {
                return [
                    'errorMessage' => 'Variable create fails'
                ];
            }

            $variableId = $variable->id;
        }

        if (in_array($data['type'], $this->parentVariables) and $data['variables'] != null) {
            foreach ($data['variables'] as $variableData) {
                $this->updateVariables($variableData, $variableId);
            }
        }
    }
}
