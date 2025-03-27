<?php
/*--------------------------------------------------------------------------------
 *  File          : SolutionFactExdata.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating solution_factexdata table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie  Development Corporation
 *  Version       : 3.03
 *  Updated       : 28 December 2023  varible length validation
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolutionFactExdata extends Model
{
	public    $timestamps = false;
    protected $connection = 'mysql2';
    protected $table      = 'solution_factexdata';
    protected $primaryKey = "sfedId";
    protected $modifiers  = ['sfedId',   
                             'sfedParentId',
                             'sfedChatIntro',
                             'sfedAttributeName',
                             'sfedAttributeTypeName',
                             'sfedValueString',
                             'sfLanguage',
                             'orderid',
                             'lastUserId'
                            ];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('sfedId', '=', $id)->get(); 
     }
	 
	 
	//--------------------------------------------------------------------
	public function getChildrenData($parentId)
     {

        return  ($this->where('sfedParentId', '=', $parentId)	
                      ->orderBy('sfedId', 'ASC')
                      ->orderBy('orderid', 'ASC')
                      ->get())->toArray(); 			              
     } 

    //--------------------------------------------------------------------
    public function hasEAV($orgid,$entityId,$attributeId)
     {

        $hasEAVRecord = 0; 

        $rs =  $this->where( function($query) use($orgid,$entityId,$attributeId) {
                       $query->where( 'ownership', '>', 0 )
                             ->where('ownerId', '=', $orgid)
                             ->where('extendedEntityId', '=', $entityId)
                             ->where('extendedAttributeId', '=', $attributeId);                           
                 })->orWhere( function($query) use($entityId,$attributeId)  {
                       $query->where( 'ownership', '=', 0  )
                             ->where('extendedEntityId', '=', $entityId)
                             ->where('extendedAttributeId', '=', $attributeId) ;
                     })
                    ->get(); 
                    
        foreach($rs as $rs1) {
           $hasEAVRecord++;
        }       
        return $hasEAVRecord;
     }

    //--------------------------------------------------------------------
    public function findEAV($orgid,$entityId,$attributeId)
     {

        $hasEAVRecord = 0; 

        return  $this->where( function($query) use($orgid,$entityId,$attributeId) {
                       $query->where( 'ownership', '>', 0 )
                             ->where('ownerId', '=', $orgid)
                             ->where('extendedEntityId', '=', $entityId)
                             ->where('extendedAttributeId', '=', $attributeId);                           
                 })->orWhere( function($query) use($entityId,$attributeId)  {
                       $query->where( 'ownership', '=', 0  )
                             ->where('extendedEntityId', '=', $entityId)
                             ->where('extendedAttributeId', '=', $attributeId) ;
                     })
                    ->get(); 
                          
     }

   //--------------------------------------------------------------------
    public function insertFactExdata($sfedParentId, $sfedChatIntro, $sfedAttributeName,
                $sfedAttributeTypeName,$sfedValueString, $lang ,$orderid, $userid)
     {

           if ( strlen($sfedChatIntro) > 1000) {
              $sfedChatIntro = substr($sfedChatintro,0,1000);
           }
           if ( strlen($sfedAttributeName) > 128) {
              $sfedAttributeName = substr($sfedAttributeName,0,128);
           }
           if ( strlen($sfedValueString) > 5000) {
              $sfedValueString = substr($sfedValueString,0,5000);
           }

           $oSFExdata  = new SolutionFactExdata();
           $oSFExdata->sfedParentId         = $sfedParentId;
           $oSFExdata->sfedChatIntro        = $sfedChatIntro;
           $oSFExdata->sfedAttributeName    = $sfedAttributeName;           
           $oSFExdata->sfedAttributeTypeName = $sfedAttributeTypeName;
           $oSFExdata->sfedValueString      = $sfedValueString;
           $oSFExdata->sfLanguage           = $lang;
           $oSFExdata->orderid              = $orderid;
           $oSFExdata->lastUserId           = $userid;    
           $oSFExdata->save();        
     }

    //--------------------------------------------------------------------
    public function deleteById($id)
     {      
        return $this->where('sfedId', '=', $id)->delete();    
     }  
     

    //--------------------------------------------------------------------
    public function deleteByUser($userid)
     {      
         return $this->where('lastUserId', '=', $userid)
                     ->delete();     
     }  	 
 
	 
}
