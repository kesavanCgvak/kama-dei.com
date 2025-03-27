<?php
   
namespace App\Console\Commands;
   
use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class uploadDumpLogs extends Command{
    protected $signature   = 'uploadDumpLogs:cron';
    protected $description = 'upload kamalogs db to amazonaws';

	public function __construct(){
        parent::__construct();
    }
    public function handle(){
		try{
			ini_set('memory_limit', '-1');
			//--------------------------------------------------------------------------------------
			$conn = mysqli_connect( 'localhost', "kamadeikb_user", 'IdgfdvIg24cI9OA9', 'kamalogs' );
			mysqli_query($conn, "SET NAMES 'utf8'");
			//--------------------------------------------------------------------------------------
			$index = 0;
			$result = mysqli_query( $conn, "select * from kama_log");
			while( $row = mysqli_fetch_assoc( $result ) ){
				$insert = [];
				foreach($row as $key=>$val){
					$insert[$key] = $val;
				}
				\App\KamaLog::insert($insert);
				$index++;
			}
			\Log::channel('single')->info( "uploadDumpLogs command"."\n"."KamaLog:{$index}" );
			//--------------------------------------------------------------------------------------
			$index = 0;
			$result = mysqli_query( $conn, "select * from kama_usage");
			while( $row = mysqli_fetch_assoc( $result ) ){
				$insert = [];
				foreach($row as $key=>$val){
					$insert[$key] = $val;
				}
				\App\KamaUsage::insert($insert);
				$index++;
			}
			\Log::channel('single')->info( "uploadDumpLogs command"."\n"."KamaUsage:{$index}" );
			//--------------------------------------------------------------------------------------
			$index = 0;
			$result = mysqli_query( $conn, "select * from log_emails");
			while( $row = mysqli_fetch_assoc( $result ) ){
				$insert = [];
				foreach($row as $key=>$val){
					$insert[$key] = $val;
				}
				\App\LogEmails::insert($insert);
				$index++;
			}
			\Log::channel('single')->info( "uploadDumpLogs command"."\n"."LogEmails:{$index}" );
			//--------------------------------------------------------------------------------------
			
			//$sql_dump = File::get('/var/www/html/login.kama-dei.com/storage/logs/kamalogs.sql');
			//DB::connection('mysqllog')->getPdo()->exec($sql_dump);
			
		}catch(\Throwable $ex){
			\Log::channel('single')->info( "uploadDumpLogs command"."\n".$ex->getMessage() );
		}
    }
}
