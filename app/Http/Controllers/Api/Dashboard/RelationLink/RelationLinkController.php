<?php

namespace App\Http\Controllers\Api\Dashboard\RelationLink;

use Illuminate\Http\Request;
use App\RelationLink;
use App\Term;
use App\Controllers;

class RelationLinkController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function show($orgID, $id){
		$data = RelationLink::findByIdprotected($id);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[]];
		}else{
			return ['result'=>0, 'msg'=>'', 'data'=>$data];
		}
	}
	public function search($orgID, $field, $value){
		//---------------------------------------
		$field = $this->validFieldName( $field );
		if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$data = RelationLink::where($field, 'like', "%{$value}%")->get();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data]; }
	}
	//---------------------------------------
	public function showAll($orgID){ return $this->showAllSorted($orgID, 'termId', 'asc'); }
	public function showAllSorted($orgID, $sort, $order){
		//---------------------------------------
		$sort = $this->validSortName( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$count = RelationLink::count();
		$data  = RelationLink::all();
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			$order = strtolower($order);
			switch($order){
				case 'asc' :{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data->sortBy($sort)->values()->all()]; }
				case 'desc':{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data->sortByDesc($sort)->values()->all()]; }
				default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
			}
		}
	}
	//---------------------------------------
	public function showPage($orgID, $perPage, $page){
		$count = RelationLink::count();
		$data  = RelationLink::myPageing($orgID, $perPage, $page, 'relationId', 'asc');
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	public function showPageSorted($orgID, $sort, $order, $perPage, $page, $ownerId=-1, $shwglblSTT=1){
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
		$count = RelationLink::myRelationLinkNew($orgID, $sort, $order, '', '', $ownerId, $shwglblSTT)->count();
		$data  = RelationLink::myPageingNew($orgID, $perPage, $page, $sort, $order, '', '', $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	public function showPageSortSearch($orgID, $sort, $order, $perPage, $page, $field, $value, $ownerId=-1, $shwglblSTT=1){
		//---------------------------------------
		$sort = $this->validSortName( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$field = $this->validFieldName( $field );
		if($field==''){ return ['result'=>1, 'msg'=>"invalid field name", 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}

		$count = RelationLink::myRelationLinkNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT)->count();
		$data  = RelationLink::myPageingNew($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$relationLink = RelationLink::find($id);
			if(is_null($relationLink) ){
				return ['result'=>1, 'msg'=>"RelationLink not found"];
			}else{
				
				$data = $request->all();
				if($data['leftRelationId' ]==0){ throw new \Exception("Invalid left knowlege record"); }
				if($data['rightRelationId']==0){ throw new \Exception("Invalid right knowlege record"); }
				
				$relationLink->leftRelationId  = trim($request->input('leftRelationId'));
				$relationLink->linkTermId      = trim($request->input('linkTermId'));
				$relationLink->rightRelationId = trim($request->input('rightRelationId'));
				$relationLink->linkOrder       = trim($request->input('linkOrder'));
				$relationLink->ownership       = trim($request->input('ownership'));
				$relationLink->ownerId         = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
				$relationLink->lastUserId      = trim($request->input('userID'    ));
				$relationLink->reserved        = trim($request->input('reserved'  ));

				if($relationLink->leftRelationId  ==''){ return ['result'=>1, 'msg'=>'Left KR is empty']; }
				if($relationLink->linkTermId      ==''){ return ['result'=>1, 'msg'=>'Link Term is empty']; }
				if($relationLink->rightRelationId ==''){ return ['result'=>1, 'msg'=>'Right KR is empty']; }
				if($relationLink->linkOrder       ==''){ return ['result'=>1, 'msg'=>'Order is empty']; }
				if($relationLink->ownership       ==''){ return ['result'=>1, 'msg'=>'Ownership is empty']; }
				if($relationLink->ownerId         ==''){ return ['result'=>1, 'msg'=>'Owner is empty']; }
				if($relationLink->lastUserId      ==''){ return ['result'=>1, 'msg'=>'Last user is empty']; }

				$relationLink->ownerId = (($relationLink->ownerId==0) ?null :$relationLink->ownerId);
				$tmp = $relationLink->save();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			/*$termName = $request->input('termName');
			$tmp      = RelationLink::where('termName','=',strtolower($termName) )->first();
			if(!is_null($tmp) ){
				if($tmp->termIsReserved==1){ return ['result'=>1, 'msg'=>'This is a reserved term']; }
				else{ return ['result'=>1, 'msg'=>'term already exists']; }
			}*/
			
			$data = $request->all();
			if($data['leftRelationId' ]==0){ throw new \Exception("Invalid left knowlege record"); }
			if($data['rightRelationId']==0){ throw new \Exception("Invalid right knowlege record"); }
			
			$relationLink = new RelationLink;
			$relationLink->leftRelationId  = trim($request->input('leftRelationId'));
			$relationLink->linkTermId      = trim($request->input('linkTermId'));
			$relationLink->rightRelationId = trim($request->input('rightRelationId'));
			//$relationLink->linkOrder       = trim($request->input('linkOrder'));
			$relationLink->linkOrder       = RelationLink::where('leftRelationId', $request->input('leftRelationId'))->count()+1;
			$relationLink->ownership       = trim($request->input('ownership'));
			$relationLink->ownerId         = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
			$relationLink->dateCreated     = date("Y-m-d H:i:s");//$request->input('dateCreated'   );
			$relationLink->lastUserId      = trim($request->input('userID'    ));
			$relationLink->reserved        = trim($request->input('reserved'  ));
			

			if($relationLink->leftRelationId  ==''){ return ['result'=>1, 'msg'=>'Left KR is empty']; }
			if($relationLink->linkTermId      ==''){ return ['result'=>1, 'msg'=>'Link Term is empty']; }
			if($relationLink->rightRelationId ==''){ return ['result'=>1, 'msg'=>'Right KR is empty']; }
			if($relationLink->linkOrder       ==''){ return ['result'=>1, 'msg'=>'Order is empty']; }
			if($relationLink->ownership       ==''){ return ['result'=>1, 'msg'=>'Ownership is empty']; }
			if($relationLink->ownerId         ==''){ return ['result'=>1, 'msg'=>'Owner is empty']; }
			if($relationLink->lastUserId      ==''){ return ['result'=>1, 'msg'=>'Last user is empty']; }

			$relationLink->ownerId = (($relationLink->ownerId==0) ?null :$relationLink->ownerId);
			$tmp = $relationLink->save();
			if($tmp){ return ['result'=>0, 'relationLinkId'=>$relationLink->relationLinkId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$relationLink = RelationLink::find($id);
			if(is_null($relationLink) ){
				return ['result'=>1, 'msg'=>"relationLink not found"];
			}else{
				if($relationLink->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this relationLink"]; }
				$tmp = $relationLink->delete($id);
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'relationlinkid'  : { return "relationLinkId";  }
			case 'leftrelationid'  : { return "leftRelationId";  }
			case 'linktermid'      : { return "linkTermId";      }
			case 'rightrelationid' : { return "rightRelationId"; }
			case 'linkorder'       : { return "linkOrder";       }
			case 'ownership'       : { return "ownership";       }
			case 'ownerid'         : { return "ownerId";         }
			case 'datecreated'     : { return "dateCreated";     }
			case 'lastuserid'      : { return "lastUserId";      }

			case 'allfields'       : { return "allFields";       }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	private function validSortName($fieldName){
		switch(strtolower($fieldName)){
			case 'relationlinkid'       : { return "relationLinkId";  }
			case 'leftrelationid'       : { return "leftRelationId";  }
			case 'linktermid'           : { return "linkTermId";      }
			case 'rightrelationid'      : { return "rightRelationId"; }
			case 'linkorder'            : { return "linkOrder";       }
			case 'ownership'            : { return "ownerShipText";   }
			case 'ownerid'              : { return "ownerId";         }
			case 'datecreated'          : { return "dateCreated";     }
			case 'lastuserid'           : { return "lastUserId";      }
			case 'ownershipcaption'     : { return "ownership";       }
			
			case 'termname'             : { return "termName";              }
			case 'rightrelationtypename': { return "rightRelationTypeName"; }
			case 'leftrelationtypename' : { return "leftRelationTypeName";  }
			case 'organizationshortname': { return "organizationShortName"; }

			case 'leftkrname'  : { return "leftKRName";  }
			case 'rightkrname' : { return "rightKRName"; }
			case 'reserved'    : { return "reserved";    }
			case 'extdatalink' : { return "extDataLink"; }

			default:{ return ''; }
		}
	}
	//---------------------------------------
	public function showTerms($orgID){
		$tmp = new RelationLink;
		$data = $tmp->getTerms($orgID);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>1, 'msg'=>'', 'data'=>$data, 'total'=>$data->count()]; }
	}
	//---------------------------------------
	public function allLinkLeft($orgID, $llkrID){
		$data = RelationLink::myRelationLink($orgID, -1, '', '')->where('leftRelationId', $llkrID)->orderBy('linkOrder');
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>1, 'msg'=>'', 'data'=>$data->get(), 'total'=>$data->count()]; }
	}
	//---------------------------------------
	public function getOwnersList($orgID){
		$data  = RelationLink::getOwnersList($orgID);
		if($data!=null){
			$tmp = array_map('strtolower',  array_column($data, 'text'));			
			array_multisort($tmp, SORT_NATURAL, $data);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function reOrder(Request $request){
		try{
			//---------------------------------------------------------------------------
			$validator = \Validator::make(
					$request->all(),
					[
						'rows' => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//---------------------------------------------------------------------------
			$data = $request->all();
			//---------------------------------------------------------------------------
			foreach($data['rows'] as $row){
				RelationLink::where('relationLinkId', $row['relationLinkId'])->update(['linkOrder'=>$row['linkOrder']]);
			}
			//---------------------------------------------------------------------------
			return ['result'=>0, 'msg'=>'Sort Success'];
			//---------------------------------------------------------------------------
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}
