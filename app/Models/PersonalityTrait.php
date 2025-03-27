<?php

/*--------------------------------------------------------------------------------
 *  File          : PersonalityTrait.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating personality_trait table.
 *                  Records are retrieved in array format.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 1.4.1
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalityTrait extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'personality_trait';
	protected $primaryKey = "personalityTraitId";
	protected $modifiers  = ['personalityTraitId', 'personalityDefn', 'personalityId', 'termTraitId',
	                         'scalarValue', 'ownership', 'ownerId', 'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
        return ($this->where('personalityTraitId', '=', $id)->get())->toArray(); 
     }

	//--------------------------------------------------------------------
	public function getByPersonality($personalityId)
     {
        return ($this->where('personalityId', '=', $personalityId)->get())->toArray(); 
     }
	 
	//--------------------------------------------------------------------
	public function retrieveScalarValueByPersonality($personaId, $personalityId)
     {
        $scalarValue = 0.5;

        $rs =  $this->where( 'personalityId', '=', $personalityId )
                    ->orWhere( 'personalityId', '=', $personaId )
                    ->get(); 

        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){						  
			    $scalarValue = $rs0->scalarValue;
            }			
        }

		return $scalarValue;
     }
	 
	//--------------------------------------------------------------------
	public function clonePersonalityTrait($sourcePersonalityId, $targetPersonalityId, $sourceOrgId, $targetOrgId)
     {
        // get source records
        $rs =  $this->where( function($query) use($sourcePersonalityId) {
                       $query->where( 'personalityId', '=', $sourcePersonalityId )
					         ->where( 'ownership', '=', 0 );
                 })->orWhere( function($query) use($sourcePersonalityId, $sourceOrgId)  {
                       $query->where( 'personalityId', '=', $sourcePersonalityId )
					         ->where( 'ownership', '>', 0 )
					         ->where('ownerId', '=', $sourceOrgId);
                     })
		            ->get(); 

        // insert target records		
        if (!empty($rs)) { 
 		
            foreach ($rs as $rs0){	
			
               $personTraitDefn  = $rs0->personalityTraitDefn;
               $termTraitId      = $rs0->termTraitId;
			         $scalarValue      = $rs0->scalarValue;
               $ownership        = $rs0->ownership;
               $ownerId          = $rs0->ownerId;
               $lastUserId       = $rs0->lastUserId;

               $oPT = new PersonalityTrait();
			         $oPT->personalityTraitDefn = $personTraitDefn;
               $oPT->personalityId       = $targetPersonalityId;
               $oPT->termTraitId         = $termTraitId;
               $oPT->scalarValue         = $scalarValue;
               $oPT->ownership           = $ownership;
               $oPT->ownerId             = $ownerId;
               $oPT->dateCreated         = date("Y-m-d H:i:s");
               $oPT->lastUserId          = $lastUserId;
               $oPT->save();

            }			
        }
					
	 }
	 
    //--------------------------------------------------------------------
    public function deleteById($id)
     {		
        return $this->where('personalityTraitId', '=', $id)->delete(); 	
     }	

    //--------------------------------------------------------------------
    public function deleteByPersonality($personalityId)
     {		
        return $this->where('personalityId', '=', $personalityId)->delete(); 	
     }		 

}
