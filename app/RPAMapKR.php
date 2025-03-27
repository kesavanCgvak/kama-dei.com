<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class RPAMapKR extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlRPA';
	protected $table      = 'mapping_kr';
	protected $primaryKey = "mapping_kr_id";
	//--------------------------------------------------------------------
	protected function getData($detail_id){
		return $this
			->with(['pre_handoff_message'])
			->leftJoin('kamadeikb.relation        as KRs'  , 'mapping_kr.kr_id'  , '=', 'KRs.relationId')
			->leftJoin('kamadeikb.term            as lTerm', 'KRs.leftTermId'    , '=', 'lTerm.termId')
			->leftJoin('kamadeikb.term            as rTerm', 'KRs.rightTermId'   , '=', 'rTerm.termId')
			->leftJoin('kamadeikb.relation_type   as rType', 'KRs.relationTypeId', '=', 'rType.relationTypeId')
			->where('mapping_detail_id', $detail_id)
			->select(
				'mapping_kr.*',
				\DB::raw('CONCAT(lTerm.termName," ",rType.relationTypeName," ",rTerm.termName) as mappedTo')
//				\DB::raw("(select pre_handoff_message from kamadeikb.pre_handoff where pre_handoff.lang_code='en' and pre_handoff.mapping_kr_id=mapping_kr.mapping_kr_id limit 1) as pre_handoff_messageEN")
			)
			->orderBy('kr_order', 'asc');
	}
	//--------------------------------------------------------------------
    public function pre_handoff_message(){
        return $this->hasMany('App\RPAPreHandoff', 'mapping_kr_id', 'mapping_kr_id');
    }
    public function en() {
        return $this->pre_handoff_message()->where('lang_code', 'en')->select('pre_handoff_message');
    }
	
}