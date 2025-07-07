<?php
/*--------------------------------------------------------------------------------
 *  File          : Portal.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating portal table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation. KDC
 *  Version       : 3.10
 *  Updated       : 12 November 2024
 *---------------------------------------------------------------------------------*/

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portalorg extends Model
{
	//----------------------------------------------------
	public    $timestamps = false;
	//----------------------------------------------------
	protected $table      = 'portal';
	protected $primaryKey = "id";

	//----------------------------------------------------
	public function getOrgPersona($orgId){
        $rs =  $this->where('organization_id', '=', $orgId)
                    ->get();
        return $rs; 
	}
	//----------------------------------------------------
}
