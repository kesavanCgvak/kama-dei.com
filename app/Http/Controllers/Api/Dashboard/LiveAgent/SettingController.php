<?php
namespace App\Http\Controllers\Api\Dashboard\LiveAgent;

use Illuminate\Http\Request;

use App\Controllers;
class SettingController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function showPage( $orgID, $sort, $order, $perPage, $page, $field='', $value='' ){
		//-----------------------------------------------------------------------------------------
		$sort = $this->sortFields( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		$count = \App\LiveAgentSetting::count();

		$data  = \App\LiveAgentSetting::myPageing($orgID, $perPage, $page, $sort, $order, $field, $value);

		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			if(!$request->has('org_id')){ return ['result'=>1, 'msg'=>"invalid organization"]; }
			if(!$request->has('portal_id')){ return ['result'=>1, 'msg'=>"invalid portal"]; }
			$data = $request->input();
			$data['org_id'   ] = trim($data['org_id'   ]);
			$data['portal_id'] = trim($data['portal_id']);

			if($data['org_id'   ]==''){ return ['result'=>1, 'msg'=>"invalid organization"]; }
			if($data['portal_id']==''){ return ['result'=>1, 'msg'=>"invalid portal"]; }

			if( \App\LiveAgentSetting::where('org_id', $data['org_id'])->count()!=0)
				{ return ['result'=>1, 'msg'=>'organization is duplcated']; }
/*			
			if(trim($data['personalityId'  ])==""){ return ['result'=>1, 'msg'=>"Invalid Persona"]; }
			if(\App\LiveAgentSetting::where('org_id', $data['org_id'])->count()!=0)
				{ return ['result'=>1, 'msg'=>"Organization duplicated"]; }
			
			$consumerClass = new \App\Consumer\ConsumerUserClass();
			$consumerData  = $consumerClass
				->create4lex($data['personalityId'], $data['org_id'], $data['userID'],"kaasConsumerEmail","KaasbotUser","Kaasbot");
			if($consumerData['result']==1){ return $consumerData; }

			$apiKey = \App\ApiKeyManager\ApiKeyManagerClass::login($consumerData['userID'], "2KAAS!");
*/			
			$tmp = new \App\LiveAgentSetting;
			$tmp->org_id           = $data['org_id'   ];
			$tmp->portal_id        = $data['portal_id'];
			$tmp->user_id          = $data['userID'   ];
			$tmp->last             = date("Y-m-d H:i:s");

			if($tmp->save()){ return ['result'=>0, 'msg'=>'']; }
			return ['result'=>1, 'msg'=>'Error on saving data'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	
	public function editRow($orgID, $id, Request $request){
		try{
			$tmp = \App\LiveAgentSetting::find($id);
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"Record not found"]; }

			$data = $request->input();
			$data['aws_customer_id'] = trim($data['aws_customer_id']);
			$data['org_id'] = trim($data['org_id']);
			
			if(trim($data['aws_customer_id'])==""){ return ['result'=>1, 'msg'=>"Invalid customer id"]; }
			if(trim($data['personalityId'  ])==""){ return ['result'=>1, 'msg'=>"Invalid Persona"]; }
			if(\App\LiveAgentSetting::where('org_id', $data['org_id'])->where('id', '<>', $id)->count()!=0)
				{ return ['result'=>1, 'msg'=>"Organization duplicated"]; }
			
//			$tmp->org_id          = $data['org_id'];
//			$tmp->ownerId         = $data['org_id'];
			$tmp->aws_customer_id = $data['aws_customer_id'];
			$tmp->user_id         = $data['userID'];
//			$tmp->last            = date("Y-m-d H:i:s");

			if($tmp->save()){ return ['result'=>0, 'msg'=>'']; }
			return ['result'=>1, 'msg'=>'Error on saving data'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id, Request $request){
		try{
			$tmp = \App\LiveAgentSetting::find($id);
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"Record not found"]; }
			if(\App\LiveAgentMapBots::where('ownerId', $tmp->org_id)->count()!=0){ return ['result'=>1, 'msg'=>"This setting used in mapping"]; }
			if($tmp->delete()){ return ['result'=>0, 'msg'=>'']; }
			return ['result'=>1, 'msg'=>'Error on deleting data'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getKey(){
		return 
			[ 
				'keys' =>[ 
					strtoupper( md5(time()) ),
					strtoupper( md5("AKIAJWZ7SXAHKUDIVDNQ") ),
					env('lexAccessKey', ''),
					strtoupper( md5(env('lexAccessKey', '123')) )."/".strtoupper( md5(env('lexSecretKey', '321')) ),
					env('lexSecretKey', '')
					]
			];
	}
	//---------------------------------------
	public function listOrganization($orgID){
		try{
			return ['result'=>0, 'data'=>\App\LiveAgentSetting::orgList($orgID)->orderBy('organizationShortName', 'asc')->get()];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		
	}
	//---------------------------------------
	public function getSettingData($portal_id){
		try{
			$tmp = \App\LiveAgentSetting::getPortalData($portal_id);
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"Record not found"]; }
			return ['result'=>0, 'data'=>$tmp];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getStructure(){
		try{
			$tmp = \App\LiveAgentStructure::orderBy('name', 'asc')->get();
			return ['result'=>0, 'data'=>$tmp];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	
	//---------------------------------------
	private function dataFields($field){
		switch(strtolower($field)){
			case 'organizationid'        : { $field="organizationId"; break; }
			case 'organizationshortname' : { $field="organizationShortName"; break; }
			case 'personalityname'       : { $field="personalityName"; break; }
			default:{ $field = ""; }
		}
		return $field;
	}
	//---------------------------------------
	private function sortFields($sort){
		switch(strtolower($sort)){
			case 'org_id'                : { $sort="org_id"; break; }
			case 'organizationshortname' : { $sort="organizationShortName"; break; }
			case 'personalityname'       : { $sort="personalityName"; break; }
			case 'portalname'            : { $sort="portalName"; break; }
			case 'portalcode'            : { $sort="portalCode"; break; }
				
			default:{ $sort = ""; }
		}
		return $sort;
	}
	//---------------------------------------
}