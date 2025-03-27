<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        return view('audit-logs.index');
    }

    public function getData(Request $request)
    {
        $columns = [
            0 => 'id',
            1 => 'description',
            2 => 'action_description',
            3 => 'user_id',
            4 => 'ip_address',
            5 => 'created_at',
            6 => 'action_type',
        ];

        $totalData = AuditLog::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $order = $columns[$request->input('order.0.column', 0)];
        $dir = $request->input('order.0.dir', 'asc');

        if (empty($request->input('search.value'))) {
            $auditLogs = AuditLog::leftJoin('actions', 'audit_logs.action_id', '=', 'actions.id')
                ->select('audit_logs.*', 'actions.name as action_name', 'actions.description')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');

            $auditLogs = AuditLog::leftJoin('actions', 'audit_logs.action_id', '=', 'actions.id')
                ->select('audit_logs.*', 'actions.name as action_name', 'actions.description')
                ->where('audit_logs.id', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.action_id', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.user_id', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.ip_address', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.created_at', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.action_type', 'LIKE', "%{$search}%")
                ->orWhere('actions.name', 'LIKE', "%{$search}%")
                ->orWhere('actions.description', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = AuditLog::leftJoin('actions', 'audit_logs.action_id', '=', 'actions.id')
                ->select('audit_logs.*', 'actions.name as action_name', 'actions.description')
                ->where('audit_logs.id', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.action_id', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.user_id', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.ip_address', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.created_at', 'LIKE', "%{$search}%")
                ->orWhere('audit_logs.action_type', 'LIKE', "%{$search}%")
                ->orWhere('actions.name', 'LIKE', "%{$search}%")
                ->orWhere('actions.description', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = [];
        if (!empty($auditLogs)) {
            foreach ($auditLogs as $auditLog) {
                $nestedData['id'] = $auditLog->id;
                $nestedData['description'] = $auditLog->description;
                $nestedData['action_description'] = $auditLog->action_description;
                $nestedData['user_id'] = $auditLog->user_id;
                $nestedData['ip_address'] = $auditLog->ip_address;
                $nestedData['created_at'] = $auditLog->created_at;
                $nestedData['action_type'] = $auditLog->action_type;
                $nestedData['old_data'] = json_encode($auditLog->old_data, JSON_PRETTY_PRINT);
                $nestedData['new_data'] = json_encode($auditLog->new_data, JSON_PRETTY_PRINT);

                $data[] = $nestedData;
            }
        }

        $json_data = [
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
        ];

        return response()->json($json_data);
    }
}
