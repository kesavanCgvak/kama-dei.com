<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

//------------------------------------------
//------------------------------------------
class SendMail extends Mailable{
	//--------------------------------------
	use Queueable, SerializesModels;
	//--------------------------------------
	public function __construct(){ }
	//--------------------------------------
	public function build(){}
	//--------------------------------------
	public function Welcome(){ return $this->view('emails.welcome'); }
	//--------------------------------------
	public function createPass($user){ return $this->subject('Kama-DEI Signup')->view('emails.createPass', ['user'=>$user]); }
	//--------------------------------------
	public function resetPass($user){ return $this->subject('Kama-DEI resetpass')->view('emails.resetPass', ['user'=>$user]); }
	//--------------------------------------
	public function mfa($user){ 
		return $this->from(env("mfa_mail_from_address"), env("mfa_mail_from_name"))
			->subject(env("mfa_mail_subject"))
			->view('emails.mfa', ['user'=>$user]);
	}
	//--------------------------------------
	public function dataClassificationPass($user, $sensitivePassword){
		return $this->subject('Kama-DEI data classification')->view('emails.dataClassificationPass', ['user'=>$user, 'sensitivePassword'=>$sensitivePassword]);
	}
	//--------------------------------------
	public function kamaLog($subject, $log_id, $instructions, $orgID){ 
		return $this
				->subject($subject)
				->view('emails.kamaLog', ['log_id'=>$log_id, 'instructions'=>$instructions, 'orgID'=>$orgID]); 
	}
	//--------------------------------------
	public function kamaautoLog($subject, $orgID, $total, $timestamp, $path, $files){ 
		$email = $this
				->subject($subject)
				->view('emails.kamaAutoLog', ['orgID'=>$orgID, 'attached'=>$total, 'timestamp'=>$timestamp]);
		foreach ($files as $file) {
			$email->attach(public_path("{$path}/{$file}"), [
//					'as' => "kamaLog.{$orgID}.".$file,
					'as' => $file
					//'mime' => 'application/pdf',
				]);
		}
		
		return $email;
	}
	//--------------------------------------
	public function kamaMyLogWithAttache($subject, $orgID, $total, $uLog, $path, $file, $body=''){
		$email = $this
				->subject($subject)
				->view('emails.kamaMyLogWithAttache', ['orgID'=>$orgID, 'attached'=>$total, 'uLog'=>$uLog, 'body'=>$body]);
			$email->attach(public_path("{$path}/{$file}"), [
//					'as' => "kamaLog.{$orgID}.".$file,
					'as' => $file
					//'mime' => 'application/pdf',
				]);
	
		return $email;
	}
	//--------------------------------------
	public function monitoring($subject, $error){ 
		return $this
				->subject($subject)
				->view('emails.monitoring', ['error'=>$error]); 
	}
	//--------------------------------------
}
//------------------------------------------
//------------------------------------------
