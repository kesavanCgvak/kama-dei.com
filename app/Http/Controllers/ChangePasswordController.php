<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
class ChangePasswordController extends Controller {
	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function __construct(){
		$this->middleware('guest');
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function index(Request $request){
		return view('change_pass');
	}

	public function passChange(Request $request) {
		if($request->input('userID') == null){
			$isLogin = $request->session()->get('isLogin');
			if(!$isLogin) return response()->json(['result'=>1, 'msg'=>'You are not logged in.']);

			$user = \App\User::find($request->session()->get('userID'));
		}else{
			$user = \App\User::find($request->input('userID'));
			if($user == null) return response()->json(['result'=>1, 'msg'=>'User not found.']);
		}


		if($user->userPass != $user->hash($request->input('oldPass'))){
			return response()->json(['result'=>1, 'msg'=>'Old Password is wrong.']);
		}else{
			$user->userPass = $user->hash($request->input('newPass'));
			$user->save();
		}

		return response()->json(['result'=>0, 'msg'=>'']);
	}
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
