<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use HttpResponses;

    public function __construct(private AuditService $audit) {}

    public function index()
    {
        $roles = Role::where('guard_name', 'user')
            ->select('id', 'name', 'created_at')
            ->latest()
            ->paginate(50);

        if ($roles->isEmpty()) {
            return $this->success(
                message: 'No roles found',
                data: []
            );
        }

        return $this->success(
            message: 'Roles fetched successfully',
            data: $roles
        );
    }


    public function listRolesWithPermissions(Request $request)
    {
        $searchTerm = $request->query('search');

        $roles = Role::where('guard_name', 'user')
            ->select('id', 'name')
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            })
            ->with(['permissions' => function ($query) {
                $query->select('permissions.id', 'permissions.name');
            }])
            ->latest()
            ->paginate(2);

        if ($roles->isEmpty() && !$searchTerm) {
            return $this->success(message: 'No roles found', data: []);
        }

        $roles->getCollection()->transform(function ($role) {
            $role->permissions->makeHidden('pivot');
            return $role;
        });

        return $this->success(
            message: 'Roles with permissions fetched successfully',
            data: $roles
        );
    }


    public function listRolesWithUsers()
    {
        $roles = Role::where('guard_name', 'user')
            ->with(['users' => function ($query) {
                $query->select('id', 'name', 'email');
            }])
            ->latest()
            ->paginate(50);

        if ($roles->isEmpty()) {
            return $this->success(
                message: 'No roles found',
                data: []
            );
        }

        $roles->getCollection()->transform(function ($role) {
            $role->users->makeHidden('pivot');
            return $role;
        });

        return $this->success(
            message: 'Roles with users fetched successfully',
            data: $roles
        );
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,NULL,id,guard_name,user',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $authUser = Auth::guard('user')->user();
        $userName = $authUser?->username ?? 'System';
        $roleName = Str::snake(trim($request->name));

        $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'user'
        ]);

        $role->syncPermissions($request->permissions);

        $this->audit->trail(
            module: 'Role',
            action: 'Create',
            auditableType: Role::class,
            auditableId: $role->id,
            before: null,
            after: $role->load('permissions')->toArray(),
            description: "The role {$role->name} with " . count($request->permissions) . " permissions was created by {$userName}."
        );

        return $this->success(message: 'Role and permissions created successfully');
    }

    public function show($id)
    {
        $role = Role::where('guard_name', 'user')
            ->with('permissions:id') // Only need the IDs for checking
            ->findOrFail($id);

        $allPermissions = Permission::where('guard_name', 'user')
            ->select('id', 'name')
            ->get();

        return $this->success(
            message: 'Role data fetched',
            data: [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'current_permissions' => $role->permissions->pluck('id')
                ],
                'all_permissions' => $allPermissions
            ]
        );
    }



    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $before = $role->load('permissions')->toArray();

        $request->validate([
            // This ensures the name is required and unique, ignoring the current record
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'present|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $authUser = Auth::guard('user')->user();

        // Use the validated name
        $role->update(['name' => Str::snake($request->name)]);

        $role->permissions()->sync($request->permissions);

        $this->audit->trail(
            module: 'Role',
            action: 'Update',
            auditableType: Role::class,
            auditableId: $role->id,
            before: $before,
            after: $role->load('permissions')->toArray(),
            description: "Role {$role->name} updated by {$authUser->name} (ID: {$authUser->id})"
        );

        return $this->success(message: 'Role and permissions updated successfully');
    }


    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $before = $role->toArray();

        $userName = Auth::guard('user')->user()?->name ?? 'System';

        $role->delete();

        $this->audit->trail(
            module: 'Role',
            action: 'Delete',
            auditableType: Role::class,
            auditableId: $before['id'],
            before: $before,
            after: null,
            description: "The role {$before['name']} was deleted by {$userName}."
        );

        return $this->success(
            message: 'Role deleted successfully.'
        );
    }


    public function assignPermissions(Request $request, $roleId)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '422',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::where('guard_name', 'user')->findOrFail($roleId);

        // Existing permissions
        $existingPermissions = $role->permissions()
            ->pluck('permissions.id')
            ->toArray();

        // Check duplicates
        $duplicates = array_intersect($existingPermissions, $request->permissions);

        if (!empty($duplicates)) {

            $duplicateNames = Permission::whereIn('id', $duplicates)
                ->pluck('name');

            return response()->json([
                'status' => 'error',
                'responseCode' => '409',
                'message' => 'Some permissions are already assigned to this role.',
                'duplicates' => $duplicateNames
            ], 409);
        }

        // Before state
        $before = $existingPermissions;

        // Attach new permissions
        $role->permissions()->attach($request->permissions);

        // After state
        $after = $role->permissions()
            ->pluck('permissions.id')
            ->toArray();

        $user = Auth::guard('user')->user()?->name ?? 'System';

        $this->audit->trail(
            module: 'Role',
            action: 'Assign Permission',
            auditableType: Role::class,
            auditableId: $role->id,
            before: $before,
            after: $after,
            description: "Permissions assigned to role {$role->name} by {$user}."
        );

        return $this->success(
            message: 'Permissions assigned successfully'
        );
    }


    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($request->user_id);
        if (!$user) {
            return $this->error('invalid credentials');
        }
        $user->syncRoles($request->role_id);

        return $this->success(message: 'Role assigned successfully');
    }
}
