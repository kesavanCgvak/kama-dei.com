<?php

namespace App\Traits;

use App\Models\AuditLog;

trait AuditLoggable
{
    /**
     * Log an audit entry.
     *
     * @param string $action
     * @param string $tableName
     * @param int $recordId
     * @param array|null $oldData
     * @param array|null $newData
     * @param string $actionType
     * @return void
     */
    public function logAudit(
        string $action,
        ?array $oldData = null,
        ?array $newData = null,
        string $actionType = 'UPDATE'
    ): void {
        if ($newData instanceof \Illuminate\Support\Collection) {
            $newData = $newData->toArray();
        }
        AuditLog::create([
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'action_type' => $actionType,
        ]);
    }
}
