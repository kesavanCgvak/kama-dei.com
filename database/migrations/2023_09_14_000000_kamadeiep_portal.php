<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KamadeiepPortal extends Migration{
	//-----------------------------------------------------------------------------------------
	private static function dataBase(){ return env('migration_kamadeiep', "-"); }
	//-----------------------------------------------------------------------------------------
    public function up(){
		DB::connection(self::dataBase())->statement('ALTER TABLE portal ENGINE = MyISAM');
		self::kamadeiep_portal();
    }
	//-----------------------------------------------------------------------------------------
    public function down(){
		//  Illuminate\Support\Facades\DB::setDefaultConnection(env('migration_kamadeiep', "-"));
		//Schema::connection(self::dataBase())->dropIfExists('portal');
    }
	//-----------------------------------------------------------------------------------------
	//-----------------------------------------------------------------------------------------
	private static function kamadeiep_portal(){
		if(!Schema::connection(self::dataBase())->hasTable('portal')){
			Schema::connection(self::dataBase())->create('portal', function (Blueprint $table){
				$table->engine    = 'MyISAM';
				$table->charset   = 'utf8';
				$table->collation = 'utf8_general_ci';

				self::id($table);
				self::MoD_($table);
				self::ntfctn_mssg_cstmztn($table);
				self::rqst_mssg_cstmztn($table);
				self::organization_id($table);
				self::code($table);
				self::portal_number($table);
				self::name($table);
				self::description($table);
				self::unknownPersonalityId($table);
				self::OnOff($table);
				self::OnOff_by($table);
				self::KaaS3PB($table);
				self::last($table);
				self::hasLiveAgent($table);
				self::feedback($table);
				self::thumbsup($table);
				self::comment($table);
			});
		}else{
			Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){
				$table->engine    = 'MyISAM';
				$table->charset   = 'utf8';
				$table->collation = 'utf8_general_ci';
			});
			
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'id') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::id($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'MoD_') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::MoD_($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'ntfctn_mssg_cstmztn') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::ntfctn_mssg_cstmztn($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'rqst_mssg_cstmztn') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::rqst_mssg_cstmztn($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'organization_id') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::organization_id($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'code') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::code($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'portal_number') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::portal_number($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'name') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::name($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'description') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::description($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'unknownPersonalityId') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::unknownPersonalityId($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'OnOff') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::OnOff($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'OnOff_by') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::OnOff_by($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'KaaS3PB') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::KaaS3PB($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'last') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::last($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'hasLiveAgent') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::hasLiveAgent($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'feedback') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::feedback($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'thumbsup') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::thumbsup($table); });
			}
			if( !Schema::connection(self::dataBase())->hasColumn('portal', 'comment') ){
				Schema::connection(self::dataBase())->table('portal', function(Blueprint $table){ self::comment($table); });
			}
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function id                   ($table){ $table->integer('id')->autoIncrement()->unsigned(); }
	private static function MoD_                 ($table){ $table->boolean('MoD_')->unsigned(); }
	private static function ntfctn_mssg_cstmztn  ($table){ $table->string('ntfctn_mssg_cstmztn', 1000); }
	private static function rqst_mssg_cstmztn    ($table){ $table->string('rqst_mssg_cstmztn'  , 1000); }
	private static function organization_id      ($table){
		$table->integer('organization_id')->unsigned();
		$table->index(['organization_id'], 'organization_id');
	}
	private static function code                 ($table){ $table->string('code'         , 5); }
	private static function portal_number        ($table){ $table->string('portal_number', 1); }
	private static function name                 ($table){ $table->string('name'         , 255); }
	private static function description          ($table){ $table->text('description')->nullable(); }
	private static function unknownPersonalityId ($table){
		$table->integer('unknownPersonalityId')->nullable();
		$table->index(['unknownPersonalityId'], 'unknownPersonalityId');
	}
	private static function OnOff                ($table){
		$table->boolean('OnOff')->unsigned()->default(0);
		$table->index(['OnOff'], 'OnOff');
	}
	private static function OnOff_by             ($table){ $table->integer('OnOff_by')->nullable(); }
	private static function KaaS3PB              ($table){ $table->tinyInteger('KaaS3PB')->nullable()->unsigned(); }
	private static function last                 ($table){ $table->timestamp('last')->useCurrent(); }
	private static function hasLiveAgent         ($table){ $table->tinyInteger('hasLiveAgent')->nullable()->default(0); }
	private static function feedback             ($table){ $table->boolean('feedback')->unsigned()->default(0); }
	private static function thumbsup             ($table){ $table->boolean('thumbsup')->unsigned()->default(0); }
	private static function comment              ($table){ $table->boolean('comment')->unsigned()->default(0); }
	//-----------------------------------------------------------------------------------------
	
}
