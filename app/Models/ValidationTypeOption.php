<?php

/*--------------------------------------------------------------------------------
 *  File          : ValidationTypeOption.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating validationTypeOption table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development  Corporation.
 *  Version       : 2.4
 *  Updated       : 16 June 2021
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ValidationTypeOption extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'validation_type_option';
	protected $primaryKey = "validationTypeOptionId";
	protected $modifiers  = ['validationTypeOptionId', 'validationTypeId', 'optionText', 'optionValue',
	                          'optionMessage', 'optionLanguage'];
	protected $dates      = ['dateCreated'];

	  
    
	//--------------------------------------------------------------------
	public function getById($id)
     {
        $rs =  $this->where('validationTypeId', '=', $id)
                    ->get(); 
        return $rs;
     }

    //--------------------------------------------------------------------
    public function getByParent($parentId)
     {
        $rs =  $this->where('validationTypeId', '=', $parentId)
                    ->get(); 
        return $rs;
     }

	//--------------------------------------------------------------------

}
