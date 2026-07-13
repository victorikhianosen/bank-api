<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use App\Services\TelegramService;
use App\Services\UserService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use HttpResponses;

    public function __construct(
        private UserService $userService,
        private AuditService $audit,

    ) {}

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|exists:users,username',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error(
                message: 'Invalid credentials',
                statusCode: 401
            );
        }

        if ($user->status !== 'active') {
            return $this->error(
                message: 'Account not active',
                statusCode: 403
            );
        }

        $token = $user->createToken('user-token')->plainTextToken;
        $this->userService->updateLastLogin($user);
        $loginTime = now()->format('Y-m-d H:i:s');

        $this->audit->trail(
            module: 'Authentication',
            action: 'Login',
            auditableType: User::class,
            auditableId: $user->id,
            before: null,
            after: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => $loginTime
            ],
            description: "User with username {$user->username} and ID: {$user->id} logged in at {$loginTime}"
        );

        return $this->success(
            message: 'Login successful',
            data: [
                'user' => $user,
                'access_token' => $token,
            ]
        );
    }


    public function logout(Request $request)
    {


        $user = Auth::guard('user')->user();
        $loginTime = $user->last_login ?? null;
        $logoutTime = now();

        try {
            $this->audit->trail(
                module: 'Authentication',
                action: 'Logout',
                auditableType: User::class,
                auditableId: $user->id,
                before: [
                    'login_at' => $loginTime
                ],
                after: [
                    'logout_at' => $logoutTime,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ],
                description: "User with username {$user->username} and ID: {$user->id} logged out at {$logoutTime} (login was {$loginTime})"
            );
        } catch (\Throwable $e) {
            logger()->error('Audit failed: ' . $e->getMessage());
        }

        optional($user->currentAccessToken())->delete();

        return $this->success('Logout successfully, see you soon!');
    }
}
