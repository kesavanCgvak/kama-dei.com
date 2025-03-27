<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $connection = 'mysqlCollection';
    // Define the table name if it does not follow Laravel's convention
    protected $table = 'actions';

    // Specify the primary key if it's not "id"
    protected $primaryKey = 'id';

    // Specify the fillable fields
    protected $fillable = [
        'name',
        'description',
    ];

    // Define the relationship with the AuditLog model
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'action_id');
    }
}
