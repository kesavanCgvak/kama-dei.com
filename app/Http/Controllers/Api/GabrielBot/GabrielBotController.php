<?php   
/* GabrielBot api
 * This api is to test third party api
 * Developer:  Gabriel Carrillo
 * Version  :  2.5

 *             31 January 2021     Improved performance
 */

namespace App\Http\Controllers\Api\GabrielBot;

use Illuminate\Http\Request;
use App\Controllers;
use Illuminate\Support\Facades\Config;

class GabrielBotController extends \App\Http\Controllers\Controller{


    /*------------------------------------------------------------------
     * getBot
     * A function to test a bot
     *-----------------------------------------------------------------*/

   public function getBot(Request $request) {

    $inputText = "";
    $name = "Gabriel Carrillo";
    $server = "Staging";

    // read input parameters, POST method


    if($request->has('inputText')){ 
        $inputText   = trim($request->input('inputText' ));      
    }


	  // resturn reponse
	  return \Response::json([ 'inputText'=>$inputText, 'name'=>$name, 'server'=>$server ]);

    //----  end of function getLargestIE------------------------------------------

	}




}
