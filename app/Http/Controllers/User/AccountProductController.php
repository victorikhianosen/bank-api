<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AccountProduct\StoreAccountProductRequest;
use App\Models\AccountProduct;
use App\Services\AccountProductService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class AccountProductController extends Controller
{
    use HttpResponses;

    public function __construct(
        private AccountProductService $service
    ) {}

    public function index(Request $request)
    {
        $query = AccountProduct::query();

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        $products = $query
            ->latest()
            ->paginate(20);

        return $this->success(
            message: 'Account Products fetched successfully',
            data: $products
        );
    }

    public function store(StoreAccountProductRequest $request)
    {
        $product = $this->service->store(
            $request->validated()
        );

        return $this->success(
            message: 'Account Product created successfully',
            data: $product
        );
    }

    public function show(string $id)
    {
        $product = AccountProduct::with([
            'generalLedgerMappings.generalLedger'
        ])->findOrFail($id);

        return $this->success(
            message: 'Account Product fetched successfully',
            data: $product
        );
    }
}