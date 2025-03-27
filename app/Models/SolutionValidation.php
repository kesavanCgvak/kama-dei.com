<?php
/*--------------------------------------------------------------------------------
 *  File          : Solutionvalidation.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating solution_fact table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development  Corporation
 *  Version       : 2.4
 *  Updated       : 16 June 2021
 *
 *---------------------------------------------------------------------------------*/



namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolutionValidation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'solution_validation';
	protected $primaryKey = "sfId";
	protected $fillable  = [ 'sfId','svRelationId', 'svLeftTermId', 'svRelationTypeId',
                        'svRightTermId', 'svRating','svValidation', 'lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	
	
	public function insertRecord($svRelationId, $svLeftTermId, $svRelationTypeId, $svRightTermId,
                $svRating,$svValidation, $lastUserId) 
	 {  
           $oSV = new SolutionValidation();
           $oSV->svRelationId     = $svRelationId;
           $oSV->svLeftTermId     = $svLeftTermId;	
           $oSV->svRelationTypeId = $svRelationTypeId; 
           $oSV->svRightTermId    = $svRightTermId;	
           $oSV->svRating         = $svRating;
           $oSV->svValidation     = $svValidation;  
           $oSV->lastUserId       = $lastUserId;	
           $oSV->save();		
	 }
	
	//--------------------------------------------------------------------
	public function getByUser($userId)
	 {
		  $rs = $this->where('lastUserId', '=', $userId)
                 ->get(); 
      return $rs;			
	 }
	 
 
	 
  //--------------------------------------------------------------------
   public function hasSuperterm($userId)
    {
       $hassp = 0;
       $rs =$this->where('lastUserId', '=', $userId)
                 ->get(); 
       foreach ($rs as $rs0) {
          $hassp++;
       }
       return $hassp;   
    } 
   

	 /*
	//--------------------------------------------------------------------
	public function updateSolutionFact($sfId,$sfFact,$sfRelationId,
					    $sfLeftTermId,$sfRelationTypeId,$sfRightTermId)
     {		
        $this->where('sfId', $sfId)->update(array(
             'sfFact'            => $sfFact,
             'sfRelationId'      => $sfRelationId,			 
             'sfLeftTermId'      => $sfLeftTermId,	
             'sfRelationTypeId'  => $sfRelationTypeId,				 
             'sfRightTermId'     => $sfRightTermId			 
            ));		
	 }
*/
 
	//--------------------------------------------------------------------
	public function deleteById($id)
     {		
         return $this->where('sfId', '=', $id)->delete(); 	
	 }	
	 

	//--------------------------------------------------------------------
	public function deleteByUser($userid)
     {		
        return $this->where('lastUserId', '=', $userid)->delete(); 	  
	 }		 

}
