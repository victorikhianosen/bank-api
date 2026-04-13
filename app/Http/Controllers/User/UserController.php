<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserListResource;
use App\Http\Resources\User\UserResource;
use App\Models\Settings;
use App\Models\User;
use App\Services\AuditService;
use App\Services\UserService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Models\Role;
use Symfony\Component\Mime\Message;

class UserController extends Controller
{
    use HttpResponses;

    public function __construct(
        private UserService $userService,
        private AuditService $audit
    ) {}

    public function profile()
    {
        $user = auth()->guard('user')->user();
        return $this->success(
            message: 'User profile fetched successfully',
            data: new UserResource($user)
        );
    }

    public function verify2faOtp(Request $request)
    {
        $request->validate([
            'login_token' => 'required',
            'otp' => 'required'
        ]);

        $userId = Cache::get("user_login_{$request->login_token}");

        if (!$userId) {
            return $this->error(
                message: 'Login session expired',
                statusCode: 401
            );
        }

        $user = User::findOrFail($userId);

        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            $user->google2fa_secret,
            $request->otp
        );

        if (!$valid) {
            return $this->error(
                message: 'Invalid OTP',
                statusCode: 422
            );
        }

        Cache::forget("user_login_{$request->login_token}");

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now()
        ]);

        $token = $user->createToken('user-token')->plainTextToken;

        $this->userService->updateLastLogin($user);

        return $this->success(
            message: 'Login successful',
            data: [
                'user' => new $user,
                'access_token' => $token
            ]
        );
    }

    public function setupTwoFactor()
    {
        $user = auth('user')->user();

        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();

        $user->update([
            'google2fa_secret' => $secret
        ]);

        $qr = $google2fa->getQRCodeInline(
            'AssetMatrix',
            $user->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qr_code' => $qr
        ]);
    }

    public function confirmTwoFactor(Request $request)
    {
        $request->validate([
            'otp' => 'required'
        ]);

        $user = auth('user')->user();

        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            $user->google2fa_secret,
            $request->otp
        );

        if (!$valid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP'
            ], 422);
        }

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '2FA enabled successfully'
        ]);
    }

    public function index(Request $request)
    {
        try {
            $query = User::query();

            if ($request->filled('search') && trim($request->search) !== '') {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $users = $query->latest()->paginate(50);

            $baseQuery = User::query();
            if ($request->filled('search') && trim($request->search) !== '') {
                $search = trim($request->search);
                $baseQuery->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            }

            return $this->success(
                message: 'User fetched successfully',
                data: [
                    'users' => UserListResource::collection($users),
                    'status' => [
                        'total'   => (clone $baseQuery)->count(),
                        'active'  => (clone $baseQuery)->where('status', 'active')->count(),
                        'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
                        'blocked' => (clone $baseQuery)->where('status', 'blocked')->count(),
                    ]
                ]
            );
        } catch (\Throwable $e) {
            // Log the error for debugging
            Log::error('User fetch error: ' . $e->getMessage());
            return $this->error(message: 'Unable to fetch data', statusCode: 500);
        }
    }


    public function store(Request $request)
    {

        $validated = $request->validate([
            'first_name' => 'required|string|min:2',
            'last_name'  => 'required|string|min:2',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'required|digits:11|unique:users,phone',
            'gender'     => 'required|string',
            'password'   => 'required|min:8|max:30',
            'role_id'    => 'nullable|integer|exists:roles,id',
            'photo'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $this->userService->store($validated);

        return $this->success(
            message: "User created successfully",
            data: $user
        );
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = $request->validate([
            'first_name' => 'nullable|string',
            'last_name'  => 'nullable|string',
            'email'      => 'nullable|email|unique:users,email,' . $user->id,
            'phone'      => 'nullable|unique:users,phone,' . $user->id,
            'gender'     => 'nullable|string',
            'address'    => 'nullable|string',
            'password'   => 'nullable|min:6',
            'role_id'    => 'nullable|integer|exists:roles,id',
            'status'     => 'nullable'
        ]);
        $updatedUser = $this->userService->update($id, $request->all());
        return $this->success(
            message: 'User updated successfully',
            data: $updatedUser
        );
    }


    public function show(string $id)
    {
        // Eager load Spatie relations
        $user = User::with(['roles', 'permissions'])->findOrFail($id);

        return $this->success(
            message: 'User fetched successfully',
            data: new UserResource($user)
        );
    }


    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->userService->resetPassword($user);

        return $this->success(
            message: "Password reset successfully"
        );
    }


    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,pending,blocked,deleted, disabled'
        ]);

        $user = User::findOrFail($id);
        $authUser = Auth::guard('user')->user();

        // 2. Prevent self-status update
        if ($authUser->id === $user->id) {
            return $this->error(
                message: "You cannot update your own account status.",
                statusCode: 403
            );
        }


        $before = $user->toArray();

        $user->update([
            'status' => $validated['status']
        ]);

        $after = $user->fresh()->toArray();

        $this->audit->trail(
            module: 'User',
            action: 'Update Status',
            auditableType: User::class,
            auditableId: $user->id,
            before: $before,
            after: $after,
            description: "User {$user->first_name} {$user->last_name} ({$user->username}) status updated to {$validated['status']} by {$authUser->username}"
        );

        return $this->success(
            message: 'User status updated successfully',
            data: new UserResource($user)
        );
    }

    public function delete(string $id)
    {
        $authUser = Auth::guard('user')->user();
        $user = User::findOrFail($id);
        if ($authUser->id === $user->id) {
            return $this->error(message: "You cannot delete your own account.", statusCode: 403);
        }

        $before = $user->toArray();
        $targetName = "{$user->first_name} {$user->last_name}";
        $targetId = $user->id;

        $user->delete();

        $this->audit->trail(
            module: 'User',
            action: 'Delete',
            auditableType: User::class,
            auditableId: $targetId,
            before: $before,
            after: null,
            description: "User {$targetName} was deleted by {$authUser->username} (ID: {$authUser->id})"
        );

        return $this->success(
            message: 'User deleted successfully'
        );
    }


}
