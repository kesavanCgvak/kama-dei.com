<?php
namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;

class Responsiblity extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'responsiblity';
    protected $primaryKey = "id";
}