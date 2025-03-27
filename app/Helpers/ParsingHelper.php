<?php
/*--------------------------------------------------------------------------------
 *  File          : ParsingHelper.php        
 *	Type          : Helper class
 *  Function      : Provides  validation function for input text
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation KDC.
 *  Version       : 2.6
 *  Updated       : 10 December 2023
 *---------------------------------------------------------------------------------*/


namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\Term;
use App\Models\RelationType;
use App\Models\Relation;
use App\Models\RelationTypeFilter;
use App\Models\SolutionFact;


class ParsingHelper
{
	
    //--------------------------------------------------------------------
    public function splitInputText($inputText)
    /*   split input text in n dimensional array.
     *   n is number of single statements
     *   input    string $inputText 
     *   output   array  $aSplitText
     */
     {

        $aInputText  = explode(";",$inputText);
        $aInput  = $aInputText;
        $arraylen = sizeof($aInputText);
        $position = -1;
        $awrk= array();
        $outputArray = array();


/*
        for ($i=0;$i<$arraylen;$i++) {
    
            if ($aInputText[$i] ==  "and" ) {
                $aInputText[$i] = "EOFSENTENCE";
                $position = $i;
                $tokText  = implode(",",$awrk); 
                $outputArray[] = $tokText;
                unset($awrk);
            } else {
                $awrk[]    = $aInputText[$i];
            }
        }
*/


/*
            $anew = array();
            if ($arraylen > 3){
                for($i=0;$i<3;$i++) {
                   $anew[] = $aInputText[$i];
                }
                $tokText  = implode(",",$anew); 
                $outputArray[] = $tokText;                
            }
*/



          if($arraylen > 2) {

             $j = 0;
             $anew = array();
             for($i=0;$i<$arraylen;$i++) {

                if($j < 3) {
                    $anew[] = $aInput[$i];

                    if($j == 2) {
                        $tokText  = implode(",",$anew); 
                        $outputArray[] = $tokText; 
                        $anew = array();
                        //$j = 0;
                    }
                    $j++;

                } else {
                    $anew = array();                   
                    if($aInput[$i] == "and") {
                       $j = 0;
                    } 


                }

             }

            //echo " BB ";
            //print_r($outputArray);    
          }
         // $outputArray = $outputArray2;
          
        return $outputArray;
     }
    
    //------   get length of last button    -----------
    function getButtonLength($aText,$elementOrder)
     { 
        $bLength  = 0;

        $count  = count($aText);

        if ($count > 0) {

              // Extract elements
            for($i=0;$i<$count;$i++) {
                  $order = $aText[$i]['elementOrder'];

                if ($order == $elementOrder) {
                	$bText   = $aText[$i]['text'];
                    $bLength = strlen($bText );
                }
            }
        }


        return  $bLength;
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
                    'attname'=>"RPA message", 'atttype'=>"text", 'elementType'=>"comment", 
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
	public function splitInputTextnew($inputText)
	/*   split input text in n dimensional array.
	 *   n is number of single statements
	 *   input    string $inputText 
	 *   output   array  $aSplitText
	 */
     {

        $aInputText  = explode(";",$inputText);
        $aInput  = $aInputText;
        $arraylen = sizeof($aInputText);
        $position = -1;
        $awrk= array();
        $outputArray = array();
        $outputArray2 = array();


        for ($i=0;$i<$arraylen;$i++) {
	
            if ($aInputText[$i] ==  "and" ) {
                $aInputText[$i] = "EOFSENTENCE";
                $position = $i;
                $tokText  = implode(",",$awrk);	
                $outputArray[] = $tokText;
                unset($awrk);
            } else {
                $awrk[]    = $aInputText[$i];
            }


        }

        // No EOFSENTENCE
        if($position == -1 ) {
            // if one triple, remove from array key 3
            $anew = array();
            if ($arraylen > 2){
                for($i=0;$i<3;$i++) {
                   $anew[] = $aInputText[$i];
                }
                $tokText  = implode(",",$anew); 
                $outputArray[] = $tokText;                
            }
        }

        // At leat one EOFSENTENCE
        if (isset($awrk)) {
          if($position >-1) {
 //echo " BB ";
//print_r($awrk);
             $j = 0;
             $anew = array();
             for($i=0;$i<$arraylen;$i++) {
//echo " AA i=$i j=$j ";
                if($j < 3) {
                    $anew[] = $aInput[$i];
//print_r($anew);
                    if($j == 2) {
                        $tokText  = implode(",",$anew); 
                        $outputArray2[] = $tokText; 
                        $anew = array();
                        //$j = 0;
                    }
                    $j++;

                } else {
                    $anew = array();                   
                    if($aInput[$i] == "and") {
                       $j = 0;
                    } //else {
                      // $i = $arraylen;
                    //}

                }

             }

            //echo " BB ";
            //print_r($outputArray);	
          }
          $outputArray = $outputArray2;
        }
		
        //$aSplitText = $outputArray;
        echo "  CC ";
        print_r($outputArray);
        return $outputArray;
	 }
	

    //--------------------------------------------------------------------
    public function splitInputTextORI($inputText)
    /*   split input text in n dimensional array.
     *   n is number of single statements
     *   input    string $inputText 
     *   output   array  $aSplitText
     */
     {

        $aInputText  = explode(";",$inputText);
        $arraylen = sizeof($aInputText);
        $position = -1;
        $awrk= array();
        $outputArray = array();


        for ($i=0;$i<$arraylen;$i++) {
    
            if ($aInputText[$i] ==  "and" ) {
                $aInputText[$i] = "EOFSENTENCE";
                $position = $i;
                $tokText  = implode(",",$awrk); 
                $outputArray[] = $tokText;
                unset($awrk);
            } else {
                $awrk[]    = $aInputText[$i];
            }


        }

        // No EOFSENTENCE
        if($position == -1 ) {
            $tokText  = implode(",",$aInputText);                                       
            $outputArray[] = $tokText;
        }

        // At leat one EOFSENTENCE
        if (isset($awrk)) {
        if($position >-1) {
            $tokText  = implode(",",$awrk);                     
            $outputArray[] = $tokText;      
        }
        }
        
        $aSplitText = $outputArray;
        echo "  CC ";
        print_r($aSplitText);
        return $aSplitText;
     }
    
 

	//--------------------------------------------------------------------
	public function convertStringToArray($inputText)
	/*   split input text in n dimensional array.
	 *   n is number of single statements
	 *   input    string $inputText 
	 *   output   array  $aSplitText
	 */
	 {
        $aInputText = array();
        $aInputText[] = $inputText;		
        return $aInputText;
	 }	 

    //--------------------------------------------------------------------
    public function getSBTermArray($slidebar)
    /*   replace term name with term id in slide bar array
     *   input    [termName][scalar] 
     *   output   [termId][scalar]
     */
     {

        $oTerm = new Term();
        $SBT = array();  
        foreach($slidebar as $key=>$value) {
            $termId  = $oTerm->retrieveTermIdByName($key);
            $SBT[$termId] = $value;
        }

        return $SBT;
     }



	//--------------------------------------------------------------------
	public function validateTerms($aSplitText, $orgId)
	/*   validate term1, verb, term 2.
	 *   input    array  $aSplitText 
	 *   output   int    $validationError  // 0=No error;  1=error  
	 */
	 {
        // Instantiate classes
        $oTerm          = new Term();
        $oRelationType  = new RelationType();		
        //$validationError = 0;  // 0=No error;  1=error
        $validationError = 1;  // 0=No error;  1=error
        $relationTypeAssociation = "is a division of";
        $rtAssociationId = $oRelationType->retrieveIdByName($relationTypeAssociation);
        $arraylen = sizeof($aSplitText);
        $isError = 0;

        $termName = "";
        for ($i=0;$i<$arraylen;$i++) {
            // variables to find $sfRelationId that matches session fact text
            $sfRelationId     = 0;
            $sfLeftTermId     = 0;
            $sfRelationTypeId = 0;
            $sfRightTermId    = 0;

            $textString = $aSplitText[$i];
            // $textString = Term1, verb, term2 

            $isFound = 1; // 0= term not found; 1 = term found

            //  find term1
            $termName = "";
            $arrayText  = explode(",",$textString);
            if (isset($arrayText[0])) {
                $termName = $arrayText[0];	
                $rsdata = $oTerm->findTermByNameOwner($termName,$orgId,$rtAssociationId);// find term1
                if (empty($rsdata)) {
                    $isFound = 0;                           // term not found
                } else {
                    $termName = $this->replacePronoun($termName);  // term found
                }  
            } else { 
                $isFound = 0;                              // no term1
                $isError = 1;
            }
		
            //  find verb
            if (isset($arrayText[1])) {
                $termName = $arrayText[1];	
                $rsdata = $oTerm->findTermByNameOwner($termName,$orgId,$rtAssociationId);// find verb
                if (empty($rsdata)) {
                    $isFound = 0;                          // verb not found
                }  
						  
            } else { 
                $isFound = 0;                             // no verb
                $isError = 1;
            }

            //  find term 2
            if (isset($arrayText[2])) {
                $termName = $arrayText[2];	
                $rsdata = $oTerm->findTermByNameOwner($termName,$orgId,$rtAssociationId);// find term 2
                if (empty($rsdata)) {
                    $isFound = 0;                           // term not found
                }  
            } else { 
                $isFound = 0;                              // no term 2
                $isError = 1;
            }
 		
            if ($isFound == 1 and $isError == 0) {
                $validationError = 0;                  // validaton error
            }

        }		 
	 
	    return $validationError;
	 }	 


    //--------------------------------------------------------------------
    public function isLonginput($text,$cmdLonginput)
    /*   validate parameter count
     *   input    array  $aSplitText 
     *   output   int    $validationError  // 0=No error;  1=error  
     */
     {
 
        $isLong = 0;
        $aText = explode(";",$text);
        $alen = sizeof($aText);

        switch ($alen) {
           case 1:
              if ( $aText[0] == $cmdLonginput ) {
                 $isLong = 1;
              }
           break;

           case 5:
              if ( $aText[4] == $cmdLonginput ) {
                 $isLong = 1;
              }
           break;

        }

        return $isLong;
     }

    //--------------------------------------------------------------------
    public function cleanValue($aText)
    /*   Remove duplicate values from option text
     */
     {
       $value = "*901*0000000000*0*000000000";   
       $wval = ""; 
       $aOption = array();
       $len = count($aText);
       for ($i=0;$i<$len;$i++) {
           $val = $aText[$i]['value'];
           //$val5 = substr($val,0,5);        // the first 5 characters

           if ($wval == $val and $value == $val) {
           //    unset($aText[$i]);
           } else {
             $aOption[] = $aText[$i];
           }
           $wval = $val;
       }

        return $aOption;
     }
    //--------------------------------------------------------------------
    public function isInputError($aSplitText)
    /*   validate parameter count
     *   input    array  $aSplitText 
     *   output   int    $validationError  // 0=No error;  1=error  
     */
     {

        $sText = implode($aSplitText,",");
        $aText = explode(",",$sText);
        $alen = sizeof($aText);
        $modulus = $alen % 3;

        // check error
        if ($modulus == 0) {
            $isError = 0;
        } else {
            $isError = 1;
        }

        return $isError;
     }
 

    //-------------------------------------------------------------------- 
    public function getPortalType($apikey="")
     { 
        
        $sText  = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";  // text portal type
        $sVoice = "abcdefghijklmnopqrstuvwxyz";            // voice portal type
        $portalType = "text";    
        $apikeylen  = 0;
                                 // default value
        if (!empty($apikey)) {
           $portalNumber =  strtolower( substr($apikey,0,1) );
           $textCount  = substr_count($sText,$portalNumber);
           $voiceCount = substr_count($sVoice,$portalNumber);

           if ($textCount > 0) {
              $portalType = "text";  
           }
           if ($voiceCount > 0) {
              $portalType = "voice";   
           }
        }

        return $portalType;
     }  

    //-------------------------------------------------------------------- 
    /*    $singleUtterance = 1   -> no "and" in utterance
          $singleUtterance = 0   -> "and" in utterance
     */ 
    public function isSingleUtterance($utterance="")
     { 
        $needle  = " and ";        // string to be searched
        $pos = strpos($utterance , $needle); 

        if ($pos === false) {
          $singleUtterance = 1;
        } else {
          $singleUtterance = 0;
        }

        return $singleUtterance;
     }  


    //-------------------------------------------------------------------- 
    /*    $multipleTriple = 1   -> more than one triple
          $multipleTriple = 0   -> 1 or 0 triple
     */ 
    public function isMultipleTriple($aText="")
     { 

        $multipleTriple  = 0;         // default value
        $outCount = count($aText);
        if (isset($outCount)) {
           if ($outCount > 1  ) {
             $multipleTriple  = 1;   
           }
        }

        return $multipleTriple;
     }  

    //-------------------------------------------------------------------- 
    public function getContentType2($answers)
     {  
        $contentType = "kr";
        $count   = 0;
        $stlen = count($answers); 

        for ($i=0;$i<$stlen;$i++) {

        	if (isset($answers[$i]['subject'])) {
               $contentType = "kr";     // knowledge records
               $count       = 1;
        	}

        	if (isset($answers[$i]['attname'])) {

                 if ( isset($answers[$i]['url']) ) {
                 	$contentType = "edtext,url";     // knowledge records
                 	$count       = 3;
                 }
                 
                 if (isset($answers[$i]['text'])) {
                 	if ($count < 3) {
                 	   $contentType = "edtext";        // knowledge records
                       $count       = 2;
                 	}
                 }
        	}

        }
        return $contentType;
     } 

    //-------------------------------------------------------------------- 
    public function getContentType($answers)
     {  
        $contentType = "text";
        $isKr       = 0;
        $isUrl      = 0;
        $isText     = 0;

        $stlen = count($answers); 

        for ($i=0;$i<$stlen;$i++) {

        	if (isset($answers[$i]['shortText'])) {
               $isKr       = 1;
        	}

        	if (isset($answers[$i]['atttype'])) {
        		$attType = $answers[$i]['atttype'];

                if ( $attType == 'url' ) {
                 	$isUrl    = 1;
                }
                 
                if ( $attType == 'text' ) {
                    $isText   = 1;
                }
        	}

        }

        //
        if ($isKr == 1) {
        	$contentType = "kr";
        }

        if ($isUrl == 1 and $isText == 1) {
        	$contentType = "edtext,url";
        }

        if ($isUrl == 0 and $isText == 1) {
        	$contentType = "edtext";
        }

        if ($isUrl == 1 and $isText == 0) {
        	$contentType = "url";
        }        


        return $contentType;
     } 


    //--------------------------------------------------------------------
    public function getSingleTerm($sText)
    /*   get single term from input text
     *   input    array  $sText   : original input text
     *   output   array  $sterm   : single term name 
     */
     {
        $sTerm  = "";
        $aText = explode(",",$sText);
        $alen = sizeof($aText);
       
        if ($alen == 1) {
            $sTerm = $aText[0];
        }

        return $sTerm;
     }

    //--------------------------------------------------------------------
    public function getKeyword($sText)
    /*   get single term from input text
     *   input    array  $sText   : original input text (string)
     *   output   array  $aTerm   : term name (array) 
     */
     {
        $aTerm  = array();
        $aText = explode(",",$sText);
        $alen = sizeof($aText);
        $loop  = 0;

        if ($alen > 0) {
        	$pos = $alen -1;
        } else {
        	$pos = $alen;
        }
       
        $term = $aText[$pos];      
        $aTerm[] = $term;


        $pos = array_search("and",$aText);

        if ($pos !== false) {
            $aText = array_slice($aText,0,$pos);

            $alen = sizeof($aText);     
            if ($alen > 0) {
        	  $pos = $alen -1; 
            } else {
        	  $pos = $alen;
            }
       
            $term = $aText[$pos];      
            $aTerm[] = $term;
        

        } else {
            $loop  = 1;
        }        

        //if (sizeof($aTerm) > 0  ) {
        	$rs = $aTerm;
        //} else {
        //	$rs = 0;
        //}

        return $rs;
     }


    //--------------------------------------------------------------------
    public function sanitizeText($sText)
    /*   validate parameter count
     *   input    array  $sText   : orinigal input text
     *   output   array  $aNText  : Sanitized input text 
     */
     {

        $sNText = $sText;
        $aText = explode(";",$sText);
        $alen = sizeof($aText);
        $modulus = $alen % 4;
        $fError = 0;
        $sError = 1;
        $pos = $alen - $modulus -1;
       
        // minumin length is 3
        if ($alen < 3) {
           $fError = 1;             
        }
        // check for valid lengths
        if ($alen == 3 or $alen == 7 or $alen == 11 or $alen == 15 or $alen ==19 or $alen== 23) {
            $sError = 0;
        }


        // check error
        if ($fError == 0 and $sError == 1) {
            $error2  = 0;
            $s       ="";
            for ($i=0;$i<$alen;$i++) { 
                if ($i < $pos ) {
                    if ($i == 0) {
                       $s = $s.$aText[$i];
                    } else {
                       $s = $s.";".$aText[$i];
                    }
                }              
            }
            $sNText = $s;
        }

        return $sNText;
     }

    //--------------------------------------------------------------------
    public function sanitizeText1($sText)
    /*   validate parameter count
     *   input    array  $sText   : orinigal input text
     *   output   array  $aNText  : Sanitized input text 
     */
     {

        $sNText = $sText;
        $aText = explode(";",$sText);
        $alen = sizeof($aText);
        $modulus = $alen % 4;
        $limit = $alen - 1;
        $fError = 0;
        $sError = 1;
        $pos = $alen - $modulus -1;
        $needle = "and";
        $hasAnd  = strpos($sText, $needle);
        if ($hasAnd === false) {
            $hasAnd = 0;
        } 
       
        // minumin length is 3
        if ($alen < 3) {
           $fError = 1;             
        }
        // check for valid lengths
        if ($alen == 3 or $alen == 7 or $alen == 11 or $alen == 15 or $alen ==19 or $alen== 23) {
            $sError = 0;
            if ($hasAnd == 0) {
                $sError = 1;
            }
        }


        // check error
        if ($fError == 0 and $sError == 1) {
            $error2  = 0;
            $s       ="";
            for ($i=0;$i<=$limit;$i++) { 


                if ($i == 0) {
                    $s = $s.$aText[$i];
                } else {
                    if ($hasAnd == 0) {
                          if ($i < 3) {
                             $s = $s.";".$aText[$i];
                          }
                          if ($i == 3) {
                             $s = $s.";"."and";
                          }
                          if ($i == $limit ){
                             $s = $s.";".$aText[$i];
                          }                          
                    }

                    if ($hasAnd > 0) {
                          if ($i <= 3 or $i == $limit) {
                             $s = $s.";".$aText[$i];
                          }
                        
                    }

                }



            }

            $sNText = $s;
        }

        return $sNText;
     }


    //--------------------------------------------------------------------
    public function sanitizeText2($sText)
    /*   validate parameter count
     *   input    array  $sText   : orinigal input text
     *   output   array  $aNText  : Sanitized input text 
     */
     {
// I,would like information on,you,do,for,mosquitoes
// modulus calculates remaining after division

        // determine if there is "and"; in $sText 
        $hasAnd = 0;
        $needle  = ";and;";        // string to be searched
        $pos = strpos($needle , $sText); 
        if ($pos === false) {
           $hasAnd = 1;
        }

        $sNText = $sText;
        $aText = explode(";",$sText);
        $alen = sizeof($aText);
        $modulus = $alen % 4;
        $fError = 0;
        $sError = 1;
        $pos = $alen - $modulus -1;

       
        // minumin length is 3
        if ($alen < 3) {
           $fError = 1;             
        }
        // check for valid lengths
        if ($alen == 3 or $alen == 7 or $alen == 11 or $alen == 15 or $alen ==19 or $alen == 23) {
            $sError = 0;
        }

 
        // check error
        if ($fError == 0 and $sError == 1) {
            $error2  = 0;
            $s       ="";
            for ($i=0;$i<$alen;$i++) { 
                if ($i < $pos ) {
                    $s = $s.$aText[$i].";";
                }  
                if ($i == $pos ) {
                   if ($hasAnd == 0) {
                      $s = $s."and".";";                    
                   } else {
                      $s = $s.$aText[$i].";";                    
                   }

                }

                if ($i == $alen - 1) {
                    $s = $s.$aText[$i];
                }

            }
            // trim rightmost;

            $sNText = $s;
        }

        return $sNText;
     }


    //--------------------------------------------------------------------
    public function sanitizeText3($sText,$sTerm)
    /*   choff off text from and 
     *   left term1,rt1,right term1,and, term2

        sText     hi;and;I;need;quote;  sterm=quote;
        output    hi;and;I; 

     */
     {

        $sString = "";
        $aText = explode(";",$sText);
        $alen = sizeof($aText);
        $pos = 2;

//echo " S1 sText=$sText; alen=$alen; ";
        if ($sTerm == "") {
            $sString = $sText;
        } else {
            for ($i=0;$i<=$pos;$i++) { 
                if ($i < $pos) {
                    $sString = $sString.$aText[$i].";";
                } else {
                    $sString = $sString.$aText[$i];                    
                }
            }

        }




        return $sString;
     }

    //--------------------------------------------------------------------
    public function getRightKeyword($sText="")
    /*   get the right mos term when input is
     *   left term1,rt1,right term1,and, term2
     */
     {
// I,would like information on,you,do,for,mosquitoes

        $sTerm = "";
        $aText = explode(";",$sText);
        $alen = sizeof($aText);

        if ($alen == 5) {
            if ($aText[3] == "and") {
                $sTerm = $aText[4];
            }
        }

        return $sTerm;
     }

    //--------------------------------------------------------------------
    public function getRightMostKeyword($sText)
    /*   get the right most term form $sText
     */
     {

        $sTerm = "";
        $aText = explode(";",$sText);
        $alen = sizeof($aText);

        if ($alen > 3) {
            $i = $alen - 1;
            $sTerm = $aText[$i];
        }

        return $sTerm;
     }


    //--------------------------------------------------------------------
    public function removeEmptyFact($userid)
    /*   remove empty solution fact records
     *   sfrating = 0
     */
     {
        $oSolutionFact        = new SolutionFact();    
        $rs = $oSolutionFact->getByUser($userid);

        foreach($rs as $rs0) {
           $sfId              = $rs0['sfId'];
           $sfRating          = $rs0['sfRating'];
           if ($sfRating == 0 ) {
              $oSolutionFact->deleteById($sfId);
           } 
        }

     }

    //--------------------------------------------------------------------
    public function replaceTerm1ByPerson($aSplitText)
    /*   Given $aSplitTExxt =" term1, verb, term2", 
     *      replace term1 by "person".
     *   input    array  $aSplitText 
     *   output   array  $aSplitText   
     */
     {
        $arraylen = sizeof($aSplitText);
        for ($i=0;$i<$arraylen;$i++) {
            $textString = $aSplitText[$i];
            // $textString = Term1, verb, term2 

            $isFound = 1; // 0= term not found; 1 = term found

            //  find term1
            $termName = "";
            $arrayText  = explode(",",$textString);
            if (isset($arrayText[0])) {
                $termName = $arrayText[0];  
                $termName = $this->replacePronoun($termName);
                $arrayText[0] =  $termName;
            }
            $aSplitText[$i] = implode(",", $arrayText);
        }    
     
        return $aSplitText;
     }



    //--------------------------------------------------------------------
    public function replacePronoun(string $str)
    /*   I -> person 
 
	 */
     { 
        $repstr = trim($str);
        $str = trim(strtolower($str));
		
        // Instantiate classes
        $oTerm            = new Term();
        $oRelationType    = new RelationType();
        $oRelation        = new Relation();
        $oRelationTypeFilter = new RelationTypeFilter();
		
        // get termId WHERE termName = $str
        $leftId = $oTerm->retrieveTermIdByName($repstr);
        if ($leftId == 0) {
            $leftId = $oTerm->retrieveTermIdByName($str);
        }
        
        // get relationTypeId from table relation_type_filter, step = 6 (is a member of)
        $step = 6;
        $relationTypeId = $oRelationTypeFilter->retrieveByStep($step);  
		
        // get termId WHERE termName ="pronoun"
		$name = "pronoun";
        $rightId = $oTerm->retrieveTermIdByName($name);

        // get relationId WHERE relationTypeName ="is a member of" and 
		//    righttermName = "pronoun"
        $relationId = $oRelation->retrieveByLeftTypeRightId($leftId, $relationTypeId, $rightId);			

        if ($relationId > 0 ) {
           $repstr = "Person";
        } else {
           // get relationTypeId from table relation_type_filter, step = 7 (is a type of)
           $step = 7;
           $relationTypeId = $oRelationTypeFilter->retrieveByStep($step);  
           $name = "person";
           $rightId = $oTerm->retrieveTermIdByName($name);
           $relationId = $oRelation->retrieveByLeftTypeRightId($leftId, $relationTypeId, $rightId);
           if ($relationId > 0 ){
              $repstr = "Person";           
           }
        }
		
        return $repstr;
     }	 
	 
}