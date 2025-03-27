<?php   
/* RelationTerm api
 * Get realtion term

 * Company  : Kamazooie devlopment Corporation
 * Developer:  Gabriel Carrillo
 * Version  :  2.0
 * Updated  :  22 October 2020
 *             04 November 2020
 */

namespace App\Http\Controllers\Api\RelationTerm;

use Illuminate\Http\Request;
use App\Controllers;
use App\Helpers\FunctionHelper;
use App\Models\Relation;
use App\Models\RelationType;
use App\Models\RelationTermLink;
use App\Models\Term;

use Illuminate\Support\Facades\Config;

class RelationTermController extends \App\Http\Controllers\Controller{


    /*------------------------------------------------------------------
     * getTerm
       get term from tabel relation_term
     
      Input

        int      $orgid
        string   $translation
        string   $krtermLink
     
      Output
     
        int      $orgid
        string   $terms   (json)
      
     *-----------------------------------------------------------------*/

    //public function getTerm($orgid, $translation, $relationType) {
    public function getTerm(Request $request) {

       $orgid         = trim($request->input('orgid' ));
       $translation   = trim($request->input('translation'  ));
       $krtermLink    = trim($request->input('krtermLink'  ));      

      /**  Validation  ------------------------------------*/
      if(is_null($orgid)  )
    	  { return \Response::json([ 'message' => 'Invalidad parameters ' ], 400); }

      if(is_null($translation) )
    	  { return \Response::json([ 'message' => 'Invalidad parameters ' ], 400); }

      if(is_null($krtermLink) )
      	{ return \Response::json([ 'message' => 'Invalidad parameters ' ], 400); }



      /** --------------------------------------------------------------------**/  
      // get triples

      $type = gettype($translation);  
      if ($type =="string") {
          $aStr =  $this->getArray($translation);
      }
      if ($type =="array") {
          $aStr =  $translation;
      }

      // get triples
      $aTriple = $this->getTriple($aStr);

      // get terms
      $aTerm = $this->getRelationterm($orgid,$aTriple,$krtermLink);
      

	    return \Response::json([ 'orgid'=>$orgid, 'terms'=>$aTerm ]);

      //----  end of function getTerm    -----------------------------//-

	  }



   /*--------------------------------------------------------------------
   *  getArray   Get and array from a string. 
   *   EXAMPLES         "I,lost,job"     
   *                    "I,lost,job","economy,caused by,covid-19"
   *                    "I,have,job,and,I,lost,job","economy,caused by,covid-19"
   *  INPUT
   *    $translation    string  translation string
   *  OUTPUT
   *    $aStr           array   translation strings are array elements
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
	 *  getTriple          get triples from traslation
	 *  
   *  INPUT
	 *     $asTr    array    translation from NLU
   *  OUTPUT
   *     $aTriple array    array with triples and offset
	 *-------------------------------------------------------------------*/
   public function getTriple($asTr) {

     $newTriple     = "";
     $offset        = -1;
     //$asTr          = explode(",",$str);          //  astr[0] = "i,have" 
     $count         = count($asTr);               //  count = 1
     $aTriple       = array();


     for ($i=0;$i<$count;$i++) {  
         $sText     = $asTr[$i];                  // get string
      
         $offset=0;

         $loop = 1;

         while ($loop == 1) {
            $aT        = explode(",",$sText);         // convert to array
            $aTcount   = count($aT);                 // count of new array

            if ($aT[0] == "and") {
               array_shift($aT);                       // remove first element
               $sText  = implode(",",$aT);             // get new sText after removal
               $aT        = explode(",",$sText);         // convert to array
               $aTcount   = count($aT);  
               $offset++;
            }

            if ($aTcount > 2) {
               $newTriple = $aT[0].",".$aT[1].",".$aT[2];
               $aTriple[] = ['triple'=>$newTriple, 'offset'=>$offset, 'index'=>$i  ];
               array_shift($aT);                       // remove first element
               $sText  = implode(",",$aT);             // get new sText after removal
               $offset = $offset + 1;
            } else {
               $loop = 0;
            }
         }
     }    

     return $aTriple;

   } 
    //--  end of function getTriple  ------------------------


   /*--------------------------------------------------------------------
   *  getRelationterm
   *
   *-------------------------------------------------------------------*/
   public function getRelationterm($orgId, $aTriple,$linkName) {
   //public function getRelationterm($orgId, $aTriple,$relationTypeName) {


    $oTerm          = new Term();
    $oRelationType  = new RelationType();
    $oRelation      = new Relation();
    $oRelationTermLink  = new RelationTermLink();

    //$relationTypeId = $oRelationType->retrieveIdByName($linkName);
    $linkTermId     = $oTerm->retrieveTermIdByName($linkName);
    $aTerm = array();
    $aux   = array();
    $icount = 0;
    $windex = -1;
    $tcount = 0;        // count of distinct index values in $aTriple
   
    foreach($aTriple as $rs0) {
      $triple = $rs0['triple'];
      $offset = $rs0['offset'];
      $index  = $rs0['index'];
     
      $a  = explode(",",$triple);
      $sLeft  = $a[0];
      $sRT    = $a[1];
      $sRight = $a[2];
      $leftId  = $oTerm->retrieveTermIdByName($sLeft);
      $RTId    = $oRelationType->retrieveIdByName($sRT);
      $rightId = $oTerm->retrieveTermIdByName($sRight);
      $relationId = 0;
      $termId     = 0;
      $termName = "";

      if ($leftId > 0 and $RTId > 0 and $rightId  > 0) {
         $relationId = $oRelation->retrieveByLeftTypeRightId($leftId, $RTId, $rightId);
      }
      if ($relationId > 0) {
         $termId = $oRelationTermLink->retrieveTerm($orgId,$relationId,$linkTermId );
         //$termId = $oRelationTerm->retrieveTerm($orgId,$relationId,$relationTypeId);
      }
      if ($termId > 0) {
         $termName = $oTerm->retrieveTermName($termId);
      }

      $inew = 0;
      if ($windex ==  $index) {
          $windex = $windex;
      } else {  
         $windex = $index;
            $tcount++;
            $inew = 1;
            $icount = 0;
      }
 

      // check if superterm was found
      if ($termName == "") {
         $termId = 0; 
     
      } else {
       
         $aux[] = ['triple'=>$triple, 'offset'=>$offset, 'term'=>$termName, 'index'=>$index ]; 

         $icount++;
    
      }

    }

    

    // create $aTerm
    //   if element index exists in $aux, copy from $aux to $aTerm
    //   otherwise add empty element:  $aTerm[]=[]


    $ivalue = -1;
    $copycount = 0;
    for ($i=0; $i<$tcount; $i++) {

        $tindex = -1;
        if (array_key_exists($i,$aux)) {
           $tindex = $aux[$i]['index'];   
           $ivalue = $tindex;       

           $aCopy ="";

           if ($tindex == $i) {
             // copy from $i element from $aux to $aTerm
             $aCopy  =  $aux[$i];
             $aTerm[]  = [$aCopy];
             $ivalue   = -1;
             $copycount++;
           } else {

             if ($copycount < $tcount) {          
                $aTerm[]=[]; 
                $copycount++;
             }


             if ($ivalue > -1) {
                if ($copycount < $tcount) {
                   $aCopy  =  $aux[$i];
                   $aTerm[]  = [$aCopy];
                   $ivalue   = -1;
                   $copycount++;            
                }
             }

           } 

        } else {
            
            if ($copycount < $tcount) {          
                $aTerm[]=[]; 
                $copycount++;
            }

        }

    }

    // remove element [$i][0][index] from $aTerm
    $count = count($aTerm);
    for ($i=0;$i<$count;$i++) {
       if (array_key_exists($i,$aTerm)) {
           if (isset($aTerm[$i][0]['index'])) {
             $tindex = $aTerm[$i][0]['index'];     
             unset($aTerm[$i][0]['index']);        
           }
      
       }
    }


    return $aTerm;

   }

    //--  end of function getRelationterm  ------------------------


}
