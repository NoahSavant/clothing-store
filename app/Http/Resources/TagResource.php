<?php

namespace App\Http\Resources;

use App\Constants\TagConstants\TagParent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
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
            'color' => $this->color,
            'parent_id' => $this->tagmorph_id,
            'parent_type' =>  TagParent::getTagParent($this->tagmorph_type),
        ];

        return $data;
    }
}
