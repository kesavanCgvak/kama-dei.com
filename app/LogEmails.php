<?php 
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogEmails extends Model {
	//---------------------------------------------------------------
	use Encryptable;
	//---------------------------------------------------------------
	protected $connection = 'mysqllog';
	protected $table      = 'log_emails';
	protected $primaryKey = "id";
	public    $timestamps = false;
	//---------------------------------------------------------------
	protected $encryptable = [
		'mail_to','mail_cc','mail_bcc','mail_subject','mail_body'
	];
	//---------------------------------------------------------------
}