<?php
/*--------------------------------------------------------------------------------
 *  File          : RelationTypeFilter.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating relation_type_filter table.
 *  Developer     : Gabriel Carrillo 
 *  Company       : Kamazooie Development Corporation
 *  Version       : 1.4.1
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationTypeFilter extends Model

{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_type_filter';
	protected $primaryKey = "rtFilterId";
	protected $modifiers  = ['relationTypeId','step','dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('rtFilterId', '=', $id)->get(); 
     }

	//--------------------------------------------------------------------
	public function getByStep($step)
     {
         return $this->where('step', '=', $step)->get(); 
     }
	 
	//--------------------------------------------------------------------
	public function retrieveByStep($step)
     {
		 
        $rtId = 0;
        $rs = $this->where('step', '=', $step)->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){						  
               $rtId = $rs0->relationTypeId;
            }			
        }
        return $rtId;
     }

    //--------------------------------------------------------------------
    public function canUseShortText($orgid)
     {
        $step = 15;    // use short text 
        $useShortText  = 0;
        $rs = $this->where('step', '=', $step)
                   ->where('relationTypeId', '=', $orgid)
                   ->get(); 
        foreach ($rs as $rs0){                        
            $useShortext = 1;
        }           
        return $useShortText;
     }     
	 
	//--------------------------------------------------------------------
	public function getAll()
     {
         return $this->get(); 
     }	 

	//--------------------------------------------------------------------
	public function getAllOrder()
     {
         return   $this->where('rtOrder', '>', 0)
                       ->orderBy('rtOrder', 'DESC')
                       ->get();
     }	

	//--------------------------------------------------------------------
	public function getRecursive()
     {
         return   $this->where('rtRecursive', '>', 0)
                       ->orderBy('rtOrder', 'DESC')
                       ->get();
     }	 
	 
}
