<?php
/*--------------------------------------------------------------------------------
 *  File          : RelationTypeGroup.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating relation_type_group table.
 *  Developer     : Gabriel Carrillo.
 *  Company       : Kamazooie Development Corporation
 *  Version       : 1.4.1
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationTypeGroup extends Model

{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_type_group';
	protected $primaryKey = "relationTypeGoupId";
	protected $modifiers  = ['relationAssociationTermId','relationTypeId',
                             'description','ssReserved','ownership','ownerId','dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('relatioTypeGroupId', '=', $id)->get(); 
     }

	//--------------------------------------------------------------------
	public function retrieveByRelationType($relationTypeId)
     {
        $rtId = 0;
        $rs = $this->where('relationTypeId', '=', $relationTypeId)->get(); 
        foreach ($rs as $rs0){                        
            $rtId = $rs0->relationTypeGroupId;
        }
        return $rtId;
     }
	 
	  
	 
}
