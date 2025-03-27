<?php

/*--------------------------------------------------------------------------------
 *  File          : PersonalityRelation.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating personality_relation_value table.
 *                  Records are retrieved in array format.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 2.3
 *  Updated       : 20 May 2021
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalityRelation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'personality_relation';
	protected $primaryKey = "personalityRelationId";
	protected $modifiers  = ['personalityRelationValueId', 'personalityId', 'relationId',
	                          'ownership', 'ownerId', 'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
	    return ($this->where('personalityRelationId', '=', $id)->get())->toArray(); 
	 }
	
	//--------------------------------------------------------------------
	public function getByPersonality($personalityId)
     {
	    return $this->where('personalityId', '=', $personalityId)->get(); 
	 }
	 
	//--------------------------------------------------------------------
	public function getByPersonalityRelation($personaId,$personalityId,$relationId)
     {
		$rs = null;
		$rs =  $this->where('personalityId', '=', $personalityId)
                    ->where('relationId', '=', $relationId)
                    ->get();

        if($rs->isEmpty()) {
		    $rs =  $this->where('personalityId', '=', $personaId)
                    ->where('relationId', '=', $relationId)
                    ->get();        	
        }

        return $rs;                   	   				
	 }

	//--------------------------------------------------------------------
	public function retrievePersonalityRelationId($personaId,$personalityId, $relationId)
     {
        $personalityRelationId = 0;
        $rs = null;
		$rs =  $this->where('personalityId', '=', $personalityId)
                    ->where('relationId', '=', $relationId)
                    ->get(); 

        if($rs->isEmpty()) {
		    $rs =  $this->where('personalityId', '=', $personaId)
                    ->where('relationId', '=', $relationId)
                    ->get();        	
        }
					

        if (!empty($rs)) {               
            foreach ($rs as $rs0){						  
			   $personalityRelationId = $rs0->personalityRelationId;
            }			
        }

        return $personalityRelationId ;				
	 }	

	//--------------------------------------------------------------------
	public function insertPersonalityRelation($personalityId, $relationId, 
				  $orgId,$ownership, $ownerId, $dateCreated, $lastUserId)
             	
	 {
           $oPR = new PersonalityRelation();
           $oPR->personalityId    = $personalityId;
           $oPR->relationId       = $relationId;
           $oPR->ownership        = $ownership;
           $oPR->ownerId          = $ownerId;
           $oPR->dateCreated      = $dateCreated;
           $oPR->lastUserId       = $lastUserId;	
           $oPR->save();
           return $oPR->personalityRelationId;		   
     }
	 
    //--------------------------------------------------------------------
    public function deleteById($id)
     {		
        return $this->where('personalityRelationId', '=', $id)->delete(); 	
     }	 
	 
}
