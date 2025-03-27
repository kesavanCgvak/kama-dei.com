<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class RPAMapDetail extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlRPA';
	protected $table      = 'mapping_detail';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
	protected function getData($parent_id, $bot_id, $type_id=0){
		return $this
			->where('mapping_header_id', $bot_id)
			->select(
				'mapping_detail.*'
			);
	}
}