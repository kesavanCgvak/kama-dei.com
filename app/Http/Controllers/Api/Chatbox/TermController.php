<?php

namespace App\Http\Controllers\Api\Chatbox;

use Illuminate\Http\Request;
use App\Chatbox;
use App\Controllers;

use App\User;
use App\Organization;
use App\Term;

use Illuminate\Support\Facades\Config;

class TermController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	//---------------------------------------
	//---------------------------------------
	public function termCheck(Request $request){
		//-----------------------------------
		if(!$request->has('userid')){ return \Response::json([ 'message' => 'user [userid] not defined'], 400); }
		if(!$request->has('orgid' )){ return \Response::json([ 'message' => 'organization ID [orgid] not defined'], 400); }
		if(!$request->has('term'  )){ return \Response::json([ 'message' => 'Term [term] not defined'], 400); }
		//-----------------------------------
		$userID = trim($request->input('userid'));
		$orgID  = trim($request->input('orgid' ));
		$term   = trim($request->input('term'  ));
		if($userID==null){ return \Response::json([ 'message' => 'user [userid] is empty' ], 400); }
		if($orgID ==null){ return \Response::json([ 'message' => 'organization ID [orgid] is empty' ], 400); }
		if($term  ==null){ return \Response::json([ 'message' => 'Term  [term] is empty' ], 400); }
		//-----------------------------------

		//-----------------------------------
		$tmp = Term::whereRaw('BINARY termName = ?', [$term])
						->where(function($q) use($orgID){ 
							if($orgID=='null'){ return $q->orWhere('ownerid', null); }
							else{ return $q->where('ownerid', $orgID)->orWhere('ownerid', null); }
						})
						->count();
		//-----------------------------------
		if($tmp==0){ return \Response::json([ 'message' => 'Term not found in database'], 204); }
		return \Response::json([ 'message' => 'OK [Term in database]' ], 200);
	}
	//---------------------------------------
	//---------------------------------------
	//---------------------------------------
	public function termsList(Request $request){
		//-----------------------------------
		if(!$request->has('orgid'      )){ return \Response::json([ 'message' => 'organization ID [orgid] not defined'], 400); }
		if(!$request->has('pagination' )){ return \Response::json([ 'message' => 'Pagination [pagination] not defined'], 400); }
		//-----------------------------------
		$orgID      = strtolower(trim($request->input('orgid'     )));
		$pagination = strtolower(trim($request->input('pagination')));
		//-----------------------------------
		if($orgID     ==null){ return \Response::json([ 'message' => 'organization ID [orgid] is empty' ], 400); }
		if($pagination==null){ return \Response::json([ 'message' => 'Pagination [pagination] is empty' ], 400); }
		if($pagination!='true' && $pagination!='false'){ return \Response::json([ 'message' => "Invalid pagination:{$pagination}" ], 400); }
		//-----------------------------------

		//-----------------------------------
		$startPage = '0';
		if($request->has('startpage')){ $startPage = trim($request->input('startpage')); }
		if($startPage!='0'){ if( !filter_var($startPage, FILTER_VALIDATE_INT) ){ return \Response::json([ 'message' => "Invalid startpage:{$startPage}" ], 400); } }
		$startPage = intval($startPage);
		if($startPage<0){ return \Response::json([ 'message' => "Invalid startpage:{$startPage}" ], 400); }
		//-----------------------------------
		$perPage = '200';
		if($request->has('perpage')){ $perPage = trim($request->input('perpage')); }
		if($perPage!='0'){ if( !filter_var($perPage, FILTER_VALIDATE_INT) ){ return \Response::json([ 'message' => "Invalid perpage:{$perPage}" ], 400); } }
		$perPage = intval($perPage);
		if($perPage<0){ return \Response::json([ 'message' => "Invalid perpage:{$perPage}" ], 400); }
		//-----------------------------------
		$order = 'asc';
		if($request->has('order')){ $order = strtolower(trim($request->input('order'))); }
		if($order!='asc' && $order!='desc'){ return \Response::json([ 'message' => "Invalid order:{$order}" ], 400); }
		//-----------------------------------
		$sort = 'term';
		if($request->has('sort')){ $sort = strtolower(trim($request->input('sort'))); }
		if($sort!='term' && $sort!='created'){ return \Response::json([ 'message' => "Invalid sort:{$sort}" ], 400); }
		if($sort=='term'   ){ $sort = 'termName'; }
		if($sort=='created'){ $sort = 'dateCreated'; }
		//-----------------------------------
		$added_by = '0';
		if($request->has('added_by')){ $added_by = strtolower(trim($request->input('added_by'))); }
		if($added_by=='all'){ $added_by = '0'; }
		if($added_by!='0'){ if( !filter_var($added_by, FILTER_VALIDATE_INT) ){ return \Response::json([ 'message' => "Invalid added_by:{$added_by}" ], 400); } }
		$added_by = intval($added_by);
		if($added_by<0){ return \Response::json([ 'message' => "Invalid added_by:{$added_by}" ], 400); }
		//-----------------------------------
		$interval_from = '0000-00-00';
		if($request->has('interval_from')){ 
			$interval_from = strtolower(trim($request->input('interval_from'))); 
			$tmp = explode('-', $interval_from);
			if(count($tmp)!=3){ return \Response::json([ 'message' => "Invalid interval_from:{$interval_from}" ], 400); }
			if( 
				!filter_var($tmp[0], FILTER_VALIDATE_INT) ||
				!filter_var($tmp[1], FILTER_VALIDATE_INT) ||
				!filter_var($tmp[2], FILTER_VALIDATE_INT) 
			){ return \Response::json([ 'message' => "Invalid interval_from:{$interval_from}" ], 400); }
			$tmp[0] = intval($tmp[0]);
			$tmp[1] = intval($tmp[1]);
			$tmp[2] = intval($tmp[2]);
			if($tmp[0]<1900){ return \Response::json([ 'message' => "Invalid interval_from:{$interval_from}" ], 400); }
			if($tmp[1]<1 || $tmp[1]>12){ return \Response::json([ 'message' => "Invalid interval_from:{$interval_from}" ], 400); }
			if($tmp[2]<1 || $tmp[2]>31){ return \Response::json([ 'message' => "Invalid interval_from:{$interval_from}" ], 400); }
		}
		//-----------------------------------
		$interval_to = date('Y-m-d',strtotime('+1 day'));
		if($request->has('interval_to')){ 
			$interval_to = strtolower(trim($request->input('interval_to'))); 
			$tmp = explode('-', $interval_to);
			if(count($tmp)!=3){ return \Response::json([ 'message' => "Invalid interval_to:{$interval_to}" ], 400); }
			if( 
				!filter_var($tmp[0], FILTER_VALIDATE_INT) ||
				!filter_var($tmp[1], FILTER_VALIDATE_INT) ||
				!filter_var($tmp[2], FILTER_VALIDATE_INT) 
			){ return \Response::json([ 'message' => "Invalid interval_to:{$interval_to}" ], 400); }
			$tmp[0] = intval($tmp[0]);
			$tmp[1] = intval($tmp[1]);
			$tmp[2] = intval($tmp[2]);
			if($tmp[0]<1900){ return \Response::json([ 'message' => "Invalid interval_to:{$interval_to}" ], 400); }
			if($tmp[1]<1 || $tmp[1]>12){ return \Response::json([ 'message' => "Invalid interval_to:{$interval_to}" ], 400); }
			if($tmp[2]<1 || $tmp[2]>31){ return \Response::json([ 'message' => "Invalid interval_to:{$interval_to}" ], 400); }
		}
		//-----------------------------------
		if($orgID=='null'){ $term = Term::where('ownerId', null); }
		else{ $term = Term::where('ownerId', $orgID); }
		//-----------------------------------
		$term = $term->whereBetween('dateCreated', [$interval_from, $interval_to]);
		//-----------------------------------
		if($added_by!=0){ $term = $term->where('lastUserId', $added_by); }
		//-----------------------------------
		$term = $term->orderBy($sort, $order);
		$totalWords = $term->count();
		if($pagination=='false'){ 
			$data      = $term->get(); 
			$endsign   = 'True';
			$startPage = 0;
			$perPage   = $totalWords;
		}else{ 
			$data = $term->get()->forPage($startPage+1, $perPage); 
			$endsign = (($term->get()->forPage($startPage+2, $perPage)->count()==0) ?'True' :'False');
		}
		//-----------------------------------
		$Admin_set = [];
		$Admin_set['Language_set'] = [];
		
		if($orgID!='null'){
			$org = \App\Organization::where('organizationId', $orgID)->first();
			if($org==null){ $MultipleLanguage="OFF"; }
			else{ $MultipleLanguage = (($org->MultiLanguage==1) ?'ON' :"OFF"); }
		}else{ $MultipleLanguage="ON"; }
		$Admin_set['Language_set']['MultipleLanguage'] = $MultipleLanguage;
		
		$Admin_set['Language_set']['SYS_LIMIT_LANs'] = ['en'];
		if($orgID=='null'){
			$language = \App\Language::where('code', '<>', 'en')->get();
			if(! $language->isEmpty()){
				foreach($language as $lng){ $Admin_set['Language_set']['SYS_LIMIT_LANs'][] = $lng->code; }
			}
		}else{
			$organizationLanguage = \App\OrganizationLanguage::where('org_id', $orgID)->where('language', '<>', 'en')->get();
			if(! $organizationLanguage->isEmpty()){
				foreach($organizationLanguage as $orgLng){ $Admin_set['Language_set']['SYS_LIMIT_LANs'][] = $orgLng->language; }
			}
		}
		
		$Admin_set['LonginputLimit'] = 0;
		//-----------------------------------
		$words = [];
		foreach($data as $tmp){ $words[] = $tmp->termName; }
		return \Response::json([ 
							'orgid'=>(($orgID=='null') ?null :$orgID), 
							'pageNumber'=>$startPage, 
							'perPage'=>$perPage, 
							'totalWords'=>$totalWords, 
							'endsign'=>$endsign, 
							'words'=>$words,
							'Admin_set' =>$Admin_set
						], 200);
		//-----------------------------------
	}
	//---------------------------------------
	//---------------------------------------
	//---------------------------------------
	public function insertTerms(Request $request){
		//-----------------------------------
		if(!$request->has('orgid' )){ return \Response::json([ 'message' => 'organization ID [orgid] not defined'], 400); }
		if(!$request->has('words' )){ return \Response::json([ 'message' => 'Words [words] not defined'], 400); }
		//-----------------------------------
		$orgID  = strtolower(trim($request->input('orgid')));
		if($orgID ==null){ return \Response::json([ 'message' => 'organization ID [orgid] is empty' ], 400); }
		//-----------------------------------
		$tmpRetVal = $this->isValidOrgID( $orgID );
		if($tmpRetVal['result']==1){ return $tmpRetVal; }
		//-----------------------------------
		$words  = trim($request->input('words'));
		if($words ==null){ return \Response::json([ 'message' => 'Words [words] is empty' ], 400); }
		$words = json_decode( utf8_encode($words) );
		//-----------------------------------
		$retVal = [];
		foreach($words as $word){
			//-------------------------------
			$word = trim( $word );
			//-------------------------------
			if($orgID==0){ $isIn = Term::whereRaw('BINARY termName = ?', [$word])->where('ownership', 0)->count(); }
			else{ $isIn = Term::whereRaw('BINARY termName = ?', [$word])->where('ownership', '<>', 0)->where('ownerId', $orgID)->count(); }
			//-------------------------------
			if($isIn==0){
				//---------------------------
				$term = new Term;
				$term->termName = $word;
				$term->ownerId = (($orgID=='null') ?null :$orgID);
				$term->ownership = (($orgID=='null') ?0 :2);
				$term->dateCreated = date("Y-m-d H:i:s");

				$term->termIsReserved = 0;
				$term->lastUserId = Config::get('kama_dei.static.KAMARONID',0);;
				//---------------------------
				if($term->save()){ $retVal[] = [$word,1]; }
				else{ $retVal[] = [$word,0]; }
				//---------------------------
			}else{ $retVal[] = [$word,0]; }
			//-------------------------------
		}
		//-----------------------------------
		return \Response::json($retVal, 200);
		//-----------------------------------
	}
	//---------------------------------------
	//---------------------------------------
	//---------------------------------------
	private function isValidOrgID( $orgID ){
		try{
			if($orgID=='null'){ return ['result'=>0, 'msg'=>'OK']; }
			$tmp = Organization::find($orgID);
			if($tmp==null ){ return ['result'=>1, 'msg'=>'Invalid organization ID']; }
			return ['result'=>0, 'msg'=>'OK'];
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	//---------------------------------------
	//---------------------------------------
}
