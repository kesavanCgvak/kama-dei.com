<?php 
namespace App;

//use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Auth\Passwords\CanResetPassword;
//use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
//use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class PageClientLevel extends Model {

	protected $table = 'page_client_level';

	protected $fillable = ['pageID', 'clientID', 'levelID'];

//	public $primarykey = 'pageID';

//	protected $hidden = ['userPass'];
    public function pages(){ 
		return $this->belongsTo('App\SitePages', 'pageID', 'id');
    }

    public function client(){
        return $this->belongsTo('App\Client', 'clientID', 'id');
    }

    public function level(){
        return $this->belongsTo('App\Level', 'levelID', 'id');
    }
}
