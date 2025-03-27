<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KamalogsFeedback extends Migration{
	//-----------------------------------------------------------------------------------------
	private static function dataBase(){ return env('migration_kamalogs', "-"); }
	//-----------------------------------------------------------------------------------------
    public function up(){
		DB::connection(self::dataBase())->statement('ALTER TABLE feedback ENGINE = MyISAM');
		self::kamalogs_feedback();
    }
	//-----------------------------------------------------------------------------------------
    public function down(){
		//  Illuminate\Support\Facades\DB::setDefaultConnection(env('migration_kamalogs', "-"));
		//Schema::connection(self::dataBase())->dropIfExists('feedback');
    }
	//-----------------------------------------------------------------------------------------
	//-----------------------------------------------------------------------------------------
	private static function kamalogs_feedback(){
		if(!Schema::connection(self::dataBase())->hasTable('feedback')){
			Schema::connection(self::dataBase())->create('feedback', function (Blueprint $table){
				$table->engine    = 'MyISAM';
				$table->charset   = 'utf8';
				$table->collation = 'utf8_general_ci';

				self::id($table);
				self::message_id($table);
				self::created_on($table);
				self::comment($table);
				self::feedback($table);
			});
		}else{
			Schema::connection(self::dataBase())->table('feedback', function(Blueprint $table){
				$table->engine    = 'MyISAM';
				$table->charset   = 'utf8';
				$table->collation = 'utf8_general_ci';
			});
			
			if( !Schema::connection(self::dataBase())->hasColumn('feedback', 'id') ){
				Schema::connection(self::dataBase())->table('feedback', function(Blueprint $table){ self::id($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('feedback', 'message_id') ){
				Schema::connection(self::dataBase())->table('feedback', function(Blueprint $table){ self::message_id($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('feedback', 'created_on') ){
				Schema::connection(self::dataBase())->table('feedback', function(Blueprint $table){ self::created_on($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('feedback', 'comment') ){
				Schema::connection(self::dataBase())->table('feedback', function(Blueprint $table){ self::comment($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('feedback', 'feedback') ){
				Schema::connection(self::dataBase())->table('feedback', function(Blueprint $table){ self::feedback($table); });
			}
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function id        ($table){ $table->bigInteger('id')->autoIncrement()->unsigned(); }
	private static function message_id($table){ $table->integer('message_id')->unsigned(); }
	private static function created_on($table){ $table->timestamp('created_on')->useCurrent(); }
	private static function comment   ($table){ $table->string('comment', 1024); }
	private static function feedback  ($table){ $table->boolean('feedback')->unsigned(); }
	//-----------------------------------------------------------------------------------------
	
}
