<?php

namespace App\Services;

use App\Enums\AuditChannel;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public function trail(
        string $module,
        string $action,
        string $auditableType,
        int|string|null $auditableId = null,
        mixed $before = null,
        mixed $after = null,
        ?string $description = null
    ): void {

        $performer = $this->resolvePerformer();

        AuditTrail::create([
            'branch_id' => $this->resolveBranch(),
            'performed_by_type' => $performer['type'],
            'performed_by_id' => $performer['id'],

            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,

            'module' => $module,
            'actions' => $action,

            'before_change' => $before ? json_encode($before) : null,
            'after_change' => $after ? json_encode($after) : null,

            'description' => $description,
            'ip' => request()->ip(),
            'agent' => request()->userAgent(),
            'channel' => AuditChannel::resolve(request()->header('X-Channel'))

        ]);
    }

    protected function resolvePerformer(): array
    {
        if (Auth::guard('user')->check()) {
            return [
                'type' => 'user',
                'id' => Auth::guard('user')->id(),
            ];
        }

        if (Auth::guard('customer')->check()) {
            return [
                'type' => 'customer',
                'id' => Auth::guard('customer')->id(),
            ];
        }

        return [
            'type' => 'system',
            'id' => null,
        ];
    }

    protected function resolveBranch(): ?int
    {
        if (Auth::guard('user')->check()) {
            return Auth::guard('user')->user()->branch_id;
        }

        return null;
    }

    public function log($title = "No Title", $content = '')
    {
        Log::info('');
        Log::info('*******************************************');
        Log::info($title);

        if (!empty($content)) {
            if (is_array($content) || is_object($content)) {
                Log::info(json_encode($content, JSON_PRETTY_PRINT));
            } else {
                Log::info($content);
            }
        }

        Log::info('*******************************************');
        Log::info('');
    }
}
