<?php

/*--------------------------------------------------------------------------------
 *  File          : PersonalityRelationValue.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating personality_relation_value table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 1.4.1
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalityRelationValue extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'personality_relation_value';
	protected $primaryKey = "personalityRelationValueId";
	protected $modifiers  = ['personalityRelationValueId', 'personalityRelationId', 'personRelationTermId',
	                         'scalarValue', 'ownership', 'ownerId', 'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
	    return ($this->where('personalityRelationValueId', '=', $id)->get())->toArray(); 
	 }
	
	//--------------------------------------------------------------------
	public function getByPersonality($personalityId)
     {
	    return ($this->where('personalityId', '=', $personalityId)->get())->toArray; 
	 }
	 
	//--------------------------------------------------------------------
	public function getByPersonalityRelation($personalityRelationId)
     {
	    return $this->where('personalityRelationId', '=', $personalityRelationId)
                    ->get(); 
	 }

	//--------------------------------------------------------------------
	public function getByPersonalityRelationTerm($personalityRelationId)
     {
	    return $this->join('term', 'term.termId','=','personRelationTermId')
	             ->where('personalityRelationId', '=', $personalityRelationId)
                 ->get(); 
	 }
	 
	//--------------------------------------------------------------------
	public function insertPRV($personalityRelationId, $personRelationTermId, $scalarValue,  
				            $ownership, $ownerId, $dateCreated, $lastUserId)
             	
	 {
           $oPRV = new PersonalityRelationValue();
           $oPRV->personalityRelationId    = $personalityRelationId;
           $oPRV->personRelationTermId     = $personRelationTermId;
		   $oPRV->scalarValue              = $scalarValue;
           $oPRV->ownership                = $ownership;
           $oPRV->ownerId                  = $ownerId;
           $oPRV->dateCreated              = $dateCreated;
           $oPRV->lastUserId               = $lastUserId;	
           $oPRV->save();
		   
     }
	 
    //--------------------------------------------------------------------
    public function deleteByPersonalityRelation($personalityRelationId)
     {		
        return $this->where('personalityRelationId', '=', $personalityRelationId)->delete(); 	
     }	

}
