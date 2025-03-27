<?php
/*--------------------------------------------------------------------------------
 *  File          : SolutionFact.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating solution_fact table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development  Corporation
 *  Version       : 3.08
 *  Updated       : 21 August 2024
 *  Comments      : Problem based logic
 *                  problem inference + problem validation
                    factType: 0=triple; 1=term; 2=inferred problem
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolutionFact extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'solution_fact';
	protected $primaryKey = "sfId";

	/*
sfId                int     11    Fact id
sfFact              varchar 255   Fact name
sfRelationId        int     11    Fact KR id
sfRating            decimal 10,2  if rating < 0, then it is a problem
sfLeftTermId        int     11    Fact left term id
sfRelationTypeId    int     11    Fact relation type id
sfRightTermId       int     11    Fact right term id

sfLanguage          varchar 12    Language: ‘en’, ‘fr’,’es’
sfParentId          int     11    > 0 if inferred problem   
sfParentFact        varchar 255   Non blank if inferred problem   
sfFactType          int     2     0: triple;  1: synonym; 2: inferred

sfFactProcessed     int     2     0: not processed;   1: processed for regular problem solving
sfInferProcessed    int     2     0: not processed;   1: processed for advanced inference
sfHideFact          int     1     1: Hide recod;   0: show record
sfKaas              int     1     1: is kaas ;   0: is not kaas 
sfHasExtendedData   int     2     0: no data;  1:has extended data
sfValidated         int     2     Inferred problem validated  0: no;  1: yes
sfHasSolution       int     2     0: no;  1: yes

sfInquiry           varchar 255
sfUtterance         varchar 255
sfParentRating      decimal 9,2
sfSource            int 4

sfssSubset          int 6
sfssFact            varchar 255
sfssRelationId      int 11
sfssLeftTermId      int 11

sfssRelationTypeId  int 11
sfssRightTermId     int 11
lastUserId          int 11


*/
  protected $fillable  = [ 'sfFact','sfRelationId', 'sfRating', 'sfLeftTermId','sfRelationTypeId','sfRightTermId', 
                        'sfLanguage', 'sfParentId', 'sfParentFact','sfFactType','sfFactProcessed','sfInferProcessed',
                        'sfHideFact','sfKaas','sfHasExtendedData','sfValidated', 'sfHasSolution',
                        'sfInquiry','sfUtterance','sfKeyword' ,'sfParentRating','sfSource', 
                        'sfssSubset','sfssFact','sfssRelationId','sfssLeftTermId',
                        'sfssRelationTypeId','sfssRightTermId', 'lastUserId' ];


	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	
	
	public function insertFact($sfFact, $sfRelationId, $sfRating, $sfLeftTermId, $sfRelationTypeId, $sfRightTermId,
                $sfLanguage,$sfParentId, $sfParentFact,$sfFactType, $sfFactProcessed, $sfHideFact,$sfKaas,
                $sfHasExtendedData,$sfValidated, $sfHasSolution,
                $sfInquiry,$sfUtterance, $sfKeyword, $sfParentRating,$sfSource,                 
                $sfssSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                $sfssRelationTypeId,$sfssRightTermId, $lastUserId ) 
	 {  
          // Check for length of variables
    
           if ( strlen($sfFact) > 254) {
              $sfFact = substr($sfFact,0,254);
           }

           if ( strlen($sfssFact) > 254) {
              $sfssFact = substr($sfssFact,0,254);
           }

           if ( strlen($sfInquiry) > 254) {
              $sfInquiry = substr($sfInquiry,0,254);
           }

           if ( strlen($sfParentFact) > 255) {
              $sfParentFact = substr($sfParentFact,0,254);
           }

           if ( strlen($sfUtterance) > 254) {
              $sfInput = substr($sfUtterance,0,254);
           }
           if ( strlen($sfKeyword) > 254) {
              $sfKeyword = substr($sfKeyword,0,254);
           }

           $oSolutionFact = new SolutionFact();
           $oSolutionFact->sfFact             = $sfFact;
           $oSolutionFact->sfRelationId       = $sfRelationId;
           $oSolutionFact->sfRating           = $sfRating;
           $oSolutionFact->sfLeftTermId       = $sfLeftTermId;	
           $oSolutionFact->sfRelationTypeId   = $sfRelationTypeId; 
           $oSolutionFact->sfRightTermId      = $sfRightTermId;	
           $oSolutionFact->sfLanguage         = $sfLanguage; 
           $oSolutionFact->sfParentId         = $sfParentId;

           $oSolutionFact->sfssSubset         = $sfssSubset;
           $oSolutionFact->sfssFact           = $sfssFact;
           $oSolutionFact->sfssRelationId     = $sfssRelationId;
           $oSolutionFact->sfssLeftTermId     = $sfssLeftTermId;	
           $oSolutionFact->sfssRelationTypeId = $sfssRelationTypeId;
           $oSolutionFact->sfssRightTermId    = $sfssRightTermId;	
           $oSolutionFact->sfInquiry          = $sfInquiry;	
           $oSolutionFact->sfParentFact       = $sfParentFact;	
           $oSolutionFact->sfParentRating     = $sfParentRating;
           $oSolutionFact->lastUserId         = $lastUserId;	
           $oSolutionFact->sfSource           = $sfSource; 
           $oSolutionFact->sfFactType         = $sfFactType; 
           $oSolutionFact->sfUtterance        = $sfUtterance; 
           $oSolutionFact->sfKeyword          = $sfKeyword; 
           $oSolutionFact->sfHasExtendedData  = 0;
           $oSolutionFact->sfFactProcessed    = $sfFactProcessed;
           $oSolutionFact->sfInferProcessed   = 0;    
           $oSolutionFact->sfHideFact         = $sfHideFact;
           $oSolutionFact->sfKaas             = $sfKaas;
           $oSolutionFact->sfValidated        = $sfValidated;
           $oSolutionFact->sfHasSolution      = $sfHasSolution;   
           $oSolutionFact->save();

           $lastInsertedId = $oSolutionFact->sfId;

           return $lastInsertedId; 
 
	 }
	
	//--------------------------------------------------------------------
	public function getByUser($userId)
	 {
		return   ($this->where('lastUserId', '=', $userId)->get())->toArray(); 			
	 }
	 
  //--------------------------------------------------------------------
  public function getByUserFacttype($userId, $factType)
   {
      $rs =    ($this->where('lastUserId', '=', $userId)
                      ->where('sfFactType', '=', $factType)
                      ->get())->toArray();      
      return $rs;
   }

  //--------------------------------------------------------------------
  /*
    get where sfFactType = $factType or
        where sfFactType =0 and sfRelationId = 0

    */

  public function getByUserFacttypeSynonym($userId, $factType)
   {
      $rs =    ($this->where('lastUserId', '=', $userId)
                      ->where('sfFactType', '=', $factType)
                      ->orWhere('lastUserId', '=', $userId)
                      ->where('sfFactType', '=', 0)
                      ->where('sfRelationId', '=', 0)
                      ->get()
                )->toArray();      
      return $rs;
   }

  //--------------------------------------------------------------------
  public function getFactByUser($userId,$pickFactId=0,$hideFact=0)
   {
    if ($pickFactId == 0) {
       $rs = ($this->where('lastUserId', '=', $userId)
                   ->where('sfRating','<', 0)
                   ->where('sfHideFact','=', $hideFact)
                   ->orderBy('sfId', 'ASC')
                   ->get())->toArray(); 
    } else {
       $rs = ($this->where('lastUserId', '=', $userId)
                   ->where('sfRating','<', 0)
                   ->where('sfHideFact','=', $hideFact)
                   ->where('sfId','=', $pickFactId)
                   ->get())->toArray(); 
    }

    return $rs;

   }


  //--------------------------------------------------------------------
  /*
     if problemid =, get all problem for this usr
     if problemId > 0, get the specific record wirh problemId
  */ 
  public function getByProblemUser($problemId,$userId, $hideFact = 0)
   {

      if ($problemId == 0) {
          $rs = ($this->where('lastUserId', '=', $userId)
                      ->where('sfHideFact','=', $hideFact)
                      ->get())
                      ->toArray();    
      } else {
          $rs = ($this->where('lastUserId', '=', $userId)
                      ->where('sfRelationId','=', $problemId)
                      ->where('sfHideFact','=', $hideFact)
                      ->get() )
                      ->toArray(); 
      }

      return $rs;
   }


  //--------------------------------------------------------------------
  /*
     if problemid =, get all problem for this usr
     if problemId > 0, get the specific record wirh problemId
  */ 
  public function getByProblemId($problemId,$userId)
   {

      $rs = ($this->where('lastUserId', '=', $userId)
                  ->where('sfId','=', $problemId)
                  ->get() )
                  ->toArray(); 
      return $rs;

   }


	//--------------------------------------------------------------------
	public function getByUser0($userId)
	 {
        return  $this->where('lastUserId', '=', $userId)
					           ->get(); 			
	 }


  //--------------------------------------------------------------------
     public function getProblem($userId,$parentId,$skipRelationTypeId=0,$hideFact = 0)
     {

        if ($parentId == 0) {
          $rs = $this->where('lastUserId', '=', $userId)
                     ->where('sfRelationId', '>', 0) 
                     ->where('sfHideFact','=', $hideFact)
                     ->where('sfRelationTypeId', '!=', $skipRelationTypeId)
                     ->orderBy('sfRating', 'ASC')
                     ->get();  
        } else {
          $rs = $this->where('lastUserId', '=', $userId)
                     ->where('sfRelationId', '>', 0) 
                     ->where('sfHideFact','=', $hideFact)
                     ->where('sfRelationTypeId', '!=', $skipRelationTypeId)
                     ->where('sfParentId', '=', $parentId)
                     ->orderBy('sfRating', 'ASC')
                     ->get();  
        }
  
      return $rs;

   }

  //--------------------------------------------------------------------
     public function getValidationProblem($userId,$skipRelationTypeId=0, $hideFact=0)
     {

          $rs = $this->where('lastUserId', '=', $userId)
                     ->where('sfRelationId', '>', 0) 
                     ->where('sfRelationTypeId', '!=', $skipRelationTypeId)
                     ->where('sfInferProcessed','=', 0)
                     ->where('sfHideFact','=', $hideFact)
                     ->where('sfValidated','=', 0)
                     ->get();  
  
      return $rs;

   }


  //--------------------------------------------------------------------
  public function getNegativeRatingByUser($userId, $pickProblemId=0, $hideFact = 0)
   {

      if ($pickProblemId == 0) {
          $rs = $this->where('lastUserId', '=', $userId)
                     ->where('sfRating','<', 0)
                     ->where('sfHideFact','=', $hideFact)
                     ->get();  
      } else {
          $rs = $this->where('lastUserId', '=', $userId)
                     ->where('sfId','=', $pickProblemId)
                     ->where('sfRating','<', 0)
                     ->where('sfHideFact','=', $hideFact)
                     ->get();
      }
 
      return $rs;     
   }

  //--------------------------------------------------------------------
  public function getFactKR($userId, $hideFact = 0)
   {
        return  $this->where('lastUserId', '=', $userId)
                     ->where('sfRating','<', 0)
                     ->where('sfRelationId','>', 0)
                     ->where('sfHideFact','=', $hideFact)
                     ->get();       
   }

  //--------------------------------------------------------------------
  public function getNewInferredProblem($parentProblemId,$userId)
   {
        return  $this->where('lastUserId', '=', $userId)
                     ->where('sfValidated','=', 0)
                     ->where('sfParentId','=', $parentProblemId)
                     ->orderBy('sfRating', 'ASC')
                     ->get();       
   }

  //--------------------------------------------------------------------
  // is $sfId the first inferred record for this parentId? 
  public function isFirstInferenceRecord($sfId, $parentId, $userId)
   {
      $isFirst = 0;
      $i       = 0;
      $rs = $this->where('lastUserId', '=', $userId)
                 ->where('sfParentId','=', $parentId)
                 ->orderBy('sfRating', 'ASC')
                 ->get(); 
      foreach ($rs as $rs0) {
          $id  = $rs0->sfId;
          $i++;
          if ($id == $sfId and $i == 1) {
              $isFirst = 1;             
          }
      }

      return $isFirst;
   }


  //--------------------------------------------------------------------
  // is $sfId the first inferred record for this parentId? 
  public function findByKeyword($sTerm, $userId)
   {
      $keywordFound = 0;
      $rs = $this->where('lastUserId', '=', $userId)
                 ->where('sfKeyword','=', $sTerm)
                 ->where('sfFactType','=', 0)
                 ->get(); 
      foreach ($rs as $rs0) {
          $keywordFound  = 1;
      }

      return $keywordFound;
   }


  //--------------------------------------------------------------------
  public function getInferredProblem($problemId,$userId)
   {
        return  $this->where('lastUserId', '=', $userId)
                     ->where('sfId','=', $problemId)
                     ->get();       
   }


  //--------------------------------------------------------------------
  public function retrieveProblemCount($userId)
   {
        $problemCount = 0;
        $rs =  $this->where('lastUserId', '=', $userId)
                    ->get();
        foreach ($rs as $rs0){              
            $problemCount++;
        } 
        return $problemCount;
   }



  //--------------------------------------------------------------------
  public function retrieveFactProcessed($id)
   {
        $factProcessed = 0;
        $rs =  $this->where('sfId', '=', $id)
                    ->get();
        foreach ($rs as $rs0){              
            $factProcessed = $rs0->sfFactProcessed;
        } 
        return $factProcessed;
   }

  //--------------------------------------------------------------------
  public function getInquiryText($userId)
   {
        $inquiryText = "";
        $rs =  $this->where('lastUserId', '=', $userId)
                    ->get();
        foreach ($rs as $rs0){              
            $inquiryText = $rs0->sfInquiry;
        } 
        return $inquiryText;
   }

	//--------------------------------------------------------------------
	public function getByUserSfssid($userId, $sfId)
	 {
		return    $this->where('lastUserId', '=', $userId)
                       ->where('sfId', '=', $sfId)
                       ->get(); 			
	 }


  //--------------------------------------------------------------------
   public function getInferredProblemDesc($userId, $parentId)
    {
        $rs  = $this->where('lastUserId', '=', $userId)
                    ->where('sfParentId', '=', $parentId)
                    ->orderBy('sfRating', 'DESC')
                    ->get();  
        return $rs;
    } 

  //--------------------------------------------------------------------
   public function getByUserOrderByRelationId($userId, $pickProblemId = 0, $hideFact = 0)
    {

      if ($pickProblemId == 0) {
        return ($this->where('lastUserId', '=', $userId)
                      ->where('sfHideFact','=', $hideFact)
                     ->orderBy('sfRelationId', 'ASC')
                     ->orderBy('sfRating', 'ASC')
                     ->get())->toArray();   
      } else {
        return ($this->where('lastUserId', '=', $userId)
                     ->where('sfId', '=', $pickProblemId)
                     ->where('sfHideFact','=', $hideFact)
                     ->orderBy('sfRelationId', 'ASC')
                     ->orderBy('sfRating', 'ASC')
                     ->get())->toArray();  
      }
   
    }  
	 
  //--------------------------------------------------------------------
   public function getSfrSet($userId)
    {
       $sfrs = array();
       $rs =$this->where('lastUserId', '=', $userId)
                 ->get(); 
       foreach ($rs as $rs0) {
          $sfrs[] = $rs0->sfId;
       }
       return $sfrs;   
    } 
   

  //--------------------------------------------------------------------
   public function getProblemCount($userId, $hideFact=0)
    {
       $count = 0;
       $rs =$this->where('lastUserId', '=', $userId)
                 ->where('sfRating', '<', 0)
                 ->where('sfHideFact','=', $hideFact)
                 ->get(); 
       foreach ($rs as $rs0) {
          $count++;
       }
       return $count;   
    } 

  //--------------------------------------------------------------------
   public function getRelationCount($relationId,$userId, $hideFact = 0)
    {
       $count = 0;
       $rs =$this->where('lastUserId', '=', $userId)
                 ->where('sfRelationId', '=', $relationId)
                 ->where('sfHideFact','=', $hideFact)
                 ->get(); 
       foreach ($rs as $rs0) {
          $count++;
       }
       return $count;   
    } 


    //--------------------------------------------------------------------
    public function retrieveFactId($userid, $sfId=0, $hideFact=0)
     {
        $relationId = 0; 

        if ($sfId == 0) {

            $rs = $this->where('lastUserId', '=', $userid)
                       ->where('sfRelationId', '>', 0)
                       ->where('sfRating', '<', 0)
                       ->where('sfHideFact','=', $hideFact)
                       ->get();
        } else {
            $rs = $this->where('lastUserId', '=', $userid)
                       ->where('sfId', '=', $sfId)
                       ->where('sfRelationId', '>', 0)
                       ->where('sfRating', '<', 0)
                       ->where('sfHideFact','=', $hideFact)
                       ->get();
        }
               
        foreach ($rs as $rs0){              
            $relationId = $rs0->sfRelationId;
        }     

        return $relationId;
     } 

    //--------------------------------------------------------------------
    public function retrieveProblemId($userid)
     {
        $sfId = 0; 

        $rs = $this->where('lastUserId', '=', $userid)
                   ->where('sfRelationId', '>', 0)
                   ->where('sfRating', '<', 0)
                   ->get(); 
        foreach ($rs as $rs0){              
            $sfId = $rs0->sfId;
        }     

        return $sfId;
     } 

  //--------------------------------------------------------------------
    
   public function retrieveRelation($userId, $hideFact = 0)
    {
       $relationId = 0 ;
       $rs =$this->where('lastUserId', '=', $userId)
                 ->where('sfRelationId', '>', 0)
                 ->where('sfHideFact','=', $hideFact)
                 ->limit(1)
                 ->get(); 
       foreach ($rs as $rs0) {
          $relationId = $rs0->sfRelationId;
       }
       return $relationId;   
    } 

  //--------------------------------------------------------------------
    
   public function retrieveById($id)
    {
       $relationId = 0 ;
       $rs =$this->where('sfId', '=', $id)
                 ->get(); 
       foreach ($rs as $rs0) {
          $relationId = $rs0->sfRelationId;
       }
       return $relationId;   
    }  
 


  //--------------------------------------------------------------------
    
   public function retrieveBySource($userid,$sfSource, $hideFact = 0)
    {
       $sfId = 0 ;
       $rs =$this->where('lastUserId', '=', $userid)
                 ->where('sfSource', '=', $sfSource)
                 ->where('sfHideFact','=', $hideFact)
                 ->get(); 
       foreach ($rs as $rs0) {
           $sfId = $rs0->sfId;
       }
       return $sfId;      
    } 

    
	//--------------------------------------------------------------------
	public function findByFactText($factText)
     {
        return ($this->where('sfFact', '=', $factText)->get())->toArray(); 
	 }
	 
	//--------------------------------------------------------------------
	public function findById($id)
     {
        return ($this->where('sfId', '=', $id)->get())->toArray(); 
	 }
	 
	//--------------------------------------------------------------------
	public function updateFactRating($id, $rating)
     {		
        $this->where('sfId', $id)->update(array(
             'sfRating'  => $rating
            ));		
	 }

   //--------------------------------------------------------------------
  public function updateFactNameRating($id, $rating, $name)
     { 

        if ( strlen($name) > 255) {
              $name = substr($name,0,255);
        }

        $this->where('sfId', $id)->update(array(
             'sfRating' => $rating,
             'sfFact'   => $name
            ));   
   }
	 
  //--------------------------------------------------------------------
  public function updateValidatedProblem($id,$sfFactProcessed,$sfValidated)
     {    
        $this->where('sfId', $id)->update(array(
             'sfFactProcessed'  => $sfFactProcessed,
             'sfInferProcessed'  => $sfFactProcessed,
             'sfValidated'  => $sfValidated            
            ));   
   }

  //--------------------------------------------------------------------
  public function updateFactProcessed($id,$sfFactProcessed)
     {    
        $this->where('sfId', $id)->update(array(
             'sfFactProcessed'  => $sfFactProcessed            
            ));   
   }

    //-----------------------------------------------------------------  
    public function updateHasextendeddata($id, $hasExtendedData)
     {    
        $this->where('sfId', $id)->update(array(
              'sfHasExtendedData'  => $hasExtendedData
            ));   
     }


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


  //--------------------------------------------------------------------
  public function updateFactSource($sfId,$sfSource)
     {    
        $this->where('sfId', $sfId)->update(array(         
             'sfSource'     => $sfSource       
            ));   
   }


  //--------------------------------------------------------------------
  public function updateFactName($sfId,$sfName)
     {    
        $this->where('sfId', $sfId)->update(array(         
             'sfFact'     => $sfName       
            ));   
   }


  //--------------------------------------------------------------------
  public function updateValidation($sfId, $scalarValue)
     {    
        $this->where('sfId', $sfId)->update(array(         
             'sfvalidated'     => $scalarValue       
            ));   
   }


	//--------------------------------------------------------------------
	public function changeFact($sfId,$sfFact,$sfRelationId, $sfRating,
					    $sfLeftTermId,$sfRelationTypeId,$sfRightTermId, 
                       $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                       $sfssRelationTypeId, $sfssRightTermId)
     {		
        $this->where('sfId', $sfId)->update(array(
             'sfFact'            => $sfFact,
             'sfRelationId'      => $sfRelationId,	
             'sfRating'          => $sfRating,		 
             'sfLeftTermId'      => $sfLeftTermId,	
             'sfRelationTypeId'  => $sfRelationTypeId,				 
             'sfRightTermId'     => $sfRightTermId,
             'sfssSubset'        => $sfssSubset,
             'sfssFact'          => $sfssFact,
             'sfssRelationId'    => $sfssRelationId,
             'sfssLeftTermId'    => $sfssLeftTermId, 
             'sfssRelationTypeId' => $sfssRelationTypeId, 
             'sfssRightTermId'    => $sfssRightTermId
            ));		
	 }


 
	//--------------------------------------------------------------------
	public function deleteById($id)
     {		
         return $this->where('sfId', '=', $id)->delete(); 	
	 }	
	 
  //--------------------------------------------------------------------
  public function deleteBySfrSet($sfrs)
   {    
        $dCount = 0;
        foreach ($sfrs as $key=>$value) {
          $id   =  $value;
          $this->where('sfId', '=', $id)->delete(); 
          $dCount++;
        }   
         return $dCount; 
   }

  //--------------------------------------------------------------------
  public function deleteNonNegative($userid)
     {    
        return $this->where('lastUserId','=', $userid)
                     ->where('sfRating', '>=', 0)
                     ->delete();   
   }

  //--------------------------------------------------------------------
  // regular problems 
  public function deletePositive($userid)
     {    
        return $this->where('lastUserId','=', $userid)
                     ->where('sfRating', '>', -0.01)
                     ->delete();   
   }


  //--------------------------------------------------------------------
  // Delete non negative fact
  public function deleteNonNegativeFact($userid)
     {    

        return $this->where('lastUserId','=', $userid)
                     ->where('sfRating', '>', -0.01)
                     ->where('sfFactType', '=', 0)
                     ->delete();   
   }

  //--------------------------------------------------------------------
  // Delete non negative fact
  public function deleteNonNegativeFactType($userid)
     {    

        return $this->where('lastUserId','=', $userid)
                     ->where('sfRating', '>=', 0)
                     ->where('sfFactType', '<', 2)
                     ->delete();   
   }

  //--------------------------------------------------------------------
  // Inferred problem
  public function deletePositiveProblem($userid)
     {    
        return $this->where('lastUserId','=', $userid)
                     ->where('sfRating', '>', -0.01)
                     ->where('sfFactType', '=', 0)
                     ->delete();   
   }
	//--------------------------------------------------------------------
	public function deleteByUser($userid)
     {		
        return $this->where('lastUserId', '=', $userid)->delete(); 	  
	 }	


  //--------------------------------------------------------------------
  public function deleteProblemByUser($userid)
     {    
        return $this->where('lastUserId', '=', $userid)
                    ->where('sfSource', '<', 99)
                    ->delete();    
   }  


  //--------------------------------------------------------------------
  public function deleteBySource($userid,$source)
     {    
        return $this->where('lastUserId', '=', $userid)
                    ->where('sfSource', '=', $source)
                    ->delete();    
   }     

}
