<?php   
/* largestiE api
 * From an input text, subtrings that match term are extracted. The goal is to
 * extract the longest expression matching a term.
 * The search is left-to-right and then right-to-left, and the sequence selected 
 * is where the longest expression is found.
 * Developer:  Gabriel Carrillo
 * Version  :  3.05
 * Updated  :  07 August 2024
 */

namespace App\Http\Controllers\Api\LargestIE;

use Illuminate\Http\Request;
use App\LargestIE;
use App\Controllers;
use App\Helpers\FunctionHelper;
use App\Organization;
use App\Models\Term;
use App\Models\Relation;
use App\Models\RelationTypeFilter;

use Illuminate\Support\Facades\Config;

class largestIEController extends \App\Http\Controllers\Controller{


    /*------------------------------------------------------------------
     * getlargestIE
     * Return an array with longest expressions and question translation
     *-----------------------------------------------------------------*/

   public function getLargestIE(Request $request) {

    $orgid = "";
    $termName = "";
    $apikey = "";
    $userid = "";

    // read input parameters, POST method
    if($request->has('orgid')){ 
        $orgid      = trim($request->input('orgid' ));      
    }

    if($request->has('inputText')){ 
        $termName   = $request->input('inputText' );      
    }

    if ($request->has('apikey')) {
        $apikey = trim($request->input('apikey'));
    }

    if($request->has('userid')){ 
        $userid     = trim($request->input('userid' ));      
    }

    // send error messages
    if($orgid==null){ return \Response::json([ 'message' => 'Organization ID not defined' ], 400); }
    if($termName == null){ return \Response::json([ 'message' => 'Term  [termName] is empty' ], 400); }
    if(is_null($apikey)){ return \Response::json([ 'message' => 'apikey [apikey]is empty' ], 400); }
    if(is_null($userid)){ return \Response::json([ 'message' => 'User ID [userid] is empty' ], 400); }    

    $orgId   = trim($orgid);

    $api_key_manager = new \App\ApiKeyManager\ApiKeyManagerClass;
    $key_result= $api_key_manager->authenticate($userid, $apikey);  
    $apikeyresult =  $key_result['result'];

    // check if input is regular utterance
    $inputText = $termName;
    $leftChar  = substr($termName,0,1);    

    if ($leftChar == "*" ) {             // skip input processing
        $termIdSet        = array();
        $termNameSet      = array(); 
        $transTermIdSet   = array();
        $transTermNameSet = array();         
    } else {                             // process input                  

        // Preprocess input string
        $termName  = $this->preprocess($termName);

        // Prepare text for precessing
        $termName = $this->prepareText($orgId, $termName);

        // make array from termName
        $aStr =  $this->getArray($termName);

        // get array with the longest terms
        $rs = $this->getLongestTerm($orgId,$aStr);

        // extract termNameSet and TermIdSet
        $termNameSet = $rs['termNameSet']; 
        $termIdSet   = $rs['termIdSet'];

        // get question translation
        $rs = $this->getQuestionTranslation($orgId,$termIdSet);

        // extract translation termId set and translation termName set
        $transTermNameSet  = $rs['transTermNameSet'] ; 
        $transTermIdSet    = $rs['transTermIdSet'] ;
    }  

	  // resturn reponse
	  return \Response::json([ 'input'=>$inputText, 'termIdSet'=>$termIdSet,
		   'termNameSet' => $termNameSet, 'translationTermIdSet'=>$transTermIdSet,
		   'translationTermNameSet'=>$transTermNameSet, 'apiKeyResponse'=>$key_result ]);

	}


   /*--------------------------------------------------------------------
   /*  preprocess input string                                                       */

   public function preprocess($str) {

      $replace1  = array(',', ';','?','!');    
      $replace2  = array('       ','      ','     ','    ','   ','  ', ' ');
      $str       = str_replace( '.....', '.', $str);
      $str       = str_replace( '....', '.', $str);
      $str       = str_replace( '...', '.', $str);
      $str       = str_replace( '..', '.', $str);
      $str       = str_replace( $replace1, ' ', $str);
      $str       = str_replace( $replace2, ' ', $str);

      // remove rightmost character if it is period
      $pos =strlen($str)-1;
      if ( $pos > 0 and $str[$pos] == ".") {
          $str = rtrim($str, ".");
      }
      $str = strtolower($str);

      return $str;
   }


   /*--------------------------------------------------------------------
   *  getArray   Get an array from a string. 
   *    
   *  INPUT
   *    $text           string  utterance        e.g.  "I am hungry"
   *  OUTPUT
   *    $aux            array   array elements   e.g.  ([0] => I,[1] => am,[2] => hungry)
   *-------------------------------------------------------------------*/
   public function getArray($text) {
      $a = str_split($text);
      $count = count($a);
      $limit = $count - 1;
      $quote = '"';

      $aux = array();
      for($i=0;$i<=$limit;$i++) {
         if ($a[$i] == $quote) {
             $count--;
         } else {
             $aux[] = $a[$i];
         }
      }

      $aux = implode("",$aux);
      $aux = explode(" ",$aux);

      return $aux;
   }

   /*--------------------------------------------------------------------
   *  getLongestTerm        Get longest terms 
   *  INPUT
   *    $a              array  utterance
   *  OUTPUT
   *    $termNameSet    array   array of longest term name
   *    $termIdSet      array   array of longest term id
   *-------------------------------------------------------------------*/
   public function getLongestTerm($orgId,$a) {

      $oTerm  = new Term();
      $aux = array();
      $len = count($a);
      $termNameSet = array();
      $termIdSet   = array();
      $aTerm = array();

      $limit = $len - 1;
      $wcount = 0;
      $loop  = 1;
      $rtgId = 8;  // can access protected data from

      // beginning of loop
      while ($loop == 1) {

        $wfound   = 0;
        $woffset  = -1;
        $j        = -1;
        $nterm    = "";
        
        /// beginning of foreach        
        for ($i=$limit; $i>=0; $i--) {
           $wterm = $a[$i]; 

              if ($nterm == "") {
                 $nterm = $wterm;
                 $aTerm = array();
                 //$rs = $oTerm->getLikeRightName($nterm);
                 $rs = $oTerm->getLikeName($orgId,$nterm,$rtgId);
                 foreach ($rs as $rows){ 
                    $key     = $rows->termId;
                    $value   = $rows->termName;
                    $aTerm[$key] =  strtolower($value);  // convert to lower case
                 }

              } else {
                 $nterm = $wterm ." " .$nterm;               
              }

              // search term in array
              $wid = array_search($nterm, $aTerm);

              if ($wid > 0) {
                 $wfound = 1;
                 $woffset = $i;
                 $wcount++;

                if ($j > -1 and $wcount > 1) {
                   $aux[$j]       = $nterm; 
                   $termIdSet[$j] = $wid;                     
                } else {
                   $aux[]       = $nterm;   
                   $termIdSet[] = $wid;                
                }

                $j = count($aux) - 1;

              }
        }
        //// end of foreach

        if ($wfound == 1) {
           $limit = $woffset - 1;
        } else {
           $limit--;
        }

        if ($limit < 0) {
           $loop = 0;
        }

      }
      // end of loop

      $termNameSet = array_reverse($aux);
      $termIdSet   = array_reverse($termIdSet);
      $rs['termNameSet'] = $termNameSet; 
      $rs['termIdSet']   = $termIdSet;

      return $rs;

   }
   //--  end of function getLongestTerm  ------------------------

   /*--------------------------------------------------------------------
   *  getQuestionTranslation   Get question translation for terms 
   *    
   *  INPUT
   *    $a                array   array with term Id       
   *  OUTPUT
   *    $transTermIdSet    array
   *    $transTermNameSet  array
   *-------------------------------------------------------------------*/
   public function getQuestionTranslation($orgid, $a) {

   	  $oRelation  = new Relation();

      $count = count($a);
      $limit = $count - 1;
      $transTermIdSet = array();
      $transTermNameSet = array();


      // Get relation type filter
      $relationTypeId = 0;
      $step  = 1;   // default step for question processing
      $rtgId = 8;   // can access protected data from
      $tmp = RelationTypeFilter::where('step', '=', $step)->get();

      foreach($tmp as $rs0) {           
          $relationTypeId = $rs0->relationTypeId;
      }      

      $aux = array();
      for($i=0;$i<=$limit;$i++) {
          $leftTermId = $a[$i];

          // Get right Term from relation
          $rightTermId = 0;
          $rightTermName = "";
 
          $tmp = $oRelation->getOrgLeftRelationType($orgid,$leftTermId,$relationTypeId,$rtgId);

          foreach($tmp  as $rs) {
              $rightTermId    = $rs->rightTermId; 

              if ($rightTermId > 0) {
                 $tmp1 = Term::where('termId', '=', $rightTermId)->get();
                 foreach($tmp1 as $tmp2) {                   
                    $rightTermName  = $tmp2->termName; 
                 }  
                 $transTermIdSet[]   = $rightTermId;     
                 $transTermNameSet[] = $rightTermName;        	
              } else {
                 $transTermIdSet[]   = 0; 
                 $transTermNameSet[] = "";              	
              }

          } 

      }

      $rs['transTermNameSet'] = $transTermNameSet; 
      $rs['transTermIdSet']   = $transTermIdSet;

      return $rs;
   }
   //--  end of function getQuestionTranslation  ------------------------

 
   /*--------------------------------------------------------------------
   *  prepareText   Prepara the text for the mani processing 
   *    
   *  INPUT
   *    $orgid             array   organization id   
   *    $inText            string  text to be prepared 
   *  OUTPUT
   *    $outText           string  prepared text for processing
   *-------------------------------------------------------------------*/
   public function prepareText($orgid, $inText ) {

      $oTerm  = new Term();

      $a        = explode(" ",$inText);;
      $aux      = array();
      $len      = count($a);
      $limit    = $len - 1;
      $wcount   = 0;
      $loop     = 0;
      $rtgId    = 8;   // used in protected access
      if ($len > 0){
          $loop = 1;
      }
  
     // beginning of loop
      while ($loop == 1) {

        $wfound   = 0;
        $woffset  = -1;
        $j        = -1;
        $nterm    = "";
        
        /// beginning of foreach        
        for ($i=$limit; $i>=0; $i--) {

              $wterm = $a[$i]; 

              if ($nterm == "") {

                 $pos =strlen($wterm)-1;

                 if ( $pos > 0 and $wterm[$pos] == ".") {
                    $wterm = rtrim($wterm, ".");
                 }

                 $nterm = $wterm;
                 $aTerm = array();
                 $rs = $oTerm->getLikeName($orgid,$nterm,$rtgId);
                 foreach ($rs as $rows){ 
                     $key     = $rows->termId;
                     $value   = $rows->termName;
                     $aTerm[$key] =  strtolower($value);  // convert to lower case
                 }

              } else {
                    $nterm = $wterm ." " .$nterm;               
              }

              // search term in array

              $needle = strtolower($nterm);            // convert to lower case
              $wid = array_search($needle, $aTerm);    // search long term in array
  
              if ($wid > 0) {
                  $wfound = 1;
                  $woffset = $i;
                  $wcount++;

                  if ($j > -1 and $wcount > 1) {
                      $aux[$j]     = $nterm;      // save largerterm                
                  } else {
                      $aux[]       = $nterm;      // save new term               
                  }

                  $j = count($aux) - 1;

              }
        }
        //// end of foreach

        if ($wfound == 1) {
            $limit = $woffset - 1;
        } else {
            $limit--;
        }

        if ($limit < 0) {
            $loop = 0;
        }

      }
      // end of loop

      $aux = array_reverse($aux);
      $sText = implode(" ", $aux);
      return $sText;

   }
   //--  end of function prepareText  -----------------------------------------




}
