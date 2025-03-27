<?php

/*--------------------------------------------------------------------------------
 *  File          : ValidationLanguage.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating validation_language table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development  Corporation.
 *  Version       : 2.4
 *  Updated       : 16 June 2021
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ValidationType extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'validation_type';
	protected $primaryKey = "validationTypeId";
	protected $modifiers  = ['validationLanguageId', 'validationClass', 'objectId', 'description',
	                          'validationText', 'validationTextLanguage','validationTypeId'];
	protected $dates      = ['dateCreated'];

	  
    //--------------------------------------------------------------------
    
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
 
    
	//--------------------------------------------------------------------
	public function getById($id)
     {
        $rs =  $this->where('validationTypeId', '=', $id)
                    ->get(); 
        return $rs;
     }

	//--------------------------------------------------------------------
    public function retrieveTypeName($id)
     {
        $typeName  = "";
        $rs =  $this->where('validationTypeId', '=', $id)
                    ->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){                        
               $typeName = $rs0->typeName;
            }           
        }
        return $typeName;
     }

    //--------------------------------------------------------------------
    public function ValidationTypeOption()
     {
        return $this->hasMany('App\Models\ValidationType', 'validationTypeOptionId');
     }     


}
