<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Kaasmapping extends Migration
{
	//-----------------------------------------------------------------------------------------
	private static function dataBase(){ return env('migration_kaasmapping', "-"); }
	//-----------------------------------------------------------------------------------------
    public function up(){
return;
		self::mapping_bot();
		self::mapping_detail();
		self::setting();
		self::structure();
		self::type();
    }
	//-----------------------------------------------------------------------------------------
    public function down(){
		Schema::connection(self::dataBase())->dropIfExists('mapping_bot');
		Schema::connection(self::dataBase())->dropIfExists('mapping_detail');
		Schema::connection(self::dataBase())->dropIfExists('setting');
		Schema::connection(self::dataBase())->dropIfExists('structure');
		Schema::connection(self::dataBase())->dropIfExists('type');
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
				$table->integer('ownerId'     )->unsigned();
				$table->integer('portal_id'   )->unsigned();
				$table->integer('structure_id')->unsigned();
				$table->enum('publish_status', ['Published', 'Unpublished'])->default('Unpublished');
				$table->integer('user_id')->unsigned();
				$table->timestamp('last')->useCurrent();

				$table->index(['portal_id']);
				$table->index(['structure_id']);

				//$table->primary('id');
				//$table->index(['personId']);
				//$table->timestamps();
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
				$table->integer('mappingBot_id')->unsigned();
				$table->integer('type_id')->unsigned();
				$table->string('val1', 250);
				$table->string('val2', 250)->nullable();
				$table->string('val3',1000)->nullable();
				$table->integer('kr_id')->unsigned()->default(0);
				$table->string('tag', 250)->nullable();
				$table->integer('user_id')->unsigned();
				$table->timestamp('last')->useCurrent();

				$table->index(['parent_id'],'parent_id');
				$table->index(['kr_id'],'kr_id');
				$table->index(['kr_id'],'key1');
				$table->index(['parent_id', 'kr_id'],'kye0');
				$table->index(['mappingBot_id'],'mappingBot_id');
				$table->index(['type_id'],'type_id');
			});
		}else{
			Schema::connection(self::dataBase())->table('mapping_detail', function (Blueprint $table){
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
				$table->integer('portal_id')->unsigned();
				$table->integer('user_id')->unsigned();
				$table->timestamp('last')->useCurrent();
				
				$table->unique(['org_id'],'org_id');
				$table->index(['user_id'],'user_id');
				$table->index(['portal_id'],'portal_id');
			});
		}else{
			Schema::connection(self::dataBase())->table('setting', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function structure(){
		if(!Schema::connection(self::dataBase())->hasTable('structure')){
			Schema::connection(self::dataBase())->create('structure', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->integer('id')->autoIncrement()->unsigned();
				$table->string('name', 300);
			});
		}else{
			Schema::connection(self::dataBase())->table('structure', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
	private static function type(){
		if(!Schema::connection(self::dataBase())->hasTable('type')){
			Schema::connection(self::dataBase())->create('type', function (Blueprint $table){
				$table->engine = 'MyISAM';

				$table->integer('id')->autoIncrement()->unsigned();
				$table->string('name', 300);
				$table->integer('structure_id')->unsigned();
				$table->integer('parent_id')->unsigned();

				$table->index(['structure_id'],'structure_id');
				$table->index(['parent_id'],'parent_id');
			});
		}else{
			Schema::connection(self::dataBase())->table('type', function (Blueprint $table){
			});
		}
	}
	//-----------------------------------------------------------------------------------------
}
