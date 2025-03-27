<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Lcobucci\JWT\Parser;
use \Lcobucci\JWT\Builder;
use \Lcobucci\JWT\Signer\Hmac\Sha256;

require dirname(__FILE__).'/../../PHPMailer/Exception.php';
require dirname(__FILE__).'/../../PHPMailer/PHPMailer.php';
require dirname(__FILE__).'/../../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
class LoginController extends Controller {
	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function __construct(){
		$this->middleware('guest');
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function index(Request $request, $userID = null, $userPass = null){
		session()->put('userID' , 0);
		session()->put('isLogin', 0);
		if($userID !== null && $userPass !== null){
			$user = \App\User::find($userID);
			if($user != null){
				if($user->userPass == $user->hash($userPass)){
					return view('login', ['userID'=>$userID, 'userPass'=>$userPass, 'isPassReset'=>true, 'hasResetError'=>false, 'msg'=>'']);
				}else{
					return view('login', ['hasResetError'=>true, 'msg'=>'Token is incorrect', 'isPassReset'=>false]);
				}
			}else{
				return view('login', ['hasResetError'=>true, 'msg'=>'User Not Found', 'isPassReset'=>false]);
			}
		}

		return view('login', ['hasResetError'=>false, 'isPassReset'=>false, 'msg'=>'']);
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function mfaValidate(){
		$user = \App\User::find(\Session('userID'));
		if($user==null || $user->isActive!=1){ return redirect('/login'); }
		else{ return view('mfa', []); }
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function isLogin(Request $request){
		$isLogin = $request->session()->get('isLogin');
		if($isLogin==false){ return redirect('/login'); }
		else{
			return redirect('/dashboard');
		}
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function logout(Request $request){
		$request->session()->put('isLogin', 0);
		$request->session()->put('userID' , 0);
		return 1;
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function isValid(Request $request){
		$request->session()->put('isLogin' , 0);
		$userLogin = $request->input('userLogin');
		$passLogin = $request->input('passLogin');
		$orgLogin  = $request->input('orgLogin' );

		$user = new \App\User;
		$row = $user
					->where(function($q) use($userLogin){
						return $q->
								where('userName', $userLogin)->
								orWhere('email', $userLogin);
					})
					->where('userPass', $user->hash($passLogin))
					->where('orgID'   , $orgLogin)
					->first();

		if($row!=false){
			if($row->isActive!=1){ return -1; }
			session()->put('userID'  , $row->id      );
			session()->put('userName', $row->userName);
			session()->put('isAdmin' , $row->isAdmin );
			session()->put('orgID'   , $row->orgID   );
			session()->put('levelID' , $row->levelID );
			
			\App\User::where('id', $row->id)->update(['mfa_code'=>null, 'mfa_valid_until'=>null]);
			
			$gotoMFA = 1;
			if($row->orgID==0 || $row->orgID==null){
				if(env('main_org_mfa', 0)==0){ $gotoMFA = 2; }
			}else{
				$org = \App\Organization::find($row->orgID);
				if($org!=null && $org->mfa==0){ $gotoMFA = 2; }
			}

			if($gotoMFA==2){
				session()->put('isLogin' , 1);
				\App\User::where('id', $row->id)->update(['lastLogin'=>date("Y-m-d H:i:s")]);
			}else{
				$tmpMail = new \App\Mail\SendMail;
				\Mail::to($row->email)->send($tmpMail->mfa($row));
			}

			return $gotoMFA;
		}else{ return 0; }
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function createPass($passKey){
		$user = new \App\User;
		if($user->isValidPassKey($passKey)){ return view('createPass',['passKey'=>$passKey]); }
		else{ return redirect('/login'); }
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function resetPass($passKey){
		$user = new \App\User;
		if($user->isValidPassKey($passKey)){ return view('createPass',['passKey'=>$passKey]); }
		else{ return redirect('/login'); }
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function setPass(Request $request){
		$passLogin = $request->input('passLogin1');
		$passKey   = $request->input('passKey');

		$tmp = \App\User::where('passKey', '=', $passKey)->first();
		if($tmp!=false){
			$user = \App\User::find($tmp->id);
			$user->userPass = $user->hash($passLogin);
			$user->passKey = '';
			$tmp = $user->save();
			if($tmp!=false){ return 1; }
			else{ return 'Error.'; }
		}else{ return 'Error. user not found'; }
	}
	//-----------------------------------------------------------------------------------
	public function forgotPassword(Request $request){
		$email = $request->input('email');
		$orgId = $request->input('orgId');

		$tmp = \App\User::where('email', $email)
						->where('isActive', 1)
						->where('levelID', '<>', 4)
						->where(function($q) use($orgId){
							if($orgId==-1){ return $q; }
							return $q->where('orgID', $orgId);
						})
						->count();
		if($tmp!=0){
//			if($tmp->levelID==4){ return response()->json(['result'=>1, 'msg'=>'Email not found']); }
			$kamaName = env('BASE_ORGANIZATION');
			$tmp = \App\User::leftjoin('organization_ep', 'user.orgID', '=', 'organization_ep.organizationId')
							->where('email', $email)
							->where('isActive', 1)
							->where('levelID', '<>', 4)
							->where(function($q) use($orgId){
								if($orgId==-1){ return $q; }
								return $q->where('orgID', $orgId);
							})
							->select(
								"user.*",
								\DB::raw("if(user.orgID>0, user.orgID, 0) as orgId"),
								\DB::raw("if(user.orgID>0, organization_ep.organizationShortName, '{$kamaName}') as orgName")
							)
							->orderBy("orgName", "asc");
			if($tmp->count()>1){
				return ['result'=>0, 'msg'=>'', 'orgList'=>$tmp->get()];
			}
			$tmp = $tmp->first();
			$passKey = $tmp->hash( $tmp->id.$tmp->createAt.'resetUser'.$tmp->id.time() );
			$tmp->passKey  = strtoupper($passKey);
			$tmp->save();
			$tmpMail = new \App\Mail\SendMail;
			\Mail::to($tmp->email)->send($tmpMail->resetPass($tmp));
			return response()->json(['result'=>0, 'msg'=>'Email successfully sent', 'orgList'=>null]);
		}else{ return response()->json(['result'=>1, 'msg'=>'This email does not match any account']); }
	}
	//-----------------------------------------------------------------------------------
    public function subtypesession(Request $request,$subtypeID){
            $request->session()->put('subtypeID', $subtypeID);
            return ['result'=>1];
    }
    public function getsubtypesession(Request $request){
            $subtypeID=$request->session()->get('subtypeID');
            return ['result'=>1,'subtypeID'=>$subtypeID];
    }
	//-----------------------------------------------------------------------------------
    public function gettoke(Request $request){
		$isLogin = $request->session()->get('isLogin');
		if($isLogin==false){ return ['result'=>0, 'msg'=>'logout']; }
		else{
/*
			$builder = new Builder();
			$signer  = new Sha256();
			$secret = "yao";
			$builder->setIssuer("kama") 
					->setAudience("chatbot") 
					->setId("chatbotlogin", true) 
					->setIssuedAt(time()); 
			$builder->sign($signer, $secret);
			$token = (string)$builder->getToken();
*/
//---------------------------------------------------------
			$json = [
				'signer' => \Crypt::encryptString(env("API_URL")),
				'secret' => env('APP_KEY'),
				'time'   => time(),
				'orgid'  => \Session('orgID')
			];

			$token = \Crypt::encryptString(json_encode($json));
//---------------------------------------------------------
			return ['result'=>1, 'msg'=>$token];
		}
    }

    public function isLogintoke($token){
/*
		$signer  = new Sha256();
		$secret = "yao";
		if (!$token) {
			return ['result'=>0, 'msg'=>'Invalid token'];
		}
		try {
			$parse = (new Parser())->parse($token);
			if (!$parse->verify($signer, $secret)) {
				return ['result'=>0, 'msg'=>'Invalid token'];
			}
			if ($parse->isExpired()) {
				return ['result'=>0, 'msg'=>'Already expired'];
			}
			return ['result'=>1, 'orgid'=>$parse->getClaim('aud')];
		} catch (Exception $e) {
			return ['result'=>0, 'msg'=>'Invalid token'];
		}
*/
//---------------------------------------------------------
$signer = env("API_URL");
$secret = env('APP_KEY');
try {
	if(!$token ){ throw new \Exception("Invalid token"); }
	
	$json = json_decode(\Crypt::decryptString($token) , true);

	if(\Crypt::decryptString($json['signer'])!=$signer){ throw new \Exception("Invalid token"); }
	if($json['secret']!=$secret){ throw new \Exception("Invalid token"); }

	if($json['time']<time()-120){ throw new \Exception("Already expired"); }
	
	return ['result'=>1, 'orgid'=>$json['orgid']];
} catch(\Exception $e) {
	return ['result'=>0, 'msg'=>$e->getMessage()];
}
			
//---------------------------------------------------------

    }
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function emailIsValid(Request $request){
		$request->session()->put('isLogin' , 0);
		$email = trim($request->input('email'));

		$isValid = \App\User::
					where('email', $email)->
					where('isActive', 1)->
					where('levelID', '<>', 4)->
					count();
		if($isValid){
			$kamaName = env('BASE_ORGANIZATION');
			$data = \App\User::
						leftjoin('organization_ep', 'user.orgID', '=', 'organization_ep.organizationId')->
						where('email', $email)->
						where('isActive', 1)->
						where('levelID', '<>', 4)->
						select(
							\DB::raw("if(user.orgID>0, user.orgID, 0) as id"),
							\DB::raw("if(user.orgID>0, organization_ep.organizationShortName, '{$kamaName}') as name")
						)->
						orderBy("name", "asc")->
						get();
			return ['result'=>0, 'msg'=>'', 'data'=>$data];
		}else{ return ['result'=>1, 'msg'=>'Email not found!']; }
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function multiFactorAuthentication(Request $request){
		//-------------------------------------------------------------------------------
		try{
			//---------------------------------------------------------------------------
			$validator = \Validator::make(
					$request->all(),
					[
						'mfaCode' => 'required'
					],
					[
						"mfaCode.required" => "Verify code required"
					]
			);
			
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $request->all();
			//---------------------------------------------------------------------------
			
/*
			if($row->isActive!=1){ return -1; }
			session()->put('userID'  , $row->id      );
			session()->put('userName', $row->userName);
			session()->put('isAdmin' , $row->isAdmin );
			session()->put('orgID'   , $row->orgID   );
			session()->put('levelID' , $row->levelID );

			//$user = \App\User::find($row->id);
			//$user->lastLogin = date("Y-m-d H:i:s");
			//$tmp = $user->save();
*/
			$tmp = new \App\User;
			$mfa_code = $tmp->hash($data['mfaCode']);
			$usr = \App\User::where('id', \Session('userID'))
				->where('orgID', \Session('orgID'))
				->where('levelID', \Session('levelID'))
				->where('isActive',1)
				->where('mfa_code', $mfa_code)
				->first();
			if($usr==null){ throw new \Exception("Invalid Verification Code"); }
			$max_mfa_validation = env("max_mfa_validation", 10)*60;
			if($usr->mfa_valid_until<=time()){ throw new \Exception("Verification code has expired."); }

			session()->put('isLogin' , 1);
			\App\User::where('id', $usr->id)
				->update([
					"mfa_code"   => null,
					"mfa_valid_until" => null,
					"lastLogin"  => date("Y-m-d H:i:s")
				]);
			return ['result'=>0, 'msg'=>''];
			//---------------------------------------------------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-------------------------------------------------------------------------------
	}
	//-----------------------------------------------------------------------------------
	public function resendVerifyCode(Request $request){
		//-------------------------------------------------------------------------------
		try{
			//---------------------------------------------------------------------------
			$validator = \Validator::make(
					$request->all(),
					[],
					[]
			);
			
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $request->all();
			//---------------------------------------------------------------------------
			$usr = \App\User::where('id', \Session('userID'))
				->where('orgID', \Session('orgID'))
				->where('levelID', \Session('levelID'))
				->where('isActive',1)
				->first();
			if($usr==null){ throw new \Exception("invalid user"); }
			
			$mfa_valid_until = time()+(env("max_mfa_validation", 10)*60);
			$tmpMail = new \App\Mail\SendMail;
			\Mail::to($usr->email)->send($tmpMail->mfa($usr));

			return ['result'=>0, 'msg'=>'', 'remind'=>date("i:s",$mfa_valid_until-time())];
			//---------------------------------------------------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-------------------------------------------------------------------------------
	}
	//-----------------------------------------------------------------------------------
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
