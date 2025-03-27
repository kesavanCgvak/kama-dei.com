<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KamaLogs extends Migration
{
	//-----------------------------------------------------------------------------------------
	private static function dataBase(){ return env('migration_kamalogs', "-"); }
	//-----------------------------------------------------------------------------------------
    public function up(){
return;
		self::feedback();
		self::kama_log();
		self::kama_usage();
		self::log_emails();
    }
	//-----------------------------------------------------------------------------------------
    public function down(){
		Schema::connection(self::dataBase())->dropIfExists('feedback');
		Schema::connection(self::dataBase())->dropIfExists('kama_log');
		Schema::connection(self::dataBase())->dropIfExists('kama_usage');
		Schema::connection(self::dataBase())->dropIfExists('log_emails');
    }
	//-----------------------------------------------------------------------------------------
	
	//-----------------------------------------------------------------------------------------
	private static function feedback(){
		if(!Schema::connection(self::dataBase())->hasTable('feedback')){
			Schema::connection(self::dataBase())->create('feedback', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->unsignedBigInteger('id')->autoIncrement();
				$table->integer('message_id')->unsigned();
				$table->timestamp('created_on')->useCurrent();
				$table->string('comment',1024);
				$table->boolean('feedback')->unsigned();

				//$table->primary('id');
				//$table->index(['personId']);
				//$table->timestamps();
			});
		}else{
			Schema::connection(self::dataBase())->table('feedback', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function kama_log(){
		if(!Schema::connection(self::dataBase())->hasTable('kama_log')){
			Schema::connection(self::dataBase())->create('kama_log', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->integer('msg_id')->autoIncrement();
				$table->integer('signin_id')->unsigned();
				$table->string('apikey',250)->nullable();
				$table->datetime('timestamp');
				$table->string('sender',255);
				$table->text('raw_msg');
				$table->text('msg');

				$table->index(['apikey']);
				$table->index(['signin_id']);
				
				//$table->primary('id');
				//->useCurrent()
				//$table->timestamps();
			});
		}else{
			Schema::connection(self::dataBase())->table('kama_log', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function kama_usage(){
		if(!Schema::connection(self::dataBase())->hasTable('kama_usage')){
			Schema::connection(self::dataBase())->create('kama_usage', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->integer('signin_id')->autoIncrement();
				$table->string('apikey',250)->nullable();
				$table->string('ip',255);
				$table->string('email',255);
				$table->integer('user_id');
				$table->integer('org_id');
				$table->text('user_name')->nullable();
				$table->text('org_name')->nullable();
				$table->datetime('timestamp');
				$table->text('memo');
				$table->integer('archive')->default(0);
				$table->integer('order')->default(0);
				$table->tinyInteger('isSend')->unsigned()->default(0);

				
				//$table->primary('id');
				//->useCurrent()
				//$table->timestamps();
			});
		}else{
			Schema::connection(self::dataBase())->table('kama_usage', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function log_emails(){
		if(!Schema::connection(self::dataBase())->hasTable('log_emails')){
			Schema::connection(self::dataBase())->create('log_emails', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->bigInteger('id')->autoIncrement()->unsigned();
				$table->string('mail_from'   , 1000);
				$table->string('mail_to'     , 1000);
				$table->string('mail_cc'     , 1000);
				$table->string('mail_bcc'    , 1000);
				$table->string('mail_subject', 3000)->nullable();
				$table->longText('mail_body')->nullable();
				$table->date('mail_date');
				$table->time('mail_time');
				$table->integer('attached_files')->unsigned();
			});
		}else{
			Schema::connection(self::dataBase())->table('log_emails', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
}
