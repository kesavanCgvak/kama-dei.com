<?php

/*--------------------------------------------------------------------------------
 *  File          : ValidationLanguage.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating validation_language table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development  Corporation.
 *  Version       : 2.4
 *  Updated       : 30 May 2021
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ValidationLanguage extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'validation_language';
	protected $primaryKey = "validationLanguageId";
	protected $modifiers  = ['validationLanguageId', 'validationClass', 'objectId', 'description',
	                          'validationText', 'validationTextLanguage','validationTypeId'];
	protected $dates      = ['dateCreated'];

	  
    //--------------------------------------------------------------------
    /*
    public function joinById($id)
     {

        // this query finds termId in two steps: join term_translation->term, and term
        $rs =  $this->leftJoin('validation_language_detail', function($join) use ($id)
              {
                $join->on('validation_language.validationLanguageId', '=', 'validation_language_detail.parentId');
              })
              ->where('validation_language.validationLanguageId', '=', $id)
              ->get();     

        return $rs;
     }  
 
    */
	//--------------------------------------------------------------------
	public function getById($id)
     {
        $rs =  $this->where('validationLanguageId', '=', $id)
                    ->get(); 
        return $rs;
     }



	//--------------------------------------------------------------------
	public function getByClassObject($validationClass,$objectId)
     {
        $rs =  $this->where('validationClass', '=', $validationClass)
                    ->where('objectId', '=', $objectId)
                    ->get(); 
        return $rs;
     }

    //--------------------------------------------------------------------
    public function retrieveByObjectId($id)
     {
        $vlId = 0;
        $rs =  $this->where('objectId', '=', $id)
                    ->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){                        
               $vlId = $rs0->validationLanguageId;
            }           
        }

        return $vlId;
     }        

    //--------------------------------------------------------------------
    public function findByObjectId($id)
     {
        $rs =  $this->where('objectId', '=', $id)
                    ->get(); 
        return $rs;
     }     

    //--------------------------------------------------------------------
    public function ValidationType()
     {
        return $this->hasMany('App\Models\ValidationType', 'validationTypeId');
     }

}
