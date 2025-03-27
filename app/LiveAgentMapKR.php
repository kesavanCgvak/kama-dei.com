<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LiveAgentMapKR extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlliveagent';
	protected $table      = 'mapping_kr';
	protected $primaryKey = "mapping_kr_id";
	//--------------------------------------------------------------------
	protected function getData($bot_id){
		return $this
			->leftJoin('kamadeikb.relation        as KRs'  , 'mapping_kr.kr_id'  , '=', 'KRs.relationId')
			->leftJoin('kamadeikb.term            as lTerm', 'KRs.leftTermId'    , '=', 'lTerm.termId')
			->leftJoin('kamadeikb.term            as rTerm', 'KRs.rightTermId'   , '=', 'rTerm.termId')
			->leftJoin('kamadeikb.relation_type   as rType', 'KRs.relationTypeId', '=', 'rType.relationTypeId')
			->where('mappingBot_id', $bot_id)
			->select(
				'mapping_kr.*',
				\DB::raw('CONCAT(lTerm.termName," ",rType.relationTypeName," ",rTerm.termName) as mappedTo')
			)
			->orderBy('kr_order', 'asc');
	}
	//--------------------------------------------------------------------
}
