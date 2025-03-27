<?php
namespace App\Http\Controllers\Api\Dashboard\DataClassification;

use Illuminate\Http\Request;
use App\DataClassification;

class DataClassificationController extends \App\Http\Controllers\Controller{
	//------------------------------------------------------------------------------------------------------------
	public function showAllSorted($tableName, $orgID, $sort, $order, $perPage, $page, $field='', $value=''){
//		$sort = $this->validFieldName($sort);
//		if( $sort=='' ){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//--------------------------------------------------------------------------------------------------------
		$data  = DataClassification::myPageing($orgID, $sort, $order, $perPage, $page, $field, $value, $tableName);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data]; }
		//--------------------------------------------------------------------------------------------------------
	}
	//------------------------------------------------------------------------------------------------------------
	public function allTables(){
		$tables = \DB::connection('mysql2')->select('SHOW TABLES');
		$data = [];
		foreach($tables as $table){ $data[] = trim(strtolower($table->Tables_in_kamadeikb)); }
		return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];

	}
	//------------------------------------------------------------------------------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$data = $request->all();
			$data['tableNames'] = strtolower(trim($data['tableNames']));
			$data['fieldName']  = strtolower(trim($data['fieldName' ]));
			$data['tableField'] = "{$data['tableNames']}.{$data['fieldName']}";

			$tmp = DataClassification::where('tableField', $data['tableField'])-> where('levelId', $data['levelId'])->where('organizationId', $data['organizationId'])->
								  where('dataClassificationId', '<>', $id)->count();
			if( $tmp!=0 ){ return ['result'=>1, 'msg'=>'This Table Field already exists for this organization and level']; }
			$dataClassification = DataClassification::find($id);
			$dataClassification->tableField	         = trim($data['tableField']);
			$dataClassification->isVisible	         = trim($data['isVisible']);
			$dataClassification->isEditableByPassword = trim($data['isEditableByPassword']);
			$dataClassification->levelId	             = trim($data['levelId']);
			$dataClassification->organizationId       = trim($data['organizationId']);
			$dataClassification->lastUserId           = trim($data['userID']);

			$tmp = $dataClassification->save();
			return ['result'=>($tmp ?0 :1), 'msg'=>($tmp ?'' :'Error on update data')];
			
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//------------------------------------------------------------------------------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$data = $request->all();
			$data['tableNames'] = strtolower(trim($data['tableNames']));
			$data['fieldName']  = strtolower(trim($data['fieldName' ]));
			$data['tableField'] = "{$data['tableNames']}.{$data['fieldName']}";

			$tmp = DataClassification::where('tableField',$data['tableField'])->where('levelId',$data['levelId'])->where('organizationId',$data['organizationId'])->count();
			if( $tmp!=0 ){ return ['result'=>1, 'msg'=>'This Table Field already exists for this organization and level']; }
			$dataClassification = new DataClassification;
			$dataClassification->tableField	         = trim($data['tableField']);
			$dataClassification->isVisible	         = trim($data['isVisible']);
			$dataClassification->isEditableByPassword = trim($data['isEditableByPassword']);
			$dataClassification->levelId	             = trim($data['levelId']);
			$dataClassification->organizationId       = trim($data['organizationId']);
			$dataClassification->dateCreated          = date("Y-m-d H:i:s");
			$dataClassification->lastUserId           = trim($data['userID']);

			$tmp = $dataClassification->save();
			return ['result'=>($tmp ?0 :1), 'msg'=>($tmp ?'' :'Error on update data')];
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//------------------------------------------------------------------------------------------------------------
	public function getPass($userId){
		$sensitivePassword = \App\SensitivePassword::find($userId);
		if($sensitivePassword==null){ return ['result'=>1, 'password'=>'']; }
		else{ return ['result'=>1, 'password'=>$sensitivePassword->sensitivePassword]; }
	}
	//------------------------------------------------------------------------------------------------------------
	public function setPass($userId, Request $request){
		try{
			$data = $request->all();
			$data['pass'] = trim($data['pass']);

			$sensitivePassword = \App\SensitivePassword::find($userId);
			if($sensitivePassword==null){
				$sensitivePassword = new \App\SensitivePassword;
				$sensitivePassword->userId      = $userId;
				$sensitivePassword->dateCreated = date("Y-m-d H:i:s");
			}
			$sensitivePassword->sensitivePassword = trim($data['pass']);
			$sensitivePassword->lastUserId        = trim($data['userID']);

			$tmp = $sensitivePassword->save();
			if($tmp){
				$user    = \App\User::find($userId);
				$tmpMail = new \App\Mail\SendMail;
				\Mail::to($user->email)->send($tmpMail->dataClassificationPass($user, trim($data['pass'])));
				return ['result'=>0, 'msg'=>''];
			}else{ return ['result'=>1, 'msg'=>'Error on set password']; }
			
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//------------------------------------------------------------------------------------------------------------
	public function allFields( $table ){
		$fields = \DB::connection('mysql2')->select("SHOW COLUMNS FROM `{$table}`");
		$data = [];
		foreach($fields as $field){ $data[] = trim(strtolower($field->Field)); }
		return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];

	}
	//------------------------------------------------------------------------------------------------------------
}