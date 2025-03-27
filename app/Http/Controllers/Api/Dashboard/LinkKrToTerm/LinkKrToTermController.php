<?php

namespace App\Http\Controllers\Api\Dashboard\LinkKrToTerm;

use Illuminate\Http\Request;
use App\Controllers;
//use App\Http\Resources\RelationType as RelationTypeResource;

class LinkKrToTermController extends \App\Http\Controllers\Controller{
	//---------------------------------------------------------------------
	public function dataTable($orgID,$sort,$order,$perpage,$page,$ownerID,$showGlobal,$defaultTrID,$defaultKbID,$field="",$value=""){
		try{
			$data = \App\LinkKrToTerm::myData($orgID, $value, $ownerID, $showGlobal);
			if($defaultTrID!=0){
				$data = $data->where('relation_term_link.termId', $defaultTrID);
			}
			if($defaultKbID!=0){
				$data = $data->where('relation_term_link.relationId', $defaultKbID);
			}
			$data = $data->orderBy($sort, $order);
			return ['result'=>0, 'msg'=>'', 'total'=>$data->count(), 'data'=>$data->get()->forPage($page, $perpage)];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage(), 'total'=>0, 'data'=>[]];
		}
	}
	//---------------------------------------------------------------------
	public function getOwners($orgID){
		try{
			$orgData = [];
			if($orgID==0){ $orgData = ['id'=>0, 'name'=>env('BASE_ORGANIZATION')]; }
			else{
				$tmp = \App\Organization::find($orgID);
				$orgData = ['id'=>$orgID, 'name'=>$tmp->organizationShortName];
			}
			$ownerList = [];
			$data = \App\LinkKrToTerm::myData($orgID, "")->orderBy("orgName", "asc")->get();
			if(! $data->isEmpty()){
				$tmp = [];
				foreach($data as $tmpData){
					if(in_array($tmpData->orgName, $tmp)===false){
						$tmp[] = $tmpData->orgName;
						$ownerList[] = ['id'=>(($tmpData->ownerId==null) ?0 :$tmpData->ownerId), 'name'=>$tmpData->orgName];
					}
				}
				if(in_array($orgData['name'], $tmp)===false){
					$ownerList[] = $orgData;
				}
			}
			return ['result'=>0, 'msg'=>'', 'data'=>$ownerList];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage(), 'data'=>[]];
		}
	}
	//---------------------------------------------------------------------
	public function getTermlink($orgID){
		try{
			$data  = \App\Term::myTerms($orgID, '', '')
					->whereRaw(
						"`termId` in (select `leftTermId` from `relation` where `rightTermId`=? and `relationTypeId`=?)",
						[
							\Config('kama_dei.static.KR_TERM_LINKS_ID' , 0),
							\Config('kama_dei.static.is_a_member_of_ID', 0)
						]
					)
					->orderBy("termName", "asc")
					->select('termId', 'termName');
/*
			$data  = \App\Term::myTerms($orgID, '', '')
					->orderBy("termName", "asc")
					->whereRaw(
						"`termTypeId` in (select `id` from `term_type` where `id`=?)",
						[\Config('kama_dei.TermType.KR_Term_Link')]
					)
					->select('termId', 'termName');
*/
			return ['result'=>0, 'msg'=>'', 'total'=>$data->count(), 'data'=>$data->get()];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage(), 'total'=>0, 'data'=>[]];
		}
	}
	//---------------------------------------------------------------------
	public function editRow($orgID, $id, Request $req){
		try{
			$validator = \Validator::make(
					$req->all(),
					[
						'relationId'   => 'required',
						'krtermLinkId' => 'required',
						'termId'       => 'required',
						'ownership'    => 'required',
						'ownerId'      => 'required',
						'reserved'     => 'required',
						'userID'       => 'required'
					],
					[
						"ownerId.required" => "The Organization not defined",
						"required" => "The :attribute not defined"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			$data = $req->all();

			$row = \App\LinkKrToTerm::find($id);
			$row->relationId   = $data['relationId'];
			$row->krtermLinkId = $data['krtermLinkId'];
			$row->termId       = $data['termId'];
			$row->ownership    = $data['ownership'];
			$row->ownerId      = $data['ownerId'];
			$row->reserved     = $data['reserved'];
			$row->lastUserid   = $data['userID'];
			$row->save();
			return ['result'=>0, 'msg'=>'OK'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------------------------------------
	public function newRow($orgID, Request $req){
		try{
			$validator = \Validator::make(
					$req->all(),
					[
						'relationId'   => 'required',
						'krtermLinkId' => 'required',
						'termId'       => 'required',
						'ownership'    => 'required',
						'ownerId'      => 'required',
						'reserved'     => 'required',
						'userID'       => 'required'
					],
					[
						"ownerId.required" => "The Organization not defined",
						"required" => "The :attribute not defined"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			$data = $req->all();

			$row = new \App\LinkKrToTerm;
			$row->relationId   = $data['relationId'];
			$row->krtermLinkId = $data['krtermLinkId'];
			$row->termId       = $data['termId'];
			$row->ownership    = $data['ownership'];
			$row->ownerId      = $data['ownerId'];
			$row->reserved     = $data['reserved'];
			$row->lastUserid   = $data['userID'];
			$row->dateCreated  = date("Y-m-d H:i:s");
			$row->save();
			return ['result'=>0, 'msg'=>'OK'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------------------------------------
	public function deleteRow($orgID, $id, Request $req){
		try{
			$row = \App\LinkKrToTerm::find($id);
			if($row==null){ return ['result'=>1, 'msg'=>'invalid record id']; }
			\App\LinkKrToTerm::where('relationTermLinkId', $id)->delete();
			return ['result'=>0, 'msg'=>'OK'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------------------------------------
}