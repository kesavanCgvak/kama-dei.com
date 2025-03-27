<?php
namespace App\Http\Controllers\Api\Dashboard\Testing;

use Illuminate\Http\Request;

use App\Controllers;
class TestingController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function apiLogin(Request $req){
		//-----------------------------------
		$tmp = new \App\ApiKeyManager\ApiKeyManagerClass;
		$data = $req->all();
		return $tmp->login($data['user_id'], $data['portal_id']);
		//-----------------------------------
	}
	//---------------------------------------
	public function apiAuthenticate(Request $req){
		//-----------------------------------
		$tmp = new \App\ApiKeyManager\ApiKeyManagerClass;
		$data = $req->all();
		return $tmp->authenticate($data['user_id'], $data['api_key']);
		//-----------------------------------
		//-----------------------------------
	}
	//---------------------------------------
	public function apiActiveUsers(Request $req){
		//-----------------------------------
		$tmp = new \App\ApiKeyManager\ApiKeyManagerClass;
		$data = $req->all();
		return $tmp->active_users();
		//-----------------------------------
		//-----------------------------------
	}
	//---------------------------------------
}