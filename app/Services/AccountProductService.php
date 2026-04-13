<?php

namespace App\Services;

use App\Models\AccountProduct;
use App\Models\LedgerAccount;
use App\Models\ProductGlMapping;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountProductService
{
    public function __construct(protected AuditService $audit) {}
    public function create(array $data): AccountProduct
    {

        return DB::transaction(function () use ($data) {

            $glMappings = collect($data['gl_mappings']);

            if ($glMappings->pluck('gl_role')->duplicates()->isNotEmpty()) {
                throw new Exception("Duplicate GL roles are not allowed");
            }

            if ($glMappings->pluck('ledger_account_id')->duplicates()->isNotEmpty()) {
                throw new Exception("Same GL account cannot be assigned multiple times");
            }

            $allowedRoles = [
                'customer_deposit',
                'interest_payable',
                'interest_income',
                'fee_income',
                'overdraft_receivable',
            ];

            foreach ($glMappings as $gl) {
                if (!in_array($gl['gl_role'], $allowedRoles)) {
                    throw new Exception("Invalid GL role: {$gl['gl_role']}");
                }
            }

            $requiredRoles = ['customer_deposit'];

            $missingRoles = array_diff(
                $requiredRoles,
                $glMappings->pluck('gl_role')->toArray()
            );

            if (!empty($missingRoles)) {
                throw new Exception("Missing required GL roles: " . implode(', ', $missingRoles));
            }

            $authUser =  Auth::guard('user')->user();

            $product = AccountProduct::create([
                'id' => Str::ulid(),
                'name' => $data['name'],
                'code' => $this->generateUniqueProductCode(),

                'account_category_id' => $data['account_category_id'],
                'currency_id' => $data['currency_id'],

                'interest_rate' => $data['interest_rate'] ?? 0,
                'transfer_fee' => $data['transfer_fee'] ?? 0,
                'maintenance_fee' => $data['maintenance_fee'] ?? 0,

                'status' => 'pending',
                'created_by' => $authUser->id,
            ]);

            foreach ($glMappings as $gl) {

                $ledger = LedgerAccount::findOrFail($gl['ledger_account_id']);

                if (!$ledger->currency_id) {
                    throw new Exception("GL account [{$ledger->name}] has no currency defined");
                }

                if ($ledger->currency_id != $data['currency_id']) {
                    throw new Exception(
                        "GL account [{$ledger->name}] currency does not match product currency"
                    );
                }

                $existing = ProductGlMapping::where('ledger_account_id', $ledger->id)->first();

                if ($existing && $existing->gl_role !== $gl['gl_role']) {
                    throw new Exception(
                        "GL account [{$ledger->name}] already assigned to role: {$existing->gl_role}"
                    );
                }

                ProductGlMapping::create([
                    'product_id' => $product->id,
                    'gl_role' => $gl['gl_role'],
                    'ledger_account_id' => $ledger->id,
                ]);
            }

            $authUser = Auth::guard('user')->user();
            $actorName = $authUser?->name ?? 'System';
            $actorId   = $authUser?->id ?? 'N/A';

            $this->audit->trail(
                module: 'Account Product',
                action: 'create Account Product',
                auditableType: AccountProduct::class,
                auditableId: $product->id,
                before: null,
                after: $product->toArray(),
                description: "{$actorName} (ID: {$actorId}) created account product [{$product->name}] (ID: {$product->id})"
            );

            return $product;
        });
    }


    public function updateStatus(array $data): AccountProduct
    {
        $accountProduct = AccountProduct::find($data['id']);

        if (!$accountProduct) {
            throw new Exception("Account product not found");
        }

        $before = $accountProduct->toArray();

        $authUser = Auth::guard('user')->user();
        $time = now();

        $accountProduct->update([
            'status' => $data['status'],
            'approved_by' => $authUser->id,
            'approved_at' => $time,
            'updated_at' => $time,
        ]);

        $this->audit->trail(
            module: 'Account Product',
            action: 'Update Account Product Status',
            auditableType: AccountProduct::class,
            auditableId: $accountProduct->id,
            before: $before,
            after: $accountProduct->fresh()->toArray(),
            description: "User {$authUser->name} (ID: {$authUser->id}) updated account product [{$accountProduct->name}] (ID: {$accountProduct->id}) status to {$data['status']}"
        );

        return $accountProduct;
    }


    private function generateUniqueProductCode(): string
    {
        do {
            $code = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            $exists = AccountProduct::where('code', $code)->exists();
        } while ($exists);

        return $code;
    }
}
