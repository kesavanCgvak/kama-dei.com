<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Swift_Attachment;

class LogSentMessage{
    public function handle($event){
		try{
			//---------------------------------------------------
			$mail_from = "";
//			foreach( $event->message->getFrom() as $email=>$name){
			foreach( $event->message->getFrom() as $item){
				$email = $item->getAddress();
				$name  = $item->getName();
				if($name!=''){ $mail_from .= "{$email}[{$name}];"; }
				else{ $mail_from .= "{$email};"; }
			}
			//---------------------------------------------------
			$mail_to = "";
//			foreach( $event->message->getTo() as $email=>$name){
			foreach( $event->message->getTo() as $item){
				$email = $item->getAddress();
				$name  = $item->getName();
				if($name!=''){ $mail_to .= "{$email}[{$name}];"; }
				else{ $mail_to .= "{$email};"; }
			}
			//---------------------------------------------------
			$mail_cc = "";
			if($event->message->getCc()==false){ $mail_cc = ""; }
			else{
				$mail_cc = "";
//				foreach( $event->message->getCc() as $email=>$name){
				foreach( $event->message->getCc() as $item){
					$email = $item->getAddress();
					$name  = $item->getName();
					if($name!=''){ $mail_cc .= "{$email}[{$name}];"; }
					else{ $mail_cc .= "{$email};"; }
				}
			}
			//---------------------------------------------------
			$mail_bcc = "";
//			foreach( $event->message->getBcc() as $email=>$name){
			foreach( $event->message->getBcc() as $item){
				$email = $item->getAddress();
				$name  = $item->getName();
				if($name!=''){ $mail_bcc .= "{$email}[{$name}];"; }
				else{ $mail_bcc .= "{$email};"; }
			}
			//---------------------------------------------------
			/*
			$attachments = collect($event->message->getChildren())->whereInstanceOf(Swift_Attachment::class);
			if($attachments->isEmpty()){ $attachCount = 0; }
			else{ $attachCount = count($attachments); }
			*/
			$attachCount = count($event->message->getAttachments());
			//---------------------------------------------------
			$body = $event->message->__serialize()[2];
			$logEmails = new \App\LogEmails;
			$logEmails->mail_from      = $mail_from;
			$logEmails->mail_to        = $mail_to;
			$logEmails->mail_cc        = $mail_cc;
			$logEmails->mail_bcc       = $mail_bcc;
			$logEmails->mail_subject   = $event->message->getSubject();
			//$logEmails->mail_body      = $event->message->getBody();
			$logEmails->mail_body      = $body;
			$logEmails->mail_date      = date("Y-m-d");
			$logEmails->mail_time      = date("H:i:s");
			$logEmails->attached_files = $attachCount;
			$logEmails->save();
/*
			\App\LogEmails::insert([
				'mail_from'      => $mail_from,
				'mail_to'        => $mail_to,
				'mail_cc'        => $mail_cc,
				'mail_bcc'       => $mail_bcc,
				'mail_subject'   => $event->message->getSubject(),
				'mail_body'      => $event->message->getBody(),
				'mail_date'      => date("Y-m-d"),
				'mail_time'      => date("H:i:s"),
				'attached_files' => $attachCount
			]);
*/
		}catch(\Throwable $ex){
			Log::info("----------------------");
			Log::info("Listeners::LogSentMessage::LogEmail");
			Log::info($ex->getMessage());
			Log::info("----------------------");
		}
/*
		Log::info($event->message->getChildren());
		Log::info($event->message->getHeaders());
		Log::info($event->message);
*/
    }
}