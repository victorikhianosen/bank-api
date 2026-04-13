<?php

namespace App\Services;

use App\Mail\CoreBankMail;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserService
{

    public function __construct(private AuditService $audit) {}
    public function store(array $data)
    {

        return DB::transaction(function () use ($data) {

            $authUser = Auth::guard('user')->user();


            $baseUsername = Str::lower(
                preg_replace('/\s+/', '', $data['first_name'] . $data['last_name'])
            );

            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }


            $photoPath = null;

            if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
                $photoPath = $data['photo']->store('users/photos', 'public');
            }

            $user = User::create([
                'branch_id'   => $authUser->branch_id ?? 1,
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'username'    => $username,
                'email'       => $data['email'],
                'phone'       => $data['phone'],
                'gender'      => $data['gender'],
                'photo'       => $photoPath,
                'status'      => 'pending',
                'password'    => Hash::make($data['password']),
                'created_by'  => $authUser->username,
            ]);

            $user = User::findOrFail($user->id);
            $user->syncRoles(isset($data['role_id']) ? (int) $data['role_id'] : null);

            $this->audit->trail(
                module: 'User',
                action: 'Create',
                auditableType: User::class,
                auditableId: $user->id,
                before: null,
                after: $user->toArray(),
                description: "User {$user->first_name} {$user->last_name} was created"
            );

            $content = "
                        <p>Your account has been successfully created.</p>

                        <p><strong>Username:</strong> {$user->username}</p>
                        <p><strong>Email:</strong> {$user->email}</p>

                        <p>Please login and change your password. {$data['password']}</p>
                        ";

            Mail::to($user->email)->send(
                new CoreBankMail(
                    subjectLine: "Account Created",
                    content: $content,
                    name: $user->first_name
                )
            );

            return $user->fresh();
        });
    }

    public function update($id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {

            $authUser = Auth::guard('user')->user();

            $user = User::where('id', $id)->first();

            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {

                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }

                $photoPath = $data['photo']->store('users/photos', 'public');
                $user->photo = $photoPath;
            }

            $user->update([
                'first_name' => $data['first_name'] ?? $user->first_name,
                'last_name'  => $data['last_name'] ?? $user->last_name,
                'middle_name' => $data['middle_name'] ?? $user->middle_name,
                'email'      => $data['email'] ?? $user->email,
                'phone'      => $data['phone'] ?? $user->phone,
                'gender'     => $data['gender'] ?? $user->gender,
                'address'    => $data['address'] ?? $user->address,
                'city'       => $data['city'] ?? $user->city,
                'state'      => $data['state'] ?? $user->state,
                'country'    => $data['country'] ?? $user->country,
                'status'     => $data['status'] ?? $user->status,
                'updated_by' => $authUser->username ?? null,
                'photo'      => $user->photo,
            ]);

            if (array_key_exists('role_id', $data)) {
                $user->syncRoles(isset($data['role_id']) ? (int) $data['role_id'] : null);
            }

            $this->audit->trail(
                module: 'User',
                action: 'Update',
                auditableType: User::class,
                auditableId: $user->id,
                before: null,
                after: $user->toArray(),
                description: "User {$user->first_name} {$user->last_name} was updated"
            );

            return $user->fresh();
        });
    }


    public function resetPassword(User $user): User
    {
        return DB::transaction(function () use ($user) {

            $authUser = Auth::guard('user')->user();

            $password = Str::random(10);

            $before = $user->toArray();

            // Update password
            $user->update([
                'password' => Hash::make($password),
            ]);

            // Audit log
            $this->audit->trail(
                module: 'User',
                action: 'Reset Password',
                auditableType: User::class,
                auditableId: $user->id,
                before: $before,
                after: null,
                description: "Password for {$user->username} was reset by {$authUser->username}"
            );

            $content = "
        <p>Your password has been reset successfully.</p>

        <p><strong>Username:</strong> {$user->username}</p>
        <p><strong>New Password:</strong> {$password}</p>

        <p>Please login and change your password immediately.</p>
        ";

            Mail::to($user->email)->send(
                new CoreBankMail(
                    subjectLine: "Password Reset Notification",
                    content: $content,
                    name: $user->first_name
                )
            );

            return $user->fresh();
        });
    }



    public function updateLastLogin(User $user): void
    {
        $user->update([
            'last_login' => now(),
        ]);
    }
}
