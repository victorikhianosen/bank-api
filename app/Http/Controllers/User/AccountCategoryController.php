<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AccountCategory;
use App\Services\AuditService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccountCategoryController extends Controller
{
    use HttpResponses;

    public function __construct(private AuditService $audit) {}

    // LIST
    public function index()
    {
        $categories = AccountCategory::latest()->paginate(50);

        if ($categories->isEmpty()) {
            return $this->success(
                message: 'No account categories found',
                data: []
            );
        }

        return $this->success(
            message: 'Account categories fetched successfully',
            data: $categories
        );
    }

    // SHOW
    public function show($id)
    {
        $category = AccountCategory::findOrFail($id);

        return $this->success(
            message: 'Account category fetched successfully',
            data: $category
        );
    }

    // STORE


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
        ]);

        $authUser = Auth::guard('user')->user() ?? 'System';
        $user = Auth::guard('user')->user()?->username;

        $data = [
            'name'       => $request->name,
            'code'       => $this->generateCategoryCode($request->name),
            'created_by' => $authUser->id,
            'updated_by' => null,
        ];

        $category = AccountCategory::create($data);

        // Audit
        $this->audit->trail(
            module: 'Account Category',
            action: 'Create',
            auditableType: AccountCategory::class,
            auditableId: $category->id,
            before: null,
            after: $category->toArray(),
            description: "Account category {$category->name} ({$category->code}) was created by {$user}."
        );

        return $this->success(
            message: 'Account category created successfully',
            data: $category
        );
    }

    // UPDATE NAME ONLY
    public function update(Request $request, $id)
    {
        $category = AccountCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('account_categories')->ignore($category->id),
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

        $before = $category->toArray();

        $authUser = Auth::guard('user')->user() ?? 'System';
        $user = Auth::guard('user')->user()?->username;


        $category->update([
            'name'       => $request->name,
            'updated_by' => $authUser->id
        ]);

        // Audit
        $this->audit->trail(
            module: 'Account Category',
            action: 'Update',
            auditableType: AccountCategory::class,
            auditableId: $category->id,
            before: $before,
            after: $category->toArray(),
            description: "Account category name changed to {$category->name} ({$category->code}) by {$user}."
        );

        return $this->success(
            message: 'Account category name updated successfully',
        );
    }

    // UPDATE STATUS ONLY
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,active,inactive'
        ]);

        $category = AccountCategory::findOrFail($id);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '422',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $before = $category->toArray();

        $user = Auth::guard('user')->user()?->username ?? 'System';

        $category->update([
            'status'     => $request->status,
            'updated_by' => $user
        ]);

        // Audit
        $this->audit->trail(
            module: 'Account Category',
            action: 'Update Status',
            auditableType: AccountCategory::class,
            auditableId: $category->id,
            before: $before,
            after: $category->toArray(),
            description: "Account category {$category->name} ({$category->code}) status changed to {$request->status} by {$user}."
        );

        return $this->success(
            message: 'Account category status updated successfully',
            data: $category
        );
    }


    private function generateCategoryCode(string $name): string
    {
        $words = explode(' ', strtoupper($name));

        if (count($words) === 1) {
            $code = substr($words[0], 0, 3);
        } else {
            $code = '';
            foreach ($words as $word) {
                $code .= substr($word, 0, 1);
            }
        }

        $originalCode = $code;
        $counter = 1;

        while (AccountCategory::where('code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
        }

        return $code;
    }
}
