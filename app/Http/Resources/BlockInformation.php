<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockInformation extends JsonResource
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
            'name' => $this->name,
            'block_id' => $this->block_id,
            'instance_name' => $this->instance_name,
            'index' => $this->index,
            'hide' => $this->hide,
            'variables' => VariableInformation::collection($this->variables)
        ];

        return $data;
    }
}
