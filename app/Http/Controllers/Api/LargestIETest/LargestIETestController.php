<?php   
/* largestiE api
 * From an input text, subtrings that match term are extracted. The goal is to
 * extract the longest expression matching a term.
 * The search is left-to-right and then right-to-left, and the sequence selected 
 * is where the longest expression is found.
 * Developer:  Gabriel Carrillo
 * Version  :  1.5
 */

namespace App\Http\Controllers\Api\LargestIETest;

use Illuminate\Http\Request;
use App\LargestIE;
use App\Controllers;
use App\Helpers\FunctionHelper;
use App\Organization;
use App\Term;
use App\Relation;
use App\Models\RelationTypeFilter;

use Illuminate\Support\Facades\Config;

class largestIETestController extends \App\Http\Controllers\Controller{


    /*------------------------------------------------------------------
     * getlargestIE
     * Return an array with longest expressions and question translation
     *-----------------------------------------------------------------*/

	public function getLargestIE($orgid, $termName, $api_key,$user_id ) {


    if(is_null($termName)){ return \Response::json([ 'message' => 'Term  [termName] is empty' ], 400); }
    if(is_null($orgid)){ return \Response::json([ 'message' => 'organization ID [orgid] is empty' ], 400); }
    $orgId   = trim($orgid);

    //$user_id = 421;
    //$api_key = 2LEX!!992ca7ce9e1c172a7d94ec665054b838

    $api_key_manager = new \App\ApiKeyManager\ApiKeyManagerClass;
    $key_result= $api_key_manager->authenticate($user_id, $api_key);
     
    // check for apikey
    $apikeyresult =  $key_result['result'];
	$kamaLog = \App\Logs\KamaLogClass::addLog($api_key, 'user', trim($termName), trim($termName));
   
    //----------------------------------------------- 
    //--- Left to Right search  ---------------------
    //----------------------------------------------- 
    $text0   = trim($termName);
    $text1   = trim($termName);

    // loop parameters
    $loop = 1;
    $loopCount = 0;
    $lrLongestE = "";


    while ($loop == 1) {

           // Before loop processing
           $text1Len   = strlen($text1);
           $largestIE  = strtolower($text1);
           $termId = 0;
           $text2 = " ";
           $termHint   = strchr($text1,$text2,true); 
           $termHintLen   = strlen($termHint);

           // get end of sentence termHint
           if ($termHintLen == 0 and $text1Len > 0) {
               $termHint = $text1;
               $termHintLen   = strlen($termHint);
           }
           $loopCount++;


           // defauklt values
           $leftTermId = 0;
           $rightTermId  = 0;
           $rightTermName = "";

           $aResponse = $this->getLeftRightText($orgid, $text1); 
           $rightTermId  = $aResponse['rightTermId'];
           $rightTermName  = $aResponse['rightTermName'];
           $largestIE  = $aResponse['largestIE'];
           $leftTermId = $aResponse['leftTermId'];

           // after loop processing  
           if ($leftTermId == 0) {
               $largestIE = "";
           }

           // prepare json response
           $termIdSet[]         = $leftTermId;
           $largestIESet[]      = $largestIE;
           $transTermIdSet[]    = $rightTermId;
           $transTermNameSet[]  = $rightTermName;
           $text1Len0  = strlen($largestIE);
           $lIELen = $text1Len0;

           // find longestE
           $longestELen = strlen($lrLongestE);
           if ($text1Len0 > $longestELen) {
               $lrLongestE = $largestIE;
           }

           // skip terms not found          
           if ($text1Len0 == 0) {
           	  if ($text1Len > $termHintLen){
                $text1Len = $termHintLen;        	  	
           	  } else {
                $text1Len = 0; 
           	  }
      	
           } else {
              $text1Len = $text1Len0;         	
           }

           // If not end of text, continue processing
           if ($text1Len > 0) {
               $text1     = trim(substr($text1,$text1Len));
               $text1Len  = strlen($text1);
           }

           if ($text1Len == 0) {
               $loop = 0;        
           }
     }

     // save left to right search variables
     $lrTermIdSet         = $termIdSet;
     $lrLargestIESet      = $largestIESet; 
     $lrTransTermIdSet    = $transTermIdSet;
     $lrTransTermNameSet  = $transTermNameSet;    

     // delete arrays with results
     unset($termIdSet);
     unset($largestIESet); 
     unset($transTermIdSet);
     unset($transTermNameSet);    

    //----------------------------------------------- 
    //--- Right to Left search  ---------------------
    //----------------------------------------------- 
    $text0   = trim($termName);
    $text1   = trim($termName);

    // loop parametrs
    $loop = 1;
    $loopCount = 0;
    $rlLongestE = "";

        //--- Main loop  -------------------------
    while ($loop == 1) {

           // Before loop processing
           $text1Len   = strlen($text1);
           $textLen    = $text1Len;
           $largestIE  = strtolower($text1);
           $termId = 0;
           $text2 = " ";
           $termHint   = trim(strchr($text1,$text2)); 
           $termHintLen   = strlen($termHint);

           // get end of sentence termHint
           if ($termHintLen == 0 and $text1Len > 0) {
               $termHint = $text1;
               $termHintLen   = strlen($termHint);
           }
           $loopCount++;

           // defauklt values
           $leftTermId = 0;
           $rightTermId  = 0;
           $rightTermName = "";

           $aResponse = $this->getLeftRightText($orgid, $termHint); 
           $rightTermId  = $aResponse['rightTermId'];
           $rightTermName  = $aResponse['rightTermName'];
           $largestIE  = $aResponse['largestIE'];
           $leftTermId = $aResponse['leftTermId'];


           // after loop processing  
           if ($leftTermId == 0) {
               $largestIE = "";
           }

           // prepare json response
           $termIdSet[]         = $leftTermId;
           $largestIESet[]      = $largestIE;
           $transTermIdSet[]    = $rightTermId;
           $transTermNameSet[]  = $rightTermName;
           $text1Len0  = strlen($largestIE);
           $lIELen = $text1Len0;


           // find longestE
           $longestELen = strlen($rlLongestE);
           if ($text1Len0 > $longestELen) {
               $rlLongestE = $largestIE;
           }

           // skip terms not found          
           if ($text1Len0 == 0) {
              if ($text1Len > $termHintLen){
                $text1Len = $termHintLen;             
              } else {
                $text1Len = 0; 
              }
        
           } else {
              $text1Len = $text1Len0;           
           }


           // If not end of text, continue processing
           if ($text1Len > 0) {
               $diff      = $textLen - $text1Len;
               $text1     = trim(substr($text1,0,$diff));
               $text1Len  = strlen($text1);
           }

           if ($text1Len == 0) {
               $loop = 0;        
           }

     }


     // Select results for response. Find the longest expression
     $lrLongestELen  = strlen($lrLongestE);
     $rlLongestELen  = strlen($rlLongestE);

     if ($rlLongestELen > $lrLongestELen) {
        // right to left search
        $termIdSet        = array_reverse($termIdSet);
        $largestIESet     = array_reverse($largestIESet); 
        $transTermIdSet   = array_reverse($transTermIdSet);
        $transTermNameSet = array_reverse($transTermNameSet); 
     } else {
        // left to right search
        $termIdSet        = $lrTermIdSet;
        $largestIESet     = $lrLargestIESet; 
        $transTermIdSet   = $lrTransTermIdSet;
        $transTermNameSet = $lrTransTermNameSet; 
     }

	// resturn reponse

	//$retVal = [ 'input'=>$text0, 'termIdSet'=>$termIdSet,
	//	   'termNameSet' => $largestIESet, 'translationTermIdSet'=>$transTermIdSet,
	//	   'translationTermNameSet'=>$transTermNameSet  ];

	//$kamaLog = \App\Logs\KamaLogClass::addLog($api_key, 'System', json_encode($retVal), print_r($retVal,1));

	return \Response::json([ 'input'=>$text0, 'termIdSet'=>$termIdSet,
		   'termNameSet' => $largestIESet, 'translationTermIdSet'=>$transTermIdSet,
		   'translationTermNameSet'=>$transTermNameSet, 'apiKeyResponse'=>$key_result  ]);

    //----  end of function getLargestIE------------------------------------------

	}

    /*--------------------------------------------------------------------
	 *  find largest Text matching a term 
	 *  search from left to right
	 *-------------------------------------------------------------------*/
	public function getLeftRightText($orgid,$termName){


		$orgID   = trim($orgid);
		$text1   = trim($termName);

        //-----------------------------------
        // prepare search parameters
        $text1Len   = strlen($text1);
        $largestIE  = strtolower($text1);
        $termId = 0;
        $text2 = " ";
        $termHint   = strchr($text1,$text2,true); 
        $thLen = strlen($termHint);
        $match = 0;
        $aTerm = array();
        $aId   = array();
        $aCount = 0;

        $leftTermId = 0;
        $rightTermId= 0;  
        $rightTermName= ""; 
//echo "  BBB text1=$text1; termHint1=$termHint; text1Len=$text1Len;  ";
        // Check for one owrd expression
        if ($text1Len > 0) {
           if ($termHint =="" or empty($termHint)) {
              $termHint = $largestIE;
           }

        }
//echo "  termHint2=$termHint;   ";
		//-----------------------------------
		$tmp = Term::where('termName', 'LIKE', $termHint."%")->get();


        // make array of candidate terms
        foreach($tmp as $rs) {
            $aTerm[] = strtolower($rs->termName);
            $aId[]   = strtolower($rs->termId);
            $sterm = strtolower($rs->termName);
            $aCount++;
        }
   
        // 1. find largest initial expression
        $matchCount = 0;
        $i = 0;
        $loop = 1;

        while ($loop == 1) {
            $match =0;
            for ($j=0;$j<$aCount; $j++) {

                if ($largestIE == $aTerm[$j]) {
                    $match = 1;
                    $termId = $aId[$j];
                    $matchCount = $termId;
                }
            }
            
            if ($match == 0) {
            // no match; chop string from right to left
                $IElen      = strlen($largestIE);
                $termSearch = strrpos($largestIE,$text2); 
                $largestIE  = substr($largestIE,0,$IElen-($IElen-$termSearch) );
            }

            // exit conditions
            if ($match == 1 or $largestIE == "") {
               $loop = 0;
            }


        }						

        if ($aCount == 0) {
           $largestIE = "";
        }


        // 2. Get relation type filter
        $relationTypeId = 0;
        $step  = 1;   // default step for question processing
        $tmp = RelationTypeFilter::where('step', '=', $step)->get();

        foreach($tmp as $rs0) {					  
           $relationTypeId = $rs0->relationTypeId;
        }

        //  3. Get termid
        $tmp = Term::where('termName', '=', $largestIE)->get();
        $leftTermId = 0;
        foreach($tmp as $rs0) {                   
            $leftTermId = $rs0->termId;
        }

        // 4. Get right Term from relation
        $rightTermId = 0;
        $rightTermName = "";

		$tmp = Relation::where('leftTermId', '=', $leftTermId)
                         ->where('relationTypeId', '=', $relationTypeId)
		                 ->get();

        foreach($tmp  as $rs) {
            $rightTermId    = $rs->rightTermId;  
        } 

        $tmp = Term::where('termId', '=', $rightTermId)->get();
        foreach($tmp as $rs0) {                   
            $rightTermName  = $rs0->termName; 
        }

		//-----------------------------------
        $aResponse['text1']      = $text1;
        $aResponse['leftTermId'] = $leftTermId;
        $aResponse['largestIE']  = $largestIE;   
        $aResponse['rightTermId']  = $rightTermId;  
        $aResponse['rightTermName']  = $rightTermName;   

		return $aResponse;
	}
    //-- end of function getLeftRightText  --------------------------------

    /*--------------------------------------------------------------------
	 *  find largest Text matching a term 
	 *  search from right to left
	 *-------------------------------------------------------------------*/
	public function getRightLeftText($orgid,$termName){

    $orgID   = trim($orgid);
    $text1   = trim($termName);

        //-----------------------------------
        // prepare search parameters
        $text1Len   = strlen($text1);
        $largestIE  = strtolower($text1);
        $termId = 0;
        $text2 = " ";
        $termHint   = strchr($text1,$text2,true); 
        $thLen = strlen($termHint);
        $match = 0;
        $aTerm = array();
        $aId   = array();
        $aCount = 0;

        $leftTermId = 0;
        $rightTermId= 0;  
        $rightTermName= ""; 

        // Check for one owrd expression
        if ($text1Len > 0) {
           if ($termHint =="" or empty($termHint)) {
              $termHint = $largestIE;
           }

        }

    //-----------------------------------
    $tmp = Term::where('termName', 'LIKE', $termHint."%")->get();


        // make array of candidate terms
        foreach($tmp as $rs) {
            $aTerm[] = strtolower($rs->termName);
            $aId[]   = strtolower($rs->termId);
            $sterm = strtolower($rs->termName);
            $aCount++;
        }
   
        // 1. find largest initial expression
        $matchCount = 0;
        $i = 0;
        $loop = 1;

        while ($loop == 1) {
            $match =0;
            for ($j=0;$j<$aCount; $j++) {

                if ($largestIE == $aTerm[$j]) {
                    $match = 1;
                    $termId = $aId[$j];
                    $matchCount = $termId;
                }
            }

            if ($match == 0) {
            // no match; chop string from right to left
              //  $IElen      = strlen($largestIE);
              //  $termSearch = strrpos($largestIE,$text2); 
              //  $largestIE  = substr($largestIE,0,$IElen-($IElen-$termSearch) );

            // no match: chop string from right to left
        
                $IElen      = strlen($largestIE);
                $termSearch = strrpos($largestIE,$text2); 
                $largestIE  = trim(substr($largestIE,$termSearch ) );
                $loop = 0;
            }


            // exit conditions
            if ($match == 1 or $largestIE == "") {
               $loop = 0;
            }


        }           

        if ($aCount == 0) {
           $largestIE = "";
        }


        // 2. Get relation type filter
        $relationTypeId = 0;
        $step  = 1;   // default step for question processing
        $tmp = RelationTypeFilter::where('step', '=', $step)->get();

        foreach($tmp as $rs0) {           
           $relationTypeId = $rs0->relationTypeId;
        }

        //  3. Get termid
        $tmp = Term::where('termName', '=', $largestIE)->get();
        $leftTermId = 0;
        foreach($tmp as $rs0) {                   
            $leftTermId = $rs0->termId;
        }

        // 4. Get right Term from relation
        $rightTermId = 0;
        $rightTermName = "";

        $tmp = Relation::where('leftTermId', '=', $leftTermId)
                         ->where('relationTypeId', '=', $relationTypeId)
                     ->get();

        foreach($tmp  as $rs) {
            $rightTermId    = $rs->rightTermId;  
        } 

        $tmp = Term::where('termId', '=', $rightTermId)->get();
        foreach($tmp as $rs0) {                   
            $rightTermName  = $rs0->termName; 
        }

    //-----------------------------------
        $aResponse['text1']      = $text1;
        $aResponse['leftTermId'] = $leftTermId;
        $aResponse['largestIE']  = $largestIE;   
        $aResponse['rightTermId']  = $rightTermId;  
        $aResponse['rightTermName']  = $rightTermName;  


    return $aResponse;

	}
  //--  end of function getRightLeftText  ------------------------

}
