<?php
/*--------------------------------------------------------------------------------
 *  File          : RelationTypeSynonym.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating relation_type_synonym table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 1.4.1
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationTypeSynonym extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_type_synonym';
	protected $primaryKey = "rtSynonymId";
	protected $modifiers  = ['rtSynonymId', 'rtSynonymDescription', 'rtSynonymRelationTypeId', 
	                       'rtSynonymTenseId', 'rtSynonymTenseId','rtSynonymDisplayName',
	                         'ownership', 'ownerId', 'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
        return ($this->where('rtSynonymId', '=', $id)->get())->toArray(); 
     }
	 
	//--------------------------------------------------------------------

	public function findTermById($termId)
     {
        return ($this->where('rtSynonymTermId', '=', $termId)->get())->toArray(); 
     }
	

	//--------------------------------------------------------------------
	public function retrieveRelationTypeIdByTermdId($termId)
     {
        $rtId = 0;
        $rs = $this->where('rtSynonymTermId', '=', $termId)->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){						  
               $rtId = $rs0->rtSynonymRelationTypeId;
            }			
        }
        return $rtId;
     }

 	//--------------------------------------------------------------------
	public function getRelationTypeIdByTermdId($termId)
     {

        return $this->where('rtSynonymTermId', '=', $termId)->get(); 
 
     } 

	//--------------------------------------------------------------------
	public function findRelationTypeByTermName($termName)
     {

        return $this->where('rtSynonymDisplayName', '=', $termName)->get(); 

     }
	 

	 
}
