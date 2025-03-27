<?php
/*--------------------------------------------------------------------------------
 *  File          : ExtendedEAV.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating extended_entity table.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation
 *  version       : 2.4.1
 *  Updated       : 14 August 2022
 *---------------------------------------------------------------------------------*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ExtendedEAV extends Model
{
	public    $timestamps = false;
    protected $connection = 'mysql2';
    protected $table      = 'extended_eav';
    protected $primaryKey = "extendedEAVID";
    protected $modifiers  = ['extendedEAVID',
        'valueString',
        'valueBlob',
        'valueFloat',
        'valueDate',
        'extendedEntityId',
        'extendedAttributeId',
        'ownerId',
        'ownership',
        'lastUserId',
        'dateCreated',      
        'memo',
        'dateUpdated', 
        'reserved'
    ];
    protected $dates_c      = ['dateCreated'];
    protected $dates_u      = ['dateUpdated'];	


    //--------------------------------------------------------------------
    public function findEAV($orgid,$entityId, $bLang, $tLang)
     {


         $rs =  $this->where( function($query) use($entityId, $tLang) {
                       $query->where('lang', '=', $tLang)
                             ->where('extendedEntityId', '=', $entityId);  
                     })
                    ->get();        

        
        if($rs->isEmpty()) {
          $rs = null;

         $rs =  $this->where( function($query) use($entityId, $bLang) {
                       $query->where('lang', '=', $bLang)
                             ->where('extendedEntityId', '=', $entityId);      
                     })
                    ->get(); 
        }


        return $rs;

     }


    //--------------------------------------------------------------------


	 
}
