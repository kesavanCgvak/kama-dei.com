<?php
/*--------------------------------------------------------------------------------
 *  File          : Message.php        
 *	Type          : Model
 *  Function      : Provide  functions for manipulating message table.
 *  Developer     : Gabriel Carrillo 
 *  Company       : Kamazooie Development Corporation
 *  Version       : 3.0           Full Mult-language support
 *  Updated       : 05 September 2023
 *---------------------------------------------------------------------------------*/


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Message extends Model

{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'message';
	protected $primaryKey = "messageId";
	protected $modifiers  = ['messageId','messageCode','orgId', 'messageLanguage', 'messageText'];
	protected $dates      = ['dateCreated'];

    //protected $modifiers  = ['messageId','messageCode','orgId', 'messageLanguage', 'messageText', 'messageVoice'];	
	//--------------------------------------------------------------------
	public function findById($id)
     {
         return $this->where('messageId', '=', $id)->get(); 
     }

	//--------------------------------------------------------------------
	public function getByCode($code)
     {
         return $this->where('messageCode', '=', $code)->get(); 
     }
	 
	//--------------------------------------------------------------------
	public function retrieveTextByCode($code)
     {
		 
        $messageText = "";
        $rs = $this->where('messageCode', '=', $code)->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){						  
               $messageText = $rs0->messageText;
            }			
        }
        return $messageText;
     }

    //--------------------------------------------------------------------
    public function retrieveMessageCode($baseLang,$text)
     {
         
        $messageCode = 0;
        $rs = $this->where( function($query) use($baseLang,$text)  {
                       $query->where('messageText', '=', $text)            
                             ->where('messageLanguage', '=', $baseLang); 
                    })
                    ->get(); 
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){                        
               $messageCode = $rs0->messageCode;
            }           
        }
        return $messageCode;

     }


     //---- parameters: $code, $orgid, $lang -------------------------------------------------
    public function retrieveTextByCodeOrgLang($code,$orgid,$lang)
     {
         
        $messageText = "";

        $rs = $this->where( function($query) use($code,$orgid,$lang)  {
                       $query->where('orgId', '=', $orgid)            // specific organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $lang); 
                     })->orWhere( function($query) use($code,$lang)  {
                       $query->where('orgId', '=', NULL)            // default organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $lang); 
                     })
                    ->get(); 

        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){                        
               $messageText = $rs0->messageText;
            }           
        }

        return $messageText;
     }   
	 

    //---- RETRIEVE MESSAGE------------------- -------------------------------------------------
    /*    $portalType    : text, voice
          $baseLang      : default language=en
          $lang          : decided language (target language)
    */
    public function retrieveMessage($code, $portalType,$orgid,$baseLang, $lang)
     {
        $message = "";
        $rs = $this->where( function($query) use($code,$orgid,$lang)  {
                       $query->where('orgId', '=', $orgid)            // specific organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $lang); 
                     })

                    ->orWhere( function($query) use($code,$lang)  {
                       $query->where('orgId', '=', NULL)            // default organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $lang); 
                     })

                     ->orWhere( function($query) use($code,$orgid,$baseLang)  {
                       $query->where('orgId', '=', $orgid)            // sepcific organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $baseLang); 
                     })

                    ->orWhere( function($query) use($code,$baseLang)  {
                       $query->where('orgId', '=', NULL)            // default organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $baseLang); 
                     })

                    ->get();
                    
/*  uncomment to enble message Voice
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){    
               if ($portalType == "voice") {
                  $message = $rs0->messageVoice; 
               }  else {
                  $message = $rs0->messageText;                
               } 
            }                
        }
*/
        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){    
                  $message = $rs0->messageText;                
            }                
        }        

        return $message;
     }

    //---- FIND MESSAGE------------------- ----------------------------------------------
    /*    $portalType    : text, voice
          $baseLang      : default language=en
          $lang          : decided language (target language)

          Return $rs : result set from search query
    */
    public function findMessage($code,$orgid,$baseLang, $lang)
     {

        $rs = $this->where( function($query) use($code,$orgid,$lang)  {
                       $query->where('orgId', '=', $orgid)            // specific organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $lang); 
                     })

                    ->orWhere( function($query) use($code,$lang)  {
                       $query->where('orgId', '=', NULL)            // default organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $lang); 
                     })

                     ->orWhere( function($query) use($code,$orgid,$baseLang)  {
                       $query->where('orgId', '=', $orgid)            // sepcific organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $baseLang); 
                     })

                    ->orWhere( function($query) use($code,$baseLang)  {
                       $query->where('orgId', '=', NULL)            // default organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $baseLang); 
                     })
                      ->skip(0)
                      ->take(1)
                      ->get();
                    //->get();

        return $rs;
     }


    //---------------------------------------------------------------------      


	
    //---- GET MESSAGE------------------- ----------------------------------------------
    /*    $portalType    : text, voice
          $baseLang      : default language=en
          $lang          : decided language (target language)

          Return $rs : result set from search query
    */
    public function getMessage($code,$portalType,$orgid,$baseLang, $lang, $mType)
     {

        $rs = $this->where( function($query) use($code,$orgid,$lang)  {
                       $query->where('orgId', '=', $orgid)            // specific organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $lang); 
                     })

                    ->orWhere( function($query) use($code,$lang)  {
                       $query->where('orgId', '=', NULL)            // default organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $lang); 
                     })

                     ->orWhere( function($query) use($code,$orgid,$baseLang)  {
                       $query->where('orgId', '=', $orgid)            // sepcific organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $baseLang); 
                     })

                    ->orWhere( function($query) use($code,$baseLang)  {
                       $query->where('orgId', '=', NULL)            // default organization
                             ->where('messageCode', '=', $code)
                             ->where('messageLanguage', '=', $baseLang); 
                     })

                    ->get();

        $aMsg = "";
        $mText = "";
        if ($mType == "greeting") {
           $attribute = "greeting";
        } else {
           $attribute = "standardmessage";            
        }


        if (!empty($rs)) {              // 
            foreach ($rs as $rs0){  
               $lang = $rs0->messageLanguage;   
               if ($portalType == "voice") {
                  $mText = $rs0->messageVoice; 
               }  else {
                  $mText = $rs0->messageText;                
               } 

               if ($attribute == "greeting") {
                   $mText = ucfirst($mText).". ";
               }
            } 
            $aMsg= ['attribute'=>$attribute , 'language'=>$lang , 'value'=>$mText ];               
        }

        return $aMsg;
     }


    //---------------------------------------------------------------------      
	 
}
