<?php
namespace App\Http\Controllers\Api\Dashboard\SysAdmin;

use Illuminate\Http\Request;

use App\Controllers;
class MonitoringController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function setData(Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'email'  => 'required',
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			$monitoringINI = [];
			$ini = [];
			foreach($data as $key=>$val){
				$ini[] = "{$key} = ".(is_numeric($val) ?$val : "\"{$val}\"");
			}
			$ini[] = "lastrun = ".(time()-($data['frequency']*60));
			file_put_contents("../storage/logs/monitoring.ini", implode(PHP_EOL, $ini));
			//-------------------------------
			\Log::channel('monitoring')->info("settings updated" );

			return ['result'=>0, 'msg'=>"Updated successfully."];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
	public function clearLog(Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			file_put_contents("../storage/logs/monitoring.log", "");
			\Log::channel('monitoring')->info("log cleard" );
			return ['result'=>0, 'msg'=>"log cleard."];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
}