<?php

namespace App\Http\Controllers\User;

use App\Helpers\TransactionReference;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Transfer\StoreInternalTransferRequest;
use App\Services\InternalTransferService;
use App\Traits\HttpResponses;
use DateTime;

class InternalTransferController extends Controller
{
    use HttpResponses;

    public function __construct(
        private InternalTransferService $internalTransferService
    ) {}

    public function store(
        StoreInternalTransferRequest $request
    ) {

        // return $request->all();

        $transfer = $this->internalTransferService->transfer(
            $request->validated()
        );

        return $this->success(
            'Transfer successful',
            $transfer
        );
    }
}
