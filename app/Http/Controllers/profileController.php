<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
class profileController extends Controller {
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function __construct()
	{
		$this->middleware('guest');
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function index(Request $request, $username, $menu=''){
		$isLogin = $request->session()->get('isLogin');
		if($isLogin==false){ return redirect('/login'); }
		else{
			switch($menu){
				case 'orders':{
					$selectedMenu = 'menu_2';
					$title        = 'Orders';
					break;
				}
				case 'favorites':{
					$selectedMenu = 'menu_3';
					$title        = 'Favorites';
					break;
				}
				default:{
					$selectedMenu = 'menu_1';
					$title        = 'Profile';
					break;
				}
			}
			return view('profile', ['selectedMenu' => $selectedMenu, 'title'=>$title, 'username'=>$username, 'session'=>$request->session()]); 
		}
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------

//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
}
