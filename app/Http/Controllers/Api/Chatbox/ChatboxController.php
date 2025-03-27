<?php
/*--------------------------------------------------------------------------------
 *  File          : ChatBoxController.php        
 *  Type          : Controller 
 *  Function      : Provides logic and control for a chatbot.
 *                  This controller implements an orchestrator and a state machine.
 *                  Orchestrator: integrates the javascript view and the php chatbot controller.
 *                  State machine: provides parsing and inferencing functions in an interactive way.
 *                  The state machine uses: (a) a while cycle; (b) switch statement.
 *  New features  :  Improved state machine/switch statements
 *                   Full sliding bar parameter processing
 *                   messages handled by database table
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation. 
 *  Version       : 1.4.5   
 *  Updated       : 07 September 2020
 *                  18 November 2020
 *                  Portal type trapping
 *                  Full keyword rating
 *                  Full search in segment 
 *                  Greeting management                    (FSD-000257)
 *                  Improved back buuton logic             (FSD-000308)
 *                  Advance search using term name set     (FSD-000309)
 *
 *---------------------------------------------------------------------------------*/

namespace App\Http\Controllers\Api\Chatbox;

use Illuminate\Http\Request;
use App\Chatbox;
use App\Controllers;
use App\Helpers\InferenceHelper;
use App\Helpers\ParsingHelper;
use App\Helpers\FunctionHelper;
use App\Models\ConsumerUserPersonality;
use App\Models\Personality;
use App\Models\SolutionFact;
use App\Models\SolutionRelation;
use App\Models\RelationTypeFilter;;
use App\Models\SolutionRelationExdata;
use App\Models\SolutionOption;
use App\Models\SolutionOptionExdata;
use App\Http\Resources\Chatbox as ChatboxResource;

class ChatboxController extends \App\Http\Controllers\Controller{
	
	
    /*-------------------------------------------------
       Controller to handle chatbot conversations           -
       input: 
         int $userid   : user id
         int $orgid    : organization id
         str $inquiry  : input text
         int $state    : optional; default = 0
       output
          response
            'type'       => 'radiobutton', 
            'message'    => $message, 
            'state'      => $state,
            'language'   => $jsonLang, 
            'contentType'=> $contentType,                       
            'answers'    => []

	/-------------------------------------------------*/
	
	//-----------------------------------------------------------------------------------
	public function wordCheck(Request $request){
		$userID = trim($request->input('userid'));
		$orgID  = trim($request->input('orgid' ));
		$word   = trim($request->input('word'  ));
		if($userID==null){ return \Response::json([ 'message' => 'UserID not defined' ], 400); }
		if($orgID ==null){ return \Response::json([ 'message' => 'orgID  not defined' ], 400); }
		if(!$request->has('word')){ return \Response::json([ 'message' => 'Word not defined 1'], 400); }

		if($word==''){ return \Response::json([ 'message' => 'Word not found in database'], 204); }
		return \Response::json([ 'message' => 'Word in database' ], 200);
	}

	//------------------------------------------------------------------------------------
	public function newShow(Request $request){

      $userId  = trim($request->input('userid' ));
      $orgId   = trim($request->input('orgid'  ));
      $state   = trim($request->input('state'  )); 
      $inquiry = trim($request->input('inquiry'));
	    $inLang  = null;

      if ($request->has('language')) {
         $inLang  = trim($request->input('language'));
      } 

      $api_key = ""; 
           
      if ($request->has('apikey')) {
         $api_key = trim($request->input('apikey'));
      }

      if($userId ==null){ return \Response::json([ 'message' => 'UserID not defined' ], 400); }
      if($orgId  ==null){ return \Response::json([ 'message' => 'orgID  not defined' ], 400); }
      if($inquiry==null){ return \Response::json([ 'message' => 'inquiry not defined' ], 400); }

      $state = (($state==null) ?0 : $state);
      $srId = 0;
      $oSolutionRelation    = new SolutionRelation();  
      $state = $oSolutionRelation->retrieveState($srId,$userId); 

      // get message and utterance
      $inquiry2 = json_decode( utf8_encode($inquiry), true );
      $utterance = "";                          // default utterance
      $termSet   = "";                          // default termSet
      $message   = "";


      foreach($inquiry2 as $rs0){ 
         $message = $rs0['message'];

         if (isset($rs0['utterance'])) {           // read utterance if it is set
             $utterance = $rs0['utterance'];
         }
         if (isset($rs0['termSet'])) {           // read termSet if it is set
             $termSet = $rs0['termSet'];
         }
       }

      //// get language parameters     /////
      $stringBar = "";
      $aLang = json_decode( utf8_encode($inLang), true );     


      // if state=300 and input is not slide bars
     
      if ($state == 300) {  
      	 $messageType = gettype($message);
         if ($messageType == "string") {            // message is string
            $substr =    substr($message,0,5); 
            if ($substr == "*301*" or $substr == "*302*") {       // user clicked a button   
               $stringBar = "";   
            } else {
               $state = 0;           
            }
         }     
      }

     
      if ($state == 300) { 
        $jsonTmp = json_decode( utf8_encode($inquiry) );

        $stringBar = "";
        foreach($jsonTmp->request->answers as $key=>$value){ 
          $bar[$value->text] = $value->value;
          $clave = $value->text;
          $valor = $value->value;

          if ($clave !="") {
           $stringBar = ",".$stringBar. $clave. ",". $valor.";";            
          }
       
        }

         // sanitize $stringBar

         $pos = strlen($stringBar) -1;
         $stringBar = substr_replace($stringBar, '',$pos,1);
         $stringBar = substr_replace($stringBar, '  ',0, 3);
         $stringBar = trim($stringBar);

         $inquiry0['message'] = $message;  
         $inquiry0['slidebar'] = $stringBar; 

      } else {
        // regular input: only message
         $inquiry0 = $message;    	
      }

      if (!isset($state)) {
    	  $state = 0;
      }


	  $returnShow = $this->show($userId, $orgId,$state, $inquiry0, $aLang, $api_key, $utterance, $termSet);

    if (is_string($inquiry0)) {
        $tmpLog = trim($inquiry0);            
    } else {
        $tmpLog = $inquiry0;            
    }

      // save logging

//	  \App\Logs\KamaLogClass::addLog($api_key, $userId, 'user', json_encode($inquiry2['request']), trim($inquiry));
//	  \App\Logs\KamaLogClass::addLog($api_key, $userId, 'AI', json_encode($returnShow), '-');


	  return $returnShow;

	}

	
	//--------------------------------------------------------------------------------------
  //   MAIN LOGIC OF THE CONTROLLER
  //--------------------------------------------------------------------------------------
	public function show($userid, $orgid, $state, $inquiry, $aLang, $apikey, $utterance, $termSet ) {

	///////////////

		
        // Instantiate classes
        $oInferenceHelper          = new InferenceHelper();
        $oParsingHelper            = new ParsingHelper();
        $oFunctionHelper           = new FunctionHelper();
        $oConsumerUserPersonality  = new ConsumerUserPersonality();
        $oPersonality              = new Personality();
        $oMessage                  = new \App\Models\Message();        
        $oSolutionFact             = new SolutionFact();;
        $oSolutionRelation         = new SolutionRelation();
        $oSolRelExdata             = new SolutionRelationExdata();
        $oSolutionOption           = new SolutionOption();
        $oSolutionOptionExdata     = new SolutionOptionExdata();
        $oRTFilter                 = new RelationTypeFilter();

        //---------------- Initial processing ------------------------

        // get state variable
        $srId = 0;              
        $oSolutionRelation->deleteState($srId,$userid);  
        if (!isset($state)) {
        	$state = 0;
        }

        ///// start of  testing language input   //////////
        $langParameter  = $aLang;            // save langauge parameter

        $baseLang     = "en";               // base language
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
        //$tLang                 = $decidedLang ;    // target language when multilange is enabled.
        $tLang                 = $baseLang;        // temporary fix. Multilanguage is disabled


        // if decidedLang is a supported language then messageLang = decidedLang
        //if ($decidedLang == "en" or $decidedLang == "fr") {
        //   $messageLang = $decidedLang;
        //}
        $jsonLang['message']   = $messageLang;     // language for messages

        ///// end of testing language input  /////////

        // get inquiry parameters: message and slide bars
        $slidebar ="";
        $inState = $state;         // save incoming state 
        $textOrgId = $orgid;       // NULL: default standard messages; $orgid: specific messages
        $sinButton = "";

        if ($state == 300) {              // slide bar parameters
            $inText   = $inquiry['message'];
            $slidebar = $inquiry['slidebar'];
            $sinButton = substr($inText,0,5);

            if (substr($slidebar,0,1) == ",")  {
               $slidebar = substr($slidebar,1);
            }

        } else {
            $inText = strtolower(trim($inquiry));
            $sinButton = substr($inText,0,5);
        }


        //// SPECIAL SETTINGS /////////////////////////////////////////////

        //-- special input ------------------------------
        $cmdLonginput = "cmd_longinput";

        //-- greeting management ----------------------------
        $sGreeting = "";

        //-- parameter used for selecting attribute address in extended data
        $addressKey = "Street address"; 

        //-- email default variables --------------------
        $toemail  = "gabriel@kama-dei.com";                     
        $emailsubject = " NO SOLUTION FOUND";

        // --- content type, Portal type
        $contentType = "kr";      // content type default value
        $portalType = "text";     // content type default value

        // --- Initial values
        $flowState = 0; 
        $attributeState = 0;
        $kwState  = 0;
        $wState = 0;                  //  used for handling multi keyword processing
        $isfound = 0;
        $inquiry2 = "";
        $inquiry2len = 0;
        $rmTerm = "";
        $rmTermlen = 0;
        $sTerm = "";
        $sTermlen = 0; 
        $zero = 0;                    // calculated flow state
        $one  = 1;
        $optionText = array();
        $exdataState = 0;
        $isLex = 0;
        $sSegment = "";      //default segment
        $segmentLen = 0;
        $hasSolution = 0;
        $hasSolutionRelation =0; // return value from  makeSolutionRelation()
        $useShortText = 0;     // 1: use short text in response format; 0: use default 
        $isBack  = 0;

        // --- Advanced search ------------------------
        $isAdvancedSearch  = 1;      //  0: diseabled; 1:enabled
        $advancedSearchOn  = 0;      //  0: not triggerd; 1: triggered

        // -- advance search test with termSet
        $termSet = implode(",",$termSet);  

        // --- Processing method    -------------------    
        $TSStrategy = 2;     // 0  First match; 1 First negative match; 2 all negative matches

        //----- slide bars processing   -------------
        $SBstate = 0; 
        $midState =  0;
        //---------------------------------------------------------------        

        if (isset($apikey)) {  
           $portalType = $oParsingHelper->getPortalType($apikey); 
        }

        $singleUtterance =  $oParsingHelper->isSingleUtterance($utterance);
        $multipleTriple = 0;                           // default= 0 -> 1 triple;
        $mainConcept = 0;                              // default: 0 -> main concepto not detected 

        $inquiryText = $oFunctionHelper->getInquiry($inText);  
        $personalityId = $oConsumerUserPersonality->retrievePersonalityByUserOrg($userid, $orgid);
        $personaId     = $oPersonality->retrieveParentPersonality($personalityId);
        $useShortText  = $oRTFilter->canUseShortText($orgid);
   
//echo " personalityId=$personalityId;  ";
       /*--------------------------------------------------------
        *  Clear previous states
        *--------------------------------------------------------*/
        if ($inState !=998 or $inState!=999) {
           $state = 0;
        } elseif ($inState !=300) {
           $state = 0;
        }
             
        
       /** -- Orchestrator        --------------------------------------            
        *    Analyze input text and tell whether input is
        *    - yes or no
        *    - relation pick. A list of solution relations has been submitted 
        *      to the user, and he/she picks a relation.
        *    - option pick. A list of solution options has been submitted 
        *      to the user, and he/she picks an option.	
        *    - otherwise: process inquiry		 
        *---------------------------------------------------------------*/

        // check flow hint: at the middle of a conversation flow ?
        $flowHint = $oFunctionHelper->isFlowHint($inText);

        if ($flowHint) {
           $state = $oInferenceHelper->getFlowState($inText);
           $flowState = $state;
           //$inState = 0;
           //$SBstate = 0;
           $isBack = 1;
        } else {
           $state = 0;
           if ($oFunctionHelper->isYesNo($inText)) {    // check for yes or no 
              $state = 990;	
           } 
        }			
  
        $flowState = $state;     
 
       /**-- End of orchestrator   -----------------------------------*/


        /** default state                               **/
        if ($state == 0) {
            $state = 1;
        }    

        //$inState = $state;
        $inSlidebar = $slidebar;

       ///////////  FSM (FINITE STATE MACHINE) BEGINS HERE  ///////////////

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

                $inputText = $oFunctionHelper->removeExtraBlank($inquiry);
                $inputText = $oFunctionHelper->replacePeriodByAnd($inputText);
                $inputText = $oFunctionHelper->replaceButByAnd($inputText);
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

                  //$state = 22;             // make solution
                  $state = 18;               // mak fact ratings
                }
 
                // catch long input               
                if ($inquiry  == $cmdLonginput) {
                    $state = 997;    // set state
                    $inLoop = 0;     // exit state machine
                }

                break;                 
				  
              /////////////////////////////////////////////////////////////////////////
              /**  State 4                                                          **/		
              /*   resolve input text                                                */	
              /*   $inputText: input text, delimited by commas: Examples             */
              /*           i,am,hungry                                               */
              /*           i,am,hungry,and,i,am,tired                                */
              /*   $aSplitText: an array that can be processed by the controller     */
              /*     Examples:                                                       */
              /*      $aSplitText = {(i,am,hungry)}                                  */
              /*      $aSplitText = { (i,am,hungry), (i,am,tired)}                   */			  
              case 4:


               // $sSegment  = $oFunctionHelper->extractSegmento($inputText); 
                if ($advancedSearchOn == 0) {
                   $sSegment  = $oFunctionHelper->getSegment($inquiry); 
                }

                $segmentLen =  strlen($sSegment);           
                $sTerm     = $oParsingHelper->getRightKeyword($inputText); 
                $sTermlen = strlen($sTerm); 
                $rmTerm = $sSegment;
                $rmTermlen = strlen($rmTerm);       
                $aSplitText = $oParsingHelper->splitInputText($inputText);
                $sGreeting = $oInferenceHelper->getGreeting($aSplitText);

                if ($singleUtterance == 1 and $multipleTriple == 1) {
                   $mainConcept = 1;               // detect main concept
                }

                if ($sTerm == "") {   // is input a single term ?
                   $wState = 0;
                } else {
                   $wState = 323;
                }

                $state = 6;
//echo " CASE_4 state=$state; inputText=$inputText;  ";                       
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

                //$isInputError  = $oParsingHelper->isInputError($aSplitText);
                $isInputError= 0;

                $validationError = $oParsingHelper->validateTerms($aSplitText, $orgid);                   

                if ($validationError == 1) {
                    $state = 998;        // validation error
                    if ($advancedSearchOn > 0) {
                       $inLoop = 0;
                    } else {
                       $inLoop = 1;                     
                    }

                } else {
                    $state = 10;          // no validation error. proceed
                }


                if ($state == 998 ) {
                  if ($sTerm == "") {  
                      $state = 324;     // if no solution can be found, get keyword and process  
                      $inLoop = 1;  
                  }
                }        
//echo " CASE_6 state=$state; inputText=$inputText; validationError=$validationError; "; 
                break;

              ///////////////////////////////////////////////////////////
              /**  State 10                                            **/		
              /*  change term 1 to "person":                           */
              /*			  I am hungry -> person am hungry                */				  
              case 10: 
			  
                $aSplitText = $oParsingHelper->replaceTerm1ByPerson($aSplitText);
                $state = 14;                    //  No errors. Proceed 	

                if ($SBstate == 300) {          // process slide bars part 1
                   $state = 300;                  
                }               
 
                break;			  

              ////////////////////////////////////////////////////////////
              /**  State  14                                           **/	
              /*  Make solution facts                                   */ 			  
              /*  change verb. use relation type synonym:               */
              /*	person am hungry  -> person can be hungry             */
              /*  save session facts                                    */			  
              case 14: 
	  
                $inquiryText = $oFunctionHelper->getInquiry($inText);  
                $isRegularSet = $oInferenceHelper->isRegularSet($aSplitText);1;

                if ($wState == 323 or $rmTermlen > 0) {

                    if ($rmTermlen > 0) {
                       $sTerm = $rmTerm;                   
                    }

                    if ($mainConcept == 0) {
                       $delete = 1;
                       $oInferenceHelper->makeSingleSolutionFact($sTerm, $userid, $inquiryText); 
                       $oInferenceHelper->makeEquivalentSingleSolutionFact($userid, $orgid,$delete);
                    }
                    //
                } 

                $inquiry2 = implode($aSplitText,";");  
                $inquiry2len = strlen($inquiry2);    

           
                $delete = 0; 
                $oInferenceHelper->makeSolutionFact($aSplitText,$userid,$inquiryText,$singleUtterance,$multipleTriple); 
                $oInferenceHelper->makeEquivalentSolutionFact($userid, $orgid, $delete); 
                if ($isRegularSet == 1) {
                    $oInferenceHelper->removeNoiseFact($userid);  
                }
                
                //$hasSubset = $oInferenceHelper->makeSubsetFact($userid, $inquiryText);         

                // remove empty fact records
                $hasSubset = 0;
                //$oParsingHelper->removeEmptyFact($userid);

                if ($hasSubset == 1) {
                    $state = 30 ;
                } else { 
                    $state = 18;                   // Proceed 
                }
 
//echo " CASE_14 BB state=$state; "; 
                break; 			  
				  
              /////////////////////////////////////////////////////////////////////
              /**  State  18                                                    **/
              /*   make fact ratings: calculate and save session fact ratings    */	
              /*   $TSStrategy       0  First match; 
                                     1 First negative match; 
                                     2 all negative matches		                   */  			  
              case 18: 
		  
                $sfNetRating = 
                  $oInferenceHelper->makeFactRating($personalityId,$userid,$TSStrategy);			 
                $state = 20;

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
//echo " CASE_20 state=$state; "; 
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

                // find solution relation
                if ($kwState == 324) {
                  $delrec = 0;
                } else {
                  $delrec = 1;
                }

                $hasSolutionRelation =  $oInferenceHelper->makeSolutionRelation($personalityId, 
                    $userid, $orgid,$hasBar, $sBar,$isLex, $state, $lang, $delrec);	

                // if solution relations are found, make relation extended data
                if ($hasSolutionRelation ==1) {
                   $oInferenceHelper->makeRelationExtendedData($userid, $orgid, $portalType);  
                }

                $state = 26; 
//echo " CASE_22 state=$state; hasSolutionRelation = $hasSolutionRelation; ";                            
                break;              
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  26                                                   **/
              /*   make options: find and save solution options                 */	
              /*   make extended data: find and save extended data              */		    
              case 26: 

                // if there are solution relation, make solution option
                if ($hasSolutionRelation == 1) {
                   $oInferenceHelper->makeSolutionOption($userid);  // options 
                   $oInferenceHelper->makeSolutionOptionLink($userid); // linking option 
                   $oInferenceHelper->makeOptionExtendedData($userid, $orgid, $portalType);
                }
		 
                $state = 34;  //               	

                if ($rmTermlen > 0) {
                   $kwState = 324;
                }


                if ($kwState == 324) {
                    $state = 326;
                }
//echo "  CASE_26 state=$state; ";
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
                    $sfNetRating = $oInferenceHelper->makeFactRating($personalityId, $userid);      
                    $state = 22;    // 
                }

/////
                $slideBarText = $oInferenceHelper->getSlideBarArray1($userid, $personaId); 
                $buttonText = $oInferenceHelper->getSlideBarArray2($userid);

                if ($slideBarText == 0) {
                   //$sbtest900 = " SLIDEBAR; ";
                } else {
                   $state = 900;
                   $inLoop = 0;
                }
/////
		  
                break; 


              ////////////////////////////////////////////////////////////////////
              /**  State  34                                                   **/
              /*   Prepare solution relations for chatbot view                  */			  
              case 34:               

                $hasRating = 0;  // no ratings
                $isLex     = 0;  // this is not Lex=;  For testing isLex=99;

                $solutionText0 = 
                  $oInferenceHelper->getSolutionRelationArray($userid,$hasRating,$isLex,$lang,$tLang);

                //if (empty($solutionText0)) {      
                if (sizeof($solutionText0) == 0) {                               	
                    $state = 999;    // no solution found
                    $oSolutionFact->deleteByUser($userid);   // delete fact records from this user
                    $oSolutionRelation->deleteByUser($userid); // delete records  form this user

                    if ($advancedSearchOn > 0) {
                       $inLoop = 0;
                    } else {
                       $inLoop = 1;                     
                    }


                } else { 
                    $solutionText = $solutionText0;
                    $state = 802;    // solution relation found
                    $inLoop = 0;
                    // count solution text
                    $solCount = count($solutionText);
                    $hasSolution = 1;

                    // if there is only one solution, display options 
                    //////   CHECK     /////////////////////////////////////////////
                    if ($solCount == 1) {
                        $state = 38;
                        $inText = $solutionText['0']['value'];
                        $inLoop = 1;
                        $isfound = 3882;
                    }

                }	


                /*---- multi keyword processing  ---------------/
                If no solution if found (state = 999) then
                  trigger multi keyword processing
                */

                if ($state == 999 ) {

                   if ($segmentLen > 0) {
                      $state = 324;
                      $inLoop = 1; 
                   } else {
                      if ($sinButton =="*301*" or $sinButton =="*302*") {
                          $inLoop = 0;
                      }
                   }

                }     

                break; 				  
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  38                                                   **/
              /*   Solution relation pick                                       */		
              /*   get options: has risk                                        */                            

              /***  extended data display control                **********
               * $hasED = 0;
               * $hasSolRelExData = 0;    // solution relation has extended data (0=No/1=Yes)
               * $exdataState   = 0;      // $exdatastate has values related to extended data in  
                                             solution relation and solution
                 value  relexdata solRiskExdata solReqExdata solOptExdata
                   0        F           F              F            F
                   1        T           F              F            F
                   2        T           T
                   3        T           F              V
                   4        T           F              F            T

               */

              case 38:

                $hasRating = 0;  // no ratings
                $isLex     = 0;  // this is not Lex

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
                    $optionText  =  $oInferenceHelper->getSolRelExtDataArray($userid,$orgid,
                        $edOptionNumber,$optionNumber, $inText,$hasReturn, $lang, $tLang, $portalType);
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

                    //if ($inState == 322 or $inState == 323) { 
                    if ($isBack == 1) {           	
                        $optNum = "";
                        $optionText0 = $oInferenceHelper->getREButtonSolutionScreen($optNum,$lang,$tLang);
                        $optionText = array_merge($optionText,$optionText0);   
                    }
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
                             $optionNumber,$appendB,$parentId,$lang,$tLang);
                        $optionText0  = 
                        $oInferenceHelper->getLinkReturnExitArray($optionNumber,$zero,$lang,$tLang);
                        $optionText = array_merge($optionText,$optionText0);  
                        $state = 806;
                        $inLoop = 0;

                    } elseif ($hasSolReq == 1) { 
                        $appendB = 0;
                        $isBack = 1; 
                        $optionNumber = $oFunctionHelper->getOptionNumber($inText); 
                        $optionText  = $oInferenceHelper->getOptionRequiresArray($userid, 
                              $optionNumber,$appendB,$zero, $lang,$tLang);
                        $optionText0  = 
                             $oInferenceHelper->getLinkReturnExitArray($optionNumber,$isBack,$lang,$tLang);
                        $optionText = array_merge($optionText,$optionText0); 
                        $state = 810;
                    
                    } elseif ($hasSolOpt == 1) {
                        $appendB = 0;
                        $state = 813;          // before 814
                        $midState = 8140;

                        $optionNumber = $oFunctionHelper->getOptionNumber($inText); 
                        $edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 

                        $optionText2 =  $oInferenceHelper->getSolRelExtDataArray($userid,$orgid,
                          $edOptionNumber,$optionNumber, $inText,$hasReturn,$lang, $tLang, $portalType);

                        $optionText  = $oInferenceHelper->getOptionHasoptionArray($userid,
                              $optionNumber,$appendB,$zero,$lang,$tLang);
                        $optionText = array_merge($optionText2,$optionText);   
                        
                        if ($inState == 322) {
                          $optNum = "";
                          $optionText0 = $oInferenceHelper->getREButtonSolutionScreen($optNum,$lang,$tLang);

                        }  else {  
 ////////////////////   fix  ///////////////////////////////////////7  
                          /*/
                           if ($flowState == 38) {
                              $isBack = 1;
                           }  else {
                              $isBack = 0;
                           }
*/
                           if ($flowState == 38) {
                              $isBack = 1;
                              if ($inState == 830) {
                                $isBack = 0;
                              }

                           }  
                        $optionText0  = 
                          $oInferenceHelper->getLinkExitArray($optionNumber,$midState, $lang,$tLang,$isBack);
 /////////////////// end of fix  //////////////////////
                        }       

                        $optionText = array_merge($optionText,$optionText0); 

                    }
                }

                // add return / button to solution screen 
                if ($state == 842) {
                    $optNum = "";
                    $optionText0 = $oInferenceHelper->getREButtonSolutionScreen($optNum,$lang,$tLang);
                    $optionText = array_merge($optionText,$optionText0);  
                }
                $inLoop = 0;

                // check for empty solution
                if ($state == 991 and $hasSolution ==1) {
                   $solutionText = 
                    $oInferenceHelper->getSolutionRelationArray($userid,$hasRating,$isLex,$lang,$tLang);
                    $state = 802;
                }

                break; 
				  
              ////////////////////////////////////////////////////////////////////
              /**  State  42                                                   **/
              /*   Solution relation                                            */		
              /*   get options: requires                                        */			   
              case 42:	

                $parentId = 0;
                $optionNumber = $oFunctionHelper->getOptionNumber($inText);
                $optionText0  = $oInferenceHelper->getOptionRequiresArray($userid, $optionNumber,
                   $parentId,$zero, $lang, $tLang);	
				  
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
                $optionText0  = $oInferenceHelper->getOptionHasoptionArray($userid,
                    $optionNumber, $parentId, $zero, $lang, $tLang);	
				  
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
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText); 
                $edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 
                $parentId  = $edOptionNumber;                  

                $optionText0  =  $oInferenceHelper->getSolOptExtDataArray($userid,$orgid,
                   $edOptionNumber,$optionNumber, $inText,$hasReturn, $lang, $tLang, $portalType);

                //// Check whether extand data has address  /////
                $hasAddress = $oFunctionHelper->hasAddress($optionText0,$addressKey);

                $optionText1 = 
                 $oInferenceHelper->getOptionHasriskArray($userid, $edOptionNumber, 
                  $hasReturn, $parentId, $lang,$tLang);

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
                    $optionText = 
                       $oInferenceHelper->getHasRiskContinueExitArray($optionNumber,$lang, $tLang); 
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
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);	
                $optionText  = $oInferenceHelper->getOptionHasriskArray($userid, $optionNumber,
                  $appendB,  $parentId,$lang,$tLang);

                $optionText0  = 
                  $oInferenceHelper->getLinkReturnExitArray($optionNumber, $zero, $lang, $tLang);
                $optionText = array_merge($optionText,$optionText0);  

                $state = 806;
                $inLoop = 0;

                break;	

              ////////////////////////////////////////////////////////////////////
              /**  State  58                                                   **/
              /*   Option pick from requires  list                              */				   
              case 58:   

                $hasReturn = 0;       
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText); 
                $edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 
                $parentId  = $edOptionNumber;                  

                $optionText0  =  $oInferenceHelper->getSolOptExtDataArray($userid,$orgid,
                   $edOptionNumber,$optionNumber, $inText,$hasReturn, $lang, $tLang, $portalType);


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
                $optionNumber = $oFunctionHelper->getParentOptionNumber($inText);	
                $edOptionNumber = $oFunctionHelper->getOptionNumber($inText); 
                $parentId  = $edOptionNumber; 

                $optionText0  =  $oInferenceHelper->getSolOptExtDataArray($userid,$orgid,
                   $edOptionNumber,$optionNumber, $inText,$hasReturn, $lang, $tLang, $portalType);
  

                $hasAddress = $oFunctionHelper->hasAddress($optionText0,$addressKey);
                
                $optionText1 = 
                 $oInferenceHelper->getOptionHasoptionArray($userid, $edOptionNumber, 
                  $hasReturn, $parentId, $lang, $tLang);


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
                       $oInferenceHelper->getLinkReturnExitArray($optionNumber, $one, $lang, $tLang);
                    $optionText = array_merge($optionText,$optionText0); 
                    //$optionText0  = 
                    //   $oInferenceHelper->getLinkReturnExitArray($optionNumber, $zero, $lang, $tLang);

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
                $optionText  = $oInferenceHelper->getOptionHasoptionArray($userid, 
                  $optionNumber,$appendB, $soParentId, $lang, $tLang);

                //$optionText0  = 
                //  $oInferenceHelper->getLinkExitArray($optionNumber,$zero, $lang,$tLang);
                // FSd-000238 fix
                $optionText0  = 
                  $oInferenceHelper->getLinkReturnExitArray($optionNumber,$zero, $lang,$tLang);
                //  end of fix
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
                $optionText  = $oInferenceHelper->getOptionHasriskArray($userid, 
                   $optionNumber,$hasReturn, $parentId, $lang,$tLang);	

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
                $optionText0  =  $oInferenceHelper->getOptionRequiresArray($userid, 
                    $optionNumber, $hasReturn, $zero, $lang, $tLang);		

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
                  $oInferenceHelper->getOptionRequiresArray($userid, $optionNumber,
                    $hasReturn, $zero, $lang, $tLang);				  
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
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber, $zero,
                    $parentId, $lang, $tLang);				  
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
                   $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber, $zero, 
                      $zero, $lang, $tLang);				  
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
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber, $zero,
                     $zero, $lang, $tLang);				  
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
                  $oInferenceHelper->getOptionHasoptionArray($userid, $optionNumber, $zero,
                    $zero, $lang, $tLang);				  
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
                  $oInferenceHelper->getOptionRequiresArray($userid, $optionNumber,
                    $hasReturn, $zero, $lang, $tLang);				  
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
                  $oInferenceHelper->makeFactRating($personalityId,$userid,$TSStrategy);

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
                  $oInferenceHelper->updatePersonalityValue($personalityId,$slidebar,$orgid,$userid); 
                }

                // find solution relations
                $hasSolutionRelation = $oInferenceHelper->makeSolutionRelation($personalityId,
                  $userid, $orgid, $hasBar, $slidebar, $isLex, $state, $lang ); 

                // if solution relation are found, make relation extended data
                if ($hasSolutionRelation == 1) {
                   $oInferenceHelper->makeRelationExtendedData($userid, $orgid, $portalType);                   
                }
 

                // makeSolutionRelation
                $state = 26;    
                                      
                break; 



//////////////    START OF SEGMENT TESING  ////////////////////////////////////////77

              ////////////////////////////////////////////////////////////////////
              /**  State  324                                                  **
              *   Segmenmt processing                                     * 
              *   If no solution if found, then:
              *     - get keywords fron inquiry
                    - Find solution for each keyword
                    - If solutions are found, then prompt search using keywprd 
              */       
              case 324:
 
                $delete = 0;
                $oSolutionFact->deleteByUser($userid);
  
                // Get rightmost character
                if ($segmentLen > 0) {
                   $sTerm = $oFunctionHelper->getRightmostTerm($sSegment);
                   $sSegment = $oFunctionHelper->choppSegment($sSegment);
                   $segmentLen =  strlen($sSegment);   

                   $oInferenceHelper->makeSingleSolutionFact($sTerm, $userid, $sTerm); 
                   $oInferenceHelper->makeEquivalentSingleSolutionFact($userid, $orgid, $delete);
                   $sfNetRating = 
                      $oInferenceHelper->makeFactRating($personalityId,$userid,$TSStrategy);
                   $kwState  = 324;
                   $state = 22; 
 
                } else {
                   $state = 326;
                };           
             
                break;


              ////////////////////////////////////////////////////////////////////
              /**  State  326                                                  **
              *   Multi keyword processing                                     * 
              *   If no solution if found, then:
              *     - get keywords fron segment
                    - Find solution for each keyword 
              */       
              case 326:
 
                // Check if solutions were found
                $hasSolution  = $oInferenceHelper->hasSolution($userid);
                $inLoop = 1; 
                $wState = 326;
                

                if ($hasSolution > 0) {      // solution found
                   if ($segmentLen > 0) {
                      $state = 324;
                   } else {

                      $state = 34;                     
                   }


                } else {

                  if ($segmentLen > 0) {
                     $state = 324;
                  } else {
                     $state = 999;            // solution not found
                     $oSolutionFact->deleteByUser($userid);   // delete fact records from this user   

                    if ($advancedSearchOn > 0) {
                       $inLoop = 0;
                    } else {
                       $inLoop = 1;                     
                    }

                    // $inLoop = 0;                     
                  }
                }

                if ($segmentLen < 1) {
                  if ($hasSolution > 0) {
                      $state = 34;  
                      $inLoop = 1;                    
                    } else {
                      $state = 999;            // solution not found
                      $oSolutionFact->deleteByUser($userid);   // delete fact records from this user 

                    if ($advancedSearchOn > 0) {
                       $inLoop = 0;
                    } else {
                       $inLoop = 1;                     
                    }

                       

                    }


                }


                break;


/////////////////END OF SEGMENT TESTING ////////////////////////////////////////77


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
              /**  State  998                                                  **/
              /*   Exit                                                        */        
              case 998:

                if ($advancedSearchOn == 0)  {
                    $inquiry = $termSet;
                    $sSegment = $termSet;
                    $advancedSearchOn = 1;
                    $state = 4;                	
                }

                break;    

              ////////////////////////////////////////////////////////////////////
              /**  State  999                                                  **/
              /*   Exit                                                        */        
              case 999:
 
                if ($advancedSearchOn == 0)  {
                    $inquiry = $termSet;
                    $sSegment = $termSet;
                    $advancedSearchOn = 1;
                    $state = 4; 
                }

                break;                      
         
              ////////////////////////////////////////////////////////////////////




            }		// END OF CASE	
       }   // END OF SWITCH

	  ///////////  FSM ENDS HERE  ///////////////	


    /////////////// START OF SEND RESPONSE /////////////
        $outState = $state;
        if ($state == 900) {     // logic to handle slidebar parameters
           $outState = 300;
        } else {
           $outState = $state;
        }  

//        if ($wState > 0) {
//           $outState  = $wState;
//        }


        switch ($state) { 


            ///////////////////////////////////////////////////////////////////   
            /**  State 802                                                  **/           
            /*   exit with solution relations                                */   
            /* I found these results. Please select one for more information */       
            case 802:

                $sCount = count($solutionText);

                if ($sCount > 1) {
                    $code = 11;
                } else {
                    $code = 10;
                }        

                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);           
                $message = $sGreeting.$message;

                $apiresponse = 
                   [
                      'response' => 
                         [
                          'type'       => 'radiobutton', 
                          'message'    => $message, 
                          'state'      => $state,
                          'language'   => $jsonLang,
                          'contentType'=> $contentType,                          
                          'answers'    => $solutionText
                         ]
                   ];           
            break;

            ///////////////////////////////////////////////////////////////////   
            /**  State 806                                                  **/           
            /*   exit with solution option: has risk                         */   
            /*   I found this additional information. Click on one for 
                   additional information, Back to continue, or Exit to start a new topic. */       
            case 806:  
          
                $code = 30;   
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                      

                $apiresponse = 
                   [
                     'response' => 
                       [
                        'type'       => 'radiobutton', 
                        'message'    => $message, 
                        'state'      => $state,
                        'language'   => $jsonLang, 
                        'contentType'=> $contentType,                        
                        'answers'    => $optionText
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
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang); 

                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'       => 'radiobutton', 
                        'message'    => $message, 
                        'state'      => $state,
                        'language'   => $jsonLang,
                        'contentType'=> $contentType, 
                        'answers'    => $optionText
                      ]
                  ];                 
                  break;  


            ///////////////////////////////////////////////////////////////////   
            /**  State 813                                                  **/           
            /*   exit with one solution.                                     */
            /*     Display extended data and linking term                    */
            case 813:  

                $message = "";
                $contentType = $oParsingHelper->getContentType($optionText);
                $message = $sGreeting.$message;
                $apiresponse = 
                   [
                      'response' => 
                         [
                           'type'       => 'radiobutton', 
                           'message'    => $message, 
                           'state'      => $state,
                           'language'   => $jsonLang, 
                           'contentType'=> $contentType,                           
                           'answers'    => $optionText
                         ]
                   ];                
                break;

            ///////////////////////////////////////////////////////////////////   
            /**  State 814                                                  **/           
            /*   exit with solution option: has option                       */
            /*   I found this additional information. Click on one for 
                  additional information, Back to continue, or Exit to start a new topic. */                           
            case 814:   
         
                $code = 30; 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang); 

                $apiresponse = 
                   [
                      'response' => 
                         [
                           'type'       => 'radiobutton', 
                           'message'    => $message, 
                           'state'      => $state,
                           'language'   => $jsonLang, 
                           'contentType'=> $contentType,                          
                           'answers'    => $optionText
                         ]
                   ];                
                break;    

            ///////////////////////////////////////////////////////////////////   
            /**  State 818                                                  **/           
            /*   exit after last continue / exit                            */
            /*   $message = Thank you! You may start another topic if you wish */           
            case 818:

                $code = 22;
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                
          
                $apiresponse = 
                   [
                     'response' => 
                        [
                          'type'       => 'radiobutton', 
                          'message'    => $message, 
                          'state'      => $state,
                          'language'   => $jsonLang, 
                          'contentType'=> $contentType,                       
                          'answers'    => []
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
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);                      

                $apiresponse = 
                   [
                     'response' => 
                       [
                         'type'       => 'radiobutton', 
                         'message'    => $message, 
                         'state'      => $state,
                         'language'   => $jsonLang,  
                         'contentType'=> $contentType,                       
                         'answers'    => $optionText
                       ]
                   ];                
                break;          
          
            ///////////////////////////////////////////////////////////////////   
            /**  State 826                                                  **/           
            /*   exit after last  exit                                      */    
            /*   Thank you! You may start another topic if you wish.        */        
            case 826:

                $code = 22; // 44
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);   
          
                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'       => 'radiobutton', 
                        'message'    => $message, 
                        'state'      => $state,
                        'language'   => $jsonLang, 
                        'contentType'=> $contentType,                       
                        'answers'    => []
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

                $code= 48; 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang); 
                $contentType = $oParsingHelper->getContentType($optionText);

                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'       => 'radiobutton', 
                        'message'    => $message, 
                        'state'      => $state,
                        'language'   => $jsonLang,  
                        'contentType'=> $contentType,                      
                        'answers'    => $optionText
                      ]
                  ];                 
                break;  

            ///////////////////////////////////////////////////////////////////   
            /**  State 832                                                 **/            
            /*   solution option extended data found. Data contains address */  
            /*   I found this additional information
                 Click Back to continue, or Exit to start a new topic.      */
            case 832: 

                $code= 48; 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);  
                $contentType = $oParsingHelper->getContentType($optionText);

                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'       => 'radiobutton', 
                        'message'    => $message, 
                        'state'      => $state,
                        'language'   => $jsonLang,   
                        'contentType'=> $contentType,                      
                        'answers'    => $optionText
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
                   $optionNumber, $inText, $zero, $lang, $tLang, $portalType);
  
                $code = 30;
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);    
                //$contentType = "edtext";
                $contentType = $oParsingHelper->getContentType($optionText);

                $apiresponse = 
                  [
                   'response' => 
                    [
                      'type'       => 'radiobutton', 
                      'message'    => $message, 
                      'state'      => $state,
                      'language'   => $jsonLang,     
                      'contentType'=> $contentType,                   
                      'answers'    => $optionText
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
                $contentType = $oParsingHelper->getContentType($optionText);                             
                $message = $sGreeting.$message;    
     
                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'       => 'radiobutton', 
                        'message'    => $message, 
                        'state'      => $state,
                        'language'   => $jsonLang,
                        'contentType'=> $contentType, 
                        'answers'    => $optionText
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
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);              
       
                $apiresponse = 
                  [
                    'response' => 
                      [
                        'type'       => 'radiobutton', 
                        'message'    => $message, 
                        'state'      => $state,
                        'language'   => $jsonLang,   
                        'contentType'=> $contentType,                      
                        'answers'    => $optionText
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
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);  
                $message = $sGreeting.$message;

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
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang); 
                $message = $sGreeting.$message;        
                  
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
            /**  State 997                                               **/            
            /*   long inpput detected                                     */  
            /*   message: I would like to help you. Can you please be     */
            /*     more specific in describing your issue or request.     */
            /*                                                            */        
            case 997:
                             
                $code = 50;
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
                $inLoop = 0;              // exit loop       
                break;

            ////////////////////////////////////////////////////////////////  
            /**  State 998                                               **/            
            /*   exit with I do not understand                            */  
            /*   code = 36  I'm sorry. I do not understand                */
            /*   code = 38  I'm sorry, I'm still not understanding you.   */        
            case 998:
        
                if($inState == 998) {
                   if ($sGreeting == "") {
                      $code = 38;
                   } else {
                      $code = 56;
                   }

                } else {                      
                   if ($sGreeting == "") {
                      $code = 36;
                   } else {
                      $code = 56;
                   }
                } 

                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);
                $message = $sGreeting.$message; 

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

                $inLoop = 0;              // exit loop       
                break;

            /////////////////////////////////////////////////////////////// 
            /**  State 999                                              **/           
            /*  exit with I could not find a solution                    */   
            /*   code = 34  Thak you for shring that. I do not detect ..    */
            /*   code = 39  I'm sorry, I still do not detect any issue ..   */                        
            case 999: 

                if($inState == 999) {
                    //$code = 39; 
                   if ($sGreeting == "") {
                      $code = 38;                    
                   } else {
                      $code = 56;                         
                   } 

                } else {  
                   if ($sGreeting == "") {
                      $code = 34;                    
                   } else {
                      $code = 56;                         
                   }                   

                } 
 
                $message = $oMessage->retrieveTextByCodeOrgLang($code,$textOrgId,$messageLang);
                $message = $sGreeting.$message;
               
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



                //$oFunctionHelper->sendErrormail($toemail, $emailsubject, $message);
               /*
               try{
                 $tmpMail = new \App\Mail\SendMail;
            
                  \Mail::to($to)
                   ->send($tmpMail->sendMessage($toemail,$emailsubject, $message));

                 return ['result'=>0, 'msg'=>null];

               } catch(\ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }                 
               */
                break;  


        } // end of switch



      // insert solution relation record with state and language info
      // state is used in the conversation flow
      $nZero  = 0;
      $sBlank = "";

      $oSolutionRelation->insertRelation($nZero,$sBlank, $nZero, $nZero,
           $nZero,$nZero,$nZero,$sBlank, $sBlank, $nZero, $nZero,$nZero,
           $userid, $outState, $lang);

      return $apiresponse; 

       ////////////// END OF SEND RESPONSE  ////////////
 
    }	  // end of show() MAIN LOGIC

}