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

class ExtendedAttributeType extends Model
{
	public    $timestamps = false;
    protected $connection = 'mysql2';
    protected $table      = 'extended_attribute_type';
    protected $primaryKey = "attributeTypeId";
    protected $modifiers  = ['attributeTypeId',
        'attributeTypeName',
        'storageType',
        'lastUserId',
        'ownerId',
        'ownership',
        'dateCreated',      
        'dateUpdated', 
        'memo', 
        'reserved'
    ];
    protected $dates_c      = ['dateCreated'];
    protected $dates_u      = ['dateUpdated'];	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('attributeTypeId', '=', $id)->get(); 
     }

	 
	//--------------------------------------------------------------------
	public function retrieveStorageType($attributeTypeId)
     {
        $storageType = "";
        $rs =  ($this->where('attributeTypeId', '=', $attributeTypeId)
                      ->get())->toArray(); 	
        foreach ($rs as $rs1) {
            $storageType = $rs1['storageType'];
        }

        return $storageType;
     } 

    //--------------------------------------------------------------------
    public function findStorageType($orgid,$attributeTypeId, $rtgId)
     {
/*
        return  ($this->where('attributeTypeId', '=', $attributeTypeId)
                      ->get())->toArray();  

*/
         return  ($this->where( function($query) use($attributeTypeId) {
                       $query->where( 'ownership', '=', 0  )            // public
                             ->where('attributeTypeId', '=', $attributeTypeId);  
                     })->orWhere( function($query) use($orgid, $attributeTypeId)  {
                       $query->where('ownership', '=', 2 )            // private
                             ->where('ownerId', '=', $orgid)
                             ->where('attributeTypeId', '=', $attributeTypeId);  
                     })->orWhere( function($query) use($orgid, $attributeTypeId)  {
                       $query->where('ownership', '=', 1 )            // protected
                             ->where('ownerId', '=', $orgid)           // same orgid
                             ->where('attributeTypeId', '=', $attributeTypeId);  
                     })->orWhere( function($query) use($orgid, $attributeTypeId, $rtgId)  {
                            $query->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.rightOrgId','=','extended_attribute_type.ownerId')
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.leftOrgId','=',$orgid)
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.relationTypeGroupId','=', $rtgId)
                             ->where('ownership', '=', 1 )            // protected onwnership
                             ->where('ownerId', '<>', $orgid)         // distinct orgid
                             ->where('attributeTypeId', '=', $attributeTypeId);  
                     })
                    ->get())->toArray() ;                      

     }  	                        
	 
}