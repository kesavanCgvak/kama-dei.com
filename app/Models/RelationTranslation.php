<?php
/*--------------------------------------------------------------------------------
 *  File          : RalationTranslation.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating relation table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation. KDC
 *  Version       : 2.3.1
 *  Updated       : 04 April 2021
 *                  19 April 2021
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationTranslation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_translation';
	protected $primaryKey = "relationTranslationId";
	protected $modifiers  = ['relationTranslationId', 'relationId', 'orgId', 'language',
                              'relationName','dateCreated','lastUserId'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('relationTraslationId', '=', $id)->get(); 
     }
	
   
    //--------------------------------------------------------------------
    public function retrieveName($relationId, $language)
     {
        $name = "";
        $rs =  $this->where('relationId', '=', $relationId)
                    ->where('language', '=', $language )    
                    ->get();          
        foreach ($rs as $rs0){                        
            $name = $rs0->relationText;
        } 
        return $name;                   
     }
      
    //--------------------------------------------------------------------
    public function getName($relationId, $language)
     {
        $rs =  $this->where('relationId', '=', $relationId)
                    ->where('language', '=', $language )    
                    ->get();          
        return $rs;                   
     }

    //--------------------------------------------------------------------
    public function getTranslation($orgid,$relationId,$targetLang)
     {

        $rs =  $this->leftJoin('relation', function($join) use ($relationId,$targetLang)
              {
                $join->on('relation.relationId', '=', 'relation_translation.relationId');
              })
              ->where('relation.relationId', '=', $relationId)
              ->where('relation_translation.language', '=', $targetLang)
            ->get();  

        return $rs;
     }      
	 
	 
}
