<?php   
/* Enterprise word api
 * Get word

 * Company  :  Kamazooie Development Corporation
 * Developer:  Gabriel Carrillo
 * Version  :  3.05
 * Updated  :  07 August 2024
 */

namespace App\Http\Controllers\Api\EnterpriseWord;

use Illuminate\Http\Request;
use App\Controllers;
use App\Helpers\FunctionHelper;
use App\Models\Relation;
use App\Models\RelationType;
use App\Models\RelationTermLink;
use App\Models\Term;

use Illuminate\Support\Facades\Config;

class EnterpriseWordController extends \App\Http\Controllers\Controller{


    /*------------------------------------------------------------------
     * getWord
       get enterprise word
     
      Input
        int      $orgid
        string   $translation
     
      Output
        int      $orgid
        string   $aWord   (json)
      
     *-----------------------------------------------------------------*/

    public function getWord(Request $request) {

       $orgid         = $request->input('orgid' );
       $translation   = $request->input('translation'  );
       $aWord         = array();  
       if (is_null($orgid)) {
          $orgid = 0;
       }   

      $replace1  = array(',', ';','?','!');    
      $replace2  = array('        ','       ','      ','     ','    ','   ','  ', ' ');

      // Validation

      if(is_null($translation) )
    	  { return \Response::json([ 'message' => 'Invalidad parameters ' ], 400); }
 
      // get tranlatuon in array format
      $type = gettype($translation);  
      if ($type =="string") {

          // replace multiple periods by single period
          $translation = str_replace( '......,', '.', $translation);
          $translation = str_replace( '.....,', '.', $translation);
          $translation = str_replace( '....', '.', $translation);
          $translation = str_replace( '...', '.', $translation);
          $translation = str_replace( '..', '.', $translation);

          $translation = str_replace( $replace1, ' ', $translation);     
          $translation = str_replace( $replace2, ' ', $translation);   

          // remove last chatrcater if it is period
          $pos =strlen($translation)-1;

          if ( $pos > 0 and $translation[$pos] == ".") {
              $translation = rtrim($translation, ".");
          }

          $aStr =  $this->getArray($translation);
      }
      if ($type =="array") {
          $aStr =  $translation;
      }

      // process elements in the translation array
      $len = count($aStr);

      for ($i=0;$i<$len;$i++)  {
          $str = $aStr[$i];
          $aW  = $this->getArray1($str);
          $aWord[] = $this->getEnterpriseWord($orgid,$aW,$str);
      }
    
	    return \Response::json([ 'orgid'=>$orgid, 'words'=>$aWord ]);

      //----  end of function getEnterpriseWord    -----------------------------//-

	  }

   /*--------------------------------------------------------------------
   *  getArray   Get and array from a string. 
   *   EXAMPLES      "I,lost,job"     
   *                 "I,have,job,and,I,lost,job","economy,caused by,covid-19"
   *  INPUT
   *    $translation  string  translation string
   *  OUTPUT
   *    $aStr         array   translation strings are array elements
   *-------------------------------------------------------------------*/
   public function getArray($translation) {
      $a = str_split($translation);
      $count = count($a);
      $limit = $count - 1;
      $j = -1;
      $i = 0;
      $state = 1;
      $quote = '"';
      $comma = ",";
      $str   = "";
      $loop  = 1;
      $aStr  = array();

      // start of finite state machine
      while ($loop == 1) { 

         switch ($state) {

           case 1:
             $str  = "";               // empty the string
             if ($a[$i] == $quote ) {
                $state = 2;            // next state            
             } 
           break;

           case 2:
             if ($a[$i] == $quote ) {
                $str = $str.$a[$i]; 
                $j++;                  // increase index j
                $str[$j] = $str;
                $state = 4;            // next state            
             } else {
                $str = $str.$a[$i]; 
                $state = 3;            // next state          
             }
           break;

           case 3:
             if ($a[$i] == $quote ) {
                $j++;                  // increase index j
                $aStr[$j] = $str;       // save array element
                $state = 4;            // next state            
             } else {
                $str = $str.$a[$i]; 
                $state = 3;            // next state          
             }
           break;

           case 4:
             if ($a[$i] == $comma ) {
                $str  = "";            // empty the string
                $state = 1;            // next state    
             } 
           break;
         }


         if ($i < $limit ) {           // move to next array index
            $i++;
         } else {
            $loop = 0;                // exit State machine
         }

      }
      // end of finite state machine
      return $aStr;
   }

   /*--------------------------------------------------------------------
   *  getArray   Get and array from a string. 
   *    
   *  INPUT
   *    $translation  string  translation string
   *  OUTPUT
   *    $aStr         array   translation strings are array elements
   *-------------------------------------------------------------------*/
   public function getArray1($translation) {

      $pos =strlen($translation)-1;

      if ( $pos > 0 and $translation[$pos] == ".") {
          $translation = rtrim($translation, ".");
      }
 
      $a = str_split($translation);
      $count = count($a);
      $limit = $count - 1;
      $quote = '"';
      $period = '.';
      $special1 = '[';
      $special2 = ']';
      $replace1  = array(',', ';','?','!');   
      $replace2  = array('        ','       ','      ','     ','    ','   ','  ', ' ');  
      $aux = array();
      for($i=0;$i<=$limit;$i++) {      
        
         $aS    = $a[$i];
         $aS    = str_replace( $replace1, " ", $aS);    
         $aS    = str_replace( $replace2, " ", $aS); 
        
         if ($aS == $quote or $aS == $special1 or $aS == $special2) {
             $count--;
         } else {
             $aux[] = $aS;
         }
      }

      $aux = implode("",$aux);
      $aux = explode(" ",$aux);

      return $aux;
   }

   /*--------------------------------------------------------------------
   *  getWord   Get valid words from traslation using table term. 
   *  INPUT
   *    $a       string  utterance
   *  OUTPUT
   *    $aStr    array   strings are array elements with enterprise words
   *-------------------------------------------------------------------*/
   public function getEnterpriseWord($orgid,$a,$utterance) {

      $oTerm    = new Term();
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
      $aux = $this->getOffset($aux,$utterance); // add offset data to array
      return $aux;

   }


   /*--------------------------------------------------------------------
   *  getOffset          
   *-------------------------------------------------------------------*/

   public function getOffset($aux,$utterance) {

      $auxLeng = count($aux);
      $aWord = array();
      $start = 0;             // search from this position in the utterance

      for  ($i=0;$i < $auxLeng; $i++) {
          $needle = $aux[$i];
          $needleLeng = strlen($needle);

          $pos = strpos($utterance,$needle,$start); 

          $aWord[] = ['offset'=>$pos, 'term'=>$needle  ];

          $start = $start + $needleLeng;

      }

      return $aWord;
   }

    /*--- end of function getOffset---- */


}
