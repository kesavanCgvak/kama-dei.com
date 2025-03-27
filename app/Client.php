<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Auth\Passwords\CanResetPassword;
//use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
//use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Client extends Model {

	protected $table = 'client';

	protected $fillable = ['clientName', 'lastLogin'];

//	protected $hidden = ['userPass'];

    public function pageClientLevel(){
        return $this->hasMany('App\PageClientLevel');
    }
}
