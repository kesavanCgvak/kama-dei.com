<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	//---------------------------------------
	public function testMapping(){
		return view("test_mapping");
	}
	//---------------------------------------
	public function testAWS(){
		return view("test_aws");
	}
	//---------------------------------------
}
