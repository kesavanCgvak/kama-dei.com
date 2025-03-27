<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelationLanguage extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_language';
	protected $primaryKey = "relationLanguageId";
	//--------------------------------------------------------------------
}