<?php
/*--------------------------------------------------------------------------------
 *  File          : ExtendedLinkTranslation.php        
 *	Type          : Model
 *  Function      : Provide tranlatiuon for extended data.
 *  Developer     : Gabriel Carrillo
 *  Company       : Kamazooie Development Corporation. KDC
 *  Version       : 2.4
 *  Updated       : March 07, 2022
 *---------------------------------------------------------------------------------*/


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtendedLinkTranslation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'extended_link_translation';
	protected $primaryKey = "id";
	protected $modifiers  = ['id','extendedLinkId', 'lang','chatIntro','voiceIntro'];
	protected $dates      = ['dateCreated'];
	
	//--------------------------------------------------------------------

	


    public function getText($extLinkId,$baseLang, $targetLang)
     {

        $rs = null;
        $rs = $this->where( function($query) use($extLinkId,$targetLang)  {
                       $query->where('extendedLinkId', '=', $extLinkId)
                             ->where('lang', '=', $targetLang); 
                     })
                    ->get();


        if($rs->isEmpty()) {
          $rs = null;
          $rs = $this->where( function($query) use($extLinkId,$baseLang)  {
                       $query->where('extendedLinkId', '=', $extLinkId)
                             ->where('lang', '=', $baseLang); 
                       })
                      ->get();
        }

        return $rs;
     }

	  
	 
}
