<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use HttpResponses;

    public function __construct(private AuditService $audit) {}

    public function index()
    {
        $permissions = Permission::where('guard_name', 'user')
            ->select('id', 'name')
            ->latest()
            ->get();

        if ($permissions->isEmpty()) {
            return $this->success(
                message: 'No permissions found',
                data: []
            );
        }

        return $this->success(
            message: 'Permissions fetched successfully',
            data: $permissions
        );
    }

    public function indexWithPagination()
    {
        $permissions = Permission::where('guard_name', 'user')
            ->select('id', 'name')
            ->latest()
            ->paginate(50);

        if ($permissions->isEmpty()) {
            return $this->success(
                message: 'No permissions found',
                data: [
                    'data' => [],
                    'meta' => []
                ]
            );
        }

        return $this->success(
            message: 'Permissions fetched successfully',
            data: $permissions
                
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name,NULL,id,guard_name,user'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '422',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::guard('user')->user()?->username ?? 'System';

        $permissionName = Str::snake(strtolower(trim($request->name)));

        $permission = Permission::create([
            'name' => $permissionName,
            'guard_name' => 'user'
        ]);

        $this->audit->trail(
            module: 'Permission',
            action: 'Create',
            auditableType: Permission::class,
            auditableId: $permission->id,
            before: null,
            after: $permission->toArray(),
            description: "The permission with ID {$permission->id} and name {$permission->name} was created by {$user}."
        );

        return $this->success(
            message: 'Permission created successfully'
        );
    }

  public function update(Request $request, $id)
{
    $permission = Permission::findOrFail($id);
    $permissionName = Str::snake(strtolower(trim($request->name)));
    $dataToValidate = $request->all();
    $dataToValidate['name'] = $permissionName;
    $validator = Validator::make($dataToValidate, [
        'name' => [
            'required',
            'string',
            Rule::unique('permissions', 'name')
                ->ignore($permission->id)
                ->where('guard_name', 'user')
        ]
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'responseCode' => '422',
            'message' => 'The permission name has already been taken.',
            'errors' => $validator->errors()
        ], 422);
    }

    $before = $permission->toArray();

    // 4. Perform the update
    $permission->update([
        'name' => $permissionName
    ]);

    $user = Auth::guard('user')->user()?->username ?? 'System';

    $this->audit->trail(
        module: 'Permission',
        action: 'Update',
        auditableType: Permission::class,
        auditableId: $permission->id,
        before: $before,
        after: $permission->toArray(),
        description: "The permission with ID {$permission->id} and name {$permission->name} was updated by {$user}."
    );

    return $this->success(
        message: 'Permission updated successfully'
    );
}

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        $before = $permission->toArray();

        $user = Auth::guard('user')->user()?->username ?? 'System';

        $permission->delete();

        $this->audit->trail(
            module: 'Permission',
            action: 'Delete',
            auditableType: Permission::class,
            auditableId: $before['id'],
            before: $before,
            after: null,
            description: "The permission with ID {$before['id']} and name {$before['name']} was deleted by {$user}."
        );

        return $this->success(
            message: 'Permission deleted successfully'
        );
    }
}
