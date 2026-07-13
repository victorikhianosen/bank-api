<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Account\StoreAccountRequest;
use App\Models\Account;
use App\Models\AccountProduct;
use App\Services\AccountService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    use HttpResponses;

    public function __construct(
        private AccountService $service
    ) {}

    public function index(Request $request)
    {
        $query = Account::query();

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where(
                    'account_no',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'account_name',
                    'like',
                    "%{$search}%"
                );
            });
        }

        $accounts = $query
            ->with([
                'customer',
                'product',
                'tier',
            ])
            ->latest()
            ->paginate(20);

        return $this->success(
            'Accounts fetched successfully',
            $accounts
        );
    }

    public function store(
        StoreAccountRequest $request
    ) {

        try {

            $account = $this->service->store(
                $request->validated()
            );

            return $this->success(
                'Account created successfully',
                $account
            );

        } catch (\Exception $e) {

            Log::error('Account creation failed', [

                'message' => $e->getMessage(),
            ]);

            return $this->error(
                $e->getMessage(),
                400
            );
        }
    }

    public function show($id)
    {
        $account = Account::with([
            'customer',
            'product',
                    'tier'

        ])->findOrFail($id);

        return $this->success(
            'Account fetched successfully',
            $account
        );
    }
}