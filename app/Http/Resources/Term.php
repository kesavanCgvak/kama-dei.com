<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Term extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
/**/
    public function toArray($request){
		if( is_array($this) ){
			$data = [];
			foreach($this as $tmp){
				$data[] =[
					'id'         => $tmp->termId,
					'name'       => $tmp->termName,
					'isReserved' => $tmp->termIsReserved,
					'ownership'  => $tmp->ownership,
					'ownerId'    => $tmp->ownerId,
					'ownerId'    => $tmp->ownerId,
					'lastUserId' => $tmp->lastUserId,
				];			
			}
			return $data;
		}else{
			return [
				'id'         => $this->termId,
				'name'       => $this->termName,
				'isReserved' => $this->termIsReserved,
				'ownership'  => $this->ownership,
				'ownerId'    => $this->ownerId,
				'ownerId'    => $this->ownerId,
				'lastUserId' => $this->lastUserId,
			];
		}
//		return Term::toArray($request);
    }
/**/
    public function singleData(){ 
		return ['result'=>0, 'msg'=>'', 'data'=>$this];
	}
    public function allData($data){ 
		return ['result'=>0, 'msg'=>'', 'data'=>$data];
	}
}
