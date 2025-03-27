<?php
/*--------------------------------------------------------------------------------
 *  File          : RelationType.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating relation_type table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 1.4.1
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationType extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_type';
	protected $primaryKey = "relationTypeId";
	protected $modifiers  = ['relationTypeId', 'relationTypeName', 'relationTypeIsReserved', 
	                         'relationTypeOperand', 'ownership', 'ownerId', 'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
        return ($this->where('relationTypeId', '=', $id)->get())->toArray(); 
     }
	
	//--------------------------------------------------------------------
	public function findByRelationTypeName($name)
     {
        return ($this->where('relationTypeName', '=', $name)->get())->toArray(); 
     }

	//--------------------------------------------------------------------
	public function retrieveIdByName($name)
     {
        $rtId = 0;
        $rs = $this->where('relationTypeName', '=', $name)->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){						  
               $rtId = $rs0->relationTypeId;
            }			
        }
        return $rtId;
     }
	 
	//--------------------------------------------------------------------
	public function retrieveNameById($id)
     {
        $name = "";
        $rs = $this->where('relationTypeId', '=', $id)->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){						  
               $name = $rs0->relationTypeName;
            }			
        }
        return $name;
     }
	 
}
