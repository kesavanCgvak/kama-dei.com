<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $connection = 'mysqlCollection';
    // Define the table name if it's different from the plural of the model name
    protected $table = 'audit_logs';

    // Define the primary key (if not 'id')
    protected $primaryKey = 'id';

    // Disable timestamps if you don't need Laravel's automatic created_at and updated_at fields
    public $timestamps = false;


    // Specify the fillable fields
    protected $fillable = [
        'action_id',
        'user_id',
        'old_data',
        'new_data',
        'action_description',
        'ip_address',
        'created_at',
        'action_type',
    ];

    // Optionally, you can cast some attributes to JSON or array (useful for old_data and new_data)
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

      // Define the relationship with the Action model
      public function action()
      {
          return $this->belongsTo(Action::class, 'action_id');
      }

}
