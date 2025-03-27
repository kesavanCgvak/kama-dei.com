<?php
/*--------------------------------------------------------------------------------
 *  File          : InferenceHelper.php        
 *  Type          : Helper class
 *  Function      : Provide functions for finding solutions in the chatbot
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Methods       : makeSolutionFact(), makeFactRating(), calculateRelationRating(),   
 *                  makeSolution(), makeRelationText, getSolutionRelationString(),
 *                  getSolutionRelationArray(),  makeRelationExtendedData,
 *                  makeOptionExtendedData, protectedAccess
 *                  improved synonym processing
 *                  "url" in extended data with url data.
 *                  Full keyword rating
 *  Version       : 2.5.4
 *  Updated       : 01 September 2022
                     2 August 2023 back button fix. Added to button value
                                                    "*1"    Extended data
                                                    "*3"    Solution KR
                                                    "*5"    Back button
                                                    "*6"    Exit button
                    28 September 2023  Inherit access to optional text
                    23 October   2023  Linked options up to five levels (recursive)
                    28 October   2023  Enhanced linked options
 *---------------------------------------------------------------------------------*/

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Controllers;
use App\Lex\LexClass;
use App\Models\Term;
use App\Models\Personality;
use App\Models\PersonalityTrait;
use App\Models\PersonalityValue;
use App\Models\PersonalityRelation;
use App\Models\PersonalityRelationValue;
use App\Models\RelationTypeSynonym;
use App\Models\SolutionFact;
use App\Models\SolutionRelation;
use App\Models\SolutionFactExdata;
use App\Models\SolutionRelationExdata;
use App\Models\SolutionOption;
use App\Models\SolutionOptionExdata;
use App\Models\OrganizationAssociation;
use App\Models\RelationType;
use App\Models\Relation;
use App\Models\RelationLanguage;
use App\Models\RelationLink;
use App\Models\RelationTypeFilter;
use App\Models\ExtendedLink;
use App\Models\ExtendedEntity;
use App\Models\ExtendedAttribute;
use App\Models\ExtendedAttributeType;
use App\Models\ExtendedLinkTranslation;
use App\Models\ExtendedEAV;
use App\Models\ConsumerUserPersonality;
use App\Helpers\FunctionHelper;

class InferenceHelper
{
	
    /*
      Inference step (relation_type_filter)
      1: question translation 
        (has question translation to): -> (largestIE)getLargestText()
      2: classification preprocessing
        (64=   is a type of)         : -> (Inferencehelper)function hasSubset()
      3: synonym processing 
        (can be synonym to)          : -> (Inferencehelpetr)function makeEquivalentSolutionFact()   
      4: changing subset fact        
        (can desire)                 : -> (InferenceHelper)function changeSubsetFact()
      5: intermediate results 
        (is a type of)               : -> (Inferencehelper)makeSolutionRelation()
        (is the noun form of)
      6: replace pronoun 
        (is a member of)             : -> (ParsingHelper)function replacePronoun()
      7: replace pronoun 
        (is a type of)               : -> (ParsingHelper)function replacePronoun()   
      8: access protected data       : -> (InferenceHelper) function makeRelationExtendedData(),
        (can access protected data from)                function makeOptionExtendedData()

      9: can be served at            : ->  (InferenceHelper) function retrieveByStep($lStep);
         (special lex processing) 
        
     10. termId: has value rating KR : -> (InferenceHelper)function getSlideBarArray1()   52026

     11. relationTypeId: can rate values: -> ()
         used to omit records in solution_relation

     12. term: person
     
     13. can be      

     */


    //--------------------------------------------------------------------
    public function makeSolutionFact($aSplitText, $userid, $orgid, $inquiry,$bLang, $tLang,
    	    $singleUtterance=0, $multipleTriple=0)
     {	

        // Instantiate classes
        $oTerm                = new Term();
        $oRelation            = new Relation();
        $oRelationTypeSynonym = new RelationTypeSynonym();
        $oRelationType        = new RelationType();
        $oSolutionFact        = new SolutionFact();
        $oRelationLanguage    = new RelationLanguage();
        $oFunctionHelper      = new FunctionHelper();
        $oParsingHelper       = new ParsingHelper();

        // Get relation type "is a member of"
        $auxRelTypeName = "is a member of";
        $auxRelTypeId   = $oRelationType->retrieveIdByName($auxRelTypeName);
 
        // Get term id for "verb"
        $auxTermName = "verb";
        $auxTermId   = $oTerm->retrieveTermIdByName($auxTermName);       
        $rtsCount = 0;
        $sfInput     = "";
        $mainConcept = 0;
        $zero = 0;
        $lang = $bLang;
        $sfSubset    = 0;

        if ($singleUtterance == 1 and $multipleTriple == 1) {
        	$mainConcept = 1;               // detect main concept
        }

        $aUtterance = $aSplitText;
        $aSplitText = $oParsingHelper->replaceTerm1ByPerson($aSplitText);
        $arraylen = sizeof($aSplitText);
        $iLim = $arraylen - 1 ;
        $wRelationId = 0;

        for ($i=0;$i<$arraylen;$i++) {

            // $textString = Term1, verb, term2 
            $textString   = $aSplitText[$i];
            $textString0  = $aUtterance[$i];

            // variables to find $sfRelationId that matches session fact text
            $sfRelationId     = 0;
            $sfLeftTermId     = 0;
            $sfRelationTypeId = 0;
            $sfRightTermId    = 0;
            $isFound = 1; // 0= term not found; 1 = term found

            //  find term1
            $termName = "";
            $RTName = "";

            $arrayText  = explode(",",$textString);
            $arrayText0  = explode(",",$textString0);

            // FIND LEFT TERM Id

            if (isset($arrayText[0])) {
                $termName = $arrayText[0];			
                $leftName0= $arrayText0[0];  				  
                $sfLeftTermId = $oTerm->retrieveTermIdByName($termName);// find term 1
            }
 
            // FIND RELATION TYPE SYNONYM
            $arrayRTSId   = array();
            $arrayRTSName = array();

            if (isset($arrayText[1])) {
                $termName = strtolower($arrayText[1]);  
                $RTName0  = strtolower($arrayText0[1]);
                $RTName   = $termName;                          
                        
                /*  if relation type synonym is found, replace verb */
                // Find termId of verb
                $termSynId = $oTerm->retrieveTermIdLikeName($termName);// find verb
                //$termSynId = $oTerm->retrieveTermIdByName($termName);// find verb

                if (empty($termSynId)) {
                    $isFound = 0;                           // term not found
                } else {
                             
                    // Find relationTypeId in relation_type_synonym
                    // $rtRelationTypeId = $oRelationTypeSynonym->retrieveRelationTypeIdByTermdId($termSynId); 
                    $rsRTS = $oRelationTypeSynonym->getRelationTypeIdByTermdId($termSynId); 

                    $rtsCount = 0;
                    foreach ($rsRTS as $rts){
                        $wrkRTSId     = $rts->rtSynonymRelationTypeId; 
                        $arrayRTSId[] = $wrkRTSId;  
                        $rtRelationTypeId = $wrkRTSId;
                        $relationTypeName = "";
                        $relationTypeName = $oRelationType->retrieveNameById($rtRelationTypeId);       
                        $arrayText[1] = $relationTypeName;   // verb->relationTypeSynonymName
                        $arrayRTSName[] = $relationTypeName;
                        $rtsCount++;                                  
                    }   
                             
                }                         

            }
	 
            // FIND RIGHT TERM Id

            if (isset($arrayText[2])) {
                $termName = $arrayText[2];	
                $rightName0 = $arrayText[2];  						  
                $sfRightTermId = $oTerm->retrieveTermIdByName($termName);// find term 2
            }					  		
   
            // FIND RELATION ID

            $sfRating    = 0;
            $sfSubset    = 0;
            $sfssFact    = "";
            $sfFact      = "";
            $sfssRelationId = 0;
            $sfssLeftTermId = 0;	
            $sfSource    = 0;
            $sfInput = "";

            $sfssRelationTypeId = 0;
            $sfssRightTermId = 0;
            $sfInquiry    = $inquiry;
            $sfParentFact = "";
            $sfParentRating = 0;
            $wRelationId = 0;

            // SET FACT FIELDS
            for ($j=0; $j< $rtsCount; $j++) {

               // get records
               $sfFact      = "";
               $sfRelationTypeId = 0;
               $relationTyepName = "";
               if (isset($arrayRTSId[$j])) {
                  $sfRelationTypeId = $arrayRTSId[$j];             	
               }
               if (isset($arrayRTSName[$j])) {
                  $relationTyepName = $arrayRTSName[$j];              	
               }

               $sfRelationId = 
                 $oRelation->retrieveByLeftTypeRightId($sfLeftTermId, $sfRelationTypeId, $sfRightTermId );

               $arrayText[1] = $relationTypeName;
               $aSplitText[$i] = implode(",", $arrayText);

               $sfInput = $aSplitText[$i];

          
               //$sfFact = $this->makeProblemText($sfRelationId,$zero,$lang,$lang); 
                $aProblem = $this->makeProblemText($sfRelationId,$orgid,$bLang,$tLang);
                $sfFact     =  $aProblem['sfRelation'];
                $sfLanguage =  $aProblem['sfLanguage'];

               // insert fact record
               if ($mainConcept == 0 or ($mainConcept == 1 and $i == $iLim)) {
                   $sfInput = $leftName0.",".$RTName0.",".$rightName0;

                 //if ( $regularSet == 0 or ($regularSet==1 and $sfRelationId > 0 )    ) { 

                  $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                    $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang,
                    $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                    $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,$sfParentFact,
                    $sfParentRating,$userid,$sfSource,$sfInput); 
                 //}                 

                }


                if ($sfRelationId > 0 ) {
                     $wRelationId = $sfRelationId;                     
                } 


            }

            // New code: catch relation not found in this function
  
            if ($rtsCount == 0 and $sfRelationId == 0) {
                   $sfRelationTypeId = $oRelationType->retrieveIdByName($RTName);
                   $sfRelationId = $oRelation->retrieveByLeftTypeRightId($sfLeftTermId, 
                                  $sfRelationTypeId, $sfRightTermId );

                   if ($sfRelationId > 0) {
                      $sfInput = $leftName0.",".$RTName0.",".$rightName0;    
                      //$sfFact = $this->makeProblemText($sfRelationId,$zero,$lang,$lang); 
                      $aProblem = $this->makeProblemText($sfRelationId,$orgid,$bLang,$tLang);
                      $sfFact     =  $aProblem['sfRelation'];
                      $sfLanguage =  $aProblem['sfLanguage'];                      
                     
                      $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                        $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang, 
                        $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                        $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,$sfParentFact,
                        $sfParentRating,$userid,$sfSource,$sfInput); 
                        $wRelationId = $sfRelationId;
                   }
            }

        }

        return $wRelationId;
     }

    //-----------------------------------------------------
    public function insertCustomProblem($sfRelationId, $sfRightTermId,$sfRating, $userid, $orgid, $bLang, $tLang)
     {  

        // Instantiate classes
        $oRelation            = new Relation();
        $oSolutionFact        = new SolutionFact();

        // Initializa variables
        $sfSubset    = 0;
        $sfssFact    = "";
        $sfFact      = "";
        $sfInquiry   = "";
        $sfssRelationId = 0;
        $sfssLeftTermId = 0;  

        $sfRelationTypeId = 0;
        $sfLeftTermId = 0;

        $sfssRelationTypeId = 0;
        $sfssRightTermId = 0; 
        $sfParentFact = "";
        $sfParentRating = 0;
        $sfSource    = 3;
        $sfLanguage = $bLang;
        $pickProblemId = 0;

        $aProblem = $this->makeProblemText($sfRelationId,$orgid,$bLang,$tLang);
        $sfFact     =  $aProblem['sfRelation'];
        $sfLanguage =  $aProblem['sfLanguage'];
        $sfInput    =  $sfFact;


        $pickProblemId = $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage, 
                $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,$sfParentFact,
                $sfParentRating,$userid,$sfSource,$sfInput); 

        return $pickProblemId;
                        
    }  


    //-----------------------------------------------------
    public function depleteTerm($userid,$aTermSet)
    {
        $oSolutionRelation  = new SolutionRelation();

        $wInput   = "";
        $rs = $oSolutionRelation->getDistinctByUser($userid);   

        foreach ($rs as $rs0){             
            $srInput   =  $rs0['srInput'];

            if ($srInput != $wInput) {
                $aInput    =  explode(",",$srInput);
                $len       =  count($aInput); 

            for($i=0;$i<$len;$i++) {
                $sInput = $aInput[$i];
                $tsLen  =  count($aTermSet); 


                for($j=0;$j<$tsLen;$j++) {
                   $aNew = array();
                   if( $sInput == $aTermSet[$j] ) {
                       unset($aTermSet[$j]);
                       
                       foreach($aTermSet as $ky => $val) {
                          $aNew[] = $val;
                       }
                       $aTermSet = $aNew;
                       $j = $tsLen + 1;

                   }

                }
            } 


            }
            $wInput = $srInput;

        }    
        
        return $aTermSet;
    }  
	 

    //---------------------------------------------------------------------
    public function getGreeting($aSplitText,$orgid,$portalType,$baseLang,$messageLang) 
    {

        $oTerm                = new Term();
        $oRelation            = new Relation();
        $oRelationType        = new RelationType();
        $oMessage             = new \App\Models\Message(); 

        $relationTypeName = "is a member of";
        $rightTermName = "greetings";
        $limit = 1;

        $triple = $aSplitText[0];
        $aTriple  = explode(",",$triple);
        $alen = sizeof($aTriple);
        $baseLang = "en";
        $aMsg = array();

        if ($alen == 3) {

          // leftTermName is the greeting word, e.g. "hi"
          $leftTermName = $aTriple[2];

          // find leftTermId
          $leftTermId = $oTerm->retrieveTermIdByName($leftTermName);

          // find relationTypeId
          $relationTypeId = $oRelationType->retrieveIdByName($relationTypeName);  

          // find rightTermId
          $rightTermId = $oTerm->retrieveTermIdByName($rightTermName);   

          // search relation
          $relationId = $oRelation->retrieveByLeftTypeRightId($leftTermId, $relationTypeId, $rightTermId);
          if ($relationId > 0) { 

             $sGreeting = ucfirst($leftTermName).". ";
             $mType = "greeting";
             $aMsg = ['attribute'=>$mType , 'language'=>$baseLang , 'value'=>$sGreeting ];            
             //$messageCode = $oMessage->retrieveMessageCode($baseLang,$leftTermName);

          }

        }

        return $aMsg;
    } 


    //--------------------------------------------------------------------
    public function makeSingleSolutionFact($orgid, $userid, $aTermSet, $lang)
     {  

        // Instantiate classes
        $oTerm                = new Term();
        $oRelation            = new Relation();
        $oRelationType        = new RelationType();
        $oSolutionFact        = new SolutionFact();
        $oRelationTypeFilter  = new RelationTypeFilter();     
                
        // Initializa variables
        $sfRating    = 0;
        $sfSubset    = 0;
        $sfssFact    = "";
        $sfFact      = "";
        $sfRelationId = 0;
        $sfssRelationId = 0;
        $sfssLeftTermId = 0;  
        $sfRelationTypeId = 0;
        $sfLeftTermId = 0;
        $sfRightTermId = 0;
        $sfssRelationTypeId = 0;
        $sfssRightTermId = 0; 
        $sfParentFact = "";
        $sfParentRating = 0;
        $wRelationId = 0;
        $sfSource    = 0;
        $rtgId = 8;   // used in protected access

        $len = count($aTermSet);  

        for ($i=0; $i < $len; $i++) {
            $sTerm       =  $aTermSet[$i];
            $sfInquiry   = $sTerm;
            $sfInput     = $sTerm; 

            // get right term id
            //$sfRightTermId = $oTerm->retrieveTermIdByName($sTerm);
            $sfRightTermId = 0;
            $tmp = $oTerm->retrieveFilteredTermId($orgid,$sTerm,$rtgId);
            foreach($tmp as $rs0) {                   
                $sfRightTermId = $rs0->termId;               
            }                 
 

            // insert fact record
            $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
              $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang,
              $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
              $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,$sfParentFact,
              $sfParentRating,$userid,$sfSource,$sfInput); 
        }


     }


    //--------------------------------------------------------------------
	  /*  Make an equivalent solution fact. Use right hand logic and synonyms 
    */
    //public function makeEquivalentSolutionFact($userid, $orgId, $delete, $lang)
    public function makeEquivalentSolutionFact($userid, $orgId, $delete, $lang)

     {
        // Instantiate classes
        $oSolutionFact         = new SolutionFact();	
        $oRelationTypeFilter   = new RelationTypeFilter();		
        $oRelation             = new Relation();
        $oRelationType         = new RelationType();
 
        // Get relation type filter
        $step = 3 ;    // step 3: can de synonym to ////
        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);
        $rtgId = 8;
        $wRelationId = 0;
        $sfSource = 0;

        // retrieve session fact for this userid a
        $rsdata = $oSolutionFact->getByUser($userid);
        if (!empty($rsdata)) {  
					    
            foreach ($rsdata as $rsFact){						  
                $sfId             = $rsFact['sfId']; 
                $sfFact           = $rsFact['sfFact'];
                $sfRelationId     = $rsFact['sfRelationId'];
                $sfLeftTermId     = $rsFact['sfLeftTermId'];
                $sfRelationTypeId = $rsFact['sfRelationTypeId'];
                $sfRightTermId    = $rsFact['sfRightTermId'];
                $sfRating         = $rsFact['sfRating'];
                $sfSubset         = $rsFact['sfssSubset'];
                $sfSubset = 0;
                $sfssFact         = $rsFact['sfssFact'];
                $sfssRelationId   = $rsFact['sfssRelationId'];
                $sfssLeftTermId   = $rsFact['sfssLeftTermId'];
                $sfssRelationTypeId = $rsFact['sfssRelationTypeId'];                
                $sfssRightTermId  = $rsFact['sfssRightTermId'];
                $sfInquiry        = $rsFact['sfInquiry'];
                $sfParentFact     = $rsFact['sfParentFact'];
                $sfParentRating   = $rsFact['sfParentRating'];
                $userid           = $rsFact['lastUserId'];
                $sfInput          = $rsFact['sfInput'];

                $sourceRelationId = $sfRelationId;
			
                  $LTA  = $sfLeftTermId;   // orignal leftTermId
                  $RTA  = $sfRightTermId;  // original rightTermId
                  $LTB  = 0;               // leftTermId synonym
                  $RTB  = 0;               // rightTermId synonym


                  // get right term synonym
                  $aRTB =  $oRelation->getOrgRTSynonym($RTA, $rtFilterId, $orgId, $rtgId);                 
                  $maxRB = sizeof($aRTB);

                  // get left term synonym
                  $aLTB =  $oRelation->getLTSynonym($LTA, $rtFilterId); 
                  $maxLB = sizeof($aLTB);

                  // seek solution fact KR ///////////////////////////////////
                  $wRelationId = 0;
                  $updateCount = 0;
                  $loopCount = 1;
                  $loop = 1;      // loop = 1 (stay in loop); loop = 0 (exit loop)
                  while ($loop == 1) {

                    switch ($loopCount) {

                      case 1:  // find solution fact LTA, RelationType, RTA
                        $sfRelationId = $oRelation->
                          retrieveByLeftTypeRightId($LTA, $sfRelationTypeId,$RTA);
 
                        if ($sfRelationId > 0) {
                           $sfLeftTermId  = $LTA;
                           $sfRightTermId  = $RTA;

                             if ($sourceRelationId = 0) {
                                $this->updateTSFact($sfId,$sfRelationId, $sfLeftTermId,
                                   $sfRelationTypeId,$sfRightTermId); 
                             } else {

                                $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                                   $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang, 
                                   $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                                   $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,$sfParentFact,
                                   $sfParentRating,$userid,$sfSource,$sfInput); 
                             }

                           $sourceRelationId = $sfRelationId; 
                           $wRelationId =  $sfRelationId;
                        }
                        break;

                      case 2:  // find solution fact LTA, RelationType, RTB
                        for ($j=0;$j<$maxRB; $j++) {
                          $RTB = $aRTB[$j];
                          $sfRelationId = $oRelation->
                            retrieveByLeftTypeRightId($LTA, $sfRelationTypeId,$RTB);

                          if ($sfRelationId > 0) {
                             $sfLeftTermId  = $LTA;
                             $sfRightTermId   = $RTB;

                             if ($sourceRelationId = 0) {
                                $this->updateTSFact($sfId,$sfRelationId, $sfLeftTermId,
                                   $sfRelationTypeId,$sfRightTermId); 
                             } else {
            
                                $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                                   $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang,
                                   $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                                   $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,$sfParentFact,
                                   $sfParentRating,$userid,$sfSource,$sfInput); 
                             }

                             $sourceRelationId = $sfRelationId; 
                             $wRelationId =  $sfRelationId;                     
                          }
                        }


                        break;

                      case 3:  // find solution fact LTB, RelationType, RTA
                        for ($j=0;$j<$maxLB; $j++) {
                          $LTB = $aLTB[$j];
                          $sfRelationId = $oRelation->
                            retrieveByLeftTypeRightId($LTB, $sfRelationTypeId,$RTA);


                          if ($sfRelationId > 0) {
                             $sfLeftTermId  = $LTB;
                             $sfRightTermId   = $RTA;

                             if ($sourceRelationId = 0) {
                                $this->updateTSFact($sfId,$sfRelationId, $sfLeftTermId,
                                   $sfRelationTypeId,$sfRightTermId); 
                             } else {
              
                                $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                                   $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang, 
                                   $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                                   $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,$sfParentFact,
                                   $sfParentRating,$userid,$sfSource,$sfInput); 
                             }

                             $sourceRelationId = $sfRelationId;  
                             $wRelationId =  $sfRelationId;

                          }
                        }

                        break;

                      case 4:  // find solution fact LTB, RelationType, RTB
                        $RTB  = $oRelation->retrieveLTermSynonym($sfRightTermId, $rtFilterId); 
                       
                        $sfRelationId = $oRelation->
                          retrieveByLeftTypeRightId($LTB, $sfRelationTypeId,$RTB);

                        if ($sfRelationId > 0) {
                           $sfLeftTermId  = $LTB;
                           $sfRightTermId  = $RTB;

                             if ($sourceRelationId = 0) {
                                $this->updateTSFact($sfId,$sfRelationId, $sfLeftTermId,
                                   $sfRelationTypeId,$sfRightTermId); 
                             } else {
          
                                $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                                   $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang, 
                                   $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                                   $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,$sfParentFact,
                                   $sfParentRating,$userid,$sfSource,$sfInput); 
                             }
 
                           $sourceRelationId = $sfRelationId;
                           $wRelationId =  $sfRelationId;                                    
                        }
                        break;

                      default:
                        $loop = 0;
                        break;
                    }
                    $loopCount++;
                  }
  
            }
        }

        // remove non negative facts
        $oSolutionFact->deleteNonNegative($userid);        

        // remove duplicate records

        if ($delete == 1) {
          $rs = $oSolutionFact->getByUserOrderByRelationId($userid);
          $wrId = 0;

          foreach($rs as $rs0) {
           $sfId              = $rs0['sfId'];
           $sfRelationId      = $rs0['sfRelationId'];
           $sfRating          = $rs0['sfRating'];

           if ($sfRelationId == $wrId ) {        
              $oSolutionFact->deleteById($sfId);    
   
           } else {
              $wrId  = $sfRelationId;
           }
          }           
        }

        $wRelationId = $oSolutionFact->retrieveRelation($userid);
        
        return $wRelationId;
     }


    //-------------------------------------------------------------------
    public function getSynonymArray($aTerm)
    {

        $oRelationTypeFilter   = new RelationTypeFilter();    
        $oRelation             = new Relation();
        $oTerm                 = new Term();

        // Get relation type filter
        $step = 3 ;                // step 3: can de synonym to ////
        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);
        $aLen = count($aTerm);     // term array length
        $aSynonym = array();       // array with synonyms


        for ($i=0; $i< $aLen; $i++) {
            $term = $aTerm[$i];
            $termId = $oTerm->retrieveTermIdByName($term);

            // find left term synonyms
            $rs = $oRelation->getRightTermRelationType($termId, $rtFilterId);
            foreach ($rs as $rs0) {
                $synonymTerm = "";
                $synonymId   = $rs0->leftTermId;
                if ($synonymId != $termId) {
                   $synonymTerm = $oTerm->retrieveTermName($synonymId); 
                   $aSynonym[]  = $synonymTerm;                  
                }        
            }

            // find right term synonyms
            $rs = $oRelation->getByLeftTermRelationType($termId, $rtFilterId);
            foreach ($rs as $rs0) {
                $synonymTerm = "";
                $synonymId   = $rs0->rightTermId;
                if ($synonymId != $termId) {
                   $synonymTerm = $oTerm->retrieveTermName($synonymId); 
                   $aSynonym[]  = $synonymTerm;                  
                }  
            }

        }

        return $aSynonym;
    }

    //--------------------------------------------------------------------
    /* isRegularSet()
       p

    */
    public function isRegularSet($aSplitText)
     {
        $regularSet = 1;
        $arraylen = sizeof($aSplitText);

        for ($i=0;$i<$arraylen;$i++) {
            $textString = $aSplitText[$i];
            $tripleArray = explode(",",$textString); 
            $tripleLen = sizeof($tripleArray);
            if ($tripleLen != 3) {
               $regularSet = 0;            // it is not a regular set
            }
        }  

        return $regularSet;

     }

    //--------------------------------------------------------------------
    /* makeEquivalentSingleSolutionFact()
       process synonym for key word matching
       equivalent facts are given a predefined rating of -1 
    */
    public function makeEquivalentSingleSolutionFact($userid, $orgId, $delete, $lang)
     {


        // Instantiate classes
        $oSolutionFact         = new SolutionFact();  
        $oRelationTypeFilter   = new RelationTypeFilter();    
        $oRelation             = new Relation();
        $oRelationType         = new RelationType();
  
        // Get relation type filter
        $step = 3 ;    // step 3: can de synonym to ////
        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);
        $rtgId = 8;
        $wRelationId = 0;

        // retrieve session fact for this userid a
        $rsdata = $oSolutionFact->getByUser($userid);
        if (!empty($rsdata)) {  
              
            foreach ($rsdata as $rsFact){             
                $sfId             = $rsFact['sfId']; 
                $sfFact           = $rsFact['sfFact'];
                $sfRelationId     = $rsFact['sfRelationId'];
                $sfLeftTermId     = $rsFact['sfLeftTermId'];
                $sfRelationTypeId = $rsFact['sfRelationTypeId'];
                $sfRightTermId    = $rsFact['sfRightTermId'];
                $sfRating         = $rsFact['sfRating'];
                $sfSubset         = $rsFact['sfssSubset'];
                $sfSubset  = 0;
                $sfssFact         = $rsFact['sfssFact'];
                $sfssRelationId   = $rsFact['sfssRelationId'];
                $sfssLeftTermId   = $rsFact['sfssLeftTermId'];
                $sfssRelationTypeId = $rsFact['sfssRelationTypeId'];                
                $sfssRightTermId  = $rsFact['sfssRightTermId'];
                $sfInquiry        = $rsFact['sfInquiry'];
                $sfParentFact     = $rsFact['sfParentFact'];
                $sfParentRating   = $rsFact['sfParentRating'];
                $userid           = $rsFact['lastUserId'];
                $sfSource         = $rsFact['sfSource'];
                $sfInput          = $rsFact['sfInput'];

                $sourceRelationId = $sfRelationId;
     
                // solution fact does not exit, find a synonym
                // use right hand logic and left hand logic on right term

                  $LTA  = $sfLeftTermId;   // orignal leftTermId
                  $RTA  = $sfRightTermId;  // original rightTermId
                  $LTB  = 0;               // leftTermId synonym
                  $RTB  = 0;               // rightTermId synonym

                  // get right term synonym
                  $aRTB =  $oRelation->getOrgRTSynonym($RTA, $rtFilterId, $orgId, $rtgId);                 
                  $maxRB = sizeof($aRTB);

                  // get left term synonym
                  //$aLTB =  $oRelation->getLTSynonym($LTA, $rtFilterId); 
                  //$maxLB = sizeof($aLTB);
                  $aLTB = array();
                  $maxLB = 0;

                  // seek solution fact KR ///////////////////////////////////
                  $wRelationId = 0;
                  $updateCount = 0;
                  $loopCount = 1;
                  $loop = 1;      // loop = 1 (stay in loop); loop = 0 (exit loop)
                  while ($loop == 1) {

                    switch ($loopCount) {

                      case 1:  // find solution fact LTA, RelationType, RTA
                          $sfLeftTermId  = $LTA;
                          $sfRightTermId  = $RTA;
                          $sfRelationId = 0;
                          $sfnFact     = $sfFact;
                          $rs2 = $oRelation->getByRightTerm($sfRightTermId);

                          foreach ($rs2 as $rs0) {
                            $sfRelationId = $rs0->relationId;
                            $sfLeftTermId = $rs0->leftTermId;
                            $sfRelationTypeId = $rs0->relationTypeId;
                            $sfShortText = $rs0->shortText;
                            if (isset($sfShortText)) {
                               $sfnFact = $sfShortText;
                            }

                           if ($sfLeftTermId>0 and $sfRelationTypeId>0 and $sfRightTermId>0) {
                             $oSolutionFact->insertFact($sfnFact, $sfRelationId, $sfRating,
                              $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang,
                              $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                              $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,
                              $sfParentFact,$sfParentRating,$userid,$sfSource,$sfInput);  
                            }


                          }


                        break;

                      case 2:  // find solution fact LTA, RelationType, RTB
                          for ($j=0;$j<$maxRB; $j++) {
                            $RTB = $aRTB[$j];
                            $sfLeftTermId  = $LTA;
                            $sfRightTermId   = $RTB;
                            $sfnFact     = $sfFact;
                            $rs2 = $oRelation->getByRightTerm($sfRightTermId);

                            foreach ($rs2 as $rs0) {
                              $sfRelationId = $rs0->relationId;
                              $sfLeftTermId = $rs0->leftTermId;
                              $sfRelationTypeId = $rs0->relationTypeId; 
                              $sfShortText = $rs0->shortText;
                              if (isset($sfShortText)) {
                                 $sfnFact = $sfShortText;
                              }

                              if ($sfLeftTermId>0 and $sfRelationTypeId>0 and $sfRightTermId>0) {
                               $oSolutionFact->insertFact($sfnFact, $sfRelationId, $sfRating,
                                 $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang, 
                                 $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                                 $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,
                                 $sfParentFact,$sfParentRating,$userid,$sfSource,$sfInput);                              
                              }
                              

                            }                             

 
                          }


                        break;

                      case 3:  // find solution fact LTB, RelationType, RTA
                        /*
                          for ($j=0;$j<$maxLB; $j++) {
                            $LTB = $aLTB[$j];
                            $sfLeftTermId  = $LTB;
                            $sfRightTermId   = $RTA;
                            $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                                $sfLeftTermId, $sfRelationTypeId, $sfRightTermId,
                                $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                                $sfssRelationTypeId,$sfssRightTermId,
                                $sfInquiry,$sfParentFact,$sfParentRating,$userid);
                          }
                        */
                        break;

                      case 4:  // find solution fact LTB, RelationType, RTB
                          $sfLeftTermId  = $LTB;
                          $sfRightTermId  = $RTB;





                          if ($sfLeftTermId>0 and $sfRelationTypeId>0 and $sfRightTermId>0) {

                          $sfnFact     = $sfFact;
                          $rs2 = $oRelation->findByLeftTypeRightId($sfLeftTermId,$sfRelationTypeId,$sfRightTermId);

                          foreach ($rs2 as $rs0) {
                              $sfRelationId = $rs0->relationId;
                              $sfLeftTermId = $rs0->leftTermId;
                              $sfRelationTypeId = $rs0->relationTypeId; 
                              $sfShortText = $rs0->shortText;
                              if (isset($sfShortText)) {
                                 $sfnFact = $sfShortText;
                              }
                           }


                            $oSolutionFact->insertFact($snFact, $sfRelationId, $sfRating,
                              $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang, 
                              $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                              $sfssRelationTypeId,$sfssRightTermId,$sfInquiry,$sfParentFact,
                              $sfParentRating,$userid,$sfSource,$sfInput);
                          }


                        break;

                      default:
                        $loop = 0;
                        break;
                    }
                    $loopCount++;
                  }
  
            }
        }

        // remove duplicate records
        if ($delete == 1) {
          $rs = $oSolutionFact->getByUserOrderByRelationId($userid);
          $wrId = 0;
          foreach($rs as $rs0) {
             $sfId              = $rs0['sfId'];
             $sfRelationId      = $rs0['sfRelationId'];
             $sfRating          = $rs0['sfRating'];
             if ($sfRelationId == $wrId and $sfRating == 0) {         
                $oSolutionFact->deleteById($sfId);
             } else {
                $wrId  = $sfRelationId;
             }
          }
        }

        //
        $wRelationId = $oSolutionFact->retrieveRelation($userid);

        return $wRelationId;
     }


    //----------------------------------------------------------------
    public function updateTSFact($sfId,$sfRelationId, $sfLeftTermId,$sfRelationTypeId,$sfRightTermId)
     {
        // Instantiate classes
        $oSolutionFact         = new SolutionFact();  
        $oRelationType         = new RelationType();
        $oTerm                 = new Term();
        
        $sfFact = "";
        // get term and relationtype names
        $sfLeftName  = $oTerm->retrieveTermName($sfLeftTermId);
        $sfRTName    = $oRelationType->retrieveNameById($sfRelationTypeId);
        $sfRightName = $oTerm->retrieveTermName($sfRightTermId); 

        $sfFact = $sfLeftName . " " . $sfRTName . " " . $sfRightName;                      
        $oSolutionFact->updateSolutionFact($sfId,$sfFact,$sfRelationId,
                        $sfLeftTermId,$sfRelationTypeId,$sfRightTermId);  

     }
  
    //--------------------------------------------------------------------
    /*  make subset fact when the input text has  term 2 with subset (2)  */
    /*   TERM (term2) has subsect if there a classifying relation         */
    /*   e.g.   person can desire car
    /*              sedan is a type of car                                */
    /*              electric car is a type of car                         */   

    public function makeSubsetFact($userid, $lang)
     {
        // Instantiate classes
        $oSolutionFact         = new SolutionFact();	
        $oRelationTypeFilter   = new RelationTypeFilter();		
        $oRelation             = new Relation();
        $oRelationType         = new RelationType();
        $oTerm                 = new Term();
	
        // get subsets from session facts
        $step = 2 ;    // step 2: filters for classification of session facts ////
                       // is a type of                                        ///
        $hasSubset = 0;

        $sfParentFact = "";
        $sfParentRating = 0;

        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);
		
        // retrieve session fact for this userid
        $rsdata = $oSolutionFact->getByUser($userid);		
        if (!empty($rsdata)) {  
					    
            foreach ($rsdata as $rsFact){						  
                $sfId             = $rsFact['sfId'];
                $sfFact           = $rsFact['sfFact'];
                $sfRelationId     = $rsFact['sfRelationId'];
                $sfRating         = $rsFact['sfRating'];
                $sfLeftTermId     = $rsFact['sfLeftTermId'];
                $sfRelationTypeId = $rsFact['sfRelationTypeId'];
                $sfRightTermId    = $rsFact['sfRightTermId'];
                $sfInquiry        = $rsFact['sfInquiry'];
				
                //  find classification relation with right term
                $ssCount = 0;        
                $ssRelation = $oRelation->getByRelTypeRightTerm($rtFilterId,$sfRightTermId);


                foreach ($ssRelation as $rsRel) {
                    $ssCount++;
                    $sfssRelationId      = $rsRel->relationId;
                    $sfssLeftTermId      = $rsRel->leftTermId;
                    $sfssRelationTypeId  = $rtFilterId;
                    $sfssRightTermId     = $rsRel->rightTermId;
                    $sfSubset            = 1;
                    $hasSubset           = 1;

                    $sfssLeftName     = $oTerm->retrieveTermName($sfssLeftTermId);
                    $sfssRTName       = $oRelationType->retrieveNameById($sfssRelationTypeId);
                    $sfssRightName    = $oTerm->retrieveTermName($sfssRightTermId);					
                    $sfssFact = $sfssLeftName . " " . $sfssRTName . " " . $sfssRightName;	
					
                    // add subset records
                    $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                         $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang,
                         $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                         $sfssRelationTypeId,$sfssRightTermId,
                         $sfInquiry,$sfParentFact,$sfParentRating,$userid); 
                }

                // delete superset record
                if ($ssCount > 0) {
                     $oSolutionFact->deleteById($sfId);                  
                }

            }
        }

        return $hasSubset;
     }
 

    //--------------------------------------------------------------------
    /*  move transform deepest categorization level into solution       */

    public function changeSubsetFact($userid)
     {
        // Instantiate classes
        $oSolutionFact         = new SolutionFact();    
        $oRelationTypeFilter   = new RelationTypeFilter();      
        $oRelation             = new Relation();
        $oRelationType         = new RelationType();
        $oTerm                 = new Term();
    
        // get subsets from session facts
        $step = 4 ;    // 84: can desire: filter for changing subset fact ////
        $hasSubset = 0;

        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);
        $sfLeftTermId  = 1596;
        $sfssSubset  = 0;
        $sfssFact    = "";
        $sfssRelationId = 0;
        $sfssLeftTermId = 0;
        $sfssRelationTypeId = 0;
        $sfssRightTermId = 0;
        $sfRelationTypeId = $rtFilterId;

        // retrieve session fact for this userid
        $rsdata = $oSolutionFact->getByUser($userid);       
        if (!empty($rsdata)) {  
                        
            foreach ($rsdata as $rsFact){                         
                $sfId             = $rsFact['sfId'];
                $sfFact           = "";
                $sfRating         = $rsFact['sfRating'];

                $sfRightTermId    = $rsFact['sfssLeftTermId'];
                $sfRelationId   = $oRelation->retrieveByLeftTypeRightId($sfLeftTermId,
                       $sfRelationTypeId, $sfRightTermId);

             
                $sfLeftName     = $oTerm->retrieveTermName($sfLeftTermId);
                $sfRTName       = $oRelationType->retrieveNameById($sfRelationTypeId);
                $sfRightName    = $oTerm->retrieveTermName($sfRightTermId);                 
                $sfFact         = $sfLeftName . " " . $sfRTName . " " . $sfRightName;            
                   
                // change subset fact record to fact record
                $oSolutionFact->changeFact($sfId,$sfFact,$sfRelationId, $sfRating,
                        $sfLeftTermId,$sfRelationTypeId,$sfRightTermId, 
                       $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                       $sfssRelationTypeId, $sfssRightTermId);
            }
        }
     }


    //--------------------------------------------------------------------
    /*  Look ahead subset fact in categoriazation problem                 */
    /*  This function determines whether there there are further          */
    /*   categorization levels. It does not update any table              */
    /*       hasSubset = 1  => there further categorization levels        */
    /*       hasSubset = 0  => there is no further categorization level   */   

    public function lookAheadSubsetFact($userid)
     {
        // Instantiate classes
        $oSolutionFact         = new SolutionFact();    
        $oRelationTypeFilter   = new RelationTypeFilter();      
        $oRelation             = new Relation();
        $oRelationType         = new RelationType();
        $oTerm                 = new Term();
    
        // get subsets from session facts
        $step = 2 ;    // step 2: filters for classification of session facts ////
        $hasSubset = 0;

        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);
   
        // retrieve session fact for this userid
        $rsdata = $oSolutionFact->getByUser($userid);       
        if (!empty($rsdata)) {  
                        
            foreach ($rsdata as $rsFact){                         
                $sfId             = $rsFact['sfId'];
                $sfFact           = $rsFact['sfFact'];
                $sfRelationId     = $rsFact['sfRelationId'];
                $sfRating         = $rsFact['sfRating'];
                $sfLeftTermId     = $rsFact['sfLeftTermId'];
                $sfRelationTypeId = $rsFact['sfRelationTypeId'];
                $sfssLeftTermId    = $rsFact['sfssLeftTermId'];
          

                //  find classification relation with right term      
                $ssRelation = $oRelation->getByRelTypeRightTerm($rtFilterId,$sfssLeftTermId);

                foreach ($ssRelation as $rsRel) { 
                    $ssRelationTypeId = $rsRel->relationTypeId;
                    $ssRelationId     = $rsRel->relationId;
                    $ssLTermId        = $rsRel->leftTermId;
                    $ssRTermId        = $rsRel->rightTermId;

                    if ($ssRelationTypeId = $rtFilterId) {
                        $hasSubset   = 1;                         
                    }

                }

            }
        }
        return $hasSubset;
     }

    //--------------------------------------------------------------------
    /*  make subset unique fact  from subset fact pick                    */
    /*    - delete all records from solutin_fact by userid                */
    /*    - add one subset fact                                           */
    /*          if new record has subset recrods, create subbset          */

    public function makeSubsetUniqueFact($userid,  $sfId, $lang)
     {
        // Instantiate classes
        $oSolutionFact     = new SolutionFact();  
        $oRelation         = new Relation();  
        $oTerm             = new Term(); 
        $oRelationType     = new RelationType();   
        
        // retrieve session fact for this userid
        $rsdata = $oSolutionFact->getByUserSfssid($userid, $sfId);  

        // delete solution_fact records for this userid
        $oSolutionFact->deleteByUser($userid);  
        $sfSubset            = 0;
        $sfssFact            = "";
        $sfssRelationId      = 0;
        $sfssLeftTermId      = 0;
        $sfssRelationTypeId  = 0;
        $sfssRightTermId     = 0;
        $sfRating            = 0;
        $sfInquiry    = "";
        $sfParentFact = "";
        $sfParentRating = 0;
           
        foreach ($rsdata as $rs1){                         

            $sfLeftTermId     = $rs1->sfLeftTermId;
            $sfRelationTypeId = $rs1->sfRelationTypeId;
            $sfRightTermId    = $rs1->sfssLeftTermId;  
            $sfRating         = $rs1->sfRating;
            $sfInquiry        = $rs1->sfInquiry;
            $sfRelationId     = $oRelation->retrieveByLeftTypeRightId($sfLeftTermId,$sfRelationTypeId,
                                   $sfRightTermId);
            $sfLeftName       = $oTerm->retrieveTermName($sfLeftTermId);
            $sfRTName         = $oRelationType->retrieveNameById($sfRelationTypeId);
            $sfRightName      = $oTerm->retrieveTermName($sfRightTermId);                 
            $sfFact           = $sfLeftName . " " . $sfRTName . " " . $sfRightName;

            // add subset record
            $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                    $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang, 
                    $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                    $sfssRelationTypeId,$sfssRightTermId,
                    $sfInquiry,$sfParentFact,$sfParentRating,$userid); 
        }

     }

    //--------------------------------------------------------------------
    public function hasSubset($userid)
     {
        // Instantiate classes
        $oSolutionFact        = new SolutionFact(); 
        $oRelation            = new Relation(); 
        $oRelationTypeFilter  = new RelationTypeFilter(); 
        $subsetCount  = 0;

        // get subsets from session facts
        $step = 2 ;    // step 2: filters for classification of session facts ////

        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);

        $rsdata = $oSolutionFact->getByUser($userid); 
        foreach ($rsdata as $rs){                         
            $sfRightTermId = $rs['sfRightTermId'];
            $rsdata1 = $oRelation->getByRelTypeRightTerm($rtFilterId,$sfRightTermId);
            foreach ($rsdata1 as $rs1){                         
                $subsetCount++; 
            }
        } 
        return $subsetCount;
     }

    //--------------------------------------------------------------------
    public function makeSubFactRating($orgid,$personaId,$personalityId,$userid, $TSStrategy=0)
     {
        // Instantiate classes
        $oSolutionFact   = new SolutionFact();			  
		  
        // retrieve session fact for this userid 
        $rsdata = $oSolutionFact->getByUser($userid);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rsFact){						  
                $sfId         = $rsFact['sfId'];
                $sfRelationId = $rsFact['sfRelationId'];
                $sfRating     = $rsFact['sfRating'];

                $netRating = $this->calculateRelationRating($personaId,$personalityId, $sfRelationId);
                $oSolutionFact->updateFactRating($sfId, $netRating );
            }
        }

     }	 
	
    //--------------------------------------------------------------------
    /*
       $TSStrategy    0   First match update rating
                      1   First negative update rating
                      2   All negative update rating
     */ 
    public function makeFactRating($orgid,$personaId,$personalityId,$userid, $TSStrategy=0)
     { 


        $wSolutionId = 0;

        // Instantiate classes
        $oSolutionFact   = new SolutionFact();      

          
        // retrieve session fact for this userid 
        $rsdata = $oSolutionFact->getByUser($userid);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rsFact){                         
                $sfId         = $rsFact['sfId'];
                $sfRelationId = $rsFact['sfRelationId'];
                $wSolutionId  = $sfRelationId;
                $netRating = $this->calculateRelationRating($personaId,$personalityId, $sfRelationId);

                $oSolutionFact->updateFactRating($sfId, $netRating );

            }
        }

        return $wSolutionId;

     }  


    //--------------------------------------------------------------------
    /**
      Calculate and update solution option ratings
     */ 
    public function makeOptionRating($orgid,$personaId,$personalityId,$userid, $TSStrategy=0)
     { 

        // Instantiate classes
        $oSolutionOption   = new SolutionOption();      

          
        // retrieve session fact for this userid 
        $rsdata = $oSolutionOption->getPositiveByUser($userid);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rsFact){                         
                $soId         = $rsFact['soId'];
                $soSolutionId = $rsFact['soSolutionId'];
                $netRating = $this->calculateRelationRating($personaId,$personalityId, $soSolutionId);

                // update negative ratings (problem)
                if ($netRating < 0) {
                   $oSolutionOption->updateFactRating($soId, $netRating );
                }

            }
        }


     } 

    //--------------------------------------------------------------------
    // Calculate relation rating with data from personality_value and 
    //     personality_relation_value
    //--------------------------------------------------------------------
    public function calculateRelationRating($personaId,$personalityId, $relationId)
     {	
        $netRating = 0;
        $emotionalityRating = 0;

        // Instantiate classes
        $oPersonalityTrait         = new PersonalityTrait();
        $oPersonalityValue         = new PersonalityValue();
        $oPersonalityRelation      = new PersonalityRelation();
        $oPersonalityRelationValue = new PersonalityRelationValue();	
		
        // get emotionality rating from perosnality_trait
        $emotionalityRating = 
          $oPersonalityTrait->retrieveScalarValueByPersonality($personaId,$personalityId);	

        // get parentPersonalityRelaitonId		
        $parentPersonalityRelationId = 
          $oPersonalityRelation->retrievePersonalityRelationId($personaId,$personalityId, $relationId);

        // get records from personality_relation_value		
        $rsdata = 
          $oPersonalityRelationValue->getByPersonalityRelation($parentPersonalityRelationId);
        if (!empty($rsdata) ){   	
            foreach ($rsdata as $rsPerRelValue){						  
                $perRelValueId   = $rsPerRelValue['personRelationTermId'];
                $prvScalarValue  = $rsPerRelValue['scalarValue'];
					 
                // get records from personality_value
                $rsdata1 = $oPersonalityValue->getByPersonalityValue($personaId,$personalityId, $perRelValueId);
                if (!empty($rsdata1)) {
                    $pvScalarValue = 0;
                    $pvScalarValueFound = 0;
                    foreach ($rsdata1 as $rsPerValue) {
                        $prScalarValue  = $rsPerValue['scalarValue'];
                        $pvScalarValueFound = 1;    // at least one scalarvalue found
                     
                    }
                    $powerValue = 1;
                    if ($pvScalarValueFound > 0) {
                        $powerValue = pow($prScalarValue,$emotionalityRating);
                    }

                    $netRating  += $prvScalarValue * $powerValue;	
    					

                } else {
                    $netRating  += $prvScalarValue;
                }
            }
				  
        }		       
	
        return $netRating;
     }
	 

    //--------------------------------------------------------------------
    // Calculate relation rating with slide bar parameters 
    //--------------------------------------------------------------------
    public function calculateRelationRatingSB($personaId,$personalityId, $relationId, $slidebar)
     {  
        $netRating = 0;
        $emotionalityRating = 0;
        $copia = $slidebar;
        // Instantiate classe
        $oTerm                     = new Term();
        $oPersonalityTrait         = new PersonalityTrait();
        $oPersonalityValue         = new PersonalityValue();
        $oPersonalityRelation      = new PersonalityRelation();
        $oPersonalityRelationValue = new PersonalityRelationValue();  


        // get emotionality rating from perosnality_trait
        $emotionalityRating = 
          $oPersonalityTrait->retrieveScalarValueByPersonality($personaId,$personalityId); 

        // get parentPersonalityRelaitonId    
        $parentPersonalityRelationId = 
          $oPersonalityRelation->retrievePersonalityRelationId($personaId,$personalityId, $relationId);

        // get records from personality_relation_value    
        $rsdata = 
          $oPersonalityRelationValue->getByPersonalityRelation($parentPersonalityRelationId);
        if (!empty($rsdata) ){    
            foreach ($rsdata as $rsPerRelValue){              
                $perRelValueId   = $rsPerRelValue['personRelationTermId'];
                $prvScalarValue  = $rsPerRelValue['scalarValue'];
  
                $sb0 = $copia;
                // get data from slide bar array
                foreach($sb0 as $key=>$value) {                

                  $tValueId = $key;
                  $tScalar  = (int)$value;
	
                  if($tValueId == $perRelValueId ) {
                      $prScalarValue  = $tScalar;
                      $powerValue = pow($prScalarValue,$emotionalityRating);
     
                      if (is_nan($powerValue)) {
                         $powerValue = 1;
                      }
                      $netRating  = $netRating + ($prvScalarValue * $powerValue);  
 	               
                  }
                }
            }
        }   

        return $netRating;
     }

    //--------------------------------------------------------------------
    //  find and save solutions 
    //--------------------------------------------------------------------
    public function makeSolutionRelation ($personaId,$personalityId, $userid, $orgid, $hasBar, $slideBar,$isLex,
        $state,$bLang,$tLang,$delrec=1,$pickProblemId=0)


    {
		// Instantiate classes

        $oSolutionFact         = new SolutionFact();
        $oRelationType         = new RelationType();
        $oTerm                 = new Term();
        $oRelation             = new Relation();
       // $oRelTranslation       = new RelationTranslation();
        $oRelationLanguage     = new RelationLanguage();
        $oRelationTypeFilter   = new RelationTypeFilter();
        $oPersonalityRelation  = new PersonalityRelation();
        $oSolutionRelation     = new SolutionRelation();
        $oSRExdata             = new SolutionRelationExdata();
        $oExtLink              = new ExtendedLink();
        $oExtEntity            = new ExtendedEntity();
        $oExtAttribute         = new ExtendedAttribute();
        $oExtAttributeType     = new ExtendedAttributeType();
        $oExtEAV               = new ExtendedEAV();

        if ($delrec == 1) {
           $oSolutionRelation->deleteByUser($userid);
        }

        $oSolutionFact->deletePositive($userid);

      // remove duplicate fact records
      $rs = $oSolutionFact->getByUserOrderByRelationId($userid,$pickProblemId);
      $wrId = 0;
      foreach($rs as $rs0) {
         $sfId              = $rs0['sfId'];
         $sfRelationId      = $rs0['sfRelationId'];
         $sfSource          = $rs0['sfSource'];

         if ($sfRelationId == $wrId) {
          
            if($sfSource < 99) {            // skip fact with RPA mapping
                $oSolutionFact->deleteById($sfId);                  
            } 

         } else {
            $wrId  = $sfRelationId;
         }
      }      

        $finalResultText = "";
        $hasExtendedData = 0;
        $srSource  = 0;
        if($slideBar =="") {
          $hasBar = 0;
        }
        $lang = $bLang;
        $sfLanguage = $bLang;
        $custom ="";
        $sfShortText = "";
        $sfFact = "";
        $rtgId = 8; 

			
        // 1. Get relation type filters

        $step = 5 ;    // step 5: filters for intermediate results /////  
                       // 64 is a type of
                       // 84 can desire 
                       // 44 can request
        $lStep = 9;    // 105 can be served at. Special Lex processing
        $lRelTypeId = 0;

        // special lex processing for lex controller
        if ($isLex == 1) {
             $lRelTypeId = $oRelationTypeFilter->retrieveByStep($lStep);
        }

        $rsFilter0 = $oRelationTypeFilter->getByStep($step);

        // 2. retrieve seesion fact for this userid
        //$rsdata = $oSolutionFact->getByUser($userid );
        $rsdata = $oSolutionFact->getFactByUser($userid,$pickProblemId );

        if (!empty($rsdata)) {  
            foreach ($rsdata as $rsFact){		

                $sfId          = $rsFact['sfId'];
                $sfRelationId  = $rsFact['sfRelationId'];
                $sfRating      = $rsFact['sfRating'];
                $sfRightTermId = $rsFact['sfRightTermId'];
                $sfFact        = $rsFact['sfFact'];
                $srInput       = $rsFact['sfInput'];
                //$sfFact = trim($sfFact);
                $fLen = strlen($sfFact);

                //$sfFact = $this->makeProblemText($sfRelationId,$orgid,$bLang,$tLang);  
                $aProblem = $this->makeProblemText($sfRelationId,$orgid,$bLang,$tLang);
                $sfFact     =  $aProblem['sfRelation'];
                $sfLanguage =  $aProblem['sfLanguage'];
                $sfShortText=  $aProblem['sfShortText'];

                $rightTermSynonymId = $sfRightTermId;
	
                // 3. Process negative fact ratings
                if ($sfRating < 0) {

                    $rsFilter = $rsFilter0;

                    foreach($rsFilter as $rtFilter) {
                        // process relation with intermediate filter relations						

				
						// 3.1 Save relation type filter id
                        $relationTypeFilterId = $rtFilter['relationTypeId'];

                        // 3.2 Get intermediate relation					
                        $rsRFilter = $oRelation->getSynonymRelationId($relationTypeFilterId, $sfRightTermId);

                        if (!empty($rsRFilter)) {


                          foreach($rsRFilter as $rsF) {

                            $rightTermSynonymId = 0;							
                            $filterRelationId = $rsF['relationId'];		
                            $srRTypeId        = $rsF['relationTypeId'];
                            $srLTermId        = $rsF['leftTermId'];
                            $srRTermId        = $rsF['rightTermId'];  	

                            if ($filterRelationId == 0) {
                                $filterRelationId = $sfRelationId; 
                                $rightTermSynonymId = $sfRightTermId;							
                            } else {
                            // 3.3 retrieve left term synonym 
                                $rightTermSynonymId = $oRelation->retrieveLeftTermSynonymId($filterRelationId); 
                            }


						    // 3.4. Get intermediate result relation
                            $srRating = 0;
                            $srRTermId = $rightTermSynonymId;
                            $rsIntermediateRelation = $oRelation->getByRightTerm($rightTermSynonymId); 
				
                            foreach($rsIntermediateRelation as $rsIR) {
                                $srRelationId     = $rsIR['relationId'];
                         
                                $srRTypeId        = $rsIR['relationTypeId'];
                                $srLTermId        = $rsIR['leftTermId'];
                                $srRTermId        = $rsIR['rightTermId'];  

				
                               // calculate rating
                                if ($hasBar == 1) {
                                   // process slide bar parameter
                                   $srRating = 
                                    $this->calculateRelationRatingSB($personaId,$personalityId, $srRelationId, $slideBar);

                                } else {
                                   // no slide bar parameters
                                   $srRating = 
                                    $this->calculateRelationRating($personaId,$personalityId, $srRelationId);
                                }
                                // pick unique potential solution relation with positive rating
                                $solutionRelationCount = 
                                   $oSolutionRelation->countSR($sfId,$srRelationId,$userid); 
									
                                if (($srRating  > 0) and ($solutionRelationCount == 0)) {

                                    // make solution relation text  


                           ///// RELATION TRANSLATION   /////////////

                                    // relation data
                                    $lang = $bLang;
                                    $srRelation = "";
                                    $srShortText = "";


                                   //// The optional text inherits access from the parent relation //////////
                                   $parentOwnerId = $this->getParentOwnerId($orgid,$srRelationId);
                                   if ($parentOwnerId == 0) {
                                       $parentOwnerId = $orgid;
                                   }
                                   //////////////////////////////////////////////////////////////////                                    

                                    // relation translation and short text
                                    $RTrs = $oRelationLanguage->getText($srRelationId,$parentOwnerId,$bLang, $tLang );
                                    foreach($RTrs as $rs0) {

                                        if (isset($rs0->optionalText)) {
                                           $srRelation = $rs0->optionalText;
                                           $srRelation = ucfirst($srRelation);
                                           $lang       = $rs0->language_code;
                                        }                                        
             

                                        if (isset($rs0->shortText)) {
                                           $srShortText = $rs0->shortText;
                                           $lang        = $rs0->language_code;
                                        }
                                    }
                                    if ($srRelation == "") {
                                        $srRelation =  $this->makeRelationText($srRelationId,$custom);
                                        $lang       = $bLang; 
                                    }


                                    // problem data
                                    $sfLanguage = $bLang;
                                    $sfFact     = "";
        
                                    // problem translation and short text
                                    $RTrs = $oRelationLanguage->getProblemText($sfRelationId,$orgid,$bLang, $tLang);
                                    foreach($RTrs as $rs1) {

                                        if (isset($rs1->optionalText)) {
                                           $sfFact     = $rs1->optionalText;
                                           $sfFact     = ucfirst($sfFact);
                                           $sfLanguage = $rs1->language_code;
                                                                
                                        }                                        

                                        if (isset($rs1->shortText)) {
                                           $sfShortText = $rs1->shortText;
                                           $sfLanguage = $rs1->language_code;
                                        }
                                    }
                                    if ($sfFact == "") {
                                        $sfFact      =  $this->makeRelationText($sfRelationId,$custom);
                                        $sfLanguage = $bLang;
                                    }                                    
                                

                                    // save solution relations                                         
                                    $sredParentId = 
                                      $oSolutionRelation->insertRelation($sfId, $srRelation, 
                                        $srRelationId,$srRating,$sfRating,$srShortText,$sfShortText,
                                        $hasExtendedData,$srLTermId,$srRTypeId,$srRTermId, $userid, 
                                        $state, $lang, $srSource,$srInput, $sfFact, $sfLanguage);  

                                }							
						
                            }

						  }
						} else {
                        // process relation with no intermediate filter relations

                            $rightTermSynonymId = $sfRightTermId;

                        // Get intermediate result relation
                            $srRating = 0;
                            $srRTypeId  = 0;
                            $srLTermId = 0;
                            $srRTermId = 0 ;
                            $wCount = 0;
                            $rsIntermediateRelation = $oRelation->getByRightTerm($rightTermSynonymId); 
		
                            foreach($rsIntermediateRelation as $rsIR) {
                                $srRelationId  = $rsIR['relationId'];
                                $srRTypeId     = $rsIR['relationTypeId'];
                                $srLTermId     = $rsIR['leftTermId'];
                                $srRTermId     = $rsIR['rightTermId'];				
                        // calculate rating

                                if ($hasBar == 1) {
                                   // process slide bar parameter
                                   $srRating = 
                                    $this->calculateRelationRatingSB($personaId,$personalityId, $srRelationId, $slideBar);

                                } else {
                                   // no slide bar parameters
                                   $srRating = 
                                    $this->calculateRelationRating($personaId,$personalityId, $srRelationId);
                                }                                

                                // pick unique potential solution relation with positive rating
                                 $solutionRelationCount = 
                                    $oSolutionRelation->countSR($sfId,$srRelationId,$userid);
        

                                if (($srRating  > 0) and ($solutionRelationCount == 0)) {   
                                    $srHasExtendedData   = 0;

                                    $srRTermId = $sfRightTermId;
                    
                                    // make solution relation text                                   
                                    $lang = $bLang;
                                    $srRelation = "";
                                    $srShortText = "";

                                   //// The optional text inherits access from the parent relation //////////
                                   $parentOwnerId = $this->getParentOwnerId($orgid,$srRelationId);
                                   if ($parentOwnerId == 0) {
                                       $parentOwnerId = $orgid;
                                   }


                                    $RTrs = $oRelationLanguage->getText($srRelationId,$parentOwnerId,$bLang, $tLang,$rtgId );
                                    foreach($RTrs as $rs0) {

                                        if (isset($rs0->optionalText)) {
                                           $srRelation = $rs0->optionalText;
                                           $srRelation = ucfirst($srRelation);
                                           $lang       = $rs0->language_code;

                                        }  


                                        if (isset($rs0->shortText)) {
                                            $srShortText = $rs0->shortText;
                                            $lang       = $rs0->language_code;
                                        }
                                    }

                                    if ($srRelation == "") {
                                        $srRelation =  $this->makeRelationText($srRelationId,$custom);
                                        $lang   = $bLang; 
                                    }
 
                                    // problem data
                                    $sfLanguage = $bLang;
                                    $sfFact     = "";
                                    $sfShortText = "";
                                    
                                    // problem translation and short text
                                    $RTrs = $oRelationLanguage->getProblemText($sfRelationId,$orgid,$bLang, $tLang);
                                    foreach($RTrs as $rs0) {

                                        if (isset($rs0->optionalText)) {
                                           $sfFact     = $rs0->optionalText;
                                           $sfFact     = ucfirst($sfFact);
                                           $sfLanguage = $rs0->language_code;
                                        }                                        
                                       
                                        if (isset($rs0->shortText)) {
                                           $sfShortText = $rs0->shortText;
                                           $sfLanguage = $rs0->language_code;
                                        }
                                    }
                                    if ($sfFact == "") {
                                        $sfFact      =  $this->makeRelationText($sfRelationId,$custom);
                                        $sfLanguage = $bLang;
                                    }  
                        

                                    $sredParentId = 
                                      $oSolutionRelation->insertRelation($sfId, $srRelation, 
                                        $srRelationId,$srRating,$sfRating,$srShortText,$sfShortText,
                                        $hasExtendedData,$srLTermId,$srRTypeId,$srRTermId, $userid, 
                                        $state, $lang, $srSource,$srInput, $sfFact, $sfLanguage);                                     

                                }							
						
                            }
						
                         ///////					
                        }						
				//////	 
                    }			   
                }	
            }
        }

      // Has soltuion relation
      $hasSolutionRelation = 0;

      // remove duplicate records
      $rs = $oSolutionRelation->getByUserOrderByRelationId($userid);
      $wrId = 0;
      foreach($rs as $rs0) {
         $srId              = $rs0['srId'];
         $srRelationId      = $rs0['srRelationId'];
         $srRating          = $rs0['srRating'];
         if ($srRating > 0) {
            $hasSolutionRelation = 1;            // there are positive relation 
         }
         if ($srRelationId == $wrId) {
            $oSolutionRelation->deleteById($srId);
         } else {
            $wrId  = $srRelationId;
         }
      }


      return $hasSolutionRelation;

    }	 


///////
    //--------------------------------------------------------------------
    //  find and save solutions 
    //--------------------------------------------------------------------
    public function makePickSolutionRelation ($personaId,$personalityId, $userid, $orgid, $hasBar, $slideBar,$isLex,
        $state,$bLang,$tLang,$delrec=1,$pickProblemId=0)

    {
    // Instantiate classes

        $oSolutionFact         = new SolutionFact();
        $oRelationType         = new RelationType();
        $oTerm                 = new Term();
        $oRelation             = new Relation();
        $oRelationLanguage     = new RelationLanguage();
        $oRelationTypeFilter   = new RelationTypeFilter();
        $oPersonalityRelation  = new PersonalityRelation();
        $oSolutionRelation     = new SolutionRelation();
        $oSRExdata             = new SolutionRelationExdata();
        $oExtLink              = new ExtendedLink();
        $oExtEntity            = new ExtendedEntity();
        $oExtAttribute         = new ExtendedAttribute();
        $oExtAttributeType     = new ExtendedAttributeType();
        $oExtEAV               = new ExtendedEAV();

      // remove duplicate fact records
      $rs = $oSolutionFact->getByUserOrderByRelationId($userid,$pickProblemId);
      $wrId = 0;
      foreach($rs as $rs0) {
         $sfId              = $rs0['sfId'];
         $sfRelationId      = $rs0['sfRelationId'];
         $sfSource          = $rs0['sfSource'];

      }      

        $finalResultText = "";
        $hasExtendedData = 0;
        $srSource  = 0;
        if($slideBar =="") {
          $hasBar = 0;
        }
        $lang = $bLang;
        $sfLanguage = $bLang;
        $custom ="";
        $sfShortText = "";
        $sfFact = "";

      
        // 1. Get relation type filters

        $step = 5 ;    // step 5: filters for intermediate results /////  
                       // 64 is a type of
                       // 84 can desire 
                       // 44 can request
        $lStep = 9;    // 105 can be served at. Special Lex processing
        $lRelTypeId = 0;

        // special lex processing for lex controller
        if ($isLex == 1) {
             $lRelTypeId = $oRelationTypeFilter->retrieveByStep($lStep);
        }

        $rsFilter0 = $oRelationTypeFilter->getByStep($step);

        // 2. retrieve seesion fact for this userid
        //$rsdata = $oSolutionFact->getByUser($userid );
        $rsdata = $oSolutionFact->getFactByUser($userid,$pickProblemId );

        if (!empty($rsdata)) {  
            foreach ($rsdata as $rsFact){   

                $sfId          = $rsFact['sfId'];
                $sfRelationId  = $rsFact['sfRelationId'];
                $sfRating      = $rsFact['sfRating'];
                $sfRightTermId = $rsFact['sfRightTermId'];
                $sfFact        = $rsFact['sfFact'];
                $srInput       = $rsFact['sfInput'];
                $fLen = strlen($sfFact);
  
                $aProblem = $this->makeProblemText($sfRelationId,$orgid,$bLang,$tLang);
                $sfFact     =  $aProblem['sfRelation'];
                $sfLanguage =  $aProblem['sfLanguage'];
                $sfShortText=  $aProblem['sfShortText'];

                $rightTermSynonymId = $sfRightTermId;
  
                // 3. Process negative fact ratings
                if ($sfRating < 0) {

                    $rsFilter = $rsFilter0;

                    foreach($rsFilter as $rtFilter) {
                        // process relation with intermediate filter relations            

            // 3.1 Save relation type filter id
                        $relationTypeFilterId = $rtFilter['relationTypeId'];

                        // 3.2 Get intermediate relation          
                        $rsRFilter = $oRelation->getSynonymRelationId($relationTypeFilterId, $sfRightTermId);

                        if (!empty($rsRFilter)) {


                          foreach($rsRFilter as $rsF) {

                            $rightTermSynonymId = 0;              
                            $filterRelationId = $rsF['relationId'];   
                            $srRTypeId        = $rsF['relationTypeId'];
                            $srLTermId        = $rsF['leftTermId'];
                            $srRTermId        = $rsF['rightTermId'];    

                            if ($filterRelationId == 0) {
                                $filterRelationId = $sfRelationId; 
                                $rightTermSynonymId = $sfRightTermId;             
                            } else {
                            // 3.3 retrieve left term synonym 
                                $rightTermSynonymId = $oRelation->retrieveLeftTermSynonymId($filterRelationId); 
                            }


                // 3.4. Get intermediate result relation
                            $srRating = 0;
                            $srRTermId = $rightTermSynonymId;
                            $rsIntermediateRelation = $oRelation->getByRightTerm($rightTermSynonymId); 
        
                            foreach($rsIntermediateRelation as $rsIR) {
                                $srRelationId     = $rsIR['relationId'];
                         
                                $srRTypeId        = $rsIR['relationTypeId'];
                                $srLTermId        = $rsIR['leftTermId'];
                                $srRTermId        = $rsIR['rightTermId'];  

        
                               // calculate rating
                                if ($hasBar == 1) {
                                   // process slide bar parameter
                                   $srRating = 
                                    $this->calculateRelationRatingSB($personaId,$personalityId, $srRelationId, $slideBar);

                                } else {
                                   // no slide bar parameters
                                   $srRating = 
                                    $this->calculateRelationRating($personaId,$personalityId, $srRelationId);
                                }

                                // pick unique potential solution relation with positive rating
                                $solutionRelationCount = 
                                   $oSolutionRelation->countSR($sfId,$srRelationId,$userid); 
                  
                                if (($srRating  > 0) and ($solutionRelationCount == 0)) {

                                    // make solution relation text  


                           ///// RELATION TRANSLATION   /////////////

                                    // relation data
                                    $lang = $bLang;
                                    $srRelation = "";
                                    $srShortText = "";

                                   //// The optional text inherits access from the parent relation //////////
                                   $parentOwnerId = $this->getParentOwnerId($orgid,$srRelationId);
                                   if ($parentOwnerId == 0) {
                                       $parentOwnerId = $orgid;
                                   }
                                   //////////////////////////////////////////////////////////////////

                                    // relation translation and short text
                                    $RTrs = $oRelationLanguage->getText($srRelationId,$parentOwnerId,$bLang, $tLang);
                                    foreach($RTrs as $rs0) {

                                        if (isset($rs0->optionalText)) {
                                           $srRelation = $rs0->optionalText;
                                           $srRelation = ucfirst($srRelation);
                                           $lang       = $rs0->language_code;
                                        }                                        
                                       
                                        if (isset($rs0->shortText)) {
                                           $srShortText = $rs0->shortText;
                                           $lang        = $rs0->language_code;
                                        }
                                    }
                                    if ($srRelation == "") {
                                        $srRelation =  $this->makeRelationText($srRelationId,$custom);
                                        $lang       = $bLang; 
                                    }


                                    // problem data
                                    $sfLanguage = $bLang;
                                    $sfFact     = "";
        
                                    // problem translation and short text
                                    $RTrs = $oRelationLanguage->getProblemText($sfRelationId,$orgid,$bLang, $tLang);
                                    foreach($RTrs as $rs1) {

                                        if (isset($rs1->optionalText)) {
                                           $sfFact     = $rs1->optionalText;
                                           $sfFact     = ucfirst($sfFact);
                                           $sfLanguage = $rs1->language_code;
                                                                
                                        }                                        

                                        if (isset($rs1->shortText)) {
                                           $sfShortText = $rs1->shortText;
                                           $sfLanguage = $rs1->language_code;
                                        }
                                    }
                                    if ($sfFact == "") {
                                        $sfFact      =  $this->makeRelationText($sfRelationId,$custom);
                                        $sfLanguage = $bLang;
                                    }                                    
                                
                           //////////////////////////////////////////

                                    // save solution relations                                         
                                    $sredParentId = 
                                      $oSolutionRelation->insertRelation($sfId, $srRelation, 
                                        $srRelationId,$srRating,$sfRating,$srShortText,$sfShortText,
                                        $hasExtendedData,$srLTermId,$srRTypeId,$srRTermId, $userid, 
                                        $state, $lang, $srSource,$srInput, $sfFact, $sfLanguage);  

                                }             
            
                            }

              }
            } else {
                        // process relation with no intermediate filter relations

                            $rightTermSynonymId = $sfRightTermId;

                        // Get intermediate result relation
                            $srRating = 0;
                            $srRTypeId  = 0;
                            $srLTermId = 0;
                            $srRTermId = 0 ;
                            $wCount = 0;
                            $rsIntermediateRelation = $oRelation->getByRightTerm($rightTermSynonymId); 
    
                            foreach($rsIntermediateRelation as $rsIR) {
                                $srRelationId  = $rsIR['relationId'];
                                $srRTypeId     = $rsIR['relationTypeId'];
                                $srLTermId     = $rsIR['leftTermId'];
                                $srRTermId     = $rsIR['rightTermId'];        
                        // calculate rating

                                if ($hasBar == 1) {
                                   // process slide bar parameter
                                   $srRating = 
                                    $this->calculateRelationRatingSB($personaId,$personalityId, $srRelationId, $slideBar);

                                } else {
                                   // no slide bar parameters
                                   $srRating = 
                                    $this->calculateRelationRating($personaId,$personalityId, $srRelationId);
                                }                                

                                // pick unique potential solution relation with positive rating
                                 $solutionRelationCount = 
                                    $oSolutionRelation->countSR($sfId,$srRelationId,$userid);
        

                                if (($srRating  > 0) and ($solutionRelationCount == 0)) {   
                                    $srHasExtendedData   = 0;

                                    $srRTermId = $sfRightTermId;
                    
                                    // make solution relation text                                   
                                    $lang = $bLang;
                                    $srRelation = "";
                                    $srShortText = "";

                                   //// The optional text inherits access from the parent relation //////////
                                   $parentOwnerId = $this->getParentOwnerId($orgid,$srRelationId);
                                   if ($parentOwnerId == 0) {
                                       $parentOwnerId = $orgid;
                                   }
                                   //////////////////////////////////////////////////////////////////                                    

                                    $RTrs = $oRelationLanguage->getText($srRelationId,$parentOwnerId,$bLang, $tLang);
                                    foreach($RTrs as $rs0) {

                                        if (isset($rs0->optionalText)) {
                                           $srRelation = $rs0->optionalText;
                                           $srRelation = ucfirst($srRelation);
                                           $lang       = $rs0->language_code;
                                        }  

                                        if (isset($rs0->shortText)) {
                                            $srShortText = $rs0->shortText;
                                            $lang       = $rs0->language_code;
                                        }
                                    }

                                    if ($srRelation == "") {
                                        $srRelation =  $this->makeRelationText($srRelationId,$custom);
                                        $lang   = $bLang; 
                                    }
 
                                    // problem data
                                    $sfLanguage = $bLang;
                                    $sfFact     = "";
                                    $sfShortText = "";
                                    
                                    // problem translation and short text
                                    $RTrs = $oRelationLanguage->getProblemText($sfRelationId,$orgid,$bLang, $tLang);
                                    foreach($RTrs as $rs0) {

                                        if (isset($rs0->optionalText)) {
                                           $sfFact     = $rs0->optionalText;
                                           $sfFact     = ucfirst($sfFact);
                                           $sfLanguage = $rs0->language_code;
                                        }                                        
                                       
                                        if (isset($rs0->shortText)) {
                                           $sfShortText = $rs0->shortText;
                                           $sfLanguage = $rs0->language_code;
                                        }
                                    }
                                    if ($sfFact == "") {
                                        $sfFact      =  $this->makeRelationText($sfRelationId,$custom);
                                        $sfLanguage = $bLang;
                                    }  
                        

                                    $sredParentId = 
                                      $oSolutionRelation->insertRelation($sfId, $srRelation, 
                                        $srRelationId,$srRating,$sfRating,$srShortText,$sfShortText,
                                        $hasExtendedData,$srLTermId,$srRTypeId,$srRTermId, $userid, 
                                        $state, $lang, $srSource,$srInput, $sfFact, $sfLanguage);                                     
                                }             
            
                            }
            
                         ///////          
                        }           
        //////   
                    }        
                } 
            }
        }

      // Has soltuion relation
      $hasSolutionRelation = 0;

      // remove duplicate records
      $rs = $oSolutionRelation->getByUserOrderByRelationId($userid);
      $wrId = 0;
      foreach($rs as $rs0) {
         $srId              = $rs0['srId'];
         $srRelationId      = $rs0['srRelationId'];
         $srRating          = $rs0['srRating'];
         if ($srRating > 0) {
            $hasSolutionRelation = 1;            // there are positive relation 
         }
         if ($srRelationId == $wrId) {
            $oSolutionRelation->deleteById($srId);
         } else {
            $wrId  = $srRelationId;
         }
      }


      return $hasSolutionRelation;

    } 


    //--------------------------------------------------------------------
    public function removeNoiseFact ($userid)
    {

      $oSolutionFact   = new SolutionFact();
      $rs = $oSolutionFact->getByUser($userid);

      foreach($rs as $rs0) {
         $sfId              = $rs0['sfId'];
         $sfRelationId      = $rs0['sfRelationId'];
         if ($sfRelationId == 0) {
            $oSolutionFact->deleteById($sfId);
         } 
      }

    }

/////////////
    //--------------------------------------------------------------------
    public function makeFactExtendedData ($userid, $orgid, $portalType, $bLang,$tLang)
    {
       

      // Instantiate classes
      $oSolutionFact       = new SolutionFact();
      $oSolFactExdata      = new SolutionFactExdata();
      $oExtLink            = new ExtendedLink();
      $oExtEntity          = new ExtendedEntity();
      $oExtAttribute       = new ExtendedAttribute();
      $oExtAttributeType   = new ExtendedAttributeType();
      $oExtEAV             = new ExtendedEAV();
      $oExtendedLinkTranslation = new ExtendedLinkTranslation();
      // $oRTGroup            = new RelationTypeGroup(); 
      $oExtSubType         = new \App\Models\ExtendedSubType();     
      $oRTFilter           = new RelationTypeFilter();     
      $oFunctionHelper     = new FunctionHelper();

      $lang            = $bLang;
      $parentTableId   = 2;
      $step = 8;   // can access protected data
      $rtCanAccessProtectedDataId = $oRTFilter->retrieveByStep($step);
      //$rtgId = $oRTGroup->retrieveByRelationType($rtCanAccessProtectedDataId);
      $rtgId = 8;    


      // get chatIntro field length
      $chatIntroLen = \Config::get('kama_dei.static.sredChatIntroLength',1000);


      // 1. get solution relations for this user
      $rsrel = $oSolutionFact->getByUser($userid);
      if (!empty($rsrel)) {   // L1
    
          foreach ($rsrel as $rsR) {  // L2          
              $srId              = $rsR['sfId'];
              $srRelationId      = $rsR['sfRelationId'];
              $srHasED           = $rsR['sfHasExtendedData'];
              $sfHasExtendedData = 0;

           if ($srHasED == 0) {  // L3

              /* find extended data                   */

              // get entityId and chatIntro
              $sredChatIntro = "";
              $extendedSubtypeId = 0;
              $entityId        = 0;
              $incExtDataName  = 0;
              $incExtDataChatIntro = 0;
              $orderid   = 0;

              $rsexlink  = $oExtLink->getEntityId($orgid,$parentTableId, $srRelationId, $rtgId);

              foreach ($rsexlink as $rselink0) {  // L4
                  $extLinkId = $rselink0['extendedLinkId'];
                  $entityId = $rselink0['entityId'];
                  $lang     = $bLang;
                 
                  $incExtDataName = $rselink0['includedExtDataName'];
                  $incExtDataChatIntro = $rselink0['includedExtDataChatIntro'];
                  $orderid = $rselink0['orderid'];
                  $sredChatIntro = "";

                  $tmped = $oExtendedLinkTranslation->getText($extLinkId,$bLang, $tLang);
           
                  foreach ($tmped as $tmped0) {
                     $sredChatIntro = $tmped0->chatIntro;
                     $lang          = $tmped0->lang;

                     if (isset($rsed0->voiceIntro) and $portalType == "voice") {
                         $sredChatIntro = $rsed0->voiceIntro;
                     }
                  }

             
                  // find extended data for each extendedLinkId
                   
                  // get data from extended_entity

                  //extendedSubtype
                  $extendedSubtypeId = 0;
                  $extendedEntityName = "";
                  $hasEntity = 0;

                  $rsEE = $oExtEntity->getEEId($orgid, $entityId, $rtgId);
                  foreach ($rsEE as $rsEE0) {
                      $extendedSubtypeId  = $rsEE0->extendedSubTypeId;
                      $extendedEntityName = $rsEE0->extendedEntityName;
                      $hasEntity = 1;
                  }
  

                 if ($hasEntity == 1) {  // L5

                    // get extendedAttributeId, attributeTypeId
                    $exAttributeId = 0;
                    $attributeTypeId = 0;
                    $storageType = "";
                    $attributeTypeName = "";
                    $attributeName = "";    
                    $sredAttributeSubtype = "";
                    $storageType = "";
                    $attributeTypeName = "";
                    $attributeName = "";   
                    $subtypeName   = "" ;               
                    //  $filter = 0;  // no organization filter

                    $rsed = $oExtAttribute->findExtendedAttribute($orgid, $extendedSubtypeId, $rtgId);        
                    foreach($rsed as $rsed1) {
                        $exAttributeId   = $rsed1['attributeId'];
                        $attributeTypeId = $rsed1['attributeTypeId'];
                        $attributeName   = $rsed1['attributeName'];
                    }

                    $subtypeName   = $oExtSubType->retrieveName($extendedSubtypeId);

                    // get storage type from table extended_attribute_type

                    $rsed = $oExtAttributeType->findStorageType($orgid,$attributeTypeId, $rtgId);
                    foreach ($rsed as $rsed1) {
                        $storageType = $rsed1['storageType'];
                        $attributeTypeName = $rsed1['attributeTypeName'];
                    }

                    if ($incExtDataName == 1) {
                        $sredChatIntro = $sredChatIntro . " ". $extendedEntityName;
                    }

                    // get extendadEAV data
                    $urlcount = 0;
                    $rseav = $oExtEAV->findEAV($orgid,$entityId, $bLang, $tLang);

                    if (!empty($rseav)) {  // L6
                        foreach ($rseav as $rs00){   // L7

                            $srSolution = "";  
                            // $srVString = stripslashes($rs00['valueString']);
                            $lang        = $rs00->lang; 
                            $attributeId = $rs00->extendedAttributeId;    
                            $srVString = stripslashes($rs00->valueString); 
                                    
                            switch ($storageType) {
                              case "CHAR":
                                $srSolution = stripslashes($rs00->valueString);
                                break;
                              case "VARCHAR":
                                $srSolution =  stripslashes($rs00->valueString);
                                break;
                              case "DATETIME":
                                $srSolution =  $rs00->valueDate;
                                break;
                              case "VARCHAR":
                                $srSolution =  stripslashes($rs00->valueString);
                                break;
                              default:
                                $srSolution =  stripslashes($rs00->valueString);
                                break;
                            }

                            $sredValueString =  $srSolution;
                            $sfHasExtendedData++;
                            $sredParentId = $srId;

                            // Get attribute name  -------------------

                            $rs02 = $oExtAttribute->findById($attributeId); 
                            foreach($rs02 as $rs03) {
                                 $attributeName   = $rs03['attributeName'];
                            }


                            // remove duplicate chat intro for URL
                            if ($attributeTypeName == "URL") {
                                $urlcount++;
                                if ($urlcount > 1 ) {
                                    $sredChatIntro = "";
                                }
                            }   

                            //  ---------------------------------------- 
                            // check catIntro length
                            $chatIntroTextLen = strlen($sredChatIntro);
                            If ($chatIntroTextLen > $chatIntroLen) {
                                $sredChatIntro = substr($sredChatIntro,0, $chatIntroLen);
                            }

                        
                            $oSolFactExdata->insertFactExdata($sredParentId, $sredChatIntro, 
                               $attributeName,$attributeTypeName, $sredValueString,$lang,$orderid, $userid);


                            //  update solution_option
                            if ($sfHasExtendedData > 0) {
                                $sfHasExtendedData = 1;
                                $oSolutionFact->updateHasextendeddata($srId, $sfHasExtendedData);        
                            }
                        }  // END L7
                    }   // END L6

                 }  // END L5

              }  // END L4

           } // END L3            

          }  // END L2
      }   // END L1 

   }     


    //--------------------------------------------------------------------
    public function makeRelationExtendedData ($userid, $orgid, $portalType, $bLang,$tLang)
    {
       

      // Instantiate classes
      $oSolutionRelation   = new SolutionRelation();
      $oSolRelExdata       = new SolutionRelationExdata();
      $oExtLink            = new ExtendedLink();
      $oExtEntity          = new ExtendedEntity();
      $oExtAttribute       = new ExtendedAttribute();
      $oExtAttributeType   = new ExtendedAttributeType();
      $oExtEAV             = new ExtendedEAV();
      $oExtendedLinkTranslation = new ExtendedLinkTranslation();
      // $oRTGroup            = new RelationTypeGroup(); 
      $oExtSubType         = new \App\Models\ExtendedSubType();     
      $oRTFilter           = new RelationTypeFilter();     
      $oFunctionHelper     = new FunctionHelper();

      $lang            = $bLang;
      $parentTableId   = 2;
      $step = 8;   // can access protected data
      $rtCanAccessProtectedDataId = $oRTFilter->retrieveByStep($step);
      //$rtgId = $oRTGroup->retrieveByRelationType($rtCanAccessProtectedDataId);
      $rtgId = 8;    


      // get chatIntro field length
      $chatIntroLen = \Config::get('kama_dei.static.sredChatIntroLength',1000);


      // 1. get solution relations for this user
      $rsrel = $oSolutionRelation->getByUser($userid);
      if (!empty($rsrel)) {   // L1
    
          foreach ($rsrel as $rsR) {  // L2          
              $srId              = $rsR['srId'];
              $srRelationId      = $rsR['srRelationId'];
              $srHasED           = $rsR['srHasExtendedData'];
              $srHasExtendedData = 0;


           if ($srHasED == 0) {  // L3

              /* find extended data                   */

              // get entityId and chatIntro
              $sredChatIntro = "";
              $extendedSubtypeId = 0;
              $entityId        = 0;
              $incExtDataName  = 0;
              $incExtDataChatIntro = 0;
              $orderid   = 0;

              $rsexlink  = $oExtLink->getEntityId($orgid,$parentTableId, $srRelationId, $rtgId);

              foreach ($rsexlink as $rselink0) {  // L4
                  $extLinkId = $rselink0['extendedLinkId'];
                  $entityId = $rselink0['entityId'];
                  $lang     = $bLang;
                 
                  $incExtDataName = $rselink0['includedExtDataName'];
                  $incExtDataChatIntro = $rselink0['includedExtDataChatIntro'];
                  $orderid = $rselink0['orderid'];
                  $sredChatIntro = "";


                  $tmped = $oExtendedLinkTranslation->getText($extLinkId,$bLang, $tLang);
           
                  foreach ($tmped as $tmped0) {
                     $sredChatIntro = $tmped0->chatIntro;
                     $lang          = $tmped0->lang;

                     if (isset($rsed0->voiceIntro) and $portalType == "voice") {
                         $sredChatIntro = $rsed0->voiceIntro;
                     }
                  }

             
                  // find extended data for each extendedLinkId
                   
                  // get data from extended_entity

                  //extendedSubtype
                  $extendedSubtypeId = 0;
                  $extendedEntityName = "";
                  $hasEntity = 0;

                  $rsEE = $oExtEntity->getEEId($orgid, $entityId, $rtgId);
                  foreach ($rsEE as $rsEE0) {
                      $extendedSubtypeId  = $rsEE0->extendedSubTypeId;
                      $extendedEntityName = $rsEE0->extendedEntityName;
                      $hasEntity = 1;
                  }
  

                 if ($hasEntity == 1) {  // L5

                    // get extendedAttributeId, attributeTypeId
                    $exAttributeId = 0;
                    $attributeTypeId = 0;
                    $storageType = "";
                    $attributeTypeName = "";
                    $attributeName = "";    
                    $sredAttributeSubtype = "";
                    $storageType = "";
                    $attributeTypeName = "";
                    $attributeName = "";   
                    $subtypeName   = "" ;               
                    //  $filter = 0;  // no organization filter

                    $rsed = $oExtAttribute->findExtendedAttribute($orgid, $extendedSubtypeId, $rtgId);        
                    foreach($rsed as $rsed1) {
                        $exAttributeId   = $rsed1['attributeId'];
                        $attributeTypeId = $rsed1['attributeTypeId'];
                        $attributeName   = $rsed1['attributeName'];
                    }

                    $subtypeName   = $oExtSubType->retrieveName($extendedSubtypeId);

                    // get storage type from table extended_attribute_type

                    $rsed = $oExtAttributeType->findStorageType($orgid,$attributeTypeId, $rtgId);
                    foreach ($rsed as $rsed1) {
                        $storageType = $rsed1['storageType'];
                        $attributeTypeName = $rsed1['attributeTypeName'];
                    }

                    if ($incExtDataName == 1) {
                        $sredChatIntro = $sredChatIntro . " ". $extendedEntityName;
                    }

                    // get extendadEAV data
                    $rseav = $oExtEAV->findEAV($orgid,$entityId, $bLang, $tLang);
                    $urlcount =  0;


                    if (!empty($rseav)) {  // L6
                        foreach ($rseav as $rs00){   // L7

                            $srSolution = "";  
                            // $srVString = stripslashes($rs00['valueString']);
                            $lang        = $rs00->lang; 
                            $attributeId = $rs00->extendedAttributeId;    
                            $srVString = stripslashes($rs00->valueString); 
 

                            switch ($storageType) {
                              case "CHAR":
                                $srSolution = stripslashes($rs00->valueString);
                                break;
                              case "VARCHAR":
                                $srSolution =  stripslashes($rs00->valueString);
                                break;
                              case "DATETIME":
                                $srSolution =  $rs00->valueDate;
                                break;
                              case "VARCHAR":
                                $srSolution =  stripslashes($rs00->valueString);
                                break;
                              default:
                                $srSolution =  stripslashes($rs00->valueString);
                                break;
                            }

                            $sredValueString =  $srSolution;
                            $srHasExtendedData++;
                            $sredParentId = $srId;

                            // Get attribute name  -------------------

                            $rs02 = $oExtAttribute->findById($attributeId); 
                            foreach($rs02 as $rs03) {
                          	     $attributeName   = $rs03['attributeName'];
                            }


                            // remove duplicate chat intro for URL
                            if ($attributeTypeName == "URL") {
                                $urlcount++;
                                if ($urlcount > 1 ) {
                                    $sredChatIntro = "";
                                }
                            }                                 

                            //  ---------------------------------------- 
                            // check catIntro length
                            $chatIntroTextLen = strlen($sredChatIntro);
                            If ($chatIntroTextLen > $chatIntroLen) {
                                $sredChatIntro = substr($sredChatIntro,0, $chatIntroLen);
                            }
                      
                            $oSolRelExdata->insertRelExdata($sredParentId, $sredChatIntro, 
                               $attributeName,$attributeTypeName, $sredValueString,$lang,$orderid, $userid);


                            //  update solution_option
                            if ($srHasExtendedData > 0) {
                                $srHasExtendedData = 1;
                                $oSolutionRelation->updateHasextendeddata($srId, $srHasExtendedData);        
                            }
                        }  // END L7
                    }   // END L6

                 }  // END L5

              }  // END L4

           } // END L3            

          }  // END L2
      }   // END L1 

   }     


    //--------------------------------------------------------------------
    public function makeSolutionOption ($userid,$orgid,$bLang,$tLang)
	 {

        // Instantiate classes
    $oRelation           = new Relation();
    $oRelationLink       = new RelationLink();
    $oSolutionRelation   = new SolutionRelation();
    $oRelationLanguage   = new RelationLanguage();
    $oSolutionOption     = new SolutionOption();
    $oTerm               = new Term();
    $oRelationType       = new RelationType();
    $oRTFilter           = new RelationTypeFilter();
    $oFunctionHelper     = new FunctionHelper();
        
    // 1. get solution relations for this personality and session id
    $soHasExtendedData = 0;
    $soParentId        = 0;
    $soSolutionId      = 0;
    $lTermId           = 0;
    $rTypeId           = 0;
    $rTermId           = 0;
    $soSfId            = -1;
    $soProcess         = -1;

    $custom     = "";
    $problemId  = 0;
    $skipRel    = 0;
 

    $rsdata = $oSolutionRelation->getByUserOrderByRating($userid,$skipRel,$problemId);
    if (!empty($rsdata)) { 
  
        foreach ($rsdata as $rsRel) {     
            $srId          = $rsRel['srId'];
            $srsfId        = $rsRel['srsfId'];
            $srRelationId  = $rsRel['srRelationId'];
            $srRating      = $rsRel['srRating'];

            // 2. get relation links for this solution relation
            $rs = $oRelationLink->getByLeftRelationIdOrder($srRelationId);  
            foreach($rs as $rsOption) {
                $soSolutionId   = $rsOption['rightRelationId'];  
                $linkTermId     = $rsOption['linkTermId'];
                $linkOrder      = $rsOption['linkOrder']; 
                $soSolution     = "";
                $linkTypeName   = $oTerm->retrieveTermName($linkTermId);   
                $lang           = $bLang;
                $soShortText    = "";

                //// The optional text inherits access from the parent relation //////////
                $parentOwnerId = $this->getParentOwnerId($orgid,$soSolutionId);
                if ($parentOwnerId == 0) {
                    $parentOwnerId = $orgid;
                }
                //////////////////////////////////////////////////////////////////

                //$RTrs = $oRelationLanguage->getText($soSolutionId,$orgid,$bLang, $tLang);
                $RTrs = $oRelationLanguage->getText($soSolutionId,$parentOwnerId,$bLang, $tLang);
                foreach($RTrs as $rs0) {

                    if (isset($rs0->optionalText)) {
                        $soSolution = $rs0->optionalText;
                        $soSolution = ucfirst($soSolution);
                        $lang       = $rs0->language_code;
                    }

                    if (isset($rs0->shortText)) {
                        $soShortText = $rs0->shortText;
                        $lang       = $rs0->language_code;
                    }
                }

                if ($soSolution == "") {
                    $soSolution =  $this->makeRelationText($soSolutionId,$custom);
                    $lang       = $bLang;
                }


                // insert solution option                   
                $oSolutionOption->insertOption($srId,$linkTypeName,$soSolution,$soSolutionId,
                    $lTermId,$rTypeId,$rTermId,$soShortText,$lang,$linkOrder,$srRating,
                    $soHasExtendedData,$soParentId, $soSfId, $userid, $soProcess, $srsfId);

            }
         }
      }

      // delete duplicate records
      $oSolutionOption->deleteDuplicate($userid);

   }		 


    //--------------------------------------------------------------------
    /**
        Make options from solution relations
     */
    public function makeSolutionOptionLink ($userid,$orgid,$bLang,$tLang)
     {
        // Instantiate classes
        $oRelationLink       = new RelationLink();
        $oSolutionOption     = new SolutionOption();
        $oRelationLanguage   = new RelationLanguage();  
        $oTerm               = new Term();     
                
        // 1. get solution relations for this personality and session id
        $soHasExtendedData = 0;
        $soParentId        = 0;
        $hasParent         = 1;
        $soLeftTermId      = 0;
        $soRelationTypeId  = 0;
        $soRightTermId     = 0; 
        $custom            = "";
        $soSfId            = -1;
        $soFactId          = 0;
        $soProcess         = -1;

        $rsdata = $oSolutionOption->getByUserParent($userid,$soParentId);
    
        foreach ($rsdata as $rsRel){            
            $soId          = $rsRel['soId'];
            $sosrId        = $rsRel['sosrId'];
            $soFactId      = $rsRel['soFactId'];
            $soRelationId  = $rsRel['soSolutionId'];
            $soProblemId   = $rsRel['soProblemId'];
            $soRating      = $rsRel['soRating'];
            $soParentId    = $soId;

            // 2. get relation links for this solution option
            $rs = $oRelationLink->getByLeftRelationIdOrder($soRelationId);  
            foreach($rs as $rsOption) {
                $soSolutionId      = $rsOption['rightRelationId'];  
                $linkTermId        = $rsOption['linkTermId'];
                $linkOrder         = $rsOption['linkOrder']; 
                $soSolution        = "";
                $linkTypeName      = $oTerm->retrieveTermName($linkTermId);  
                $lang              = $bLang;  
                $soShortText       = "";


                //// The optional text inherits access from the parent relation //////////
                $parentOwnerId = $this->getParentOwnerId($orgid,$soSolutionId);
                if ($parentOwnerId == 0) {
                    $parentOwnerId = $orgid;
                }
               

                $RTrs = $oRelationLanguage->getText($soSolutionId,$parentOwnerId,$bLang, $tLang);
                foreach($RTrs as $rs0) {
                    $soSolution = $rs0->optionalText;
                    $soSolution = ucfirst($soSolution);
                    $lang       = $rs0->language_code;
                    if (isset($rs0->shortText)) {
                        $soShortText = $rs0->shortText;
                    }
                }


                if ($soSolution == "") {
                    $soSolution =  $this->makeRelationText($soSolutionId,$custom);
                }                 
                  
                // insert solution option
                $hasParent = 1;
                $lastInsertId = $oSolutionOption->insertOption($sosrId,$linkTypeName,$soSolution,$soSolutionId,
                    $soLeftTermId,$soRelationTypeId,$soRightTermId,$soShortText,$lang, $linkOrder,$soRating,
                    $soHasExtendedData,$soId, $soSfId,$userid, $soProcess, $soFactId);

                $soParentId = $lastInsertId;

                // Insert option links at any deeper level    //
                while ($soParentId > 0) {
                   $lastInsertId = $this->makeSolutionOptionDeepLink($userid,$orgid,$bLang,$tLang,$lastInsertId,
                                         $sosrId, $soFactId, $soSolutionId, $parentOwnerId);
                   $soParentId = $lastInsertId; 
                }

            }
        }


     }



    //--------------------------------------------------------------------
    /**
        Make options from solution option at any deeper level
     */
    public function makeSolutionOptionDeepLink ($userid,$orgid,$bLang,$tLang,$id,$sosrId, $soFactId, $soSolutionId,$parentOwnerId )
     {
        // Instantiate classes
        $oRelationLink       = new RelationLink();
        $oSolutionOption     = new SolutionOption();
        $oRelationLanguage   = new RelationLanguage(); 
        $oTerm               = new Term();        
                
        // 1. get solution relations for this personality and session id
        $soHasExtendedData = 0;
        $hasParent         = 1;
        $soLeftTermId      = 0;
        $soRelationTypeId  = 0;
        $soRightTermId     = 0; 
        $custom            = "";
        $soSfId            = -1;
        $soFactId          = 0;
        $soRating          = 0;
        $lastInsertId      = 0;
        $soProcess         = -1;
        $hasParent         = 1;
        $soParentId        = $id;

        //   FIND CHILD LINKED OPTION KR
            
        $rs = $oRelationLink->getByLeftRelationIdOrder($soSolutionId);  
        foreach($rs as $rsOption) {
            $soSolutionId      = $rsOption['rightRelationId'];  
            $linkTermId        = $rsOption['linkTermId'];
            $linkOrder         = $rsOption['linkOrder']; 
            $soSolution        = "";
            $linkTypeName      = $oTerm->retrieveTermName($linkTermId);  
            $lang              = $bLang;  
            $soShortText       = "";
 
            $RTrs = $oRelationLanguage->getText($soSolutionId,$parentOwnerId ,$bLang, $tLang);           

            foreach($RTrs as $rs0) {
                $soSolution = $rs0->optionalText;
                $soSolution = ucfirst($soSolution);
                $lang       = $rs0->language_code;
                if (isset($rs0->shortText)) {
                    $soShortText = $rs0->shortText;
                }
            }

            if ($soSolution == "") {
                  $soSolution =  $this->makeRelationText($soSolutionId,$custom);
            }                 
                                         
            // insert solution option

            $lastInsertId =  $oSolutionOption->insertOption($sosrId,$linkTypeName,$soSolution,$soSolutionId, 
                $soLeftTermId,$soRelationTypeId,$soRightTermId,$soShortText,$lang,$linkOrder,$soRating, 
                $soHasExtendedData,$id, $soSfId,$userid, $soProcess, $soFactId);


            ////// Insert recursively option links at deeper levels    //////////////////////////
            $soParentId = $lastInsertId;
            while ($soParentId > 0) {
                $lastInsertId = $this->makeSolutionOptionDeepLink($userid,$orgid,$bLang,$tLang,$lastInsertId,
                                       $sosrId, $soFactId, $soSolutionId, $parentOwnerId);
                $soParentId = $lastInsertId; 
            }
                ////////////////////////////////////////////////////////////////////////////////////

        }
        

        return $lastInsertId;
     }


    //--------------------------------------------------------------------
    /**
        getParentOwnerId()
        Given a relation id, get the orgid that has access to it.
        LOGIC
        if the relation is public (0), return $orgid
        if the relation is protected and the $orgid is the owner, retunr $orgid
        if the relation is protected and the $orgid can access protected data from the ownerid, return $ownerid
        if the relation is private and the $orgid is equal the ownerid, return $orgid
        otherwise return 0
     */
    public function getParentOwnerId($orgid,$krId)
     {
        // Instantiate classes
        $oRelation        = new Relation();
        $oOrgAssociation  = new OrganizationAssociation();

        $parentOwnerId      = 0;
        $ownerId            = 0;
        $ownership          = 0;
        $canAccessProtected = 0;
        $rtgId              = 8;
                      
        // 1. get ownerid and ownwership if the relation
        $rs = $oRelation->findById($krId);
        foreach ($rs as $rs0){        
            $ownerId    = $rs0['ownerId'];
            $ownership  = $rs0['ownership'];
        }

        // 2. Get organization association
        $rs = $oOrgAssociation->getByTriple($orgid,$rtgId,$ownerId);
        foreach ($rs as $rs0){   
            $canAccessProtected = 1;
        }

      
        // 3. Determine if the parent org id of the KR
        switch ($ownership) {

            case 0:
                // if the relation is public (0), return $orgid
                $parentOwnerId  = $orgid;
            break;


            case 1:
               // if the relation is protected and the $orgid is equal to the ownerid, retunr $orgid
                if ($ownerId == $orgid) {
                    $parentOwnerId  = $orgid;
                } 

                // if the relation is protected and the $orgid can access protected data from the ownerid, return $ownerid
                if ($canAccessProtected == 1) {
                    $parentOwnerId  = $ownerId;
                } 
            break; 


            case 2:
                // if the relation is private (2) and the $orgid is equal the ownerid, return $orgid
                if ($ownerId == $orgid) {
                    $parentOwnerId  = $orgid;
                } 
            break;            

        }


        // 4. logic
        //   if $parentOwnerId > 0, this $orgid has access to optional text
        //   otherwise this $orgid has no access to optional text

        return $parentOwnerId;

     }




    //--------------------------------------------------------------------
    /**
        Make options from solution relations
        1. select solution option records with negative ratings
        2. create and add options  

     */
    public function makeSolutionProblemOptionLink ($userid,$orgid,$bLang,$tLang)

     {
        // Instantiate classes
        $oRelationLink       = new RelationLink();
        $oSolutionOption     = new SolutionOption();
        $oFunctionHelper     = new FunctionHelper();
        $oRelation           = new Relation();
        $oRelationLanguage   = new RelationLanguage();       
        $oTerm               = new Term();
        $oRelationType       = new RelationType();
        $oRTFilter           = new RelationTypeFilter();
                
        // 1. get solution relations for this personality and session id
        $soHasExtendedData = 0;
        $soParentId        = 0;
        $hasParent         = 0;
        $soLeftTermId      = 0;
        $soRelationTypeId  = 0;
        $soRightTermId     = 0; 
        $custom            = "";
        $soSfId            = -1;
        $soFactId          = 0;
        $soProcess         = -1;

        $rsdata = $oSolutionOption->getNegativeByUserParent($userid,$soParentId);
    
        foreach ($rsdata as $rsRel){            
            $soId          = $rsRel['soId'];
            $sosrId        = $rsRel['sosrId'];
            $soFactId      = $rsrel['soFactId'];
            $soRelationId  = $rsRel['soSolutionId'];
            $soRating      = $rsRel['soRating'];
            $soParentId    = $soId;

            // 2. get relation links for this solution option
            $rs = $oRelationLink->getByLeftRelationIdOrder($soRelationId);  
            foreach($rs as $rsOption) {
                $soSolutionId      = $rsOption['rightRelationId'];  
                $linkTermId        = $rsOption['linkTermId'];
                $linkOrder         = $rsOption['linkOrder']; 
                $soSolution        = "";
                $linkTypeName      = $oTerm->retrieveTermName($linkTermId);  
                $lang              = $bLang;  
                $soShortText       = "";

                $RTrs = $oRelationLanguage->getText($soSolutionId,$orgid,$bLang, $tLang);
                foreach($RTrs as $rs0) {
                    $soSolution = $rs0->optionalText;
                    $soSolution = ucfirst($soSolution);
                    $lang       = $rs0->language_code;
                    if (isset($rs0->shortText)) {
                        $soShortText = $rs0->shortText;
                    }
                }

                if ($soSolution == "") {
                    $soSolution =  $this->makeRelationText($soSolutionId,$custom);
                }                 
                
                // insert solution option
                $hasParent = 1;
                $oSolutionOption->insertOption($sosrId,$linkTypeName,$soSolution,$soSolutionId,
                    $soLeftTermId,$soRelationTypeId,$soRightTermId,$soShortText,$lang,$linkOrder,$soRating,
                    $soHasExtendedData,$soParentId, $soSfId,$userid, $soProcess, $soFactId);

            }
        }

        // delete duplicate records
        $oSolutionOption->deleteDuplicate($userid);

     }


    //--------------------------------------------------------------------
    public function makeOptionExtendedData ($userid, $orgid, $portalType,$bLang,$tLang)
    {

        // Instantiate classes
        $oSolutionOption     = new SolutionOption();
        $oSolOptionExdata    = new SolutionOptionExdata();
        $oExtLink            = new ExtendedLink();
        $oExtEntity          = new ExtendedEntity();
        $oExtAttribute       = new ExtendedAttribute();
        $oExtAttributeType   = new ExtendedAttributeType();
        $oExtEAV             = new ExtendedEAV();
        $oExtendedLinkTranslation = new ExtendedLinkTranslation();
      //  $oRTGroup            = new RelationTypeGroup();    
        $oExtSubType         = new \App\Models\ExtendedSubType();         
        $oRTFilter           = new RelationTypeFilter(); 
        $oFunctionHelper     = new FunctionHelper();
        $parentTableId   = 2;

        $lang            = $bLang;
        $step = 8;   // can access protected data
        $rtCanAccessProtectedDataId = $oRTFilter->retrieveByStep($step);
     //   $rtgId = $oRTGroup->retrieveByRelationType($rtCanAccessProtectedDataId);
        $rtgId = 8;

        // get chatIntro field length
        $chatIntroLen = \Config::get('kama_dei.static.soedChatIntroLength',1000);        

        // 1. get solution relations for this user
        $rsoption = $oSolutionOption->getByUser($userid);

        if (!empty($rsoption)) {   // 1
    
            foreach ($rsoption as $rsOP) {    // L2        
                $soId              = $rsOP['soId'];
                $soRelationId      = $rsOP['soSolutionId'];
                $soHasED           = $rsOP['soHasExtendedData'];
                $soHasExtendedData = 0;

              if ($soHasED == 0) {   // L3

                /* find extended data                   */

                // get entityId and chatIntro
                $soedChatIntro = "";
                $extendedSubtypeId = 0;
                $entityId        = 0;
                $incExtDataName  = 0;
                $incExtDataChatIntro = 0;
                $orderid = 0;

                $rsexlink  = $oExtLink->getEntityId($orgid,$parentTableId, $soRelationId, $rtgId);

                foreach ($rsexlink as $rselink0) {  // L4
                    $extLinkId = $rselink0['extendedLinkId'];
                    $entityId = $rselink0['entityId'];
                    $lang     = $bLang;
                             
                    $incExtDataName = $rselink0['includedExtDataName'];
                    $incExtDataChatIntro = $rselink0['includedExtDataChatIntro'];            
                    $orderid = $rselink0['orderid'];
                    $soedChatIntro = "";

                    $tmped = $oExtendedLinkTranslation->getText($extLinkId,$bLang, $tLang);
           
                    foreach ($tmped as $tmped0) {
                        $soedChatIntro = $tmped0->chatIntro;
                        $lang          = $tmped0->lang;

                        if (isset($rsed0->voiceIntro) and $portalType == "voice") {
                            $soedChatIntro = $rsed0->voiceIntro;
                        }
                    }                    


                    // find extended data for each extendedLinkId
                   
                    // get data from extended_entity
                    $extendedSubtypeId = 0;
                    $extendedEntityName = "";
                    $hasEntity = 0;

                    $rsEE = $oExtEntity->getEEId($orgid, $entityId, $rtgId);

                    foreach ($rsEE as $rsEE0) {
                        $extendedSubtypeId  = $rsEE0->extendedSubTypeId;
                        $extendedEntityName = $rsEE0->extendedEntityName; 
                        $hasEntity = 1;
                    }
    

                    if ($hasEntity == 1) {  // L5


                      // get extendedAttributeId, attributeTypeId
                      $exAttributeId = 0;
                      $attributeTypeId = 0;
                      $storageType = "";
                      $attributeTypeName = "";
                      $attributeName = "";    
                      $soedAttributeSubtype = "";
                      $storageType = "";
                      $attributeTypeName = "";
                      $attributeName = "";  
                      $subtypeName   = "";                 
                      $filter = 0; // no organization filter
 
                      $rsed = $oExtAttribute->findExtendedAttribute($orgid, $extendedSubtypeId,$filter);        
                      foreach($rsed as $rsed1) {
                        $exAttributeId   = $rsed1['attributeId'];
                        $attributeTypeId = $rsed1['attributeTypeId'];
                        $attributeName   = $rsed1['attributeName'];

                      }

                      $subtypeName   = $oExtSubType->retrieveName($extendedSubtypeId);

                      // get storage type from table extended_attribute_type

                      $rsed = $oExtAttributeType->findStorageType($orgid, $attributeTypeId,$rtgId);
                      foreach ($rsed as $rsed1) {
                        $storageType = $rsed1['storageType'];
                        $attributeTypeName = $rsed1['attributeTypeName'];
                      }

                      if ($incExtDataName == 1) {
                         $soedChatIntro = $soedChatIntro . " ". $extendedEntityName;
                      }


                      // get extendadEAV ;
                      $urlcount = 0;
                      $rseav = $oExtEAV->findEAV($orgid,$entityId, $bLang, $tLang);

                      if (!empty($rseav)) {    // L6
                          foreach ($rseav as $rs00){  // L7

                             $extendedEAVID = $rs00->extendedEAVID;
                             $lang          = $rs00->lang; 
                             $soSolution = "";  
                             $srVString = stripslashes($rs00->valueString); 

                             switch ($storageType) {
                              case "CHAR":
                                $soSolution = stripslashes($rs00->valueString);
                                break;
                              case "VARCHAR":
                                $soSolution =  stripslashes($rs00->valueString);
                                break;
                              case "DATETIME":
                                $soSolution =  $rs00->valueDate;
                                break;
                              case "VARCHAR":
                                $soSolution =  stripslashes($rs00->valueString);
                                break;
                              default:
                                $soSolution =  stripslashes($rs00->valueString);
                                break;
                            }

                            $soedValueString =  $soSolution;
                            $soHasExtendedData++;
                            $soedParentId = $soId;

                            $attributeId = $rs00->extendedAttributeId;

                            $rsed2 = $oExtAttribute->findById($attributeId); 
                            foreach($rsed2 as $rsed3) {
                          	     $attributeName   = $rsed3['attributeName'];
                            }

                            // remove duplicate chat intro for URL
                            if ($attributeTypeName == "URL") {
                                $urlcount++;
                                if ($urlcount > 1 ) {
                                    $soedChatIntro = "";
                                }
                            }                                  

                            // check chatIntro length
                            $chatIntroTextLen = strlen($soedChatIntro);
                            If ($chatIntroTextLen > $chatIntroLen) {
                               $soedChatIntro = substr($soedChatIntro,0, $chatIntroLen);
                            }                       


                            $oSolOptionExdata->insertOptExdata($soedParentId, $soedChatIntro, 
                               $attributeName,$attributeTypeName, $soedValueString,$lang ,$orderid, $userid);   
                           //  update solution_option
                           if ($soHasExtendedData > 0) {
                              $soHasExtendedData = 1;
                              $oSolutionOption->updateHasextendeddata($soId, $soHasExtendedData);        
                           } 

                        }  // END L7

                    }   // END L6

                 }  // END L5

              }  // END L4

           } // END L3            

          }  // END L2

      }   // END L1 

    }     

	
    //--------------------------------------------------------------------
    public function makeRelationText($relationId,$custom="")
	  {

        // Instantiate classes
        $oTerm               = new Term();
        $oRelationType       = new RelationType();
        $oRelation           = new Relation();
        $oFunctionHelper     = new FunctionHelper();
        
        // get relationId field values
        $you = " You"; 
        $person = "person";

        $leftTermId = 0;
        $relationTypeId  = 0;
        $rightTermId = 0;
        $relationText = "";   
    
        $rsdata = $oRelation->findById($relationId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){              
                $leftTermId     = $rs0->leftTermId;
                $relationTypeId = $rs0->relationTypeId;
                $rightTermId    = $rs0->rightTermId;
            }
        }


/*    
        // retrieve left term name
        if ($custom == ""){             // default name
            $leftTermName = $oTerm->retrieveTermName($leftTermId);           
        } else {
        	$leftTermName = $you;       // customized name
        }

        $leftTermName = $oFunctionHelper->replacePersonByYou($leftTermName);
        // retrieve relatio type name
        $relationTypeName = $oRelationType->retrieveNameById($relationTypeId);  

        // retrieve right term name
        $rightTermName = $oTerm->retrieveTermName($rightTermId);  


        $relationText  = $leftTermName ." ". $relationTypeName ." ". $rightTermName;  

 */       

        $relationText = $oRelation->seekRelationName($relationId);

        $relationText = ucfirst($relationText);

        return $relationText;		
    }

    //--------------------------------------------------------------------
    public function makeProblemText($relationId,$orgid,$bLang,$tLang)
    {

        // Instantiate classes
        $oRelationLanguage   = new RelationLanguage();
           
        $relationText = "";  
        $person = "You"; 
                      
        $lang = $bLang;
        $sfRelation = "";
        $sfShortText = "";
     
        $RTrs = $oRelationLanguage->getText($relationId,$orgid,$bLang, $tLang);
        if (!empty($RTrs)) {

          foreach($RTrs as $rs0) {
            $sfRelation = $rs0->optionalText;
            $sfRelation = ucfirst($sfRelation);
            $lang       = $rs0->language_code;
            if (isset($rs0->shortText)) {
                $sfShortText = $rs0->shortText;
            }
          }


        }


        if ($sfRelation == "") {
            $sfRelation =  $this->makeRelationText($relationId,$person);
            $lang       = $bLang; 
        }
                                  
        $aProblem['sfRelation']  = $sfRelation;
        $aProblem['sfLanguage']  = $lang;
        $aProblem['sfShortText'] = $sfShortText;
   
        return $aProblem;  
    }
 

    //--------------------------------------------------------------------
    public function updatePersonalityValue($personaId,$personalityId, $slidebar, $orgId, $userId)
    {
      $oPersonalityValue = new PersonalityValue();
      $ownership = 2;            // default private ownership

      foreach($slidebar as $key=>$value) {

        $termId      = $key;
        $scalarValue = (int)$value;
        $PVid = $oPersonalityValue->retrieveByPersonalityValue($personalityId,$termId);

        if ($PVid > 0 ) {   // existining personality-value:   update
            $oPersonalityValue->updatePVScalarById($PVid,$termId,$scalarValue);

        } else {           // new personality-value:   add
            $oPersonalityValue->addPVScalar($personalityId,$termId,$scalarValue,$ownership,$orgId,$userId);      
        }

      }

    }

    //--------------------------------------------------------------------
 /*
    public function hasSuperterm($orgid,$userid)
     {
        $oSolutionFact   = new SolutionFact();
        $oRTLink         = new RelationTermLink();

        $wrId = 0; 
        $linkTermId = 52976;
     
        $rs = $oSolutionFact->getByUser($userid);        
        foreach ($rs as $rs0){              
            $relationId  = $rs0['sfRelationId'];
          
            $rId = $oRTLink->retrieveTerm($orgid,$relationId,$linkTermId); 
            if ($rId > 0) {
                $wrId = $rId;  
            }
        }

        return $wrId;

     }
*/

    //--------------------------------------------------------------------
    public function getSolutionRelationString($userid)
     {
        // Instantiate classes
        $oSolutionRelation       = new SolutionRelation();  
        $solutionRelationText = "";
        $skipRel = 0;
        $problemId = 0;
		
        $rsdata = $oSolutionRelation->getByUserOrderByRating($userid,$skipRel,$problemId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){						  
                $srRelation            = $rs0->srRelation;
                $srRelation            = $srRelation."; ";
                $solutionRelationText .= $srRelation;
            }
        }
		
        return $solutionRelationText;
     }
	 

    //--------------------------------------------------------------------
    public function hasSolution($userid)
     {
        // Instantiate classes
        $oSolutionRelation       = new SolutionRelation(); 
        $solCount = 0; 
    
        $rsdata = $oSolutionRelation->hasSolutionRelation($userid);

        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){              
            $solCount++;
            }
        }        
    
        return $solCount;
     }  


    //--------------------------------------------------------------------
    /* get subset facts and make output                                 */
    /* value format
      *14**8888888888*1*9999999999   *13**     subset preprocessing pick
      *141*8888888888*1*9999999999   *131*     subset preprocessing Select all
      *140*8888888888*1*9999999999   *130*     subset preprocessing exit
                                                                       */
    public function getSubsetFactArray($userid, $hasRating)
     {
        // Instantiate classes
        $oSolutionFact      = new SolutionFact();
        $oFunctionHelper    = new FunctionHelper();
        $solutionText       =  array();
        $hasParentId        = "1";                // no parent record
        $zero               = 0;
				
        $rsdata = $oSolutionFact->getByUser0($userid);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){						  
                $srId           = $rs0->sfId;
                $srIdString     = $oFunctionHelper->makePadString($srId,10,"0"); 
                $subsetId       = $oFunctionHelper->makePadString($zero,10,"0");
                $srIdValue      = "*14**".$srIdString."*".$hasParentId."*".$subsetId;
                $srRelation     = $rs0->sfssFact;			
                $solutionText[] = ['text'=>$srRelation  , 'value'=>$srIdValue  ];                             
            }
        }
        return $solutionText;
     }	


    //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                    */

    public function getProblemArray($orgid, $userid,$portalType, $bLang,$tLang,$problemId,$problemCount,$removeP )
    {


        $oSolutionRelation  = new SolutionRelation();
        $oFunctionHelper    = new FunctionHelper();

        $problemText    = array();
        $wsFact         = "*-*-*";
        $hasParentId    = "0";  
        $parentId       = $oFunctionHelper->makePadString(0,10,"0");
        $pCount         = 0;
        $lang           = $bLang;
        $button         = "button";
                    
        $rsProblem = $oSolutionRelation->getProblemId($userid);                    
        foreach($rsProblem as $rs0) {
                    //$srId           = $rsP->srId;
            $srId        = $rs0->srsfId;
            $srsfId      = $rs0->srsfId;
            $srFact      = $rs0->srFact; 
            $shortText   = $rs0->sfShortText;
            if($wsFact != $srFact ) {
                $srRating     = $rs0->sfRating;
                $lang         = $rs0->sfLanguage;
                $srIdString   = $oFunctionHelper->makePadString($srId,10,"0"); 
                $problemId    = $oFunctionHelper->makePadString($srsfId,10,"0");

                if ($problemCount == 1 and $removeP == 0) {
                    $srIdValue      = "*803*".$srIdString."*".$hasParentId."*".$parentId."*0".$problemId;
                } else {
                    $srIdValue      = "*803*".$srIdString."*".$hasParentId."*".$parentId;  
                }

                $problemText[] = ['text'=>$srFact,'shortText'=>$shortText, 'language'=>$lang, 
                        'atttype'=>$button, 'elementType'=>$button, 'elementOrder'=>'3', 'value'=>$srIdValue ];

                $wsFact = $srFact;
                $pCount++;
                }
        } 

        return $problemText;

    }


    //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                    */

    public function getSolutionRelationArray($orgid, $userid,$portalType,$hasRating, $isLex, $bLang,$tLang,
         $problemId, $hasLiveAgent, $liveKrId)
     {

        // Instantiate classes
        $oSolutionRelation  = new SolutionRelation();
        $oFunctionHelper    = new FunctionHelper();
        $oRTFilter          = new RelationTypeFilter();
        $oRelExdata         = new SolutionRelationExdata();         
        $solutionText       =  array();
        $hasParentId        = "0";                // no parent record
        $hasED              = 0;
        $parentId           = $oFunctionHelper->makePadString(0,10,"0");
        $step               = 11;   // omit this relation type in solution response
        $rtFilterId         = $oRTFilter->retrieveByStep($step);    
        $isfound = 0;
        $opsource = "0";
        $lang = $bLang;

        $rsdata = $oSolutionRelation->getByUserOrderByRating($userid, $rtFilterId, $problemId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $srId           = $rs0->srId;
                $problemId      = $rs0->srsfId;
                $shortText      = $rs0->srShortText;
                $srIdString     = $oFunctionHelper->makePadString($srId,10,"0"); 
                $problemId      = $oFunctionHelper->makePadString($problemId,10,"0");     

           
            // new 
                $srIdValue      = "*****".$srIdString."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId;


                $srRelation     = $rs0->srRelation;   
                $srRating       = $rs0->srRating; 
                $srRelationId   = $rs0->srRelationId;
                $hasExtendedData = $rs0->srHasExtendedData;
                $lang            = $rs0->srLanguage;
                $srChatIntro    = "";
                $intent         = "";
                $slot           = "";
                $slotvalue      = "";
                $button         = "button";

                $isfound = 1; 

   
                if ($hasRating == 1) {
                    $solutionText[] = ['text'=>$srRelation,'shortText'=>$shortText, 'atttype'=>$button,'elementType'=>$button,
                      'value'=>$srIdValue ,'rating'=>$srRating ];  
                } else {
                  if ($isLex == 1) { // called from lex api
                     $solutionText[] = ['text'=>$srRelation , 'shortText'=>$shortText, 'intent'=>$intent,
                     'slot'=>$slot, 'slotvalue'=>$slotvalue, 'language'=>$lang,'elementType'=>$button, 
                     'value'=>$srIdValue ];  
                  } else {
                     $solutionText[] = ['text'=>$srRelation , 'shortText'=>$shortText, 
                        'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,'elementOrder'=>"3", 'value'=>$srIdValue ]; 
                     $isfound = 2; 
                               
                  }                  
                 
                }
          
            }
        }

        if ($hasLiveAgent == 1) {
             $transferText = $this->getTransferButton($orgid,$portalType, $bLang, $tLang,$liveKrId);
             $solutionText[] = $transferText;
        }

      if ($isLex == 99) {
          return $isfound;
       } else {
          return $solutionText;
       }
        //return $solutionText;
     } 


    //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                    */

    public function getPickRelationArray($orgid, $userid,$portalType,$bLang,$tLang,$problemId, $prefix)
     {

        // Instantiate classes
        $oSolutionRelation  = new SolutionRelation();
        $oFunctionHelper    = new FunctionHelper();
        $oRTFilter          = new RelationTypeFilter();
        $oRelExdata         = new SolutionRelationExdata();         
        $solutionText       =  array();
        $hasParentId        = "0";                // no parent record
        $hasED              = 0;
        $parentId           = $oFunctionHelper->makePadString(0,10,"0");
        $step               = 11;   // omit this relation type in solution response
        $rtFilterId         = $oRTFilter->retrieveByStep($step);    
        $isfound = 0;
        $opsource = "0";
        $lang = $bLang;

        $rsdata = $oSolutionRelation->getByUserOrderByRating($userid, $rtFilterId, $problemId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $srId           = $rs0->srId;
                $problemId      = $rs0->srsfId;
                $shortText      = $rs0->srShortText;
                $srIdString     = $oFunctionHelper->makePadString($srId,10,"0"); 
                $problemId      = $oFunctionHelper->makePadString($problemId,10,"0");     

                $srIdValue      = $prefix.$srIdString."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId;

                $srRelation     = $rs0->srRelation;   
                $srRating       = $rs0->srRating; 
                $srRelationId   = $rs0->srRelationId;
                $lang            = $rs0->srLanguage;
                $button         = "button";
                $isfound = 1; 
     
                $solutionText[] = ['text'=>$srRelation , 'shortText'=>$shortText, 'language'=>$lang,
                  'atttype'=>$button,'elementType'=>$button,'elementOrder'=>"3", 'value'=>$srIdValue ]; 

            }                   
        }

        return $solutionText;
     }     

     //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                     */
    public function getProblemRelationArray($userid,$parentId, $bLang,$tLang)
     {

        // Instantiate classes
        $oSolutionRelation  = new SolutionRelation();
        $oFunctionHelper    = new FunctionHelper();
        $oRTFilter          = new RelationTypeFilter();
        $oRelExdata         = new SolutionRelationExdata();        
        $solutionText       =  array();
        $hasParentId        = "0";                // no parent record
        $hasED              = 0;
        $sparentId          = $oFunctionHelper->makePadString(0,10,"0");
        $step               = 11;   // omit this relation type in solution response
        $rtFilterId         = $oRTFilter->retrieveByStep($step);    
        $isfound = 0;
        $button  = "button"; 
        $lang =$bLang;

        $rsdata = $oSolutionRelation->getProblemSolution($userid,$parentId,$rtFilterId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $srId           = $rs0->srId;
                $srIdString     = $oFunctionHelper->makePadString($srId,10,"0"); 
                $srIdValue      = "*****".$srIdString."*".$hasParentId."*".$sparentId;
                $srRelation     = $rs0->srRelation;   
                $srRating       = $rs0->srRating; 
                $srRelationId   = $rs0->srRelationId;
                $shortText      = $rs0->srLanguage;
                $lang           = $rs0->srLanguage;
  
                $solutionText[] = ['text'=>$srRelation, 'shortText'=>$shortText, 
                'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,'elementOrder'=>"3", 'value'=>$srIdValue ];  
          
            }
        }

        return $solutionText;
     }     

    //-----------------------------------------------------------------------------
    /* Make Live Agent transfer Button                                            */
    /* value format= "*901*9999999999*0*0000000000"                               */
    public function getTransferButton($orgid, $portalType, $bLang, $tLang, $liveKrId=0)
     {
        $oMessage         = new \App\Models\Message(); 
        $oFunctionHelper  = new FunctionHelper();
        $code   = 64;  // transfer to Live agent 
        $tTransfer  = "";  
        $transferText = ""; 
        $button   = "button";

        $liveKrId = $oFunctionHelper->makePadString($liveKrId,10,"0");
        $soIdValue = "*901*".$liveKrId."*0*0000000000";   
        $rsB    = $oMessage->findMessage($code,$orgid,$bLang,$tLang);
        foreach ($rsB as $rs0){  
              $lang = $rs0->messageLanguage;   
              if ($portalType == "voice") {
                 $tTransfer = $rs0->messageVoice; 
              }  else {
                 $tTransfer = $rs0->messageText;                
              } 
        } 
        $transferText = ['text'=>$tTransfer , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
             'elementOrder'=>"4", 'value'=>$soIdValue ];
        
        return $transferText;

     }

    //--------------------------------------------------------------------
    /* Make RPA transfer Button                                             */
    /* value format= "*903*9999999999*0*0000000000"                     */
    public function getRPAButton($orgid, $portalType, $bLang, $tLang, $krId)
     {
        $oMessage         = new \App\Models\Message(); 
        $oFunctionHelper  = new FunctionHelper();
        $code   = 66;  // transfer to RPA 
        $tTransfer  = "";  
        $RPAText = ""; 
        $button   = "button";

        $krId = $oFunctionHelper->makePadString($krId,10,"0");
        $soIdValue = "*903*".$krId."*0*0000000000";   
        $rsB    = $oMessage->findMessage($code,$orgid,$bLang,$tLang);
        foreach ($rsB as $rs0){  
              $lang = $rs0->messageLanguage;   
              if ($portalType == "voice") {
                 $tTransfer = $rs0->messageVoice; 
              }  else {
                 $tTransfer = $rs0->messageText;                
              } 
        } 

        $RPAText = ['text'=>$tTransfer , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
           'elementOrder'=>"4", 'value'=>$soIdValue ];
        
        return $RPAText;

     }

    //--------------------------------------------------------------------
    /* Make RPA transfer Button                                             */
    /* value format= "*903*9999999999*0*0000000000"                     */
    public function getExitRPAButton($orgid, $portalType, $bLang, $tLang, $krId)
     {
        $oMessage         = new \App\Models\Message(); 
        $oFunctionHelper  = new FunctionHelper();
        $code   = 68;  // Exit RPA 
        $tTransfer  = "";  
        $RPAText = ""; 
        $button   = "button";

        $krId = $oFunctionHelper->makePadString($krId,10,"0");
        $soIdValue = "*904*".$krId."*0*0000000000";  

        $rsB    = $oMessage->findMessage($code,$orgid,$bLang,$tLang);
        foreach ($rsB as $rs0){  
              $lang = $rs0->messageLanguage;   
              if ($portalType == "voice") {
                 $tTransfer = $rs0->messageVoice; 
              }  else {
                 $tTransfer = $rs0->messageText;                
              } 
        } 

        $ExitRPAText = ['text'=>$tTransfer , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
           'elementOrder'=>"4", 'value'=>$soIdValue ];
        
        return $ExitRPAText;

     }     


    //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                     */
    public function getLexSolutionRelationArray($orgid, $userid, $hasRating, $isLex,$bLang,$tLang)
     {

        // Instantiate classes
        $oSolutionRelation  = new SolutionRelation();
        $oFunctionHelper    = new FunctionHelper();
        $oRelExdata         = new SolutionRelationExdata();
         

        $botid   = 'BookTrip';
        $aliasid = 'BookTripAlias';         
        $oLexClass          = new LexClass($botid, $aliasid, $orgid, $userid);        
        $solutionText       =  array();
        $hasParentId        = "0";                // no parent record
        $hasED              = 0;
        $skipRel = 0;
        $problemId = 0;
        $parentId           = $oFunctionHelper->makePadString(0,10,"0");
                
        $rsdata = $oSolutionRelation->getByUserOrderByRating($userid, $skipRel, $problemId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $srId           = $rs0->srId;
                $srIdString     = $oFunctionHelper->makePadString($srId,10,"0"); 
                $srIdValue      = "*****".$srIdString."*".$hasParentId."*".$parentId;
                $srRelation     = $rs0->srRelation;   
                $srRating       = $rs0->srRating; 
                $srRelationId   = $rs0->srRelationId; 
                $lang           = $rs0->srLanguage; 
                $shortText      = $rs0->srShortText;
                $hasExtendedData = $rs0->srHasExtendedData;
                $srChatIntro    = "";
                $intent         = "";
                $slot           = "";
                $slotvalue      = "";
                
                // search mapping

                $oLexClass->findKR($srRelationId);
                $jlexTMP0  = $oLexClass->getData();
                $jlexTMP   = json_encode($jlexTMP0, JSON_UNESCAPED_SLASHES );
                $jlexTMP2  = json_encode($jlexTMP0, JSON_UNESCAPED_SLASHES );

                $aLexTMP = $jlexTMP2;

                $aLexTMP = json_decode( utf8_encode($aLexTMP), true );

                if (isset($aLexTMP['intent'])) {    
                    $intent   = $aLexTMP['intent']; 
                }
                if (isset($aLexTMP['slots'][0]['name'])) {    
                    $slot   = $aLexTMP['slots'][0]['name']; 
                }
                if (isset($aLexTMP['slots'][0]['value'])) {    
                    $slotvalue = $aLexTMP['slots'][0]['value']; 
                }


                if ($hasRating == 1) {
                    $solutionText[] = ['text'=>$srRelation , 'shortText'=>$shortText,
                       'value'=>$srIdValue , 'rating'=>$srRating ];  
                } else {
                  if ($isLex == 1) { // called from lex api
                     $solutionText[] = ['text'=>$srRelation ,  'shortText'=>$shortText,'intent'=>$intent,
                     'slot'=>$slot, 'slotvalue'=>$slotvalue,'language'=>$lang, 'value'=>$srIdValue ];  
                  } else {
                     $solutionText[] = ['text'=>$srRelation , 'shortText'=>$shortText,'language'=>$lang,
                      'value'=>$srIdValue ];                     
                  }                  
                 
                }
                          
            }
        }
        return $solutionText;

     } 


    //--------------------------------------------------------------------
    /* get Has Solution risk Button                                        */
    /* value format= "*****9999999999*0*0000000000"                         */
    //public function getSolutionReviewButton($orgid,$portalType,$optionNumber,$buttonType,$useShortText,$lang,$tLang)
    public function getSolutionReviewButton($orgid,$portalType,$inText,$buttonType,$useShortText,$lang,$tLang)
     {

        $oFunctionHelper   = new FunctionHelper();
        $button   = "";
        $bText    = "";
        $button   = "button";
        $hasParentId = 1;
        $optionNumber = $oFunctionHelper->getOptionNumber($inText);
        $bValue   = $optionNumber;
        $parentId = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $idString = $oFunctionHelper->makePadString(0,10,"0"); 
        $problemId = $oFunctionHelper->getProblemId($inText);
        $problemId = $oFunctionHelper->makePadString($problemId,10,"0");
        $oMessage = new \App\Models\Message();

        switch ($buttonType) {
            case "Risk":
           
             // $bValue = "*116*".$idString."*".$hasParentId."*".$parentId;  
                $bValue = "*116*".$idString."*".$hasParentId."*".$parentId."*0*".$problemId;  

                $code   = 53; 
                $bText  = "";      
                $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
                foreach ($rsB as $rs0){  
                   $lang = $rs0->messageLanguage;   
                   if ($portalType == "voice") {
                      $bText = $rs0->messageVoice; 
                   }  else {
                      $bText = $rs0->messageText;                
                   } 
                }
            break;

            case "Req":  

                $bValue = "*117*".$idString."*".$hasParentId."*".$parentId."*0*".$problemId;;  

                $code   = 54; 
                $bText  = "";      
                $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
                foreach ($rsB as $rs0){  
                   $lang = $rs0->messageLanguage;   
                   if ($portalType == "voice") {
                      $bText = $rs0->messageVoice; 
                   }  else {
                      $bText = $rs0->messageText;                
                   } 
                }
            break;

            case "Opt":        
                $bValue = "*118*".$idString."*".$hasParentId."*".$parentId."*0*".$problemId;   
                $code   = 55; 
                $bText  = "";      
                $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
                foreach ($rsB as $rs0){  
                   $lang = $rs0->messageLanguage;   
                   if ($portalType == "voice") {
                      $bText = $rs0->messageVoice; 
                   }  else {
                      $bText = $rs0->messageText;                
                   } 
                }

            break;
        }

                
        if ($optionNumber != "") {
            $button = ['text'=>$bText , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
               'elementOrder'=>"3", 'value'=>$bValue ];  
        } 
          
        return $button;


     } 


    //--------------------------------------------------------------------
	/* get solution option: has risk  and make output                   */
    /* value format= "*11**9999999999*1*9999999999"                     */
    public function getOptionHasriskArray($orgid,$portalType,$userid, $optionNumber, $appendB, 
         $soParentId, $bLang,$tLang, $opsource=0, $problemId=0)
     {

        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
               
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $noParent          = "0";
        $opsource          = strval($opsource);
        $parentId          = $oFunctionHelper->makePadString($optionNumber,10,"0");  
        $problemId         = $oFunctionHelper->makePadString($problemId,10,"0");  
        $lang              = $bLang; 
        $button            = "button";   
        $zeros             = "0000000000";

        $rsdata = $oSolutionOption->getOptionHasrisk($userid,$optionNumber,$soParentId);
       // $rsdata = $oSolutionOption->getOptionHasrisk($orgid,$portalType,$userid,$optionNumber,$soParentId);   

        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){              
                $soId           = $rs0->soId;
                $sosrId         = $rs0->sosrId;
                $sosrId         = $oFunctionHelper->makePadString($sosrId,10,"0"); 
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 

           //   $soIdValue      = "*11**".$soIdString."*".$hasParentId."*".$parentId.$sosrId.$opsource;
                $soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;

                $soSolution     = $rs0->soSolution;      
                $soSolutionId   = $rs0->soSolutionId;
                $lang           = $rs0->soLanguage;
                $shortText      = $rs0->soShortText;

                $solutionText[] = ['text'=>$soSolution , 'shortText'=>$shortText, 
                   'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 'elementOrder'=>"3",'value'=>$soIdValue ];                           
            }       
        } 
      
        if (!empty($solutionText) and $appendB > 1) {

            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 

            if ($appendB == 2) {  // return to Review linking options 
                $soIdValue      = "*****".$parentId."*".$noParent."*".$soIdString."*".$problemId."*3";  
            } else {
                $soIdValue      = "*111*" . $soIdString."*".$hasParentId."*".$parentId."*".$problemId."*3"; 
            } 


            $code   = 51; 
            $tBack  = "";      
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            } 
 
            $soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId."*5";
            $solutionText[] = ['text'=>$tBack , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
                    'elementOrder'=>"5",'value'=>$soIdValue."*5" ]; 

            $code   = 52;
            $tExit  = "";  
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            }
            $soIdValue      = "*110*".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId."*6";       
            $solutionText[] = ['text'=>$tExit ,'language'=>$lang , 'atttype'=>$button,'elementType'=>$button, 
                'elementOrder'=>"6",'value'=>$soIdValue."*6"];
        }
    
        return $solutionText;

     }	 

    //--------------------------------------------------------------------
	  /* pick from has risk list. Make output                             */
    /* value format= "*111*9999999999*1*9999999999"                     */
    public function getHasriskContinueExitArray($optionNumber,$bLang, $tLang)
     {

        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 

        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");

        $lang   = $bLang;
        $orgId  = NULL;
        $code1   = 51;
        $tBack  = "";     
        $tExit  = "";   
        $button = "button";
        $zeros  = "0000000000";
        //$tBack  = $oMessage->retrieveTextByCodeOrgLang($code1,$orgId,$lang);              
        $code2   = 52;
        //$tExit  = $oMessage->retrieveTextByCodeOrgLang($code2,$orgId,$lang);

        // Text overrides not found in target langauge. Use base language
        if ($tBack == "") {
            $lang = $bLang;
            $tBack  = $oMessage->retrieveTextByCodeOrgLang($code1,$orgId,$lang); 
        }
        if ($tExit == "") {
            $lang = $bLang;
            $tExit  = $oMessage->retrieveTextByCodeOrgLang($code2,$orgId,$lang);              
        }

        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*112*".$soIdString."*".$hasParentId."*".$parentId."*0*".$zeros."*5";     
        $solutionText[] = ['text'=>$tBack  , 'language'=>$lang , 'atttype'=>$button,'elementType'=>$button, 
                 'elementOrder'=>"5",'value'=>$soIdValue."*5" ];  
      
        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*110*".$soIdString."*".$hasParentId."*".$parentId."*0*".$zeros."*6"; ;       
        $solutionText[] = ['text'=>$tExit  , 'language'=>$lang , 'atttype'=>$button,'elementType'=>$button,
                 'elementOrder'=>"6",'value'=>$soIdValue."*6"];
    
        return $solutionText;

     }	

    //--------------------------------------------------------------------
    /* getLinkReturnExitArray                                           */ 
    /* Make Return Back Exit button                                     */
    /* value format= *****2222222222*p*333333333344444444445   
                     value       offset length meaning       
                     *****         0      5    value type
                     2222222222    5     10    record id from solution_option 
                     *            15      1    separator
                     p            16      1    1 has parent id; 0 no parent id
                     *            17      1    separator
                     3333333333   18     10    parent id from solution_option
                     4444444444   28     10    parent id from solution_relation 
                     5            38      1    type of option: 0  undefined
                                                               1  has risk
                                                               2  requires
                                                               3  has option
      */

    public function getLinkReturnExitArray($orgid,$portalType,$optionNumber,$midState, $lang,
              $tLang, $isBack, $inText, $opsource=0)
     {

        $oFunctionHelper   = new FunctionHelper();
        $oSolutionOption   = new SolutionOption();
        $oMessage          = new \App\Models\Message();        
        $solutionText      =  array();
        $hasParentId       = "0";                // no parent record

        $parentId     = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $parentId0    = substr($inText,5,10);
        $parentId2    = substr($inText,28,10);
        $optionType   = substr($inText,38,1);
        $problemId    = $oFunctionHelper->getProblemId($inText);
        $problemId    = $oFunctionHelper->makePadString($problemId,10,"0");
        $zeros  = "0000000000";
        $opsource = strval($opsource);  
        $button   = "button";

        $soParentId  = 0;
        $soProblemId = 0;
        $soId        = $oFunctionHelper->getSoId($inText);
        $rs          = $oSolutionOption->findById($soId);
        // Current level
        foreach($rs as $rs0) {
            $soParentId   = $rs0->soParentId;
            $soProblemId  = $rs0->soFactId;
        }
        $soId         = $oFunctionHelper->makePadString($soParentId,10,"0");
        $soId         = $soParentId;
        $soProblemId  = $oFunctionHelper->makePadString($soProblemId,10,"0");

        $zeroString     = $oFunctionHelper->makePadString(0,10,"0"); 

        if($midState == 0) {
            $soIdValue      = "*****".$parentId."*".$hasParentId."*".$zeroString."*".$opsource."*".$problemId;   
        } else {

            if ($isBack == 2) {
                $hasParentId = 1;
                $soIdValue      = "*134*".$parentId."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId; 
            } 
            if ($isBack == 1 ) {
                $hasParentId = 1; 
                $soIdValue      = "*13**".$parentId."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId; 

            } 

            if ($isBack == 4) {  // return to review requirements
                $hasParentId = 1;  
                $soIdValue      = "*117*".$zeros."*".$hasParentId."*".$parentId."*2*".$problemId;
            } 

            if ($isBack == 5) {
                $hasParentId = 0;
                $soIdValue      = "*****".$parentId."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId;  
       
            } 

            if ($isBack == 15) {
                $hasParentId = 0;
                //$soIdValue      = "*****".$parentId."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId;  
                $soIdValue      = "*****".$soId."*".$hasParentId."*".$soParentId."*".$opsource."*".$soProblemId;
            } 

            if ($isBack == 6) {  // return to review risks
                $hasParentId = 1;
                $soIdValue      = "*116*".$zeros."*".$hasParentId."*".$parentId."*1*".$problemId;
     
            } 

            if ($isBack == 7) {  // return to review options
                $hasParentId = 1;
                $soIdValue      = "*118*".$zeros."*".$hasParentId."*".$parentId."*3*".$problemId; 
            } 

            if ($isBack == 12) {  // return to review options
                $hasParentId = 1;
                $soIdValue      = "*13**".$parentId."*".$hasParentId."*".$zeros."*".$opsource."*".$problemId; 
            } 

            if ($isBack == 0) {
              //  $soIdValue      = "*119*".$parentId."*".$hasParentId."*".$zeros.$parentId2.$opsource;      
                $soIdValue      = "*119*".$parentId."*".$hasParentId."*".$zeros. "*0*".$problemId;          
            }

        }       
 

        if ($midState > 0) {
            $tBack = "";
            $code   = 51; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            }
           $solutionText[] = ['text'=>$tBack , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
                 'elementOrder'=>"5",'value'=>$soIdValue."*5" ];  
        }       

        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*110*".$soIdString."*".$hasParentId."*".$parentId; 

        $tExit = "";
        $code   = 52; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
           $lang = $rs0->messageLanguage;   
           if ($portalType == "voice") {
              $tExit = $rs0->messageVoice; 
           }  else {
              $tExit = $rs0->messageText;                
           } 
        }

        $solutionText[] = ['text'=>$tExit , 'language'=>$lang , 'atttype'=>$button,'elementType'=>$button, 
             'elementOrder'=>"6",'value'=>$soIdValue."*5"];
        
        return $solutionText;

     }  


     //--------------------------------------------------------------------
    /* getNewReturnExitArray                                                */ 
    /* Make Return Back Exit button                                         */
    /* value format= *****2222222222*p*3333333333*b*4444444444*5  
                          current      parentId     problemId 
                     value       offset length meaning       
                     *****         0      5    value type
                     2222222222    5     10    record id from solution_option 
                     *            15      1    separator
                     p            16      1    1 has parent id; 0 no parent id
                     *            17      1    separator
                     3333333333   18     10    parent id from solution_option
                     4444444444   28     10    parent id from solution_relation 
                     5            38      1    type of option: 0  undefined
                                                               1  has risk
                                                               2  requires
                                                               3  has option
      */

    public function getNewReturnExitArray($orgid,$portalType,$optionNumber,$midState, $lang,
              $tLang, $isBack, $inText, $opsource=0)
     {

        $oFunctionHelper   = new FunctionHelper();
        $oSolutionOption   = new SolutionOption();
        $oMessage          = new \App\Models\Message();        
        $solutionText      =  array();
        $hasParentId       = "0";                // no parent record

        $parentId     = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $parentId0    = substr($inText,5,10);
        $parentId2    = substr($inText,28,10);
        $optionType   = substr($inText,38,1);
        $problemId    = $oFunctionHelper->getProblemId($inText);
        $problemId    = $oFunctionHelper->makePadString($problemId,10,"0");
        $zeros       = "0000000000";
        $opsource    = strval($opsource);  
        $button      = "button";
        $soParentId  = 0;
        $soProblemId = 0;
        $sosrId      = 0;
        $soId        = $oFunctionHelper->getSoId($inText);
        $rs          = $oSolutionOption->findById($soId);
        // Current level
        foreach($rs as $rs0) {
            $soParentId   = $rs0->soParentId;
            $soProblemId  = $rs0->soFactId;
            $sosrId       = $rs0->sosrId;
        }
        //$soId         = $oFunctionHelper->makePadString($soParentId,10,"0");
        $soId         = $soParentId;
        $soProblemId  = $oFunctionHelper->makePadString($soProblemId,10,"0");

        if ($soId > 0) {  // parent Id is > 0
            $isBack = 2;   // back button at intermediate level

            // one level higher
            $rs          = $oSolutionOption->findById($soId);
            foreach($rs as $rs0) {
                $soParentId   = $rs0->soParentId;
            }
            
        } else {
            $isBack = 1;     // back biutton at top level
        }   

        $soId         = $oFunctionHelper->makePadString($soId,10,"0");
        $soParentId   = $oFunctionHelper->makePadString($soParentId,10,"0");


        if ($isBack == 1) {
            $hasParentId = 0;
            $sosrId      = $oFunctionHelper->makePadString($sosrId,10,"0");
            $soIdValue      = "*****".$sosrId."*".$hasParentId."*".$zeros."*".$opsource."*".$soProblemId;  
        } 
 

        if ($isBack == 2) {  // return to 
            $hasParentId = 1;
            $soIdValue      = "*13**".$soId."*".$hasParentId."*".$soParentId."*".$opsource."*".$soProblemId; 
        } 
      

        if ($midState > 0) {
            $tBack = "";
            $code   = 51; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            }
           $solutionText[] = ['text'=>$tBack , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
                 'elementOrder'=>"5",'value'=>$soIdValue."*5" ];  
        }
         
        $soIdValue      = "*110**".$zeros."*".$hasParentId."*".$soParentId."*".$opsource."*".$soProblemId; 

        $tExit = "";
        $code   = 52; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
           $lang = $rs0->messageLanguage;   
           if ($portalType == "voice") {
              $tExit = $rs0->messageVoice; 
           }  else {
              $tExit = $rs0->messageText;                
           } 
        }


        $solutionText[] = ['text'=>$tExit , 'language'=>$lang , 'atttype'=>$button,'elementType'=>$button, 
             'elementOrder'=>"6",'value'=>$soIdValue."*6"];
        
        return $solutionText;

     }  


/*--------------------------------------------------------------------*/
public function getBackExitArray($orgid,$portalType,$lang,$tLang, $hasExit=0,$problemId=0)
     {

        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message();        
        $solutionText      =  array();
        $hasParentId       = "0";                // no parent record
        $zeros  = "0000000000";
        $parentId     = $zeros;
        $problemId    = $oFunctionHelper->makePadString($problemId,10,"0");
        $opsource = "0";  
        $soIdValue      = "*119*".$parentId."*".$hasParentId."*".$zeros."*".$opsource."*".$problemId;

        $button = "button";
        $tBack = "Back";
        $code   = 51; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
            $lang = $rs0->messageLanguage;   
            if ($portalType == "voice") {
                $tBack = $rs0->messageVoice; 
            }  else {
                $tBack = $rs0->messageText;                
            } 
        }
        $solutionText[] = ['text'=>$tBack , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
             'elementOrder'=>"5",'value'=>$soIdValue,"*5" ];  


        if ($hasExit == 1) {     
          $soIdValue      = "*110*".$zeros."*".$hasParentId."*".$zeros; 

          $tExit = "Exit";
          $code   = 52; 
          $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
          foreach ($rsB as $rs0){  
              $lang = $rs0->messageLanguage;   
              if ($portalType == "voice") {
                 $tExit = $rs0->messageVoice; 
              }  else {
                 $tExit = $rs0->messageText;                
              } 
          }
          $solutionText[] = ['text'=>$tExit , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
                'elementOrder'=>"6",'value'=>$soIdValue."*6"];

        }

        return $solutionText;

     }    

/*--------------------------------------------------------------------*/
public function getBackExitProblemArray($orgid,$portalType,$lang,$tLang, $hasExit=0,$problemId=0)
     {

        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message();        
        $solutionText      =  array();
        $hasParentId       = "0";                // no parent record
        $zeros  = "0000000000";
        $parentId     = $zeros;
        $problemId    = $oFunctionHelper->makePadString($problemId,10,"0");
        $opsource = "0";  

        //$soIdValue      = "*807*".$parentId."*".$hasParentId."*".$zeros."*".$opsource."*".$problemId;
        $soIdValue      = "*119*".$parentId."*".$hasParentId."*".$zeros."*".$opsource."*".$zeros;

        $button = "button";
        $tBack = "Back";
        $code   = 51; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
            $lang = $rs0->messageLanguage;   
            if ($portalType == "voice") {
                $tBack = $rs0->messageVoice; 
            }  else {
                $tBack = $rs0->messageText;                
            } 
        }
        $solutionText[] = ['text'=>$tBack , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
              'elementOrder'=>"5",'value'=>$soIdValue."*5" ];  


        if ($hasExit == 1) {     
          $soIdValue      = "*110*".$zeros."*".$hasParentId."*".$zeros; 

          $tExit = "Exit";
          $code   = 52; 
          $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
          foreach ($rsB as $rs0){  
              $lang = $rs0->messageLanguage;   
              if ($portalType == "voice") {
                 $tExit = $rs0->messageVoice; 
              }  else {
                 $tExit = $rs0->messageText;                
              } 
          }
          $solutionText[] = ['text'=>$tExit , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
               'elementOrder'=>"6",'value'=>$soIdValue."*6"];

        }

        return $solutionText;

     }       


    //--------------------------------------------------------------------
    /* Make Return / exit button from  has risk list.                   */
    /* value format= "*****9999999999*0*0000000000"                     */
    public function getLinkExitArray2($orgid,$portalType,$optionNumber, $midState, $lang, $tLang,$isBack=0,$problemId=0 )
     {

        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message();  
        $solutionText      =  array();
        $hasParentId       = "0";                // no parent record
        $parentId          = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $problemId         = $oFunctionHelper->makePadString($problemId,10,"0");
        $button            = "button"; 

        $zeroString     = $oFunctionHelper->makePadString(0,10,"0"); 
        if($midState == 0) {
            $soIdValue      = "*****".$parentId."*".$hasParentId."*".$zeroString."*0*".$problemId;   
        } else {
            if ($isBack == 1) {
                $soIdValue      = "*13**".$parentId."*".$hasParentId."*".$zeroString."*0*".$problemId; 
            } else {
                $soIdValue      = "*119*".$parentId."*".$hasParentId."*".$zeroString."*0*".$problemId;                
        
            }

        }           
       
        if ($midState > 0) {
           if ($isBack == 2) {
               $soIdValue      = "*119*".$parentId."*".$hasParentId."*".$zeroString."*0*".$problemId; 
           }

            $tBack = "";
            $code   = 51; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            }

           $solutionText[] = ['text'=>$tBack, 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
                  'elementOrder'=>"5", 'value'=>$soIdValue."*5" ];         
        }

        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*110*".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;  

        $tExit = "";
        $code   = 52; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
           $lang = $rs0->messageLanguage;   
           if ($portalType == "voice") {
              $tExit = $rs0->messageVoice; 
           }  else {
              $tExit = $rs0->messageText;                
           } 
        }

        $solutionText[] = ['text'=>$tExit ,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
             'elementOrder'=>"6",'value'=>$soIdValue."*6"];

        return $solutionText;

     }

    //--------------------------------------------------------------------
	/* get solution option: requires and make output                    */
    /* value format= "*12**9999999999*1*9999999999"                     */
    public function getOptionRequiresArray($orgid,$portalType,$userid,$optionNumber,
         $appendB,$soParentId,$bLang,$tLang,$problemId=0)
    {

        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message();                  
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $noParent          = 0;
        $parentId          = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $problemId         = $oFunctionHelper->makePadString($problemId,10,"0");
        $lang              = $bLang;   
        $button            = "button";    

        if ($soParentId == 0) {
            $rsdata = $oSolutionOption->getOptionRequires($userid,$optionNumber);
        } else { 
            $rsdata = $oSolutionOption->getOptionRequiresParent($userid, $soParentId);
        }

        foreach ($rsdata as $rs0){              
                $soId           = $rs0->soId;
                $sosrId         = $rs0->sosrId;
                $shortText      = $rs0->soShortText;
                $sosrId         = $oFunctionHelper->makePadString($sosrId,10,"0");
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 

              //$soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId.$sosrId;
                $soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;


                $soSolution     = $rs0->soSolution;  

                $solutionText[] = ['text'=>$soSolution , 'shortText'=>$shortText,'language'=>$lang, 
                   'atttype'=>$button ,'elementType'=>$button, 'elementOrder'=>"3", 'value'=>$soIdValue ];                                 
        } 


        if (!empty($solutionText) and $appendB > 0) {
            // Yes   means Continue
            // No    means Exit
            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 

            if ($appendB == 2) {  // return to Review linking options 
                $soIdValue      = "*****" . $parentId."*".$noParent."*".$soIdString."*0*".$problemId; 
            } else {
                $soIdValue      = "*121*".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;
            }  

            $tBack = "";
            $code   = 51; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            }

            $solutionText[] = ['text'=>$tBack ,'language'=>$lang , 'atttype'=>$button,'elementType'=>$button,
                    'elementOrder'=>"5",'value'=>$soIdValue."*5" ];  
            $soIdValue      = "*120*".$soIdString."*".$hasParentId."*".$parentId; 

            $tExit = "";
            $code   = 52; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tExit = $rs0->messageVoice; 
               }  else {
                  $tExit = $rs0->messageText;                
               } 
            }

            $solutionText[] = ['text'=>$tExit ,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
                   'elementOrder'=>"6",'value'=>$soIdValue."*6"];
        }
    
        return $solutionText;

    }	 

    //--------------------------------------------------------------------
	/* pick from requires list. Make output                             */
    /* value format= "*121*9999999999*1*9999999999"                     */
    public function getRequiresContinueExitArray($orgid,$portalType,$optionNumber, $hasReturn=0, 
      $lang, $tLan, $problemId=0)
    {

        $oFunctionHelper   = new FunctionHelper();
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $noParent          = 0;
        $parentId          = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $problemId         = $oFunctionHelper->makePadString($problemId,10,"0");
        $oMessage          = new \App\Models\Message();    
        $button            = "button";    

        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        if ($hasReturn == 2) {
           $soIdValue      = "*****".$parentId."*".$noParent."*".$soIdString."*0*".$problemId;
        } else {
           $soIdValue      = "*122*".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;           
        } 

        $tBack = "";
        $code   = 51; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
           $lang = $rs0->messageLanguage;   
           if ($portalType == "voice") {
              $tBack = $rs0->messageVoice; 
           }  else {
              $tBack = $rs0->messageText;                
           } 
        }        
        $solutionText[] = ['text'=>$tBack,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
               'elementOrder'=>"5",'value'=>$soIdValue."*5" ];  
     
        $soIdValue      = "*120*".$soIdString."*".$hasParentId."*".$parentId;   

        $tExit = "";
        $code   = 52; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
           $lang = $rs0->messageLanguage;   
           if ($portalType == "voice") {
              $tExit = $rs0->messageVoice; 
           }  else {
              $tExit = $rs0->messageText;                
           } 
        }

        $solutionText[] = ['text'=>$tExit,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
             'elementOrder'=>"6",'value'=>$soIdValue."*6"];
    
        return $solutionText;
    }		 
	 
    //--------------------------------------------------------------------
	  /* get solution option: has option and make output                  */
    /* value format= "*13**9999999999*1*9999999999"                     */
    public function getOptionHasoptionArray($orgid,$portalType,$userid, $optionNumber, $appendB, 
         $soParentId,$bLang,$tLang,$problemId=0)

    {

        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();  
        $oMessage          = new \App\Models\Message();       
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $noParent          = 0;
        $parentId          = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $problemId         = $oFunctionHelper->makePadString($problemId,10,"0");
        $lang              = $bLang;  
        $button            = "button";

        $oSolutionOption->deleteBlank($userid);
        if ($soParentId == 0) {
            $rsdata = $oSolutionOption->getOptionHasoption($userid,$optionNumber);    //ORIGINAL
            //$rsdata = $oSolutionOption->getOptionHasoption2($userid,$optionNumber);  // PATCH

        } else { 
            $rsdata = $oSolutionOption->getOptionHasoptionParent($userid, $soParentId);

        }

        foreach ($rsdata as $rs0){              
                $soId           = $rs0['soId'];
                $sosrId         = $rs0['sosrId'];
                $shortText      = $rs0['soShortText'];
                $sosrId         = $oFunctionHelper->makePadString($sosrId,10,"0"); 
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 

             // $soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId.$sosrId."*0*".$problemId;
                $soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;
                  
                $soSolution     = $rs0['soSolution'];   
                $soSolutionId   = $rs0['soSolutionId'];                    
                $lang           = $rs0->soLanguage;

                $solutionText[] = ['text'=>$soSolution , 'shortText'=>$shortText,'language'=>$lang, 
                   'atttype'=>$button,'elementType'=>$button, 'elementOrder'=>"3", 'value'=>$soIdValue."*3" ];                               
        }       
 
        if (!empty($solutionText) and $appendB > 0 ) {

            $code   = 51;             
            $tBack  = $oMessage->retrieveMessage($code,$portalType,$orgid,$lang,$tLang); 
            $code   = 52;
            $tExit  = $oMessage->retrieveMessage($code,$portalType,$orgid,$lang,$tLang); 


            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            if ($appendB == 2) {  // return to Review linking options 
                $soIdValue      = "*****" . $parentId."*".$noParent."*".$soIdString; 
            } else {
                $soIdValue      = "*131*".$soIdString."*".$hasParentId."*".$parentId;
            }         
            $solutionText[] = ['text'=>$tBack ,'language'=>$tLang, 'atttype'=>$button,'elementType'=>$button, 
                 'elementOrder'=>"5",'value'=>$soIdValue."*5" ];  
      
            $soIdValue      = "*130*".$soIdString."*".$hasParentId."*".$parentId;       
            $solutionText[] = ['text'=>$tExit ,'language'=>$tLang, 'atttype'=>$button,'elementType'=>$button, 
                'elementOrder'=>"6",'value'=>$soIdValue."*6"];
        }
    
        return $solutionText;

    }		 


    //--------------------------------------------------------------------
    /* get solution option: has option and make output                  */
    /* value format= "*13**9999999999*1*9999999999"                     */
    public function getOptionHasoptionArray2($orgid, $portalType,$userid, $optionNumber, $appendB,
         $soParentId,$lang,$tLang,$problemId,$hasLiveAgent,$liveKrId)
    {

        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper(); 
        $oMessage          = new \App\Models\Message();         
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $noParent          = 0;
        $parentId          = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $problemId         = $oFunctionHelper->makePadString($problemId,10,"0");
        $button            = "button";

        $oSolutionOption->deleteBlank($userid);
        if ($soParentId == 0) {
            $rsdata = $oSolutionOption->getOptionHasoption($userid,$optionNumber,$noParent);    //ORIGINAL
            //$rsdata = $oSolutionOption->getOptionHasoption2($userid,$optionNumber);  // PATCH
        } else { 
            $rsdata = $oSolutionOption->getOptionHasoptionParent($userid, $soParentId);
        }

        foreach ($rsdata as $rs0){              
                $soId           = $rs0['soId'];
                $sosrId         = $rs0['sosrId'];
                $shortText      = $rs0['soShortText'];
                $sosrId         = $oFunctionHelper->makePadString($sosrId,10,"0"); 
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 
                //$soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId.$sosrId;
                $soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;
             //                   "*131*".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;

                $soSolution     = $rs0['soSolution'];   
                $soSolutionId   = $rs0['soSolutionId'];                             

                $solutionText[] = ['text'=>$soSolution , 'shortText'=>$shortText,'language'=>$lang,
                      'atttype'=>$button ,'elementType'=>$button, 'elementOrder'=>"3",'value'=>$soIdValue."*3" ];                                
        }       
  

        if ($hasLiveAgent == 1) {
             $transferText = $this->getTransferButton($orgid,$portalType, $lang, $tLang, $liveKrId);
             $solutionText[] = $transferText;
        }


        if (!empty($solutionText) and $appendB > 0 ) {

            $code   = 51;             
            $tBack  = $oMessage->retrieveMessage($code,$portalType,$orgid,$lang,$tLang); 
            $code   = 52;
            $tExit  = $oMessage->retrieveMessage($code,$portalType,$orgid,$lang,$tLang);           

            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            if ($appendB == 2) {  // return to Review linking options 
                $soIdValue      = "*****" . $parentId."*".$noParent."*".$soIdString."*0*".$problemId; 
            } else {
                $soIdValue      = "*131*".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;
            }  

            $tBack = "";
            $code   = 51; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            }

            $solutionText[] = ['text'=>$tBack,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
                 'elementOrder'=>"5", 'value'=>$soIdValue."*5" ];  
      
            $soIdValue      = "*130*".$soIdString."*".$hasParentId."*".$parentId; 

            $tExit = "";
            $code   = 52; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tExit = $rs0->messageVoice; 
               }  else {
                  $tExit = $rs0->messageText;                
               } 
            }
            $solutionText[] = ['text'=>$tExit,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
                'elementOrder'=>"6",'value'=>$soIdValue."*6"];
        }
    
        return $solutionText;
    }     


    //--------------------------------------------------------------------
    public function getNewOptiontext($optionText,$RPAhandoffMessage, $lang)
    /*   split input text in n dimensional array.
     *   n is number of single statements
     *   input    array  $optionText 
     *   output   array  $newOptionText              
     */
     {
        $wText = array();
        $count = 0;
        $waMessage = ['text'=>$RPAhandoffMessage , 'language'=>$lang , 
                    'attname'=>"RPA message", 'atttype'=>"text", 'elementType'=>"comment",'elementOrder'=>'1', 
                     'value'=>"*****0000000000*0*0000000000*0*0000000000*4" ];

        $solCount = sizeof($optionText);
    
        for($i=0;$i<$solCount;$i++) {

            if (isset($optionText[$i]['attname']) and isset($optionText[$i]['atttype'])   ) {
                $wText[] = $optionText[$i];           
            } else {
                if ($count == 0) {
                    $count++;
                    $wText[] = $waMessage;
                    $wText[] = $optionText[$i];
                } else {
                    $wText[] = $optionText[$i];                
                }
            }

        }

        return $wText;
     }


    //--------------------------------------------------------------------
    /* get get slide bar array                                           */
    /* use linking term "has value rating KR"                            */
    public function getSlideBarArray1($userid,$personaId,$relationId,$isLinked,$orgId,$tLang,$bLang)
     {

        $oSolutionFact     = new SolutionFact();
        $oRelationLink     = new RelationLink();
        $oRTFilter         = new RelationTypeFilter();
        $oPR               = new PersonalityRelation();
        $oPRV              = new PersonalityRelationValue();
        $oFunction         = new FunctionHelper();
        $oTerm             = new Term();
        $slideText   =  array();
        $step        = 10;  // term "has value rating KR"
        $KR1 = 0;
        $KR2 = 0;
        $sbCount = 0;

        if ($isLinked == 1) {    // a linked problem
            $KR1 = $relationId;
        } else {
           // Get relationId that has negative ratings from solutin_fact       
           $rs = $oSolutionFact->getNegativeRatingByUser($userid);
           foreach ($rs as $rs0){              
              $KR1   = $rs0->sfRelationId;                                
           }
        }

        // termid for step 10
        $linkId = $oRTFilter->retrieveByStep($step);
 
        // find KR2 from relation_link: KR1, linkId, KR2
        $KR2 = $oRelationLink->retrieveRightRelationId($KR1,$linkId);


        // KR2 found
        if ($KR2 > 0) {
           // personalityRelationId
           $PRId = $oPR->retrievePersonalityRelationId($personaId,$personaId, $KR2);

           $rs = $oPRV->getByPersonalityRelation($PRId)  ;
           foreach($rs as $rs0) {
             $valueId     = $rs0->personRelationTermId;  
             $scalarValue = $rs0->scalarValue;
             $nameId      = $rs0->scalarValue;
             //$termName   = $oTerm->retrieveTermName($valueId);  
             $aName       = $oTerm->retrieveMultiTermName($orgId,$valueId,$tLang,$bLang);
             $termName    = $aName['termName'];
             $language    = $aName['language'];
             $sbCount++;
             $slideText[] = [
                  'name'=> $termName, 
                  'nameId'=> $valueId, 
                  'language'=> $language,
                  'value1'=> $scalarValue,
                  'lowlimit'=> "lowlimit", 
                  'value2'=> 0,
                  'uplimit'=> "uplimit",
                  'value3'=> 10
                           ];

           }
        }

        if ($sbCount == 0) {
           $slideText = 0;
        } else {
           $sortField = "name";
           $slideText = $oFunction->sortArray($slideText, $sortField, $reverse=false);
        }

        return $slideText;
     }     

    //--------------------------------------------------------------------
    /* get get slide bar array                                           */
    /* use linking term "has value rating KR"                            */
    /* pcick a problem form multiple problems                            */
    public function getSlideBarArray12($userid,$personaId,$relationId,$isLinked,$orgId,$tLang,$bLang,$pickProblemId)
     {

        $oSolutionFact     = new SolutionFact();
        $oRelationLink     = new RelationLink();
        $oRTFilter         = new RelationTypeFilter();
        $oPR               = new PersonalityRelation();
        $oPRV              = new PersonalityRelationValue();
        $oFunction         = new FunctionHelper();
        $oTerm             = new Term();
        $slideText   =  array();
        $step        = 10;  // term "has value rating KR"
        $KR1 = 0;
        $KR2 = 0;
        $sbCount = 0;

        // Get relationId that has negative ratings from solutin_fact       
        $rs = $oSolutionFact->getNegativeRatingByUser($userid, $pickProblemId);
        foreach ($rs as $rs0){              
              $KR1   = $rs0->sfRelationId;                                
        }

        // termid for step 10
        $linkId = $oRTFilter->retrieveByStep($step);
 
        // find KR2 from relation_link: KR1, linkId, KR2
        $KR2 = $oRelationLink->retrieveRightRelationId($KR1,$linkId);


        // KR2 found
        if ($KR2 > 0) {
           // personalityRelationId
           $PRId = $oPR->retrievePersonalityRelationId($personaId,$personaId, $KR2);

           $rs = $oPRV->getByPersonalityRelation($PRId)  ;
           foreach($rs as $rs0) {
             $valueId     = $rs0->personRelationTermId;  
             $scalarValue = $rs0->scalarValue;
             $nameId      = $rs0->scalarValue;
             //$termName   = $oTerm->retrieveTermName($valueId);  
             $aName       = $oTerm->retrieveMultiTermName($orgId,$valueId,$tLang,$bLang);
             $termName    = $aName['termName'];
             $language    = $aName['language'];
             $sbCount++;
             $slideText[] = [
                  'name'=> $termName, 
                  'nameId'=> $valueId, 
                  'language'=> $language,
                  'value1'=> $scalarValue,
                  'lowlimit'=> "lowlimit", 
                  'value2'=> 0,
                  'uplimit'=> "uplimit",
                  'value3'=> 10
                           ];

           }
        }

        if ($sbCount == 0) {
           $slideText = 0;
        } else {
           $sortField = "name";
           $slideText = $oFunction->sortArray($slideText, $sortField, $reverse=false);
        }

        return $slideText;
     }     


    //--------------------------------------------------------------------
    /* get get slide bar array  for linked problem                                         */
    /* use linking term "has value rating KR"                            */
    public function getSlideBarArray1LinkedProblem($userid,$personaId,$relationId)
     {

        //$oSolutionFact     = new SolutionFact();
        $oRelationLink     = new RelationLink();
        $oRTFilter         = new RelationTypeFilter();
        $oPR               = new PersonalityRelation();
        $oPRV              = new PersonalityRelationValue();
        $oTerm             = new Term();
        $slideText   =  array();
        $step        = 10;  // term "has value rating KR"
        $KR1 = 0;
        $KR2 = 0;
        $sbCount = 0;

        $KR1 = $relationId;

        // termid for step 10
        $linkId = $oRTFilter->retrieveByStep($step);
 
        // find KR2 from relation_link: KR1, linkId, KR2
        $KR2 = $oRelationLink->retrieveRightRelationId($KR1,$linkId);

        // KR2 found
        if ($KR2 > 0) {
           // personalityRelationId
           $PRId = $oPR->retrievePersonalityRelationId($personaId, $personaId,$KR2);

           $rs = $oPRV->getByPersonalityRelation($PRId)  ;
           foreach($rs as $rs0) {
             $valueId     = $rs0->personRelationTermId;  
             $scalarValue = $rs0->scalarValue;
             $valueName   = $oTerm->retrieveTermName($valueId);  
             $sbCount++;
             $slideText[] = [
                  'name'=> $valueName, 
                  'nameId'=> $valueId, 
                  'value1'=> $scalarValue,
                  'lowlimit'=> "lowlimit", 'value2'=> 0,
                  'uplimit'=> "uplimit", 'value3'=> 10
                           ];

           }
        }


        if ($sbCount == 0) {
           $slideText = 0;
        }


        return $slideText;
     }  

    //--------------------------------------------------------------------
    /* get get slide bar array                                           */
    /* value format= "*300**9999999999*1*9999999999"                     */
    public function getSlideBarArray2($userid,$portalType,$orgid,$lang,$tLang)
     {

        $oMessage   = new \App\Models\Message(); 
        $buttonText   =  array();   
        $button      = "button"; 

        $tSkip = "";
        $code   = 60; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
           $lang = $rs0->messageLanguage;   
           if ($portalType == "voice") {
              $tSkip = $rs0->messageVoice; 
           }  else {
              $tSkip = $rs0->messageText;                
           } 
        }
        $buttonText[] = ['text'=>$tSkip, 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
            'elementOrder'=>"3", 'value'=>"*301*0000000009*0*0000000000*0*0000000000*3"];
  
        $tSave = "";
        $code   = 61; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
           $lang = $rs0->messageLanguage;   
           if ($portalType == "voice") {
              $tSave = $rs0->messageVoice; 
           }  else {
              $tSave = $rs0->messageText;                
           } 
        }
        $buttonText[] = ['text'=>$tSave, 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
             'elementOrder'=>"3", 'value'=>"*302*0000000000*1*0000000009*0*0000000000*3"];

        return $buttonText;
     } 


    //--------------------------------------------------------------------
    /* get get slide bar array                                           */
    /* value format= "*300**9999999999*1*9999999999"                     */
    public function getSlideBarArray22($userid,$portalType,$orgid,$lang,$tLang,$pickProblemId)
     {

        $oMessage   = new \App\Models\Message(); 
        $buttonText  =  array();   
        $button      = "button"; 
        $oFunction   = new FunctionHelper();
        $problemId   = $oFunction->makePadString($pickProblemId,10,"0");

        $tSkip = "";
        $code   = 60; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
           $lang = $rs0->messageLanguage;   
           if ($portalType == "voice") {
              $tSkip = $rs0->messageVoice; 
           }  else {
              $tSkip = $rs0->messageText;                
           } 
        }

        $val = "*305*".$problemId."*0*0000000000*0*0000000000*3"  ;

        $buttonText[] = ['text'=>$tSkip, 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
            'elementOrder'=>"3", 'value'=>$val];

        $tSave = "";
        $code   = 61; 
        $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
           $lang = $rs0->messageLanguage;   
           if ($portalType == "voice") {
              $tSave = $rs0->messageVoice; 
           }  else {
              $tSave = $rs0->messageText;                
           } 
        }

        $val = "*306*".$problemId."*0*0000000000*0*0000000000*3"  ;
        $buttonText[] = ['text'=>$tSave, 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
             'elementOrder'=>"3", 'value'=>$val];

        return $buttonText;
     } 


    //--------------------------------------------------------------------
    /* get srId from srsfId                                              */

    public function retrieveSrId($srsfId)
     {
       $id = $srsfId;

       return $id;
     }


    //--------------------------------------------------------------------
    /* get solution relation extended data array                         */

    public function getSolFactExtDataArray($userid,$orgid,$sfId,$bLang,$tLang,$portalType)
     {

        $oSolutionFact     = new SolutionFact();
        $oSolFactExdata    = new SolutionFactExdata();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
        $solutionText      =  array();
        $hasParentId       = "0";                // there is parent record
        $parentId          = $oFunctionHelper->makePadString($sfId,10,"0");
        $sfIdValue        = "*****".$parentId."*0*0000000000*0*0000000000*1";
        $button           = "button";
        $comment          = "comment";
        $lang             = $bLang;


        // get solution relation extended data
        $rsdata = $oSolFactExdata->getChildrenData($sfId); 
        foreach ($rsdata as $rs0){   
            $sfedId        = $rs0['sfedId'];  
            $sfSolution    = $rs0['sfedValueString'];   
            $sfChatIntro   = $rs0['sfedChatIntro'];   
            $sfAttTypeName = $rs0['sfedAttributeTypeName'];
            $attName       = $rs0['sfedAttributeName'];   
            $lang          = $rs0['sfLanguage'];          
            $sfAttTypeName = strtolower($sfAttTypeName);   

            if ($sfAttTypeName == "url") {

                if ($sfChatIntro <> "") {
                    $sfAttTypeName = "text";
                    $solutionText[] = ['text'=>$sfChatIntro ,'language'=>$lang, 'attname'=>$attName, 
                    'atttype'=>$sfAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$sfIdValue  ];                    
                } else {
                    if ($sfChatIntro == "" and $portalType == "voice") {
                       $solutionText[] = ['text'=>"" ,'language'=>$lang,'attname'=>$attName, 
                       'atttype'=>$sfAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1", 'value'=>$sfIdValue  ]; 
                    }
                }

                $solutionText[] = ['url'=>$sfSolution, 'language'=>$lang,'attname'=>$attName,
                  'atttype'=>$sfAttTypeName, 'elementType'=>$button, 'elementOrder'=>"1",'value'=>$sfIdValue  ]; 
            } else {
            
                $sfSolution = $sfChatIntro ." ". $sfSolution;          
                $solutionText[] = ['text'=>$sfSolution ,'language'=>$lang, 'attname'=>$attName,
                  'atttype'=>$sfAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$sfIdValue  ];  

            }                    
        }           
        
        return $solutionText;

     }     


    //--------------------------------------------------------------------
    /* get solution relation extended data array                         */

    public function getSolRelExtDataArray($userid,$orgid,$EDoptionNumber,$optionNumber,
         $inText,$hasReturn, $bLang, $tLang, $portalType )
     {

        $oSolutionRelation= new SolutionRelation();
        $oSolRelExdata    = new SolutionRelationExdata();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
        $solutionText      =  array();
        $hasParentId       = "0";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $srIdValue        = $inText;
        $button           = "button";
        $comment          = "comment";
        $lang             = $bLang;

       //	$srAttText = "text";

        // get solution relation extended data
        $rsdata = $oSolRelExdata->getChildrenData($optionNumber); 
        foreach ($rsdata as $rs0){   
            $sredId        = $rs0['sredId'];  
            $srSolution    = $rs0['sredValueString'];   
            $srChatIntro   = $rs0['sredChatIntro'];   
            $srAttTypeName = $rs0['sredAttributeTypeName'];
            $attName       = $rs0['sredAttributeName'];   
            $lang          = $rs0['srLanguage'];          
            $srAttTypeName = strtolower($srAttTypeName);   

            if ($srAttTypeName == "url") {

                if ($srChatIntro <> "") {
                	$srAttTypeName = "text";
                    $solutionText[] = ['text'=>$srChatIntro ,'language'=>$lang, 'attname'=>$attName, 
                    'atttype'=>$srAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$srIdValue  ];                    
                } else {
                    if ($srChatIntro == "" and $portalType == "voice") {
                       $solutionText[] = ['text'=>"" ,'language'=>$lang,'attname'=>$attName, 
                       'atttype'=>$srAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1", 'value'=>$srIdValue  ]; 
                    }
                }

                $solutionText[] = ['url'=>$srSolution, 'language'=>$lang,'attname'=>$attName,
                  'atttype'=>$srAttTypeName, 'elementType'=>$button, 'elementOrder'=>"1",'value'=>$srIdValue  ]; 
            } else {
            
                $srSolution = $srChatIntro ." ". $srSolution;          
              //  $solutionText[] = ['text'=>$srSolution ,'language'=>$lang, 'attname'=>$attName,
              //    'atttype'=>$srAttText, 'value'=>$srIdValue  ];   

                $solutionText[] = ['text'=>$srSolution ,'language'=>$lang, 'attname'=>$attName,
                  'atttype'=>$srAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$srIdValue  ];  

            }                    
        }           
        

        $code   = 51;             
        $tBack  = $oMessage->retrieveMessage($code,$portalType,$orgid,$lang,$tLang); 
        $code   = 52;
        $tExit  = $oMessage->retrieveMessage($code,$portalType,$orgid,$lang,$tLang); 


        if (!empty($solutionText) and $hasReturn == 1) {

            $srIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $srIdValue      = "*131*".$srIdString."*".$hasParentId."*".$parentId; 

            $tBack = "";
            $code   = 51; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tExit = $rs0->messageVoice; 
               }  else {
                  $tExit = $rs0->messageText;                
               } 
            }
            $solutionText[] = ['text'=>$tBack, 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
                 'elementOrder'=>"5",'value'=>$srIdValue."*5" ];  
            
            $srIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $srIdValue      = "*130*".$srIdString."*".$hasParentId."*".$parentId;      


            $tExit = "";
            $code   = 52; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tExit = $rs0->messageVoice; 
               }  else {
                  $tExit = $rs0->messageText;                
               } 
            }
            $solutionText[] = ['text'=>$tExit,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
                 'elementOrder'=>"6",'value'=>$srIdValue."*6"];
        }
        
        return $solutionText;

     }


    //--------------------------------------------------------------------
    /* get solution relation extended data array for                     */
    /*   solved problem from linked option                               */

    public function getProblemRelExtDataArray($userid,$orgid,$optionNumber,
         $inText, $bLang, $tLang, $portalType )
     {

        $oSolutionRelation= new SolutionRelation();
        $oSolRelExdata    = new SolutionRelationExdata();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
        $solutionText      =  array();
        $hasParentId       = "0";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $srIdValue        = $inText;
        $button           = "button";
        $comment          = "comment";
        $lang             = $bLang;

       // $srAttText = "text";

        // get solution relation extended data
        $rsdata = $oSolRelExdata->getChildrenData($optionNumber); 
        foreach ($rsdata as $rs0){   
            $sredId        = $rs0['sredId'];  
            $srSolution    = $rs0['sredValueString'];   
            $srChatIntro   = $rs0['sredChatIntro'];   
            $srAttTypeName = $rs0['sredAttributeTypeName'];
            $attName       = $rs0['sredAttributeName'];   
            $lang          = $rs0['srLanguage'];          
            $srAttTypeName = strtolower($srAttTypeName);   

            if ($srAttTypeName == "url") {

                if ($srChatIntro <> "") {
                  $srAttTypeName = "text";
                    $solutionText[] = ['text'=>$srChatIntro ,'language'=>$lang, 'attname'=>$attName, 
                    'atttype'=>$srAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$srIdValue  ];                    
                } else {
                    if ($srChatIntro == "" and $portalType == "voice") {
                       $solutionText[] = ['text'=>"" ,'language'=>$lang,'attname'=>$attName, 
                       'atttype'=>$srAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1", 'value'=>$srIdValue  ]; 
                    }
                }

                $solutionText[] = ['url'=>$srSolution, 'language'=>$lang,'attname'=>$attName,
                  'atttype'=>$srAttTypeName, 'elementType'=>$button, 'elementOrder'=>"1",'value'=>$srIdValue  ]; 
            } else {
            
                $srSolution = $srChatIntro ." ". $srSolution;            
                $solutionText[] = ['text'=>$srSolution ,'language'=>$lang, 'attname'=>$attName,
                  'atttype'=>$srAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$srIdValue  ];  

            }                    
        }           
        
        return $solutionText;

     }     




    //--------------------------------------------------------------------
    /* get solution OPTION extended data array                          */
    public function getSolOptExtDataArray($userid,$orgid,$EDoptionNumber,$optionNumber,
       $inText,$hasReturn, $bLang, $tLang, $portalType)
     {

        $oSolutionOption   = new SolutionOption();
        $oSolOptExdata     = new SolutionOptionExdata();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message();         
        $solutionText      =  array();
        $hasParentId       = "0";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $soIdValue        = $inText;
       	//$srAttText = "text";
        $solutionText     = array();
        $button           = "button";
        $comment          = "comment";
        $lang             = $bLang;

        // get solution option extended data
        $rsdata = $oSolOptExdata->getChildrenData($EDoptionNumber); 
        foreach ($rsdata as $rs0){   
            $soSolution = $rs0['soedValueString'];   
            $soChatIntro = $rs0['soedChatIntro'];   
            $attName     = $rs0['soedAttributeName'];            
            $soAttTypeName = $rs0['soedAttributeTypeName'];
            $lang          = $rs0['soLanguage'];
            $soAttTypeName = strtolower($soAttTypeName);   

            if ($soAttTypeName == "url") {
                if ($soChatIntro != "") {
                   $srAttTypeName = "text";
                   $solutionText[] = ['text'=>$soChatIntro ,'language'=>$lang ,'attname'=>$attName,
                     'atttype'=>$soAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$soIdValue  ]; 
                } else {
                    if ($soChatIntro == "" and $portalType == "voice") {
                       $solutionText[] = ['text'=>"" ,'language'=>$lang,'attname'=>$attName,
                        'atttype'=>$soAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$soIdValue  ]; 
                    }
                }

                $solutionText[] = ['url'=>$soSolution ,'language'=>$lang ,'attname'=>$attName, 
                  'atttype'=>$soAttTypeName, 'elementType'=>$button, 'elementOrder'=>"1",'value'=>$soIdValue  ]; 

            } else {
            	
                $soSolution = $soChatIntro ." " . $soSolution;
              //  $solutionText[] = ['text'=>$soSolution ,'language'=>$lang,'attname'=>$attName, 
              //   'atttype'=>$soAttText, 'value'=>$soIdValue  ];     

                $solutionText[] = ['text'=>$soSolution ,'language'=>$lang,'attname'=>$attName, 
                 'atttype'=>$soAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$soIdValue  ];    

            }                    
        }           
        
        $code   = 51;
        $tBack  = $oMessage->retrieveMessage($code, $portalType, $orgid,$lang,$tLang);              
        $code   = 52;
        $tExit  = $oMessage->retrieveMessage($code, $portalType, $orgid,$lang,$tLang);    

        if (!empty($solutionText) and $hasReturn ==1) {

            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*131*".$soIdString."*".$hasParentId."*".$parentId;  

            $tBack = "";
            $code   = 51; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            }

            $solutionText[] = ['text'=>$tBack,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
                 'elementOrder'=>"5",'value'=>$soIdValue."*5" ];  
            
            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*130*".$soIdString."*".$hasParentId."*".$parentId;   

            $tExit = "";
            $code   = 52; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tExit = $rs0->messageVoice; 
               }  else {
                  $tExit = $rs0->messageText;                
               } 
            }
            $solutionText[] = ['text'=>$tExit,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
                 'elementOrder'=>"6",'value'=>$soIdValue."*6"];
        }
        
        return $solutionText;

     }


   //--------------------------------------------------------------------
    /* get Return Exit Button to solution Screen                       */
    public function getREButtonSolutionScreen($orgid,$portalType,$optionNumber,$lang, $tLang,$problemId=0)
     {


        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
        $solutionText      =  array();
        $hasParentId       = "0";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $idString           = $oFunctionHelper->makePadString(0,10,"0"); 
        $problemId          = $oFunctionHelper->makePadString($problemId,10,"0");
        $button            = "button";

        $code   = 51;
        $tBack  = $oMessage->retrieveMessage($code, $portalType, $orgid,$lang,$tLang);              
        $code   = 52;
        $tExit  = $oMessage->retrieveMessage($code, $portalType, $orgid,$lang,$tLang);        

        $soIdValue      = "*119*".$idString."*".$hasParentId."*".$idString."*0*".$problemId;  

            $tBack = "";
            $code   = 51; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            }

        $solutionText[] = ['text'=>$tBack,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
            'elementOrder'=>"5",'value'=>$soIdValue."*5" ];          
            
        $soIdValue      = "*130*".$idString."*".$hasParentId."*".$idString;    

            $tExit = "";
            $code   = 52; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tExit = $rs0->messageVoice; 
               }  else {
                  $tExit = $rs0->messageText;                
               } 
            }

        $solutionText[] = ['text'=>$tExit,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
             'elementOrder'=>"6",'value'=>$soIdValue."*6"];
        
        return $solutionText;

     }


   //--------------------------------------------------------------------
    /* get Back Button to solution Screen                       */
    public function getBackSolutionScreen($orgid,$portalType,$optionNumber="",$lang, $tLang,$problemId=0)
     {


        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
        $solutionText      =  array();
        $hasParentId       = "0";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $idString           = $oFunctionHelper->makePadString(0,10,"0"); 
        $problemId          = $oFunctionHelper->makePadString($problemId,10,"0");
        $button            = "button";

        $code   = 51;
        $tBack  = $oMessage->retrieveMessage($code, $portalType, $orgid,$lang,$tLang);                    

        $soIdValue      = "*119*".$idString."*".$hasParentId."*".$idString."*0*".$problemId;  

            $tBack = "";
            $code   = 51; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tBack = $rs0->messageVoice; 
               }  else {
                  $tBack = $rs0->messageText;                
               } 
            }

        $solutionText = ['text'=>$tBack,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
              'elementOrder'=>"5",'value'=>$soIdValue."*5" ];          
       
        return $solutionText;

     }

   //--------------------------------------------------------------------
    /* get Exit Button to solution Screen                       */
    public function getExitSolutionScreen($orgid,$portalType,$optionNumber="",$lang, $tLang,$problemId=0)
     {


        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
        $solutionText      =  array();
        $hasParentId       = "0";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $idString           = $oFunctionHelper->makePadString(0,10,"0"); 
        $problemId          = $oFunctionHelper->makePadString($problemId,10,"0");
        $button            = "button";

        $code   = 52;
        $tExit  = $oMessage->retrieveMessage($code, $portalType, $orgid,$lang,$tLang);                    

        $soIdValue      = "*110*".$idString."*".$hasParentId."*".$idString; 

            $tExit = "";
            $code   = 52; 
            $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);
            foreach ($rsB as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $tExit = $rs0->messageVoice; 
               }  else {
                  $tExit = $rs0->messageText;                
               } 
            }

        $solutionText = ['text'=>$tExit,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
              'elementOrder'=>"6",'value'=>$soIdValue."*6" ];          
            
        
        return $solutionText;

     }

    //--------------------------------------------------------------------
    /*  NOT USED                                                 */
    /* get  Exit Button to solution Screen                       */
    public function getEButtonSolutionScreen($optionNumber="", $bLang,$tLang)
     {

        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message();         
        $solutionText      =  array();
        $hasParentId       = "0";                // there is parent record
        $parentId          = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $idString          = $oFunctionHelper->makePadString(0,10,"0");
        $button            = "button";

        $lang   = $bLang;
        $orgId  = NULL;  
        $tBack  = "";     
        $tExit  = "";            
        $code   = 52;
          
        // Text overrides not found in target language. USe base language
        if ($tExit == "") {
            $lang = $bLang;
            $tExit  = $oMessage->retrieveTextByCodeOrgLang($code,$orgId,$lang);              
        }      

        $soIdValue      = "*130*".$idString."*".$hasParentId."*".$idString;               
        $solutionText[] = ['text'=>$tExit,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
            'elementOrder'=>"6",'value'=>$soIdValue."*6"];
        
        return $solutionText;

     }


    //--------------------------------------------------------------------
    /* get has options value from solution_option                       */
    public function getHasOptions($userid, $optionNumber)
     {

        $oSolutionOption  = new SolutionOption();
        $hasSolRisk     = 0;
        $hasSolReq      = 0;
        $hasSolOpt      = 0;

        // get all options for this user   
        $rsd = $oSolutionOption->getByUserSosrid($userid,$optionNumber);
        foreach ($rsd as $rs0){                        
            $soOption   = $rs0['soOption'];
            switch ($soOption) {
                case "has risk":
                    $hasSolRisk = 1;
                    break;
                case "requires":
                    $hasSolReq  = 1;
                    break;
                case "has option":
                    $hasSolOpt  = 1;
                    break;
            }
        }

        $hasSol['hasSolRisk'] = $hasSolRisk;
        $hasSol['hasSolReq']  = $hasSolReq; 
        $hasSol['hasSolOpt']  = $hasSolOpt;       

        return $hasSol;
     }

    ////////////////////////////////////////////////////////////////
    //--------------------------------------------------------------------
    /*  Get right single term synonym
    */
    public function getRTermSynonym($termName)  
     {
        // Instantiate classes
        $oRelationTypeFilter   = new RelationTypeFilter();    
        $oRelation             = new Relation();
        $oTerm                 = new Term();
    
        $termSynonym = "";

        // Get relation type filter
        $step = 3 ;    // step 3: can de synonym to ////
        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);
        $rtgId = 8;

        // get value name id
        $termId = $oTerm->retrieveTermIdByName($termName);        

        // retrieve term synonym id
        $termSynonymId =  $oRelation->retrieveRTermSynonym($termId, $rtFilterId); 
        if ($termSynonymId > 0) {
          $termSynonym = $oTerm->retrieveTermName($termSynonymId);
        }
        if ($termSynonym != "" ) {
          $termName = $termSynonym;
        }

        return $termName;

     }


    //--------------------------------------------------------------------
    /*  Get left single term synonym
    */
    public function getLTermSynonym($termName)  
     {
        // Instantiate classes
        $oRelationTypeFilter   = new RelationTypeFilter();    
        $oRelation             = new Relation();
        $oTerm                 = new Term();
    
        $termSynonym = "";

        // Get relation type filter
        $step = 3 ;    // step 3: can de synonym to ////
        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);
        $rtgId = 8;

        // get value name id
        $termId = $oTerm->retrieveTermIdByName($termName);        

        // retrieve term synonym id
        $termSynonymId =  $oRelation->retrieveLTermSynonym($termId, $rtFilterId); 
        if ($termSynonymId > 0) {
          $termSynonym = $oTerm->retrieveTermName($termSynonymId);
        }
        if ($termSynonym != "" ) {
          $termName = $termSynonym;
        }

        return $termName;

     }




    ///////////////////////////////////////////////////////////////

    //--------------------------------------------------------------------
    /* get extended data state                                        
           value  relexdata solRiskExdata solReqExdata solOptExdata
             0        F           F              F            F
             1        T           F              F            F
             2        T           T
             3        T           F              V
             4        T           F              F            T
     */             
    public function getExdataState($hasRelExData,$hasSolRisk,$hasSolReq, $hasSolOpt)
     {
        $exdataState    = 0;
        if ($hasRelExData==0) {
           $exdataState = 0; 
        } elseif ($hasSolRisk==0 and $hasSolReq==0 and $hasSolOpt==0) {
           $exdataState = 1;            
        } elseif ($hasSolRisk==0) {
           $exdataState = 2;           
        } elseif ($hasSolRisk==0 and $hasSolReq==1) {
           $exdataState = 3;             
        } elseif ($hasSolRisk==0 and $hasSolReq==0 and $hasSolOpt==1) {
           $exdataState = 4; 
        }

        return $exdataState;
     }


    //--------------------------------------------------------------------
	/* get flow state                                                   */
    /* infere flow state from $inText content                           */
    /*  
       Value format                  Meaning    state 
      *****8888888888*d*9999999999   *****             solution relation prefix 
                                     8888888888        solution relation id
                                     d                 0=no parent, 1= has parent
                                     9999999999        parent id

      *****8888888888*0*9999999999   *****       21    solution option pick
      option has risk
      *11**8888888888*1*9999999999   *11**       24    option HasRisk pick
      *111*8888888888*1*9999999999   *111*       32    option HasRisk continue
      *110*8888888888*1*9999999999   *110*       55    option HasRisk exit
      *112*8888888888*1*9999999999   *112*       41    option HasRisk continue 2  
      *116*8888888888*0*9999999999               71    Review associated Risks pick  

      option requires
      *12**8888888888*1*9999999999   *12**       25    option Requires pick
      *121*8888888888*1*9999999999   *121*       35    option Requires continue
      *120*8888888888*1*9999999999   *120*       55    option Requires exit
      *122*8888888888*1*9999999999   *122*       42    option Requires continue 2 
      *117*8888888888*0*9999999999               72    Review associated Requirements pick 

      option has option
      *13**8888888888*1*9999999999   *13**       26    option HasOption pick
      *131*8888888888*1*9999999999   *131*       38    option HasOption continue
      *130*8888888888*1*9999999999   *130*       57    option HasOption exit
      *132*8888888888*1*9999999999   *132*       43    option HasOption continue 2 
      *118*8888888888*0*9999999999               73    Review associated Options pick 

      subset preprocessing
      *14**8888888888*1*9999999999   *14**       61    subset preprocessing pick
      *141*8888888888*1*9999999999   *141*             subset preprocessing Select all
      *140*8888888888*1*9999999999   *140*             subset preprocessing exit

      return from link Review option
      *119*9999999999*0*9999999999               74    Return to solutution screen      

     */
    public function getFlowState($inText)
    {

        // default state 
        $state = 0;
    
        // get option prefix
        $optionPrefix = substr($inText,0,5);
        
        switch ($optionPrefix) {
                
            case "*****":      // is pick from  solution relation
               $state = 38;
            break;

            case "*11**":      // is pick from option  has risk list
               $state = 50;
            break;      
      
      
            case "*111*":      // is pick from option  has risk Continue
               $state = 78;
            break;

            case "*110*":      // is pick from  option has risk Exit
               $state = 818;  
            break;
      
            case "*112*":      // is pick from option  has risk Continue2 
               $state = 110;   
            break;      

            case "*122*":      // is pick from option  requires Continue2 
               $state = 114;  
            break;      

            case "*132*":      // is pick from option  has option Continue2 
               $state = 118;  
            break;
      
            case "*12**":      // is pick from option requires pick
               $state = 58;  
            break;

            case "*121*":      // is pick from option requires Continue
               $state = 90; 
            break;

            case "*120*":      // is pick from option requires Exit
               $state = 818;  
            break;  

            case "*13**":      // is pick from option has option list
               $state = 66;   
            break;

            case "*134*":      // is pick from option has option list
               $state = 67;   
            break;

            case "*131*":      // is pick from option has option Continue
               $state = 102;  // 38;
            break;

            case "*130*":      // is pick from option has option Exit
               $state = 818;  // 55;
            break;  

            case "*14**":      // is pick from preprocessign subset list
               $state = 122;  // 61;
            break;  

            case "*116*":      // is pick from button Review associated Risks
               $state = 54;  // 71;
            break;  

            case "*117*":      // is pick from button Review associated Requires
               $state = 62; // 72;
            break;  

            case "*118*":      // is pick from button Review associated Options
               $state = 70; // 73;
            break;

            case "*119*":      // return to solution screen
               $state = 34;  // 10;
            break;

            case "*159*":      // prompt for problem validation
               $state = 19;  
            break;

            case "*266*":      // a linked option solved problem button was clicked. From 861, 862, 863
               $state = 266;  
            break;  

            case "*269*":      // a linked option solved problem button was clicked. From 865, 866, 867
               $state = 269;  
            break;  


            case "*803*":      // from state=803. View Solution for the picked problem
               $state = 88;  
            break;

            case "*807*":      // from state=803. View Solution for the picked problem
               $state = 807;  
            break;

            case "*851*":      // from state=855. process validation
               $state = 851;  
            break;

            case "*852*":      // from state=856. process validation
               $state = 852;  
            break;

            case "*853*":      // from state=857. process validation
               $state = 853;  
            break;            

            case "*857*":      // 
               $state = 857;  
            break;  


            case "*300*":      // display sliding bars
               $state = 300;  // 
            break;

            case "*301*":      // slide bar input: skip and continue
               $state = 301;  // 
            break;

            case "*302*":      // slide bar input: save and continue
               $state = 302;  // 
            break;

            case "*303*":      // exception error from slide bars
               $state = 303;  // 
            break;


            case "*305*":      // slide bar input: skip and continue
               $state = 311;  // 
            break;

            case "*306*":      // slide bar input: save and continue
               $state = 312;  // 
            break;


            case "*871*":      // RPA dialog mode. Elicit slot
               $state = 871;  // 
            break;


            case "*901*":      // handoff to live agent
               $state = 305;  // 
            break;

            case "*903*":      // handoff to RPA
               $state = 307;  // 
            break;

            case "*904*":      // Exit RPA button was clicked
               $state = 308;  // 
            break;


        }
      
        return $state;

    }		 
	 

  //----------------------------------------------------------	
	
}