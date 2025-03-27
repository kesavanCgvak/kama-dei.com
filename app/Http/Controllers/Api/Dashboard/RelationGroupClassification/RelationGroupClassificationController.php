<?php

namespace App\Http\Controllers\Api\Dashboard\RelationGroupClassification;

use Illuminate\Http\Request;
use App\RelationGroupClassification;

use Illuminate\Support\Facades\Config;

class RelationGroupClassificationController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function showPageSorted($orgID, $sort, $order, $perPage, $page){
		//---------------------------------------
		$sort = $this->validSortName( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		$count = RelationGroupClassification::myRelationGroupClassification($orgID, '', '')->count();
		$data  = RelationGroupClassification::myPageing($orgID, $perPage, $page, $sort, $order);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function showPageSortSearch($orgID, $sort, $order, $perPage, $page, $field, $value){
		//---------------------------------------
		$sort = $this->validSortName( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$field = $this->validFieldName( $field );
		if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		$count = RelationGroupClassification::myRelationGroupClassification($orgID, $field, $value)->count();
		$data  = RelationGroupClassification::myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			//-------------------------------
			$data = $request->all();
			//-------------------------------
			$tmpTmp  = RelationGroupClassification::
													where('relationGroupId', $data['relationGroupId'] )->
													count();
			if( $tmpTmp!=0 ){ return ['result'=>1, 'msg'=>'Relation Group Type already exists.']; }
			//-------------------------------
			$tmpTmp = new RelationGroupClassification;

			$tmpTmp->relationGroupId = trim($data['relationGroupId']);
			$tmpTmp->ownership       = trim($data['ownership'      ]);
			$tmpTmp->lastUserId      = trim($data['userID'         ]);
			$tmpTmp->dateCreated     = date("Y-m-d H:i:s");
			
			if($tmpTmp->relationGroupId ==''){ return ['result'=>1, 'msg'=>'Relation Group Type is empty']; }
			if($tmpTmp->lastUserId      ==''){ return ['result'=>1, 'msg'=>'User is empty']; }
			//-------------------------------
			$tmp = $tmpTmp->save();
			if($tmp){ return ['result'=>0, 'relationGroupClassficationId'=>$tmpTmp->relationGroupClassficationId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
			//-------------------------------
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			//-------------------------------
			$tmpTmp = RelationGroupClassification::find($id);
			if( is_null($tmpTmp) ){ return ['result'=>1, 'msg'=>"Relation Group Classification not found"]; }
			//-------------------------------
			$data = $request->all();

			$tmp  = RelationGroupClassification::
										where('relationGroupId', $data['relationGroupId'] )->
										where('relationGroupClassficationId', '<>', $id )->
										count();
			if( $tmp!=0 ){ return ['result'=>1, 'msg'=>'Relation Group Classification already exists.']; }
			$tmpTmp->relationGroupId = trim($data['relationGroupId']);
			$tmpTmp->ownership       = trim($data['ownership'      ]);
			$tmpTmp->lastUserId      = trim($data['userID'         ]);
			
			if($tmpTmp->relationGroupId ==''){ return ['result'=>1, 'msg'=>'Relation Group Type is empty']; }
			if($tmpTmp->lastUserId      ==''){ return ['result'=>1, 'msg'=>'User is empty']; }

			$tmp = $tmpTmp->save();
			return ['result'=>($tmp ?0 :1), 'msg'=>''];
			//-------------------------------
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$temp = RelationGroupClassification::find($id);
			if(is_null($temp) ){ return ['result'=>1, 'msg'=>"Relation Group Classification not found"]; }
			else{
				//---------------------------
				$temp = $temp->delete($id);
				//---------------------------
				return ['result'=>($temp ?0 :1), 'msg'=>''];
				//---------------------------
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	private function validSortName($fieldName){
		switch(strtolower($fieldName)){
			case 'relationgrouptype': { return "relationGroupType"; }
			
			default:{ return ''; }
		}
	}
	//---------------------------------------
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'relationgrouptype': { return "relationGroupType"; }

			default:{ return ''; }
		}
	}
	//---------------------------------------
	public function all($orgID){
		//---------------------------------------
		$count = RelationGroupClassification::myRelationGroupClassification($orgID, '', '')->count();
		$data  = RelationGroupClassification::myRelationGroupClassification($orgID, '', '')->get();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
}