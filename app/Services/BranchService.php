<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BranchService
{
    public function create(array $data): Branch
    {
        $user = Auth::guard('user')->user();

        return Branch::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? $this->generateCode($data['name']),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'Nigeria',
            'status' => $data['status'] ?? 'active',
            'created_by' => $user?->id,
        ]);
    }

    public function update(Branch $branch, array $data): Branch
    {
        $user = Auth::guard('user')->user();

        $branch->update([
            'name' => $data['name'] ?? $branch->name,
            'email' => $data['email'] ?? $branch->email,
            'phone' => $data['phone'] ?? $branch->phone,
            'address' => $data['address'] ?? $branch->address,
            'city' => $data['city'] ?? $branch->city,
            'state' => $data['state'] ?? $branch->state,
            'country' => $data['country'] ?? $branch->country,
            'status' => $data['status'] ?? $branch->status,
            'updated_by' => $user?->id,
        ]);

        return $branch;
    }

    public function changeStatus(Branch $branch, string $status): Branch
    {
        $user = Auth::guard('user')->user();

        $branch->update([
            'status' => $status,
            'updated_by' => $user?->id,
        ]);

        return $branch;
    }

    protected function generateCode(string $name): string
    {
        return strtoupper(Str::slug($name)) . '-' . rand(100, 999);
    }
}