<?php
namespace App\Http\Controllers\Api\Dashboard\RPA;

use Illuminate\Http\Request;

use App\Controllers;
class RPATypesController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function showPage( $orgID, $sort, $order, $perPage, $page, $field='', $value='' ){
		//-----------------------------------
		try{
			//-------------------------------
			$order = strtolower($order);
			switch($order){
				case 'asc' :
				case 'desc':{ break; }
				default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
			}
			
			if($value!=""){
				$qry = \App\RPATypes::where(function($q) use($value){
						return $q->
							where('id' , 'like', "%{$value}%")->
							orwhere('name', 'like', "%{$value}%");
					});
				$count = $qry->count();
				$data = $qry->orderBy($sort, $order)->get()->forPage($page, $perPage);
			}
			else{
				$count = \App\RPATypes::count();
				$data = \App\RPATypes::orderBy($sort, $order)->get()->forPage($page, $perPage);
			}
			

			if($count==0){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
			else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage(), 'data'=>[], 'total'=>0];
		}
		//-----------------------------------
	}
	//---------------------------------------
	public function addItem($orgID, Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'name'       => 'required',
						'show_order' => 'required|gt:3'
					],
					[
						"show_order.required" => "The order field is required",
						"show_order.gt" => "The order must be greater than 3"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			$id = \App\RPATypes::insertGetId([
				"name"       => $data['name'      ],
				"show_order" => $data['show_order']
			]);
			return ['result'=>0, 'msg'=>"", 'id'=>$id];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
	public function editItem($orgID, $id, Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'name'       => 'required',
						'show_order' => 'required|gt:3'
					],
					[
						"show_order.required" => "The order field is required",
						"show_order.gt" => "The order must be greater than 3"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			if(\App\RPATypes::find($id)==null){ return ['result'=>1, 'msg'=>"Invalid id"]; }
			//-------------------------------
			$id = \App\RPATypes::where("id", $id)->
				update([
					"name"       => $data['name'      ],
					"show_order" => $data['show_order']
				]);
			return ['result'=>0, 'msg'=>"", 'id'=>$id];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
	public function getTypes(Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			$data = \App\RPATypes::where("id", ">", 3)->orderBy('show_order', 'asc')->get();
			return ['result'=>0, 'msg'=>"", 'data'=>$data];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
	public function getStructure($bot_type_id, Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			$data = \App\RPAStructure::where("bot_type_id", $bot_type_id)->get();
			return ['result'=>0, 'msg'=>"", 'data'=>$data];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
}