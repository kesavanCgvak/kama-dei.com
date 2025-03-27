<?php
/*--------------------------------------------------------------------------------
 *  File          : SolutionRelation.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating solution_relation table.
 *                  Records are retrieved in array or string format.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie  Development Corporation
 *  Version       : 3.03
 *  Updated       : 27 May 2024 
 *---------------------------------------------------------------------------------*/

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SolutionRelation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'solution_relation';
	protected $primaryKey = "srId";
	protected $fillable  = ['srsfId', 'srRelationText','srShortText','srRelationId','srRating','srHasExtendedData',
                           'srLeftTermId','srRelationTypeId','srRightTermid','srLanguage','srInput','srUtterance', 
                           'apikey','srSource','srState', 'rpaState', 'AWSOpen','msbotState','mappingType',
                           'lastUserId', 'optionState', 'problemCount', 'sampleUtterance', 'conversationId', 'botName'];

/*
srsfId
srRelationText
srShortText
srRelationId
srRating
srHastExtendedData
srLeftTermId
srRelationTypeId
srRightTermId
srLanguage
srInput
srUtterance
apikey
srSource
srState
rpaState
AWSOpen
msbotState
mappingType
lastUserId
*/                         
	protected $dates      = ['dateCreated'];
	

	 
	//--------------------------------------------------------------------
     public function getByUser($userId)
     {
        return   ($this->where('lastUserId', '=', $userId)
                       ->where('srRelationId', '>', 0)	
                       ->get())->toArray(); 			
     }

  //--------------------------------------------------------------------
     public function getByUserOrderByRating($userId,$skipRelationTypeId=0,$problemId=0)
     {

        if ($problemId > 0) {
          $rs = $this->where('lastUserId', '=', $userId)
                     ->where('srRelationId', '>', 0) 
                     ->where('srsfId', '=', $problemId) 
                     ->where('srRelationTypeId', '!=', $skipRelationTypeId)
                     ->orderBy('srRating', 'ASC')
                     ->get();
        } else {
          $rs = $this->where('lastUserId', '=', $userId)
                     ->where('srRelationId', '>', 0) 
                     ->where('srRelationTypeId', '!=', $skipRelationTypeId)
                     ->orderBy('srRating', 'ASC')
                     ->get();
        }

       return $rs; 
   }


  //--------------------------------------------------------------------
     public function getByUserOrderDESCByRating($userId,$skipRelationTypeId=0,$problemId=0)
     {

        if ($problemId > 0) {
          $rs = $this->where('lastUserId', '=', $userId)
                     ->where('srRelationId', '>', 0) 
                     ->where('srsfId', '=', $problemId) 
                     ->where('srRelationTypeId', '!=', $skipRelationTypeId)
                     ->orderBy('srRating', 'DESC')
                     ->get();
        } else {
          $rs = $this->where('lastUserId', '=', $userId)
                     ->where('srRelationId', '>', 0) 
                     ->where('srRelationTypeId', '!=', $skipRelationTypeId)
                     ->orderBy('srRating', 'DESC')
                     ->get();
        }

       return $rs; 

   }

	//--------------------------------------------------------------------
     public function getProblemSolution($userId,$parentId,$skipRelationTypeId=0)
     {
	
       return   $this->where('lastUserId', '=', $userId)
                     ->where('srRelationId', '>', 0) 
                     ->where('srRelationTypeId', '!=', $skipRelationTypeId)
                     ->where('srsfId', '=', $parentId)
                     ->orderBy('srRating', 'ASC')
		                 ->get(); 			
	 }

  //--------------------------------------------------------------------
     public function hasSolutionRelation($userId)
     {
       return   $this->where('lastUserId', '=', $userId)
                     ->where('srRating', '>', 0) 
                     ->get();       
     }

    //--------------------------------------------------------------------
     public function getByUserRTFilterOrderByRating($userId, $rtFilterId)
     {
      //  solution_relation: srRelationId
      //  relation:  relationId, relationTypeId

       return  $this->Where( function($query) use($orgid, $rtFilterId)  {
                            $query->leftJoin('relation as relation',
                                   'relation.relationId','=','solution_relation.srRelationIdId')
                             ->leftJoin('relation as relation',
                                   'relation.relationTypeId','<>',$rtFilterId)
                             ->where('lastUserId', '=', $userId)
                             ->where('srRelationId', '>', 0) 
                             ->orderBy('srRating', 'ASC');
                     })
                    ->get() ;  

     }   

  //--------------------------------------------------------------------
     public function getByUserOrderByRelationId($userId)
     {
       return   $this->where('lastUserId', '=', $userId)
                      ->where('srRelationId', '>', 0)  
                      ->orderBy('srRelationId', 'ASC')
                      ->get();      
     }         


  //--------------------------------------------------------------------
     public function getKaasLink($userId,$inText)
     {

        $relationId = 0;
        
        $srId = intval(substr($inText,5,10));
        $rs =  $this->where('lastUserId', '=', $userId)
                   ->where('srId', '=', $srId)  
                   ->get(); 
        foreach($rs as $rs0) {
            $relationId   = $rs0->srRelationId;                 
        }

        return $relationId;
     }	 

  //--------------------------------------------------------------------
     public function getKaasLink0($userId,$inText)
     {

        $termName = "";
        $termId = 0;
        
        $srId = intval(substr($inText,5,10));

        $rs =  $this->where('lastUserId', '=', $userId)
                   ->where('srId', '=', $srId)  
                   ->get(); 
        foreach($rs as $rs0) {
            $termName = $rs0->srSubject;
            $side    = $rs0->srSide;
            if ($side == "LH") {
                $termId = $rs0->srLeftTermId; 
            }
            if ($side == "RH") {
                $termId = $rs0->srRightTermId; 
            }                      
        }

        $aLink = array($termName,$termId);
        $sLink = implode(",",$aLink);

        return $sLink;
     }         
   

  //--------------------------------------------------------------------
     public function retrieveState($srId,$userId)
     {
        $rs = $this->where('lastUserId', '=', $userId)
                   ->where('srsfId', '=', $srId)  
                   ->get();  
        $state = 0;
        if (!empty($rs)) {               
            foreach ($rs as $rs0){              
                $state = $rs0->srState;
            }     
        }
        return $state;

     }  

  //--------------------------------------------------------------------
     public function getState($srsfId,$userId)
     {
        $rs = $this->where('lastUserId', '=', $userId)
                   ->where('srsfId', '=', $srsfId)  
                   ->get();  
        return $rs;

     } 


    //---------------------------------------------------
     public function findSrid($srsfId,$userId)
     {
  
        $rs = $this->where('lastUserId', '=', $userId)
                   ->where('srsfId', '=', $srsfId)  
                   ->get();  
       return $rs;

     }   
     
    //---------------------------------------------------
     public function retrieveSrid($srsfId,$userId)
     {

        $srId = $srsfId;
        $rs = $this->where('lastUserId', '=', $userId)
                   ->where('srsfId', '=', $srsfId)  
                   ->get();  
        foreach ($rs as $rs0){              
                $srId = $rs0->srId;
        } 

        return $srId;

     } 


    //---------------------------------------------------
     public function retrieveSridById($id)
     {

        $srId = 0;
        $rs = $this->where('srId', '=', $id)  
                   ->get();  
        foreach ($rs as $rs0){              
                $srId = $rs0->srRelationId;
        } 

        return $srId;

     } 

    //---------------------------------------------------
     public function retrieveSingleId($userId, $problemId=0)   
     {

        $srId = 0;

        if ($problemId == 0) {
            $rs = $this->where('lastUserId', '=', $userId)
                       ->where('srSource', '!=', 7)
                       ->get();  
        } else {
            $rs = $this->where('lastUserId', '=', $userId)
                       ->where('srsfId', '=', $problemId)
                       ->where('srSource', '!=', 7)
                       ->get();            
        }

        foreach ($rs as $rs0){              
            $srId = $rs0->srId;
        } 

        return $srId;

     } 



    //--------------------------------------------------------------------
    public function getUniqueProblem($userid)
     { 

        $rs = $this->where('lastUserId', '=', $userid)
                    ->where('srRelationId', '>', 0)  
                    ->groupby('srsfId') 
                    ->distinct(['srsfId'])
                   ->get(['srsfId']);
        return $rs;
     }
     

    //--------------------------------------------------------------------
    public function countSR($sfId, $srRelationId, $userId)
     {
      return  ($this->where('srsfId', '=', $sfId)
		                ->where('srRelationId', '=', $srRelationId)
                    ->where('lastUserId', '=', $userId)
                    ->get())->count(); 			
	 }	 

	 
    //--------------------------------------------------------------------
    public function findByRelationText($relationText)
     {
        return ($this->where('srRelation', '=', $relationText)->get())->toArray(); 
     }
	 
    //--------------------------------------------------------------------
    public function findById($id)
     {
        return ($this->where('srId', '=', $id)->get())->toArray(); 
     }
	 
	//--------------------------------------------------------------------

	public function insertRelation($srsfId,$srRelationText,$srShortText, $srRelationId,$srRating,
             	$srHasExtendedData,$srLeftTermId, $srRelationTypeId, $srRightTermId, $srLanguage,
              $srInput, $srUtterance, $apikey,$srSource, $srState, $rpaState,$AWSOpen, $msbotState,
               $mappingType, $userid, $optionState=0, $problemCount=0, $sampleUtterance="", 
               $conversationId="", $botName="" )
	 {


           if ( strlen($srRelationText) > 1000) {
              $srRelationText = substr($srRelationText,0,1000);
           }

           if ( gettype($srInput) == "array"   ) {
               $srInput = implode(" ",$srInput);
           }
           
           if ( strlen($srInput) > 250) {
              $srInput = substr($srInput,0,250);
           }    
           
           if ( strlen($srUtterance) > 250) {
              $srUtterance = substr($srUtterance,0,250);
           }  

           if ( strlen($sampleUtterance) > 250) {
              $sampleUtterance = substr($sampleUtterance,0,250);
           } 

           if ( strlen($botName) > 250) {
              $botName = substr($botName,0,250);
           } 

           $oSolutionRelation = new SolutionRelation();

           $oSolutionRelation->srsfId           = $srsfId;
           $oSolutionRelation->srRelationtext   = $srRelationText;
           $oSolutionRelation->srShortText      = $srShortText;
           $oSolutionRelation->srRelationId     = $srRelationId;
           $oSolutionRelation->srRating         = $srRating;
           $oSolutionRelation->srHasExtendedData= $srHasExtendedData;
           $oSolutionRelation->srLeftTermId     = $srLeftTermId;
           $oSolutionRelation->srRelationTypeId = $srRelationTypeId;
           $oSolutionRelation->srRightTermId    = $srRightTermId;
           $oSolutionRelation->srLanguage       = $srLanguage; 
           $oSolutionRelation->srInput          = $srInput;
           $oSolutionRelation->srUtterance      = $srUtterance;
           $oSolutionRelation->apikey           = $apikey;
           $oSolutionRelation->srSource         = $srSource; 
           $oSolutionRelation->srState          = $srState;            
           $oSolutionRelation->rpaState         = $rpaState;
	         $oSolutionRelation->AWSOpen          = $AWSOpen;
           $oSolutionRelation->msbotState       = $msbotState;
           $oSolutionRelation->mappingType      = $mappingType;
           $oSolutionRelation->lastUserId       = $userid;
           $oSolutionRelation->optionState      = $optionState;
           $oSolutionRelation->problemCount     = $problemCount;
           $oSolutionRelation->sampleUtterance  = $sampleUtterance;
           $oSolutionRelation->conversationId   = $conversationId;
           $oSolutionRelation->botName          = $botName;
           $oSolutionRelation->save();	

           $lastInsertedId = $oSolutionRelation->srId;

           return $lastInsertedId;      	
     }
	 
    //--------------------------------------------------------------------
    public function updateFactRating($id, $rating)
     {		
        $this->where('srId', $id)
              ->update(array(
              'srRating'  => $rating
            ));		
     }


    //--------------------------------------------------------------------
    public function getDistinctByUser($userid)
     {    
        $rs = $this->where('lastUserId', '=', $userid)
                   ->distinct()
                   ->get(['srInput']);
        return $rs;
     }


    //--------------------------------------------------------------------
    public function getProblem($userid)
     {    
        $rs = $this->where('lastUserId', '=', $userid)
                   ->distinct()
                   ->get(['srsfId']);
        return $rs;
     }


    //--------------------------------------------------------------------
    public function getProblemId($userid)
     {    
        $rs = $this->where('lastUserId', '=', $userid)
                    ->where('srRelationId', '>', 0)  
                     ->orderBy('sfRating', 'ASC')
                     ->orderBy('srRating', 'DESC')
                    ->get();
        return $rs;
     }
    //-----------------------------------------------------------------	 
    public function updateHasextendeddata($id, $hasExtendedData)
     {    
        $this->where('srId', $id)->update(array(
              'srHasExtendedData'  => $hasExtendedData
            ));   
     }


    //--------------------------------------------------------------------
    public function retrieveRelationId($userid)
     {
        $rs = $this->where('lastUserId', '=', $userid)
                   ->where('srRelationId', '>', 0)
                   ->where('srRating', '>', 0)
                   ->get(); 
        $relationId = 0; 
               
        foreach ($rs as $rs0){              
            $relationId = $rs0->srRelationId;
        }     

        return $relationId;
     } 



    //--------------------------------------------------------------------
    public function retrieveSRRelationId($userid, $optionNumber)
     {
        $rs = $this->where('lastUserId', '=', $userid)
                   ->where('srId', '=', $optionNumber)
                   ->get(); 
        $relationId = 0; 
        if (!empty($rs)) {               
            foreach ($rs as $rs0){              
                $relationId = $rs0->srRelationId;
            }     
        }
        return $relationId;
     } 

  //---------------------------------------------------------
    public function hasExData($userid,$pickSolutionId, $hasExdata)
     {    

        $rs = $this->where('lastUserId', '=', $userid)
                   ->where('srId', '=', $pickSolutionId)
                   ->where('srHasExtendedData', '=', $hasExdata)
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
    public function deleteById($id)
     {		
        return $this->where('srId', '=', $id)->delete(); 	
     }	
	 
    //--------------------------------------------------------------------
    public function deleteBySource($userid,$source)
      {    
        return $this->where('lastUserId', '=', $userid)
                    ->where('srSource', '=', $source)
                    ->delete();    
     } 

    //--------------------------------------------------------------------
    public function deleteByUser($userid,$pickFactId=0)
     {	

        if ($pickFactId == 0) {
            return $this->where('lastUserId', '=', $userid)
                        ->delete();
        }	else {

            return $this->where('lastUserId', '=', $userid)
                        ->where('srsfId', '=', $pickFactId)
                        ->delete(); 	
        }
     }		 

    //--------------------------------------------------------------------
    public function deleteState($relationId,$userid)
     {    
         $this->where('srRelationId', '=', $relationId)
              ->where('lastUserId', '=', $userid)
              ->delete();  

         return 0;             
     }         

}
