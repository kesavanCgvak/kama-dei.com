<?php
/*--------------------------------------------------------------------------------
 *  File          : SolutionOptionExdata.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating solution_optionexdata table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  Version       : 3.03
 *  Updated       : 28 December 2023  varible length validation
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolutionOptionExdata extends Model
{
	public    $timestamps = false;
    protected $connection = 'mysql2';
    protected $table      = 'solution_optionexdata';
    protected $primaryKey = "soedId";
    protected $modifiers  = ['soedId',   
                             'soedParentId',
                             'soedChatIntro',
                             'soedAttributeName',
                             'soedAttributeTypeName',
                             'soedValueString',
                             'soLanguage',
                             'orderid',
                             'lastUserId'
                            ];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('soedId', '=', $id)->get(); 
     }
	 
	//--------------------------------------------------------------------
	public function getChildrenData($parentId)
     {

        return  ($this->where('soedParentId', '=', $parentId)	
                     ->orderBy('orderid', 'ASC')
                     ->get())->toArray(); 			              
     } 

   //--------------------------------------------------------------------
    public function insertOptExdata($soedParentId, $soedChatIntro, $soedAttributeName,
               $soedAttributeTypeName,$soedValueString,$lang, $orderid, $userid)
     {

           if ( strlen($soedChatIntro) > 1000) {
              $soedChatIntro = substr($soedChatintro,0,1000);
           }
           if ( strlen($soedAttributeName) > 128) {
              $soedAttributeName = substr($soedAttributeName,0,128);
           }
           if ( strlen($soedValueString) > 5000) {
              $soedValueString = substr($soedValueString,0,5000);
           }

           $oSOExdata  = new SolutionOptionExdata();
           $oSOExdata->soedParentId         = $soedParentId;
           $oSOExdata->soedChatIntro        = $soedChatIntro;
           $oSOExdata->soedAttributeName    = $soedAttributeName;
           $oSOExdata->soedAttributeTypeName = $soedAttributeTypeName;           
           $oSOExdata->soedValueString      = $soedValueString;
           $oSOExdata->soLanguage           = $lang;
           $oSOExdata->orderid              = $orderid;
           $oSOExdata->lastUserId           = $userid;    
           $oSOExdata->save();        
     }

    //--------------------------------------------------------------------
    public function deleteById($id)
     {      
        return $this->where('sredId', '=', $id)->delete();    
     }  
     
    //--------------------------------------------------------------------
    public function deleteByUser($userid)
     {      
         return $this->where('lastUserId', '=', $userid)->delete();     
     }  	 
 
	 
}
