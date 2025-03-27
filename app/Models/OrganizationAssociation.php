<?php

/*--------------------------------------------------------------------------------
 *  File          : OrganizationAssociation.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating organization association.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation. 
 *  Version       : 3.0
 *  Updated       : 16 October 2023
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationAssociation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'organization_association';
	protected $primaryKey = "orgAssociationId";
	protected $modifiers  = ['orgAssociationId', 'leftOrgId', 'relationTypeGroupId', 'rightOrgId',
	                          'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	 
	//--------------------------------------------------------------------	

    public function getById($id)
     {

        $rs = $this->where('orgAssociationId', '=', $id)
                   ->get();     
        return $rs;
     }

    //--------------------------------------------------------------------   
    public function getByTriple($leftId,$rtgId,$rightId)
     {

        $rs = $this->where('leftOrgId', '=', $leftId)
                   ->where('relationTypeGroupId', '=', $rtgId)
                   ->where('rightOrgId', '=', $rightId)
                   ->get();     
        return $rs;
     } 

}
