<?php
/*--------------------------------------------------------------------------------
 *  File          : ExtendedEntity.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating extended_entity table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 2.4.1
 *  Updated       : 14 March 2022
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtendedEntity extends Model
{
	public    $timestamps = false;
    protected $connection = 'mysql2';
    protected $table      = 'extended_entity';
    protected $primaryKey = "extendedEntityId";
    protected $modifiers  = ['extendedEntityId',
        'extendedEntityName',
        'extendedSubTypeId',
        'lastUserId',
        'ownerId',
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
         return $this->where('extendedEntityId', '=', $id)->get(); 
     }


	 
	//--------------------------------------------------------------------
	public function retrieveESTypeId($id)
     {

        $extendedSubtypeId = 0;
        $rs =  ($this->where('extendedEntityId', '=', $id)	
                    ->get())->toArray(); 
					              
        foreach($rs as $rs1) {
           $extendedSubtypeId = $rs1['extendedSubTypeId'];
        }		
		return $extendedSubtypeId;
     } 


    //--------------------------------------------------------------------
    public function getEEId($orgid,$entityId, $rtgId)
     {


        $rs =  $this->where( function($query) use($entityId) {
                       $query->where( 'ownership', '=', 0  )            // public
                             ->where('extendedEntityId', '=', $entityId);
                            
                     })->orWhere( function($query) use($orgid,$entityId)  {
                       $query->where( 'ownership', '=', 2 )            // private
                             ->where('ownerId', '=', $orgid)
                             ->where('extendedEntityId', '=', $entityId);
                         
                     })->orWhere( function($query) use($orgid,$entityId)  {
                       $query->where( 'ownership', '=', 1 )            // protected
                             ->where('ownerId', '=', $orgid)           // same orgid
                             ->where('extendedEntityId', '=', $entityId);
                              
                     })

                    ->get();   


         if (count($rs) == 0) { 

            $rs = ExtendedEntity::select('extended_entity.*')
                ->leftjoin('organization_association', 'extended_entity.ownerId', '=', 'organization_association.rightOrgId')
                       ->where('extended_entity.extendedEntityId', '=', $entityId)
                       ->where('extended_entity.ownership', '=', 1 )   
                       ->where('extended_entity.ownerId', '<>', $orgid)                  
                       ->where('organization_association.relationTypeGroupId', '=', $rtgId)
                       ->where('organization_association.leftOrgId', '=', $orgid)
                ->get();
         }



        return $rs;

     }


	//--------------------------------------------------------------------
	public function findEntityByParentId($orgid,$parentTable,$id)
     {

        return   ($this->where('ownerId', '=', $orgid)	
                    ->where('parentTable', '=', $parentTable )
                    ->where('parentId', '=', $id )
                    ->get())->toArray(); 
					              
     } 	 
	 
}
