<?php
/*--------------------------------------------------------------------------------
 *  File          : ExtendedLink.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating extended_link table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 2.5.3
 *  Updated       : 21 July 2023
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtendedLink extends Model
{
	public    $timestamps = false;
    protected $connection = 'mysql2';
    protected $table      = 'extended_link';
    protected $primaryKey = "extendedLinkId";
    protected $modifiers  = ['extendedLinkId',
        'entityId',
        'parentTable',
        'parentId',
        'sampleChatDisplay',
        'ownerId',
        'ownership',
        'includedExtDataName',      
        'includedExtDataChatIntro',
		'updated_at', 
		'created_at', 
        'reserved',
        'lastUserId',
        'memo',
        'chatIntro',
        'voiceIntro',
        'orderid'
    ];
	
	protected $dates   = ['created_at'];
	protected $dates_c = ['created_at'];
	protected $dates_u = ['updated_at'];	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('extendedLinkId', '=', $id)->get(); 
     }


	 
	//--------------------------------------------------------------------
	public function retrieveEntityId($orgid,$parentTable,$id)
     {

        $entityId = 0;
        $rs =  ($this->where('ownerId', '=', $orgid)	
                    ->where('parentTable', '=', $parentTable )
                    ->where('parentId', '=', $id )
                    ->get())->toArray(); 
					              
        foreach($rs as $rs1) {
           $entityId = $rs1['entityId'];
        }		
		return $entityId;
     } 

    //--------------------------------------------------------------------
    public function getEntityId($orgid,$parentTable,$parentId,$rtgId)
     {

        $rs = null;
        $rs = $this->where( function($query) use($orgid,$parentTable,$parentId)  {
                       $query->where( 'ownership', '=', 2 )            // private
                             ->where('ownerId', '=', $orgid)
                             ->where('parentTable', '=', $parentTable)
                             ->where('parentId', '=', $parentId);
                     })->orWhere( function($query) use($parentTable,$parentId)  {
                       $query->where( 'ownership', '=', 0 )            // public
                             ->where('parentTable', '=', $parentTable)
                             ->where('parentId', '=', $parentId);
                     })->orWhere( function($query) use($orgid,$parentTable,$parentId)  {
                       $query->where( 'ownership', '=', 1 )            // protected
                             ->where('ownerId', '=', $orgid)           // same orgid
                             ->where('parentTable', '=', $parentTable)
                             ->where('parentId', '=', $parentId);
                     })->orWhere( function($query) use($orgid,$parentTable,$parentId,$rtgId) {
                       $query->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.rightOrgId','=','extended_link_new.ownerId')
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.leftOrgId','=',$orgid)
                             ->leftJoin('organization_association as orgAssociaton',
                                   'orgAssociation.relationTypeGroupId','=', $rtgId)
                             ->where( 'ownership', '=', 1 )            // protected
                             ->where('ownerId', '<>', $orgid)          // distinct orgid
                             ->where('parentTable', '=', $parentTable)
                             ->where('parentId', '=', $parentId);
                     })
                    ->orderBy('orderid', 'asc')
                    ->get(); 



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
