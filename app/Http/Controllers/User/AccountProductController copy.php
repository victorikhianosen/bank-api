<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProductGlMapping;
use App\Services\AccountProductService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AccountProductController extends Controller
{
    use HttpResponses;

    public function __construct(private AccountProductService $productService) {}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'account_category_id' => [
                'required',
                'string',
                'size:26',
                Rule::exists('account_categories', 'id'),
            ],
            'currency_id' => 'required|exists:currencies,id',

            'interest_rate' => 'nullable|numeric|min:0',
            'transfer_fee' => 'nullable|numeric|min:0',
            'maintenance_fee' => 'nullable|numeric|min:0',

            'gl_mappings' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {

                    $roles = [];
                    $combinations = [];

                    foreach ($value as $index => $item) {
                        // 1. Prevent duplicate gl_role in same product
                        if (in_array($item['gl_role'], $roles)) {
                            $fail("Duplicate GL role '{$item['gl_role']}' in request.");
                        }

                        $roles[] = $item['gl_role'];

                        $key = $item['ledger_account_id'] . '-' . $item['gl_role'];

                        if (in_array($key, $combinations)) {
                            $fail("Duplicate GL mapping for role '{$item['gl_role']}' and ledger '{$item['ledger_account_id']}'.");
                        }

                        $combinations[] = $key;

                        $exists = DB::table('product_gl_mappings')
                            ->where('ledger_account_id', $item['ledger_account_id'])
                            ->where('gl_role', $item['gl_role'])
                            ->exists();

                        if ($exists) {
                            $fail("GL role '{$item['gl_role']}' is already assigned to this ledger account.");
                        }
                    }
                }
            ],

            'gl_mappings.*.gl_role' => 'required|string',
            'gl_mappings.*.ledger_account_id' => 'required|exists:ledger_accounts,id',
        ]);

        try {
            $product = $this->productService->create($validated);

            return $this->success(
                'Product created successfully',
                $product
            );
        } catch (\Throwable $e) {

            Log::info('Create GL Error', [
                'Error' => $e->getMessage()
            ]);

            return $this->error('Unable to create Product, Please try again');
        }
    }

    public function activate($id)
    {
        try {

            $product = $this->productService->activate($id);

            return $this->success(
                'Product created successfully',
                $product
            );
        } catch (\Exception $e) {

            Log::info('Create GL Error', [
                'Error' => $e->getMessage()
            ]);
            return $this->error('Unable to create Product, Please try again');
        }
    }
}
