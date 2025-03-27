<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/9/2
 * Time: 下午3:14
 */

namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;
class Extended_Data_View extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'extended_data_new';
    protected $modifiers  = ['extendedSubTypeId',
        'extendedSubTypeName',
        'attributeId',
        'attributeName',
        'displayName',
        'defaultValue',
        'notNullFlag',
        'extendedEAVID',
        'valueString',
        'extendedEntityId',
        'extendedEntityName',
        'ownerId',
        'ownership',
        'attributeTypeId',
        'attributeTypeName',
        'storageType',
        'reserved'


    ];


    //--------------------------------------------------------------------
    protected function myExtended_Data_Views($extendedEntityId){
        return $this
            ->with(['organization'])
            ->where('extendedEntityId', '=', $extendedEntityId);
    }
    //--------------------------------------------------------------------
    protected function myPageing($extendedEntityId, $lang='en'){
        //----------------------------------------------------------------
        $data = null;
        //----------------------------------------------------------------

        //$data = $this->myExtended_Data_Views($extendedEntityId)->get();

        $data = $this
            ->with(['organization'])
			->leftjoin('extended_entity', 'extended_data_new.extendedEntityId', '=', 'extended_entity.extendedEntityId')
            ->where('extended_data_new.extendedEntityId', $extendedEntityId)
            ->where(function($q) use($lang){
				if($lang=='en')
					{ return $q->where('lang', $lang)->orWhere('lang', null); }
				else
					{ return $q->where('lang', $lang); }
			})
			->select(
				"extended_data_new.*",
				"extended_entity.review_by"
			)
			->orderBy('attributeId', 'asc')
			->get();
        //----------------------------------------------------------------
        if($data->isEmpty()){ return null; }
        //----------------------------------------------------------------
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            $retVal[] = $tmp;
        }
        //----------------------------------------------------------------
        return $retVal;
        //----------------------------------------------------------------
    }
    //--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
}