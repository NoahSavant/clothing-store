<?php

namespace App\Http\Resources;

use App\Constants\VariableConstants\VariableParentType;
use App\Constants\VariableConstants\VariableType;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariableInformation extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'updated_at' => $this->updated_at,
            'parent_id' => $this->variablemorph_id,
            'parent_type' => $this->variablemorph_type == Block::class ? VariableParentType::BLOCK : VariableParentType::VARIABLE,
        ];

        if(in_array($this->type, [VariableType::REPEATER, VariableType::OBJECT, VariableType::SELECT])) {
            $data['variables'] = VariableInformation::collection($this->variables);
        }

        return $data;
    }
}
