<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
   public $timestamps = false ;
   protected $connection = 'mysql';
   protected $table = 'language';
   protected $primaryKey = 'languageId';
}
