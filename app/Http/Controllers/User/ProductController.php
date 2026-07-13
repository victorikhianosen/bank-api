<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Product\StoreAccountProductRequest;
use App\Services\AccountProductService;
use App\Traits\HttpResponses;

class ProductController extends Controller
{
    use HttpResponses;

    public function __construct(
        private AccountProductService $productService
    ) {}

    public function store(StoreAccountProductRequest $request)
    {
        $product = $this->productService->store(
            $request->validated()
        );

        return $this->success(
            message: 'Account Product created successfully',
            data: $product
        );
    }
}
