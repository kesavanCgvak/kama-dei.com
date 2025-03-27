<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class PortalType extends Model
{
	//----------------------------------------------------
	public    $timestamps = false;
	//----------------------------------------------------
	protected $table      = 'portalType';
	protected $primaryKey = "id";
	//----------------------------------------------------
}
