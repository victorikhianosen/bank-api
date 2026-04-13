<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{


    public function toArray(Request $request): array
    {
        $role = $this->roles->first();
        $branch = $this->branch;

        return [
            "id"          => $this->id,
            "first_name"  => $this->first_name,
            "last_name"   => $this->last_name,
            "photo"       => $this->photo,
            "middle_name" => $this->middle_name,
            "username"    => $this->username,
            "status"      => $this->status,
            "email"       => $this->email,
            "phone"       => $this->phone,
            "gender"      => $this->gender,

            "address"      => $this->address,
            "city"      => $this->city,
            "state"      => $this->state,
            "last_login"      => $this->last_login,

            "role" => $role ? [
                "id"   => $role->id,
                "name" => $role->name,
            ] : null,

            "branch" => $branch ? [
                "id"   => $branch->id,
                "name" => $branch->name,
            ] : null,

            "permissions" => $this->getAllPermissions()->pluck('name'),
        ];
    }
}
