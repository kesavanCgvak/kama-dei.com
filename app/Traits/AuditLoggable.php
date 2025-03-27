<?php

namespace App\Traits;

use App\Models\AuditLog;
use App\Models\Action;

trait AuditLoggable
{
    /**
     * Log an audit entry.
     *
     * @param string $actionName
     * @param array|null $oldData
     * @param array|null $newData
     * @param string $action_description
     * @param string $actionType
     * @return void
     */
    public function logAudit(
        string $actionName,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $action_description = null,
        string $actionType = 'UPDATE'
    ): void {
        // Retrieve the action ID from the actions table using the provided name
        $action = Action::where('name', $actionName)->first();

        if (!$action) {
            // Optionally, handle the case where the action name is not found
            throw new \Exception("Action with name '{$actionName}' not found.");
        }

        // Convert newData to an array if it's a Collection
        if ($newData instanceof \Illuminate\Support\Collection) {
            $newData = $newData->toArray();
        }

        // Create the audit log entry
        AuditLog::create([
            'action_id' => $action->id, // Use the retrieved action ID
            'user_id' => session()->get('userID'),
            'old_data' => $oldData,
            'new_data' => $newData,
            'action_description' => $action_description,
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'action_type' => $actionType,
        ]);
    }
}
