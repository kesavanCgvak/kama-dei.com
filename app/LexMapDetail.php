<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LexMapDetail extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqllex';
	protected $table      = 'mapping_detail';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
	protected $fillable = ['id', 'parent_id', 'type', 'val1', 'val2', 'val3', 'tag', 'user_id', 'last'];
	//--------------------------------------------------------------------
	protected function getData($id, $type){
		return $this
			->leftJoin('kamadeikb.relation        as KRs'  , 'mapping_detail.kr_id', '=', 'KRs.relationId')
			->leftJoin('kamadeikb.term            as lTerm', 'KRs.leftTermId'      , '=', 'lTerm.termId')
			->leftJoin('kamadeikb.term            as rTerm', 'KRs.rightTermId'     , '=', 'rTerm.termId')
			->leftJoin('kamadeikb.relation_type   as rType', 'KRs.relationTypeId'  , '=', 'rType.relationTypeId')
			->where('parent_id', $id)
			->where('type', $type)
			->select(
				'mapping_detail.*',
				\DB::raw('CONCAT(lTerm.termName," ",rType.relationTypeName," ",rTerm.termName) as mappedTo')
			);
	}
}