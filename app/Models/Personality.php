<?php

/*--------------------------------------------------------------------------------
 *  File          : Personality.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating personality.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 22.5
 *  Updated       : 10 February 2023
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personality extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'personality';
	protected $primaryKey = "personalityId";
	protected $modifiers  = ['personalityId', 'parentPersonaId', 'personalityName', 'personalityDescription',
	                         'ownership', 'ownerId', 'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	 
	//--------------------------------------------------------------------	
    // retrive personalityId from table consumer_user_personality	
    public function retrievePersonalityId($consumerUserId)
     {
        $personalityId = 0;		  
        $rs = Personality::with(['consumerUserPersonality' 
		     => function($query) use ($consumerUserPersonalityId) {
                $query->where('consumerUserId', '=', $consumerUserId);
            }])->get();			   
			   
        if (!empty($rs)) {               
            foreach ($rs as $rs0){						  
               $personalityId = $rs0->personalityId;
            }			
        }		
        return $personalityId;
     }

    //--------------------------------------------------------------------   
    public function retrieveParentPersonality($personalityId)
     {
        $personaId = $personalityId;
        $zero = 0;
        $rs = $this->where('personalityId', '=', $personalityId)
                   ->where('parentPersonaId', '>', $zero)
                   ->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){                        
               $personaId = $rs0->parentPersonaId;
            }           
        }       
        return $personaId;
     }
     

	 
    //--------------------------------------------------------------------
    public function consumerUserPersonality(){
        return $this->hasMany('App\Models\ConsumerUserPersonality', 'consUserPersonalityId');
    }

}
