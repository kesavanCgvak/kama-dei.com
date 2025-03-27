<?php
namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'draft';
    protected $primaryKey = "id_draft";
}