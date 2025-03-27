<?php
namespace App\Http\Controllers\Api\Dashboard\Lex;

use Illuminate\Http\Request;

use App\Controllers;
class LexClassTestController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function setTestValue(Request $req){
		//-----------------------------------
		$data = $req->all();
		//-----------------------------------
		$req->session()->put('botName'  , $data['botName'  ]);
		$req->session()->put('botAlias' , $data['botAlias' ]);
		$req->session()->put('orgId'    , $data['orgId'    ]);
		$req->session()->put('lexUserId', $data['lexUserId']);
		$req->session()->put('inttKrId' , $data['inttKrId' ]);
		$req->session()->put('slotKrId' , $data['slotKrId' ]);
		$req->session()->put('valuKrId' , $data['valuKrId' ]);
		$req->session()->put('findKrId' , $data['findKrId' ]);
		$req->session()->put('intentNm' , $data['intentNm' ]);
		$req->session()->put('slotName' , $data['slotName' ]);
		$req->session()->put('valuName' , $data['valuName' ]);
		//-----------------------------------
	}
	//---------------------------------------
}