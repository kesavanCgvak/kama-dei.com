<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Auth\Passwords\CanResetPassword;
//use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
//use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Level extends Model {

	public    $timestamps = false;
	protected $table = 'level';
	protected $fillable = ['levelName', 'last'];

/*
	protected $hidden = ['userPass'];
    public function pageClientLevel(){
        return $this->hasMany('App\PageClientLevel');
    }
*/
	//---------------------------------------
    public function getName($id){ 
		try{
			$tmp = $this->find($id);
			if(is_null($tmp)){ return ''; }
			return $tmp->levelName;
		}catch(Exception  $ex){ return ''; }
    }
	//---------------------------------------
}
