<?php
/*--------------------------------------------------------------------------------
 *  File          : ExtendedAttribute.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating extended_attribute table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  version       : 1.4.1
 *  Updated       : 14 March 2020
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtendedAttribute extends Model
{
	public    $timestamps = false;
    protected $connection = 'mysql2';
    protected $table      = 'extended_attribute';
    protected $primaryKey = "attributeId";
    protected $modifiers  = ['attributeId',
        'attributeName',
        'displayName',
        'extendedSubTypeId',
        'attributeTypeId',
        'ownerId',
        'lastUserId',
        'ownership',
        'defaultValue',
        'notNullFlag',
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
         return $this->where('attributeId', '=', $id)->get(); 
     }

	 
	//--------------------------------------------------------------------
	public function findExtendedAttribute($orgid, $extendedSubtypeId, $rtgId)
     {

        return  ($this->where( function($query) use($extendedSubtypeId) {
                       $query->where( 'ownership', '=', 0  )            // public
                             ->where('extendedSubTypeId', '=', $extendedSubtypeId);  
                     })->orWhere( function($query) use($orgid, $extendedSubtypeId)  {
                       $query->where( 'ownership', '=', 2 )            // private
                             ->where('ownerId', '=', $orgid)
                             ->where('extendedSubTypeId', '=', $extendedSubtypeId);  
                     })->orWhere( function($query) use($orgid, $extendedSubtypeId)  {
                       $query->where('ownership', '=', 1 )            // protected
                             ->where('ownerId', '=', $orgid)           // same orgid
                             ->where('extendedSubTypeId', '=', $extendedSubtypeId);  
                     })->orWhere( function($query) use($orgid, $extendedSubtypeId, $rtgId)  {
                       $query->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.rightOrgId','=','extended_attribute.ownerId')
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.leftOrgId','=',$orgid)
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.relationTypeGroupId','=', $rtgId)
                             ->where('ownership', '=', 1 )            // protected
                             ->where('ownerId', '<>', $orgid)           // distinct orgid
                             ->where('extendedSubTypeId', '=', $extendedSubtypeId);  
                     })
                    ->get())->toArray() ; 
     } 	    
	 
}
