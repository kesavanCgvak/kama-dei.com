<?php
/*--------------------------------------------------------------------------------
 *  File          : PersonalityValue.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating personality_value table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development  Corporation
 *  Version       : 2.3.1
 *  Updated       : 22 July 2021
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use App\Models\Term;
use Illuminate\Database\Eloquent\Model;

class PersonalityValue extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'personality_value';
	protected $primaryKey = "personalityValueId";
	protected $modifiers  = ['personalityValueId', 'personalityId', 'personTermId',
	                         'scalarValue', 'ownership', 'ownerId', 'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     { 
	    return ($this->where('personalityValueId', '=', $id)->get())->toArray(); 
	 }
	
	//--------------------------------------------------------------------
	public function findByPersonality($personalityId)
     {
	    return ($this->where('personalityId', '=', $personalityId)->get())->toArray(); 
	 }


	//--------------------------------------------------------------------
	public function getByPersonalityValue($personaId,$personalityId, $termId)
     {

        $rs = null;

/*        
	      $rs = $this->where('personalityId', ',=', $personalityId)
                    ->where('personTermId', '=', $termValueId)				
		                ->get(); 

        if($rs->isEmpty()) {
            $rs = $this->where('personalityId', '=', $personaId)
                      ->where('personTermId', '=', $termValueId)        
                      ->get();           
        }
*/
        $rs =  $this->where( function($query) use($personalityId, $termId) {
                       $query->where('personalityId', '=', $personalityId )
                             ->where('personTermId', '=', $termId);                           
                 })->orWhere( function($query) use($personaId, $termId)  {
                       $query->where('personalityId', '=', $personaId )
                             ->where('personTermId', '=', $termId) ;
                     })
                    ->get(); 


        if (!empty($rs)) { 
           $rs->toArray();
        }

      return $rs;
	 }

  //--------------------------------------------------------------------
  public function retrieveByPersonalityValue($personalityId, $termValueId)
     {

        $rs = null;
        $id = 0;
        $rs = $this->where('personalityId', '=', $personalityId)
                   ->where('personTermId', '=', $termValueId)        
                   ->get(); 

        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){              
               $id = $rs0->personalityValueId;
            }     
        }
        return $id;
   }

  //--------------------------------------------------------------------
  public function updatePVScalarById($pvId, $termValueId,$scalarValue)
     {
        $this->where('personalityValueId', '=', $pvId)      
              ->update(array(
                 'scalarValue'  => $scalarValue
               ));    
   }

     //--------------------------------------------------------------------
  public function updatePVScalar($personalityId, $termValueId,$scalarValue)
     {
        $this->where('personalityId', '=', $personalityId)
             ->where('personTermId', '=', $termValueId)        
              ->update(array(
                 'scalarValue'  => $scalarValue
               ));    
   }

  //--------------------------------------------------------------------
  public function addPVScalar($personalityId, $termId,$scalarValue, $ownership, $ownerId,$userId )
    {
        $oPV = new PersonalityValue();
        $oPV->personalityId   = $personalityId;
        $oPV->personTermId    = $termId;
        $oPV->scalarValue     = $scalarValue;
        $oPV->ownership       = $ownership;
        $oPV->ownerId         = $ownerId;
        $oPV->dateCreated     = date("Y-m-d H:i:s");
        $oPV->lastUserId      = $userId;
        $oPV->save();
   
    }

	//--------------------------------------------------------------------
	public function clonePersonalityValue($sourcePersonalityId, $targetPersonalityId, $sourceOrgId, $targetOrgId)
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
			
               $personTermId = $rs0->personTermId;
               $scalarValue  = $rs0->scalarValue;
               $ownership    = $rs0->ownership;
               $ownerId      = $rs0->ownerId;
               $lastUserId   = $rs0->lastUserId;

               $oPV = new PersonalityValue();
               $oPV->personalityId   = $targetPersonalityId;
               $oPV->personTermId    = $personTermId;
               $oPV->scalarValue     = $scalarValue;
               $oPV->ownership       = $ownership;
               $oPV->ownerId         = $ownerId;
               $oPV->dateCreated     = date("Y-m-d H:i:s");
               $oPV->lastUserId      = $lastUserId;
               $oPV->save();
            }			
        }
					
	 }
	 
  //--------------------------------------------------------------------
  public function retrieveScalarValue($personalityId,$termId)
     {
        $scalarValue = 0;
        $rs  = null;
        $rs =  $this->where('personalityId', '=', $personalityId)
                    ->where('personTermId', '=', $termId)
                    ->get(); 

        foreach ($rs as $rs0){              
           $scalarValue = $rs0->scalarValue;
        }     
        return $scalarValue;
     }

    //--------------------------------------------------------------------
    public function deleteByPersonality($personalityId)
     {		
        return $this->where('personalityId', '=', $personalityId)->delete(); 	
     }	
	 
}
