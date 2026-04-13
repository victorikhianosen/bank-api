<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id"          => $this->id,
            "branch_id"   => $this->branch_id,
            "first_name"  => $this->first_name,
            "last_name"   => $this->last_name,
            "photo"       => $this->photo,
            "middle_name" => $this->middle_name,
            "username"    => $this->username,
            "status"      => $this->status,
            "email"       => $this->email,
            "phone"       => $this->phone,
            "gender"      => $this->gender,
            "address"      => $this->gender,
            "city"      => $this->gender,
            "state"      => $this->gender,
            "role_name"   => $this->getRoleNames()->first(),
        ];
    }
}
