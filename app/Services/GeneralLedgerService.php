<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\GeneralLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GeneralLedgerService
{
    public function __construct(
        private AuditService $audit
    ) {}

    public function store(array $data): GeneralLedger
    {
        return DB::transaction(function () use ($data) {

            $authUser = Auth::guard('user')->user();

            /*
            |--------------------------------------------------------------------------
            | Normalize
            |--------------------------------------------------------------------------
            */

            $name = strtolower(
                str_replace(' ', '_', trim($data['name']))
            );

            $type = strtolower(
                str_replace(' ', '_', trim($data['type']))
            );

            /*
            |--------------------------------------------------------------------------
            | Parent Validation
            |--------------------------------------------------------------------------
            */

            if (! empty($data['parent_id'])) {

                $parent = GeneralLedger::findOrFail(
                    $data['parent_id']
                );

                if (
                    $parent->currency_id &&
                    $parent->currency_id != $data['currency_id']
                ) {

                    throw ValidationException::withMessages([
                        'currency_id' => [
                            'Parent ledger currency mismatch'
                        ]
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Create Ledger
            |--------------------------------------------------------------------------
            */

         
            $currency = Currency::findOrFail(
                $data['currency_id']
            );

            $ledger = GeneralLedger::create([

                'name' => $name,

                'gl_code' => $data['gl_code'],

                'balance' => 0,

                'currency_id' => $currency->id,

                'currency_code' => $currency->code,

                'parent_id' => $data['parent_id'] ?? null,

                'classification' => $data['classification'],

                'type' => $type,

                'manual_journal_entries_allowed' =>
                    $data['manual_journal_entries_allowed'] ?? true,

                'status' => $data['status'] ?? 'pending',

                'description' => $data['description'] ?? null,
            ]);



            /*
            |--------------------------------------------------------------------------
            | Hierarchy
            |--------------------------------------------------------------------------
            */

            if ($ledger->parent_id) {

                $parent = GeneralLedger::find(
                    $ledger->parent_id
                );

                $ledger->update([
                    'hierarchy' => $parent->hierarchy
                        ? "{$parent->hierarchy}.{$ledger->id}"
                        : "{$parent->id}.{$ledger->id}"
                ]);
            } else {

                $ledger->update([
                    'hierarchy' => (string) $ledger->id
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Audit Trail
            |--------------------------------------------------------------------------
            */

            $this->audit->trail(
                module: 'General Ledger',
                action: 'Create',
                auditableType: GeneralLedger::class,
                auditableId: $ledger->id,
                before: null,
                after: $ledger->fresh()->toArray(),
                description: "General Ledger {$ledger->name} created by {$authUser->username}"
            );

            return $ledger->fresh();
        });
    }

    public function update(
        GeneralLedger $ledger,
        array $data
    ): GeneralLedger {

        return DB::transaction(function () use ($ledger, $data) {

            $authUser = Auth::guard('user')->user();

            $before = $ledger->toArray();

            /*
            |--------------------------------------------------------------------------
            | Prevent Self Parenting
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['parent_id']) &&
                $data['parent_id'] == $ledger->id
            ) {

                throw ValidationException::withMessages([
                    'parent_id' => [
                        'Ledger cannot be parent of itself'
                    ]
                ]);
            }

            $ledger->update([

                'name' => isset($data['name'])
                    ? strtolower(str_replace(' ', '_', trim($data['name'])))
                    : $ledger->name,

                'gl_code' => $data['gl_code']
                    ?? $ledger->gl_code,

                'currency_id' => $data['currency_id']
                    ?? $ledger->currency_id,

                'currency_code' => $data['currency_code']
                    ?? $ledger->currency_code,

                'parent_id' => $data['parent_id']
                    ?? $ledger->parent_id,

                'classification' => $data['classification']
                    ?? $ledger->classification,

                'type' => isset($data['type'])
                    ? strtolower(str_replace(' ', '_', trim($data['type'])))
                    : $ledger->type,

                'manual_journal_entries_allowed' =>
                $data['manual_journal_entries_allowed']
                    ?? $ledger->manual_journal_entries_allowed,

                'status' => $data['status']
                    ?? $ledger->status,

                'description' => $data['description']
                    ?? $ledger->description,
            ]);

            $this->audit->trail(
                module: 'General Ledger',
                action: 'Update',
                auditableType: GeneralLedger::class,
                auditableId: $ledger->id,
                before: $before,
                after: $ledger->fresh()->toArray(),
                description: "General Ledger {$ledger->name} updated by {$authUser->username}"
            );

            return $ledger->fresh();
        });
    }

    public function updateStatus(
        GeneralLedger $ledger,
        string $status
    ): GeneralLedger {

        return DB::transaction(function () use ($ledger, $status) {

            $before = $ledger->toArray();

            $ledger->update([
                'status' => $status
            ]);

            $this->audit->trail(
                module: 'General Ledger',
                action: 'Update Status',
                auditableType: GeneralLedger::class,
                auditableId: $ledger->id,
                before: $before,
                after: $ledger->fresh()->toArray(),
                description: "General Ledger status updated to {$status}"
            );

            return $ledger->fresh();
        });
    }

    public function delete(GeneralLedger $ledger): void
    {
        DB::transaction(function () use ($ledger) {

            $before = $ledger->toArray();

            $ledger->update([
                'status' => 'deleted'
            ]);

            $this->audit->trail(
                module: 'General Ledger',
                action: 'Delete',
                auditableType: GeneralLedger::class,
                auditableId: $ledger->id,
                before: $before,
                after: null,
                description: "General Ledger {$ledger->name} deleted"
            );
        });
    }
}
