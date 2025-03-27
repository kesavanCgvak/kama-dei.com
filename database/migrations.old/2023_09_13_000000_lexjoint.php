<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Lexjoint extends Migration
{
	//-----------------------------------------------------------------------------------------
	private static function dataBase(){ return env('migration_lexjoint', "-"); }
	//-----------------------------------------------------------------------------------------
    public function up(){
return;
		self::mapping_bot();
		self::mapping_detail();
		self::mapping_json();
		self::setting();
    }
	//-----------------------------------------------------------------------------------------
    public function down(){
		Schema::connection(self::dataBase())->dropIfExists('mapping_bot');
		Schema::connection(self::dataBase())->dropIfExists('mapping_detail');
		Schema::connection(self::dataBase())->dropIfExists('mapping_json');
		Schema::connection(self::dataBase())->dropIfExists('setting');
    }
	//-----------------------------------------------------------------------------------------
	//-----------------------------------------------------------------------------------------
	private static function mapping_bot(){
		if(!Schema::connection(self::dataBase())->hasTable('mapping_bot')){
			Schema::connection(self::dataBase())->create('mapping_bot', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->integer('bot_id')->autoIncrement()->unsigned();
				$table->string('mappingName', 250);
				$table->string('bot_name'   , 250);
				$table->string('bot_alias'  , 250);
				$table->integer('ownerId'         )->unsigned();
				$table->integer('personaiD'       )->unsigned();
				$table->integer('lexPersonalityID')->unsigned();
				$table->integer('lexUserID'       )->unsigned();
				$table->enum('publish_status', ['Published', 'Unpublished'])->default('Unpublished');
				$table->integer('user_id')->unsigned();
				$table->timestamp('last')->useCurrent();
			});
		}else{
			Schema::connection(self::dataBase())->table('mapping_bot', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function mapping_detail(){
		if(!Schema::connection(self::dataBase())->hasTable('mapping_detail')){
			Schema::connection(self::dataBase())->create('mapping_detail', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->integer('id')->autoIncrement()->unsigned();
				$table->integer('parent_id')->unsigned();
				$table->enum('type', ['intent', 'slot', 'value']);
				$table->string('val1', 250);
				$table->string('val2', 250)->nullable();
				$table->string('val3',1000)->nullable();
				$table->integer('kr_id')->unsigned();
				$table->string('tag', 250);
				$table->integer('user_id')->unsigned();
				$table->timestamp('last')->useCurrent();

				$table->index(['type'],'type');
				$table->index(['parent_id'],'parent_id');
				$table->index(['kr_id'],'kr_id');
				$table->index(['type', 'kr_id'],'key1');
				$table->index(['parent_id', 'type', 'kr_id'],'kye0');
			});
		}else{
			Schema::connection(self::dataBase())->table('mapping_detail', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function mapping_json(){
		if(!Schema::connection(self::dataBase())->hasTable('mapping_json')){
			Schema::connection(self::dataBase())->create('mapping_json', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->integer('id')->autoIncrement()->unsigned();
				$table->integer('mapId')->unsigned();
				$table->enum('type', ['bot', 'intent', 'slot', 'value']);
				$table->string('name', 250);
				$table->string('version', 250);
				$table->text('json');
				
				$table->index(['mapId'],'mapId');
				$table->index(['type'],'type');
			});
		}else{
			Schema::connection(self::dataBase())->table('mapping_json', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function setting(){
		if(!Schema::connection(self::dataBase())->hasTable('setting')){
			Schema::connection(self::dataBase())->create('setting', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->integer('id')->autoIncrement()->unsigned();
				$table->integer('org_id')->unsigned();
				$table->string('aws_customer_id', 250);
				$table->integer('personalityId')->unsigned();
				$table->integer('lexPersonalityID')->unsigned();
				$table->integer('lexUserID')->unsigned();
				$table->integer('ownerId')->unsigned();
				$table->integer('user_id')->unsigned();
				$table->timestamp('last')->useCurrent();

				$table->unique(['org_id'],'org_id');
				$table->index(['user_id'],'user_id');
			});
		}else{
			Schema::connection(self::dataBase())->table('setting', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
}
