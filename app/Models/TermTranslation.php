<?php

/*--------------------------------------------------------------------------------
 *  File          : TermTranslation.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating term_translation table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development  Corporation.
 *  Version       : 2.3
 *  Updated       : 28 March 2021
 *                  Data filtering: private, protected, public
 *                  28 March 2021
 *                  04 April 2021
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TermTranslation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'term_translation';
	protected $primaryKey = "termTranslationId";
	protected $modifiers  = ['termTranslationId', 'termId', 'orgId', 'termLanguage',
                           	'termName', 'dateCreated', 'lastUserId'];
	protected $dates      = ['dateCreated'];

	//--------------------------------------------------------------------
	public function findTermByName($termName)
     {
        return ($this->where('termName', '=', $termName)->get())->toArray(); 
     }
	 
	//--------------------------------------------------------------------
	public function findTermByNameOwner($termName,$orgId,$rtAssociationId)
     {
        return ($this->where('termName', '=', $termName)->get())->toArray(); 
     }
	  
	//--------------------------------------------------------------------
	public function retrieveTermIdByName($termName)
     {
        $termId = 0;
        $rs =  $this->where('termName', '=', $termName)->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){						  
			   $termId = $rs0->termId;
            }			
        }
		return $termId;
     }	 
	
    //--------------------------------------------------------------------
    public function retrieveTermIdByNameOrg($termName,$orgid)
     {

        $termId = 0;
        $rs =  $this->where('termName', '=', $termName)->get(); 

        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){                        
               $termId = $rs0->termId;
            }           
        }
        return $termId;
     }  

    //--------------------------------------------------------------------
    public function retrieveFilteredTermId($orgid,$termName,$rtgId)
     {

        $PUBLIC = 0;   // public access
        $PRTCTD = 1;   // protected access
        $PRIVTE = 2;   // private access
                   
        $rs =  $this->where( function($query) use($orgid,$termName, $PRIVTE)  {
                       $query->where( 'ownership', '=', $PRIVTE )            // private
                             ->where('ownerId', '=', $orgid)
                             ->where('termName', '=', $termName); 
                     })->orWhere( function($query) use($orgid,$termName,$PRTCTD)  {
                       $query->where( 'ownership', '=', $PRTCTD )            // protected
                             ->where('ownerId', '=', $orgid)           // same orgid
                             ->where('termName', '=', $termName); 
                     })->orWhere( function($query) use($termName,$PUBLIC)  {
                       $query->where( 'ownership', '=', $PUBLIC  )            // public
                             ->where('termName', '=', $termName) ; 
                     })
                    ->get(); 

         if (count($rs) == 0) { 

            $rs = Term::select('term.*')
                ->leftjoin('organization_association', 'term.ownerId', '=', 'organization_association.rightOrgId')
                       ->where('term.termName', '=', $termName)
                       ->where('term.ownership', '=', $PRTCTD)   
                       ->where('term.ownerId', '<>', $orgid)                  
                       ->where('organization_association.relationTypeGroupId', '=', $rtgId)
                       ->where('organization_association.leftOrgId', '=', $orgid)
                ->get();


         }


        return $rs;
     } 

    //--------------------------------------------------------------------
    public function getByName($orgid,$termName,$rtgId)

     {

          return $this->where( function($query) use($orgid,$termName)  {
                       $query->where( 'ownership', '=', 2 )            // private
                             ->where('ownerId', '=', $orgid)
                             ->where('termName', '=', $termName); 
                     })->orWhere( function($query) use($orgid,$termName)  {
                       $query->where( 'ownership', '=', 1 )            // protected
                             ->where('ownerId', '=', $orgid)           // same orgid
                             ->where('termName', '=', $termName); 
                     })->orWhere( function($query) use($orgid,$termName,$rtgId)  {
                       $query->leftJoin('organization_association as orgAssociation',
                                   'orgAssociation.rightOrgId','=','term.ownerId')
                             ->leftJoin('organization_association as orgAssociation',
                                   'orgAssociation.leftOrgId','=',$orgid)
                             ->leftJoin('organization_association as orgAssociation',
                                   'orgAssociation.relationTypeGroupId','=', $rtgId)
                             ->where( 'ownership', '=', 1 )            // protected
                             ->where('ownerId', '<>', $orgid)          // distinct orgid
                             ->where('termName', '=', $termName)  ; 
                     })->orWhere( function($query) use($termName)  {
                       $query->where( 'ownership', '=', 0  )            // public
                             ->where('termName', '=', $termName) ; 
                     })
                    ->get(); 



     }  



    //--------------------------------------------------------------------
    public function retrieveTermIdLikeName($termName)
     {
        $termId = 0;
        $rs =  $this->where('termName', 'LIKE BINARY', $termName)->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){                        
               $termId = $rs0->termId;
            }           
        }
        return $termId;
     }

	//--------------------------------------------------------------------
	public function retrieveTermName($termId)
     {
        $termName = "";
        $rs =  $this->where('termId', '=', $termId)->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){						  
			   $termName = $rs0->termName;
            }			
        }
		return $termName;
     }	 
	 
    //--------------------------------------------------------------------
    public function retrieveMultiTermName($orgId,$termId,$targetLang)
     {
        $termName = "";


        $rs =  $this->where( function($query) use($orgId,$termId,$targetLang)  {
                       $query->leftJoin('term_translation as termTrans',
                                   'termTrans.termId','=','term.termId'),
                             ->leftJoin('term_translation as termTrans',  
                                   'termTrans.orgId','=','term.ownerId'),
                             ->leftJoin('term_translation as termTrans',
                                   'termTrans.termLanguage','=',$targetLang)
                             ->where('ownerId', '==', $orgId)
                             ->where('termId', '=', $termId) ;          
                     })->orWhere( function($query) use($orgId,$termId,$targetLang)  {
                       $query->leftJoin('term_translation as termTrans',
                                   'termTrans.termId','=','term.termId'),
                             ->leftJoin('term_translation as termTrans',
                                   'termTrans.termLanguage','=',$targetLang)
                             ->where('termId', '=', $termId) ; 
                     })->orWhere( function($query) use($termName)  {
                       $query->where('termId', '=', $termId); 
                     })
                    ->get();

        
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){                        
               $termName = $rs0->termName;
            }           
        }
        return $termName;
     }  

	//--------------------------------------------------------------------
	public function findById($id)
     {
        return ($this->where('termId', '=', $id)->get())->toArray(); 
     }

    //--------------------------------------------------------------------
    public function getLike($termName)
     {
        return $this->where('termName', 'LIKE', $termName."%")->get(); 
     }



}
