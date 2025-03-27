<?php
/*--------------------------------------------------------------------------------
 *  File          : AmazonInferenceHelper.php        
 *  Type          : Helper class
 *  Function      : Provide functions for finding solutions in the chatbot
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Methods       : makeSolutionFact(), makeFactRating(), calculateRelationRating(),   
 *                  makeSolution(), makeRelationText,  getSolutionRelationString(),
 *                  getSolutionRelationArray()
 *  Version       : 0.3.1
 *
 *---------------------------------------------------------------------------------*/
namespace App\Helpers;

use Illuminate\Http\Request;
use App\Controllers;
use App\Models\Term;
use App\Models\Personality;
use App\Models\PersonalityTrait;
use App\Models\PersonalityValue;
use App\Models\PersonalityRelation;
use App\Models\PersonalityRelationValue;
use App\Models\RelationTypeSynonym;
use App\Models\SolutionFact;
use App\Models\SolutionRelation;
use App\Models\SolutionOption;
use App\Models\RelationType;
use App\Models\Relation;
use App\Models\RelationLink;
use App\Models\RelationTypeFilter;
use App\Models\ConsumerUserPersonality;
use App\Helpers\FunctionHelper;

class AmazonInferenceHelper
{
	
    //-------------------------------------------------------------------
    /*   Function: getLatgestIE
     *   Purpose : To get the largest initial expression from a text.
     *             It is the largest expression that can be found in   
     *             table term. The comparsion is input text ($text1) against
     *             the term name (termName).
     *   input   : string $text1         
     *   output  : string $largestIE;
     *-------------------------------------------------------------------*/


    //--------------------------------------------------------------------
    public function makeSolutionFact($aSplitText, $userid, $inquiry)
     {	
        // Instantiate classes
        $oTerm                = new Term();
        $oRelation            = new Relation();
        $oRelationTypeSynonym = new RelationTypeSynonym();
        $oRelationType        = new RelationType();
        $oSolutionFact        = new SolutionFact();
        $oFunctionHelper      = new FunctionHelper();

        // Get relation type "is a member of"
        $auxRelTypeName = "is a member of";
        $auxRelTypeId   = $oRelationType->retrieveIdByName($auxRelTypeName);
 
        // Get term id for "verb"
        $auxTermName = "verb";
        $auxTermId   = $oTerm->retrieveTermIdByName($auxTermName);       

		
        $arraylen = sizeof($aSplitText);
				 			 
        for ($i=0;$i<$arraylen;$i++) {
			
            // $textString = Term1, verb, term2 
            $textString = $aSplitText[$i];

            // variables to find $sfRelationId that matches session fact text
            $sfRelationId     = 0;
            $sfLeftTermId     = 0;
            $sfRelationTypeId = 0;
            $sfRightTermId    = 0;
            $isFound = 1; // 0= term not found; 1 = term found

            //  find term1
            $termName = "";

            $arrayText  = explode(",",$textString);

            // FIND LEFT TERM Id

            if (isset($arrayText[0])) {
                $termName = $arrayText[0];							  
                $sfLeftTermId = $oTerm->retrieveTermIdByName($termName);// find term 1
            }

            // FIND RELATION TYPE SYNONYM

            if (isset($arrayText[1])) {
                $termName = strtolower($arrayText[1]);                            
                          
                /*  if relation type synonym is found, replace verb */
                // Find termId of verb
                $termSynId = $oTerm->retrieveTermIdLikeName($termName);// find verb

                if (empty($termSynId)) {
                    $isFound = 0;                           // term not found

                } else {
                             
                    // Find relationTypeId in relation_type_synonym
                    $rtRelationTypeId = $oRelationTypeSynonym->retrieveRelationTypeIdByTermdId($termSynId);  
                    if ($rtRelationTypeId == 0) {
                        $isFound = 0;                           // term not found//echo " SYNONYM termName = $termName;  termSynId = $termSynId ;  ";

                    } else {
                                
                        // Retrieve relation type name from relation_type
                        $relationTypeName = $oRelationType->retrieveNameById($rtRelationTypeId); 
                        if (empty($relationTypeName)) {
                            $isFound = 0;                           // relation not found
                        } else {    
                            $arrayText[1] = $relationTypeName;   // verb->relationTypeSynonymName
                            $sfRelationTypeId = $rtRelationTypeId;
                        }

                    }    
                             
                }                         

            }


					  
            // FIND RIGHT TERM Id

            if (isset($arrayText[2])) {
                $termName = $arrayText[2];							  
                $sfRightTermId = $oTerm->retrieveTermIdByName($termName);// find term 2
            }					  
					  
            // FIND RELATION ID

            $sfRelationId = $oRelation->retrieveByLeftTypeRightId($sfLeftTermId, $sfRelationTypeId, $sfRightTermId );
            $aSplitText[$i] = implode(",", $arrayText);
            $aSplitTextBlank[$i] = $oFunctionHelper->replaceCommaByBlank($aSplitText[$i]);

            $sfFact      = $aSplitText[$i];
            $sfFact      = $oFunctionHelper->replaceCommaByBlank($sfFact);
            $sfRating    = 0;
            $sfSubset    = 0;
            $sfssFact    = "";
            $sfssRelationId = 0;
            $sfssLeftTermId = 0;	
            $sfssRelationTypeId = 0;
            $sfssRightTermId = 0;
            $sfInquiry    = $inquiry;
            $sfParentFact = "";
            $sfParentRating = 0;


            // insert fact record
         //   $oSolutionFact->insertFact($sfFact,$sfRelationId, $sfRating,
		//      $sfLeftTermId, $rtRelationTypeId, $sfRightTermId, $userid); 

            $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                  $sfLeftTermId, $sfRelationTypeId, $sfRightTermId,
                  $sfSubset,$sfssFact,$sfssRelationId,$sfssLeftTermId,
                  $sfssRelationTypeId,$sfssRightTermId,
                  $sfInquiry,$sfParentFact,$sfParentRating,$userid);
        }
		
        return $aSplitText;
     }
	 
    //--------------------------------------------------------------------
	/*  Make an equivalent solution fact. Use right hand logic and synonyms */
    public function makeEquivalentSolutionFact($userid)
     {
        // Instantiate classes
        $oSolutionFact         = new SolutionFact();	
        $oRelationTypeFilter   = new RelationTypeFilter();		
        $oRelation             = new Relation();
        $oRelationType         = new RelationType();
        $oTerm                 = new Term();
			  
        // Get relation type filter
        $step = 3 ;    // step 3: filters for synonyms ////
        $rtFilterId = $oRelationTypeFilter->retrieveByStep($step);

        // retrieve session fact for this userid a
        $rsdata = $oSolutionFact->getByUser($userid);
        if (!empty($rsdata)) {  
					    
            foreach ($rsdata as $rsFact){						  
                $sfId             = $rsFact['sfId'];
                $sfRelationId     = $rsFact['sfRelationId'];
                $sfLeftTermId     = $rsFact['sfLeftTermId'];
                $sfRelationTypeId = $rsFact['sfRelationTypeId'];
                $sfRightTermId    = $rsFact['sfRightTermId'];
				
                // get right term synonym to left term (from relation)
                $rsEq = $oRelation->getByLeftTermRelationType($sfRightTermId, $rtFilterId);
                foreach ($rsEq as $rsEq0) {
                    $sfRelationId   = $rsEq0->relationId;
                    $sfRightTermId  = $rsEq0->rightTermId;
                    $sfLeftName     = $oTerm->retrieveTermName($sfLeftTermId);
                    $sfRTName       = $oRelationType->retrieveNameById($sfRelationTypeId);
                    $sfRightName    = $oTerm->retrieveTermName($sfRightTermId);					
					$sfFact = $sfLeftName . " " . $sfRTName . " " . $sfRightName;	
                    $sfRelationId = $oRelation->retrieveByLeftTypeRightId($sfLeftTermId, $sfRelationTypeId, $sfRightTermId);
					
                    $oSolutionFact->updateSolutionFact($sfId,$sfFact,$sfRelationId,
					    $sfLeftTermId,$sfRelationTypeId,$sfRightTermId);					
                }
            }
        }

     }
 
    //--------------------------------------------------------------------
	/*  make subset fact when the input text has  term 2 with subset (2)  */
    /*   TERM (term2) has subsect if there a classifying relation         */
    /*   e.g.   person can desire car
    /*              sedan is a type of car                                */
    /*              electric car is a type of car                         */   

    public function makeSubsetFact($userid)
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
					$sfssFact         = $sfssLeftName . " " . $sfssRTName . " " . $sfssRightName;	
					
                    // add subset records
                    $oSolutionFact->insertFact($sfFact, $sfRelationId, $sfRating,
                         $sfLeftTermId, $sfRelationTypeId, $sfRightTermId,
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
        $step = 4 ;    // step 2: filter for changing subset fact ////
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
    /*       hasSubset = 0  => there is no further categorization ñeveñ   */   

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

    public function makeSubsetUniqueFact($userid,  $sfId)
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
                    $sfLeftTermId, $sfRelationTypeId, $sfRightTermId,
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
         //   $sfRightTermId = $rs['sfRelationId'];  
            $sfRightTermId = $rs['sfRightTermId'];
            $rsdata1 = $oRelation->getByRelTypeRightTerm($rtFilterId,$sfRightTermId);
            foreach ($rsdata1 as $rs1){                         
                $subsetCount++; 
            }

        } 
        return $subsetCount;
     }

    //--------------------------------------------------------------------
    public function makeSubFactRating($personalityId,$userid)
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

             //   if ($sfRating < 0 ) {
             //       $netRating = $sfRating;
             //   } else {
                    $netRating = $this->calculateRelationRating($personalityId, $sfRelationId);
              //  }
                $oSolutionFact->updateFactRating($sfId, $netRating );
            }
        }

     }	 
	
    //--------------------------------------------------------------------
    public function makeFactRating($personalityId,$userid)
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
                $netRating = $this->calculateRelationRating($personalityId, $sfRelationId);
                if ($sfRating < 0 and $netRating == 0) {
                   $netRating = $sfRating;
                }
                $oSolutionFact->updateFactRating($sfId, $netRating );
            }
        }

     }  

    //--------------------------------------------------------------------
    public function calculateRelationRating($personalityId, $relationId)
     {	
        $netRating = 0;
        $emotionalityRating = 0;
		
        // Instantiate classes
        $oPersonalityTrait         = new PersonalityTrait();
        $oPersonalityValue         = new PersonalityValue();
        $oPersonalityRelation      = new PersonalityRelation();
        $oPersonalityRelationValue = new PersonalityRelationValue();	
		
        // get emotionality rating from perosnality_trait
        $emotionalityRating = $oPersonalityTrait->retrieveScalarValueByPersonality($personalityId);	

        // get parentPersonalityRelaitonId		
		$parentPersonalityRelationId = $oPersonalityRelation->retrievePersonalityRelationId($personalityId, $relationId);
	
        // get records from personality_relation_value		
        $rsdata = $oPersonalityRelationValue->getByPersonalityRelation($parentPersonalityRelationId);
        if (!empty($rsdata) ){   	
            foreach ($rsdata as $rsPerRelValue){						  
                $perRelValueId   = $rsPerRelValue['personRelationTermId'];
                $prvScalarValue  = $rsPerRelValue['scalarValue'];
						 
                // get records from personality_value
                $rsdata1 = $oPersonalityValue->getByPersonalityValue($personalityId, $perRelValueId);
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
    public function makeSolutionRelation ($personalityId, $userid)
     {
		// Instantiate classes
        $oSolutionFact         = new SolutionFact();
        $oRelationType         = new RelationType();
        $oRelation             = new Relation();
        $oRelationTypeFilter   = new RelationTypeFilter();
        $oPersonalityRelation  = new PersonalityRelation();
        $oSolutionRelation     = new SolutionRelation();

        $finalResultText = "";
			
        // 1. Get relation type filters
        $step = 5 ;    // step 5: filters for intermediate results /////
        $rsFilter = $oRelationTypeFilter->getByStep($step);
		
        // 2. retrieve seesion fact for this userid
        $rsdata = $oSolutionFact->getByUser($userid );
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rsFact){			
                $sfId          = $rsFact['sfId'];
                $sfRelationId  = $rsFact['sfRelationId'];
                $sfRating      = $rsFact['sfRating'];
                $sfRightTermId = $rsFact['sfRightTermId'];
                $sfFact        = $rsFact['sfFact'];
                $rightTermSynonymId = $sfRightTermId;
							
                // 3. Process negative fact ratings
                if ($sfRating < 0) {
				
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

                            if ($filterRelationId == 0) {
                                $filterRelationId = $sfRelationId; 
                                $rightTermSynonymId = $sfRightTermId;							
                            } else {
                            // 3.3 retrieve left term synonym
                                $rightTermSynonymId = $oRelation->retrieveLeftTermSynonymId($filterRelationId);  
                            }	

						    // 3.4. Get intermediate result relation
                            $srRating = 0;
                            $srMatch =  0;
                            $rsIntermediateRelation = $oRelation->getByRightTerm($rightTermSynonymId); 
				
                            foreach($rsIntermediateRelation as $rsIR) {
                                $srRelationId     = $rsIR['relationId'];
							
							    // calculate rating
                                $srRating = $this->calculateRelationRating($personalityId, $srRelationId);
						
                                // pick unique potential solution relation with positive rating
                                $solutionRelationCount = $oSolutionRelation->countSR($sfId,$srRelationId,$userid); 
									
                                if (($srRating  > 0) and ($solutionRelationCount == 0)) {   
							
                                    // make solution relation text  
                                    $srRelation = $this->makeRelationText($srRelationId);
								
                                    // save solution relations
                                    $oSolutionRelation->insertRelation($sfId, $srRelation, 
							            $srRelationId, $srRating, $srMatch,$userid);
                                    $srRelation = $srRelation."; ";
                                    $finalResultText .= $srRelation;
                                }							
						
                            }

						  }
						} else {
                        // process relation with no intermediate filter relations

                            $rightTermSynonymId = $sfRightTermId;
						    // Get intermediate result relation
                            $srRating = 0;
                            $srMatch =  0;
                            $rsIntermediateRelation = $oRelation->getByRightTerm($rightTermSynonymId); 
						
                            foreach($rsIntermediateRelation as $rsIR) {
                                $srRelationId     = $rsIR['relationId'];
							
							    // calculate rating
                                $srRating = $this->calculateRelationRating($personalityId, $srRelationId);
						
                                // pick unique potential solution relation with positive rating
                                $solutionRelationCount = $oSolutionRelation->countSR($sfId,$srRelationId,$userid); 
									
                                if (($srRating  > 0) and ($solutionRelationCount == 0)) {   
							
                                    // make solution relation text  
                                    $srRelation = $this->makeRelationText($srRelationId);
								
                                    // save solution relations

                                    $oSolutionRelation->insertRelation($sfId, $srRelation, 
							            $srRelationId, $srRating, $srMatch,$userid);
                                    $srRelation = $srRelation."; ";
                                    $finalResultText .= $srRelation;
                                }							
						
                            }
						
                         ///////					
                        }						
				//////	 
                    }			   
                }	
            }
        }

        return $finalResultText;
     }	 
	 
    //--------------------------------------------------------------------
    public function makeSolutionOption ($userid)
	 {
        // Instantiate classes
		$oRelation             = new Relation();
		$oRelationLink         = new RelationLink();
		$oSolutionRelation     = new SolutionRelation();
		$oSolutionOption       = new SolutionOption();
		$oTerm                 = new Term();
		$oRelationType         = new RelationType();
        $oFunctionHelper       = new FunctionHelper();
				
        // 1. get solution relations for this personality and session id
        $rsdata = $oSolutionRelation->getByUserOrderByRating($userid);
        if (!empty($rsdata)) { 
	
            foreach ($rsdata as $rsRel){			
                $srId          = $rsRel['srId'];
                $srRelationId  = $rsRel['srRelationId'];
                $srRating      = $rsRel['srRating'];

                // 2. get relation links for this solution relation
                $rs = $oRelationLink->getByLeftRelationIdOrder($srRelationId);	
                foreach($rs as $rsOption) {
                    $soSolutionId      = $rsOption['rightRelationId'];  
                    $linkTermId        = $rsOption['linkTermId'];
                    $linkOrder         = $rsOption['linkOrder']; 
                    $soSolution        = "";
                    $linkTypeName      = $oTerm->retrieveTermName($linkTermId);		
					
                    // get solution option text
                    $rsRel = $oRelation->findById($soSolutionId);
                    foreach($rsRel as $rs2) {
                       $lTermId =  $rs2->leftTermId;
                       $rTypeId =  $rs2->relationTypeId;
                       $rTermId =  $rs2->rightTermId;
                       $lTermName = $oTerm->retrieveTermName($lTermId);
                       $rTermName = $oTerm->retrieveTermName($rTermId);					   
                       $rTypeName = $oRelationType->retrieveNameById($rTypeId);	

                       $lTermName = $oFunctionHelper->replacePersonByYou($lTermName);
                       $soSolution = $lTermName." ".$rTypeName." ".$rTermName;  

                    }
					
                    // insert solution option
                    $oSolutionOption->insertOption($srId,$linkTypeName, 
                         $soSolution, $soSolutionId,$linkOrder,$srRating,$userid);
                }
            }
        }

     }		 
	
    //--------------------------------------------------------------------
    public function makeRelationText($relationId)
	 {
        // Instantiate classes
        $oTerm               = new Term();
        $oRelationType       = new RelationType();
        $oRelation           = new Relation();
        $oFunctionHelper     = new FunctionHelper();
			  
        // get relationId field values
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
        $leftTermName = $oTerm->retrieveTermName($leftTermId);

        $leftTermName = $oFunctionHelper->replacePersonByYou($leftTermName);
        // retrieve relatio type name
        $relationTypeName = $oRelationType->retrieveNameById($relationTypeId);	

        // retrieve right term name
        $rightTermName = $oTerm->retrieveTermName($rightTermId);	

        $relationText  = $leftTermName ." ". $relationTypeName ." ". $rightTermName;		
        return $relationText;
		
     }

    //--------------------------------------------------------------------
    public function getSolutionRelationString($userid)
     {
        // Instantiate classes
        $oSolutionRelation       = new SolutionRelation();  
        $solutionRelationText = "";
		
        $rsdata = $oSolutionRelation->getByUserOrderByRating($userid);
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
    /* get subset facts and make output                                 */
    /* value format
      *14**8888888888*1*9999999999   *13**     subset preprocessing pick
      *141*8888888888*1*9999999999   *131*     subset preprocessing Select all
      *140*8888888888*1*9999999999   *130*     subset preprocessing exit
                                                                       */
    public function getSubsetFactArray($userid)
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
                $rating         = $rs0->sfRating;
                $srIdString     = $oFunctionHelper->makePadString($srId,10,"0"); 
                $subsetId       = $oFunctionHelper->makePadString($zero,10,"0");
                $srIdValue      = "*14**".$srIdString."*".$hasParentId."*".$subsetId;
                $srRelation     = $rs0->sfssFact;	
                $sfRelationId   = $rs0->sfssRelationId;		
                $solutionText[] = ['KRName'=>$srRelation  , 'KRid'=>$sfRelationId,
                       'value'=>$srIdValue  ];                             
            }
        }
        return $solutionText;
     }	 

    //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                     */

    public function getSolutionFactArray($userid, $personalityId, $emotionalityRating)
     {
        // Instantiate classes
        $oSolutionFact      = new SolutionFact();
        $oFunctionHelper    = new FunctionHelper();
        $oPR                = new PersonalityRelation();
        $oPRV               = new PersonalityRelationValue();
        $oPV                = new PersonalityValue();
        $oTerm              = new Term();
        $solutionText       =  array();
        $hasParentId        = "0";                // no parent record
        $parentId           = $oFunctionHelper->makePadString(0,10,"0");
        $factCount          = 0;
                
        $rsdata = $oSolutionFact->getByUser0($userid);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $sfId           = $rs0->sfId;
                //$srId           = "*****".(string)$srId;
                $sfIdString     = $oFunctionHelper->makePadString($sfId,10,"0"); 
                $sfRelationId   = $rs0->sfRelationId;
                $sfIdValue      = "*****".$sfIdString."*".$hasParentId."*".$parentId;
                $sfText         = $rs0->sfFact;   
                $sfRating       = $rs0->sfRating;        
                $solutionText[] = ['KRName'=>$sfText ,'KRid'=>$sfRelationId, 'value'=>$sfIdValue , 'netRating'=>$sfRating] ;
                $factCount++;

                // retrieve personality relation
                $personalityRelationId = $oPR->retrievePersonalityRelationId($personalityId, $sfRelationId);

                // search personality relation value
                $rs1 = $oPRV->getByPersonalityRelationTerm($personalityRelationId);

                $pvText[] = array();
                $pvCount = 0;

                foreach($rs1 as $rs2) {
                   $PRTermId     = $rs2->personRelationTermId;
                   $scalarValue  = $rs2->scalarValue;
                   $prvName      = $rs2->termName;


                   // get personality value
                   $pvScalarValue = $oPV->retrieveScalarValue($personalityId,$PRTermId);

                   if ($pvScalarValue != 0) {
                       $powerValue = pow($pvScalarValue,$emotionalityRating);
                       $scalarValue = $scalarValue * $powerValue;
                   }
                   $solutionText[] = ['PRValueName'=>$prvName,'PRValueId'=>$PRTermId, 'rating'=>$scalarValue];

                }
                           
            }
        }

        if ($factCount == 0) {
            $solutionText = 0;
        }

        return $solutionText;
     } 



    //--------------------------------------------------------------------
    /* get solution option and make output                             */
    /* value format= "*****9999999999*0*0000000000"                     */
    public function getSolutionRelationArray($userid, $personalityId, $emotionalityRating)
     {
        // Instantiate classes
        $oSolutionRelation  = new SolutionRelation();
        $oFunctionHelper    = new FunctionHelper(); 
        $oPR                = new PersonalityRelation();
        $oPRV               = new PersonalityRelationValue();
        $oPV                = new PersonalityValue();
        $oTerm              = new Term();

        $solutionText       =  array();
        $hasParentId        = "0";                // no parent record
        $parentId           = $oFunctionHelper->makePadString(0,10,"0");
        $relationCount      = 0;
                
        $rsdata = $oSolutionRelation->getByUserOrderByRating($userid);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $srId           = $rs0->srId;
                //$srId           = "*****".(string)$srId;
                $srIdString     = $oFunctionHelper->makePadString($srId,10,"0"); 
                $srIdValue      = "*****".$srIdString."*".$hasParentId."*".$parentId;
                $srRelation     = $rs0->srRelation;   
                $srRelationId   = $rs0->srRelationId; 
                $srRating       = $rs0->srRating;        
   
                $solutionText[] = ['KRName'=>$srRelation , 'KRid'=>$srRelationId,
                          'value'=>$srIdValue , 'netRating'=>$srRating ];  
                $relationCount++;

                // retrieve personality relation
                $personalityRelationId = $oPR->retrievePersonalityRelationId($personalityId, $srRelationId);

                // search personality relation value
                $rs1 = $oPRV->getByPersonalityRelationTerm($personalityRelationId);

                $pvText[] = array();
                $pvCount = 0;

                foreach($rs1 as $rs2) {
                   $PRTermId     = $rs2->personRelationTermId;
                   $scalarValue  = $rs2->scalarValue;
                   $prvName      = $rs2->termName;

                   // get personality value
                   $pvScalarValue = $oPV->retrieveScalarValue($personalityId,$PRTermId);

                   if ($pvScalarValue != 0) {
                       $powerValue = pow($pvScalarValue,$emotionalityRating);
                       $scalarValue = $scalarValue * $powerValue;
                   }
                   $solutionText[] = ['PRValueName'=>$prvName,'PRValueId'=>$PRTermId, 'rating'=>$scalarValue];

                }
                           
            }
        }

        if ($relationCount == 0) {
            $solutionText = $relationCount;
        }  

        return $solutionText;
     }   






    //--------------------------------------------------------------------
    /* get solution option  and make output                   */
    /* value format= "*11**9999999999*1*9999999999"                     */
    public function getSolutionOptionArray($userid)
     {
        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $optionCount = 0;

                
        $rsdata = $oSolutionOption->getObjectByUser($userid);
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){                        
                $soId           = $rs0->soId;
                $optionNumber   = $rs0->sosrId;
                $soSolution     = $rs0->soSolution;  
                $parentId       = $oFunctionHelper->makePadString($optionNumber,10,"0");               
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 
                $soIdValue      = "*11**".$soIdString."*".$hasParentId."*".$parentId;
                $solutionText[] = ['KRName'=>$soSolution  , 'value'=>$soIdValue  ];   
                $optionCount++;                          
            }           
        } 
        
        if (!empty($solutionText)) {
            // No means Continue
            // Yes means Exit
            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*111*".$soIdString."*".$hasParentId."*".$parentId;           
            $solutionText[] = ['text'=>"Return"  , 'value'=>$soIdValue ];  
            
            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*110*".$soIdString."*".$hasParentId."*".$parentId;               
            $solutionText[] = ['text'=>"Exit"  , 'value'=>$soIdValue];
        }
        
        if ($optionCount == 0) {
            $solutionText = $optionCount;
        }  

        return $solutionText;

     }  


    //--------------------------------------------------------------------
	/* get solution option: has risk  and make output                   */
    /* value format= "*11**9999999999*1*9999999999"                     */
    public function getOptionHasriskArray($userid, $optionNumber)
     {
        $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
				
        $rsdata = $oSolutionOption->getOptionHasrisk($userid,$optionNumber );
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){						  
                $soId           = $rs0->soId;
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 
                $soIdValue      = "*11**".$soIdString."*".$hasParentId."*".$parentId;
                $soSolution     = $rs0->soSolution;			
                $solutionText[] = ['text'=>$soSolution  , 'value'=>$soIdValue  ];                             
            } 			
        } 
		
		if (!empty($solutionText)) {
            // No means Continue
            // Yes means Exit
            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*111*".$soIdString."*".$hasParentId."*".$parentId;			
            $solutionText[] = ['text'=>"Return"  , 'value'=>$soIdValue ];  
			
            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*110*".$soIdString."*".$hasParentId."*".$parentId;				
            $solutionText[] = ['text'=>"Exit"  , 'value'=>$soIdValue];
		}
		
        return $solutionText;

     }	 

    //--------------------------------------------------------------------
	/* pick from has risk list. Make output                             */
    /* value format= "*111*9999999999*1*9999999999"                     */
    public function getHasriskContinueExitArray($optionNumber)
     {
        $oFunctionHelper   = new FunctionHelper();
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");

        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*112*".$soIdString."*".$hasParentId."*".$parentId;			
        $solutionText[] = ['text'=>"Return"  , 'value'=>$soIdValue ];  
			
        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*110*".$soIdString."*".$hasParentId."*".$parentId;				
        $solutionText[] = ['text'=>"Exit"  , 'value'=>$soIdValue];
		
        return $solutionText;

     }		 
	 
    //--------------------------------------------------------------------
	/* get solution option: requires and make output                    */
    /* value format= "*12**9999999999*1*9999999999"                     */
    public function getOptionRequiresArray($userid, $optionNumber)
     {
	    $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
				
        $rsdata = $oSolutionOption->getOptionRequires($userid,$optionNumber );
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){						  
                $soId           = $rs0->soId;
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 
                $soIdValue      = "*12**".$soIdString."*".$hasParentId."*".$parentId;
                $soSolution     = $rs0->soSolution;			
                $solutionText[] = ['text'=>$soSolution  , 'value'=>$soIdValue  ];                             
            } 			
        } 
		
		if (!empty($solutionText)) {
            // Yes   means Continue
            // No    means Exit
            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*121*".$soIdString."*".$hasParentId."*".$parentId;			
            $solutionText[] = ['text'=>"Return"  , 'value'=>$soIdValue ];  
			
            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*120*".$soIdString."*".$hasParentId."*".$parentId;				
            $solutionText[] = ['text'=>"Exit"  , 'value'=>$soIdValue];
		}
		
        return $solutionText;

     }	 

    //--------------------------------------------------------------------
	/* pick from requires list. Make output                             */
    /* value format= "*121*9999999999*1*9999999999"                     */
    public function getRequiresContinueExitArray($optionNumber)
     {
        $oFunctionHelper   = new FunctionHelper();
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");

        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*122*".$soIdString."*".$hasParentId."*".$parentId;			
        $solutionText[] = ['text'=>"Return"  , 'value'=>$soIdValue ];  
			
        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*120*".$soIdString."*".$hasParentId."*".$parentId;				
        $solutionText[] = ['text'=>"Exit"  , 'value'=>$soIdValue];
		
        return $solutionText;
     }		 
	 
    //--------------------------------------------------------------------
	/* get solution option: has option and make output                  */
    /* value format= "*13**9999999999*1*9999999999"                     */
    public function getOptionHasoptionArray($userid, $optionNumber)
     {
	    $oSolutionOption   = new SolutionOption();
        $oFunctionHelper   = new FunctionHelper();
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $parentId           = $oFunctionHelper->makePadString($optionNumber,10,"0");
				
        $rsdata = $oSolutionOption->getOptionHasoption($userid,$optionNumber );
        if (!empty($rsdata)) {  
            foreach ($rsdata as $rs0){						  
                $soId           = $rs0->soId;
                $soIdString     = $oFunctionHelper->makePadString($soId,10,"0"); 
                $soIdValue      = "*13**".$soIdString."*".$hasParentId."*".$parentId;
                $soSolution     = $rs0->soSolution;			
                $solutionText[] = ['text'=>$soSolution  , 'value'=>$soIdValue  ];                             
            } 			
        } 
		
		if (!empty($solutionText)) {

            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*131*".$soIdString."*".$hasParentId."*".$parentId;			
            $solutionText[] = ['text'=>"Return"  , 'value'=>$soIdValue ];  
			
            $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
            $soIdValue      = "*130*".$soIdString."*".$hasParentId."*".$parentId;				
            $solutionText[] = ['text'=>"Exit"  , 'value'=>$soIdValue];
		}
		
        return $solutionText;
     }		 

    //--------------------------------------------------------------------
	/* pick from has option list. Make output                             */
    /* value format= "*131*9999999999*1*9999999999"                     */
    public function getHasoptionContinueExitArray($optionNumber)
     {
        $oFunctionHelper   = new FunctionHelper();
        $solutionText      =  array();
        $hasParentId       = "1";                // there is parent record
        $parentId          = $oFunctionHelper->makePadString($optionNumber,10,"0");

        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*132*".$soIdString."*".$hasParentId."*".$parentId;			
        $solutionText[] = ['text'=>"Return"  , 'value'=>$soIdValue ];  
			
        $soIdString     = $oFunctionHelper->makePadString(0,10,"0"); 
        $soIdValue      = "*130*".$soIdString."*".$hasParentId."*".$parentId;				
        $solutionText[] = ['text'=>"Exit"  , 'value'=>$soIdValue];
		
        return $solutionText;
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

      option requires
      *12**8888888888*1*9999999999   *12**       25    option Requires pick
      *121*8888888888*1*9999999999   *121*       35    option Requires continue
      *120*8888888888*1*9999999999   *120*       55    option Requires exit
      *122*8888888888*1*9999999999   *122*       42    option Requires continue 2 

      option has option
      *13**8888888888*1*9999999999   *13**       26    option HasOption pick
      *131*8888888888*1*9999999999   *131*       38    option HasOption continue
      *130*8888888888*1*9999999999   *130*       55    option HasOption exit
      *132*8888888888*1*9999999999   *132*       43    option HasOption continue 2 

      subset preprocessing
      *14**8888888888*1*9999999999   *14**       61    subset preprocessing pick
      *141*8888888888*1*9999999999   *141*             subset preprocessing Select all
      *140*8888888888*1*9999999999   *140*             subset preprocessing exit



     */
    public function getFlowState($inText)
     {
        // default state 
        $state = 0;
		
        // get option prefix
        $optionPrefix = substr($inText,0,5);
				
        switch ($optionPrefix) {
							  
            case "*****":      // is pick from  solution relation
               $state = 21;
            break;

            case "*11**":      // is pick from option  has risk list
               $state = 24;
            break;			
			
			
            case "*111*":      // is pick from option  has risk Continue
               $state = 32;
            break;

            case "*110*":      // is pick from  option has risk Exit
               $state = 55;
            break;
			
            case "*112*":      // is pick from option  has risk Continue2 
               $state = 41;
            break;			

            case "*122*":      // is pick from option  requires Continue2 
               $state = 42;
            break;			

            case "*132*":      // is pick from option  has option Continue2 
               $state = 43;
            break;
			
            case "*12**":      // is pick from option requires pick
               $state = 25;
            break;

            case "*121*":      // is pick from option requires Continue
               $state = 35;
            break;

            case "*120*":      // is pick from option requires Exit
               $state = 55;
            break;	

            case "*13**":      // is pick from option has option pick
               $state = 26;
            break;

            case "*131*":      // is pick from option has option Continue
               $state = 38;
            break;

            case "*130*":      // is pick from option has option Exit
               $state = 55;
            break;	

            case "*14**":      // is pick from preprocessign subset list
               $state = 61;
            break;  


        }
			
        return $state;
     }		 
	 
    //--------------------------------------------------------------------
    public function deletePersonalityRecords($personalityId)
     {
        $oPersonalityTrait         = new PersonalityTrait();
        $oPersonalityValue         = new PersonalityValue();
        $oPersonalityRelation      = new PersonalityRelation();
        $oPersonalityRelationValue = new PersonalityRelationValue();
		
        /* Delete records from personality_value        */
        $oPersonalityValue->deleteByPersonality($personalityId);		
		
        /* Delete records from personality_trait        */
        $oPersonalityTrait->deleteByPersonality($personalityId);		
		
        /* Get records from personality_relation     */
		$rs = $oPersonalityRelation->getByPersonality($personalityId);	
        if (!empty($rs)) {  
            foreach ($rs as $rs0){						  
                $personalityRelationId = $rs0->personalityRelationId;
				
				// delete personality_relation_value records
                $oPersonalityRelationValue->deleteByPersonalityRelation($personalityRelationId);
				
				// delete personality_relation record
                $oPersonalityRelation->deleteById($personalityRelationId);

            }
        }		
		
     }		 
	
    //--------------------------------------------------------------------	
	
    public function clonePersonalityNew($sourcePersonalityId, $targetPersonalityId, $targetConsumerUserId,
              $sourceOrganizationId, $targetOrganizationId)
     {
        $oConsumerUserPersonality  = new ConsumerUserPersonality();
        $oPersonalityTrait         = new PersonalityTrait();
        $oPersonalityValue         = new PersonalityValue();
        $oPersonalityRelation      = new PersonalityRelation();
        $oPersonalityRelationValue = new PersonalityRelationValue();
		$dateCreated = date("Y-m-d H:i:s");
		
		/*  update consumer_user_personality               */
        $oConsumerUserPersonality->updatePersonalityOrganization($targetConsumerUserId, 
		    $targetPersonalityId, $targetOrganizationId);
		
        /*  clone personality_value                        */
        $oPersonalityValue->clonePersonalityValue($sourcePersonalityId, $targetPersonalityId, 
                $sourceOrganizationId, $targetOrganizationId);		
		
        /* Clone personality_trait        */
        $oPersonalityTrait->clonePersonalityTrait($sourcePersonalityId, $targetPersonalityId, 
                $sourceOrganizationId, $targetOrganizationId);		
		
        /* clone personality_relation     */
		$rs = $oPersonalityRelation->getByPersonality($sourcePersonalityId);	
        if (!empty($rs)) {  
            foreach ($rs as $rs0){						  
                $personalityRelationId = $rs0->personalityRelationId;
                $relationId            = $rs0->relationId;	
                $ownership             = $rs0->ownership;
                $ownerId               = $rs0->ownerId;
                $lastUserId            = $rs0->lastUserId;				
				
				// insert personality_relation record
                $lastPRId = $oPersonalityRelation->insertPersonalityRelation($targetPersonalityId, $relationId, 
				  $targetOrganizationId,$ownership, $ownerId, $dateCreated, $lastUserId);
 				
                // get children personality_relation_value
		        $rsPRV = $oPersonalityRelationValue->getByPersonalityRelation($personalityRelationId);

                if (!empty($rsPRV)) {  
                    foreach ($rsPRV as $rs1){					
                        $personRelationTermId = $rs1->personRelationTermId;
                        $scalarValuePRV       = $rs1->scalarValue;							
                        $ownershipPRV         = $rs1->ownership;
                        $ownerIdPRV           = $rs1->ownerId;
                        $lastUserIdPRV        = $rs1->lastUserId;
 
                        $oPersonalityRelationValue->insertPRV($lastPRId, $personRelationTermId,  
				            $scalarValuePRV,$ownership, $targetOrganizationId, $dateCreated, $lastUserIdPRV);						
                    }
                }

            }
        }		
		

     }	
	
  //----------------------------------------------------------	
	
}