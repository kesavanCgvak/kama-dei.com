<?php
//--------------------------------------------------------------------------------------------------
namespace App\Console\Commands;
//--------------------------------------------------------------------------------------------------
use Illuminate\Console\Command;
//--------------------------------------------------------------------------------------------------
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
//--------------------------------------------------------------------------------------------------

use Illuminate\Support\Facades\Http;

class monitoring extends Command{
	//----------------------------------------------------------------------------------------------
    protected $signature   = 'monitoring:cron';
    protected $description = 'monitoring';
	//----------------------------------------------------------------------------------------------
	public function __construct(){ parent::__construct(); }
	//----------------------------------------------------------------------------------------------
    public function handle(){
		//------------------------------------------------------------------------------------------
		try{
			ini_set('memory_limit', '-1');
			//--------------------------------------------------------------------------------------
			if(file_exists(env('monitoring_location')."/monitoring.ini")){
				$ini = parse_ini_file(env('monitoring_location')."/monitoring.ini");
				if(($ini['lastrun']+($ini['frequency']*60))<=time()){
					
					$ini['lastrun'] = time();
					self::saveIni($ini);
					
					$apiData = [
						'portalcode' => $ini['portalcode'],
						'orgid'      => $ini['orgid'     ],
						'email'      => $ini['user'      ]
					];
					$response = Http::post(env('nlu_consumer_identify'), $apiData);
					$responseJSON = json_decode($response->getBody(), true);
					if($response->status()!=200){
						$msg = ((isset($responseJSON['message'])) ?$responseJSON['message'] :'');
						$error = "Error consumer identify:".$response->status()." - {$msg}";
						throw new \Exception($error);
					}else{
						if($responseJSON['result']==1){
							$error = "Error consumer identify: invalid user";
							throw new \Exception($error);
						}else{
							$start = time();
							$inquiry = [
								'request' => [
									'type'      => 'text',
									'message'   => $ini['uu_text'],
									'utterance' => $ini['uu_text'],
									'answers'   => []
								]
							];
							$apiParams = [
								'state'  => 0,
								'userid' => $responseJSON['id'    ],
								'orgid'  => $ini         ['orgid' ],
								'inquiry'=> json_encode($inquiry)
							];
							$apiHeader = ['apikey'=>$responseJSON['apikey']];

							$response = Http::withHeaders($apiHeader)->asForm()->post(env('nlu_python_api'), $apiParams);
							$responseJSON = json_decode($response->getBody(), true);
							$end = time();
							if($response->status()!=200){
								$msg = ((isset($responseJSON['message'])) ?$responseJSON['message'] :'');
								$error = "python api:".$response->status()." - {$msg}";
								throw new \Exception($error);
							}else{
								if($start+($ini['art']*60)>$end){ \Log::channel('monitoring')->info( "Passed" ); }
								else{
									$rt = round((($end-$start)/60),4);
									$error = "trouble:1 responsetime:{$rt} Acceptable time:{$ini['art']}";
									throw new \Exception($error);
								}
							}
						}
					}
				}
			}else{
				$error = "monitoring.ini not found";
				throw new \Exception($error);
			}
			//--------------------------------------------------------------------------------------
		}catch(\Throwable $ex){
			$error = $ex->getMessage();
			\Log::channel('monitoring')->info($error);
			self::mailLog($ini['email'], date("Y-m-d H:i:s")." ".$error);
		}
		//------------------------------------------------------------------------------------------
    }
	//----------------------------------------------------------------------------------------------
	private function saveIni($data){
		$ini = [];
		foreach($data as $key=>$val){ $ini[] = "{$key} = ".(is_numeric($val) ?$val : "\"{$val}\""); }
		file_put_contents(getcwd()."/storage/logs/monitoring.ini", implode(PHP_EOL, $ini));
	}
	//----------------------------------------------------------------------------------------------
	private function mailLog($emailsIn, $error){
		$tmpMail = new \App\Mail\SendMail;
		$emails = explode(",", $emailsIn);
		foreach($emails as $email){
			$email = trim($email);
			\Log::channel('monitoring')->info($email);
			if($email!=''){ \Mail::to($email)->send($tmpMail->monitoring("Kama-dei Monitoring", $error)); }
		}
	}
	//----------------------------------------------------------------------------------------------
}
//--------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------
