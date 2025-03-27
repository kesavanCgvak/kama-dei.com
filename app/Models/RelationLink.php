<?php
/*--------------------------------------------------------------------------------
 *  File          : RalationLink.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating relation table.
 *                  Records are retrieved in array format.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 2.6
 *  Updated       : 15 August 2023
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationLink extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_link';
	protected $primaryKey = "relationLinkId";
	protected $modifiers  = ['leftRelationId', 'linkTermId', 'rightRelationId', 
	                         'linkOrder', 'ownership', 'ownerId', 'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('relationLinkId', '=', $id)->get(); 
     }


	//--------------------------------------------------------------------
	public function getByLeftRelationId($id)
     {
         return $this->where('leftRelationId', '=', $id)->get(); 
     }	

	//--------------------------------------------------------------------
	public function getByLeftRelationIdOrder($id)
     {
         return $this->where('leftRelationId', '=', $id)
                     ->orderBy('linkOrder', 'ASC')
                     ->get(); 
     }	



    //--------------------------------------------------------------------
     public function getRightRelationId($leftId, $linkId)
     {
        $rs = $this->where('leftRelationId','=', $leftId)
                   ->where('linkTermId', '=', $linkId)
                   ->orderBy('linkOrder', 'ASC')
                   ->get();
        
        return $rs;
     }

    //--------------------------------------------------------------------
     public function retrieveRightRelationId($leftId, $linkId)
     {
        $rs = $this->where('leftRelationId','=', $leftId)
                   ->where('linkTermId', '=', $linkId)
                   ->get();

        $RRelationId = 0;
        foreach($rs as $rs0) {
            $RRelationId = $rs0->rightRelationId;
        }
        
        return $RRelationId;
     }
	 
	//--------------------------------------------------------------------
	public function retrieveLinkTypeName($id)
     {
        $linkTypeName = "";
        $rs =  $this->where('relationLinkId', '=', $id)	
                     ->get(); 
					 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){						  
               $linkTypeName = $rs0->$linkTypeName;
            }			
        }
		return $linkTypeName;
     } 
	 
	 
	 
}
