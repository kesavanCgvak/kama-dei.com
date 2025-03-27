<?php
/*--------------------------------------------------------------------------------
 *  File          : LexController.php        
 *  Type          : Controller 
 *  Function      : Provides logic and control for a AWS Lex api 
 *                  LexController has been created from ChatboxController 
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation. 
 *  Version       : 1.4.3
 *  Updated       : 29 April 2020
 *                  key word processing, multi-language support
 *                  recognition of address in extended data
 *---------------------------------------------------------------------------------*/

namespace App\Http\Controllers\Api\Lex;

use Illuminate\Http\Request;
use App\Lex\LexClass;
use App\Controllers;
use App\Helpers\InferenceHelper;
use App\Helpers\ParsingHelper;
use App\Helpers\FunctionHelper;
use App\Models\ConsumerUserPersonality;
use App\Models\Personality;
//use App\Models\Message;
use App\Models\SolutionFact;
use App\Models\SolutionRelation;
use App\Models\RelationTypeFilter;;
use App\Models\SolutionRelationExdata;
use App\Models\SolutionOption;
use App\Models\SolutionOptionExdata;

class LexController extends \App\Http\Controllers\Controller{
	
	
	//---------------------------------------
  public function wordCheck(Request $request) {
    $userID = trim($request->input('userid'));
    $orgID  = trim($request->input('orgid' ));
    $word   = trim($request->input('word'  ));
    if($userID==null){ return \Response::json([ 'message' => 'UserID not defined' ], 400); }
    if($orgID ==null){ return \Response::json([ 'message' => 'orgID  not defined' ], 400); }
    if(!$request->has('word')){ return \Response::json([ 'message' => 'Word not defined 1'], 400); }

    if($word==''){ return \Response::json([ 'message' => 'Word not found in database'], 204); }
    return \Response::json([ 'message' => 'Word in database' ], 200);
  }

	//---------------------------------------
	public function newShow(Request $request)  {

    $oSolutionRelation   = new SolutionRelation();

    $userId  = trim($request->input('userid' ));
    $orgId   = trim($request->input('orgid'  ));
    $state   = trim($request->input('state'  ));   
    $inquiry = trim($request->input('inquiry'));

    $botName   = trim($request->input('botName'  ));
    $botVersion= trim($request->input('botVersion' ));
    $lexState  = trim($request->input('lexState')); 
    $intentName = trim($request->input('intentName' ));
    $slotName  = trim($request->input('slotName'  ));
    $inquiry   = trim($request->input('inquiry'));   

    if($userId ==null){ return \Response::json([ 'message' => 'UserID not defined' ], 400); }
    if($orgId  ==null){ return \Response::json([ 'message' => 'orgID  not defined' ], 400); }
    if($inquiry==null){ return \Response::json([ 'message' => 'inquiry not defined' ], 400); }

    if($botName==null){ $botName =" "; }
    if($botVersion==null){ $botVersion ="v1"; }
    if($intentName==null){ $intentName =" "; }
    if($slotName==null){ $slotName =" "; }     

    // Language parameter

    if ($request->has('language')) {
       $inLang  = trim($request->input('language'));
    }     
    $aLang = json_decode( utf8_encode($inLang), true );  
    // end of language parameter

    $state = (($state==null) ?0 : $state);

    // get state from table solution_relation
    $srId = 0;
    $state = $oSolutionRelation->retrieveState($srId,$userId);     

    // get message
    $inquiry = json_decode( utf8_encode($inquiry) );
    $inquiry0 = $inquiry->request->message; // get message from JSON


    // check for text input
    $m1 = substr($inquiry0,0,1);
    if ($m1 !="*") {
      $state = 0;
    }    

    return $this->show($userId, $orgId, $botName, $botVersion, $lexState, $intentName,
     $slotName, $inquiry0, $state, $aLang);
	}


	//------------------------------------------------------------------
  public function show($userid, $orgid, $botName, $botVersion, $lexState, $intentName, 
   $slotName, $inquiry, $state, $aLang ) {

	///////////////

		
        // Instantiate classes
        $oInferenceHelper          = new InferenceHelper();
        $oParsingHelper            = new ParsingHelper();
        $oFunctionHelper           = new FunctionHelper();
        $oConsumerUserPersonality  = new ConsumerUserPersonality();
        $oPersonality              = new Personality();
        //$oMessage                  = new Message();
        $oMessage                  = new \App\Models\Message();          
        $oSolutionFact             = new SolutionFact();;
        $oSolutionRelation         = new SolutionRelation();
        $oSolRelExdata             = new SolutionRelationExdata();
        $oSolutionOption           = new SolutionOption();
        $oSolutionOptionExdata     = new SolutionOptionExdata();
        $oRTFilter                 = new RelationTypeFilter();


        $aliasid = 'BookTripAlias';
        $botName = 'BookTrip';
        $oLexClass                 = new LexClass($botName, $aliasid, $orgid, $userid);                


        // delete state variable
        $srId = 0;              
        $oSolutionRelation->deleteState($srId,$userid);  
        if (!isset($state)) {
           $state = 0; 
        }

        ///// start of  language input   //////////
        $langParameter  = $aLang;            // save langauge parameter        
        $baseLang     = "en";                // base / default language
        $messageLang  = $baseLang;        
        $decidedLang  = $baseLang; 
        $detectedLang = $baseLang;                  
     
        if (isset($aLang['decided'])) {           // is there decided language?
            $decidedLang = $aLang['decided'];          
        }  
        if (isset($aLang['detected'])) {          // is there detected language
            $detectedLang = $aLang['detected'];
        }           
                        

        // $jsonlang is the the language paramater in the response
        $jsonLang['decided']   = $decidedLang;     // language for knowledge records 
        $jsonLang['detected']  = $detectedLang;
        $lang                  = $baseLang;        // default / base language
        $tLang                 = $decidedLang ;    // target language

        // if decidedLang is a supported language then messageLang = decidedLang
        if ($decidedLang == "en" or $decidedLang == "fr") {
           $messageLang = $decidedLang;
        }        
        $jsonLang['message']   = $messageLang;     // language for messages

        ///// end of language input  /////////



        // get inquiry parameters: message and slide bars
        $slidebar ="";
        $inText = strtolower(trim($inquiry));
        $inState = $state;      // save incoming state 
        $textOrgId = NULL;         // default organization for message text
        $valueName = "";
        $isLex = 1;
        $zero  = 0;
        $wRelationId = 0;
        $swRelationId = strval($wRelationId);

        $flowState = 0;                     // calculated flow state
        $slidebar0 = "";
        $optionText = array();
        $exdataState = 0;
        $useShortText = 0;     // 1: use short text in response format; 0: use default values
        $addressKey = "Street address"; // parameter used for selecting attribute address in extended data

        $contentType = "kr";     // content type default value
        $portalType = "text";     // content type default value

        // patch for testing slide bars

        $SBstate = 0; 
        $midState =  0;

        $sTest = " START";     

        $inquiryText = $oFunctionHelper->getInquiry($inText);  
        $personalityId = $oConsumerUserPersonality->retrievePersonalityByUserOrg($userid, $orgid);
        $personaId     = $oPersonality->retrieveParentPersonality($personalityId);
        $useShortText = $oRTFilter->canUseShortText($orgid);
        $TSStrategy = 2;

       /*--------------------------------------------------------
        *  Clear previous states
        *--------------------------------------------------------*/
        if ($inState !=998 or $inState!=999) {
           $state = 0;
        } elseif ($inState !=300) {
           $state = 0;
        }


       /**  Orchestrator                        *****************            
        *    Analyze input text and tell whether input is
        *    - yes or no
        *    - relation pick. A list of solution relations has been submitted 
        *      to the user, and he/she picks a relation.
        *    - option pick. A list of solution options has been submitted 
        *      to the user, and he/she picks an option.	
        *    - otherwise: process inquiry		 
        */

        // check flow hint: at the middle of a conversation flow ?
        $flowHint = $oFunctionHelper->isFlowHint($inText);

        if ($flowHint) {
           $state = $oInferenceHelper->getFlowState($inText);
           $flowState = $state;
        } else {
           if ($oFunctionHelper->isYesNo($inText)) {    // check for yes or no 
              $state = 990;	
           } 
        }			
  
        $sflowState = strval($flowState);
        $flowState = $state;
        //$state = 998;

       /** End of orchestrator                   ****/

	

        /** default state                               **/
        if ($state == 0) {
            $state = 1;
        }    

        //$inState = $state;
        $inSlidebar = $slidebar;


///////////  FSM STARTS HERE  ///////////////


        $inLoop = 1;  // stay in loop
		
        while ($inLoop == 1) {            // FSM loop

            switch ($state) { 

              /////////////////////////////////////////////////////////	
              /**  State 1                                          **/			  
              /*   check for blank input text                        */
              case 1:

                if (empty($inquiry)) {
                    $state = 998;   // exit with error	
                    $inLoop = 0;						  
                } else {
                    $state = 2;    // proceed to next state
                }
                break;
				  
              ///////////////////////////////////////////////////////////	
              /**  State 2                                            **/					  
              /*  clean input text                                     */					  
              case 2:

                $inputText = $oFunctionHelper->removeDelimitedStopWord($inquiry);
                $inputText = $oFunctionHelper->removeExtraBlank($inputText);
                $inputText = $oFunctionHelper->replacePeriodByAnd($inputText);
                $inputText = $oFunctionHelper->replaceButByAnd($inputText);
                $inputText = $oFunctionHelper->trimEOSPeriod($inputText);
                $inputText = $oFunctionHelper->replaceCommaBySemicolon($inputText); 
                $state = 3;    // next state  
                break;
				  
               /////////////////////////////////////////////////////////////////////////
              /**  State 3                                                          **/   
              /*   process  single Term. Make single solution fact                   */ 
              /* Split Sentences. A sentence ends with delimiters: and or blank 
                 input :  string  $inputText  e.g. $inputText = hungry
                 output:  string  $term
              */        
              case 3:

                $sTerm = $oParsingHelper->getSingleTerm($inquiry);  

                if ($sTerm == "") {
                  $state = 4;              // proceed, regular processing
                } else {
                  $delete = 1;
                  $oSolutionFact->deleteByUser($userid);
                  $oSolutionRelation->deleteByUser($userid);
                  $oSolRelExdata->deleteByUser($userid);
                  $oSolutionOption->deleteByUser($userid);  
                  $oSolutionOptionExdata->deleteByUser($userid);                    
                  $oInferenceHelper->makeSingleSolutionFact($sTerm, $userid, $inquiryText); 
                  $oInferenceHelper->makeEquivalentSingleSolutionFact($userid, $orgid, $delete);                   
                  $state = 22;             // make solution
                }
                
                break;   


              /////////////////////////////////////////////////////////////////////////
              /**  State 4                                                          **/		
              /*   split input text                                                  */	
              /* Split Sentences. A sentence ends with delimiters: and or blank 
                 input :  string  $inputText  e.g. $inputText = I am hungry and I am tired
                 output:  array   $aSplitText      $aSplitText[0] = I am hungry
                                                   $aSplitText[1] = I am tired
              */			  
              case 4:

                $inputText = $oParsingHelper->sanitizeText($inputText);  
                $aSplitText = $oParsingHelper->splitInputText($inputText);

                $state = 6;
                break;	

              //////////////////////////////////////////////////////////////
              /**  State 6                                                */		
              /*  validate term1, verb, term2 against table term          */				   
              case 6: 	
			  
                // delete records from previous conversations for this userid
                $oSolutionFact->deleteByUser($userid);
                $oSolutionRelation->deleteByUser($userid);
                $oSolRelExdata->deleteByUser($userid);
                $oSolutionOption->deleteByUser($userid);	
                $oSolutionOptionExdata->deleteByUser($userid);			  
	
                $isInputError  = $oParsingHelper->isInputError($aSplitText);

                if ($isInputError == 1) {
                    $validationError = 1;  // parameter count error
                } else {
                    $validationError = $oParsingHelper->validateTerms($aSplitText, $orgid);                   
                }

                if ($validationError == 1) {
                    $state = 998;        // validation error
                    $inLoop = 0;
                } else {
                    $state = 10;          // no validation error. proceed
                }
                break;

              ///////////////////////////////////////////////////////////
              /**  State 10                                            **/		
              /*  change term 1 to "person":                           */
              /*			  I am hungry -> person am hungry                */				  
              case 10: 
			  
                $aSplitText = $oParsingHelper->replaceTerm1ByPerson($aSplitText);
                $state = 14;                    //  No errors. Proceed 	
             
 
                break;			  

              ////////////////////////////////////////////////////////////
              /**  State  14                                           **/	
              /*  Make solution facts                                   */ 			  
              /*  change verb. use relation type synonym:               */
              /*	person am hungry  -> person can be hungry             */
              /*  save session facts                                    */			  
              case 14: 
                
                $valueName = $oFunctionHelper->getRightTerm($inText);
                $inquiryText = $oFunctionHelper->getInquiry($inText);  
                $sinText = $inText;    
                $isLex  = 1;

                $wRelationId = 0;
                $wSolution   = 0;
                $valueNameId = 0;


                if ($slotName == NULL) {
                   $slotName = "";
                }
                if ($intentName == NULL) {
                   $intentName = "";
                }

                // First search
                $synonymCounter = 1;
                
                while ($synonymCounter <= 3) { 

                  if ($synonymCounter ==2) {
                     $valueName = $oInferenceHelper->getRTermSynonym($valueName);  
                  }

                  if ($synonymCounter ==3) {
                     $valueName = $oInferenceHelper->getLTermSynonym($valueName);  
                  }

                  // Firts search
                  // wSolution 1=found;   0= not found
                  /// Priority 1: Find solution in mapping data with: intent, slot, value  ///
                  $oLexClass->findSlotName($intentName,$slotName,$valueName);
                  $jlexTMP0  = $oLexClass->getData();
                  $jlexTMP   = json_encode($jlexTMP0, JSON_UNESCAPED_SLASHES );
                  $aLexTMP = $jlexTMP;
                  $aLexTMP = json_decode( utf8_encode($aLexTMP), true );

                  if (isset($aLexTMP['Intent_krId'])) {    
                     $valueNameId = $aLexTMP['slots'][0]['Value_krId'];
                  }
                  // test found condition
                  if ($valueNameId > 0) {
                     $wSolution = 1;
                     $state = 804;
                      $inLoop = 0;
                     $synonymCounter = 4;
                  }

                  $synonymCounter++;
                }


                // Second search: find problem relationId 

                if ($wSolution == 0) {
                	$delete = 1;
                   $wRelationId = 
                      $oInferenceHelper->makeSolutionFact($aSplitText, $userid, $inquiryText);   
                   //if ( $wRelationId == 0) {
                   $oInferenceHelper->makeEquivalentSolutionFact($userid, $orgid,$delete);  
                   $wRelationId = 
                      $oInferenceHelper->makeFactRating($personalityId,$userid,$TSStrategy,$isLex);                  
                   //}

                }
   
                $swRelationId = strval($wRelationId);        
                $sTest = $swRelationId;  // for testing
                /// Priority 2: Find solution in mapping data with: kr_id  ///
                if ($wRelationId > 0) {
                  $oLexClass->findKR($wRelationId);
                  $jlexTMP0  = $oLexClass->getData();
                  $jlexTMP   = json_encode($jlexTMP0, JSON_UNESCAPED_SLASHES );
                  $aLexTMP = $jlexTMP;
                  $aLexTMP = json_decode( utf8_encode($aLexTMP), true );

                  if (isset($aLexTMP['intent'])) {    
                      $state = 804;
                      $inLoop = 0;
                  }                  
                }

                /// Priority 3: Find solution in kama-Dei                 ///
                if ($state != 804 ) {
                	$delete = 1;
                   $oSolutionFact->deleteByUser($userid);
                   $oInferenceHelper->makeSolutionFact($aSplitText, $userid, $inquiryText);  
                   $oInferenceHelper->makeEquivalentSolutionFact($userid, $orgid, $delete); 
                   $hasSubset = $oInferenceHelper->makeSubsetFact($userid, $inquiryText);         

                   if ($hasSubset == 1) {
                      $state = 30 ;
                   } else {
                      $state = 18;                   // Proceed 
                   }
                }


                break;				  
				  
              /////////////////////////////////////////////////////////////////////
              /**  State  18                                                    **/
              /*   make fact ratings: calculate and save session fact ratings    */			  			  
              case 18: 
		  
                $sfNetRating = 
                  $oInferenceHelper->makeFactRating($personalityId,$userid,$TSStrategy,$isLex);			 
                  $state = 22;
                break;

              /////////////////////////////////////////////////////////////////////
              /**  State  20                                                    **/
              /*   NEW: get slide bar values from solution_fact                  */			  			  
              case 20: 

                $slideBarText = $oInferenceHelper->getSlideBarArray1($userid, $personaId); 
                $buttonText = $oInferenceHelper->getSlideBarArray2($userid);

                if ($slideBarText == 0) {
                   $state = 22;
                } else {
                   $state = 900;
                   $inLoop = 0;
                }

                break;			  


              /////////////////////////////////////////////////////////////////////
              /**  State  22                                                     **/
              /*   make solutions: find and save solution relations              */			    
              case 22:

                // find and save solutions
                $hasBar = 0;               // no slide bar parameters
                $isLex  = 0;
                $sBar = "";

                if ($flowState == 302) {
                   $hasBar = 1;
                   $sBar = $slidebar;
                }

                $hasBar = 0;
                $oInferenceHelper->makeSolutionRelation($personalityId, $userid, $orgid,$hasBar,
                 $sBar,$isLex, $state, $lang);	
                $oInferenceHelper->makeRelationExtendedData($userid, $orgid, $portalType);		 

                $state = 26;
                break;              
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  26                                                   **/
              /*   make options: find and save solution options                 */	
              /*   make extneded data: find and save extended data              */		    
              case 26: 

                $oInferenceHelper->makeSolutionOption($userid);  // options	
                $oInferenceHelper->makeSolutionOptionLink($userid); // linking option	
                $oInferenceHelper->makeOptionExtendedData($userid, $orgid, $portalType);		 
                $state = 34;                 	

                break; 
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  30                                                    **/
              /*   Preprocessing. Prepare data for chatbot view                  */			  
              case 30:

                $sfNetRating = $oInferenceHelper->makeSubFactRating($personalityId, $userid);   
                $hasRating = 0; 
                $lookAheadSubset = 0;
                $hasSubset  =  $oInferenceHelper->hasSubset($userid);

                if ($hasSubset > 0){
                    $lookAheadSubset  =  $oInferenceHelper->lookAheadSubsetFact($userid);
                }

                if ($lookAheadSubset > 0){
                    $solutionText = $oInferenceHelper->getSubsetFactArray($userid, $hasRating);     
                    $state = 802;    // subset relations found  
                    $inLoop = 0;         
                } else {
                    $hasSubset = $oInferenceHelper->changeSubsetFact($userid);  
                    $sfNetRating = $oInferenceHelper->makeFactRating($personalityId,$userid,$TSStrategy,$isLex);      
                    $state = 22;    // 
                }
		  
                break; 


              ////////////////////////////////////////////////////////////////////
              /**  State  34                                                   **/
              /*   Prepare solution relations for chatbot view                  */			  
              case 34:               

                $hasRating = 0;  // no ratings
                $isLex     = 1;  // this is Lex
                $solutionText0 = 
                  $oInferenceHelper->getLexSolutionRelationArray($orgid,$userid,$hasRating,
                      $isLex,$lang,$tLang); 

                if (sizeof($solutionText0) == 0) { 
                    $state = 999;    // no solution found
                    $inLoop = 0;
                } else { 
                    $solutionText = $solutionText0;
                    $state = 802;    // solution relation found
                    $inLoop = 0;

                    // count solution text
                    $solCount = count($solutionText);

                    // if there is only one solution, display options 
                    if ($solCount == 1) {
                    	$state = 38;
                    	$inText = $solutionText['0']['value'];
                      $inLoop = 1;
                    }

                }	

                break; 				  
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  38                                                   **/
              /*   Solution relation pick                                       */		
              /*   get options: has risk                                        */				  
              case 38:

                $hasRating = 0;  // no ratings
                $isLex     = 0;  // this is not Lex
                $zero    = 0;

                $hasExdata = 1;   //  extended adata
                $optionText = array();
                $hasReturn = 0;
                $optionNumber = $oFunctionHelper->getOptionNumber($inText);

                // check whether solution relation has extended data
                $hasSolRelExData = $oSolutionRelation->hasExData($userid,$optionNumber,$hasExdata);

                // check whether solution options have extended data
                $hasSolOptExData = $oInferenceHelper->getHasOptions($userid,$optionNumber);
                $hasSolRisk  = $hasSolOptExData['hasSolRisk'];     
                $hasSolReq   = $hasSolOptExData['hasSolReq'];    
                $hasSolOpt   = $hasSolOptExData['hasSolOpt'];   

                // calculate solution extended data state
                $exdataState = $oInferenceHelper->
                       getExdataState($hasSolRelExData,$hasSolRisk,$hasSolReq,$hasSolOpt);
    
                // there are Solution relation extended data
                if ($exdataState > 0) {
                    $edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 
                    //$optionText = 
                    //   $oInferenceHelper->getSolutionRelationArray($userid,$hasRating,$isLex,$lang,$tLang);                     
                    $optionText  =  $oInferenceHelper->getSolRelExtDataArray($userid,$orgid,
                        $edOptionNumber,$optionNumber, $inText,$hasReturn, $lang, $tLang);
                    //$optionText = array_merge($optionText,$optionText0); 
                }                  


                // check for solution option has risk
                $solCount = $hasSolRisk + $hasSolReq + $hasSolOpt;

                if ($hasSolRisk == 1 and $solCount > 1)  {
                    $bType = "Risk";
                    $optionText0 = $oInferenceHelper->getSolutionReviewButton($optionNumber,
                      $bType,$useShortText,$lang,$tLang); 
                    $optionText[] = $optionText0;  
                }  

                // check for solution option Requires
                if ($hasSolReq == 1 and $solCount > 1)  {
                    $bType = "Req";
                    $optionText0 = $oInferenceHelper->getSolutionReviewButton($optionNumber,
                      $bType,$useShortText,$lang,$tLang);
                    $optionText[] = $optionText0; 
                } 

                // check for solution option Has options
                if ($hasSolOpt == 1 and $solCount > 1)  {
                    $bType = "Opt";
                    $optionText0 = $oInferenceHelper->getSolutionReviewButton($optionNumber,
                      $bType, $useShortText,$lang,$tLang); 
                    $optionText[] = $optionText0; 
                } 

                if ($exdataState > 0) {
                    $state = 842;                   
                } elseif ($hasSolRisk == 0 and $hasSolReq == 0 and $hasSolOpt == 0) {
                    $state = 991;
                    $optNum = "";
                    $optionText = $oInferenceHelper->getREButtonSolutionScreen($optNum,$lang,$tLang);
                } else {
                    $state = 842;
                } 

                if ($exdataState == 1 and $solCount == 0) {
                    $state = 841;
                    //$optionText0 = $oInferenceHelper->getREButtonSolutionSreen($optionNumber);
                    //$optionText0 = $oInferenceHelper->getEButtonSolutionSreen($optionNumber);
                    //$optionText = array_merge($optionText,$optionText0);  

                }


                /*  When there is one set of linking option (has risk, requires, has options)
                    display directly the list of linking options
                */
   
                if ($solCount == 1) {
                    if ($hasSolRisk == 1) {                  
                        $appendB = 0;
                        $parentId = 0;
                        $optionNumber = $oFunctionHelper->getOptionNumber($inText); 
                        $optionText  = $oInferenceHelper->getOptionHasriskArray($userid,
                             $optionNumber,$appendB, $parentId,$lang,$tLang);
                        $optionText0  = 
                        $oInferenceHelper->getLinkReturnExitArray($optionNumber,$zero,$lang,$tLang);
                        $optionText = array_merge($optionText,$optionText0);  
                        $state = 806;
                        $inLoop = 0;

                    } elseif ($hasSolReq == 1) { 
                        $appendB = 0;
                        $optionNumber = $oFunctionHelper->getOptionNumber($inText); 
                        $optionText  = $oInferenceHelper->getOptionRequiresArray($userid, 
                              $optionNumber,$appendB,$zero,$lang,$tLang);
                        $optionText0  = 
                             $oInferenceHelper->getLinkReturnExitArray($optionNumber,$zero,$lang,$tLang);
                        $optionText = array_merge($optionText,$optionText0); 
                        $state = 810;

                    } elseif ($hasSolOpt == 1) {
                        $appendB = 0;
                        $state = 813;
                        $midState = 8140;
                        $optionNumber = $oFunctionHelper->getOptionNumber($inText); 
                        $edOptionNumber = $oFunctionHelper->getOptionNumber($inText);     

                        $optionText2 =  $oInferenceHelper->getSolRelExtDataArray($userid,$orgid,
                          $edOptionNumber,$optionNumber, $inText,$hasReturn,$lang, $tLang);

                        $optionText  = $oInferenceHelper->getOptionHasoptionArray($userid,
                              $optionNumber,$appendB,$zero,$lang,$tLang);
                        $optionText = array_merge($optionText2,$optionText); 
                        
                        $optionText0  = 
                        $oInferenceHelper->getLinkExitArray($optionNumber,$midState,$lang,$tLang);
                        $optionText = array_merge($optionText,$optionText0); 
                /////////////////////////////
                        //$optionNumber = $oFunctionHelper->getOptionNumber($inText); 
                        //$edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 

                        $optionText2 =  $oInferenceHelper->getSolRelExtDataArray($userid,$orgid,
                          $edOptionNumber,$optionNumber, $inText,$hasReturn,$lang, $tLang);

                        $optionText  = $oInferenceHelper->getOptionHasoptionArray($userid,
                              $optionNumber,$appendB,$zero,$lang,$tLang);
                        $optionText = array_merge($optionText2,$optionText);   
                                 
                        $optionText0  = 
                        $oInferenceHelper->getLinkExitArray($optionNumber,$midState, $lang,$tLang);
                        $optionText = array_merge($optionText,$optionText0); 




                ///////////////////////////////
                    }
                }

                // add return / button to solution screen 
                if ($state == 842) {
                    $optNum = "";
                    $optionText0 = $oInferenceHelper->getREButtonSolutionScreen($optNum,$lang,$tLang);
                    $optionText = array_merge($optionText,$optionText0);  
                }
                $inLoop = 0;
                break; 
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  42                                                   **/
              /*   Solution relation                                            */		
              /*   get options: requires                                        */			   
              case 42:	

                $parentId = 0;
                $optionNumber = $oFunctionHelper->getOptionNumber($inText);
                $optionText0  = $oInferenceHelper->getOptionRequiresArray($userid, $optionNumber,
                 $parentId, $zero, $lang, $tLang);	
				  
                if (empty($optionText0)) { 
                    $state = 46;    // no requires found
                } else { 
                    $state = 810;    // solution option: requires
                    if($exdataState == 0) {
                       $optionText = $optionText0;
                    } else {
                       $optionText = array_merge($optionText,$optionText0);   
                    }                    
                }	
 
                break; 
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  46                                                   **/
              /*   Solution relation                                            */		
              /*   get options: has option                                      */			  
              case 46: 

                $parentId = 0;			  
                $optionNumber = $oFunctionHelper->getOptionNumber($inText);
                $optionText0  = $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber, 
                  $parentId, $zero, $lang, $tLang);	
				  
                if (empty($optionText0)) { 
                   $state = 991;    // not found
                } else { 
                   $state = 814;    // solution option: has option
                   if($exdataState == 0) {
                      $optionText = $optionText0;
                   } else {
                      $optionText = array_merge($optionText,$optionText0);                      
                   }   
                }	
                $inLoop = 0;

                break;
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  50                                                   **/
              /*   Option pick from has risk                                    */				  
              case 50:   

                $hasReturn = 0;  
                $parentId = 0;  
                $zero  = 0;   
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText); 
                $edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 
                $parentId  = $edOptionNumber;                  

                $optionText0  =  $oInferenceHelper->getSolOptExtDataArray($userid,$orgid,
                   $edOptionNumber,$optionNumber, $inText,$hasReturn, $lang, $tLang);

                //// Check whether extand data has address  /////
                $hasAddress = $oFunctionHelper->hasAddress($optionText0,$addressKey);

                $optionText1 = 
                 $oInferenceHelper->getOptionHasriskArray($userid, $edOptionNumber, $hasReturn, 
                  $parentId, $lang,$tLang);

                if (!empty($optionText0)) {
                     $optionText = $optionText0;
                     if (!empty($optionText1)) {
                        $optionText = array_merge($optionText0,$optionText1); 
                   }
                } else {
                   if (!empty($optionText1)) {
                      $optionText = $optionText1; 
                   }                  
                }
                  

                if (empty($optionText)) {
                    $hasReturn = 2;
                    $optionText = $oInferenceHelper->getHasRiskContinueExitArray($optionNumber,$lang, $tLang); 
                    $state = 822;       
                } else {
                    $state = 830;
                    $optionText0  = 
                       $oInferenceHelper->getLinkReturnExitArray($optionNumber, $zero,$lang,$tLang);
                    $optionText = array_merge($optionText,$optionText0); 

                    // if extended data attname contanis address, set stat= 832
                    if ($hasAddress == 1) {
                        $state = 832;
                    }

                }
                $inLoop = 0;
                break;	

              ////////////////////////////////////////////////////////////////////
              /**  State  54                                                   **/
              /*   Option pick from has risk                                    */				  
              case 54: 

                $appendB = 0;
                $parentId = 0;
                $zero = 0;
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);	
                $optionText  = $oInferenceHelper->getOptionHasriskArray($userid, $optionNumber,
                  $appendB, $parentId,$lang,$tLang);

                $optionText0  = 
                  $oInferenceHelper->getLinkReturnExitArray($optionNumber,$zero, $lang,$tLang);
                $optionText = array_merge($optionText,$optionText0);  

                $state = 806;
                $inLoop = 0;
                break;	

              ////////////////////////////////////////////////////////////////////
              /**  State  58                                                   **/
              /*   Option pick from requires  list                              */				   
              case 58:   

                $hasReturn = 0;  
                $zero = 0;     
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText); 
                $edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 
                $parentId  = $edOptionNumber;                  

                $optionText0  =  $oInferenceHelper->getSolOptExtDataArray($userid,$orgid,
                   $edOptionNumber,$optionNumber, $inText,$hasReturn, $lang, $tLang);

                $hasAddress = $oFunctionHelper->hasAddress($optiontext0,$addressKey); 

                $optionText1 = 
                $oInferenceHelper->getOptionRequiresArray($userid, $edOptionNumber, $hasReturn,
                 $parentId, $lang, $tLang);

                if (!empty($optionText0)) {
                   $optionText = $optionText0;
                   if (!empty($optionText1)) {
                      $optionText = array_merge($optionText0,$optionText1); 
                   }
                } else {
                   if (!empty($optionText1)) {
                      $optionText = $optionText1; 
                   }                  
                }               

                $optionText = array_merge($optionText0,$optionText1); 

                if (empty($optionText)) {
                    $hasReturn = 2;
                    $optionText = 
                      $oInferenceHelper->getRequiresContinueExitArray($optionNumber,$hasReturn);
                    $state = 822;        
                } else {
                    $state = 830;

                    $optionText0  = 
                       $oInferenceHelper->getLinkReturnExitArray($optionNumber,$zero,$lang,$tLang);
                    $optionText = array_merge($optionText,$optionText0); 

                    // if extended data attname contanis address, set state= 832
                    if ($hasAddress == 1) {
                        $state = 832;
                    } 

                }
                $inLoop = 0;
                break;					  

              ////////////////////////////////////////////////////////////////////
              /**  State  62                                                   **/
              /*   Option pick from requires                                    */				  
              case 62:

                $appendB = 0;
                $zero = 0;
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);	
                $optionText  = $oInferenceHelper->getOptionRequiresArray($userid, $optionNumber,
                  $appendB, $zero, $lang, $tLang);
                $optionText0  = 
                  $oInferenceHelper->getLinkReturnExitArray($optionNumber, $zero, $lang, $tLang);
                $optionText = array_merge($optionText,$optionText0); 

                $state = 810;
                $inLoop = 0;
                break;

              ////////////////////////////////////////////////////////////////////
              /**  State  66                                                   **/
              /*   Option pick from has option list                             */				  
              case 66: 

                $hasReturn = 0;		
                $zero  = 0;	  
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);	
                $edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 
                $parentId  = $edOptionNumber; 

                $optionText0  =  $oInferenceHelper->getSolOptExtDataArray($userid,$orgid,
                   $edOptionNumber,$optionNumber, $inText,$hasReturn, $lang, $tLang);

                $hasAddress = $oFunctionHelper->hasAddress($optionText0,$addressKey);

                $optionText1 = 
                 $oInferenceHelper->getOptionHasoptionArray($userid, $edOptionNumber, $hasReturn,
                    $parentId, $lang, $tLang);


                if (!empty($optionText0)) {
                   $optionText = $optionText0;
                	 if (!empty($optionText1)) {
                      $optionText = array_merge($optionText0,$optionText1); 
                	 }
                } else {
                   if (!empty($optionText1)) {
                      $optionText = $optionText1; 
                	 }                	
                }

                $optionText = array_merge($optionText0,$optionText1); 


                if (empty($optionText)) {
                    $hasReturn = 2;
                    $optionText = $oInferenceHelper->getHasOptionContinueExitArray($optionNumber,$hasReturn);
                    $state = 822;      	
                } else {
                    $state = 830;
                    $optionText0  = 
                       $oInferenceHelper->getLinkReturnExitArray($optionNumber, $zero, $lang, $tLang);
                    $optionText = array_merge($optionText,$optionText0); 

                    // if extended data attname contanis address, set stat= 832
                    if ($hasAddress == 1) {
                        $state = 832;
                    }

                }   
                $inLoop = 0;
                break;

              ////////////////////////////////////////////////////////////////////
              /**  State  70                                                   **/
              /*   Option pick from has option                                  */				   
              case 70:   
			  
                $appendB = 0;
                $soParentId = 0;
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);	
                $optionText  = 
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber,$appendB,
                     $soParentId, $lang, $tLang);
                $optionText0  = 
                  $oInferenceHelper->getLinkExitArray($optionNumber, $zero,$lang, $tLang);
                $optionText = array_merge($optionText,$optionText0); 
                $state = 814;
                $inLoop = 0;

                break;				  
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  74                                                   **/
              /*   Process options for chatbot views: has risk                  */			  
              case 74:

                $hasReturn = 1;	
                $parentId = 0;		  
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);
                $optionText[]  = 
                  $oInferenceHelper->getOptionHasriskArray($userid, $optionNumber,$hasReturn,
                   $parentId,$lang,$tLang);				  
                if (empty($optionText0)) { 
                    $state = 991;    // no solution options found
                } else { 
                    $state = 806;    // solution option found
                }
                $inLoop = 0;	
                break; 
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  78                                                   **/
              /*   Process options for chatbot views: has risk continue         */			  
              case 78:  	

                $hasReturn = 1;				  
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);
                $optionText0  = 
                  $oInferenceHelper->getOptionRequiresArray($userid, $optionNumber, $hasReturn,
                    $zero, $lang, $tLang);				  
                if (empty($optionText0)) { 
                    $state = 991;    // no solution options found
                } else { 
                    $state = 810;    // solution option found    
                    $optionText = array_merge($optionText,$optionText0);
                }	
                $inLoop = 0;                
                break;
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  82                                                   **/
              /*   Process options for chatbot views: has risk exit             */			   
              case 82:  

                $hasReturn = 1;				  
                $optionNumber = $oFunctionHelper->getOptionNumber($inText);
                $optionText[] = 
                  $oInferenceHelper->getOptionRequiresArray($userid, $optionNumber,$hasReturn, 
                    $zero, $lang, $tLang);				  
                if (empty($optionText)) { 
                    $state = 991;    // no solution options found
                } else { 
                    $state = 810;    // solution option found   
                }	
                $inLoop = 0;
                break;
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  86                                                   **/
              /*   Process options for chatbot views: requires                  */			  
              case 86:  	

                $parentId = 0;			  
                $optionNumber = $oFunctionHelper->getOptionNumber($inText);
                $optionText[] = 
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber, 
                    $parentId, $zero, $lang, $tLang);				  
                if (empty($optionText)) { 
                    $state = 991;    // no solution options found
                } else { 
                   $state = 806;    // solution option found 
                }	
                $inLoop = 0;
                break; 
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  90                                                   **/
              /*   Process options for chatbot views: requires continue         */			  
              case 90:  
			  
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);
                $optionText0  = 
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber,
                    $zero, $zero, $lang, $tLang);
				  
                if (empty($optionText0)) { 
                    $state = 991;    // no solution options found
                } else { 
                    $state = 814;    // solution option found   
                    $optionText = array_merge($optionText,$optionText0);   
                }			
                $inLoop = 0;                		  
                break;
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  94                                                   **/
              /*   Process options for chatbot views: requires exit             */			  
              case 94:  
			  
                $optionNumber = $oFunctionHelper->getOptionNumber($inText);
                $optionText0  = 
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber, 
                    $zero, $zero, $lang, $tLang);				  
                if (empty($optionText0)) { 
                    $state = 991;    // no solution options found
                } else { 
                    $state = 810;    // solution option found    
                    $optionText = array_merge($optionText,$optionText0);   
                }	
                $inLoop = 0;                
                break;				  
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  98                                                   **/
              /*   Process options for chatbot views: has option                */			  
              case 98: 

                $optionNumber = $oFunctionHelper->getOptionNumber($inText);
                $optionText0  = 
                   $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber,
                    $zero, $zero, $lang, $tLang);				  
                if (empty($optionText0)) { 
                    $state = 991;    // no solution options found
                } else { 
                    $state = 814;    // solution option found  
                    $optionText = array_merge($optionText,$optionText0);   
                }	
                $inLoop = 0;
                break; 
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  102                                                  **/
              /*   Process options for chatbot views: has option   return       */			   
              case 102: 	
			  
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);
                $optionText0  = 
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber, 
                    $zero, $zero, $lang, $tLang);				  
                if (empty($optionText0)) { 
                    $state = 991;    // no solution options found
                    $optionText = array_merge($optionText,$optionText0);  
                    $inLoop = 0;
                } else { 
                    $state = 34;    // return from last option. display solutions  
                    $optionText = array_merge($optionText,$optionText0);   
                }	
                break;
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  106                                                  **/
              /*   Process options for chatbot views: has option exit          */			  
              case 106: 
			  
                $optionNumber = $oFunctionHelper->getOptionNumber($inText);
                $optionText0  = 
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber, 
                    $zero, $zero, $lang, $tLang);				  
                if (empty($optionText0)) { 
                    $state = 991;    // no solution options found
                    $optionText = array_merge($optionText,$optionText0); 
                } else { 
                    $state = 818;    // solution option found  
                    $optionText = array_merge($optionText,$optionText0);   
                }
                $inLoop = 0;	
                break;				  
				 
              ////////////////////////////////////////////////////////////////////
              /**  State  110                                                  **/
              /*   Process options for chatbot views: has risk continue 2       */	
              /*   Redisplay  list of risks                                     */			  
              case 110:

                $hasReturn = 2;
                $parentId = 0;
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);
                $optionText0  = 
                  $oInferenceHelper->getOptionHasriskArray($userid, $optionNumber,
                   $hasReturn, $parentId,$lang,$tLang);				  
                if (empty($optionText0)) { 
                    $state = 991;    // no solution options found
                } else { 
                    $state = 806;    // solution option found    
                    $optionText = array_merge($optionText,$optionText0);   
                }	
                $inLoop = 0;
                break;	
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  114                                                  **/
              /*   Process options for chatbot views: requires continue 2       */	
              /*   Redisplay  list of requirements                              */			  
              case 114: 

                $hasReturn = 2;			  
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);
                $optionText0  = 
                  $oInferenceHelper->getOptionRequiresArray($userid, $optionNumber,$hasReturn, 
                    $zero, $lang, $tLang);				  
                if (empty($optionText0)) { 
                    $state = 991;    // no solution options found
                    $optionText = array_merge($optionText,$optionText0); 
                } else { 
                    $state = 810;    // solution option found
                    $optionText = array_merge($optionText,$optionText0);   
                }	
                $inLoop = 0;
                break;				  

              ////////////////////////////////////////////////////////////////////
              /**  State  118                                                  **/
              /*   Process options for chatbot views: requires continue 2       */	
              /*   Redisplay  list of has options                               */			  
              //case 43:
              case 118:

                $hasReturn = 2;			  
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);
                $optionText0 = 
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber,$hasReturn,
                    $zero, $lang, $tLang);				  
                if (empty($optionText)) { 
                    $state = 991;    // no solution options found
                    $optionText = array_merge($optionText,$optionText0); 
                } else { 
                    $state = 814;    // solution option found
                    $optionText = array_merge($optionText,$optionText0);   
                }	
                $inLoop = 0;
                break;		

              ////////////////////////////////////////////////////////////////////
              /**  State  122                                                  **/
              /*   Preprocessing subset: pick from list                         */        
              case 122: 

                  $parentId = 0;
                  $appenddB = 0;
                  $sfssId    = $oFunctionHelper->getOptionNumber($inText);        
                  $oInferenceHelper->makeSubsetUniqueFact($userid, $sfssId);  
                  $hasSubset = $oInferenceHelper->hasSubset($userid);
                  $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);
                  $optionText = $oInferenceHelper->getOptionHasriskArray($userid,
                    $optionNumber, $appendB, $parentId,$lang,$tLang);   

                  // further deeper level ?   
                  if ($hasSubset > 0 ) { 
                      $hasSubset = $oInferenceHelper->makeSubsetFact($userid); 
                      $state = 30;    // has subset 
                  } else { 
                      $state = 18;    // no subset. Proceed to processing
                  }   
 
                  break; 

              ////////////////////////////////////////////////////////////////////
              /**  State  300                                                  **/
              /*   Process sliding bar PART 1                                   */        
              case 300:         

                //1.prepare data

                //2. make solution fact
                $inquiryText = $oFunctionHelper->getInquiry($inText);  
                $oInferenceHelper->makeSolutionFact($aSplitText, $userid, $inquiryText); 

                $sfNetRating = 
                  $oInferenceHelper->makeFactRating($personalityId,$userid,$TSStrategy,$isLex);

                $slideBarText = $oInferenceHelper->getSlideBarArray1($userid); 
                $buttonText = $oInferenceHelper->getSlideBarArray2($userid);
                $state = 900;
                $inLoop = 0;

                break; 

              ////////////////////////////////////////////////////////////////////
              /**  State  301                                                  **/
              /*   Process sliding bar:  skip and continue                      */        
              case 301:
 
                // 1.makeSolutionFact is done. makeSolutioRelation
                // 2.$state = 22;   Next three lines are a temporary patch
                // 1.and 2. are normal flow.
                $inquiry = "";
                $inText = strtolower(trim($inquiry));
                $inquiryText = $oFunctionHelper->getInquiry($inText);  

                $state = 22;
                break; 

              ////////////////////////////////////////////////////////////////////
              /**  State  302                                                  **/
              /*   Process sliding bar: save and contiue                        */        
              case 302:
 
                // find and save solutions with slide bar parameters

                $hasBar = 0;
                $isLex = 0;

                if ($slidebar == "") {
                  $hasBar = 0;                 
                } else {

                  $slidebar = $oFunctionHelper->getSBArray($slidebar); 
                  $slidebar = $oParsingHelper->getSBTermArray($slidebar);
                  $hasBar = 1; 
                  $asb = $slidebar;
                  $slidebar0 = "";
                  foreach($asb as $key=>$value) {
                     $sk = strval($key);
                     $sv = strval($value);
                     $slidebar0 = $slidebar0.$sk."=".$sv.",";
                  }           
                  $oInferenceHelper->updatePersonalityValue($personalityId,$slidebar,$orgid,$userid); 
                }

                $oInferenceHelper->makeSolutionRelation($personalityId, $userid, 
                     $orgid, $hasBar, $slidebar, $isLex, $state, $lang ); 
                
                $oInferenceHelper->makeRelationExtendedData($userid, $orgid, $portalType);  

                // makeSolutionRelation
                $state = 26;    
                                      
                break; 

              ////////////////////////////////////////////////////////////////////
              /**  State  818                                                  **/
              /*   Exit                                                        */        
              case 818:
 
                $inLoop = 0; 
                break;
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  900                                                  **/
              /*   Exit                                                        */        
              case 900:
 
                $inLoop = 0; 
                break;			  

				 
              ////////////////////////////////////////////////////////////////////		
            }		// END OF CASE	
       }   // END OF SWITCH

	   ///////////  FSM ENDS HERE  ///////////////	


       /////////////// START OF SEND RESPONSE /////////////
        $outState = $state;
        if ($state == 900) {     // logic to handle slidebar parameters
           $outState = 300;
        }   

        switch ($state) { 

            ///////////////////////////////////////////////////////////////////   
            /**  State 802                                                  **/           
            /*   exit with solution relations                                */   
            /* I found these results. Please select one for more information */       
            case 802:
        
                $code = 11;
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);
                //$message = " I found these results. Please select one for more information. ";


                  $apiresponse = 
                   [
                      'response' => 
                         [
                          'type'    => 'radiobutton', 
                          'message' => $message, 
                          'state'   => $state,
                          'lexState'=> $lexState,
                          'slotName'=> $slotName,
                          'language'=> $jsonLang,   
                          'contentType'=> $contentType,                        
                          'answers' => $solutionText
                         ]
                   ];           
            break;



               ///////////////////////////////////////////////////////////////////   
              /**  State 804                                                  **/           
              /*   exit with solution from lexClass                            */           
              case 804:
        
                //$message = 'I found these options. Please select one.';
                $code = 11;
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);

                $apiresponse = 
                   [
                      'response' => 
                         [
                          'type'    => 'mapping', 
                          'message' => $message, 
                          'state'   => $state,
                          'lexState'=> $lexState,
                          'slotName'=> $slotName,
                          'language'=> $jsonLang,  
                          'contentType'=> $contentType,                         
                          'mapping' => $jlexTMP
                         ]
                   ];         
    
                $inLoop = 0;               // exit loop        
                break;           

            ///////////////////////////////////////////////////////////////////   
            /**  State 806                                                  **/           
            /*   exit with solution option: has risk                         */   
            /*   I found this additional information. Click on one for 
                   additional information, Back to continue, or Exit to start a new topic. */       
            case 806:  
          
                $code = 30;   
                //$message = " This item has additional information. Click on one for additional information, Back to continue, or Exit to start a new topic. ";  
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                                  

                $apiresponse = 
                   [
                     'response' => 
                       [
                        'type'    => 'radiobutton', 
                        'message' => $message, 
                        'state'   => $state,
                        'language'=> $jsonLang,                        
                        'answers' => $optionText
                       ]
                   ];   

     
                break;

            ///////////////////////////////////////////////////////////////////   
            /**  State 810                                                  **/           
            /*   exit with solution option: requires                         */ 
            /*   I found this additional information. Click on one for 
                 additional information, Back to continue, or Exit to start a new topic. */                         
            case 810: 
          
                $code = 30; 
                //$message = " This item has additional information. Click on one for additional information, Back to continue, or Exit to start a new topic. "; 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                

                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'    => 'radiobutton', 
                        'message' => $message, 
                        'state'   => $state,
                        'language'=> $jsonLang,   
                        'contentType'=> $contentType,                      
                        'answers' => $optionText
                      ]
                  ];                 
                  break;  

            ///////////////////////////////////////////////////////////////////   
            /**  State 813                                                  **/           
            /*   exit with one solution.                                     */
            /*     Display extended data and linking term                    */
            case 813:   
                $message = "";
              
                $apiresponse = 
                   [
                      'response' => 
                         [
                           'type'    => 'radiobutton', 
                           'message' => $message, 
                           'state'   => $state,
                           'language'=> $jsonLang, 
                           'contentType'=> $contentType,                           
                           'answers' => $optionText
                         ]
                   ];                
                break;




            ///////////////////////////////////////////////////////////////////   
            /**  State 814                                                  **/           
            /*   exit with solution option: has option                       */
            /*   I found this additional information. Click on one for 
                  additional information, Back to continue, or Exit to start a new topic. */                           
            case 814:   
          
                $code = 52; // 29;  30 
                //$message = " Select an option for more information or Click Exit to start a new topic."; 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                

                $apiresponse = 
                   [
                      'response' => 
                         [
                           'type'    => 'radiobutton', 
                           'message' => $message, 
                           'state'   => $state,
                          'language'=> $jsonLang, 
                          'contentType'=> $contentType,                           
                           'answers' => $optionText
                         ]
                   ];                
                break;    

            ///////////////////////////////////////////////////////////////////   
            /**  State 818                                                  **/           
            /*   exit after last continue / exit                            */
            /*   $message = Thank you! You may start another topic if you wish */           
            case 818:

                $code = 22;  
                //$message = " Thank you! You may start another topic if you wish. ";  
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);
          
                $apiresponse = 
                   [
                     'response' => 
                        [
                          'type'    => 'radiobutton', 
                          'message' => $message, 
                          'state'   => $state,
                          'language'=> $jsonLang, 
                          'contentType'=> $contentType,                          
                          'answers' => []
                        ]
                   ];               
                break;  

            ///////////////////////////////////////////////////////////////////   
            /**  State 822                                                 **/            
            /*   exit after processing continue / exit.                    **/
            /*   No further processing                                     */   
            /*    I do not have additional information.      
                   Click Back to continue, or Exit to start a new topic.     */
            case 822:

                $code= 46; 
                //$message = " I do not have additional information. Click Back to continue, or Exit to start a new topic.";                      
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);
                $apiresponse = 
                   [
                     'response' => 
                       [
                         'type'    => 'radiobutton', 
                         'message' => $message, 
                         'state'   => $state,
                         'language'=> $jsonLang, 
                         'contentType'=> $contentType,                           
                         'answers' => $optionText
                       ]
                   ];                
                break;          
          
            ///////////////////////////////////////////////////////////////////   
            /**  State 826                                                  **/           
            /*   exit after last  exit                                      */    
            /*   Thank you! You may start another topic if you wish.        */        
            case 826:

                $code = 44;
                //$message = " Thank you! You may start another topic if you wish."; 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                 
          
                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'    => 'radiobutton', 
                        'message' => $message, 
                        'state'   => $state,
                        'language'=> $jsonLang,    
                        'contentType'=> $contentType,                    
                        'answers' => []
                      ]
                  ];                  
                break;

            ///////////////////////////////////////////////////////////////////   
            /**  State 830                                                 **/            
            /*   exit after processing continue / exit.                    **/
            /*   solution option extended data found                        */  
            /*   I found this additional information.
                 Click Back to continue, or Exit to start a new topic.      */
            case 830: 

                $code= 48; // 28    
                //$message = " Click  Back to continue, or Exit to start a new topic."; 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                        

                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'    => 'radiobutton', 
                        'message' => $message, 
                        'state'   => $state,
                        'language'=> $jsonLang, 
                         'contentType'=> $contentType,                       
                        'answers' => $optionText
                      ]
                  ];                 
                break;  

            ///////////////////////////////////////////////////////////////////   
            /**  State 832                                                 **/            
            /*   exit after processing continue / exit.                    **/
            /*   solution option extended data found, attribute address    */  
            /*   I found this additional information.
                 Click Back to continue, or Exit to start a new topic.      */
            case 832: 

                $code= 48;   
                //$message = " Click  Back to continue, or Exit to start a new topic."; 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                        

                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'    => 'radiobutton', 
                        'message' => $message, 
                        'state'   => $state,
                        'language'=> $jsonLang,  
                         'contentType'=> $contentType,                      
                        'answers' => $optionText
                      ]
                  ];                 
                break;  

            ///////////////////////////////////////////////////////////////////   
            /**  State 838                                                  **/           
            /*   exit with extended data                                     */  
            /*   I found this additional information. Click on one for 
                  additional information, Back to continue, or Exit to start a new topic. */                          
            case 838:

                $edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 
                $optionText  = 
                   $oInferenceHelper->getSolOptExtDataArray($userid,$orgid,$edOptionNumber,
                   $optionNumber, $inText, $zero, $lang, $tLang);
  
                $code = 30;  
                //$message = " This item has additional information. Click on one for additional information, Back to continue, or Exit to start a new topic. ";     
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                   

                $apiresponse = 
                  [
                   'response' => 
                    [
                      'type'    => 'radiobutton', 
                      'message' => $message, 
                      'state'   => $state,
                      'language'=> $jsonLang,   
                      'contentType'=> $contentType,                   
                      'answers' => $optionText
                    ]
                  ];               
                break;

            ///////////////////////////////////////////////////////////////////    
            /**  State 841                                                  **/           
            /*   Extended data on Solution                                   */
            /*                                                               */   
            /*   I found this additional information.
                 Click Back to continue, or Exit to start a new topic.      */                       
            case 841:

                $code= 29; 
                $message = "";
               
                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'    => 'radiobutton', 
                        'message' => $message, 
                        'state'   => $state,
                        'language'=> $jsonLang,  
                         'contentType'=> $contentType,                      
                        'answers' => $optionText
                      ]
                  ];                
                break;

            ///////////////////////////////////////////////////////////////////    
            /**  State 842                                                  **/           
            /*   value  relexdata solRiskExdata solReqExdata solOptExdata    */
            /*    1        T           F              F            F         */  
            /*   I found this additional information. Click on one for 
                   additional information, Back to continue, or Exit to start a new topic. */                            
            case 842:

                $code = 30;
                //$message = " This item has additional information. Click on one for additional information, Back to continue, or Exit to start a new topic. ";    
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                                   
                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'    => 'radiobutton', 
                        'message' => $message, 
                        'state'   => $state,
                        'language'=> $jsonLang,  
                         'contentType'=> $contentType,                      
                        'answers' => $optionText
                      ]
                  ];         
                break; 

            ///////////////////////////////////////////////////////////////////    
            /**  State 900                                                  **/           
            /*   Slide bar display                                           */
            /*   Thank you! For a better understanding of your preferences,
                 please rate these values as they generally apply to you.
                 */
            case 900:

                $state = 300;
                $code = 32;  
                //$message = " Thank you! For a better understanding of your preferences, please rate these values as they generally apply to you.";   
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);

                $apiresponse = 
                   [
                      'response' => 
                         [
                           'type'    => 'radiobutton', 
                           'message' => $message, 
                           'state'   => $state,
                           'language'=> $jsonLang,   
                           'contentType'=> $contentType,                        
                           'slidebar' => $slideBarText,
                           'buttons' => $buttonText
                         ]
                   ];
       
                break;

            ///////////////////////////////////////////////////////////////////   
            /**  State 990                                                  **/           
            /*   input:        yes/no                                        */
            /*   output:       Write here ...                                */       
            case 990:

                $code = 42;
                //$message = " Write here ... ";
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                
                  
                $apiresponse = 
                  [
                    'response' => 
                      [
                       'type'    => 'text', 
                       'message' => $message, 
                       'state'   => $state,
                       'language'=> $jsonLang, 
                       'contentType'=> $contentType,                      
                       'answers' => []
                      ]
                  ];                 
                break;
          
            ///////////////////////////////////////////////////////////////////   
            /**  State 991                                                  **/           
            /*   exit: solution relation found, no solution options          */ 
            /*   message = I did not find additional inforamtion             */         
            case 991:

                $code = 40; 
                //$message = " I did not find additional information.";
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                


                $apiresponse = 
                [
                  'response' => 
                    [
                        'type'    => 'radiobutton', 
                        'message' => $message,
                        'state'   => $state,     
                        'language'=> $jsonLang, 
                         'contentType'=> $contentType,                                  
                        'answers' => $optionText
                    ]
                ];    


                $inLoop = 0;               // exit loop        
                break;

            ////////////////////////////////////////////////////////////////  
            /**  State 998                                               **/            
            /*   exit with I do not understand                            */  
            /*   code = 36  I'm sorry. I do not understand                */
            /*   code = 38  I'm sorry, I'm still not understanding you.   */        
            case 998:
        
                if($inState == 998) {
                   $code = 38;
                   //$message = " I'm sorry, I still don't understand. You can rephrase or you can select Contact for other ways of reaching us.";
                } else {                      
                   $code = 36;
                   //$message = " I'm sorry. I do not understand.";
                } 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);  
         
                  $apiresponse = 
                   [
                      'response' => 
                         [
                          'type'    => 'radiobutton', 
                          'message' => $message, 
                          'state'   => $state,
                          'lexState'=> $lexState,
                          'slotName'=> $slotName,
                          'language'=> $jsonLang,    
                          'contentType'=> $contentType,                      
                          'answers' => []
                         ]
                   ];                   
          
                $inLoop = 0;              // exit loop       
                break;

            /////////////////////////////////////////////////////////////// 
            /**  State 999                                              **/           
            /*  exit with I could not find a solution                    */   
            /*   code = 34  Thak you for shring that. I do not detect ..    */
            /*   code = 39  I'm sorry, I still do not detect any issue ..   */                        
            case 999: 
                $sinState = strval($inState);

                if($inState == 999) {
                   $code = 39;
                   //$message = " I'm sorry, I still do not detect any issue that I can help you with.  You can rephrase or you can select Contact for other ways of reaching us.";
                } else {                      
                   $code = 34;
                   //$message = " Thank you for sharing that. I do not detect any issue that I can help you with.";
                } 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang); 

                  $apiresponse = 
                   [
                      'response' => 
                         [
                          'type'    => 'radiobutton', 
                          'message' => $message, 
                          'state'   => $state,
                          'lexState'=> $lexState,
                          'slotName'=> $slotName,
                          'language'=> $jsonLang,  
                          'contentType'=> $contentType,                        
                          'answers' => []
                         ]
                   ];  

                break;  


        } // end of switch



      // insert solution relation record with state and language info
      $nZero  = 0;
      $sBlank = "";
      $oSolutionRelation->insertRelation($nZero,$sBlank, $nZero, $nZero,
           $nZero, $nZero,$nZero,$sBlank, $sBlank, $nZero, $nZero,$nZero,
           $userid, $outState, $lang);
       return $apiresponse; 
       ////////////// END OF SEND RESPONSE  ////////////
 
    }	  // end of show()
}