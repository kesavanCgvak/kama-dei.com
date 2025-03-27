<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/9/10
 * Time: 下午12:34
 */

namespace App\Http\Controllers\Api\Extend\Extended_upload;

use Illuminate\Http\Request;
use App\Controllers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
class UploadFileController extends \App\Http\Controllers\Controller
{

    public function upload_action(Request $request)
    {
        $file = $request->file('file');
        $orgID =$request->input('orgID');
        $orgID=$orgID=='undefined'?0:$orgID;
        $folder = date('Ymd');
        /* //判断文件夹是否已存在
       if(!Storage::disk('public')->exists($orgID)){
           Storage::disk('public')::makeDirectory($orgID);
       }


       //判断文件夹是否已存在
       if(!Storage::disk('public')::disk($orgID)->exists($folder)){
           Storage::disk('public')::disk($orgID)::makeDirectory($folder);
       }*/
        //判断文件是否有效
        if($file->isValid()) {
            $newFileName = md5(microtime()).'.'.$file->getClientOriginalExtension();
            Storage::disk('public')->put($orgID.'/'.$folder.'/'.$newFileName, file_get_contents($file));

            //return "/api/get_upload_file/app/public/".$folder."/".$newFileName;

            $raw_success = array('IsSuccess' => 1, 'Msg' => '上传成功', 'Src' => "/api/get_upload_file/app/public/".$orgID."/".$folder."/".$newFileName);



            $res_success = json_encode($raw_success);



            header('Content-Type:application/json');//这个类型声明非常关键
            echo $res_success;
        }else{
            header('Content-Type:application/json');//这个类型声明非常关键
            $raw_fail = array('IsSuccess' => 0, 'Msg' => '上传失败','Src' =>'');
            $res_fail = json_encode($raw_fail);
            echo $res_fail;
        }
    }

}