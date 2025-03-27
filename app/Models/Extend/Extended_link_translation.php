<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/11/8
 * Time: 1:19 PM
 */

namespace App\Models\Extend;
use Illuminate\Database\Eloquent\Model;

class Extended_link_translation extends Model
{
    protected $connection = 'mysql2';
    protected $table      = 'extended_link_translation';
    protected $primaryKey = "id";
	public    $timestamps = false;
}