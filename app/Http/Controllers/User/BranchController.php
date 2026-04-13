<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\AuditService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    use HttpResponses;

    public function __construct(private AuditService $audit) {}

    public function index()
    {
        $branches = Branch::latest()->paginate(50);

        if ($branches->isEmpty()) {
            return $this->success(
                message: 'No branches found',
                data: []
            );
        }

        return $this->success(
            message: 'Branches fetched successfully',
            data: $branches
        );
    }

    public function show($id)
    {
        $branch = Branch::findOrFail($id);

        return $this->success(
            message: 'Branch fetched successfully',
            data: $branch
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'status' => 'nullable|in:active,pending,blocked,disabled,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '422',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['code'] = $this->generateBranchCode();

        $branch = Branch::create($data);

        $user = Auth::guard('user')->user()?->username ?? 'System';

        $this->audit->trail(
            module: 'Branch',
            action: 'Create',
            auditableType: Branch::class,
            auditableId: $branch->id,
            before: null,
            after: $branch->toArray(),
            description: "Branch {$branch->name} ({$branch->code}) was created by {$user}."
        );

        return $this->success(
            message: 'Branch created successfully',
            data: $branch
        );
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'status' => 'nullable|in:active,pending,blocked,disabled,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '422',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $before = $branch->toArray();

        $branch->update($request->all());

        $user = Auth::guard('user')->user()?->username ?? 'System';

        $this->audit->trail(
            module: 'Branch',
            action: 'Update',
            auditableType: Branch::class,
            auditableId: $branch->id,
            before: $before,
            after: $branch->toArray(),
            description: "Branch {$branch->name} ({$branch->code}) was updated by {$user}."
        );

        return $this->success(
            message: 'Branch updated successfully',
            data: $branch
        );
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);

        $before = $branch->toArray();

        $user = Auth::guard('user')->user()?->username ?? 'System';

        $branch->update([
            'status' => 'delete'
        ]);

        $this->audit->trail(
            module: 'Branch',
            action: 'Delete',
            auditableType: Branch::class,
            auditableId: $before['id'],
            before: $before,
            after: null,
            description: "Branch {$before['name']} ({$before['code']}) was deleted by {$user}."
        );

        return $this->success(
            message: 'Branch deleted successfully'
        );
    }

    private function generateBranchCode(): string
    {
        $lastBranch = Branch::orderBy('id', 'desc')->first();

        $nextNumber = $lastBranch ? $lastBranch->id + 1 : 1;

        return 'BR' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}