<?php

namespace App\Http\Controllers\Api\Dashboard\Organization;

use Illuminate\Http\Request;
use App\Organization;

use App\User;
use App\Term;
use App\Relation;
use App\RelationType;
use App\OrganizationPersonality;

use App\Controllers;

//use App\Http\Resources\User as UserResource;

class OrganizationController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function allOrganization(){
		//----------------------
		$data  = Organization::orderBy('organizationId')->get();
		$total = Organization::count();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'records not found', 'data'=>[], 'total'=>0]; }
		else{
			$tmp = new class{};
			$tmp->id   = 0;
			$tmp->name = env('BASE_ORGANIZATION');
			$newData[] = $tmp;
			for( $i=0; $i<$total; $i++){
				$tmp = new class{};
				$tmp->id   = $data[$i]['organizationId'];
				$tmp->name = $data[$i]['organizationShortName'];
				$newData[] = $tmp;
			}
			return ['result'=>0, 'msg'=>'', 'total'=>$total+1, 'data'=>$newData]; 
		}
	}
	//---------------------------------------
	public function listOrganization($orgID){ 
		//----------------------
		if($orgID<0){
			$tmpOrgID = abs($orgID);
			$data  = Organization::whereIn('organizationId', [$tmpOrgID,0])
						->orWhereNull('organizationId');
		}else{
			if($orgID!=0){ $data  = Organization::where('organizationId', '=', $orgID); }
			else{ $data  = new Organization; }
		}
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'records not found', 'data'=>[], 'total'=>0]; }
		else{
			$data = $data->orderBy('organizationShortName')
							->select(
								"organization_ep.*",
								\DB::raw("(select personalityId from kamadeikb.organization_personality where is_default = 1 and organizationId=organization_ep.organizationId) as defultPersona")
							)
							->get();
			if($orgID<=0){
				$tmp = new \StdClass;
				$tmp->organizationId = 0;
				$tmp->organizationShortName = env('BASE_ORGANIZATION');
				$tmp->defultPersona = "";
				$data->prepend($tmp);
			}

			$data = json_decode(json_encode($data), true);
			$tmp  = array_map('strtolower',  array_column($data, 'organizationShortName'));
			array_multisort($tmp, SORT_NATURAL, $data);

			return ['result'=>1, 'msg'=>'aa', 'total'=>count($data), 'data'=>$data, $orgID]; 
		}
	}
	//---------------------------------------
	public function showPageSorted( $orgID, $sort, $order, $perPage, $page ){
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
		$data  = Organization::myPageing($orgID, $perPage, $page, $sort, $order);
		$count = Organization::myOrganization($orgID, '', '')->count();

		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function showPageSortSearch( $orgID, $sort, $order, $perPage, $page, $field, $value ){
		//-----------------------------------------------------------------------------------------
		$sort = $this->sortFields( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$field = $this->dataFields( $field );
		if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		
		$data  = Organization::myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value);
		$count = Organization::myOrganization($orgID, $field, $value)->count();
		
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function isKaasActive($orgID){
		$data=Organization::find($orgID);
		return ['result'=>0, 'msg'=>'', 'data'=>$data];		
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$org = Organization::find($id);
			if(is_null($org) ){
				return ['result'=>1, 'msg'=>"Organization not found"];
			}else{
				//$OverTime = trim($request->input('OverTime'));
				//if($OverTime==''){ $OverTime=0; }
				if($request->has('organizationShortName')){
					$organizationShortName = trim($request->input('organizationShortName'));
					if($organizationShortName==''){ return ['result'=>1, 'msg'=>'Organization Short Name is empty']; }
					$org->organizationShortName = trim($request->input('organizationShortName'));
				}
				if($request->has('Descripiton')){
					$org->Descripiton = trim($request->input('Descripiton'));
				}
				if($request->has('EmailTheme')){
					$org->EmailTheme = trim($request->input('EmailTheme'));
				}
				if($request->has('EmailBody')){
					$org->EmailBody = trim($request->input('EmailBody'));
				}
				if($request->has('AutoEmail')){
					$org->AutoEmail = trim($request->input('AutoEmail'));
				}
				if($request->has('AutoOnOff')){
					$org->AutoOnOff = trim($request->input('AutoOnOff'));
					if($org->AutoOnOff==1){
						$org->send_chat_format = trim($request->input('send_chat_format'));
						$org->chat_logs_sent   = trim($request->input('chat_logs_sent'  ));
					}
				}
				if($request->has('Footer')){
					$org->Footer = trim($request->input('Footer'));
				}
				if($request->has('Billable')){
					$org->Billable = trim($request->input('Billable'));
				}
				if($request->has('RPA')){
					$org->RPA = trim($request->input('RPA'));
				}
				if($request->has('MultiLanguage')){
					$org->MultiLanguage = trim($request->input('MultiLanguage'));
				}
				if($request->has('MessageOfTheDay')){
					$org->MessageOfTheDay = trim($request->input('MessageOfTheDay'));
				}
				if($request->has('KaaS3PB')){
					$org->KaaS3PB = trim($request->input('KaaS3PB'));
				}
				if($request->has('hasLiveAgent')){
					$org->hasLiveAgent = trim($request->input('hasLiveAgent'));
				}
				$org->last = date("Y-m-d H:i:s");
				if($request->has('RAG')){
					$org->RAG = trim($request->input('RAG'));
				}
				if($request->has('mfa')){
					$org->mfa = trim($request->input('mfa'));
				}
				if($request->has('feedback')){
					if($org->feedback!=$request->input('feedback')){
						\App\Portal::where('organization_id', $id)->update(['feedback'=>$request->input('feedback')]);
						if($request->input('feedback')==0){
							\App\Portal::where('organization_id', $id)->update(['thumbsup'=>0, 'comment'=>0]);
						}
					}
					$org->feedback = trim($request->input('feedback'));
				}

				//$org->OverTime         = $OverTime;
				//$org->NeedRegister     = trim($request->input('NeedRegister'));


				if($org->save()){
					if($request->has('personalityId')){
						$personalityId = trim($request->input('personalityId'));
						$tmp = OrganizationPersonality::where('organizationId',$id)->where('is_default', 1)->first();
						if($tmp!=null ){
							$tmp->is_default = 0;
							if(!$tmp->save()){ return ['result'=>1, 'msg'=>'Error on saving data [OrganizationPersonality]']; }
						}
						$tmp = OrganizationPersonality::where('organizationId', $id)->where("personalityId", $personalityId)->first();
						if($tmp!=null ){
							$tmp->is_default = 1;
							if(!$tmp->save()){ return ['result'=>1, 'msg'=>"Error on saving data [OrganizationPersonality]"]; }
						}else{
							if($personalityId!=null){
								$orgPersonality = new OrganizationPersonality;
								$orgPersonality->organizationId = $id;
								$orgPersonality->personalityId  = $personalityId;
								$orgPersonality->is_default     = 1;
								if(!$orgPersonality->save()){return ['result'=>1,'msg'=>'Error on saving data [OrganizationPersonality]'];}
							}
						}
					}
					if($request->has('language')){
						\App\OrganizationLanguage::where('org_id', $org->organizationId)->delete();
						if($org->MultiLanguage==1){ 
							foreach($request->input('language') as $tmp){
								\App\OrganizationLanguage::insert(['org_id'=>$org->organizationId, 'language'=>$tmp]);
							}
						}else{ \App\OrganizationLanguage::insert(['org_id'=>$org->organizationId, 'language'=>'en']); }
					}
					if($org->hasLiveAgent==0){
						\App\Portal::where('organization_id', $org->organizationId)->update(['hasLiveAgent'=>0]);
						\App\LiveAgentMapBots::where('ownerId', $org->organizationId)->update(['publish_status'=>'Unpublished']);
					}
					return ['result'=>0, 'msg'=>'', 'orgID'=>$id];
				}else{ return ['result'=>1, 'msg'=>'Error on saving data [Organization]']; }
			}
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$tmpName = $request->input('organizationShortName');
			$tmp      = Organization::where('organizationShortName','=',strtolower($tmpName) )->first();
			if(!is_null($tmp) ){ return ['result'=>1, 'msg'=>'Organization already exists']; }
			
			//$OverTime = trim($request->input('OverTime'));
			//if($OverTime==''){ $OverTime=0; }
			
			$org = new Organization;
			$org->organizationShortName = trim($request->input('organizationShortName'));

			//$org->OverTime         = $OverTime;
			//$org->NeedRegister     = trim($request->input('NeedRegister'));

			$org->Descripiton     = trim($request->input('Descripiton'));
			$org->EmailTheme      = trim($request->input('EmailTheme'));
			$org->EmailBody       = trim($request->input('EmailBody'));
			$org->AutoEmail       = trim($request->input('AutoEmail'));
			$org->AutoOnOff       = trim($request->input('AutoOnOff'));
			$org->Footer          = trim($request->input('Footer'));
			$org->Billable        = trim($request->input('Billable'));
			$org->RPA             = trim($request->input('RPA'));
			$org->MultiLanguage   = trim($request->input('MultiLanguage'));
			$org->MessageOfTheDay = trim($request->input('MessageOfTheDay'));
			$org->KaaS3PB         = trim($request->input('KaaS3PB'));
			$org->hasLiveAgent    = trim($request->input('hasLiveAgent'));
			$org->RAG             = trim($request->input('RAG'));
			$org->mfa             = trim($request->input('mfa'));
			$org->feedback        = trim($request->input('feedback'));
			
			$org->last             = date("Y-m-d H:i:s");
			
			if($org->organizationShortName==''){ return ['result'=>1, 'msg'=>'Organization Short Name is empty']; }
			
			if($org->save()){
				if($request->has('language')){
					\App\OrganizationLanguage::where('org_id', $org->organizationId)->delete();
					if($org->MultiLanguage==1){ 
						foreach($request->input('language') as $tmp){
							\App\OrganizationLanguage::insert(['org_id'=>$org->organizationId, 'language'=>$tmp]);
						}
					}else{ \App\OrganizationLanguage::insert(['org_id'=>$org->organizationId, 'language'=>'en']); }
					
				}
				$personalityId = trim($request->input('personalityId'));
				$orgPersonality = new OrganizationPersonality;
				$orgPersonality->organizationId = $org->organizationId;
				$orgPersonality->personalityId  = $personalityId;
				$orgPersonality->is_default     = 1;
				if($orgPersonality->save()){ return ['result'=>0, 'msg'=>'', 'orgID'=>$orgPersonality->organizationId]; }
				else{ return ['result'=>1, 'msg'=>'Error on saving data [OrganizationPersonality]']; }
			}else{ return ['result'=>1, 'msg'=>'Error on saving data [Organization]']; }

//			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$org = Organization::find($id);
			if(is_null($org) ){ return ['result'=>1, 'msg'=>"Organization not found"]; }
			else{
				if( 
					User::where('orgID', '=', $id)->count()!=0 ||
					Term::where('ownerId', '=', $id)->count()!=0 ||
					Relation::where('ownerId', '=', $id)->count()!=0 ||
					RelationType::where('ownerId', '=', $id)->count()!=0 
				){ return ['result'=>1, 'msg'=>"This Organization is used in at least one relation or term or ..., it can not be deleted."]; }

				OrganizationPersonality::where('organizationId', $id)->delete();
				\App\OrganizationLanguage::where('org_id', $id)->delete();
				$tmp = $org->delete($id);
				$destinationPath = getcwd().'/../logos/'.$id;
				if(file_exists($destinationPath)){ 
					array_map('unlink', glob("{$destinationPath}/*.*"));
					@rmdir ($destinationPath); 
				}
				
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function setDefaultPersona(Request $request){
		try{
			$data = $request->all();
			$org = OrganizationPersonality::where('organizationId', $data['orgId'])->where("is_default", 1)->first();
			if($org!=null ){ 
				$org->is_default = 0;
				if(!$org->save()){ return ['result'=>1, 'msg'=>"Error on change default persona"]; }
			}
			$org = OrganizationPersonality::where('organizationId', $data['orgId'])->where("personalityId", $data['defaultPersona'])->first();
			if($org!=null ){ 
				$org->is_default = 1;
				if(!$org->save()){ return ['result'=>1, 'msg'=>"Error on set default persona"]; }
			}else{
				$org = new OrganizationPersonality();
				$org->organizationId = $data['orgId'];
				$org->personalityId  = $data['defaultPersona'];
				$org->is_default     = 1;
				if(!$org->save()){ return ['result'=>1, 'msg'=>"Error on create default persona"]; }
			}
			return ['result'=>0, 'msg'=>""];
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	private function dataFields($field){
		switch(strtolower($field)){
			case 'organizationid'        : { $field="organizationId"; break; }
			case 'organizationshortname' : { $field="organizationShortName"; break; }
			default:{ $field = ""; }
		}
		return $field;
	}
	//---------------------------------------
	private function sortFields($sort){
		switch(strtolower($sort)){
			case 'organizationid'        : { $sort="organizationId"; break; }
			case 'organizationshortname' : { $sort="organizationShortName"; break; }
			default:{ $sort = ""; }
		}
		return $sort;
	}
	//---------------------------------------
	public function uploadLogo(Request $request){
		$file = $request->file('organizationLogo-upload');
		$orgID = $request->input('organizationLogo-orgID');
/*
//File Name
$file->getClientOriginalName();

//Display File Extension
$file->getClientOriginalExtension();

//Display File Real Path
$file->getRealPath();

//Display File Size
$file->getSize();

//Display File Mime Type
$file->getMimeType();
*/
		//Move Uploaded File
		$destinationPath = getcwd().'/../logos/'.$orgID;
		if(!file_exists($destinationPath)){ 
			@mkdir($destinationPath); 
			@file_put_contents($destinationPath."/index.html",'<html><body onLoad="window.location=\'../../login\'"></body></html>');
		}
		if($file->move($destinationPath,$file->getClientOriginalName())){
			$org = Organization::find($orgID);
			if(is_null($org) ){
				return ['result'=>1, 'msg'=>"Organization not found"];
			}else{
				
				$organizationLogo = 
					(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http").
					"://{$_SERVER['HTTP_HOST']}/logos/{$orgID}/".$file->getClientOriginalName();

				$org->organizationLogo = $organizationLogo;
				$org->last = date("Y-m-d H:i:s");
				$org->save();
				return ['result'=>0, 'msg'=>''];
			}
		}else{
			return ['result'=>1, 'msg'=>'Error on uploding'];
		}
	}
 	//---------------------------------------
	public function allLanguage($org_id=0, Request $request){
		$languages = \App\Language::leftJoin('organization_language', function($j) use($org_id){
										$j->on('language.code', '=', 'organization_language.language')->where('org_id', $org_id);
									})
									->select(
										'language.*',
										\DB::raw('if(organization_language.org_id is null, 0, 1) as isActive')
									)
									->orderBy('language.languageId', 'asc')
									->get();
		if($org_id==0){
			foreach($languages as $language){ $language->isActive=1; }
		}
		return ['result'=>0, 'msg'=>'', 'data'=>$languages];
	}
	//---------------------------------------
}