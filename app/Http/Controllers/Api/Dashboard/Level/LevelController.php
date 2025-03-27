<?php

namespace App\Http\Controllers\Api\Dashboard\Level;

use Illuminate\Http\Request;
use App\Level;

use App\User;
use App\PageLevel;

use App\Controllers;
//use App\Http\Resources\User as UserResource;

class LevelController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function listLevel($orgID){
		//----------------------
/*
		if($orgID!=0){ $data  = Level::where('id', '<>', 1)->get(); }
		else{ $data  = Level::all(); }
*/
		$data  = Level::all();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'records not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data->sortBy('id')->values()->all()]; }
	}
	//---------------------------------------
	public function allLevels($orgID){
		//----------------------
		$data  = Level::all();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'records not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data->sortBy('id')->values()->all()]; }
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$level = Level::find($id);
			if(is_null($level) ){
				return ['result'=>1, 'msg'=>"level not found"];
			}else{
				$level->levelName = trim($request->input('levelName'));
//				$level->order     = trim($request->input('order'));
				$level->last      = date("Y-m-d H:i:s");//$request->input('last');
				
				if($level->levelName ==''){ return ['result'=>1, 'msg'=>'level name is empty']; }

				$tmp = $level->save();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$levelName = $request->input('levelName');
			$tmp       = Level::where('levelName','=',strtolower($levelName) )->first();
			if(!is_null($tmp) ){ return ['result'=>1, 'msg'=>'Level name already exists']; }
			$level = new Level;
			$level->levelName = trim($request->input('levelName'));
			$level->order     = 4;
			$level->last      = date("Y-m-d H:i:s");//$request->input('last');
			
			if($level->levelName ==''){ return ['result'=>1, 'msg'=>'level name is empty']; }

			$tmp = $level->save();
			return ['result'=>($tmp ?0 :1), 'msg'=>''];
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$lvl = Level::find($id);
			if(is_null($lvl) ){ return ['result'=>1, 'msg'=>"Level not found"]; }
			else{
				if( 
					User::where('levelID', '=', $id)->count()!=0 ||
					PageLevel::where('levelID', '=', $id)->count()!=0 
				){ return ['result'=>1, 'msg'=>"This Level is used in at least one user or pagelevel , it can not be deleted."]; }
				$tmp = $lvl->delete($id);
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}