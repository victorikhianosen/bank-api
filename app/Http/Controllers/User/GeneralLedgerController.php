<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\GeneralLedger\StoreGeneralLedgerRequest;
use App\Http\Requests\User\GeneralLedger\UpdateGeneralLedgerRequest;
use App\Http\Requests\User\GeneralLedger\UpdateGeneralLedgerStatusRequest;
use App\Models\GeneralLedger;
use App\Services\GeneralLedgerService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class GeneralLedgerController extends Controller
{
    use HttpResponses;

    public function __construct(
        private GeneralLedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = GeneralLedger::query();

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('gl_code', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('classification')) {
            $query->where(
                'classification',
                $request->classification
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        $ledgers = $query
            ->with('parent')
            ->latest()
            ->paginate(50);

        return $this->success(
            message: 'General Ledgers fetched successfully',
            data: $ledgers
        );
    }

    public function store(StoreGeneralLedgerRequest $request)
    {
        // return $request->all();

        $ledger = $this->ledgerService->store(
            $request->validated()
        );

        return $this->success(
            message: 'General Ledger created successfully',
            data: $ledger
        );
    }

    public function show(string $id)
    {
        $ledger = GeneralLedger::with('parent')
            ->findOrFail($id);

        return $this->success(
            message: 'General Ledger fetched successfully',
            data: $ledger
        );
    }

    public function update(
        UpdateGeneralLedgerRequest $request,
        string $id
    ) {

        $ledger = GeneralLedger::findOrFail($id);

        $ledger = $this->ledgerService->update(
            $ledger,
            $request->validated()
        );

        return $this->success(
            message: 'General Ledger updated successfully',
            data: $ledger
        );
    }

    public function updateStatus(
        UpdateGeneralLedgerStatusRequest $request,
        string $id
    ) {

        $ledger = GeneralLedger::findOrFail($id);

        $ledger = $this->ledgerService->updateStatus(
            $ledger,
            $request->status
        );

        return $this->success(
            message: 'General Ledger status updated successfully',
            data: $ledger
        );
    }

    public function destroy(string $id)
    {
        $ledger = GeneralLedger::findOrFail($id);

        $this->ledgerService->delete($ledger);

        return $this->success(
            message: 'General Ledger deleted successfully'
        );
    }
}