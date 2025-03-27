<?php

/*--------------------------------------------------------------------------------
 *  File          : ConsumerUserPersonality.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating consumer_user_personality table.
 *                  Records are retrieved in array format.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation 
 *  Version       : 1.4.1
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumerUserpersonality extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'consumer_user_personality';
	protected $primaryKey = "consUserPersonalityId";
	protected $modifiers  = ['consUserPersonalityId', 'consumerUserId', 'personalityId', 'organizationId',
	                          'dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
		return ($this->where('consUserPersonalityId', '=', $id)->get())->toArray(); 
	 }
	
	//--------------------------------------------------------------------
	public function findByPersonality($personalityId)
     {
		return ($this->where('personalityId', '=', $personalityId)->get())->toArray(); 
	 }
	 
	//--------------------------------------------------------------------	 
    public function retrievePersonalityByUser($userid)
     {
        $personalityId = 0;
        $rs = $this->where('consumerUserId', '=', $userid)->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){						  
               $personalityId = $rs0->personalityId;
            }			
        }		
        return $personalityId;
     }

    //--------------------------------------------------------------------   
    public function retrievePersonalityByUserOrg($userid, $orgid)
     {
        $personalityId = 0;
        $rs = $this->where('consumerUserId', '=', $userid)
                   ->where('organizationId', '=', $orgid)
                   ->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){                        
               $personalityId = $rs0->personalityId;
            }           
        }       
        return $personalityId;
     }
	 
    //--------------------------------------------------------------------
    public function updatePersonalityOrganization($consumerUserId, $personalityId, $organizationId)
     {		
        $this->where('consumerUserId', $consumerUserId)->update(array(
              'personalityId'  => $personalityId,
			  'organizationId' => $organizationId
            ));	
     }
	 
    //--------------------------------------------------------------------
    public function deleteByConsumerUser($userid)
     {		
        return $this->where('consumerUserId', '=', $userid)->delete(); 	
     }	
	 
	 
    //--------------------------------------------------------------------
    public function personality(){
        return $this->belongsTo('App\Models\Personality', 'personalityId');
    }

}
