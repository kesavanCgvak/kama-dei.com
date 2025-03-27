<?php   
/* msbot api
 * Handle communication with the Microsoft Bot
 * Developer:  Gabriel Carrillo
 * Version  :  3.0
 * Updated  :  04 October 2023

 */

namespace App\Http\Controllers\Api\MSBot;

use Illuminate\Http\Request;
use App\LargestIE;
use App\Controllers;
use App\Helpers\FunctionHelper;
use App\Organization;

use Illuminate\Support\Facades\Config;

class MSBotController extends \App\Http\Controllers\Controller{


    /*------------------------------------------------------------------
     * Start Conversation
     * Input
     * Output
     *-----------------------------------------------------------------*/
   public function startConversation($responseFormat=1) {

    $oFunctionHelper = new FunctionHelper();

    if (isset($_SERVER['HTTP_BEARER'])) {
        $bearer = $_SERVER['HTTP_BEARER'];
    } else {
        $bearer = "";
    }

    if (isset($_SERVER['HTTP_RESPONSEFORMAT'])) {
        $responseFormat = $_SERVER['HTTP_RESPONSEFORMAT'];
    }

    //$bearer = 'Bearer '.$bearer1;
    //         'Authorization:Bearer nq68BrLgfdg.0QZovWuRLPOyqPWW06TtakBXFp-F40KRnkfWanZmlq8'));
    $bearer = 'Bearer nq68BrLgfdg.0QZovWuRLPOyqPWW06TtakBXFp-F40KRnkfWanZmlq8';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://directline.botframework.com/v3/directline/conversations');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json',
        'Authorization:'.$bearer));

    $error  = "";

    $cr     = curl_exec($ch);

    if(curl_errno($ch)) {
        $error = 'Error: ' . curl_error($ch); 

    } else {

        $cr3 = $oFunctionHelper->extractDataArray($cr);
        $conversationId = $cr3 ->conversationId;
        $streamUrl      = $cr3 ->streamUrl;
        $token          = $cr3 ->token;

    }
    curl_close($ch);

    // prepare response
    $aResponse = array();
    $aResponse['error']    = $error;

    if ($error == "") {                     // No error

        $aResponse['conversationId'] = $conversationId;
        $aResponse['streamUrl']      = $streamUrl;
        $aResponse['token']          = $token;

    } 


     return $aResponse;
    //----  end of function starConversation  ------------------------------------------

  }

//////////////////////////////////////////////////////////////////////////////////////////
    /*------------------------------------------------------------------
     * Post Activities
     * Input
     * Output
     *-----------------------------------------------------------------*/
   public function postActivities($responseFormat,$aActivities, $conversationId) {

    $oFunctionHelper = new FunctionHelper();

    $bearer = 'Bearer nq68BrLgfdg.0QZovWuRLPOyqPWW06TtakBXFp-F40KRnkfWanZmlq8';
    $jsonActivities = json_encode($aActivities);

    $url = 'https://directline.botframework.com/v3/directline/conversations/'.$conversationId.'/activities';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonActivities);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json',
        'Authorization:'.$bearer));


    $error          = "";
    $id      ="";

    $cr = curl_exec($ch);

    if(curl_errno($ch)) {
        $error = 'Error: ' . curl_error($ch); 

    } else {

        $cr3 = $oFunctionHelper->extractDataArray($cr);

        if (isset($cr3->id)) {
            $id  = $cr3->id; 
        } else {
            $error = " Post actitivities failed";
        }


    }

    curl_close($ch);

    // prepare response
    $aResponse = array();
    $aResponse['error']    = $error;

    if ($error == "") {                     // No error

        $aResponse['id'] = $id;

    }

    return $aResponse;
   }


//////////////////////////////////////////////////////////////////////////////////////////
    /*------------------------------------------------------------------
     * get Activities
     * Input
     * Output
     *-----------------------------------------------------------------*/
   public function getActivities($conversationId, $watermark) {

    $oFunctionHelper = new FunctionHelper();

    $bearer = 'Bearer nq68BrLgfdg.0QZovWuRLPOyqPWW06TtakBXFp-F40KRnkfWanZmlq8';
    //$watermark = '-';

//    $url='https://directline.botframework.com/v3/directline/conversations/'.$conversationId.'/activities?watermark='.$watermark;

    $ch = curl_init();
    //curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_URL,
        'https://directline.botframework.com/v3/directline/conversations/'.$conversationId.'/activities?watermark='.$watermark);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json',
        'Authorization:'.$bearer));

    $error       = "";
    $watermark   = "";

    $cr = curl_exec($ch);

    if(curl_errno($ch)) {
        $error = 'Error: ' . curl_error($ch); 

    } else {

        $cr3 = json_decode($cr, true);


    }

    curl_close($ch);


    return $cr3;
   }

//////////////////////////////////////////////////////////////////////////////////////////
    /*------------------------------------------------------------------
     * get Activities
     * Input
     * Output
     *-----------------------------------------------------------------*/
   public function getNextActivities($responseFormat,$aActivities, $conversationId, $watermark) {

    $oFunctionHelper = new FunctionHelper();

    $bearer = 'Bearer nq68BrLgfdg.0QZovWuRLPOyqPWW06TtakBXFp-F40KRnkfWanZmlq8';

    $sequence = intval($watermark);
    if ($sequence >= 0) {
        $sequence++;
        $watermark = strval($sequence);
    }

    $url='https://directline.botframework.com/v3/directline/conversations/'.$conversationId.'/activities?watermark='.$watermark;;
         //https://directline.botframework.com/v3/directline/conversations/6DrVTgtKUDIENdTDQHwb3J-us/activities?watermark=9


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json',
        'Authorization:'.$bearer));

    //curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //'Content-Type:application/json',
    //'Authorization:Bearer nq68BrLgfdg.0QZovWuRLPOyqPWW06TtakBXFp-F40KRnkfWanZmlq8'));

    $error          = "";
    $needle1 = '{';
    $needle2 = '}';
    $cr2     = "";
    $id      ="";

    $cr = curl_exec($ch);

    if(curl_errno($ch)) {
        $error = 'Error: ' . curl_error($ch); 

    } else {

        $cr3 = $oFunctionHelper->extractDataArray($cr);

       $cr3 = json_decode($cr, true);


        if (isset($cr3 ->id)) {
            $id  = $cr3 ->id; 
        } else {
            $error = " Post actitivities failed";
        }


    }

    curl_close($ch);

    // prepare response
    $aResponse = array();
    $aResponse['error']    = $error;

    if ($error == "") {                     // No error

        $aResponse['id'] = $id;

    }

    return $aResponse;
   }   

/////////////////////////////////////////////////////////////////////////////////////////    


// --------- end of api  ---------------------------------------------------------------
} 
