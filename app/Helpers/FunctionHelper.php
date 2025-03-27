<?php
/*--------------------------------------------------------------------------------
 *  File          : FunctionHelper.php        
 *	Type          : Helper class
 *  Function      : Provides assorted functions for input text
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 3.03
 *  Updated       : 02 June 2024
 *---------------------------------------------------------------------------------*/

namespace App\Helpers;

use Illuminate\Http\Request;


class FunctionHelper
{

    //------------------------------  call api   ---------------------- 
    public function callApi($url,$params)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
 
        //execute the request
        $chResponse = curl_exec($ch);
        $result = json_decode($chResponse);
        curl_close($ch);

        return $result;
    }


    //-------     replace contraction: I'm -> I am   ----------------------	
    function replaceContractionInArray($sText)
     {
        // replacement pattern
        $pattern = array("I’m", "I’m ", "You’re", "you’re", "He’s", "he’s", "She’s", "she’s",
		       "We’re","we’re", "They’re", "they’re", "I'm", "I'm ", "You're", "you're", "He's",
			   "he's", "She's", "she's","We're","we're", "They're", "they're");
        $replacement = array("I am", "I am", "you are", "you are", "He is", "he is", "She is",
               "she is", "We are", "we are","they are","they are", "I am", "I am", "you are", 
			   "you are","He is","he is","She is","she is","We are","we are","they are","they are");

	    // convert string to array
        $aText = explode(" ",$sText);
		$len = sizeof($aText);
		for ($i=0; $i<$len; $i++) {	
           $str = $aText[$i];		
           $aText[$i] =  str_replace($pattern, $replacement, $str);
        }
		$sText = implode(" ",$aText);
	    return $sText;
     }


    //---  extract data from Microsoft bot response ------------------------------------------ 
    function extractDataArray($cr)
     {  

        $needle1 = '{';
        $needle2 = '}';
        $cr2     = "";
        $len     = strlen($cr);
        $state   = 0;

        for ($i=0;$i<$len;$i++){

            $piece = substr($cr,$i,1);

            switch($state) {

                case 0:
                    if ($piece == $needle1) {
                        $state = 1;
                    }
                    break;

                case 1:
                    if ($piece == $needle2) {
                        $state = 2;
                    }
                    break;

                case 2:
                    if ($piece != $needle2) {
                        $state = 3;
                    }
                    break;

            }

            if ($state==1 or $state==2){
                $cr2 = $cr2.$piece;
            }

        }


        $cr3 = json_decode($cr2);
        return $cr3;
  
     }  


    //---  remove stop words from non-deleimited text (a, an, on, the, . --
	function removeStopWord($inputText)
     {	
        $patterns = array(" an ", " the ", " a ", " on ");
        $replacement = array(" ", " ", " ", " ");
        return  str_replace($patterns, $replacement, $inputText);	
     }	 

    //---  remove stop words from delimited text (a, an, on, the, . ----------------------
	function removeDelimitedStopWord($inputText)
     {	
        $patterns = array(",an," , ",the," , ",a," , ",on,");
        $replacement = array("," , "," , "," , ",");
        return  str_replace($patterns, $replacement, $inputText);	
     }	

    //------ remove extra blank spaces ---------------------------------
	function removeExtraBlank($inputText)
     { 
		return preg_replace("~\s+~"," ",$inputText);
     }
		
    //------   replace "," by "and"    ----------------------------------
	function replaceCommaByAnd($inputText)
     { 
        $patterns = array(", ", ",");
        $replacement = array(" and ", " and ");
        return  str_replace($patterns, $replacement, $inputText);
     }	

    //------   get inquiry text ----------------------------------
    function getInquiry($inputText)
     { 
        $patterns = array(",");
        $replacement = array(" ");
        return  str_replace($patterns, $replacement, $inputText);
     }  

    //------   get right term ----------------------------------
    function getRightTerm($sText)
     { 
        $rightTerm = "";
        $aText = explode(",",$sText);
        if (isset($aText[2])) {
           $rightTerm = $aText[2];
        } elseif (isset($aText[0])){
           $rightTerm = $aText[0];
        }

        return  $rightTerm;
     } 

    //------   input text is a relation pick -------------------------
    function getProblemId($inText)
     { 
        $problemId = 0;
        $len = strlen($inText);
        if ($len >40) {
            $problemId = substr($inText,31,10); 
            $problemId = (int)$problemId;        
        } 
        return $problemId;
     }

    //------   input soId from in text   -------------------------
    function getSoId($inText)
     { 
        $soId = 0;
        $len = strlen($inText);
        if ($len >= 15) {
            $soId = substr($inText,5,10); 
            $soId = (int)$soId;        
        } 
        return $soId;
     }       

    //------   replace "," by blank    ------------------------------
	function replaceCommaByBlank($inputText)
     { 
        $patterns = array(", ", ",");
        $replacement = array(" ", " ");
        return  str_replace($patterns, $replacement, $inputText);
     }

    //---   replace ". " by "and"   (not at the end of sentence) --------
	function replacePeriodByAnd($str="")
     { 
        $strlen = mb_strlen($str, 'utf8');	
        $limit = $strlen - 1;
		
        for ($i=0; $i < $strlen; $i++) {
					
			
            if (substr($str,$i,1) == "." and ($i != $limit)) {
                $patterns = array(".");
                $replacement = array(" and ");
                $str =  str_replace($patterns, $replacement, $str);
            }          
        }
        return $str;
		
     }	 

    //------   replace "but " by "and"    ------------------------------
	function replaceButByAnd($inputText)
     { 
        $patterns = array(" but ");
        $replacement = array(" and ");
        return  str_replace($patterns, $replacement, $inputText);
     }  

    //------   replace "but " by "and"    ------------------------------
    function replacePersonByYou($str)
     { 
        if ($str == "person") {
           $str = "you";
        }
        return $str;
     }

    //------   replace "," by ";"    ------------------------------
	function replaceCommaBySemicolon($inputText)
     { 
        $patterns = array(",");
        $replacement = array(";");
        return  str_replace($patterns, $replacement, $inputText);
     }  	 
	 
    //------   check whether solution array has address    -----------
    function hasAddress($soArray,$keyw)
     { 
        $hasAddress  = 0;

        foreach ($soArray as $item) {
            if (isset($item['attname'])) {
               $att   = $item['attname'];
               $ar3[] = $att;
               if ($att == $keyw) {
                  $hasAddress = 1;
               }
            }
        }        

        return  $hasAddress;
     } 

     
     //----------------------------------------------------------------
    public function extractSegmento($inputText)
     /*   
      *   get segmnt form input text
      *   input    string $inputText 
      *   output   string $sSegment
     */
   {
    //   $timeStart = microtime(true);
        $sSegment = $inputText;
        $search = "and";
        $aText  = explode(",",$inputText);
        $aText  = array_reverse($aText);
        $sText  = implode(",",$aText);
        $isFound = 0;
        $aNew    = array();

        foreach ($aText as $key=>$value) {

           if ($isFound == 0) {

              if ($value == $search) {
                  $isFound = 1;
                  $pos1    = $key;
              } else {
                  $aNew[] = $value;               
              }
           } 

        }

        if ($isFound == 1) {
            $aText    = array_reverse($aNew);     
            $sSegment = implode(",",$aText);          
        }

        $sSegment = $this->removeStopword2($sSegment);


        return $sSegment;
   }   


    //-------------------------------------------------------------------
    public function getRightmostTerm($sText="")
    /*   get the rightmost term 
     */
     {
      //  $timeStart = microtime(true);


        $sTerm = "";
      
        $aText = explode(",",$sText);
        $alen = sizeof($aText);
        if ($alen == 1) {
           $sTerm = $sText;
        } else {
           $sTerm = $aText[$alen - 1];           
        }


        return $sTerm;
     }
    //-------------------------------------------------------------------
    public function choppSegment($sText="")
    /*   get the rightmost term 
     */
     {
        $sTerm = "";
        $aNew  = array();
        $aText = explode(",",$sText);
        $alen = sizeof($aText);
        if ($alen > 1) {
            for ($i=0;$i< $alen-1; $i++ ) {
                $aNew[] = $aText[$i];
            } 
            $sTerm = implode(",",$aNew);          
        }

        return $sTerm;
     }

    //-------------------------------------------------------------------
    public function getMappingType($aMapping)
    /*   $aMapping['mapping']['type'] > 3   => RPA
         $aMapping['mapping']['type'] = 2   => Kaas
         $aMapping['mapping']['type'] = 3   => Live Agent
         $aMapping['mapping']['type'] = 4   => RPA1
         $aMapping['mapping']['type'] = 5   => RPA2
     */
     {
        $mappingType = 0;

        if (isset($aMapping['mapping']['type'])) {  
           $mappingType =  $aMapping['mapping']['type'];        
        }

        return $mappingType;
     }

    //-------------------------------------------------------------------
    public function getShowOrder($aMapping)
    /*   $aMapping['mapping']['showOrder'] > 3   => RPA
         $aMapping['mapping']['showOrder'] = 2   => Kaas
         $aMapping['mapping']['showOrder'] = 3   => Live Agent
         $aMapping['mapping']['showorder'] = 4   => RPA1

     */
     {
        $showOrder = 0;

        if (isset($aMapping['mapping']['showOrder'])) {  
           $showOrder =  $aMapping['mapping']['showOrder'];        
        }

        return $showOrder;
     }


    //--------------------------------------------------------------------
    public function removeStopword2($sText)
    /*   remove stop words: a,an,the,in,on,at,from,to
     *   input    string: $sText
     *   output   string: $sNewText
     */
     {

        $sNewText  = "";
        $aText  = explode(",",$sText);
        $keywords = array();
        $stopwords = array("a","an","the","in","on","at","from","to","i","you","he","she",
               "it","we","they","person");

        foreach ($aText as $term) {
          if (!in_array($term, $stopwords)) {
             $keywords[] = $term;
          }
        }
        
        $nCount  = count($keywords);
        if ($nCount > 0) {
            $sNewText = implode(",",$keywords);
        }

        return $sNewText;
     }


    //------   trim end of sentence "."   ------------------------------
	function trimEOSPeriod($str)
     { 
        $str = trim($str);	
        $strlen = strlen($str);	
        $i = $strlen - 1;

        if (substr($str,$i,1) == ".") {
            $str = substr($str,0,$i);
            $str = trim($str);
        }
        return $str;
     }

    //------   trim end of sentence "."   ------------------------------
	function replacePronoun($str)
     { 
        $repstr = $str;
        if ($str == "I" or $str =="i") {
           $repstr = "Person";
		} 
        if ($str == "You" or $str == "you") {
           $repstr = "Person";
        }
        if ($str ==  "He" or $str == "he" ) {
           $repstr = "Person";
        }
        if ($str ==  "She" or $str == "she" ) {
           $repstr = "Person";
        }
        if ($str ==  "They" or $str == "they" ) {
           $repstr = "Person";
        }
        if ($str ==  "We" or $str == "we" ) {
           $repstr = "Person";
        }
        return $repstr;
     }
	
    //-------------------------------------------
    //   input       string $apikey
    //   output      string $portalType:   't'=text;  'v'=voice  
    function getLexMode($inText)
     { 
        
        $lexMode = 0;
        $modeText = "*904*";  // Ideltifies Amazon lex 

        if (substr($inText,0,5)  == $modeText) {
           $lexMode = 1;
        }

        return $lexMode;
     }   


    //-------------------------------------------
    //   input       string $apikey
    //   output      string $portalType:   't'=text;  'v'=voice  
	function getPortalType($apikey)
     { 
        
        $portalNumber =  strtolower( substr($apikey,0,1) );

        //$sText  = "1234567890";
        $sVoice = "abcdefghijklmnopqrstuvwxyz"; 
        $portalType = "text";                             // default value

        //$textCount  = substr_count($sText,$portalNumber);
        $voiceCount = substr_count($sVoice,$portalNumber);

        //if ($textCount > 0) {
        //   $portalType = "text";	
        //}
        if ($voiceCount > 0) {
           $portalType = "voice";	
        }

        return $portalType;
     }	 

    //------   text is "yes" or "no"  ------------------------------
	function isYesNo($str)
     { 
        if ($str == "yes" or $str == "no") {
            $yesNo = true;
        } else {
            $yesNo = false;			
        } 
        return $yesNo;
     }

    //------ inut:string, output: array ------------------------------
    /* example: input "value,2;leadership,1;experience,9", 
                output ['value'] = 2
                       ['leadership'] = 1
                       ['experience'] = 9
    */

    function getSBArray($str)
     { 
        //$SBArray = $str;
        $a1 = explode(";",$str);
        $len = sizeof($a1);
        for ($i=0;$i<$len;$i++) {
           $a2 = $a1[$i];
           $a2 = explode(",",$a2);
           $clave = $a2[0];
           $valor = $a2[1];
           $SBArray[$clave] = $valor;
        }

        return $SBArray;
     }

    //------   text is "yes" or "no"  ------------------------------
	function isPreposition($str)
     { 
        $aPreposition = array("in", "on", "at", "about", "for", "to", "near","from");

        if (in_array($str, $aPreposition)) {
	        $isYesNo = true;  
        } else {
            $isYesNo = false;
        } 

        return $isYesNo;
     }

    //---- is flow hint: input text is at the middle of a conversation  ------
	function isFlowHint($str)
     { 
        $asterisk = substr($str,0,1);	
        if ($asterisk == "*" ) {
            $yesNo = true;
        } else {
            $yesNo = false;			
        } 
        return $yesNo;
     }


    //--------- get kaas lonk type ----------------------------------
    function getKaasLinkType($inquiry)
    {
        $leadText = substr($inquiry,0,5);

        $kaasLinkType  = 0;
        switch($leadText) {

            case '*****':
                 $kaasLinkType  = 1;   // relation pick. Table solution_relation                
            break;

            case '*11**':
                 $kaasLinkType  = 2;   // has risk pick. Table solution_option                
            break;

            case '*12**':
                 $kaasLinkType  = 2;   // has requirement pick. Table solution_option                
            break;

            case '*13**':
                 $kaasLinkType  = 2;   // has option pick. Table solution_option                
            break;
        
        }
        
        return $kaasLinkType;

    }

	 
    //------   input text is a relation pick -------------------------
	function isRelationPick($str)
     { 
        $asterisk = substr($str,0,5);	
        if ($asterisk == "*****") {
            $yesNo = true;
        } else {
            $yesNo = false;			
        } 
        return $yesNo;
     }

    //------   input text is a option pick -------------------------
	function isOptionPick($str)
     { 
        $asterisk = substr($str,0,5);	
        if ($asterisk == "**1**") {
            $yesNo = true;
        } else {
            $yesNo = false;			
        } 
        return $yesNo;
     }
	 
    //------   input text is a option has risk pick -------------------------
	function isOptionHasriskPick($str)
     { 
        $asterisk = substr($str,0,5);	
        if ($asterisk == "**11*") {
            $yesNo = true;
        } else {
            $yesNo = false;			
        } 
        return $yesNo;
     }
	 
    //------   input text is a option has risk: Continue -------------------------
	function isOptionHasriskContinue($str)
     { 
        $asterisk = substr($str,0,5);	
        if ($asterisk == "*111*") {
            $yesNo = true;
        } else {
            $yesNo = false;			
        } 
        return $yesNo;
     }

    //------   input text is a option has risk: Exit -------------------------
	function isOptionHasriskExit($str)
     { 
        $asterisk = substr($str,0,5);	
        if ($asterisk == "*110*") {
            $yesNo = true;
        } else {
            $yesNo = false;			
        } 
        return $yesNo;
     }	 
	 
    //------   input text is a option requires pick -------------------------
	function isOptionRequiresPick($str)
     { 
        $asterisk = substr($str,0,5);	
        if ($asterisk == "**12*") {
            $yesNo = true;
        } else {
            $yesNo = false;			
        } 
        return $yesNo;
     }

    //------   input text is a option has option pick -------------------------
	function isOptionHasoptionPick($str)
     { 
        $asterisk = substr($str,0,5);	
        if ($asterisk == "**13*") {
            $yesNo = true;
        } else {
            $yesNo = false;			
        } 
        return $yesNo;
     }	
	 
    //------ get in text problem  -------------------------
    function getInProblem($str)
     { 
        //*****99999999999             Problem
        //return (int)substr($str,5,10);
        $problem = 0;
        $len = strlen($str);
        if ($len > 15) {
           $problem = (int)substr($str,5,10);
        }
        return $problem;        
     }  

    //------   get in text solution ------------------
    function getInSolution($str)
     { 
        // ***210000000001*0000000002*0000000000*0000000000*2
        $solution = 0;
        $len = strlen($str);
        if ($len > 26) {
           $solution = (int)substr($str,16,10);
        }
        return $solution;

     }

    //------   get in text option ------------------
    function getInOption($str)
     { 
       //***210000000001*0000000002*0000000000*0000000000*2
        //return (int)substr($str,27,10);
        $option = 0;
        $len = strlen($str);
        if ($len > 37) {
           $option = (int)substr($str,27,10);
        }
        return $option;

     }

    //------   get in text level ------------------
    function getInLevel($str)
     { 
        // ***210000000001*0000000002*0000000000*0000000000*2
        $level = 0;
        $len = strlen($str);
        if ($len > 49) {
           $level = (int)substr($str,49,1);
        }
        return $level;
     }


    //------   get option number  -------------------------
	function getOptionNumber($str)
     { 
	    // str = "*****99999999999"
        return (int)substr($str,5,10);
     }	

    //------   get parent option number  ------------------
	function getParentOptionNumber($str)
     { 
	    // str = "*****99999999999*1*9999999999"
        return (int)substr($str,18,10);
     }	
	 

    //--- sort array by field   -----------------------
    function sortArray($records, $field, $reverse=false)
     {
        $hash = array();
   
        foreach($records as $record) {
           $hash[$record[$field]] = $record;
        }
   
        ($reverse)? krsort($hash) : ksort($hash);
   
        $records = array();
   
        foreach($hash as $record)
        {
            $records []= $record;
        }
   
         return $records;
     }



	//------ make string with left * padding ----------------
    //   default string length = 10
    //   str_pad  = "*****99999"
	function makePadString($integer1, $stringLength = 10, $strPad = "*")
     { 
        if ($integer1 < 1) {
           $integer1 = "";	
		}
        return str_pad($integer1, $stringLength, $strPad, STR_PAD_LEFT);
     }	 
 
	 
}