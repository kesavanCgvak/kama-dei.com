<?php
/*--------------------------------------------------------------------------------
 *  File          : SolutionOption.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating solution_option table.
 *                  Records are retrieved in array or string format.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation.
 *  Version       : 3.03
 *  Updated       : 09 June 2024 
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolutionOption extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'solution_option';
    protected $primaryKey = "soId";
    protected $fillable  = ['sosrId', 'soOption', 'soSolution', 'soSolutionId','soLeftTermId', 
                            'soRelationTypeId', 'soRightTermId', 'soShortText','soLanguage',
	                        'soLinkOrder', 'soRating','soHasExtendedData', 'soParentId','lastUserId', 'soChildren'];
    protected $dates      = ['dateCreated'];

    //--------------------------------------------------------------------
    /*   soChildren          -1    Solution process not executed
                              0    Solution process execiuted and no solution
                            n > 0  sfId of the problem when solution found
    */

    //--------------------------------------------------------------------
    public function insertOption($sosrId,$soOption, $soSolution, $soSolutionId,
              $soLeftTermId,$soRelationTypeId,$soRightTermId,$soShortText,$soLanguage,
              $soLinkOrder,$soRating,$soHasExtendedData, $soParentId, $userid, $soChildren=-1)
     {

           if ( strlen($soSolution) > 1000) {
              $soSolution = substr($soSolution,0,1000);
           }

           $oSolutionOption = new SolutionOption();
           $oSolutionOption->sosrId           = $sosrId;
           $oSolutionOption->soOption         = $soOption;		   
           $oSolutionOption->soSolution       = $soSolution;
           $oSolutionOption->soSolutionId     = $soSolutionId;
           $oSolutionOption->soLeftTermId     = $soLeftTermId;
           $oSolutionOption->soRelationTypeId = $soRelationTypeId;
           $oSolutionOption->soRightTermId    = $soRightTermId;
           $oSolutionOption->soShortText      = $soShortText; 
           $oSolutionOption->soLanguage       = $soLanguage;        
           $oSolutionOption->soLinkOrder      = $soLinkOrder;
           $oSolutionOption->soRating         = $soRating;		 
           $oSolutionOption->soHasExtendedData = $soHasExtendedData;  
           $oSolutionOption->soParentId       = $soParentId;      
           $oSolutionOption->lastUserId       = $userid;	
           $oSolutionOption->soChildren       = $soChildren;
           $oSolutionOption->save();
           $lastInsertId   = $oSolutionOption->soId;

           return $lastInsertId;  

     }
	
    //--------------------------------------------------------------------
    public function getByUser($userId)
     {
        return  $this->where('lastUserId', '=', $userId)
                     ->get(); 			
     }
	 
    //--------------------------------------------------------------------
    public function getByUserHasParent($userId,$parentId)
     {
        $zero = 0;
        if ($parentId == 0) {
           return  ($this->where('lastUserId', '=', $userId)
                         ->where('soParentId', '=', $parentId)
                   ->get())->toArray();  
        } else {
          return  ($this->where('lastUserId', '=', $userId)
                        ->where('soparentId', '>', $zero)
                   ->get())->toArray(); 
        }

      
     }

    //--------------------------------------------------------------------
    public function getByUserParent($userId,$parentId)
     {
        return  $this->where('lastUserId', '=', $userId)
                     ->where('soParentId', '=', $parentId)
                     ->get();       
     }

    //--------------------------------------------------------------------
    public function getNegativeByUserParent($userId,$parentId)
     {
        return  $this->where('lastUserId', '=', $userId)
                     ->where('soParentId', '=', $parentId)
                     ->where('soRating', '<', 0)
                     ->get();       
     }     

    //--------------------------------------------------------------------
    public function getPositiveByUser($userId)
     {
        return  $this->where('lastUserId', '=', $userId)
                     ->where('soRating', '>', 0)
                     ->get();       
     }    

    //--------------------------------------------------------------------
    public function updateFactRating($id, $rating)
     {    
        $this->where('soId', $id)
              ->update(array(
              'soRating'  => $rating
            ));   
     }

    //--------------------------------------------------------------------
    public function updateChildren($id, $soChildren)
     {    
        $this->where('soId', $id)->update(array(
              'soChildren'  => $soChildren
            ));   
     }     


    //--------------------------------------------------------------------
    public function getObjectByUser($userId)
     {
        return  $this->where('lastUserId', '=', $userId)
                     ->get();       
     }

    //--------------------------------------------------------------------
    public function getByUserOrderByRating($userId)
     {
        return   $this->where('lastUserId', '=', $userId)
                      ->orderBy('soRating', 'DESC')
                      ->get(); 			
     }

    //--------------------------------------------------------------------
    public function getByUserSosrid($userId, $sosrid)
     {
        $zero = 0;
        return   $this->where('lastUserId', '=', $userId)
                      ->where('sosrId', '=', $sosrid)
                      ->where('soParentId', '=', $zero)
                      ->get();      
     }

    //--------------------------------------------------------------------
    public function getCountByRelation($sosrid, $userId)
     {
        $optionCount = 0;
        $rs =  $this->where('lastUserId', '=', $userId)
                    ->where('sosrId', '=', $sosrid)
                    ->get(); 
        foreach ($rs as $rs0) {
            $optionCount++;
        }
        return $optionCount;

     }

    //--------------------------------------------------------------------
    public function getCountByParent($parentId, $userId)
     {
        $optionCount = 0;
        $rs =  $this->where('lastUserId', '=', $userId)
                    ->where('soParentId', '=', $parentId)
                    ->get(); 
        foreach ($rs as $rs0) {
            $optionCount++;
        }
        return $optionCount;

     }



    //--------------------------------------------------------------------
    public function getByUserSoid($userId, $soid)
     {
        return   $this->where('lastUserId', '=', $userId)
                      ->where('soId', '=', $soid)
                      ->get();      
     }

  //--------------------------------------------------------------------
     public function getKaasLink($userId,$inText)
     {

        $relationId = 0;
        
        $soId = intval(substr($inText,5,10));

        $rs =  $this->where('lastUserId', '=', $userId)
                   ->where('soId', '=', $soId)  
                   ->get(); 
        foreach($rs as $rs0) {
            $relationId   = $rs0->soSolutionId;                 
        }

        return $relationId;
     } 


    //--------------------------------------------------------------------
    public function getUserRelationOrderByRatingLink($userId, $optionNumber)
     {
        return   $this->where('lastUserId', '=', $userId)
                      ->where('sosrId', '=', $optionNumber)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get(); 			
     }

    //--------------------------------------------------------------------
    public function getOptionHasrisk($userId, $optionNumber, $parentId = 0)
     {
       
      if ($parentId == 0) {
          return $this->where('lastUserId', '=', $userId)
                      ->where('sosrId', '=', $optionNumber)
                      ->where('soOption', '=', 'has risk')
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get(); 
      } else {
          return $this->where('lastUserId', '=', $userId)
                      ->where('soOption', '=', 'has risk')
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get();
      }
           
     }

    //--------------------------------------------------------------------
    public function getOptionRequires($userId, $pickSolutionId, $parentId = 0)
     {

      if ($parentId == 0) {
          return $this->where('lastUserId', '=', $userId)
                      ->where('sosrId', '=', $pickSolutionId)
                      ->where('soOption', '=', 'requires')
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get(); 
      } else {
          return $this->where('lastUserId', '=', $userId)
                      ->where('soOption', '=', 'requires')
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get();
      }

     }  


    //--------------------------------------------------------------------
    public function getOptionHasoption($userId, $solutionId, $parentId = 0)
     {

      if ($parentId == 0) {
          return $this->where('lastUserId', '=', $userId)
                      ->where('sosrId', '=', $solutionId)
                      ->where('soOption', '=', 'has option')
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get(); 
      } else {
          return $this->where('lastUserId', '=', $userId)
                      ->where('soOption', '=', 'has option')
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get();
      }

     }  


    //--------------------------------------------------------------------
    public function getOptionRecord($userId, $action, $optionNumber, $parentId = 0)
     {

      if ($parentId == 0) {
          return $this->where('lastUserId', '=', $userId)
                      ->where('sosrId', '=', $optionNumber)
                      ->where('soOption', '=', $action)
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get(); 
      } else {
          return $this->where('lastUserId', '=', $userId)
                      ->where('soOption', '=', $action)
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get();
      }

     }  

    //--------------------------------------------------------------------
    public function getOptionHasrisk2($userId, $optionNumber, $parentId = 0)
     {
       
      if ($parentId == 0) {
          return $this->where('lastUserId', '=', $userId)
                      ->where('sosrId', '=', $optionNumber)
                      ->where('soOption', '=', 'has risk')
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get(); 
      } else {
          return $this->where('lastUserId', '=', $userId)
                      ->where('soOption', '=', 'has risk')
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get();
      }
           
     }


    //--------------------------------------------------------------------
    public function getOptionHasoption2($userId, $optionNumber, $parentId = 0)
     {

      if ($parentId == 0) {
          return $this->where('lastUserId', '=', $userId)
                      ->where('sosrId', '=', $optionNumber)
                      ->where('soOption', '=', 'has option')
                      ->where('soParentId', '=', 0)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get(); 
      } else {
          return $this->where('lastUserId', '=', $userId)
                      ->where('soOption', '=', 'has option')
                      ->where('soParentId', '=', $parentId)
                      ->orderBy('soLinkOrder', 'ASC')
                      ->get();
      }
     } 


    //--------------------------------------------------------------------
    public function getOptionHasVR($userId, $optionName)
     {

        return $this->where('lastUserId', '=', $userId)
                    ->where('soOption', '=', $optionName)
                    ->distinct('soSolutionId')
                    ->get();
     }         

     //--------------------------------------------------------------------
    public function retrieveSOSolutionId($userId, $soId)
     {
        $rs = $this->where('lastUserId', '=', $userId)
                   ->where('soId', '=', $soId)
                   ->get(); 
        $relationId = 0; 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){              
                $relationId = $rs0->soSolutionId;
            }     
        }
        return $relationId;
     } 


     //--------------------------------------------------------------------
    public function retrieveParentId($userid, $soId)
     {
        $rs = $this->where('lastUserId', '=', $userid)
                   ->where('soId', '=', $soId)
                   ->get(); 
        $parentId = 0; 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){              
                $parentId = $rs0->soParentId;
            }     
        }
        return $parentId;
     } 


     //--------------------------------------------------------------------
    public function hasExtendedData($userid)
     {
        $rs = $this->where('lastUserId', '=', $userid)
                   ->get(); 
        $hasED = 0; 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){              
                $hasED = 1;
            }     
        }
        return $hasED;

     } 

    //--------------------------------------------------------------------
    public function getOptionHasriskParent($userId,$parentId )
     {
        return   ($this->where('lastUserId', '=', $userId)
                       ->where('soParentId', '=', $parentId)
                       ->get())->toArray();      
     }

    //--------------------------------------------------------------------
    public function getOptionRequiresParent($userId,$parentId )
     {
        return    $this->where('lastUserId', '=', $userId)
                       ->where('soParentId', '=', $parentId)
                       ->orderBy('soLinkOrder', 'ASC')                       
                       ->get();      
     }   

    //--------------------------------------------------------------------
    public function getOptionHasoptionParent($userId,$parentId )
     {
        return    $this->where('lastUserId', '=', $userId)
                       ->where('soParentId', '=', $parentId)
                       ->orderBy('soLinkOrder', 'ASC')                       
                       ->get();      
     }
	 
    //--------------------------------------------------------------------
    public function findBySolutionText($solutionText)
     {
         return $this->where('soSolution', '=', $solutionText)
                     ->get(); 
     }
	 
    //--------------------------------------------------------------------
    public function findById($id)
     {
         return $this->where('soId', '=', $id)
                     ->get(); 
     }

    //--------------------------------------------------------------------
    public function findNewById($id, $soChildren)
     {
         return $this->where('soId', '=', $id)
                     ->where('soChildren', '=', $soChildren)
                     ->get(); 
     }

    //--------------------------------------------------------------------
    public function updateHasextendeddata($id, $hasExtendedData)
     {    
        $this->where('soId', $id)->update(array(
              'soHasExtendedData'  => $hasExtendedData
            ));   
     }
	 
    //--------------------------------------------------------------------
    public function deleteById($id)
     {		
         return $this->where('soId', '=', $id)
                     ->delete(); 	
     }	

    //--------------------------------------------------------------------
    public function deleteBlank($userid)
     {    
         return $this->where('lastUserId', '=', $userid)
                     ->where('soSolution', '=', "")
                     ->delete();   
     }
	 
    //--------------------------------------------------------------------
    public function deleteByUser($userid)
     {		
         return $this->where('lastUserId', '=', $userid)
                     ->delete(); 	
     }	

    //--------------------------------------------------------------------
    public function deleteDuplicateORIG($userId )
     {
        $rs =   $this->where('lastUserId', '=', $userId)
                       ->orderBy('sosrId', 'ASC')
                       ->orderBy('soSolutionId', 'ASC')                         
                       ->get();  
        $wsosrId =  0;
        $wsoSolutionId = 0;              
        foreach($rs as $rs0) {
            $soId         = $rs0->soId;
            $sosrId       = $rs0->sosrId;
            $soSolutionId = $rs0->soSolutionId;
            if ($sosrId == $wsosrId and $soSolutionId == $wsoSolutionId ) {
                 $this->where('soId', '=', $soId)
                      ->where('soParentId', '=', 0)
                      ->delete(); 
                 $this->where('soParentId', '=', $soId)
                      ->delete(); 

            } else {
               $wsosrId =  $sosrId;
               $wsoSolutionId = $soSolutionId;              
            }
        }

     }

    //--------------------------------------------------------------------
    public function deleteDuplicate($userId )
     {
        $rs =   $this->where('lastUserId', '=', $userId)
                       ->orderBy('sosrId', 'ASC')
                       ->orderBy('soParentId', 'ASC') 
                       ->orderBy('soSolutionId', 'ASC')  
                         
                       ->get();  
        $wsosrId =  -1;
        $wsoSolutionId = -1;  
        $wsoParentId  = -1;            
        foreach($rs as $rs0) {
            $soId         = $rs0->soId;
            $sosrId       = $rs0->sosrId;
            $soSolutionId = $rs0->soSolutionId;
            $soParentId   = $rs0->soParentId;

            
            if ($sosrId == $wsosrId and $soSolutionId == $wsoSolutionId and $soParentId == $wsoParentId ) {
                 $this->where('soId', '=', $soId)
                      ->delete(); 

                 $this->where('soParentId', '=', $soId)
                      ->delete(); 

            } else {
               $wsosrId =  $sosrId;
               $wsoSolutionId = $soSolutionId;     
               $wsoParentId   = $soParentId;          
            }


        }

     }


    //-------  End of SolutonOption() -----------------------------------------------


}
