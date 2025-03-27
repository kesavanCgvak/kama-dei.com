<?php
/*--------------------------------------------------------------------------------
 *  File          : RelationTermLink.php        
 *  Type          : Model
 *  Function      : Provide  functions for manipulating relation_term table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 2.1
 *  Updated       : 29 October 2020
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationTermLink extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'relation_term_link';
    protected $primaryKey = "relationTermLinkId";
    protected $modifiers  = ['relationTermLinkId', 'relationId', 'krtermLinkId','termId', 
	                         'ownership', 'ownerId', 'dateCreated','lastUserId'];
    protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
        return $this->where('relationTermLinkId', '=', $id)->get(); 
     }
	
	//--------------------------------------------------------------------
	public function retrieveTerm($orgId,$relationId,$linkTermId)
     {


        $termId = 0;
        $rs = $this->where('relationId', '=', $relationId)
                   ->where('krtermLinkId', '=', $linkTermId)
                   ->get(); 
        if (!empty($rs)) {  
           // 
            foreach ($rs as $rs0){						  
               $termId = $rs0->termId;   
            }			
        }
        return $termId;
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
