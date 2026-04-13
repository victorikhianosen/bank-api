<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\GeneralLedger;
use App\Services\AuditService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GeneralLedgerController extends Controller
{
    use HttpResponses;

    public function __construct(private AuditService $audit) {}


    public function index()
    {
        $ledgers = GeneralLedger::with('currency')
            ->latest()
            ->paginate(50);

        if ($ledgers->isEmpty()) {
            return $this->success(
                message: 'No general ledgers found',
                data: []
            );
        }

        return $this->success(
            message: 'General ledgers fetched successfully',
            data: $ledgers
        );
    }


    public function show($id)
    {
        $ledger = GeneralLedger::findOrFail($id);

        return $this->success(
            message: 'General ledger fetched successfully',
            data: $ledger
        );
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'currency_id' => 'required|exists:currencies,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('general_ledgers')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->branch_id);
                }),
            ],
            'type' => 'required|in:asset,liability,equity,income,expense',
        ]);

        // Get currency
        $currency = Currency::find($request->currency_id);

        $user = Auth::guard('user')->user()?->username ?? 'System';

        $data = [
            'branch_id'   => $request->branch_id,
            'currency_id' => $request->currency_id,
            'currency'    => $currency?->code,
            'name'        => $request->name,
            'type'        => $request->type,
            'code'        => $this->generateGLCode(),
            'balance'     => 0,
            'status'      => 'active',
            'created_by'  => $user,
            'updated_by'  => null,
        ];

        $ledger = GeneralLedger::create($data);
        $ledger->load('currency');

        $this->audit->trail(
            module: 'General Ledger',
            action: 'Create',
            auditableType: GeneralLedger::class,
            auditableId: $ledger->id,
            before: null,
            after: $ledger->toArray(),
            description: "GL {$ledger->name} ({$ledger->code}) was created by {$user}."
        );

        return $this->success(
            message: 'General ledger created successfully',
            data: $ledger
        );
    }


    public function update(Request $request, $id)
    {
        $ledger = GeneralLedger::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('general_ledgers')
                    ->where(function ($query) use ($ledger) {
                        return $query->where('branch_id', $ledger->branch_id);
                    })
                    ->ignore($ledger->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '422',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $before = $ledger->toArray();

        $user = Auth::guard('user')->user()?->username ?? 'System';

        $ledger->update([
            'name' => $request->name,
            'updated_by' => $user
        ]);

        $ledger->load('currency');

        $this->audit->trail(
            module: 'General Ledger',
            action: 'Update',
            auditableType: GeneralLedger::class,
            auditableId: $ledger->id,
            before: $before,
            after: $ledger->toArray(),
            description: "GL name changed to {$ledger->name} ({$ledger->code}) by {$user}."
        );

        return $this->success(
            message: 'General ledger name updated successfully',
        );
    }


    public function updateStatus(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive'
        ]);

        $ledger = GeneralLedger::findOrFail($id);


        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '422',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $before = $ledger->toArray();

        $user = Auth::guard('user')->user()?->username ?? 'System';

        $ledger->update([
            'status' => $request->status,
            'updated_by' => $user
        ]);
        // Audit
        $this->audit->trail(
            module: 'General Ledger',
            action: 'Update Status',
            auditableType: GeneralLedger::class,
            auditableId: $ledger->id,
            before: $before,
            after: $ledger->toArray(),
            description: "GL {$ledger->name} ({$ledger->code}) status changed to {$request->status} by {$user}."
        );

        return $this->success(
            message: 'General ledger status updated successfully',
            data: $ledger
        );
    }

    private function generateGLCode(): string
    {
        do {
            $code = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (GeneralLedger::where('code', $code)->exists());

        return $code;
    }
}
