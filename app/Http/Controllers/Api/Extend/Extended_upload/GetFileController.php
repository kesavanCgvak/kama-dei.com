<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/9/10
 * Time: 下午2:32
 */

namespace App\Http\Controllers\Api\Extend\Extended_upload;
use App\Controllers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;
class GetFileController extends \App\Http\Controllers\Controller
{
    static public function fileStorageRoute(){


        //获取当前的url
        $realpath = str_replace('api/get_upload_file','',Request::path());

        $path = storage_path() . $realpath;



        if(!file_exists($path)){
            //报404错误
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        //输出图片

        $mime_type = mime_content_type($path);

        header('Content-type:'.$mime_type);
        echo file_get_contents($path);
        exit;
    }
}