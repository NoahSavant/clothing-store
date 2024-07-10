<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInformation extends JsonResource
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
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'image_url' => $this->image_url,
            'phonenumber' => $this->phonenumber,
            'status' => $this->status,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
        ];

        return $data;
    }
}
