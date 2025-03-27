<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
Route::get('/', function () {
    return view('welcome');
});
*/
//GET
Route::get('/test_liveagentclass', 'Api\Dashboard\LiveAgent\MappingController@show');
Route::get('/test/mapping'  , 'Controller@testMapping');
Route::get('/test/aws'      , 'Controller@testAWS');
Route::get('/monitoring/log', function(){ return view('monitoring_log'); });


Route::get('/'        , 'LoginController@isLogin');
Route::get('/login'   , 'LoginController@index');
Route::get('/mfa_code', 'LoginController@mfaValidate');
Route::get('/pass/create/{passKey}', 'LoginController@createPass');
Route::get('/pass/change', 'ChangePasswordController@index');

Route::prefix('api')->post('/pass_change/', 'ChangePasswordController@passChange');

Route::get('/dashboard', function(){ return redirect('/panel/dashboard'); });
Route::get('/collection', function(){ return redirect('/panel/collection'); });
Route::get('/collection/logs', function(){ return redirect('/panel/collection/logs'); });

Route::group(['prefix' => 'login'], function(){
	Route::get('/'              , 'LoginController@index');
	Route::get('/reset/{userID}/{userPass}'        , 'LoginController@index');
});

Route::group(['prefix' => 'billing'], function(){
	Route::get('/bill/{id}/{dt}', 'Api\Dashboard\Billing\BillingController@bill');
	Route::get('/detail/{id}/{dt}', 'Api\Dashboard\Billing\BillingController@detail');
});

Route::group(['prefix' => 'panel/dashboard'], function(){
	Route::get('/'              , 'dashboardController@index');
	Route::get('/{menu}'        , 'dashboardController@index');
	Route::get('/{menu}/{child}', 'dashboardController@index');
});

Route::group(['prefix' => 'panel'], function(){
	Route::get('/'              , 'dashboardController@index');
	Route::get('/{menu}'        , 'dashboardController@index');
	Route::get('/{menu}/{child}', 'dashboardController@index');
    Route::get('/{menu}/{child}/{table}', 'dashboardController@index');
    Route::get('/{menu}/{child}/{table}/{parent}', 'dashboardController@index');
});

Route::get('/u'                  , 'LoginController@isLogin');
Route::get('/u/{username}'       , 'profileController@index');
Route::get('/u/{username}/{menu}', 'profileController@index');

//POST
Route::post('/login/emailisvalid'               , 'LoginController@emailIsValid');
Route::post('/login/isvalid'                    , 'LoginController@isValid');
Route::post('/login/out'                        , 'LoginController@logout');
Route::post('/login/setPass'                    , 'LoginController@setPass');
Route::post('/login/multi_factor_authentication', 'LoginController@multiFactorAuthentication');
Route::post('/login/resend_verify_code'         , 'LoginController@resendVerifyCode');


Route::get('/gettoken'   , 'LoginController@gettoke');
Route::get('/islogintoke/{menu}'   , 'LoginController@isLogintoke');

Route::get('/emaileav', function () {
    return [
        'MAIL_DRIVER'=>env('MAIL_DRIVER'),
        'MAIL_HOST'=>env('MAIL_HOST'),
        'MAIL_PORT'=>env('MAIL_PORT'),
        'MAIL_USERNAME'=>env('MAIL_USERNAME'),
        'MAIL_PASSWORD'=>env('MAIL_PASSWORD'),
        'MAIL_DRIVER'=>env('MAIL_DRIVER'),
        'MAIL_ENCRYPTION'=>env('MAIL_ENCRYPTION'),

        'MAIL_FROM_ADDRESS'=>env('MAIL_FROM_ADDRESS'),
        'MAIL_FROM_NAME'=>env('MAIL_FROM_NAME')
    ];
});

Route::get('/login/subtypesession/{subtypeID}', 'LoginController@subtypesession');
Route::get('/login/getsubtypesession', 'LoginController@getsubtypesession');



Route::get('/clear/all', function() {
	$view_clear  = Artisan::call('view:clear');
    $cache_clear = Artisan::call('cache:clear');
	$config_cache = Artisan::call('config:cache');
	$config_clear = Artisan::call('config:clear');

	$directory = '../storage/framework/sessions';
	$ignoreFiles = ['.gitignore', '.', '..'];
	$files = scandir($directory);
	foreach ($files as $file){ if(!in_array($file,$ignoreFiles)) unlink($directory . '/' . $file); }

	return
		"<h4>View cache cleared: ".print_r($view_clear,1)."</h4>".
		"<h4>Cache facade value cleared: ".print_r($cache_clear,1)."</h4>".
		"<h4>Config cached: $config_cache</h4>".
		"<h4>Clear Config cleared: $config_clear</h4>".
		"<h4>Session cleared: </h4>".getcwd().
		"<hr/>";
});
Route::get('/clear/optimize', function() {
	$optimize     = Artisan::call('optimize');
	$config_cache = Artisan::call('config:cache');
	$config_clear = Artisan::call('config:clear');

	return
		"<h4>Reoptimized class loader: $optimize</h4>".
		"<h4>Config cached: $config_cache</h4>".
		"<h4>Clear Config cleared: $config_clear</h4>".
		"<hr/>";
});

Route::get('/messages/upload/{jsonFile}', 'Api\Dashboard\BotMessage\BotMessageController@upload');

Route::group(['prefix' => '/logs'], function(){
	Route::get('/', function(){
		if(\Session('isLogin')==null || \Session('isLogin')==0){
			return redirect('/login');
		}else{ return view('logs/upload'); }
	});
	//----------------------------------------------------------------------------------------------
	Route::get('/load', function(){
		if(\Session('isLogin')==null || \Session('isLogin')==0){
			return null;
		}else{
			try{
				//--------------------------------------------------------------------------------------
				$conn = mysqli_connect( 'localhost', "kamadeikb_user", 'IdgfdvIg24cI9OA9', 'kamalogs' );
				mysqli_query($conn, "SET NAMES 'utf8'");
				//--------------------------------------------------------------------------------------
				$kama_usage_result = mysqli_query( $conn, "select * from kama_usage where send=0 limit 50");
				while( $kama_usage = mysqli_fetch_assoc( $kama_usage_result ) ){
					/*************************************************************************************/
					$kama_usage_insert = ["me"=>1];
					foreach($kama_usage as $key=>$val){
						if($key=='signin_id' || $key=='send'){ continue; }
						if($key=='email' || $key=='user_name'){ $kama_usage_insert[$key] = \Crypt::encryptString($val); }
						else{ $kama_usage_insert[$key] = $val; }
					}
					$signin_id = \App\KamaUsage::insertGetId($kama_usage_insert);
					mysqli_query( $conn, "update kama_usage set send=1 where signin_id={$kama_usage['signin_id']}");
					/*************************************************************************************/
					$kama_log_result = mysqli_query( $conn, "select * from kama_log where signin_id={$kama_usage['signin_id']}");
					while( $kama_log = mysqli_fetch_assoc( $kama_log_result ) ){
						$kama_log_insert = ["me"=>1, 'signin_id'=>$signin_id];
						foreach($kama_log as $key=>$val){
							if($key=='msg_id' || $key=='send' || $key=='signin_id'){ continue; }
							if($key=='raw_msg' || $key=='msg'){ $kama_log_insert[$key] = \Crypt::encryptString($val); }
							else{ $kama_log_insert[$key] = $val; }
						}
						\App\KamaLog::insert($kama_log_insert);
						mysqli_query( $conn, "update kama_log set send=1 where msg_id={$kama_log['msg_id']}");
					}
				}
				//--------------------------------------------------------------------------------------
				$uploaded = 0;
				$result = mysqli_query( $conn, "select count(*) as cnt from kama_usage where send=1");
				while( $row = mysqli_fetch_assoc( $result ) ){ $uploaded = $row['cnt']; }

				$remind = 0;
				$result = mysqli_query( $conn, "select count(*) as cnt from kama_usage where send=0");
				while( $row = mysqli_fetch_assoc( $result ) ){ $remind = $row['cnt']; }

				return ["result"=>0, "uploaded"=>number_format($uploaded,0,'',','), "remind"=>number_format($remind,0,'',','), 'end'=>$remind];
			}catch(\Throwable $ex){
				return ["result"=>1, "msg"=>$ex->getMessage()];
			}
		}
	});
	//----------------------------------------------------------------------------------------------
});

use App\Http\Controllers\ApiController;
use App\Http\Controllers\CollectionController;


Route::get('/drives', [ApiController::class, 'index'])->name('drives');
Route::get('/get-system-source-types', [ApiController::class, 'getSystemSourceTypes'])->name('get-system-source-types');
Route::post('/getcollections', [ApiController::class, 'getCollections'])->name('getcollections');
// Route::post('/getbucketitems', [ApiController::class, 'getBucketItems'])->name('getBucketItems');
Route::post('/getbucketitems', [ApiController::class, 'getBucketItems'])->name('getBucketItems');
Route::post('/getLocalCollections', [CollectionController::class, 'getLocalCollections'])->name('getLocalCollections');
Route::get('/getLocalBucketItems', [ApiController::class, 'getLocalBucketItems'])->name('getLocalBucketItems');
Route::post('/storeLocalIems', [CollectionController::class, 'storeLocalIems'])->name('storeLocalIems');
Route::post('/deleteLocalFile', [CollectionController::class, 'deleteLocalFile'])->name('deleteLocalFile');
Route::post('/updateLocalFiles', [CollectionController::class, 'updateLocalFiles'])->name('updateLocalFiles');
// Route::get('/s3buckets', [ApiController::class, 's3Bucket'])->name('s3bucket');
Route::get('/manage-collection', [ApiController::class, 'manageCollection'])->name('manage-collection');
Route::get('/s3details/{org_id}/{bucket_name}', [ApiController::class, 's3BucketObjects'])->name('s3bucket');
Route::get('/get-collections', [ApiController::class, 'getCollectionNew'])->name('get-collections');
Route::post('/publish-collection', [ApiController::class, 'publishCollection'])->name('publish-collection');
Route::post('/deleteCollection', [ApiController::class, 'deleteCollection'])->name('deleteCollection');
Route::post('/deleteLocalCollection', [CollectionController::class, 'destroy'])->name('deleteCollection');
Route::post('/rename-db-collection', [CollectionController::class, 'renameDbCollection'])->name('rename-db-collection');
Route::post('/copy-collection', [CollectionController::class, 'copyCollection'])->name('copy-collection');
Route::post('/update-collection-note', [CollectionController::class, 'updateCollectionNote'])->name('update-collection-note');
Route::post('/check-collection', [CollectionController::class, 'checkCollection'])->name('check-collection');


Route::prefix('collections')->group(function () {
    // Basic CRUD operations
    Route::get('/', [CollectionController::class, 'index']);           // Get all collections
    Route::post('/', [CollectionController::class, 'store']);          // Create a new collection
    Route::get('/{id}', [CollectionController::class, 'show']);        // Get a specific collection
    Route::put('/{id}', [CollectionController::class, 'update']);      // Update a specific collection
    // Delete a specific collection
});

use App\Http\Controllers\AuditLogController;


Route::get('/audit-logs/data', [AuditLogController::class, 'getData']);
