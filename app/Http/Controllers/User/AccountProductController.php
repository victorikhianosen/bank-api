<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AccountProduct;
use App\Models\ProductGlMapping;
use App\Services\AccountProductService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AccountProductController extends Controller
{
    use HttpResponses;

    public function __construct(private AccountProductService $productService) {}


    public function index()
    {
        $products = AccountProduct::latest()->paginate(10);

        return $this->success(
            'Products retrieved successfully',
            $products
        );
    }

    public function show($id)
    {
        $product = AccountProduct::find($id);

        if (!$product) {
            return $this->error('Product not found');
        }

        return $this->success(
            'Product retrieved successfully',
            $product
        );
    }

    public function store(Request $request)
    {

        Log::error('🔥 Test Telegram Error Log');
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

                        $role = $item['gl_role'];
                        $ledgerId = $item['ledger_account_id'];

                        // 1. Prevent duplicate roles in same request
                        if (in_array($role, $roles)) {
                            $fail("Duplicate GL role '{$role}' in request.");
                        }

                        $roles[] = $role;

                        // 2. Prevent duplicate role + ledger combo in request
                        $key = $ledgerId . '-' . $role;
                        if (in_array($key, $combinations)) {
                            $fail("Duplicate GL mapping for role '{$role}' and ledger '{$ledgerId}'.");
                        }

                        $combinations[] = $key;

                        /**
                         * ✅ ONLY APPLY STRICT RULE TO CUSTOMER BALANCE
                         */
                        if ($role === 'customer_deposit') {

                            $exists = DB::table('product_gl_mappings')
                                ->where('ledger_account_id', $ledgerId)
                                ->exists(); // 🔥 no need to check role

                            if ($exists) {
                                $fail("Customer Balance GL must use a new ledger account. Ledger is already assigned.");
                            }
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

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:account_products,id',
            'status' => 'required|in:active,inactive,pending',
        ]);

        try {
           $product = $this->productService->updateStatus($validated);

            return $this->success(
                'Product status updated successfully',
                $product
            );
        } catch (\Throwable $e) {

            Log::info('Update Product Status Error', [
                'Error' => $e->getMessage()
            ]);

            return $this->error('Unable to update Product status, Please try again');
        }

        
    }
}
