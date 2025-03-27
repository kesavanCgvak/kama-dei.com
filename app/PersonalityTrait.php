<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonalityTrait extends Model
{
	//----------------------------------------------------
	public    $timestamps = false;
	//----------------------------------------------------
	protected $connection = 'mysql2';
	protected $table      = 'personality_trait';
	protected $primaryKey = "personalityTraitId";
	//----------------------------------------------------
}
