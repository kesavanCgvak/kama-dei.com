<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/24
 * Time: 下午12:19
 */

namespace App\Http\Controllers\Api\Extend\Extended_attribute_type;

use App\Controllers;
class test extends \App\Http\Controllers\Controller{
    public function showAll($orgID){
        echo $orgID;
    }

}