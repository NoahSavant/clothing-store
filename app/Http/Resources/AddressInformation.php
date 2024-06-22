<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressInformation extends JsonResource
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
            'default' => $this->default,
            'name' => $this->name,
            'content' => $this->content,
            'detail' => $this->detail,
            'url' => $this->url,
        ];

        return $data;
    }
}
