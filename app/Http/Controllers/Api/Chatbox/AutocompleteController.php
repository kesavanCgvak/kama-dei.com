<?php

namespace App\Http\Controllers\Api\Chatbox;

use Illuminate\Http\Request;
use App\Controllers;
use App\Autocomplete;

class AutocompleteController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function index(Request $request, $val=''){
		$term = strtolower($request->input('term'));
		$tmpValues = Autocomplete::where('value', 'like', "{$term}%")->get();
		return $tmpValues;
		/*
		return 
			[
				['id'=> 'Upupa epops1', 'label'=> 'Eurasian Hoopoe 1', 'value'=> 'Eurasian Hoopoe 1'],
				['id'=> 'Upupa epops2', 'label'=> 'Eurasian Hoopoe 2', 'value'=> 'Eurasian Hoopoe 2']
			];
		*/
/*
		return 
			[
				'Eurasian Hoopoe 1',
				'Eurasian Hoopoe 2'
			];
*/
	}
	//---------------------------------------
}
