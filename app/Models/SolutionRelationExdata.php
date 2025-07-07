<?php
/*--------------------------------------------------------------------------------
 *  File          : SolutionRelationExdata.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating solution_relation_exdata table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie  Development Corporation
 *  Version       : 3.08
 *  Updated       : 15 octoberr 2024  varible length validation
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolutionRelationExdata extends Model
{
	public    $timestamps = false;
    protected $connection = 'mysql2';
    protected $table      = 'solution_relationexdata';
    protected $primaryKey = "sredId";
    protected $modifiers  = ['sredId',   
                             'sredParentId',
                             'sredChatIntro',
                             'sredAttributeName',
                             'sredAttributeTypeName',
                             'sredValueString',
                             'srLanguage',
                             'orderid',
                             'lastUserId'
                            ];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('sredId', '=', $id)->get(); 
     }
	 

	//--------------------------------------------------------------------
	public function getMetadata()
     {

     	return  $this->newQuery()->fromQuery("SHOW FIELDS FROM ".$this->getTable());

     }

	 
	//--------------------------------------------------------------------
	public function getChildrenData($parentId)
     {

        return  ($this->where('sredParentId', '=', $parentId)	
                      ->orderBy('sredId', 'ASC')
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
    public function insertRelExdata($sredParentId, $sredChatIntro, $sredAttributeName,
                $sredAttributeTypeName,$sredValueString, $lang ,$orderid, $userid)
     {

           if ( strlen($sredChatIntro) > 1000) {
              $sredChatIntro = substr($sredChatIntro,0,1000);
           }
           if ( strlen($sredAttributeName) > 128) {
              $sredAttributeName = substr($sredAttributeName,0,128);
           }
           if ( strlen($sredValueString) > 5000) {
              $sredValueString = substr($sredValueString,0,5000);
           }

           $oSRExdata  = new SolutionRelationExdata();
           $oSRExdata->sredParentId         = $sredParentId;
           $oSRExdata->sredChatIntro        = $sredChatIntro;
           $oSRExdata->sredAttributeName    = $sredAttributeName;           
           $oSRExdata->sredAttributeTypeName = $sredAttributeTypeName;
           $oSRExdata->sredValueString      = $sredValueString;
           $oSRExdata->srLanguage           = $lang;
           $oSRExdata->orderid              = $orderid;
           $oSRExdata->lastUserId           = $userid;    
           $oSRExdata->save();        
     }

    //--------------------------------------------------------------------
    public function deleteById($id)
     {      
        return $this->where('sredId', '=', $id)->delete();    
     }  
     
    //--------------------------------------------------------------------
    public function deleteByUser($userid)
     {      
         return $this->where('lastUserId', '=', $userid)
                     ->delete();     
     }  	 
 
	 
}
