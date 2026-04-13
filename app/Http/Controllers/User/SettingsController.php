<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Services\AuditService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    use HttpResponses;

    public function __construct(
        private AuditService $audit
    ) {}


    public function index()
    {
        $settings = Settings::all();
        if ($settings->isEmpty()) {
            return $this->error(message: 'No settings found', data: []);
        }
        return $this->success(message: 'Settings fetched successfully', data: $settings);
    }



    public function store(Request $request)
    {
        $request->merge([
            'setting_key' => Str::slug($request->setting_key, '_'),
            'setting_group' => Str::slug($request->setting_group, '_'),
        ]);


        $request->validate([
            'setting_group' => 'required|string',
            'setting_key' => 'required|string|unique:settings,setting_key',
            'setting_value' => 'nullable|string',
        ]);

        $actor = Auth::guard('user')->user();

        $setting = Settings::create([
            'setting_group' => strtolower($request->setting_group),
            'setting_key' => strtolower(str_replace(' ', '_', $request->setting_key)),
            'setting_value' => $request->setting_value,
        ]);

        $this->audit->trail(
            module: 'Settings',
            action: 'Create',
            auditableType: Settings::class,
            auditableId: $setting->id,
            before: [],
            after: $setting->toArray(),
            description: "User {$actor->username} created a new setting '{$setting->setting_key}'"
        );

        return $this->success(
            message: 'New setting added successfully',
            data: $setting
        );
    }



public function update(Request $request)
{
    $actor = Auth::guard('user')->user();

    $data = $request->all();

    // handle JSON string
    if (isset($data[0]) && is_string($data[0])) {
        $data = json_decode($data[0], true);
    }

    foreach ($data as $id => $value) {

        $setting = Settings::find($id);
        if (!$setting) continue;

        // ✅ 1. HANDLE NORMAL FILE UPLOAD
        if ($request->hasFile($id)) {
            $file = $request->file($id);
            $path = $file->store('settings', 'public');
            $value = $path;
        }

        // ✅ 2. HANDLE BASE64 IMAGE (THIS IS YOUR CASE)
        elseif (is_string($value) && str_starts_with($value, 'data:image')) {

            // extract image type
            preg_match('/data:image\/(\w+);base64,/', $value, $type);

            $extension = $type[1] ?? 'png';

            // remove base64 header
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $value);

            $base64 = str_replace(' ', '+', $base64);

            // decode
            $fileData = base64_decode($base64);

            // generate filename
            $fileName = 'settings/' . Str::random(20) . '.' . $extension;

            // store file
            Storage::disk('public')->put($fileName, $fileData);

            $value = $fileName; // ✅ SHORT PATH
        }

        // skip if same
        if ($setting->setting_value == $value) continue;

        $before = $setting->toArray();

        $setting->update([
            'setting_value' => $value
        ]);

        $this->audit->trail(
            module: 'Settings',
            action: 'Update',
            auditableType: Settings::class,
            auditableId: $setting->id,
            before: $before,
            after: $setting->fresh()->toArray(),
            description: "User {$actor->username} updated '{$setting->setting_key}'"
        );
    }

    return $this->success(message: 'Settings updated successfully');
}

}
