<?php
/*--------------------------------------------------------------------------------
 *  File          : ExtendedAttributeType.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating extended_attribute_type table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  version       : 1.4.1
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtendedSubType extends Model
{
	public    $timestamps = false;
    protected $connection = 'mysql2';
    protected $table      = 'extended_subtype';
    protected $primaryKey = "attributeTypeId";
    protected $modifiers  = ['extendedSubTypeId',
        'extendedSubTypeName',
        'extendedTypeId',
        'ownerId',
        'lastUserId',
        'ownership',
        'dateCreated', 
        'memo',      
        'dateUpdated', 
        'reserved'
    ];
    protected $dates_c      = ['dateCreated'];
    protected $dates_u      = ['dateUpdated'];	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('extendedSubTypeId', '=', $id)->get(); 
     }

	 
	//--------------------------------------------------------------------
	public function retrieveName($id)
     {
        $name = "";
        $rs =  ($this->where('extendedSubTypeId', '=', $id)
                      ->get())->toArray(); 	
        foreach ($rs as $rs1) {
            $name = $rs1['extendedSubTypeName'];
        }

        return $name;
     } 

 	                        
	 
}