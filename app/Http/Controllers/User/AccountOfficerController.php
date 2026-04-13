<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AccountOfficer;
use App\Services\AuditService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountOfficerController extends Controller
{
    use HttpResponses;

    public function __construct(private AuditService $audit) {}


    public function index(Request $request)
    {
        $search = $request->search;

        $officers = AccountOfficer::with([
            'branch:id,name',
            'user:id,username,first_name,last_name'
        ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('gender', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(50);

        return $this->success(
            message: 'Account officers fetched successfully',
            data: $officers
        );
    }

    public function show($id)
    {
        $officer = AccountOfficer::with([
            'branch:id,name',
        ])->findOrFail($id);

        return $this->success(
            message: 'Account officer fetched successfully',
            data: $officer
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'  => 'required|exists:branches,id',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:account_officers,email',
            'phone'      => 'required|digits:11|string|unique:account_officers,phone',
            'address'    => 'required|string',
            'gender'    => 'required|string',
            'city'       => 'required|string',
            'state'      => 'required|string',
            'country'    => 'nullable|string',
        ]);

        $validated['code'] = $this->generateOfficerCode();

        // Modern Eloquent way: Create through the authenticated user relationship
        $user = Auth::guard('user')->user();
        $officer = $user->accountOfficers()->create($validated);

        $this->audit->trail(
            module: 'AccountOfficer',
            action: 'Create',
            auditableType: AccountOfficer::class,
            auditableId: $officer->id,
            before: null,
            after: $officer->toArray(),
            description: "Account Officer {$officer->first_name} was created by {$user->username}."
        );

        return $this->success(
            message: 'Account officer created successfully',
            // data: $officer
        );
    }

    public function update(Request $request, $id)
    {
        $officer = AccountOfficer::findOrFail($id);
        $validated = $request->validate([
            'branch_id'  => 'nullable|exists:branches,id',
            'first_name' => 'nullable|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'nullable|email|unique:account_officers,email,' . $id,
            'phone'      => 'nullable|digits:11|string|unique:account_officers,phone,' . $id,
            'address'    => 'nullable|string',
            'gender'    => 'nullable|string',
            'city'       => 'nullable|string',
            'state'      => 'nullable|string',
        ]);

        $before = $officer->toArray();
        $officer->update($validated);

        $user = Auth::guard('user')->user();

        $this->audit->trail(
            module: 'AccountOfficer',
            action: 'Update',
            auditableType: AccountOfficer::class,
            auditableId: $officer->id,
            before: $before,
            after: $officer->toArray(),
            description: "Account Officer {$officer->code} and ID {$officer->id} updated by {$user->username}."
        );

        return $this->success(
            message: 'Account officer updated successfully',
            data: $officer
        );
    }

    public function delete($id)
    {
        $officer = AccountOfficer::findOrFail($id);
        $before = $officer->toArray();
        $username = Auth::guard('user')->user()?->username ?? 'System';

        $officer->delete();

        $this->audit->trail(
            module: 'AccountOfficer',
            action: 'Delete',
            auditableType: AccountOfficer::class,
            auditableId: $id,
            before: $before,
            after: null,
            description: "Account Officer {$before['code']} deleted by {$username}."
        );

        return $this->success(message: 'Account officer deleted successfully');
    }

    private function generateOfficerCode(): string
    {
        $lastId = AccountOfficer::max('id') ?? 0;
        return 'AO-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
    }
}
