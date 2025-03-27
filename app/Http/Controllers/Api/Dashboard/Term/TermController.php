<?php

namespace App\Http\Controllers\Api\Dashboard\Term;

use Illuminate\Http\Request;
use App\Term;
use App\Relation;
use App\RelationType;
use App\PersonalityValue;
use App\PersonalityRelationValue;

use App\Controllers;

class TermController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function show($orgID, $id){
		$data = Term::findTermByID($orgID, $id);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[]];
		}else{
			return ['result'=>0, 'msg'=>'', 'data'=>$data];
		}
	}
	public function search($orgID, $field, $value){
		//-----------------------------------------------------------------------------------------
		$field = $this->dataFields( $field );
		if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$data = Term::findTerm($orgID, $field, $value);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
		}
	}
	//---------------------------------------
	public function showAll( $orgID ){ return $this->showAllSorted($orgID, 'termId', 'asc'); }
	public function showAllSorted($orgID, $sort, $order){
		//-----------------------------------------------------------------------------------------
		$sort = $this->sortFields( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$order = strtolower($order);
		switch($order){
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
			case 'asc' :
			case 'desc':{ break; }
		}
		//-----------------------------------------------------------------------------------------
		$data  = Term::myTerms($orgID, '', '')->orderBy($sort, $order)->get();
		$total = Term::myTerms($orgID, '', '')->count();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$total, 'data'=>$data]; }
	}
	public function showAllTense($orgID, $sort, $order){
		//-----------------------------------------------------------------------------------------
		$sort = $this->sortFields( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$order = strtolower($order);
		switch($order){
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
			case 'asc' :
			case 'desc':{ break; }
		}
		//-----------------------------------------------------------------------------------------
		$data  = Term::myTerms($orgID, '', '')
					->whereRaw(
						"`termId` in (select `leftTermId` from `relation` where `rightTermId`=? and `relationTypeId`=?)",
						[
							\Config('kama_dei.static.TERM_TENSE'       , 0),
							\Config('kama_dei.static.is_a_member_of_ID', 0)
						]
					)
					->orderBy($sort, $order)
					->get();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data]; }
	}
	//---------------------------------------
	public function showAllValues($orgID, $prsID, $sort, $order){
		//-----------------------------------------------------------------------------------------
		$sort = $this->sortFields( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$order = strtolower($order);
		switch($order){
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
			case 'asc' :
			case 'desc':{ break; }
		}
		//-----------------------------------------------------------------------------------------
		$termValues = Term::where('termName', '=', 'values')->first();
		if($termValues==null){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$relationType = RelationType::where('relationTypeName', '=', 'is a member of')->first();
		if($relationType==null){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$termValuesID   = $termValues->termId;
		$relationTypeID = $relationType->relationTypeId;
		$data  = Term::myTerms($orgID, '', '')
							->with(['organization'])
							->whereIn('termId',
								function($query) use($termValuesID, $relationTypeID){
									$query->select('leftTermId')
									->from(with(new Relation)->getTable())
									->where('relationTypeId', '=', $relationTypeID)
									->where('rightTermId'   , '=', $termValuesID  );
								})
							->whereNotIn('termId',
								function($query) use($prsID){
									$query->select('personTermId')
									->from(with(new PersonalityValue)->getTable())
									->where('personalityId', '=', $prsID);
								})
							->orderBy($sort, $order)
							->get();
		//-----------------------------------------------------------------------------------------
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
		}
	}
	//---------------------------------------
	public function showPage( $orgID, $perPage, $page){
		$data  = Term::myPageing($orgID, $perPage, $page, 'termId', 'asc');
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
		}
	}
	public function showPageSorted( $orgID, $sort, $order, $perPage, $page, $ownerId=-1, $shwglblSTT=1, $showAllType=0 ){
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
		$count = Term::myTermsNew($orgID, $sort, $order, '', '', $ownerId, $shwglblSTT, $showAllType)->count();
		$data  = Term::myPageingNew($orgID, $perPage, $page, $sort, $order, '', '', $ownerId, $shwglblSTT, $showAllType);
//var_dump($data);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	public function showPageSortSearch( $orgID,$sort,$order,$perPage,$page,$field,$value,$ownerId=-1,$shwglblSTT=1,$showAllType=0 ){
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
		$count = Term::myTermsNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT, $showAllType)->count();
		$data  = Term::myPageingNew($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId, $shwglblSTT, $showAllType);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$term = Term::find($id);
			if(is_null($term) ){ return ['result'=>1, 'msg'=>"This term not found"]; }
			else{
				//---------------------------
				if($term->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't edit this term"]; }
				//---------------------------
				//---------------------------
				$term->termName       = trim($request->input('termName'      ));
				$term->termIsReserved = trim($request->input('termIsReserved'));
				$term->ownership      = trim($request->input('ownership'     ));
				$term->ownerId        = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
				if($request->has('IsSystemTermOnly')){ $term->IsSystemTermOnly = trim($request->input('IsSystemTermOnly')); }
				$term->lastUserId     = trim($request->input('userID'        ));

				if($term->termName  ==''){ return ['result'=>1, 'msg'=>'Term name is empty']; }
				if($term->ownership ==''){ return ['result'=>1, 'msg'=>'Term ownership is empty']; }
				if($term->ownerId   ==''){ return ['result'=>1, 'msg'=>'Term owner ID is empty']; }
//				if($term->termTypeId==''){ return ['result'=>1, 'msg'=>'Term Type ID is empty']; }
				if($term->lastUserId==''){ return ['result'=>1, 'msg'=>'Term last user id is empty']; }

				$term->ownerId = (($term->ownerId==0) ?null :$term->ownerId);

				if($this->isTermExists($id, $term->termName, $term->ownerId)){
					return ['result'=>1, 'msg'=>'This term exists for this owner.'];
				}

				$tmp = $term->save();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$ownerId = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
			$ownerId = (($ownerId==0) ?null :$ownerId);
			if($this->isTermExists(0, trim($request->input('termName')), $ownerId)){ return ['result'=>1, 'msg'=>'This term exists for this owner.']; }
			$term = new Term;
			$term->termName       = trim($request->input('termName'      ));
			$term->termIsReserved = trim($request->input('termIsReserved'));
			$term->ownership      = trim($request->input('ownership'     ));
			$term->ownerId        = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
//			$term->termTypeId     = trim($request->input('termTypeId'    ));
			$term->dateCreated    = date("Y-m-d H:i:s");
			$term->lastUserId     = trim($request->input('userID'        ));

			if($term->termName  ==''){ return ['result'=>1, 'msg'=>'Term name is empty']; }
			if($term->ownership ==''){ return ['result'=>1, 'msg'=>'Term ownership is empty']; }
			if($term->ownerId   ==''){ return ['result'=>1, 'msg'=>'Term owner ID is empty']; }
//			if($term->termTypeId==''){ return ['result'=>1, 'msg'=>'Term Type ID is empty']; }
			if($term->lastUserId==''){ return ['result'=>1, 'msg'=>'Term last user id is empty']; }

			$term->ownerId = (($term->ownerId==0) ?null :$term->ownerId);
			$tmp = $term->save();
			if($tmp){ return ['result'=>0, 'termId'=>$term->termId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$term = Term::find($id);
			if(is_null($term) ){
				return ['result'=>1, 'msg'=>"term not found"];
			}else{
				if($term->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this term"]; }
				if( Relation::where('leftTermId', $id)->orwhere('rightTermId', $id)->count()!=0 )
					{ return ['result'=>1, 'msg'=>"This Term is used in at least one Knowledge Record, it can not be deleted."]; }
				if( \App\RelationLink::where('linkTermId', $id)->count()!=0 )
					{ return ['result'=>1, 'msg'=>"This Term is used in at least one Knowledge Record Link, it can not be deleted."]; }
				if( \App\LinkKrToTerm::where('termId', $id)->count()!=0 )
					{ return ['result'=>1, 'msg'=>"This item is being used in a kr-term kink , and cannot be deleted."]; }
				if( \App\LinkKrToTerm::where('krtermLinkId', $id)->count()!=0 )
					{ return ['result'=>1, 'msg'=>"This item is being used in a kr-term kink , and cannot be deleted."]; }
				$tmp = $term->delete($id);
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	private function dataFields($field){
		switch(strtolower($field)){
			case 'termid'        : { $field="termId"; break; }
			case 'termname'      : { $field="termName"; break; }
			case 'termisreserved': { $field="termIsReserved"; break; }
			case 'ownership'     : { $field="ownership"; break; }
			case 'ownerid'       : { $field="ownerId"; break; }
			case 'datecreated'   : { $field="dateCreated"; break; }
			case 'lastuserid'    : { $field="lastUserId"; break; }
			default:{ $field = ""; }
		}
		return $field;
	}
	//---------------------------------------
	private function sortFields($sort){
		switch(strtolower($sort)){
			case 'termid'               : { $sort="termId"; break; }
			case 'termname'             : { $sort="termName"; break; }
			case 'termisreserved'       : { $sort="termIsReserved"; break; }
			case 'ownership'            : { $sort="ownerShipText"; break; }
			case 'ownerid'              : { $sort="ownerId"; break; }
			case 'datecreated'          : { $sort="dateCreated"; break; }
			case 'lastuserid'           : { $sort="lastUserId"; break; }
			case 'ownershipcaption'     : { $sort="ownerShipText"; break; }
			case 'organizationshortname': { $sort="organizationShortName"; break; }
			case 'termtypename'         : { $sort="termTypeName"; break; }
			case 'systemterm'           : { $sort="systemTerm"; break; }

			default:{ $sort = ""; }
		}
		return $sort;
	}
	//---------------------------------------
	public function showValues($orgID, $prID){
		$data =
			Term::
			with('organization')->
			whereNotIn('termid', function($q) use($orgID, $prID){
				$tmp = new \App\PersonalityRelationValue;
				return $q
					->select('personRelationTermId')
					->from($tmp->getTable())
					->where('personalityRelationId',$prID);
				})->
			whereIN('termid', function($q){
				$tmp = new \App\Relation;
				return $q
					->select('leftTermId')
					->from($tmp->getTable())
					->where('relationTypeId',61)
					->where('rightTermId',2080);
				});
		return ['result'=>0, 'msg'=>'', 'total'=>$data->count(), 'data'=>$data->orderBy('termName')->get()];
	}
	//---------------------------------------
	private function isTermExists ($termId, $termName, $ownerId) {
//		$data = Term::where('termName', $termName);
		$data = Term::whereRaw('BINARY termName = ?' ,[$termName] );

		if(is_null($ownerId) || $ownerId == 0){ $data = $data->whereRaw('(ownerId = 0 OR ownerId IS NULL)'); }
		else{ $data = $data->where('ownerId', $ownerId); }

		$data = $data->where('termId', '<>', $termId)->first();

		if(is_null($data)) return false;
		return true;
	}
	//---------------------------------------
	public function getTermsAroundMe($orgID, $id, $pkgLen, $direction, $ownerId=-1){
		try{
			$sort    = 'termName';
			$order   = 'asc';
			$reorder = 'desc';
			$pkgLen  = 10;
			if($id==0){ 
				switch($ownerId){
					case -1: $thisItem = Term::orderBy($sort, $order)
//								->whereRaw("`termTypeId` in (select `id` from `term_type` where `name`=?)", ['Normal'])
//								->where("IsSystemTermOnly", 0)
								->first(); break;
					case  0: $thisItem = Term::where('ownerId', null)
//								->whereRaw("`termTypeId` in (select `id` from `term_type` where `name`=?)", ['Normal'])
//								->where("IsSystemTermOnly", 0)
								->orderBy($sort, $order)->first(); break;
					default: $thisItem = Term::where('ownerId', $ownerId)
//								->whereRaw("`termTypeId` in (select `id` from `term_type` where `name`=?)", ['Normal'])
//								->where("IsSystemTermOnly", 0)
								->orderBy($sort, $order)->first(); break;
				}
			}
			else{ $thisItem = Term::myTermsByID($orgID, $id)->first(); }
			switch($ownerId){
				case -1: 
					$cnt  = Term::myTerms($orgID, '', '')
//								->whereRaw("`termTypeId` in (select `id` from `term_type` where `name`=?)", ['Normal'])
//								->where("IsSystemTermOnly", 0)
								->count();
					break;
				case  0: 
					$cnt  = Term::myTerms($orgID, '', '')
								->where('ownerId', null)
//								->whereRaw("`termTypeId` in (select `id` from `term_type` where `name`=?)", ['Normal'])
//								->where("IsSystemTermOnly", 0)
								->count();
					break;
				default: 
					$cnt  = Term::myTerms($orgID, '', '')
								->where('ownerId', $ownerId)
//								->whereRaw("`termTypeId` in (select `id` from `term_type` where `name`=?)", ['Normal'])
//								->where("IsSystemTermOnly", 0)
								->count();
					break;
			}
			$data = Term::getTermsAroundMe($orgID, $thisItem->termName, $sort, $order, $pkgLen, $ownerId);
			return ['result'=>0, 'msg'=>'', 'total'=>$cnt, 'data'=>$data];
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage(), 'total'=>0, 'data'=>[]]; }
	}
	//---------------------------------------
	public function getTerms($orgID, $id, $pkgLen, $direction){
		$sort    = 'termName';
		$order   = 'asc';
		$reorder = 'desc';
		if($id==0){ $thisItem = Term::orderBy($sort, $order)->first(); }
		else{ $thisItem = Term::myTermsByID  ($orgID, $id)->first(); }
		$prev = 1;
		$next = 1;
		if(trim(strtolower($direction))=='n'){ 
			$last = Term::orderBy($sort, $reorder)->first();
			$data = Term::myTermsByName_N($orgID, $thisItem->termName, $sort, $order, $pkgLen)->get();
			if(count($data)<$pkgLen){ $next=0; }
			$isLast = false;
			foreach($data as $tmp){ if($last->termId==$tmp->termId){ $isLast = true; } }
			if($isLast){
				$data = Term::myTermsByName_P($orgID, $last->termName, $sort, $order, $pkgLen)->get();
				$ret = [];
				foreach($data as $tmp){ array_unshift( $ret, $tmp ); }
				$data = $ret;
				$next=0;
			}
		}else{ 
			$first = Term::orderBy($sort, $order)->first();
			$data  = Term::myTermsByName_P($orgID, $thisItem->termName, $sort, $order, $pkgLen)->get();
			if(count($data)<$pkgLen){ $prev=0; }
			$isFirst = false;
			foreach($data as $tmp){ if($first->termId==$tmp->termId){ $isFirst = true; } }
			if($isFirst){
				$data = Term::myTermsByName_N($orgID, $first->termName, $sort, $order, $pkgLen)->get();
				$ret = [];
				foreach($data as $tmp){ array_unshift( $ret, $tmp ); }
				$data = $ret;
				$prev=0;
			}
		}
		$prev = (($prev==1) ?Term::myTermsByName_HaveP($orgID, $data[0             ]->termName, $sort, $order)->count() :$prev);
		$next = (($next==1) ?Term::myTermsByName_HaveN($orgID, $data[count($data)-1]->termName, $sort, $order)->count() :$next);
		$cnt  = Term::myTerms($orgID, '', '')->count();
		return ['result'=>0, 'msg'=>'', 'total'=>$cnt, 'data'=>$data, 'prev'=>$prev, 'next'=>$next];
	}
	//---------------------------------------
	public function getTermsByVal($orgID, $val, $pkgLen, $ownerId=-1){
		$sort    = 'termName';
		$order   = 'asc';
		$data = Term::myTermsByName_N($orgID, $val, $sort, $order, $pkgLen)
							->where(function($q) use($ownerId){
								if($ownerId==-1){ return $q; }
								if($ownerId==0){ return $q->where('ownerId', null); }
								return $q->where('ownerId', $ownerId);
							})->get();
		$cnt  = Term::myTerms($orgID, '', '')
							->where(function($q) use($ownerId){
								if($ownerId==-1){ return $q; }
								if($ownerId==0){ return $q->where('ownerId', null); }
								return $q->where('ownerId', $ownerId);
							})->count();
		return ['result'=>0, 'msg'=>'', 'total'=>$cnt, 'data'=>$data];
	}
	//---------------------------------------
	public function termOwnersList($orgID){
		$data  = Term::getOwnersList($orgID);
		if($data!=null){
			$tmp = array_map('strtolower', array_column($data, 'text'));
			array_multisort($tmp, SORT_NATURAL, $data);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
	}
	//---------------------------------------
	public function deleteRows(Request $req){
		try{
			$data = $req->all();

			$pass = "";
			if(isset($data['pass']) ){ $pass = trim($data['pass']); }

			$userID = 0;
			if(isset($data['userID']) && trim($data['userID'])!='' ){ $userID = trim($data['userID']); }
			else{ return ['result'=>1, 'msg'=>"Invalid user"]; }

			$IDs = [];
			if(isset($data['IDs']) && is_array($data['IDs']) ){ $IDs = $data['IDs']; }
			else{ return ['result'=>1, 'msg'=>"Invalid terms"]; }

			$user = new \App\User;
			$row = $user->where('id', $userID)
						->where('userPass', $user->hash($pass))
						->first();
			
			$haveAccess = false;
			if($row!=false){ if($row->levelID==1){ $haveAccess = true; } }
			
			foreach($IDs as $id){
				$term = Term::find($id);
				if(is_null($term)){ return ['result'=>1, 'msg'=>"TERM({$id}) not found"]; }
				if($term->termIsReserved==1 && $haveAccess==false)
					{ return ['result'=>1, 'msg'=>" You do not have authorization to delete TERM({$id}) is <b>Reserved</b>"]; }
				if( Relation::where('leftTermId', '=', $id)->orwhere('rightTermId', '=', $id)->count()!=0 )
					{ return ['result'=>1, 'msg'=>"This TERM({$id}) is used in at least one relation, it can not be deleted."]; }
			}
			foreach($IDs as $id){ 
				$term = Term::find($id);
				$term->delete($id); 
			}
			return ['result'=>0, 'msg'=>''];
		}catch(\ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}
