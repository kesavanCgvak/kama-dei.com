<?php
/*--------------------------------------------------------------------------------
 *  File          : RalationLangua.php        
 *	Type          : Model
 *  Function      : Provide optional text, short text, and translation for relations.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation. KDC
 *  Version       : 3.01
 *  Updated       : 17 may 2024
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationLanguage extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_language';
	protected $primaryKey = "relationLanguageId";
	protected $modifiers  = ['relationLanguageId','relationId', 'language_code', 'orgId', 
                            'optionalText','shortText','validationText'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('relationLanguageId', '=', $id)->get(); 
     }
	


    //---- GET TEXT EXAMPLE       -------------------------------------------
    /*    Find optional text, short text, translation
          $baseLang      : default language=en
          $lang          : decided language (target language)

          Return $rs : result set from search query
    */
    public function getText($relationId,$orgid,$bLang, $tLang)
     {
 
        $rs = null;
        // STEP 1:  specific org, target language
          $rs = $this->where('orgId', '=', $orgid)            // specific organization
                     ->where('relationId', '=', $relationId)
                     ->where('language_code', '=', $tLang)
                     ->whereNotNull('optionalText')
                     ->get();

        // STEP 2: default org, target language
        if($rs->isEmpty()) { 
          $rs = null;
          $rs = $this->where('orgId', '=', null)            // default organization
                     ->where('relationId', '=', $relationId)
                     ->where('language_code', '=', $tLang)
                     ->whereNotNull('optionalText')
                     ->get();
        }

        // STEP 3: specific org, base language
        if($rs->isEmpty()) { 
          $rs = null;
          $rs = $this->where('orgId', '=', $orgid)            // specific organization
                     ->where('relationId', '=', $relationId)
                     ->where('language_code', '=', $bLang)
                     ->whereNotNull('optionalText')
                     ->get();
        }

        // STEP 4: default org, base language
        if($rs->isEmpty()) { 
          $rs = null;
          $rs = $this->where('orgId', '=', null)            // default organization
                     ->where('relationId', '=', $relationId)
                     ->where('language_code', '=', $bLang)
                     ->whereNotNull('optionalText')
                     ->get();
        }                     

        return $rs;
     }


    //---- FIND PROBLEM TEXT      -------------------------------------------
    public function getProblemText($relationId,$orgid,$bLang, $tLang)
     {
        $rs = null;
        // STEP 1:  specific org, target language
          $rs = $this->where('orgId', '=', $orgid)            // specific organization
                     ->where('relationId', '=', $relationId)
                     ->where('language_code', '=', $tLang)
                     ->whereNotNull('optionalText')
                     ->get();

        // STEP 2: default org, target language
        if($rs->isEmpty()) { 
          $rs = null;
          $rs = $this->where('orgId', '=', null)            // default organization
                     ->where('relationId', '=', $relationId)
                     ->where('language_code', '=', $tLang)
                     ->whereNotNull('optionalText')
                     ->get();
        }

        // STEP 3: specific org, base language
        if($rs->isEmpty()) { 
          $rs = null;
          $rs = $this->where('orgId', '=', $orgid)            // specific organization
                     ->where('relationId', '=', $relationId)
                     ->where('language_code', '=', $bLang)
                     ->whereNotNull('optionalText')
                     ->get();
        }

        // STEP 4: default org, base language
        if($rs->isEmpty()) { 
          $rs = null;
          $rs = $this->where('orgId', '=', null)            // default organization
                     ->where('relationId', '=', $relationId)
                     ->where('language_code', '=', $bLang)
                     ->whereNotNull('optionalText')
                     ->get();
        }                     

        return $rs;


     }
	 

    //////////////////////////////////////////////////////////////////// 
	 
}
