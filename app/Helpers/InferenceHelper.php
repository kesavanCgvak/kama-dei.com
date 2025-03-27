<?php
/*--------------------------------------------------------------------------------
 *  File          : InferenceHelper.php        
 *  Type          : Helper class
 *  Function      : Provide functions for finding solutions in the chatbot
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 3.08  
 *  Updated       : 21 August 2024

SOLUTION BUTTON FORMAT
Prefix fact * relation * option* parent option * level

Solution button
*****0000000001*0000000000*0000000000*0000000000*1      problem         5,10
*****0000000001*0000000002*0000000000*0000000000*2      solution       16,10
*****0000000001*0000000002*0000000012*0000000000*3      option         27,10
*****0000000001*0000000002*0000000013*0000000012*4      linked option  38,10

Back button 
*??** parent value

Exit button
*??** parent value

SOLUTION_FACT TABLE
sfFactType    0   The fact was built with triple match
              1   The fact was built with term match
              2   Inferred problem

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
    public function makeSolutionFact($aSplitText, $userid, $orgid, $inquiry,$bLang, $tLang, $sfUtterance,$sfInput,
    	    $singleUtterance=0, $multipleTriple=0, $makeFactText=1)
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
        $rtsCount    = 0;
        $sfInput     = "";
        $mainConcept = 0;
        $sfKeyword   = "";
        $zero        = 0;
        $lang        = $bLang;
        $sfSubset    = 0;
        $sfHideFact  = 0;
        $sfKaas      = 0;  
        $sBlank      = 0;     

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
            $isFound          = 1; // 0= term not found; 1 = term found

            //  find term1
            $termName = "";
            $RTName   = "";
            $sfKeyword= "";

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
                $sfKeyword     = $termName;
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

            $sfParentId = 0;
            $sfParentFact = "";
            $sfFactType = 0;
            $sfFactProcessed = 0;
            $sfHasExtendedData = 0;
            $sfValidated = 0;
            $sfHasSolution = 0;
            $sfParentRating =0;
            $sfSource = 0;
            $sfssSubset = 0;


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
               //$aSplitText[$i] = implode(",", $arrayText);

               $sfInput = $aSplitText[$i];

          
               //$sfFact = $this->makeProblemText($sfRelationId,$zero,$lang,$lang); 
                $aProblem = $this->makeProblemText($sfRelationId,$orgid,$bLang,$tLang);
                $sfFact     =  $aProblem['sfRelation'];
                $sfLanguage =  $aProblem['sfLanguage'];


               // insert fact record
               if ($mainConcept == 0 or ($mainConcept == 1 and $i == $iLim)) {
                   $sfInput = $leftName0.",".$RTName0.",".$rightName0;

                  $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating, 
                    $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                    $sfParentId, $sfParentFact, $sfFactType,
                    $sfFactProcessed, $sfHideFact, $sfKaas,$sfHasExtendedData, $sfValidated, $sfHasSolution,
                    $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                    $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                    $sfssRelationTypeId, $sfssRightTermId, $userid ); 
                                 
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
                       $aProblem = $this->makeProblemText($sfRelationId,$orgid,$bLang,$tLang);
                       $sfFact     =  $aProblem['sfRelation'];
                       $sfLanguage =  $aProblem['sfLanguage']; 
                       $sfKeyword  =  $rightName0;                    
                     
                       $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating, 
                         $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                         $sfParentId, $sfParentFact, $sfFactType,
                         $sfFactProcessed, $sfHideFact, $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                         $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                         $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                         $sfssRelationTypeId, $sfssRightTermId, $userid ); 

                      $wRelationId = $sfRelationId;
                   }
            }


        }
        

        return $wRelationId;
     }

    //-----------------------------------------------------
    public function insertInferredProblem($orgid, $sfFact, $relationId, $bLang,$tLang,
         $sfParentId, $sfInquiry, $sfUtterance, $factType,$factProcessed, $userid)
    {
        /*  sfHideFact = 0 => inferred records are visible
            sfHideFact = 1 => inferred records are not visible
        */
        $oSolutionFact  = new SolutionFact();
        $sfId       = 0;
        $sfHideFact = 0;
        $sfKeyword  = "";
        $fCount = $oSolutionFact->getRelationCount($relationId,$userid);
        if ($fCount == 0) {
            $nzero      = 0;
            $sblank     = "";
            $aProblem   = $this->makeProblemText($relationId,$orgid,$bLang,$tLang);
            $sfFact     =  $aProblem['sfRelation'];
            $sfLanguage =  $aProblem['sfLanguage'];  
 

            $sfId = $oSolutionFact->insertFact($sfFact, $relationId, $nzero,$nzero, $nzero,
                $nzero, $sfLanguage, $sfParentId, $sblank, $factType,$factProcessed, $sfHideFact,
                $nzero, $nzero, $nzero,$nzero, $sfInquiry, $sfUtterance, $sfKeyword,$nzero, $nzero, $nzero,
                $sblank, $nzero, $nzero, $nzero, $nzero,  $userid ); 
        }
        return $sfId;

    }


    //-----------------------------------------------------
    public function addFactIntent($orgid, $userid, $bLang, $aMapping)
    {
        /*  Save intent record as problem in solution_fact
            sfHideFact = 0:inferred records are visible; 1 :not visible
        */
        $oSolutionFact  = new SolutionFact();
        $sfId          = 0;
        $sfHideFact    = 1;
        $sfKeyword     = "";
        $nzero         = 0;
        $factType      = 4;
        $sfSource      = 4;   // kaas
        $sfRating      = -1;  // default rating
        $nzero         = 0;
        $sblank        = "";
        $relationId    = 0;
        $sfFact        = ""; 
        $factProcessed = 1;
        $sfLanguage    =  $bLang;

        if (isset($aMapping['intent'])) {
           $sfFact     =  $aMapping['intent'];
        }

        if (isset($aMapping['kr_id'])) {
            $relationId = $aMapping['kr_id'];
        }
        $sfKeyword  = $sfFact;
 
        if ($relationId > 0) {
            $sfId = $oSolutionFact->insertFact($sfFact, $relationId, $sfRating,$nzero, $nzero,
                $nzero, $sfLanguage, $nzero, $sblank, $factType,$factProcessed, $sfHideFact,
                $nzero, $nzero, $nzero,$nzero, $sblank, $sblank, $sfKeyword,$nzero, $sfSource, $nzero,
                $sblank, $nzero, $nzero, $nzero, $nzero,  $userid ); 
        }

        return $sfId;
    }   

    //---------------------------------------------------------------------
    public function addSolutionSlot($orgid, $userid, $bLang, $sfId, $aMapping)
    {
        $oSolutionRelation     = new SolutionRelation();

        $nZero         = 0;
        $srRating      = 1;
        $sBlank        = "";
        $srSource      = 0;
        $mappingType   = 2;
        $srId          = 0;
        $len           = count($aMapping['slot']);

        for ($i=0;$i<$len;$i++) {
           if (isset($aMapping['slot'][$i]['slotname'])) {
               $slotName = $aMapping['slot'][$i]['slotname'];
               $slotId   = $aMapping['slot'][$i]['slotid'];    
               $srRelationId  = $aMapping['slot'][$i]['slotkrid'];        
               $srRelation  = $slotName;
               $srShortText = $slotName;
               //$srRelationId = $slotId;
               $srId = $oSolutionRelation->insertRelation($sfId,$srRelation,$srShortText,$srRelationId, $srRating, 
                   $nZero, $nZero, $nZero,$nZero, $bLang, $sBlank, $sBlank, $sBlank,$srSource,
                   $nZero, $nZero, $nZero, $nZero, $mappingType, $userid, $nZero, $nZero, $sBlank, $sBlank);
           } 

        }
 
        return $srId;
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
          	 //$sGreeting = $leftTermName.". ";
          	 //$sGreeting = ucfirst($sGreeting);
             $sGreeting = ucfirst($leftTermName).". ";
             $mType = "greeting";
             $aMsg = ['attribute'=>$mType , 'language'=>$baseLang , 'value'=>$sGreeting ];            
             //$messageCode = $oMessage->retrieveMessageCode($baseLang,$leftTermName);
            
             //if ($messageCode > 0) {
             //   $aMsg = $oMessage->getMessage($messageCode,$portalType,$orgid,$baseLang,$messageLang,$mType);  
             //}


          }

        }

        return $aMsg;
    } 


    //--------------------------------------------------------------------
    public function makeSingleSolutionFact($orgid, $userid, $aTermSet, $lang,$sfUtterance)
     {  

        // Instantiate classes
        $oTerm                = new Term();
        $oRelation            = new Relation();
        $oRelationType        = new RelationType();
        $oSolutionFact        = new SolutionFact();
        $oRelationTypeFilter  = new RelationTypeFilter();     
                
        // Initializa variables
        $sfParentId         = 0;
        $sfParentFact       = "";
        $sfFactType         = 1;   // fact was built with a term match
        $sfFactProcessed    = 0;
        $sfHasExtendedData  = 0;
        $sfValidated        = 0;
        $sfHasSolution      = 0;
        $sfParentRating     =0;
        $sfSource           = 0;
        $sfssSubset         = 0;

        $sfRating           = 0;
        $sfSubset           = 0;
        $sfssFact           = "";
        $sfFact             = "";
        $sfRelationId       = 0;
        $sfssRelationId     = 0;
        $sfssLeftTermId     = 0;  
        $sfRelationTypeId   = 0;
        $sfLeftTermId       = 0;
        $sfRightTermId      = 0;
        $sfssRelationTypeId = 0;
        $sfssRightTermId    = 0; 
        $sfParentFact       = "";
        $sfParentRating     = 0;
        $wRelationId        = 0;
        $sfSource           = 0;
        $sfKeyword          =  "";
        $sfHideFact         = 0;
        $sfKaas             = 0;
        $rtgId = 8;   // used in protected access

        $len = count($aTermSet);  

        for ($i=0; $i < $len; $i++) {
            $sTerm       = $aTermSet[$i];
            $sfInquiry   = $sTerm;
            $sfInput     = $sTerm; 

            // check if keyword is a triple fact
            $tripleKeywordFound = $oSolutionFact->findByKeyword($sTerm, $userid);

            if ($tripleKeywordFound == 0) {

                // get right term id
                $sfRightTermId = 0;
                $tmp = $oTerm->retrieveFilteredTermId($orgid,$sTerm,$rtgId);
                foreach($tmp as $rs0) {                   
                    $sfRightTermId = $rs0->termId;               
                }                 

                // insert fact record

                $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating, 
                    $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $lang,
                    $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, $sfKaas, 
                    $sfHasExtendedData, $sfValidated, $sfHasSolution,
                    $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                    $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                    $sfssRelationTypeId, $sfssRightTermId, $userid ); 
            }

        }


     }


    //--------------------------------------------------------------------
	  /*  Make an equivalent solution fact. Use right hand logic and synonyms 
    */
    public function makeEquivalentSolutionFact($userid, $orgId, $delete, $lang)
     {
        // Instantiate classes
        $oSolutionFact         = new SolutionFact();	
        $oRelationTypeFilter   = new RelationTypeFilter();		
        $oRelation             = new Relation();
        $oRelationType         = new RelationType();
 
        // Get relation type filter
        $step              = 3 ;    // step 3: can de synonym to ////
        $rtFilterId        = $oRelationTypeFilter->retrieveByStep($step);
        $rtgId             = 8;
        $wRelationId       = 0;
        $sfSource          = 0;
        $sfParentId        = 0;
        $sfParentFact      = "";
        $sfFactType        = 1;
        $sfFactProcessed   = 0;
        $sfHasExtendedData = 0;
        $sfValidated       = 0;
        $sfHasSolution     = 0;
        $sfParentRating    = 0;
        $sfSource          = 0;
        $sfssSubset        = 0;
        $sfKeyword         = ""; 
        $sfHideFact        = 0;
        $sfKaas            = 0;
        $custom            = "";

        // retrieve session fact for this userid a
        $rsdata = $oSolutionFact->getByUserFacttype($userid,$sfFactType);
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
                $sfSubset         = 0;
                $sfssFact         = $rsFact['sfssFact'];
                $sfssRelationId   = $rsFact['sfssRelationId'];
                $sfssLeftTermId   = $rsFact['sfssLeftTermId'];
                $sfssRelationTypeId = $rsFact['sfssRelationTypeId'];                
                $sfssRightTermId  = $rsFact['sfssRightTermId'];
                $sfInquiry        = $rsFact['sfInquiry'];
                $sfParentFact     = $rsFact['sfParentFact'];
                $sfParentRating   = $rsFact['sfParentRating'];
                $userid           = $rsFact['lastUserId'];
                $sfUtterance      = $rsFact['sfUtterance'];
                $sfLanguage       = $rsFact['sfLanguage'];

                $sourceRelationId = $sfRelationId;
			
                  $LTA  = $sfLeftTermId;   // orignal leftTermId
                  $RTA  = $sfRightTermId;  // original rightTermId
                  $LTB  = 0;               // leftTermId synonym
                  $RTB  = 0;               // rightTermId synonym


                  // get right term synonym
                 // $aRTB =  $oRelation->getOrgRTSynonym($RTA, $rtFilterId, $orgId, $rtgId);      
                  $aRTB =  $oRelation->getLTSynonym($RTA, $rtFilterId);              
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
                                 $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                                 $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, 
                                 $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                                 $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                                 $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                                 $sfssRelationTypeId, $sfssRightTermId,$userid ); 
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
                                 $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                                 $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, 
                                 $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                                 $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                                 $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                                 $sfssRelationTypeId, $sfssRightTermId, $userid ); 

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
                                 $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                                 $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, 
                                 $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                                 $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                                 $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                                 $sfssRelationTypeId, $sfssRightTermId, $userid ); 

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
                                 $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                                 $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, 
                                 $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                                 $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                                 $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                                 $sfssRelationTypeId, $sfssRightTermId, $userid ); 

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
    public function makeEquivalentSingleSolutionFact($userid, $orgId, $delete, $lang, $utterance)
     {


        // Instantiate classes
        $oSolutionFact         = new SolutionFact();  
        $oRelationTypeFilter   = new RelationTypeFilter();    
        $oRelation             = new Relation();
        $oRelationType         = new RelationType();
  
        // Get relation type filter
        $step        = 3 ;    // step 3: can de synonym to ////
        $rtFilterId  = $oRelationTypeFilter->retrieveByStep($step);
        $rtgId       = 8;
        $wRelationId = 0;
        $sfKeyword   = "";
        $sfHideFact  = 0;
        $sfKaas      = 0;
        $factType    = 1;

        // retrieve session fact for this userid 
        //$rsdata = $oSolutionFact->getByUserFacttype($userid,$factType);
        $rsdata = $oSolutionFact->getByUserFacttypeSynonym($userid,$factType);        
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
                $sfSubset         = 0;
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
                $sfUtterance      = $rsFact['sfUtterance'];
                $sfLanguage       = $rsFact['sfLanguage'];
                $sfParentId       = $rsFact['sfParentId'];
                $sfFactType       = $rsFact['sfFactType'];
                $sfFactProcessed  = $rsFact['sfFactProcessed'];
                $sfHasExtendedData= $rsFact['sfHasExtendedData'];
                $sfValidated      = $rsFact['sfValidated'];
                $sfHasSolution    = $rsFact['sfHasSolution'];
                $sfssSubset       = $rsFact['sfssSubset'];

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

                             $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating, 
                               $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                               $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, 
                               $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                               $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                               $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                               $sfssRelationTypeId, $sfssRightTermId,$userid ); 

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
   
                                $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating, 
                                  $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                                  $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, 
                                  $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                                  $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                                  $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                                  $sfssRelationTypeId, $sfssRightTermId, $userid ); 

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


                           $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating, 
                             $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                             $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, 
                             $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                             $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                             $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                             $sfssRelationTypeId, $sfssRightTermId, $userid ); 

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
        $step           = 2 ;    // step 2: filters for classification of session facts ////
                                 // is a type of                                        ///
        $hasSubset      = 0;
        $sfParentFact   = "";
        $sfParentRating = 0;
        $sfKeyword      = "";
        $sfHideFact     = 0;
        $sfKaas         = 0;

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
                      $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                      $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, 
                      $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                      $sfInquiry, $sfUtterance, $sfKeyword,$sfParentRating ,$sfSource,   
                      $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                      $sfssRelationTypeId, $sfssRightTermId, $userid ); 

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
        $sfInquiry           = "";
        $sfParentFact        = "";
        $sfParentRating      = 0;
        $sfHideFact          = 0;
        $sfKaas              = 0;
           
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
                    $sfLeftTermId, $sfRelationTypeId, $sfRightTermId, $sfLanguage,
                    $sfParentId, $sfParentFact, $sfFactType, $sfFactProcessed, $sfHideFact, 
                    $sfKaas, $sfHasExtendedData, $sfValidated, $sfHasSolution,
                    $sfInquiry, $sfUtterance, $sfParentRating ,$sfSource,   
                    $sfssSubset, $sfssFact, $sfssRelationId, $sfssLeftTermId,
                    $sfssRelationTypeId, $sfssRightTermId, $userid ); 

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
    public function makeFactRating($orgid,$personaId,$personalityId,$userid,$bLang, $tLang ,$TSStrategy=0)
     { 


        $sfNetRating = 0;  
        $custom = "";

        // Instantiate classes
        $oSolutionFact   = new SolutionFact();      

        // retrieve session fact for this userid 
        $rsdata = $oSolutionFact->getByUser($userid);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rsFact){                         
                $sfId         = $rsFact['sfId'];
                $sfRelationId = $rsFact['sfRelationId'];
                $sfName       = $rsFact['sfFact'];

                $wSolutionId  = $sfRelationId;
                $netRating = $this->calculateRelationRating($personaId,$personalityId, $sfRelationId);

                if ($netRating < 0) {
                    if ($sfName =="") {

                        if ($sfName == "") {
                            $sfName =  $this->makeRelationText($sfRelationId,$custom);
                            $lang       = $bLang; 
                        }

                        $oSolutionFact->updateFactNameRating($sfId, $netRating, $sfName, $lang);                      

                    } else {
                        $oSolutionFact->updateFactRating($sfId, $netRating );
                    }

                }

                if ($netRating < 0) {
                    $sfNetRating = $netRating;
                }

            }
        }

        return $sfNetRating;  // if problem found then $sfNetrating < 0

     }  


    //--------------------------------------------------------------------
    /*  Make rating for inferred problem

     */ 
    public function makeProblemRating($sfId,$relationId, $personaId,$personalityId,$userid, $TSStrategy=0)
     { 

        $netRating = 0;  

        $oSolutionFact   = new SolutionFact();  

        $netRating = $this->calculateRelationRating($personaId,$personalityId, $relationId);
        $oSolutionFact->updateFactRating($sfId, $netRating );

        return $netRating;  

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

                    if ($powerValue == 0){
                        $netRating = $netRating + $prvScalarValue ;   
                    } else {
                        $netRating = $netRating + ($prvScalarValue * $powerValue);                       
                    }
 					
                } else {
                      $netRating = $netRating + $prvScalarValue ;  
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
        $state,$bLang,$tLang,$delrec=1, $pickProblemId=0)
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

      if ($delrec == 1) {
           $oSolutionRelation->deleteByUser($userid,$pickProblemId);
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
      $nZero  = 0;
      $one    = 1;
      $zero   = 0;
      $sBlank = "";
      $srHasExtendedData = 0;
      $canRateValue      = 107;
    
      // 1. Get relation type filters

      $step = 5 ;    // step 5: filters for intermediate results /////  
                     // 64 is a type of
                     // 85 is the noun form of
      $lStep = 9;    // 105 can be served at. Special Lex processing
      $lRelTypeId = 0;

      // special lex processing for lex controller
      if ($isLex == 1) {
          $lRelTypeId = $oRelationTypeFilter->retrieveByStep($lStep);
      }

      $rsFilter0 = $oRelationTypeFilter->getByStep($step);

      // 2. get problems for this userid
   
      $rsdata = $oSolutionFact->getFactByUser($userid,$pickProblemId );

      foreach ($rsdata as $rsFact){                     // L1

          $sfId          = $rsFact['sfId'];
          $sfRelationId  = $rsFact['sfRelationId'];
          $sfRating      = $rsFact['sfRating'];
          $sfRightTermId = $rsFact['sfRightTermId'];

          $oSolutionFact->updateFactProcessed($sfId,$one);

          $rightTermSynonymId = $sfRightTermId;

  
          // 3. Process negative fact ratings

          $rsFilter = $rsFilter0;


          foreach($rsFilter as $rtFilter) {                 // L2
              // process relation with intermediate filter relations            
    
              // 3.1 Save relation type filter id
              $relationTypeFilterId = $rtFilter['relationTypeId'];

              // 3.2 Get intermediate relation          
              $rsRFilter = $oRelation->getSynonymRelationId($relationTypeFilterId, $sfRightTermId);

              if (!empty($rsRFilter)) {                            // L3


                  foreach($rsRFilter as $rsF) {                      // L4

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
                      $srHasExtendedData = 0;
                      $srRTermId = $rightTermSynonymId;
                      $rsIntermediateRelation = $oRelation->getByRightTerm($rightTermSynonymId); 
        
                      foreach($rsIntermediateRelation as $rsIR) {                // L5
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
                              $srRating = $this->calculateRelationRating($personaId,$personalityId, $srRelationId);  
                          }

                          // pick unique potential solution relation with positive rating
                          $solutionRelationCount = $oSolutionRelation->countSR($sfId,$srRelationId,$userid); 
                  
                          if (($srRating  > 0) and ($solutionRelationCount == 0)) {     //  L6 

                              // make solution relation text  

                              $lang = $bLang;
                              $srRelation = "";
                              $srShortText = "";
                              $srHasExtendedData = 0;

                              // relation translation and short text
                              $RTrs = $oRelationLanguage->getText($srRelationId,$orgid,$bLang, $tLang);

                              foreach($RTrs as $rs0) {                                      // L7

                                  if (isset($rs0->optionalText)) {
                                      $srRelation = $rs0->optionalText;
                                      $srRelation = ucfirst($srRelation);
                                      $lang       = $rs0->language_code;
                                  }                                        
                                       
                                  if (isset($rs0->shortText)) {
                                      $srShortText = $rs0->shortText;
                                      $lang        = $rs0->language_code;
                                  }
                              }                                                            // END L7

                              if ($srRelation == "") {
                                  $srRelation =  $this->makeRelationText($srRelationId,$custom);
                                  $lang       = $bLang; 
                              }

                              $srSource = 0;
                              if ($srRTypeId == $canRateValue ) {
                                  $srSource = 7;
                              }
                                           
                              // save solution relations                     
                              $sredParentId = $oSolutionRelation->insertRelation($sfId,$srRelation,$srShortText,
                                  $srRelationId, $srRating, $zero, $srLTermId,$srRTypeId,$srRTermId, 
                                  $lang,$sBlank, $sBlank, $sBlank,$srSource, $nZero, $nZero, 
                                  $nZero, $nZero, $nZero, $userid);

                          }                                                            // END L6 

                      }                                                               // END L5


                  }                                                               // END L4


              }                                                                 // END L3
                 else

              {

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
                      $solutionRelationCount = $oSolutionRelation->countSR($sfId,$srRelationId,$userid);
       

                      if (($srRating  > 0) and ($solutionRelationCount == 0)) {   
                          $srHasExtendedData   = 0;
                          $srRTermId = $sfRightTermId;
                    
                          // make solution relation text                                   
                          $lang = $bLang;
                          $srRelation = "";
                          $srShortText = "";

                          $RTrs = $oRelationLanguage->getText($srRelationId,$orgid,$bLang, $tLang);

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

                              $srSource = 0;
                              if ($srRTypeId == $canRateValue ) {
                                  $srSource = 7;
                              }                          
                                                           
                          $sredParentId = $oSolutionRelation->insertRelation($sfId,$srRelation,$srShortText,
                              $srRelationId, $srRating, $srHasExtendedData, $srLTermId,$srRTypeId,$srRTermId, 
                              $lang,$sBlank, $sBlank, $sBlank,$srSource, $nZero, $nZero, 
                              $nZero, $nZero, $nZero, $userid);

                            }   

                      }         
                  }
              }
      }                                                                  // END L1



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
    //  find and save solutions for inferred problem
    //-------------------------------------------------------------------- 
      public function makePickProblemRelation($personaId,$personalityId, $userid, $orgid, $hasBar, $slideBar,
        $state,$bLang,$tLang,$delrec=1, $pickFactId=0)
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

        if ($delrec == 1) {
           $oSolutionRelation->deleteByUser($userid,$pickFactId);
        }

        $oSolutionFact->deletePositive($userid);

      // remove duplicate fact records
      $rs = $oSolutionFact->getByUserOrderByRelationId($userid);
      $wrId = 0;
      foreach($rs as $rs0) {
         $sfId              = $rs0['sfId'];
         $sfRelationId      = $rs0['sfRelationId'];
         $sfSource          = $rs0['sfSource'];


         if ($sfRelationId == $wrId) {
          
            if($sfSource < 99) {            // skip fact with RPA mapping
                //$oSolutionFact->deleteById($sfId);  
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
        $isLex = 0;

        $nZero  = 0;
        $zero   = 0;
        $sBlank = "";
        $srHasExtendedData = 0;
    
        // 1. Get relation type filters

        $step = 5 ;    // step 5: filters for intermediate results /////  
                       // 64 is a type of
                       // 84 can desire 
                       // 44 can request
        $lStep = 9;    // 105 can be served at. Special Lex processing
        $lRelTypeId = 0;

        // special lex processing for lex controller
        //if ($isLex == 1) {
        //     $lRelTypeId = $oRelationTypeFilter->retrieveByStep($lStep);
        //}

        $rsFilter0 = $oRelationTypeFilter->getByStep($step);
      
        // 2. retrieve seesion fact for this userid
        $rsdata = $oSolutionFact->getFactByUser($userid, $pickFactId );

        if (!empty($rsdata)) {                       // If L1

            foreach ($rsdata as $rsFact){            // FOREACH L2

                $sfId          = $rsFact['sfId'];
                $sfRelationId  = $rsFact['sfRelationId'];
                $sfRating      = $rsFact['sfRating'];
                $sfRightTermId = $rsFact['sfRightTermId'];

               // $aProblem = $this->makeProblemText($sfRelationId,$orgid,$bLang,$tLang);

                $rightTermSynonymId = $sfRightTermId;
  
                // 3. Process negative fact ratings
                if ($sfRating < 0) {                  // IF L3

                    $rsFilter = $rsFilter0;

                    foreach($rsFilter as $rtFilter) {  // FOREACH L4
                        // process relation with intermediate filter relations            

                        // 3.1 Save relation type filter id
                        $relationTypeFilterId = $rtFilter['relationTypeId'];

                        // 3.2 Get intermediate relation          
                        $rsRFilter = $oRelation->getSynonymRelationId($relationTypeFilterId, $sfRightTermId);

                        if (!empty($rsRFilter)) {     // IF L5

                          foreach($rsRFilter as $rsF) {   // FOREACH L6

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
                            $srHasExtendedData = 0;
                            $srRTermId = $rightTermSynonymId;
                            $rsIntermediateRelation = $oRelation->getByRightTerm($rightTermSynonymId); 
        
                            foreach($rsIntermediateRelation as $rsIR) {  // FOREACH L7
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
                  
                                if (($srRating  > 0) and ($solutionRelationCount == 0)) { // IF L8

                                    // make solution relation text  


                                     //// RELATION TRANSLATION   /////////////

                                    // relation data
                                    $lang = $bLang;
                                    $srRelation = "";
                                    $srShortText = "";
                                    $srHasExtendedData = 0;

                                    // relation translation and short text
                                    $RTrs = $oRelationLanguage->getText($srRelationId,$orgid,$bLang, $tLang);
                                    foreach($RTrs as $rs0) {                        // FOREACH L9

                                        if (isset($rs0->optionalText)) {
                                           $srRelation = $rs0->optionalText;
                                           $srRelation = ucfirst($srRelation);
                                           $lang       = $rs0->language_code;
                                        }                                        
                                       
                                        if (isset($rs0->shortText)) {
                                           $srShortText = $rs0->shortText;
                                           $lang        = $rs0->language_code;
                                        }
                                    }                                             // END L9

                                    if ($srRelation == "") {
                                        $srRelation =  $this->makeRelationText($srRelationId,$custom);
                                        $lang       = $bLang; 
                                    }
                                           
                                
                                    ///////////////////////////////////////

                                    // save solution relations                     

                                    $sredParentId = 
                                    $oSolutionRelation->insertRelation($sfId,$srRelation,$srShortText,
                                       $srRelationId, $srRating, $zero, $srLTermId,$srRTypeId,$srRTermId, 
                                       $lang,$sBlank, $sBlank, $sBlank,$srSource, $nZero, $nZero, 
                                       $nZero, $nZero, $nZero, $userid);

                                }                                                 // END L8         
                            }                                                    // END L7

                          }                                                     // END L6

                           ///
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

                                    $RTrs = $oRelationLanguage->getText($srRelationId,$orgid,$bLang, $tLang);
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
                                                            
                                    $sredParentId = 
                                    $oSolutionRelation->insertRelation($sfId,$srRelation,$srShortText,
                                       $srRelationId, $srRating, $srHasExtendedData, $srLTermId,$srRTypeId,$srRTermId, 
                                       $lang,$sBlank, $sBlank, $sBlank,$srSource, $nZero, $nZero, 
                                       $nZero, $nZero, $nZero, $userid);

                                }   


            
      //                      }
            
                         ///////          
      //                  }           
        //////   
                   }         
                } 
            }
        }    // END L1

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
    //  Get and update problem text
    //--------------------------------------------------------------------
    public function makeFactText ($orgid,$userid,$bLang,$tLang)
    {
        $oSolutionFact      = new SolutionFact();
        $oRelationLanguage  = new RelationLanguage();
        $problemId          = 0;
        $custom             = "";

        $rsdata = $oSolutionFact->getByProblemUser($problemId,$userid, 0);

        foreach ($rsdata as $rsFact){                             // L1

            $sfId          = $rsFact['sfId'];
            $sfRelationId  = $rsFact['sfRelationId'];
            $sfRating      = $rsFact['sfRating'];
            $sfLeftId      = $rsFact['sfLeftTermId'];
            $sfRTId        = $rsFact['sfRelationTypeId'];
            $sfRightId     = $rsFact['sfRightTermId'];

            if ($sfRating < 0) {                                  // L2
                    
                $sfFact      = "";
                $srShortText = "";
                $lang        =  $bLang;

                // relation translation and short text
                $RTrs = $oRelationLanguage->getText($sfRelationId,$orgid,$bLang, $tLang);
                foreach($RTrs as $rs0) {                          // L3

                        if (isset($rs0->optionalText)) {
                            $sfFact     = $rs0->optionalText;
                            $sfFact     = ucfirst($sfFact);
                            $lang       = $rs0->language_code;
                        }                                        
                                       
                        if (isset($rs0->shortText)) {
                            $srShortText = $rs0->shortText;
                            $lang        = $rs0->language_code;
                        }
                }                                                // END L3

                if ($sfFact == "") {
                    $sfFact =  $this->makeRelationText($sfRelationId,$custom);
                    $lang       = $bLang; 
                }

                $oSolutionFact->updateFactName($sfId,$sfFact);

              }                                                  // END L2

        }                                                        // END L1

    }

 
    //--------------------------------------------------------------------
    //  find and save solutions for inferred problem
    //--------------------------------------------------------------------
    public function makeProblemRelation ($problemId, $personaId,$personalityId, $userid, $orgid,  
        $bLang,$tLang,$delrec=1, $pickFactId=0)
    {
    // Instantiate classes

        $oSolutionFact         = new SolutionFact();
        $oRelationType         = new RelationType();
        $oTerm                 = new Term();
        $oRelation             = new Relation();
        $oRelationLanguage     = new RelationLanguage();
        $oPersonalityRelation  = new PersonalityRelation();
        $oSolutionRelation     = new SolutionRelation();
        $oSRExdata             = new SolutionRelationExdata();


        if ($delrec == 1) {
            $oSolutionRelation->deleteByUser($userid,$pickFactId);
        }

        $oSolutionFact->deletePositiveProblem($userid);

      // remove duplicate fact records
      $rs = $oSolutionFact->getByUserOrderByRelationId($userid);
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
        $lang = $bLang;
        $sfLanguage = $bLang;
        $custom ="";
        $sfShortText = "";
        $sfFact = "";
        $solCount = 0;
        $nZero  = 0;
        $sBlank = "";

        // 1. get data for this problem
        if ($pickFactId > 0) {
            $problemId = $oSolutionFact->retrieveById($pickFactId);
        }

        $rsdata = $oSolutionFact->getByProblemUser($problemId,$userid);


        foreach ($rsdata as $rsFact){   

                $sfId          = $rsFact['sfId'];
                $sfRelationId  = $rsFact['sfRelationId'];
                $sfRating      = $rsFact['sfRating'];
                $sfRightTermId = $oRelation->retrieveRTermId($sfRelationId);   

                if ($sfRating < 0) {
                    
                    $rsREL = $oRelation->getByRightTerm($sfRightTermId); 
        
                    foreach($rsREL as $rsREL0) {
                        $srRelationId     = $rsREL0['relationId'];
                        $srRTypeId        = $rsREL0['relationTypeId'];
                        $srLTermId        = $rsREL0['leftTermId'];
                        $srRTermId        = $rsREL0['rightTermId'];  

                        $srRating = $this->calculateRelationRating($personaId,$personalityId, $srRelationId);

                        if ($srRating > 0) {

                            $srRelation = "";
                            $srShortText = "";

                            // relation translation and short text
                            $RTrs = $oRelationLanguage->getText($srRelationId,$orgid,$bLang, $tLang);
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

                            $oSolutionRelation->insertRelation($sfId,$srRelation,$srShortText,
                                $srRelationId, $srRating, $nZero, $srLTermId,$srRTypeId,$srRTermId, 
                                $lang,$sBlank, $sBlank, $sBlank,$srSource, $nZero, $nZero, 
                                $nZero, $nZero, $nZero, $userid);


                        }


                    }


                }

        }
  

      // Has soltuion relation
      $hasSolutionRelation = 0;

      // remove duplicate records
      $rs = $oSolutionRelation->getByUserOrderByRelationId($userid);
      foreach($rs as $rs0) {
         $srId              = $rs0['srId'];
         $srRelationId      = $rs0['srRelationId'];
         $srRating          = $rs0['srRating'];
         if ($srRating > 0) {
             $hasSolutionRelation = 1;            // there are positive relation 
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
    public function makeFactExtendedData ($userid, $orgid, $portalType, $bLang,$tLang,$pickProblemId=0)
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
      //$rsrel = $oSolutionFact->getByUser($userid);
      if ($pickProblemId == 0) {
         $rsrel = $oSolutionFact->getByProblemUser($pickProblemId,$userid);
      } else {
         $rsrel = $oSolutionFact->getByProblemId($pickProblemId,$userid);        
      }

      //$rsrel = $oSolutionFact->getByProblemUser($pickProblemId,$userid);

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
//echo " INSIDE C ";

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
//echo " INSIDE D orgid=$orgid, entityId=$entityId, hasEntity=$hasEntity ; ";  

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

                    if (!empty($rseav)) {  // L6
                         foreach ($rseav as $rs00){   // L7
//echo " INSIDE Entity";
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
    public function makeSolutionOption ($userid,$orgid,$bLang,$tLang, $problemId)
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

    $custom     = "";
    //$problemId  = 0;
    $skipRel    = 0;
 

    $rsdata = $oSolutionRelation->getByUserOrderDESCByRating($userid,$skipRel,$problemId);
    if (!empty($rsdata)) { 
  
        foreach ($rsdata as $rsRel) {     
            $srId          = $rsRel['srId'];
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


                $RTrs = $oRelationLanguage->getText($soSolutionId,$orgid,$bLang, $tLang);
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

                // insert valid solution option  
                if ($soSolution !="" and $soSolution != null) {                 
                    $oSolutionOption->insertOption($srId,$linkTypeName,$soSolution,$soSolutionId,
                        $lTermId,$rTypeId,$rTermId,$soShortText,$lang,$linkOrder,$srRating,
                        $soHasExtendedData,$soParentId,$userid);
                }

            }
         }
      }


   }		 


    //--------------------------------------------------------------------
    /**
        Make options a level deeper
     */
    public function makeNewSolutionOptionLink ($userid,$orgid,$bLang,$tLang,$pickSolutionId,$pickOptionId,$portalType)
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
        $soSolutionId      = 0;
        $parentOwnerId     = 0;
        $soChildren        = -1;
        $soParentId        = $pickOptionId;

        //$rsdata = $oSolutionOption->findById($pickOptionId);
        $rsdata = $oSolutionOption->findNewById($pickOptionId, $soChildren);
    
        foreach ($rsdata as $rsRel){            
            $soId          = $rsRel['soId'];
            //$sosrId        = $rsRel['sosrId'];
            $soFactId      = $rsRel['soFactId'];
            $soSolutionId  = $rsRel['soSolutionId'];
            $soProblemId   = $rsRel['soProblemId'];
            $soRating      = $rsRel['soRating'];
            $soChildren    = $rsRel['soChildren'];

        }        


        //// The optional text inherits access from the parent relation //////////
        $parentOwnerId = $this->getParentOwnerId($orgid,$soSolutionId);
        if ($parentOwnerId == 0) {
            $parentOwnerId = $orgid;
        }

        //   FIND CHILD LINKED OPTION KR
      if ($soChildren == -1 and $soSolutionId > 0) {                       // L1
                               
        $rs = $oRelationLink->getByLeftRelationIdOrder($soSolutionId);  
        foreach($rs as $rsOption) {                    // L2  
            $soSolutionId      = $rsOption['rightRelationId'];  
            $linkTermId        = $rsOption['linkTermId'];
            $linkOrder         = $rsOption['linkOrder']; 
            $soSolution        = "";
            $linkTypeName      = $oTerm->retrieveTermName($linkTermId);  
            $lang              = $bLang;  
            $soShortText       = "";
            $leftKrId          = $rsOption['leftRelationId'];  
            $rightKrId         = $rsOption['rightRelationId']; 

            if ($leftKrId != $rightKrId ) {
 
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
                if ($soSolution != "" and $soSolution != null) {
                
                    $lastInsertId =  $oSolutionOption->insertOption($pickSolutionId,$linkTypeName,$soSolution,
                      $soSolutionId, $soLeftTermId,$soRelationTypeId,$soRightTermId,$soShortText,$lang,
                      $linkOrder,$soRating, $soHasExtendedData,$soParentId, $userid);
                    $soChildren++;

                    // Build extended data for this solution option record
                    $this->makeOptionExtendedDataInside($userid, $orgid, $portalType,$bLang,$tLang, 
                           $lastInsertId, $soSolutionId, $soHasExtendedData);

                }
            }

        }  // END L2
           $oSolutionOption->updateChildren($soParentId,$soChildren);
      }  // END L1


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
        $hasParent         = 0;
        $soLeftTermId      = 0;
        $soRelationTypeId  = 0;
        $soRightTermId     = 0; 
        $custom            = "";
        $soSfId            = -1;

        $rsdata = $oSolutionOption->getByUserParent($userid,$soParentId);
    
        foreach ($rsdata as $rsRel){            
            $soId          = $rsRel['soId'];
            $sosrId        = $rsRel['sosrId'];
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

                //// The optional text inherits access from the parent relation //////////
                $parentOwnerId = $this->getParentOwnerId($orgid,$soSolutionId);
                if ($parentOwnerId == 0) {
                    $parentOwnerId = $orgid;
                }
                //////////////////////////////////////////////////////////////////

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
                $lastInsertId = $oSolutionOption->insertOption($sosrId,$linkTypeName,$soSolution,
                   $soSolutionId,$soLeftTermId,$soRelationTypeId,$soRightTermId,$soShortText,$lang,
                   $linkOrder,$soRating,$soHasExtendedData,$soParentId,$userid);

                $soParentId = $lastInsertId;

                // Insert option links at any deeper level    //

                while ($soParentId > 0) {
                    $lastInsertId = $this->makeSolutionOptionDeepLink ($userid,$orgid,$bLang,$tLang,$soParentId,$lastInsertId);
                    $soParentId = $lastInsertId;

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
    public function makeSolutionOptionDeepLink ($userid,$orgid,$bLang,$tLang,$soParentId,$id)
     {
        // Instantiate classes
        $oRelationLink       = new RelationLink();
        $oSolutionOption     = new SolutionOption();
        $oRelationLanguage   = new RelationLanguage(); 
        $oTerm               = new Term();  
                
        // 1. get solution relations for this personality and session id
        $soHasExtendedData = 0;
        $soParentId        = 0;
        $hasParent         = 0;
        $soLeftTermId      = 0;
        $soRelationTypeId  = 0;
        $soRightTermId     = 0; 
        $custom            = "";
        $soSfId            = -1;
        $lastInsertId      = 0;
        $soProcess         = -1;

        $rsdata = $oSolutionOption->findById($id);
    
        foreach ($rsdata as $rsRel){            
            $soId          = $rsRel['soId'];
            $sosrId        = $rsRel['sosrId'];
            $soRelationId  = $rsRel['soSolutionId'];
            $soRating      = $rsRel['soRating'];
            $soParentId    = $id;

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
                //////////////////////////////////////////////////////////////////                

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
                $hasParent = 1;
                $lastInsertId = $oSolutionOption->insertOption($sosrId,$linkTypeName,$soSolution,
                   $soSolutionId,$soLeftTermId,$soRelationTypeId,$soRightTermId,$soShortText,$lang,
                   $linkOrder,$soRating,$soHasExtendedData,$id,$userid);

                ////// Insert recursively option links at deeper levels    //////////////////////////
                $soParentId = $lastInsertId;
                while ($soParentId > 0) {
                   $lastInsertId = $this->makeSolutionOptionDeepLink($userid,$orgid,$bLang,$tLang,$soParentId,$lastInsertId);
                   $soParentId = $lastInsertId; 
                }
                ////////////////////////////////////////////////////////////////////////////////////


            }
        }

        return $lastInsertId;

     }


    //--------------------------------------------------------------------
    /* this function is called from makeOptionDeepLink
     */
    public function makeOptionExtendedDataInside ($userid, $orgid, $portalType,$bLang,$tLang, 
           $soId, $soSolutionId, $soHasExtendedData)
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
        $oExtSubType         = new \App\Models\ExtendedSubType();         
        $oRTFilter           = new RelationTypeFilter(); 
        $oFunctionHelper     = new FunctionHelper();
        $parentTableId   = 2;

        $lang            = $bLang;
        $step = 8;   // can access protected data
        $rtCanAccessProtectedDataId = $oRTFilter->retrieveByStep($step);
        $rtgId = 8;

        // get chatIntro field length
        $chatIntroLen = \Config::get('kama_dei.static.soedChatIntroLength',1000);        

        $soHasED           = $soHasExtendedData;
        $soRelationId      = $soSolutionId;
        $soHasExtendedData = 0;

        if ($soHasED == 0) {   // L3

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

        $rsdata = $oSolutionOption->getNegativeByUserParent($userid,$soParentId);
    
        foreach ($rsdata as $rsRel){            
            $soId          = $rsRel['soId'];
            $sosrId        = $rsRel['sosrId'];
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
                    $soHasExtendedData,$soParentId,$userid);

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
        $oRelation           = new Relation();
        
        // get relationId field values
        $you = "You"; 
        $person = "person";

        $leftTermId = 0;
        $relationTypeId  = 0;
        $rightTermId = 0;
        $relationText = "";   
    
        $relationText = $oRelation->seekRelationName($relationId,$you);

        $relationText = ucfirst($relationText);

        return $relationText; 
        	
    }

    //--------------------------------------------------------------------
    public function makeRelationTextArray($relationId,$custom="")
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
        $relationText = ucfirst($relationText);

        $aRelation['relationText']  = $relationText;
        $aRelation['sfLanguage']  = $lang;
        $aRelation['sfShortText'] = $sfShortText;        

        return $arelation;   
    }


    //--------------------------------------------------------------------
    public function makeProblemText($relationId,$orgid,$bLang,$tLang)
    {

        // Instantiate classes
        $oRelationLanguage   = new RelationLanguage();
           
        $relationText = "";  
        $person       = "You"; 
        $sfKeyword    = "";
                      
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
    /* makeValidationRecord
       When a superterm is found, this function can create a relation (problem)
       in table solition_validation that can be validated. 
       If validation si Yes, 
        the validated problem (solution_validation) will be copy to regular problem (solution_fact)

    */
/*

    public function makeValidationRecord($orgid,$personaId,$personalityId,$leftTermId,$userid)
    {
        // Instantiate classes
        $oRelation           = new Relation();
        $oSolutionValidation = new SolutionValidation(); 
        $validation          = -1;
        $rating              = 0;

        $rsdata = $oRelation->getByLeftTerm($orgid, $leftTermId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){              
                $relationId     = $rs0->relationId;
                $relationTypeId = $rs0->relationTypeId;
                $rightTermId    = $rs0->rightTermId;

                // Calculate rating. Save records with negative rating
                $rating = $this->calculateRelationRating($personaId,$personalityId, $relationId);
                if ($rating < 0) {
                    $oSolutionValidation->insertRecord($relationId, $leftTermId, 
                       $relationTypeId,$rightTermId, $rating, $validation, $userid);    
             
                }
            }
        }
      
    }
 */   

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
		
        $rsdata = $oSolutionRelation->getByUserOrderDESCByRating($userid,$skipRel,$problemId);
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




    //----------------------------------------------------------------------
    /* get solution option and make output                                */
    /* value format= *****0000000001*0000000002*0000000000*0000000000*2   */

    public function getSolutionRelationArray($orgid, $userid,$portalType,$hasRating, $bLang,$tLang,$problemId)
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

        $rsdata = $oSolutionRelation->getByUserOrderDESCByRating($userid, $rtFilterId, $problemId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $srId           = $rs0->srId;
                $nproblemId     = $rs0->srsfId;
                $shortText      = $rs0->srShortText;
                $srIdString     = $oFunctionHelper->makePadString($srId,10,"0"); 
                $problemId      = $oFunctionHelper->makePadString($nproblemId,10,"0");     
       
                //$srIdValue      = "*****".$srIdString."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId;
                //                *****0000000001*0000000002*0000000000*0000000000*2
                $srIdValue      = "*****".$problemId."*".$srIdString."*0000000000*0000000000*2";


                $srRelation     = $rs0->srRelationText;   
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
                    $solutionText[] = ['text'=>$srRelation, 'newLine'=>"0", 'shortText'=>$shortText, 'atttype'=>$button,
                      'elementType'=>$button, 'value'=>$srIdValue ,'rating'=>$srRating ];  
                } else {

                     $solutionText[] = ['text'=>$srRelation , 'newLine'=>"0",  'shortText'=>$shortText, 'language'=>$lang,
                      'atttype'=>$button,'elementType'=>$button,'elementOrder'=>"3", 'value'=>$srIdValue ]; 
                     $isfound = 2; 
                 
                }
          
            }
        }



      //if ($isLex == 99) {
      //    return $isfound;
      // } else {
      //    return $solutionText;
      // }
      return $solutionText;
     } 


    //----------------------------------------------------------------------
    /* get solution relation count                                         */
    public function getSolutionRelationCount($orgid, $userid,$problemId)
     {

        // Instantiate classes
        $oSolutionRelation  = new SolutionRelation();
        $oRTFilter          = new RelationTypeFilter();        

        $solutionCount      = 0;    
        $step               = 11;   // omit this relation type in solution response
        $rtFilterId         = $oRTFilter->retrieveByStep($step);    
        $rsdata = $oSolutionRelation->getByUserOrderDESCByRating($userid, $rtFilterId, $problemId); 
        foreach ($rsdata as $rs0){                        
            $solutionCount++;
        }

        return $solutionCount;
     } 

    //----------------------------------------------------------------------
    /* get solution relation count                                         */
    public function getSolutionFactCount($userid)
     {

        // Instantiate classes
        $oSolutionFact      = new SolutionFact();      

        $factCount      = 0;    
        $rsdata = $oSolutionFact->getFactByUser($userid,0,0); 
        foreach ($rsdata as $rs0){                        
            $factCount++;
        }

        return $factCount;
     } 


   //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                    */

    public function getProblemSolRelationArray($orgid, $userid,$portalType,$hasRating, $bLang,$tLang,$problemId)
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
        $button         = "button";
        $lang = $bLang;

        $rsdata = $oSolutionRelation->getByUserOrderDESCByRating($userid, $rtFilterId, $problemId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $srId           = $rs0->srId;
                $problemId      = $rs0->srsfId;
                $shortText      = $rs0->srShortText;
                $srIdString     = $oFunctionHelper->makePadString($srId,10,"0"); 
                $problemId      = $oFunctionHelper->makePadString($problemId,10,"0");     

                $srIdValue      = "*****".$srIdString."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId;

                $srRelation     = $rs0->srRelationText;   
                $srRating       = $rs0->srRating; 
                $srRelationId   = $rs0->srRelationId;
                $lang            = $rs0->srLanguage;

                $solutionText[] = ['text'=>$srRelation,'shortText'=>$shortText, 'atttype'=>$button,'elementType'=>$button,
                    'elementOrder'=>"3",'value'=>$srIdValue ,'rating'=>$srRating ];  

            }
        }


        return $solutionText;

     } 
//rrr


     //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                     */
    public function getSolutionFactArray($userid,$parentId, $bLang,$tLang)
     {

        // Instantiate classes
        $oSolutionFact      = new SolutionFact();
        $oFunctionHelper    = new FunctionHelper();
        $oRTFilter          = new RelationTypeFilter();       
        $solutionText       =  array();
        $hasParentId        = "0";                // no parent record
        $hasED              = 0;
        $sparentId          = $oFunctionHelper->makePadString(0,10,"0");
        $step               = 11;   // omit this relation type in solution response
        $rtFilterId         = $oRTFilter->retrieveByStep($step);    
        $isfound = 0;
        $button  = "button"; 
        $lang =$bLang;
        $prefix = "*100*";   // Pick from  problem list

        $rsdata = $oSolutionFact->getProblem($userid,$parentId,$rtFilterId);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $sfId           = $rs0->sfId;
                $sfIdString     = $oFunctionHelper->makePadString($sfId,10,"0"); 

                // *****0000000009*0000000000*0000000000*0000000000*1      problem
                //$sfIdValue      = $prefix.$sfIdString."*".$hasParentId."*".$sparentId."*0*".$sfIdString;
                $sfIdValue      = $prefix.$sfIdString."*0000000000*0000000000*0000000000*1";

                $sfFactText     = $rs0->sfFact;   
                $sfRating       = $rs0->sfRating; 
                $sfRelationId   = $rs0->sfRelationId;
                $shortText      = substr($sfFactText,0,24);
                $lang           = $rs0->sfLanguage;
  
                $solutionText[] = ['text'=>$sfFactText, 'shortText'=>$shortText, 
                'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,'elementOrder'=>"3", 'value'=>$sfIdValue ];  
            }
        }

        return $solutionText;
     }  



     //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                     */
    public function getValidationFactArray($userid,$validationKRId, $sfId, $bLang,$tLang)
     {

        // Instantiate classes
        $oSolutionFact      = new SolutionFact();
        $oFunctionHelper    = new FunctionHelper();       
        $solutionText       =  array();
        $hasParentId        = "0";                // no parent record
        $hasED              = 0;
        $sparentId          = $oFunctionHelper->makePadString(0,10,"0");  
        $isfound = 0;
        $button  = "button"; 
        $custom  = "";
        $lang =$bLang;
        $sfFactText = " xxxxxx Automobile pedestrian collison can cause injury";
        $shortText ="";
        $hasParentId = 1;       
        $sfFactText = $this->makeRelationText($validationKRId,$custom);

        $sfIdString     = $oFunctionHelper->makePadString($sfId,10,"0"); 
        $sfIdValue      = "*****".$sfIdString."*".$hasParentId."*".$sparentId;

        //$solutionText[] = ['text'=>$sfFactText, 'shortText'=>$shortText, 'language'=>$lang,
        //    'atttype'=>$button,'elementType'=>$button,'elementOrder'=>"3", 'value'=>$sfIdValue ];  

        $solutionText = ['text'=>$sfFactText, 'shortText'=>$shortText, 'language'=>$lang,
            'atttype'=>$button,'elementType'=>$button,'elementOrder'=>"3", 'value'=>$sfIdValue ];             

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
    /* value format= "*903*9999999999*0*0000000000"                               */
    public function getTransferLAButton($orgid, $portalType, $bLang, $tLang, $liveKrId=0)
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
        $transferText = ['text'=>$tTransfer , 'newLine'=>"1",  'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
             'elementOrder'=>"4", 'value'=>$soIdValue ];
        
        return $transferText;

     }

    //--------------------------------------------------------------------
    /* Make RPA transfer Button                                             */
    /* value format= "*903*9999999999*0*0000000000"                     */
    public function getRPAButton($orgid, $portalType, $bLang, $tLang, $krId, $RPAtype=0)
     {
        $oMessage         = new \App\Models\Message(); 
        $oFunctionHelper  = new FunctionHelper();
        $code   = 66;  // transfer to RPA 
        $tTransfer  = "";  
        $RPAText = ""; 
        $button   = "button";

        $krId = $oFunctionHelper->makePadString($krId,10,"0");

        switch($RPAtype) {

            case 4:

                $soIdValue = "*904*".$krId."*0*0000000000*0*0000000000";
                break;
            case 5:
                $soIdValue = "*905*".$krId."*0*0000000000*0*0000000000";
                break;

            default:
                $soIdValue = "*904*".$krId."*0*0000000000*0*0000000000";
                break;                
        }


        $rsB    = $oMessage->findMessage($code,$orgid,$bLang,$tLang);
        foreach ($rsB as $rs0){  
              $lang = $rs0->messageLanguage;   
              if ($portalType == "voice") {
                 $tTransfer = $rs0->messageVoice; 
              }  else {
                 $tTransfer = $rs0->messageText;                
              } 
        } 

        $RPAText = ['text'=>$tTransfer, 'newLine'=>"1",'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
           'elementOrder'=>"4", 'value'=>$soIdValue ];
        
        return $RPAText;

     }


    //--------------------------------------------------------------------
    /* Make RPA1 Exit Button Request                                     */
    /* value format= "*903*9999999999*0*0000000000"                     */
    public function getExitRPA1Button($orgid, $portalType, $bLang, $tLang, $krId, $newLine="0")
     {
        $oMessage  = new \App\Models\Message(); 
        $code      = 68;  // Exit RPA 
        $tTransfer = "";  
        $RPAText   = ""; 
        $zeros     = "0000000000";
        $button    = "button";
        $idValue   = "*954*".$zeros."*0*".$zeros."*0*".$zeros;  

        $rsB    = $oMessage->findMessage($code,$orgid,$bLang,$tLang);
        foreach ($rsB as $rs0){  
              $lang = $rs0->messageLanguage;   
              if ($portalType == "voice") {
                 $tTransfer = $rs0->messageVoice; 
              }  else {
                 $tTransfer = $rs0->messageText;                
              } 
        } 

        $ExitRPAText = ['text'=>$tTransfer, 'newLine'=>$newLine, 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
           'elementOrder'=>"6", 'value'=>$idValue ];
        
        return $ExitRPAText;

     }     

    //--------------------------------------------------------------------
    /* Make RPA1 Exit Button Request                                     */
    /* value format= "*903*9999999999*0*0000000000"                     */
    public function getExitRPA2Button($orgid, $portalType, $bLang, $tLang, $newLine="0")
     {
        $oMessage  = new \App\Models\Message(); 
        $code      = 68;  // Exit RPA 
        $tTransfer = "";  
        $RPAText   = ""; 
        $zeros     = "0000000000";
        $button    = "button";
        $idValue   = "*955*".$zeros."*0*".$zeros."*0*".$zeros;  

        $rsB    = $oMessage->findMessage($code,$orgid,$bLang,$tLang);
        foreach ($rsB as $rs0){  
              $lang = $rs0->messageLanguage;   
              if ($portalType == "voice") {
                 $tTransfer = $rs0->messageVoice; 
              }  else {
                 $tTransfer = $rs0->messageText;                
              } 
        } 

        $ExitRPAText = ['text'=>$tTransfer, 'newLine'=>"1", 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
           'elementOrder'=>"6", 'value'=>$idValue ];
        
        return $ExitRPAText;

     } 


    //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                     */
    public function insertValueRecord($lkRelationId, $lkRating, $lkleftId, $lkrtId, $lkrightId, $lang,
                   $inquiry, $utterance, $userid )
     {

        $oSolutionFact  = new SolutionFact();
        $sfFact         = "";
        $sBlank         = "";

        $lastFactId = $oSolutionFact->insertFact($sfFact, $lkRelationId, $lkRating, 
                    $lkleftId, $lkrtId, $lkrightId, $lang, 0, $sfFact, 0,
                    0, 0, 0,0, 0, 0, $inquiry, $utterance, $sBlank, 0, 0,   
                    0, $sBlank, 0, 0, 0, 0, $userid ); 

        return $lastFactId;        

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
                
        $rsdata = $oSolutionRelation->getByUserOrderDESCByRating($userid, $skipRel, $problemId);
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


                //

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
    /* get Review button                                                     */
    /* value format= "*****9999999999*0*0000000000"                         */
    public function getSolutionReviewButton($orgid,$portalType,$inText,$buttonType,$lang,$tLang)
     {

        $oFunctionHelper   = new FunctionHelper();
        $button    = "";
        $bText     = "";
        $button    = "button";
        $hasParentId = 1;
        $bValue    = "";
        $problemId  = substr($inText,5,10);
        $solutionId = substr($inText,16,10);

        $oMessage  = new \App\Models\Message();

        switch ($buttonType) {
            case "Risk":
                //$bValue = "*116*".$idString."*".$hasParentId."*".$parentId."*0*".$problemId; 
                $bValue = "*116*".$problemId."*".$solutionId."*"."0000000000"."*0000000000*3";

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
               // $bValue = "*117*".$idString."*".$hasParentId."*".$parentId."*0*".$problemId;; 
                $bValue = "*117*".$problemId."*".$solutionId."*"."0000000000"."*0000000000*3"; 

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
        
                //$bValue = "*118*".$idString."*".$hasParentId."*".$parentId."*0*".$problemId;  
                $bValue = "*118*".$problemId."*".$solutionId."*"."0000000000"."*0000000000*3"; 
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

                
        $button = ['text'=>$bText , 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button,
               'elementOrder'=>"3", 'value'=>$bValue ];  
      
        return $button;

     } 


    //--------------------------------------------------------------------
	/* get solution option: has risk  and make output                   */

    public function getOptionHasriskArray($orgid,$portalType,$userid, $inText, $problemId, $solutionId, $bLang,$tLang )
     {

        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
               
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        //$parentId          = substr($inText,5,10);   

        $parentId          = substr($inText,38,10);  

        if ($problemId == 0) {
            $problemId     = substr($inText,5,10); 
        }  else {
            $problemId     = $oFunctionHelper->makePadString($problemId,10,"0"); 
        } 

        if ($solutionId == 0) {
            $solutionId      = substr($inText,16,10); 
            $pickSolutionId  = (int)$solutionId;
        }  else {
            $pickSolutionId  = $solutionId;
            $solutionId      = $oFunctionHelper->makePadString($solutionId,10,"0"); 
        }   


        $lang              = $bLang; 
        $button            = "button";   
  
        $rsdata = $oSolutionOption->getOptionHasrisk($userid,$pickSolutionId,0);  

        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){              
                $soId           = $rs0->soId;
                $sosrId         = $rs0->sosrId;
                $sosrId         = $oFunctionHelper->makePadString($sosrId,10,"0"); 
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 

                //$soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;
                $soIdValue      = "*13**".$problemId."*".$solutionId."*".$soIdString."*0000000000*3";

                $soSolution     = $rs0->soSolution;      
                $soSolutionId   = $rs0->soSolutionId;
                $lang           = $rs0->soLanguage;
                $shortText      = $rs0->soShortText;

                $solutionText[] = ['text'=>$soSolution , 'newLine'=>"0", 'shortText'=>$shortText, 
                   'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 'elementOrder'=>"3",'value'=>$soIdValue ];                           
            }       
        } 
      
        return $solutionText;
     }	 


    //--------------------------------------------------------------------
  /* get solution option: requires and make output                    */
    /* value format= "*12**9999999999*1*9999999999"                     */
    public function getOptionRequiresArray($orgid,$portalType,$userid, $inText, $problemId, $solutionId, $bLang,$tLang)
    {
        
        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
               
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $parentId          = substr($inText,38,10);  

        if ($problemId == 0) {
            $problemId     = substr($inText,5,10); 
        }  else {
            $problemId     = $oFunctionHelper->makePadString($problemId,10,"0"); 
        } 

        if ($solutionId == 0) {
            $solutionId        = substr($inText,16,10); 
            $pickSolutionId    = (int)$solutionId;
        }  else {
            $pickSolutionId    = $solutionId;
            $solutionId        = $oFunctionHelper->makePadString($solutionId,10,"0"); 
        }       
       
        //*****0000000001*0000000002*0000000013*0000000012*4       

        $lang              = $bLang; 
        $button            = "button";   
  
        $rsdata = $oSolutionOption->getOptionRequires($userid,$pickSolutionId,0);  

        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){              
                $soId           = $rs0->soId;
                $sosrId         = $rs0->sosrId;
                $soParentId     = $rs0->soParentId; 
                $sosrId         = $oFunctionHelper->makePadString($sosrId,10,"0"); 
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 
                if ($soParentId == 0) {
                    $level = 3;
                } else {
                    $level = 4;
                }

                //$soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;
                $soIdValue      = "*13**".$problemId."*".$solutionId."*".$soIdString."*0000000000*".$level;

                $soSolution     = $rs0->soSolution;      
                $soSolutionId   = $rs0->soSolutionId;
                $lang           = $rs0->soLanguage;
                $shortText      = $rs0->soShortText;

                $solutionText[] = ['text'=>$soSolution , 'newLine'=>"0", 'shortText'=>$shortText, 
                   'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 'elementOrder'=>"3",'value'=>$soIdValue ];
            }       
        } 
      
        return $solutionText;
    }  



    //--------------------------------------------------------------------
    /* get solution option: has option and make output                  */
    /* value format= "*13**9999999999*1*9999999999"                     */

    public function getOptionHasoptionArray($orgid,$portalType,$userid, $inText, $problemId, 
          $solutionId, $optionId, $bLang,$tLang, $oplevel=1)

    {
       // *****0000000001*0000000002*0000000013*0000000012*4 
        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
               
        $solutionText      =  array();

        if ($problemId == 0) {
            $problemId     = substr($inText,5,10); 
        }  else {
            $problemId     = $oFunctionHelper->makePadString($problemId,10,"0"); 
        } 

        if ($solutionId == 0) {
            $solutionId        = substr($inText,16,10); 
            $pickSolutionId    = (int)$solutionId;
        }  else {
            $pickSolutionId    = $solutionId;
            $solutionId        = $oFunctionHelper->makePadString($solutionId,10,"0"); 
        }

        $lang              = $bLang; 
        $button            = "button";   

        //if ($optionId == 0) {
        //   $rsdata = $oSolutionOption->getOptionHasoption($userid,$pickSolutionId,0);           
        //} else {
           $rsdata = $oSolutionOption->getOptionHasoption($userid,$pickSolutionId,$optionId);          
        //}

        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){              
                $soId           = $rs0->soId;
                $sosrId         = $rs0->sosrId;
                $sosrId         = $oFunctionHelper->makePadString($sosrId,10,"0"); 
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 

                //$soIdValue      = "*13**".$problemId."*".$solutionId."*".$soIdString."*0000000000*3";
                $soIdValue      = "*13**".$problemId."*".$solutionId."*".$soIdString."*0000000000*3";

                $soSolution     = $rs0->soSolution;      
                $soSolutionId   = $rs0->soSolutionId;
                $lang           = $rs0->soLanguage;
                $shortText      = $rs0->soShortText;
                $solutionText[] = ['text'=>$soSolution , 'newLine'=>"0", 'shortText'=>$shortText, 
                   'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 'elementOrder'=>"3",'value'=>$soIdValue ];                           
            }       
        } 
      
        return $solutionText;

    }    

    //--------------------------------------------------------------------
    /* get solution option: has option and make output                  */
    /* value format= "*13**9999999999*1*9999999999"                     */

    public function getOptionHasoptionDeepArray($orgid,$portalType,$userid, $inText, $problemId, 
          $solutionId, $optionId, $parentId,$bLang,$tLang, $oplevel=1)

    {
       // *****0000000001*0000000002*0000000013*0000000012*4 
        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
               
        $solutionText      =  array();

        $lang              = $bLang; 
        $button            = "button";   

//        if ($oplevel == 1) {
//           $rsdata = $oSolutionOption->getOptionHasoption($userid,$pickSolutionId,0);           
//        } else {
//           $rsdata = $oSolutionOption->getOptionHasoption($userid,0,$pickSolutionId);          
//        }

        $rsdata = $oSolutionOption->getOptionHasoption($userid,$solutionId, $optionId);   

        $problemId   = $oFunctionHelper->makePadString($problemId,10,"0"); 
        $solutionId  = $oFunctionHelper->makePadString($solutionId,10,"0"); 
        $optionId    = $oFunctionHelper->makePadString($optionId,10,"0"); 

        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){              
                $soId           = $rs0->soId;
                $sosrId         = $rs0->sosrId;
                $sosrId         = $oFunctionHelper->makePadString($sosrId,10,"0"); 
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 

                //$soIdValue      = "*13**".$problemId."*".$solutionId."*".$soIdString."*0000000000*3";
                $soIdValue      = "*13**".$problemId."*".$solutionId."*".$soIdString."*0000000000*3";

                $soSolution     = $rs0->soSolution;      
                $soSolutionId   = $rs0->soSolutionId;
                $lang           = $rs0->soLanguage;
                $shortText      = $rs0->soShortText;
                $solutionText[] = ['text'=>$soSolution , 'newLine'=>"0", 'shortText'=>$shortText, 
                   'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 'elementOrder'=>"3",'value'=>$soIdValue ];                           
            }       
        } 
      
        return $solutionText;

    }    


    //---------------------------------------------------------------------
    /*  Get Back button
        Level 0
              1
              2    ***21   
              3
              4    ***22   
    
    */ 

        public function getNewBackButton($level, $inText, $problemId,$solutionId,$optionId,
               $portalType, $orgid,$lang,$tLang, $newLine="0")
        {

         /* Levels of response data
             1  Problem list
             2  Solution relation list
             3  Option level 1 list
             4  Option level 2 list
             5  Option leveñ >2 list
         */


          $oFunctionHelper   = new FunctionHelper();
          $oMessage          = new \App\Models\Message(); 
          $backButtonText    = "";
          $button            = "button";
/*          
          if ($problemId == 0) {
              $problemId  = substr($inText,5,10);
          } else {
              $problemId = $oFunctionHelper->makePadString($problemId,10,"0"); 
          }

          if ($solutionId == 0) {
              $solutionId = substr($inText,16,10);
          } else {
              $solutionId = $oFunctionHelper->makePadString($solutionId,10,"0"); 
          }

          if ($optionId == 0) {
              $optionId = substr($inText,27,10);
          } else {
              $optionId = $oFunctionHelper->makePadString($optionId,10,"0"); 
          }          
*/ 
          $problemId  = $oFunctionHelper->makePadString($problemId,10,"0"); 
          $solutionId = $oFunctionHelper->makePadString($solutionId,10,"0"); 
          $optionId   = $oFunctionHelper->makePadString($optionId,10,"0");

          $sLevel = strval($level);
          $idValue  = $problemId."*".$solutionId."*".$optionId."*0000000000*".$sLevel;


          switch ($level) { 


              case 0:
                // Go back to: click solution button button
                //$idValue  = "***19".$idString."*".$hasParentId."*".$parentId."*1*".$problemId;
                $idValue  = "***19".$idValue;
              break;

              case 1:
                // Go back to: click solution button button
                //$idValue  = "*****".$idString."*".$hasParentId."*".$parentId."*1*".$problemId;
                $idValue  = "*****".$idValue;
              break;

              case 2:
                // From level 2 to level 1  
                //$idValue  = "***21".$problemId."*".$hasParentId."*".$parentId."*2*".$problemId;
                $idValue  = "***21".$idValue;
              break;              

              case 3:
                // From level 3 to level 2
                //$idValue  = "***22".$idString."*".$hasParentId."*".$parentId."*3*".$problemId;
                $idValue  = "***22".$idValue;
              break;

              case 4:
                // From level 4 to level 3
                //$idValue  = "***23".$idString."*".$hasParentId."*".$parentId."*4*".$problemId;
                $idValue  = "***23".$idValue;
              break;

              case 5:
                // From level 5 to level 4
                //$idValue  = "***24".$idString."*".$hasParentId."*".$parentId."*5*".$problemId;
                $idValue  = "***24".$idValue;
              break;

              case 116:
                // Pick from Review Associated risks
                //$idValue  = "*116*".$idString."*".$hasParentId."*".$parentId."*3*".$problemId;
                $idValue  = "*116*".$idValue;
              break;

              case 117:
                // Pick from Review Associated requires
                //$idValue  = "*117*".$idString."*".$hasParentId."*".$parentId."*3*".$problemId;
                $idValue  = "*117*".$idValue;
              break;

              case 118:
                // Pick from Review Associated options
                //$idValue  = "*118*".$idString."*".$hasParentId."*".$parentId."*3*".$problemId;
                $idValue  = "*118*".$idValue;
              break;

              default:
                //$idValue  = "***19".$idString."*".$hasParentId."*".$parentId."*3*".$problemId;
                $idValue  = "***19".$idValue;
              break;

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
          $backButtonText = ['text'=>$tBack, 'newLine'=>$newLine, 'newLine'=>"1", 'language'=>$lang, 'atttype'=>$button,
               'elementType'=>$button, 'elementOrder'=>"6",'value'=>$idValue ]; 
          return $backButtonText;
      }


    //---------------------------------------------------------------------
    /*  Get New Exit button
    
    */ 
        public function getNewExitButton($level,$inText,$portalType, $orgid,$lang,$tLang,$newLine="0")
        {

          $oFunctionHelper   = new FunctionHelper();
          $oMessage          = new \App\Models\Message(); 

          $exitButtonText    = "";
          $button            = "button";
         
          $hasParentId       = 0; 
          //$idValue           = "*899*".$idString."*".$hasParentId."*".$parentId."*0*".$problemId;
          $idValue           = substr($inText,5);
          $idValue           = "*899*".$idValue;

          $code   = 52; 
          $tExit  = "";      
          $rsB    = $oMessage->findMessage($code,$orgid,$lang,$tLang);

          foreach ($rsB as $rs0){  
              $lang = $rs0->messageLanguage;   
              if ($portalType == "voice") {
                  $tExit = $rs0->messageVoice; 
              }  else {
                  $tExit = $rs0->messageText;                
              } 
          } 
          $exitButtonText = ['text'=>$tExit, 'newLine'=>$newLine, 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
                    'elementOrder'=>"7",'value'=>$idValue ]; 
          return $exitButtonText;      
        } 



    //--------------------------------------------------------------------
    // get Line break 
    public function getLineBreak($n)
     {

        $customText = "";
        for ($i=0;$i<$n;$i++) {
            $customText = $customText."&nbsp;";
        }

        $idValue    = "*****0000000000"; 
        $button     = "text";
        $comment    = "comment";
        $sfAttTypeName = "text";
        $lang       = "en";     

        $newText = ['text'=>$customText ,  'language'=>$lang, 'atttype'=>$button, 
               'elementType'=>$comment, 'elementOrder'=>"6",'value'=>$idValue  ];   

        return $newText;

     }

    //--------------------------------------------------------------------
    /* make Custom text                                                 */
    /* elementorder = 2                                                 */
    /* value format= "??????"                     */
    public function getCustomText($customText, $portalType, $orgid,$lang,$tLang,$smType, $newLine="0")
     {

        $idValue  = "*****0000000000"; 
        $button   = "button";
        $comment  = "comment";

        $sfAttTypeName = "text";
        $newText = ['text'=>$customText, 'newLine'=>$newLine,'language'=>$lang,  'atttype'=>$sfAttTypeName, 
               'elementType'=>$comment, 'elementOrder'=>"2",'value'=>$idValue  ];                    

        return $newText;

     }


    //--------------------------------------------------------------------
    /* make Custom button                                                */
    /* elementorder = 4                                                 */

    public function getCustomButton($aButton,$lang,$newLine="0")
     {

        $button   = "button";  
        $bText    = $aButton['text'];
        $bValue   = $aButton['value'];
 
        $buttonText = ['text'=>$bText, 'newLine'=>$newLine, 'language'=>$lang, 'atttype'=>$button,
             'elementType'=>$button, 'elementOrder'=>"4",'value'=>$bValue ];

        return $buttonText;
     }

    //--------------------------------------------------------------------
    /* make Yes / No validation button                                  */
    /* elementorder = 4                                                 */
    /* Yes value format= "*360*+ sfId"                                 */

    public function getButtonYes($sfId,$parentId, $code, $portalType, $orgid,$lang,$tLang,$smType, $newLine="0")
     {

        $oMessage          = new \App\Models\Message(); 
        $oFunctionHelper   = new FunctionHelper();

        $bText   = "";  
        $button  = "button";  
        $sId1    = $oFunctionHelper->makePadString($sfId,10,"0"); 
        $sId2    = $oFunctionHelper->makePadString($parentId,10,"0"); 
        $idValue = "*360*".$sId1."*1*".$sId2."*0*".$sId2;

        $rsB      = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
            $lang = $rs0->messageLanguage;   
            if ($portalType == "voice") {
               $bText = $rs0->messageVoice; 
            }  else {
               $bText = $rs0->messageText;                
            } 
        } 
        $buttonText = ['text'=>$bText, 'newLine'=>$newLine, 'language'=>$lang, 'atttype'=>$button,
             'elementType'=>$button, 'elementOrder'=>"4",'value'=>$idValue ];

        return $buttonText;

     }


    //--------------------------------------------------------------------
    /* make Yes / No validation button                                  */
    /* elementorder = 4                                                 */
    /* NO  value format= "*366*+ sfId"                                 */
    public function getButtonNo($sfId, $parentId,$code, $portalType, $orgid,$lang,$tLang,$smType, $newLine="0")
     {

        $oMessage          = new \App\Models\Message(); 
        $oFunctionHelper   = new FunctionHelper();

        $bText   = "";  
        $button  = "button";  
        $sId1    = $oFunctionHelper->makePadString($sfId,10,"0"); 
        $sId2    = $oFunctionHelper->makePadString($parentId,10,"0"); 
        $idValue = "*366*".$sId1."*1*".$sId2."*0*".$sId2;

        $rsB      = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
            $lang = $rs0->messageLanguage;   
            if ($portalType == "voice") {
               $bText = $rs0->messageVoice; 
            }  else {
               $bText = $rs0->messageText;                
            } 
        } 
        $buttonText = ['text'=>$bText, 'newLine'=>$newLine, 'language'=>$lang, 'atttype'=>$button,
             'elementType'=>$button, 'elementOrder'=>"4",'value'=>$idValue ];

        return $buttonText;

     }

    //--------------------------------------------------------------------
    /* Make custom messsage                                              */
    public function getCustomMessage($customText,$lang)
     {

        $attribute = "standardmessage"; 

        $aMsg= ['attribute'=>$attribute , 'language'=>$lang , 'value'=>$customText ]; 

        return $aMsg;

     }

    //--------------------------------------------------------------------
    /* Make Continue button in problem inference                      */
    /* elementorder = 4                                                 */
    /* Yes value format= "*356*+ sfId"                                 */

    public function getButtonContinue($sfId,$parentId, $code, $portalType, $orgid,$lang,$tLang,$smType, $newLine="0")
     {

        $oMessage          = new \App\Models\Message(); 
        $oFunctionHelper   = new FunctionHelper();

        $bText   = "";  
        $button  = "button";  
        $sId1    = $oFunctionHelper->makePadString($sfId,10,"0"); 
        $sId2    = $oFunctionHelper->makePadString($parentId,10,"0"); 
        $idValue = "*356*".$sId1."*1*".$sId2."*0*".$sId2;

        $rsB      = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
            $lang = $rs0->messageLanguage;   
            if ($portalType == "voice") {
               $bText = $rs0->messageVoice; 
            }  else {
               $bText = $rs0->messageText;                
            } 
        } 
        $buttonText = ['text'=>$bText, 'newLine'=>$newLine, 'language'=>$lang, 'atttype'=>$button,
             'elementType'=>$button, 'elementOrder'=>"4",'value'=>$idValue ];

        return $buttonText;

     }


    //--------------------------------------------------------------------
    /* Make Continue button in problem inference                      */
    /* elementorder = 4                                                 */
    /* Yes value format= "*357*+ sfId"                                 */

    public function getButtonIPBack($sfId,$parentId, $code, $portalType, $orgid,$lang,$tLang,$smType, $newLine)
     {

        $oMessage          = new \App\Models\Message(); 
        $oFunctionHelper   = new FunctionHelper();

        $bText   = "";  
        $button  = "button";  
        $sId1    = $oFunctionHelper->makePadString($sfId,10,"0"); 
        $sId2    = $oFunctionHelper->makePadString($parentId,10,"0"); 
        $idValue = "*357*".$sId1."*1*".$sId2."*0*".$sId2;

        $rsB      = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
            $lang = $rs0->messageLanguage;   
            if ($portalType == "voice") {
               $bText = $rs0->messageVoice; 
            }  else {
               $bText = $rs0->messageText;                
            } 
        } 
        $buttonText = ['text'=>$bText, 'newLine'=>$newLine, 'language'=>$lang, 'atttype'=>$button,
             'elementType'=>$button, 'elementOrder'=>"4",'value'=>$idValue ];

        return $buttonText;

     }


    //--------------------------------------------------------------------
    /* Make Back button in YES / NO problem inference validation        */
    /* elementorder = 4                                                 */
    /* Yes value format= "*358*+ sfId"                                  */

    public function getButtonYNBack($sfId,$parentId, $code, $portalType, $orgid,$lang,$tLang,$smType, $newLine="0")
     {

        $oMessage          = new \App\Models\Message(); 
        $oFunctionHelper   = new FunctionHelper();

        $bText   = "";  
        $button  = "button";  
        $sId1    = $oFunctionHelper->makePadString($sfId,10,"0"); 
        $sId2    = $oFunctionHelper->makePadString($parentId,10,"0"); 
        $idValue = "*358*".$sId1."*1*".$sId2."*0*".$sId2;

        $rsB      = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
            $lang = $rs0->messageLanguage;   
            if ($portalType == "voice") {
               $bText = $rs0->messageVoice; 
            }  else {
               $bText = $rs0->messageText;                
            } 
        } 
        $buttonText = ['text'=>$bText, 'newLine'=>$newLine, 'language'=>$lang, 'atttype'=>$button,
             'elementType'=>$button, 'elementOrder'=>"4",'value'=>$idValue ];

        return $buttonText;

     }

    //--------------------------------------------------------------------
    /* make Exit button                                                 */
    /* elementorder = 4                                                 */
    /* Yes value format= "*360*+ sfId"                                 */

    public function getButtonExit($sfId,$parentId, $code, $portalType, $orgid,$lang,$tLang,$smType, $newLine="0")
     {

        $oMessage          = new \App\Models\Message(); 
        $oFunctionHelper   = new FunctionHelper();

        $bText    = "";  
        $button   = "button";  
        $sId1    = $oFunctionHelper->makePadString($sfId,10,"0"); 
        $sId2    = $oFunctionHelper->makePadString($parentId,10,"0"); 
        $idValue = "*899*".$sId1."*1*".$sId2."*0*".$sId2;

        $rsB      = $oMessage->findMessage($code,$orgid,$lang,$tLang);
        foreach ($rsB as $rs0){  
            $lang = $rs0->messageLanguage;   
            if ($portalType == "voice") {
               $bText = $rs0->messageVoice; 
            }  else {
               $bText = $rs0->messageText;                
            } 
        } 
        $buttonText = ['text'=>$bText, 'newLine'=>$newLine, 'language'=>$lang, 'atttype'=>$button,
             'elementType'=>$button, 'elementOrder'=>"7",'value'=>$idValue ];

        return $buttonText;

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
        $soIdValue      = "*112*".$soIdString."*".$hasParentId."*".$parentId;     
        $solutionText[] = ['text'=>$tBack  , 'language'=>$lang , 'atttype'=>$button,'elementType'=>$button, 
                 'elementOrder'=>"5",'value'=>$soIdValue ];  
      
        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*899*".$soIdString."*".$hasParentId."*".$parentId;       
        $solutionText[] = ['text'=>$tExit  , 'language'=>$lang , 'atttype'=>$button,'elementType'=>$button,
                 'elementOrder'=>"5",'value'=>$soIdValue];
    
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

        $zeroString     = $oFunctionHelper->makePadString(0,10,"0"); 

        if($midState == 0) {
            //$soIdValue      = "*****".$parentId."*".$hasParentId."*".$zeroString.$parentId2.$opsource; 
            $soIdValue      = "*****".$parentId."*".$hasParentId."*".$zeroString."*".$opsource."*".$problemId;   
        } else {

            if ($isBack == 2) {
                $hasParentId = 1;
                //$soIdValue      = "*134*".$parentId."*".$hasParentId."*".$parentId.$parentId2.$opsource; 
                $soIdValue      = "*134*".$parentId."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId; 
            } 
            if ($isBack == 1 ) {
                $hasParentId = 1;
          //    $soIdValue      = "*13**".$parentId."*".$hasParentId."*".$parentId.$parentId2.$opsource; 
                $soIdValue      = "*13**".$parentId."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId; 

            } 

            if ($isBack == 4) {  // return to review requirements
                $hasParentId = 1;  
            //  $soIdValue      = "*117*".     $zeros."*".$hasParentId."*".$parentId2.$parentId2."2"; 
                $soIdValue      = "*117*".$zeros."*".$hasParentId."*".$parentId."*2*".$problemId;
            } 

            if ($isBack == 5) {
                $hasParentId = 0;
                //$soIdValue      = "*****".$parentId."*".$hasParentId."*".$parentId.$parentId2.$opsource; 
                $soIdValue      = "*****".$parentId."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId;  
       
            } 

            if ($isBack == 15) {
                $hasParentId = 0;
                //$soIdValue      = "*****".$parentId."*".$hasParentId."*".$parentId.$parentId2.$opsource;
                $soIdValue      = "*****".$parentId."*".$hasParentId."*".$parentId."*".$opsource."*".$problemId;  
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
                //$soIdValue      = "*13**".$parentId."*".$hasParentId."*".$zeros.$parentId2.$opsource; 
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
                 'elementOrder'=>"5",'value'=>$soIdValue ];  
        }
         
        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*899*".$soIdString."*".$hasParentId."*".$parentId; 

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
             'elementOrder'=>"6",'value'=>$soIdValue];
        
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
             'elementOrder'=>"5",'value'=>$soIdValue ];  


        if ($hasExit == 1) {     
          $soIdValue      = "*899*".$zeros."*".$hasParentId."*".$zeros; 

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
                'elementOrder'=>"6",'value'=>$soIdValue];

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
              'elementOrder'=>"5",'value'=>$soIdValue ];  


        if ($hasExit == 1) {     
          $soIdValue      = "*899*".$zeros."*".$hasParentId."*".$zeros; 

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
               'elementOrder'=>"6",'value'=>$soIdValue];

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
                  'elementOrder'=>"5", 'value'=>$soIdValue ];         
        }

        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*899*".$soIdString."*".$hasParentId."*".$parentId."*0*".$problemId;  

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
             'elementOrder'=>"6",'value'=>$soIdValue];

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
               'elementOrder'=>"5",'value'=>$soIdValue ];  
     
        $soIdValue      = "*899*".$soIdString."*".$hasParentId."*".$parentId;   

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
             'elementOrder'=>"6",'value'=>$soIdValue];
    
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
           

                $solutionText[] = ['text'=>$soSolution , 'newLine'=>"0", 'shortText'=>$shortText,'language'=>$lang,
                      'atttype'=>$button ,'elementType'=>$button, 'elementOrder'=>"3",'value'=>$soIdValue ];                                
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
                 'elementOrder'=>"5", 'value'=>$soIdValue ];  
      
            $soIdValue      = "*899*".$soIdString."*".$hasParentId."*".$parentId; 

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
                'elementOrder'=>"6",'value'=>$soIdValue];
        }
    
        return $solutionText;

    }     

/// NEW //////////////////////
    //--------------------------------------------------------------------
    /* get solution option: has option and make output                  */
    /* value format= "*13**9999999999*1*9999999999"                     */
    public function getNewOptionArray($level, $orgid, $portalType,$userid, $optionNumber,$lang,$tLang,$problemId)
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
           

                $solutionText[] = ['text'=>$soSolution , 'newLine'=>"0", 'shortText'=>$shortText,'language'=>$lang,
                      'atttype'=>$button ,'elementType'=>$button, 'elementOrder'=>"3",'value'=>$soIdValue ];                                
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
                 'elementOrder'=>"5", 'value'=>$soIdValue ];  
      
            $soIdValue      = "*899*".$soIdString."*".$hasParentId."*".$parentId; 

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
                'elementOrder'=>"6",'value'=>$soIdValue];
        }
    
        return $solutionText;

    }     


    //--------------------------------------------------------------------
    public function getHandoffMessage($handoffMessage, $lang)
    /*   Build handoff message array.
     */
     {

        $hMessage = ['text'=>$handoffMessage , 'language'=>$lang , 
                    'attname'=>"RPA message", 'atttype'=>"text", 'elementType'=>"comment",'elementOrder'=>'1', 
                     'value'=>"****90000000000*0*0000000000*0*0000000000" ];

        return $hMessage;
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
                     'value'=>"*****0000000000*0*0000000000*0*0000000000" ];

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
    public function getSlideBarArray1($userid,$personaId,$relationId,$isLinked,$orgId,$tLang,$bLang, $pickProblemId=0)
     {

        $oSolutionFact  = new SolutionFact();
        $oRelationLink  = new RelationLink();
        $oRTFilter      = new RelationTypeFilter();
        $oPR            = new PersonalityRelation();
        $oPRV           = new PersonalityRelationValue();
        $oFunction      = new FunctionHelper();
        $oTerm          = new Term();
        $slideText      = array();
        $step           = 10;  // term "has value rating KR"
        $KR1            = 0;
        $KR2            = 0;
        $sbCount        = 0;

        if ($isLinked == 1) {  // a linked problem
            $KR1 = $relationId;
        } else {
            // Get relationId that has negative ratings from solutin_fact       
            $rs = $oSolutionFact->getNegativeRatingByUser($userid, $pickProblemId);
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
            $slideText = array();
        } else {
            $sortField = "name";
            $slideText = $oFunction->sortArray($slideText, $sortField, $reverse=false);
        }

        return $slideText;
     }     

//
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
    public function getSlideBarArray2($userid,$portalType,$orgid,$lang,$tLang,$problemId)
     {

        $oFunctionHelper = new FunctionHelper();
        $oMessage        = new \App\Models\Message(); 
        $buttonText      =  array();   
        $button          = "button"; 
        $problemId       = $oFunctionHelper->makePadString($problemId,10,"0");
        //$idValue         = "*301*0000000009*0*0000000000*0*0000000000";


        $tSkip  = "";
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

        $idValue         = "*301*".$problemId."*0*"."0000000000*0*".$problemId;
        $buttonText[] = ['text'=>$tSkip, 'newLine'=>"1", 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
            'elementOrder'=>"5", 'value'=>$idValue];

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

        $idValue         = "*302*".$problemId."*0*"."0000000000*0*".$problemId;
        $buttonText[] = ['text'=>$tSave, 'newLine'=>"0", 'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
             'elementOrder'=>"5", 'value'=>$idValue];

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
        $sfIdValue        = "*****".$parentId;
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

    public function getSolRelExtDataArray($userid,$orgid,$pickSolutionId, $inText, 
           $hasReturn, $bLang, $tLang, $portalType )
     {

        $oSolutionRelation= new SolutionRelation();
        $oSolRelExdata    = new SolutionRelationExdata();
        $oFunctionHelper   = new FunctionHelper();
        $oMessage          = new \App\Models\Message(); 
        $solutionText      =  array();
        $hasParentId       = "0";                // there is parent record
        //$parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $parentId = 0;
        $srIdValue        = $inText;
        $button           = "button";
        $comment          = "comment";
        $lang             = $bLang;

       //	$srAttText = "text";

        // get solution relation extended data
        $rsdata = $oSolRelExdata->getChildrenData($pickSolutionId); 
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
                    $solutionText[] = ['text'=>$srChatIntro, 'newLine'=>"0",'language'=>$lang, 'attname'=>$attName, 
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

                $solutionText[] = ['text'=>$srSolution, 'newLine'=>"0", 'language'=>$lang, 'attname'=>$attName,
                  'atttype'=>$srAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$srIdValue  ];  

            }                    
        }           
        
        return $solutionText;

     }

    //--------------------------------------------------------------------
    /* get solution OPTION extended data array                          */
    public function getSolOptExtDataArray($userid,$orgid,$optionId,$optionNumber,
       $inText, $bLang, $tLang, $portalType)
     {

        $oSolutionOption  = new SolutionOption();
        $oSolOptExdata    = new SolutionOptionExdata();
        $oFunctionHelper  = new FunctionHelper();
        $oMessage         = new \App\Models\Message();         
        $solutionText     =  array();
        $hasParentId      = "0";                // there is parent record
        $parentId         = $oFunctionHelper->makePadString($optionNumber,10,"0");
        $soIdValue        = $inText;
        $solutionText     = array();
        $button           = "button";
        $comment          = "comment";
        $lang             = $bLang;

        // get solution option extended data
        $rsdata = $oSolOptExdata->getChildrenData($optionId); 
        foreach ($rsdata as $rs0){   
            $soSolution    = $rs0['soedValueString'];   
            $soChatIntro   = $rs0['soedChatIntro'];   
            $attName       = $rs0['soedAttributeName'];            
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
                $solutionText[] = ['text'=>$soSolution ,'language'=>$lang,'attname'=>$attName, 
                 'atttype'=>$soAttTypeName, 'elementType'=>$comment, 'elementOrder'=>"1",'value'=>$soIdValue  ];    

            }                    
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
            'elementOrder'=>"5",'value'=>$soIdValue ];          
            
        $soIdValue      = "*899*".$idString."*".$hasParentId."*".$idString;    

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
             'elementOrder'=>"6",'value'=>$soIdValue];
        
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
              'elementOrder'=>"5",'value'=>$soIdValue ];          
       
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

        $soIdValue      = "*899*".$idString."*".$hasParentId."*".$idString; 

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
              'elementOrder'=>"6",'value'=>$soIdValue ];          
            
        
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

        $soIdValue      = "*899*".$idString."*".$hasParentId."*".$idString;               
        $solutionText[] = ['text'=>$tExit,'language'=>$lang, 'atttype'=>$button,'elementType'=>$button, 
            'elementOrder'=>"6",'value'=>$soIdValue];
        
        return $solutionText;

     }


    //--------------------------------------------------------------------
    /* get has options value from solution_option                       */
    public function getHasOptions($userid, $pickSolutionId)
     {

        $oSolutionOption  = new SolutionOption();
        $hasSolRisk     = 0;
        $hasSolReq      = 0;
        $hasSolOpt      = 0;

        // get all options for this user   
        $rsd = $oSolutionOption->getByUserSosrid($userid,$pickSolutionId);
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


    //--------------------------------------------------------------------
    /*  Reverse valoidated inferred problem
        Reverse from one record before the problemId until the end
    */
    public function reverseValidation($userid, $parentId, $pickProblemId)  
     {
        $oSolutionFact = new SolutionFact();    
        $rs            = $oSolutionFact->getInferredProblemDesc($userid, $parentId);
        $idFound       = 0;
        $zero          = 0;
        $count         = 0;

        // get data from inferred problem
        foreach($rs as $rs0) {
            $sfId       = $rs0->sfId;
            $sfRating   = $rs0->sfRating;

            if ($count < 2) {
                $oSolutionFact->updateValidation($sfId, $zero);               
            }

            if ($sfId == $pickProblemId and $idFound == 0) {
                $idFound = 1;
            }
  
            if ($idFound == 1) {
               $count++;
            }


        }
        
        return true;

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
      *899*8888888888*1*9999999999   *110*       55    option HasRisk exit
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
               $state = 105;    // 
            break;

            case "***19":      // go to level 0 problem list
               $state = 19;    // 
            break;

            case "***21":      // go to level 1 problem list
               $state = 22;    // 19
            break;

            case "***22":      // go to level 2. Use picked problemId
               $state = 29;    // 28 
            break;

            case "***23":      // go to level 3. Use soParentId
               $state = 30;   // 30
            break;

            case "***24":      // go to level 4. Use soParentId
               $state = 31;    // 
            break;

            case "*11**":      // is pick from option  has risk list
               $state = 120;   //50;
            break;      
      
            case "*100*":      // is pick from problem list
               $state = 100;    // 22
            break;        

            case "*111*":      // is pick from option  has risk Continue
               $state = 78;
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
               $state = 130;   // 58;  
            break;

            case "*121*":      // is pick from option requires Continue
               $state = 90; 
            break; 

            case "*13**":      // is pick from option has option list
               $state = 140;   // 66 in release 2.7      
            break;

            case "*134*":      // is pick from option has option list
               $state = 67;   
            break;

            case "*131*":      // is pick from option has option Continue
               $state = 102;  // 38;
            break;

            case "*14**":      // is pick from preprocessign subset list
               $state = 122;  // 61;
            break;  

            case "*116*":      // is pick from button Review associated Risks
               $state = 110;    
            break;  

            case "*117*":      // is pick from button Review associated Requires
               $state = 111;   
            break;  

            case "*118*":      // is pick from button Review associated Options
               $state = 112;   
            break;

            case "*119*":      // return to solution screen
               $state = 34;    
            break;

            case "*159*":      // prompt for problem validation
               $state = 19;  
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

            case "*356*":      // Process CONTINUE in problem validation
                $state = 356;  // 
                break;

            case "*357*":      // Process (Continue) BACK  in problem validation
                $state = 357;  // Return to validate same problem
                break;                

            case "*358*":      // Process (Yes / No) BACK  in problem validation
                $state = 358;  // Return to validate previuos problem
                break;         

            case "*360*":      // Process YES inferred problem validation
                $state = 360;  // 
                break;

            case "*366*":      // Process NO inferred problem validation
                $state = 366;  // 
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

            case "*871*":      // RPA dialog mode. Elicit slot
                $state = 871;  // 
                break;

            case "*899*":      //  Exit button
               //$state = 818;  
                $state = 899; 
                break;

            case "*902*":      // handoff to Kaas
                $state = 305;  // 
                break;

            case "*901*":      // handoff to live agent  *901*
                $state = 759;  // 
                break;

            case "*904*":      // handoff to RPA1
                //$state = 540;  // 
                //$state = 307;  // 
                $state = 870;  // 
                break;

            case "*905*":      // handoff to RPA2
                $state = 550;  // 
                break;

            case "*954*":      // Exit RPA1 button 
                $state = 954;  // 
                break;                

            case "*955*":      // RPA2 Exit Button Request 
                $state = 955;  // RPA2 EXit button clicked
                break; 

        }
      
        return $state;

    }		 
	 

  //----------------------------------------------------------	
	
}