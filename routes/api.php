<?php
 use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/forgot_pass/", 'LoginController@forgotPassword');



// ----------------------------------------------------------------------------------------
// -- CHATBOX -----------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/chatbox'], function() {
		Route::get('/{userid}/{orgid}/{inquiry}', 'Api\Chatbox\ChatboxController@show');
		Route::any('/', 'Api\Chatbox\ChatboxController@newShow');

		Route::post('/termcheck', 'Api\Chatbox\TermController@termCheck');

		Route::post('/consumer_identify', 'Api\Chatbox\ConsumerController@consumerIdentify');
		Route::post('/consumer_register', 'Api\Chatbox\ConsumerController@consumerRegister');
	});

	Route::group(['prefix' => '/autocomplete'], function() {
		Route::post('/{callback?}', 'Api\Chatbox\AutocompleteController@index');
	});

	Route::group(['prefix' => '/organization'], function() {
		Route::get('/', 'Api\Dashboard\Organization\OrganizationController@allOrganization');
	});

	Route::group(['prefix' => '/words'], function() {
		Route::post('/'           , 'Api\Chatbox\TermController@termsList');
		Route::post('/term_insert', 'Api\Chatbox\TermController@insertTerms');
	});

});

// ----------------------------------------------------------------------------------------
// -- LARGEST INITIAL EXPRESION------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/largestIE'], function() {
		Route::any('/{orgid}/{inputText}/{apikey}/{userid}', 'Api\LargestIE\LargestIEController@getLargestIE');
		Route::post('/', 'Api\LargestIE\LargestIEController@getLargestIE');
	});
});

// ----------------------------------------------------------------------------------------
// -- MS BOT   ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/msBot'], function() {
	Route::post('/start', 'Api\MSBot\MSBotController@startConversation');
	Route::post('/post', 'Api\MSBot\MSBotController@postACtivities');
	Route::post('/get', 'Api\MSBot\MSBotController@getActivities');
	});
});



// ----------------------------------------------------------------------------------------
// -- RELATION TRIPLE          ------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/relationTriple'], function() {
		Route::any('/{orgid}/{limit}/{leftTerm}/{relationType}/{rightTerm}','Api\RelationTriple\RelationTripleController@getRelation');
		Route::post('/', 'Api\RelationTriple\RelationTripleController@getRelation');
	});
});


// ----------------------------------------------------------------------------------------
// -- RELATION TERM            ------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/relationTerm'], function() {
		Route::post('/{orgid}/{translation}/{krTermLink}','Api\RelationTerm\RelationTermController@getTerm');
		Route::post('/', 'Api\RelationTerm\RelationTermController@getTerm');
	});
});


// ----------------------------------------------------------------------------------------
// -- ENTERPRISE WORD          ------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/enterpriseWord'], function() {
		Route::post('/{orgid}/{translation}','Api\EnterpriseWord\enterpriseWordController@getWord');
		Route::post('/', 'Api\EnterpriseWord\EnterpriseWordController@getWord');
	});
});

// ----------------------------------------------------------------------------------------
// -- LEX ------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/lex'], function() {
		Route::any('/{userid}/{orgid}/{botName}/{botVersion}/{lexState}/{intentName}/{slotName}/{language}/{inquiry}', 'Api\Lex\LexController@newShow');
		Route::post('/', 'Api\Lex\LexController@newShow');
	});

});


// ----------------------------------------------------------------------------------------
// -- CHAT COMBINED CONTROLLER  -----------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/chat'], function() {
		Route::any('/{userid}/{orgid}/{state}/{botName}/{botVersion}/{botAlias}/{botState}/{intentName}/{slotName}/{language}/{inquiry}', 'Api\Chat\ChatController@newShow');
		Route::post('/', 'Api\Chat\ChatController@newShow');
	});

});


// ----------------------------------------------------------------------------------------
// -- CHAT CONTROLLER KAAS TESTER ----------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/kaaschat'], function() {
		Route::any('/{userid}/{orgid}/{state}/{botName}/{botVersion}/{botAlias}/{botState}/{intentName}/{slotName}/{language}/{inquiry}', 'Api\Chat\KaasChatController@newShow');
		Route::post('/', 'Api\Chat\KaasChatController@newShow');
	});

});


// ----------------------------------------------------------------------------------------
// -- KAAS ------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1'], function() {
	Route::group(['prefix' => '/kaas'], function() {
		Route::any('/{userid}/{orgid}/{state}/{botName}/{botVersion}/{botAlias}/{botState}/{intentName}/{slotName}/{language}/{inquiry}', 'Api\Chat\ChatController@newShow');
		Route::post('/', 'Api\Chat\ChatController@newShow');
	});

});


// ----------------------------------------------------------------------------------------
// -- dashboard/term ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/term'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'                  , 'Api\Dashboard\Term\TermController@showAll'      );
	Route::get('/all/{orgID}/{sort}/{order}'   , 'Api\Dashboard\Term\TermController@showAllSorted');
	Route::get('/tense/{orgID}/{sort}/{order}' , 'Api\Dashboard\Term\TermController@showAllTense' );
	Route::get('/values/{orgID}/{prsID}/{sort}/{order}', 'Api\Dashboard\Term\TermController@showAllValues');
	Route::get('/knowledgerecordValues/{orgID}/{prID}' , 'Api\Dashboard\Term\TermController@showValues'   );


	Route::get('/page/{orgID}/{perPage}/{page}', 'Api\Dashboard\Term\TermController@showPage'          );

	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}',
		'Api\Dashboard\Term\TermController@showPageSorted'
	);
	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}',
		'Api\Dashboard\Term\TermController@showPageSorted'
	);
	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
		'Api\Dashboard\Term\TermController@showPageSorted'
	);
	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/showglobal/{shwglblSTT}/{showAllType}',
		'Api\Dashboard\Term\TermController@showPageSorted'
	);
	
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}',
		'Api\Dashboard\Term\TermController@showPageSortSearch'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}',
		'Api\Dashboard\Term\TermController@showPageSortSearch'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
		'Api\Dashboard\Term\TermController@showPageSortSearch'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/showglobal/{shwglblSTT}/{showAllType}',
		'Api\Dashboard\Term\TermController@showPageSortSearch'
	);

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\Term\TermController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\Term\TermController@search');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\Term\TermController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\Term\TermController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Term\TermController@deleteRow');
	Route::delete('/delete/'            , 'Api\Dashboard\Term\TermController@deleteRows');

//	Route::get('/getid/{orgID}/{sort}/{order}/(pkgLen)/{id}', 'Api\Dashboard\Term\TermController@getTerms');
	Route::post('/getterms/{orgID}/{id}/{pkgLen}/{direction}', 'Api\Dashboard\Term\TermController@getTerms');

	Route::post('/gettermsaroundme/{orgID}/{id}/{pkgLen}/{direction}'                  , 'Api\Dashboard\Term\TermController@getTermsAroundMe');
	Route::post('/gettermsaroundme/{orgID}/{id}/{pkgLen}/{direction}/ownerId/{ownerId}', 'Api\Dashboard\Term\TermController@getTermsAroundMe');
	
	Route::post('/gettermsaroundme/{orgID}/{val}/{pkgLen}'                  , 'Api\Dashboard\Term\TermController@getTermsByVal');
	Route::post('/gettermsaroundme/{orgID}/{val}/{pkgLen}/ownerId/{ownerId}', 'Api\Dashboard\Term\TermController@getTermsByVal');

	Route::post('/getterms/{orgID}/{val}/{pkgLen}', 'Api\Dashboard\Term\TermController@getTermsByVal');
	
	Route::get('/termowners/{orgID}', 'Api\Dashboard\Term\TermController@termOwnersList');
	
	Route::get('/gettermtypes', 'Api\Dashboard\Term\TermController@getTermTypes');
	
});
// ----------------------------------------------------------------------------------------
// -- dashboard/relation ------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/relation'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'                , 'Api\Dashboard\Relation\RelationController@showAll'       );
	Route::get('/all/{orgID}/{sort}/{order}' , 'Api\Dashboard\Relation\RelationController@showAllSorted' );

	Route::get('/knowledgerecords/{orgID}/{prsnaID}', 'Api\Dashboard\Relation\RelationController@allKnowledgeRecords');
	Route::get(
		'/knowledgerecordswithowner/{orgID}/{prsnaID}/{ownrID}',
		'Api\Dashboard\Relation\RelationController@allKnowledgeRecordsWithOwner'
	);
	Route::get(
		'/knowledgerecordswithowner/{orgID}/{prsnaID}/{ownrID}/{showGlobal}',
		'Api\Dashboard\Relation\RelationController@allKnowledgeRecordsWithOwner'
	);

	Route::get('/page/{orgID}/{perPage}/{page}', 'Api\Dashboard\Relation\RelationController@showPage'          );

	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}'                  , 'Api\Dashboard\Relation\RelationController@showPageSorted');
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}', 'Api\Dashboard\Relation\RelationController@showPageSorted');
	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
		'Api\Dashboard\Relation\RelationController@showPageSorted'
	);

	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\Relation\RelationController@showPageSortSearch');
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}',
		'Api\Dashboard\Relation\RelationController@showPageSortSearch'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
		'Api\Dashboard\Relation\RelationController@showPageSortSearch'
	);

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\Relation\RelationController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\Relation\RelationController@search');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\Relation\RelationController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\Relation\RelationController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Relation\RelationController@deleteRow');
	Route::delete('/delete/'            , 'Api\Dashboard\Relation\RelationController@deleteRows');

	Route::get('/relationowners/{orgID}', 'Api\Dashboard\Relation\RelationController@getOwnersList');
	
	Route::get(
		'/relation_language/{relationId}/{orgID}/{code}',
		'Api\Dashboard\Relation\RelationController@getRelationLanguage'
	);
	Route::put(
		'/relation_language',
		'Api\Dashboard\Relation\RelationController@setRelationLanguage'
	);
});
// ----------------------------------------------------------------------------------------
// -- dashboard/relation_synonym ----------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/relation_type_synonym'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'                , 'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@showAll'       );
	Route::get('/all/{orgID}/{sort}/{order}' , 'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@showAllSorted' );

	Route::get('/page/{orgID}/{perPage}/{page}'                          , 'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@showPage'          );

	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}',
		'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@showPageSorted'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}',
		'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@showPageSortSearch'
	);

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@search');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@deleteRow');

	Route::get('/relationtypesynonymowners/{orgID}', 'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@getOwnersList');

	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
		'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@showPageSorted'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
		'Api\Dashboard\RelationTypeSynonym\RelationTypeSynonymController@showPageSortSearch'
	);
});
// ----------------------------------------------------------------------------------------
// -- dashboard/relation_type -------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/relation_type'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'                , 'Api\Dashboard\RelationType\RelationTypeController@showAll'       );
	Route::get('/all/{orgID}/{sort}/{order}' , 'Api\Dashboard\RelationType\RelationTypeController@showAllSorted' );

	Route::get('/page/{orgID}/{perPage}/{page}'                          , 'Api\Dashboard\RelationType\RelationTypeController@showPage'          );
	
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}'           , 'Api\Dashboard\RelationType\RelationTypeController@showPageSorted'    );
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}', 'Api\Dashboard\RelationType\RelationTypeController@showPageSortSearch');

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\RelationType\RelationTypeController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\RelationType\RelationTypeController@search');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\RelationType\RelationTypeController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\RelationType\RelationTypeController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\RelationType\RelationTypeController@deleteRow');

	Route::get('/relationtypeowners/{orgID}', 'Api\Dashboard\RelationType\RelationTypeController@getOwnersList');

	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
		'Api\Dashboard\RelationType\RelationTypeController@showPageSorted'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
		'Api\Dashboard\RelationType\RelationTypeController@showPageSortSearch'
	);
});
// ----------------------------------------------------------------------------------------
// -- dashboard/relation_link -------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/relation_link'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'                , 'Api\Dashboard\RelationLink\RelationLinkController@showAll'       );
	Route::get('/all/{orgID}/{sort}/{order}' , 'Api\Dashboard\RelationLink\RelationLinkController@showAllSorted' );
	Route::get('/term/{orgID}'               , 'Api\Dashboard\RelationLink\RelationLinkController@showTerms'     );

	Route::get('/page/{orgID}/{perPage}/{page}', 'Api\Dashboard\RelationLink\RelationLinkController@showPage');

	Route::get(
				'/page/{orgID}/{sort}/{order}/{perPage}/{page}',
				'Api\Dashboard\RelationLink\RelationLinkController@showPageSorted'
			);
	Route::get(
				'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}',
				'Api\Dashboard\RelationLink\RelationLinkController@showPageSorted'
			);
			
	Route::get(
				'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}',
				'Api\Dashboard\RelationLink\RelationLinkController@showPageSortSearch'
			);
	Route::get(
				'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}',
				'Api\Dashboard\RelationLink\RelationLinkController@showPageSortSearch'
			);

	Route::get('/alllinkleft/{orgID}/{llkrID}', 'Api\Dashboard\RelationLink\RelationLinkController@allLinkLeft' );

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\RelationLink\RelationLinkController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\RelationLink\RelationLinkController@search');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\RelationLink\RelationLinkController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\RelationLink\RelationLinkController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\RelationLink\RelationLinkController@deleteRow');
	
	Route::get('/relationlinkowners/{orgID}', 'Api\Dashboard\RelationLink\RelationLinkController@getOwnersList');
	
	Route::get(
				'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
				'Api\Dashboard\RelationLink\RelationLinkController@showPageSorted'
			);
	Route::get(
				'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
				'Api\Dashboard\RelationLink\RelationLinkController@showPageSortSearch'
			);

	Route::put('/reorder', 'Api\Dashboard\RelationLink\RelationLinkController@reOrder');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/persona -------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/persona'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'                , 'Api\Dashboard\Personality\PersonalityController@showAll'       );
	Route::get('/all/{orgID}/{sort}/{order}' , 'Api\Dashboard\Personality\PersonalityController@showAllSorted' );

	Route::get('/page/{orgID}/{perPage}/{page}', 'Api\Dashboard\Personality\PersonalityController@showPage');
	
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}'                  , 'Api\Dashboard\Personality\PersonalityController@showPageSorted');
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}', 'Api\Dashboard\Personality\PersonalityController@showPageSorted');

	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}'                  , 'Api\Dashboard\Personality\PersonalityController@showPageSortSearch');
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}', 'Api\Dashboard\Personality\PersonalityController@showPageSortSearch');

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\Personality\PersonalityController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\Personality\PersonalityController@search');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\Personality\PersonalityController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\Personality\PersonalityController@insertRow');
	Route::put('/clone'            , 'Api\Dashboard\Personality\PersonalityController@clonePersona');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Personality\PersonalityController@deleteRow');

	Route::get('/personaowners/{orgID}', 'Api\Dashboard\Personality\PersonalityController@getPersonaOwnersList');

	Route::get(
				'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
				'Api\Dashboard\Personality\PersonalityController@showPageSorted'
			);
	Route::get(
				'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
				'Api\Dashboard\Personality\PersonalityController@showPageSortSearch'
			);
	Route::get(
				'/getpersonalitiesofpersona/{orgID}/{personaID}',
				'Api\Dashboard\Personality\PersonalityController@getPersonalitiesOfPersona'
	);
});
// ----------------------------------------------------------------------------------------
// -- dashboard/personality ---------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/personality'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/find/{id}'                      , 'Api\Dashboard\Personality\PersonalityController@find'         );
	Route::get('/all/{orgID}'                    , 'Api\Dashboard\Personality\PersonalityController@showAll'      );
	Route::get('/all/{orgID}/{sort}/{order}'     , 'Api\Dashboard\Personality\PersonalityController@showAllSorted');

	Route::get(
		'/allPersonality/{orgID}/{ownerID}/{sort}/{order}/',
		'Api\Dashboard\Personality\PersonalityController@allPersonality'
	);
	Route::get(
		'/allPersonality/{orgID}/{ownerID}/{sort}/{order}/{search}',
		'Api\Dashboard\Personality\PersonalityController@allPersonality'
	);
	Route::get(
		'/zeroPersonality/{orgID}/{ownerID}/{sort}/{order}',
		'Api\Dashboard\Personality\PersonalityController@zeroPersonality'
	);
	Route::get(
		'/zeroPersonality/{orgID}/{ownerID}/{sort}/{order}/{search}',
		'Api\Dashboard\Personality\PersonalityController@zeroPersonality'
	);
	Route::get(
		'/nonzeroPersonality/{orgID}/{ownerID}/{sort}/{order}/',
		'Api\Dashboard\Personality\PersonalityController@nonzeroPersonality'
	);
	Route::get(
		'/nonzeroPersonality/{orgID}/{ownerID}/{sort}/{order}/{search}',
		'Api\Dashboard\Personality\PersonalityController@nonzeroPersonality'
	);

	Route::get('/parents/{orgID}' , 'Api\Dashboard\Personality\PersonalityController@showAllParents' );

	Route::get('/page/{orgID}/{perPage}/{page}' , 'Api\Dashboard\Personality\PersonalityController@showPage');
	
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}'                  , 'Api\Dashboard\Personality\PersonalityController@showPageSorted'    );
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}', 'Api\Dashboard\Personality\PersonalityController@showPageSorted'    );

	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\Personality\PersonalityController@showPageSortSearch');
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}', 'Api\Dashboard\Personality\PersonalityController@showPageSortSearch');

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\Personality\PersonalityController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\Personality\PersonalityController@search');

	Route::get('/getuserdate/{uID}', 'Api\Dashboard\Personality\PersonalityController@getUserDate');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\Personality\PersonalityController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\Personality\PersonalityController@insertRow');
	Route::put('/clone'            , 'Api\Dashboard\Personality\PersonalityController@clonePersonality');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Personality\PersonalityController@deleteRow');

	Route::get('/personalityowners/{orgID}', 'Api\Dashboard\Personality\PersonalityController@getPersonalityOwnersList');

	Route::get(
				'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
				'Api\Dashboard\Personality\PersonalityController@showPageSorted'
			);
	Route::get(
				'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
				'Api\Dashboard\Personality\PersonalityController@showPageSortSearch'
			);
});
// ----------------------------------------------------------------------------------------
// -- dashboard/personality_value ---------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/personality_value'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'                , 'Api\Dashboard\PersonalityValue\PersonalityValueController@showAll'       );
	Route::get('/all/{orgID}/{sort}/{order}' , 'Api\Dashboard\PersonalityValue\PersonalityValueController@showAllSorted' );

	Route::get('/page/{orgID}/{prsnltyID}/{ownerID}/{perPage}/{page}'                          ,
																					'Api\Dashboard\PersonalityValue\PersonalityValueController@showPage'          );
	Route::get('/page/{orgID}/{prsnltyID}/{ownerID}/{sort}/{order}/{perPage}/{page}'           ,
																					'Api\Dashboard\PersonalityValue\PersonalityValueController@showPageSorted'    );
	Route::get('/{orgID}/{prsnltyID}/{ownerID}/{sort}/{order}/{perPage}/{page}/{field}/{value}',
																					'Api\Dashboard\PersonalityValue\PersonalityValueController@showPageSortSearch');

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\PersonalityValue\PersonalityValueController@show'  );
	Route::get('/search/{orgID}/{prsnltyID}/{field}/{value}', 'Api\Dashboard\PersonalityValue\PersonalityValueController@search');

	Route::put('/edit/{orgID}/{prsnltyID}/{reservd}/{id}', 'Api\Dashboard\PersonalityValue\PersonalityValueController@newEditRow'     );
	Route::put('/edit/{orgID}/{prsnltyID}/{id}'          , 'Api\Dashboard\PersonalityValue\PersonalityValueController@editRow'        );
	Route::put('/new/{orgID}/{prsnltyID}/{reservd}'      , 'Api\Dashboard\PersonalityValue\PersonalityValueController@newInsertRow'   );
	Route::put('/new/{orgID}/{prsnltyID}/'               , 'Api\Dashboard\PersonalityValue\PersonalityValueController@insertRow'      );
	Route::put('/scalarvalue/{orgID}/{id}'               , 'Api\Dashboard\PersonalityValue\PersonalityValueController@editScalarValue');

	Route::delete('/delete/{orgID}/{prsnltyID}/{id}'          , 'Api\Dashboard\PersonalityValue\PersonalityValueController@deleteRow'   );
	Route::delete('/delete/{orgID}/{prsnltyID}/{reservd}/{id}', 'Api\Dashboard\PersonalityValue\PersonalityValueController@newDeleteRow');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/user ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/user'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'               , 'Api\Dashboard\User\UserController@showAll'       );
	Route::get('/all/{orgID}/{sort}/{order}', 'Api\Dashboard\User\UserController@showAllSorted' );

	Route::get('/page/{orgID}/{perPage}/{page}', 'Api\Dashboard\User\UserController@showPage');

	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}'                  , 'Api\Dashboard\User\UserController@showPageSorted');
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}', 'Api\Dashboard\User\UserController@showPageSorted');
	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/level/{level}',
		'Api\Dashboard\User\UserController@showPageSorted'
	);

	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\User\UserController@showPageSortSearch');
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}',
		'Api\Dashboard\User\UserController@showPageSortSearch'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/level/{level}',
		'Api\Dashboard\User\UserController@showPageSortSearch'
	);

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\User\UserController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\User\UserController@search');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\User\UserController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\User\UserController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\User\UserController@deleteRow');

	Route::post('/reset/{id}', 'Api\Dashboard\User\UserController@resetPass'  );

	Route::get('/myowners/{orgID}', 'Api\Dashboard\User\UserController@getOwnersList');
	Route::get('/mylevels/{orgID}', 'Api\Dashboard\User\UserController@getLevelsList');
});

// ----------------------------------------------------------------------------------------
// -- panel/settings ---------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'panel/settings'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::post('/edit/{orgID}/{id}'               , 'Api\Dashboard\User\UserController@editRow'       );
});

// ----------------------------------------------------------------------------------------
// -- dashboard/level ---------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/level'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}' , 'Api\Dashboard\Level\LevelController@listLevel');
	Route::get('/list/{orgID}', 'Api\Dashboard\Level\LevelController@allLevels');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\Level\LevelController@editRow');
	Route::put('/new/{orgID}', 'Api\Dashboard\Level\LevelController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Level\LevelController@deleteRow');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/organization --------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/organization'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');
    Route::get('/get/language'         , 'Api\Dashboard\Organization\OrganizationController@allLanguage');
    Route::get('/get/language/{org_id}', 'Api\Dashboard\Organization\OrganizationController@allLanguage');


	Route::get('/all/{orgID}', 'Api\Dashboard\Organization\OrganizationController@listOrganization');
	Route::get('/isKaasActive/{orgID}', 'Api\Dashboard\Organization\OrganizationController@isKaasActive');

	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}'           , 'Api\Dashboard\Organization\OrganizationController@showPageSorted'    );
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\Organization\OrganizationController@showPageSortSearch');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\Organization\OrganizationController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\Organization\OrganizationController@insertRow');
	Route::put('/setdefaultpersona/', 'Api\Dashboard\Organization\OrganizationController@setDefaultPersona');

	Route::delete('/delete/{orgID}/{id}','Api\Dashboard\Organization\OrganizationController@deleteRow');
	
	Route::post('/uploadlogo/','Api\Dashboard\Organization\OrganizationController@uploadLogo');
	
});
// ----------------------------------------------------------------------------------------
// -- dashboard/pages ---------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/pages'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'               , 'Api\Dashboard\SitePage\SitePageController@showAll'       );
	Route::get('/all/{orgID}/{sort}/{order}', 'Api\Dashboard\SitePage\SitePageController@showAllSorted' );

	Route::get('/page/{orgID}/{perPage}/{page}'                          , 'Api\Dashboard\SitePage\SitePageController@showPage'          );
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}'           , 'Api\Dashboard\SitePage\SitePageController@showPageSorted'    );
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\SitePage\SitePageController@showPageSortSearch');

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\SitePage\SitePageController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\SitePage\SitePageController@search');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\SitePage\SitePageController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\SitePage\SitePageController@insertRow');

	Route::put('/create/{orgID}/{pageid}/{levelid}',   'Api\Dashboard\SitePage\SitePageController@createRow');
	Route::delete('/delete/{orgID}/{pageid}/{levelid}','Api\Dashboard\SitePage\SitePageController@deleteRow');
});
Route::group(['prefix' => 'dashboard/security'], function() {
    header('Access-Control-Allow-Credentials: true');

	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}',
		'Api\Dashboard\SitePage\SitePageController@showAllMenuItems'
	);
});
// ----------------------------------------------------------------------------------------
// -- dashboard/relationLink --------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/relationlink'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/all/{orgID}'                , 'Api\Dashboard\RelationLink\RelationLinkController@showAll'       );
	Route::get('/all/{orgID}/{sort}/{order}' , 'Api\Dashboard\RelationLink\RelationLinkController@showAllSorted' );

	Route::get('/page/{orgID}/{perPage}/{page}'                          , 'Api\Dashboard\RelationLink\RelationLinkController@showPage'          );
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}'           , 'Api\Dashboard\RelationLink\RelationLinkController@showPageSorted'    );
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\RelationLink\RelationLinkController@showPageSortSearch');

	Route::get('/get/{orgID}/{id}'              , 'Api\Dashboard\RelationLink\RelationLinkController@show'  );
	Route::get('/search/{orgID}/{field}/{value}', 'Api\Dashboard\RelationLink\RelationLinkController@search');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\RelationLink\RelationLinkController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\RelationLink\RelationLinkController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\RelationLink\RelationLinkController@deleteRow');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/personality_relation_value ------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/personality_relation_value'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::post(
		'/all/{orgID}/{ownrID}/{prsID}',
		'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@showAll'
	);
	Route::post(
		'/all/{orgID}/{ownrID}/{prsID}/{showProblemsSolutions}', 'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@showAll'
	);
	Route::post(
		'/all/{orgID}/{ownrID}/{prsID}/{showProblemsSolutions}/{oldParentID}', 'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@showAll'
	);
	Route::post(
		'/allValue/{orgID}/{personalityRelationId}',
		'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@showAllValue'
	);
	Route::put('/knowledgeRecord'                          , 'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@addKnowledgeRecord');
	Route::put('/scalarvalue/{id}'                         , 'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@editScalarValue'   );
	Route::put('/create'                                   , 'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@createValue'       );

	Route::delete(
		'/knowledgeRecord/{orgID}/{id}', 
		'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@eraseKnowledgeRecord_'
	);
	Route::delete(
		'/knowledgeRecord/{orgID}/{id}/{andPersonalities}/{erasePersonalities}',
		'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@eraseKnowledgeRecord'
	);
	Route::delete('/knowledgeRecordValue/{orgID}/{id}', 'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@eraseKnowledgeRecordValue');

	Route::post('/copyKRs/{rID}'      , 'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@copyKRs');
	Route::post('/getcopytoorgs/{rID}', 'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@getCopyToOrgs');
	Route::post('/getkrcaption/{ID}'  , 'Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController@getKRcaption');
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// -- attribute_type ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extend/attribute_type'], function() {
	//header("Content-Type: application/json; charset=UTF-8"                                  );
	//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"   );
	//header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS"                      );
	//header("Access-Control-Allow-Origin: *"                                                 );
	header('Access-Control-Allow-Credentials: true'                                         );
	
	Route::get('/all/{orgID}'                                            , 'Api\Extend\Extended_attribute_type\AttributetypeController@showAll'           );
	Route::get('/all/{orgID}/{sort}/{order}'                             , 'Api\Extend\Extended_attribute_type\AttributetypeController@showAllSorted'     );
	
	Route::get('/page/{orgID}/{perPage}/{page}'                          , 'Api\Extend\Extended_attribute_type\AttributetypeController@showPage'          );
	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}'           , 'Api\Extend\Extended_attribute_type\AttributetypeController@showPageSorted'    );
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Extend\Extended_attribute_type\AttributetypeController@showPageSortSearch');
	
	Route::get('/get/{orgID}/{id}'                                       , 'Api\Extend\Extended_attribute_type\AttributetypeController@show'              );
	Route::get('/search/{orgID}/{field}/{value}'                         , 'Api\Extend\Extended_attribute_type\AttributetypeController@search'            );
	
	Route::put('/edit/{orgID}/{id}'                                      , 'Api\Extend\Extended_attribute_type\AttributetypeController@editRow'           );
	Route::put('/new/{orgID}'                                            , 'Api\Extend\Extended_attribute_type\AttributetypeController@insertRow'         );
	Route::delete('/delete/{orgID}/{id}'                                 , 'Api\Extend\Extended_attribute_type\AttributetypeController@deleteRow'         );
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// -- extended_type ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extend/extended_type'], function() {
	//header("Content-Type: application/json; charset=UTF-8"                                  );
	//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"   );
	//header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS"                      );
	//header("Access-Control-Allow-Origin: *"                                                 );
	header('Access-Control-Allow-Credentials: true'                                         );
	
	Route::get('/all/{termId}/{orgID}'                                            , 'Api\Extend\Extended_type\ExtendedtypeController@showAll'           );
	Route::get('/all/{termId}/{orgID}/{sort}/{order}'                             , 'Api\Extend\Extended_type\ExtendedtypeController@showAllSorted'     );
	
	Route::get('/page/{termId}/{orgID}/{perPage}/{page}'                          , 'Api\Extend\Extended_type\ExtendedtypeController@showPage'          );
	Route::get('/page/{termId}/{orgID}/{sort}/{order}/{perPage}/{page}'           , 'Api\Extend\Extended_type\ExtendedtypeController@showPageSorted'    );
	Route::get('/{termId}/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Extend\Extended_type\ExtendedtypeController@showPageSortSearch');
	
	Route::get('/get/{termId}/{orgID}/{id}'                                       , 'Api\Extend\Extended_type\ExtendedtypeController@show'              );
	Route::get('/search/{termId}/{orgID}/{field}/{value}'                         , 'Api\Extend\Extended_type\ExtendedtypeController@search'            );
	
	Route::put('/edit/{orgID}/{id}'                                      , 'Api\Extend\Extended_type\ExtendedtypeController@editRow'           );
	Route::put('/new/{orgID}'                                            , 'Api\Extend\Extended_type\ExtendedtypeController@insertRow'         );
	Route::delete('/delete/{orgID}/{id}'                                 , 'Api\Extend\Extended_type\ExtendedtypeController@deleteRow'         );
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// -- subtype ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extend/extended_subtype'], function() {
	//header("Content-Type: application/json; charset=UTF-8"                                  );
	//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"   );
	//header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS"                      );
	//header("Access-Control-Allow-Origin: *"                                                 );
	header('Access-Control-Allow-Credentials: true'                                         );
	
	Route::get('/all/{typeID}/{orgID}'                                            , 'Api\Extend\Extended_subtype\ExtendedsubtypeController@showAll'           );
	Route::get('/all/{typeID}/{orgID}/{sort}/{order}'                             , 'Api\Extend\Extended_subtype\ExtendedsubtypeController@showAllSorted'     );
	
	Route::get('/page/{typeID}/{orgID}/{perPage}/{page}'                          , 'Api\Extend\Extended_subtype\ExtendedsubtypeController@showPage'          );

	Route::get(
		'/page/{typeID}/{orgID}/{sort}/{order}/{perPage}/{page}',
		'Api\Extend\Extended_subtype\ExtendedsubtypeController@showPageSorted'
	);
	Route::get(
		'/page/{typeID}/{orgID}/{sort}/{order}/{perPage}/{page}/showglobal/{showGlobal}',
		'Api\Extend\Extended_subtype\ExtendedsubtypeController@showPageSorted'
	);
	Route::get(
		'/{typeID}/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}',
		'Api\Extend\Extended_subtype\ExtendedsubtypeController@showPageSortSearch'
	);
	Route::get(
		'/{typeID}/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/showglobal/{showGlobal}',
		'Api\Extend\Extended_subtype\ExtendedsubtypeController@showPageSortSearch'
	);
	
	Route::get('/get/{typeID}/{orgID}/{id}'                                       , 'Api\Extend\Extended_subtype\ExtendedsubtypeController@show'              );
	Route::get('/search/{typeID}/{orgID}/{field}/{value}'                         , 'Api\Extend\Extended_subtype\ExtendedsubtypeController@search'            );
	
	Route::put('/edit/{orgID}/{id}'                                      , 'Api\Extend\Extended_subtype\ExtendedsubtypeController@editRow'           );
	Route::put('/copy/{orgID}/{id}'                                      , 'Api\Extend\Extended_subtype\ExtendedsubtypeController@copyRow'           );
	Route::put('/new/{orgID}'                                            , 'Api\Extend\Extended_subtype\ExtendedsubtypeController@insertRow'         );
	Route::delete('/delete/{orgID}/{id}'                                 , 'Api\Extend\Extended_subtype\ExtendedsubtypeController@deleteRow'         );
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// -- attribute ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extend/extended_attribute'], function() {
	//header("Content-Type: application/json; charset=UTF-8"                                  );
	//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"   );
	//header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS"                      );
	//header("Access-Control-Allow-Origin: *"                                                 );
	header('Access-Control-Allow-Credentials: true'                                         );
	
	Route::get('/all/{attributetypeID}/{subtypeID}/{orgID}', 'Api\Extend\Extended_attribute\AttributeController@showAll');
	Route::get('/all/{attributetypeID}/{subtypeID}/{orgID}/{sort}/{order}', 'Api\Extend\Extended_attribute\AttributeController@showAllSorted');
	
	Route::get('/page/{attributetypeID}/{subtypeID}/{orgID}/{perPage}/{page}', 'Api\Extend\Extended_attribute\AttributeController@showPage');
	Route::get('/page/{attributetypeID}/{subtypeID}/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Extend\Extended_attribute\AttributeController@showPageSorted');
	Route::get(
		'/{attributetypeID}/{subtypeID}/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 
		'Api\Extend\Extended_attribute\AttributeController@showPageSortSearch'
	);
	
	Route::get('/get/{attributetypeID}/{subtypeID}/{orgID}/{id}', 'Api\Extend\Extended_attribute\AttributeController@show');
	Route::get('/search/{attributetypeID}/{subtypeID}/{orgID}/{field}/{value}', 'Api\Extend\Extended_attribute\AttributeController@search');
	
	Route::put('/edit/{orgID}/{id}', 'Api\Extend\Extended_attribute\AttributeController@editRow');
	Route::put('/new/{orgID}', 'Api\Extend\Extended_attribute\AttributeController@insertRow');
	Route::delete('/delete/{orgID}/{id}', 'Api\Extend\Extended_attribute\AttributeController@deleteRow');
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// -- ExtendedEntity ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extend/extended_entity'], function() {
	//header("Content-Type: application/json; charset=UTF-8"                                  );
	//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"   );
	//header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS"                      );
	//header("Access-Control-Allow-Origin: *"                                                 );
	header('Access-Control-Allow-Credentials: true'                                         );
	
	Route::get('/all/{subtypeID}/{orgID}', 'Api\Extend\Extended_entity\ExtendedEntityController@showAll');
	Route::get('/all/{subtypeID}/{orgID}/{sort}/{order}', 'Api\Extend\Extended_entity\ExtendedEntityController@showAllSorted');
	
	Route::get('/page/{subtypeID}/{orgID}/{perPage}/{page}', 'Api\Extend\Extended_entity\ExtendedEntityController@showPage');
	Route::get(
		'/page/{subtypeID}/{orgID}/{sort}/{order}/{perPage}/{page}',
		'Api\Extend\Extended_entity\ExtendedEntityController@showPageSorted'
	);
	Route::get(
		'/page/{subtypeID}/{orgID}/{sort}/{order}/{perPage}/{page}/showglobal/{showGlobal}',
		'Api\Extend\Extended_entity\ExtendedEntityController@showPageSorted'
	);
	Route::get(
		'/{subtypeID}/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}',
		'Api\Extend\Extended_entity\ExtendedEntityController@showPageSortSearch'
	);
	Route::get(
		'/{subtypeID}/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/showglobal/{showGlobal}',
		'Api\Extend\Extended_entity\ExtendedEntityController@showPageSortSearch'
	);
	
	Route::get('/get/{subtypeID}/{orgID}/{id}', 'Api\Extend\Extended_entity\ExtendedEntityController@show');
	Route::get('/search/{subtypeID}/{orgID}/{field}/{value}', 'Api\Extend\Extended_entity\ExtendedEntityController@search');

	Route::get('/{extendedEntityId}/langs', 'Api\Extend\Extended_entity\ExtendedEntityController@otherLang');
	
	Route::put('/edit/{orgID}/{id}', 'Api\Extend\Extended_entity\ExtendedEntityController@editRow');
	Route::put('/edit', 'Api\Extend\Extended_EAV\ExtendedEAVController@editRow');
	Route::put('/new/{orgID}', 'Api\Extend\Extended_entity\ExtendedEntityController@insertRow');
	Route::delete('/delete/{orgID}/{id}', 'Api\Extend\Extended_entity\ExtendedEntityController@deleteRow');
	Route::delete('/delete/{orgID}/{id}/{lang}', 'Api\Extend\Extended_EAV\ExtendedEAVController@deleteRow');
    //Route::post('/upolder', 'Api\Extend\Extended_entity\ExtendedEntityController@upolder');
	Route::put('/draft', 'Api\Extend\Extended_EAV\ExtendedEAVController@draft');
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// -- eav ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extend/eav'], function() {
	//header("Content-Type: application/json; charset=UTF-8"                                  );
	//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"   );
	//header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS"                      );
	//header("Access-Control-Allow-Origin: *"                                                 );
	header('Access-Control-Allow-Credentials: true'                                         );
	
	Route::get('/all/{extendedEntityId}/{extendedAttributeId}/{orgID}', 'Api\Extend\Extended_EAV\ExtendedEAVController@showAll');
	Route::get('/all/{extendedEntityId}/{extendedAttributeId}/{orgID}/{sort}/{order}', 'Api\Extend\Extended_EAV\ExtendedEAVController@showAllSorted');
	
	Route::get('/page/{extendedEntityId}/{extendedAttributeId}/{orgID}/{perPage}/{page}', 'Api\Extend\Extended_EAV\ExtendedEAVController@showPage');
	Route::get(
		'/page/{extendedEntityId}/{extendedAttributeId}/{orgID}/{sort}/{order}/{perPage}/{page}', 
		'Api\Extend\Extended_EAV\ExtendedEAVController@showPageSorted'
	);
	Route::get(
		'/{extendedEntityId}/{extendedAttributeId}/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 
		'Api\Extend\Extended_EAV\ExtendedEAVController@showPageSortSearch'
	);
	
	Route::get('/get/{extendedEntityId}/{extendedAttributeId}/{orgID}/{id}', 'Api\Extend\Extended_EAV\ExtendedEAVController@show');
	Route::get('/search/{extendedEntityId}/{extendedAttributeId}/{orgID}/{field}/{value}', 'Api\Extend\Extended_EAV\ExtendedEAVController@search');
	
	Route::put('/edit', 'Api\Extend\Extended_EAV\ExtendedEAVController@editRow');
	Route::put('/new/{orgID}', 'Api\Extend\Extended_EAV\ExtendedEAVController@insertRow');
	Route::delete('/delete/{orgID}/{id}', 'Api\Extend\Extended_EAV\ExtendedEAVController@deleteRow');
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------


// ----------------------------------------------------------------------------------------
// -- ExtendedDataView ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extend/extendeddataview'], function() {
	//header("Content-Type: application/json; charset=UTF-8"                                  );
	//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"   );
	//header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS"                      );
	//header("Access-Control-Allow-Origin: *"                                                 );
	header('Access-Control-Allow-Credentials: true'                                         );
	
	Route::get('/all/{extendedEntityId}/{lang}', 'Api\Extend\Extended_Data_View\ExtendedDataViewController@showAll');
	Route::post('/notes/{extendedEntityId}'     , 'Api\Extend\Extended_Data_View\ExtendedDataViewController@allNotes');
});

// ----------------------------------------------------------------------------------------
// -- extended_type ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extend/extended_link'], function() {
	//header("Content-Type: application/json; charset=UTF-8"                                  );
	//header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"   );
	//header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS"                      );
	//header("Access-Control-Allow-Origin: *"                                                 );
	header('Access-Control-Allow-Credentials: true'                                         );
	
	Route::get('/all/{entityId}/{parentTable}/{parentId}/{orgID}', 'Api\Extend\Extended_link\ExtendedLinkController@showAll');
	Route::get('/all/{entityId}/{parentTable}/{parentId}/{orgID}/{sort}/{order}', 'Api\Extend\Extended_link\ExtendedLinkController@showAllSorted');
	
	Route::get(
		'/page/{entityId}/{parentTable}/{parentId}/{orgID}/{perPage}/{page}',
		'Api\Extend\Extended_link\ExtendedLinkController@showPage'
	);
	Route::get(
		'/page/{entityId}/{parentTable}/{parentId}/{orgID}/{sort}/{order}/{perPage}/{page}',
		'Api\Extend\Extended_link\ExtendedLinkController@showPageSorted2'
	);
	Route::get(
		'/page/{entityId}/{parentTable}/{parentId}/{orgID}/{sort}/{order}/{perPage}/{page}/showglobal/{showglobal}',
		'Api\Extend\Extended_link\ExtendedLinkController@showPageSorted2'
	);
    Route::get(
        '/page/{entityId}/{parentTable}/{parentId}/{orgID}/{sort}/{order}/{perPage}/{page}/{searc}',
        'Api\Extend\Extended_link\ExtendedLinkController@showPageSorted'
    );
    Route::get(
        '/page/{entityId}/{parentTable}/{parentId}/{orgID}/{sort}/{order}/{perPage}/{page}/{searc}/showglobal/{showglobal}',
        'Api\Extend\Extended_link\ExtendedLinkController@showPageSorted'
    );
    /*Route::get(
        '/{entityId}/{parentTable}/{parentId}/{orgID}/{sort}/{order}/{perPage}/{page}/{searc}',
        'Api\Extend\Extended_link\ExtendedLinkController@showPageSorted'
    );*/
	
	Route::get('/get/{entityId}/{parentTable}/{parentId}/{orgID}/{id}', 'Api\Extend\Extended_link\ExtendedLinkController@show');
	Route::put('/edit/{orgID}/{id}', 'Api\Extend\Extended_link\ExtendedLinkController@editRow');
	Route::put('/new/{orgID}', 'Api\Extend\Extended_link\ExtendedLinkController@insertRow');
	Route::delete('/delete/{orgID}/{id}', 'Api\Extend\Extended_link\ExtendedLinkController@deleteRow');
    Route::post('/upolder', 'Api\Extend\Extended_link\ExtendedLinkController@upolder');

	Route::get('/translation/{itemId}/{orgId}/{langCode}', 'Api\Extend\Extended_link\ExtendedLinkController@getTranslation');
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

Route::post('upload_action', 'Api\Extend\Extended_upload\UploadFileController@upload_action');
Route::get('get_upload_file/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}/{seven?}/{eight?}/{nine?}',
    'Api\Extend\Extended_upload\GetFileController@fileStorageRoute');

// ----------------------------------------------------------------------------------------
// -- dashboard/org_relation_type ---------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/org_relation_type'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Dashboard\OrgRelationType\OrgRelationTypeController@showPageSorted'    );
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\OrgRelationType\OrgRelationTypeController@showPageSortSearch');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\OrgRelationType\OrgRelationTypeController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\OrgRelationType\OrgRelationTypeController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\OrgRelationType\OrgRelationTypeController@deleteRow');

	Route::get('/all/{orgID}', 'Api\Dashboard\OrgRelationType\OrgRelationTypeController@showAll');
});

// ----------------------------------------------------------------------------------------
// -- dashboard/organization_association --------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/organization_association'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Dashboard\OrganizationAssociation\OrganizationAssociationController@showPageSorted'    );
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\OrganizationAssociation\OrganizationAssociationController@showPageSortSearch');

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\OrganizationAssociation\OrganizationAssociationController@editRow'  );
	Route::put('/new/{orgID}'      , 'Api\Dashboard\OrganizationAssociation\OrganizationAssociationController@insertRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\OrganizationAssociation\OrganizationAssociationController@deleteRow');

	Route::get('/all/{orgID}', 'Api\Dashboard\OrganizationAssociation\OrganizationAssociationController@showAll');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/relation_group_type -------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/relation_group_type'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Dashboard\Relation\RelationController@showAllRelationGroupTypes' );
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\Relation\RelationController@showAllRelationGroupTypesSearch');

	Route::get('/allrelationgrouptypes/{orgID}', 'Api\Dashboard\Relation\RelationController@showAllRelationGroupType');

	Route::put('/new/{orgID}'      , 'Api\Dashboard\Relation\RelationController@insertRelationGroupTypes');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Relation\RelationController@deleteRow');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/relation_group_classification ---------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/relation_group_classification'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Dashboard\RelationGroupClassification\RelationGroupClassificationController@showPageSorted' );
	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\RelationGroupClassification\RelationGroupClassificationController@showPageSortSearch');

	Route::put('/new/{orgID}'      , 'Api\Dashboard\RelationGroupClassification\RelationGroupClassificationController@insertRow');
	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\RelationGroupClassification\RelationGroupClassificationController@editRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\RelationGroupClassification\RelationGroupClassificationController@deleteRow');

	Route::get('/all/{orgID}', 'Api\Dashboard\RelationGroupClassification\RelationGroupClassificationController@all' );
});
// ----------------------------------------------------------------------------------------
// -- dashboard/relation_type_group -------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/relation_type_group'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get(
		'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}',
		'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@showAllSorted'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}',
		'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@showPageSortSearch'
	);
	
//	Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@showPageSortSearch');
	Route::get('/myterms/{orgID}/{relationTypeID}', 'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@myTermsShow' );
	Route::get('/all/{orgID}', 'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@allShow' );
	
	Route::put('/new/{orgID}'      , 'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@insertRow');
	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@editRow');
	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@deleteRow');

	Route::get('/relationtypegroupowners/{orgID}', 'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@getOwnersList');

	Route::get(
				'/page/{orgID}/{sort}/{order}/{perPage}/{page}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
				'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@showAllSorted'
			);
	Route::get(
				'/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}/ownerId/{ownerId}/showglobal/{shwglblSTT}',
				'Api\Dashboard\RelationTypeGroup\RelationTypeGroupController@showPageSortSearch'
			);
});
// ----------------------------------------------------------------------------------------
// -- dashboard/data_classification -------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/data_classification'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::get( '/page/tableId/{tableName}/{orgID}/{sort}/{order}/{perPage}/{page}'           , 'Api\Dashboard\DataClassification\DataClassificationController@showAllSorted' );
	Route::get( '/tableId/{tableName}/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\DataClassification\DataClassificationController@showAllSorted' );

	Route::get( '/alltables', 'Api\Dashboard\DataClassification\DataClassificationController@allTables' );
	Route::get( '/allfileds/{table}', 'Api\Dashboard\DataClassification\DataClassificationController@allFields' );

	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\DataClassification\DataClassificationController@editRow');
	Route::put('/new/{orgID}'      , 'Api\Dashboard\DataClassification\DataClassificationController@insertRow');

	Route::put('/setpass/{userId}'  , 'Api\Dashboard\DataClassification\DataClassificationController@setPass');
	Route::post( '/getpass/{userId}', 'Api\Dashboard\DataClassification\DataClassificationController@getPass');
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// -- chatbotlog ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extend/chatbotlog'], function() {
    //header("Content-Type: application/json; charset=UTF-8"                                  );
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept"   );
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS"                      );
    //header("Access-Control-Allow-Origin: *"                                                 );
    header('Access-Control-Allow-Credentials: true'                                         );

    Route::get('/all/{archive}/{subtypeID}/{orgID}'                                            , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@showAll'           );
    Route::get('/all/{archive}/{subtypeID}/{orgID}/{sort}/{order}'                             , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@showAllSorted'     );
    // $s_time,$e_time,$user_id,$org_id, $perPage
    Route::get('/page/{archive}/{s_time}/{e_time}/{user_id}/{org_id}/{perPage}/{page}'         , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@showPage'          );
    Route::get('/page/{archive}/{s_time}/{e_time}/{user_id}/{org_id}/{sort}/{order}/{perPage}/{page}/{searc_email}/{search}'           , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@showPageSorted'    );
    Route::get('/chatlog/{chat_id}'           , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@getchatlog'    );
    Route::get('/{archive}/{subtypeID}/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@showPageSortSearch');

    Route::get('/get/{subtypeID}/{orgID}/{id}'                                       , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@show'              );
    Route::get('/search/{archive}/{subtypeID}/{orgID}/{field}/{value}'                         , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@search'            );
    Route::get('/org_all'           , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@org_all'    );
    Route::put('/delete/{orgID}/{id}', 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@deleteRow');
    Route::put('/upArchive/{orgID}/{id}'                                   , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@upArchive'              );
    Route::get('/upOrder/{orgID}/{id}}/{aftid}'                                   , 'Api\Extend\Extended_chatbot_usage\Extended_chatbot_usageController@upOrder'              );
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------
// -- dashboard/lex -----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/lex'], function() {
    //header("Content-Type: application/json; charset=UTF-8");
    //header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    //header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    //header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');

	Route::group(['prefix' => 'setting'], function() {
		Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Dashboard\Lex\SettingController@showPage' );
		Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\Lex\SettingController@showPage' );
		Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\Lex\SettingController@editRow'  );
		Route::put('/new/{orgID}'      , 'Api\Dashboard\Lex\SettingController@insertRow');
		Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Lex\SettingController@deleteRow'  );

		Route::put('/getkey', 'Api\Dashboard\Lex\SettingController@getKey');

		Route::get('/organization/{orgID}', 'Api\Dashboard\Lex\SettingController@listOrganization' );
		Route::get('/get/{settingID}'     , 'Api\Dashboard\Lex\SettingController@getSettingData'   );
	});

	Route::group(['prefix' => 'mapping'], function() {
		Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Dashboard\Lex\MappingController@showPage' );
		Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\Lex\MappingController@showPage' );
		Route::put('/new/{orgID}'      , 'Api\Dashboard\Lex\MappingController@insertRow');
		Route::put('/mapped/'          , 'Api\Dashboard\Lex\MappingController@mappedTo');
		Route::put('/Published/'       , 'Api\Dashboard\Lex\MappingController@publishedMappStatus');
		Route::put('/Unpublished/'     , 'Api\Dashboard\Lex\MappingController@unpublishedMappStatus');
		Route::post('/getmapped/'      , 'Api\Dashboard\Lex\MappingController@getMappedData');
		Route::delete('/delete/'       , 'Api\Dashboard\Lex\MappingController@deleteMap');

		Route::delete('/clearjson', 'Api\Dashboard\Lex\MappingController@clearJson');

		Route::put('/setjson/{mapId}/{type}/{name}/{version}', 'Api\Dashboard\Lex\MappingController@setJson');
		Route::get('/getjson'                                , 'Api\Dashboard\Lex\MappingController@getJson');
		
		Route::get('/terms', 'Api\Dashboard\Lex\MappingController@showTerms' );
		Route::get('/relationtypes', 'Api\Dashboard\Lex\MappingController@showRelationTypes' );
		Route::get('/searckkr', 'Api\Dashboard\Lex\MappingController@searchKrs' );
		Route::post('/getratevalue/{orgID}/{personID}/{relationID}/{userID}/{ownerID}', 'Api\Dashboard\Lex\MappingController@getRateValue' );
	});

	Route::group(['prefix' => 'testing'], function() {
		Route::post('/set', 'Api\Dashboard\Lex\LexClassTestController@setTestValue');
	});

});
// ----------------------------------------------------------------------------------------
// -- dashboard/testing -------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/testing'], function() {
	Route::post('/apilogin'       , 'Api\Dashboard\Testing\TestingController@apiLogin');
	Route::post('/apiauthenticate', 'Api\Dashboard\Testing\TestingController@apiAuthenticate');
	Route::post('/activeusers'    , 'Api\Dashboard\Testing\TestingController@apiActiveUsers');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/logs ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/logs'], function() {
	Route::get('/org_all'          , 'Api\Dashboard\Logs\LogsController@org_all'   );
    Route::get('/chatlog/{chat_id}', 'Api\Dashboard\Logs\LogsController@getchatlog');
    Route::get(
		'/page/{archive}/{s_time}/{e_time}/{user_id}/{org_id}/{sort}/{order}/{perPage}/{page}/{searc_email}/{search}',
		'Api\Dashboard\Logs\LogsController@showPageSorted');
    Route::put('/upArchive/{orgID}/{id}', 'Api\Dashboard\Logs\LogsController@upArchive');
    Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Logs\LogsController@deleteRow');

    Route::post('/email/', 'Api\Dashboard\Logs\LogsController@sendEmail');
});
Route::post('/log/email', 'Api\Dashboard\Logs\LogsController@sendAutoEmails');
Route::post('/log/mylog', 'Api\Dashboard\Logs\LogsController@sendMylog');
// ----------------------------------------------------------------------------------------
// -- test --------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'v1/test'], function() {
	Route::any('/load/{entity}', function($entity){
		$tmp = new \App\Helpers\Extended;
		return $tmp->load( $entity );
	});
});
// ----------------------------------------------------------------------------------------
// -- dashboard/billing -------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/billing'], function() {
	Route::post('/{orgID}/{sort}/{order}'          , 'Api\Dashboard\Billing\BillingController@all'     );
	Route::post('/details/{orgID}/{sort}/{order}'  , 'Api\Dashboard\Billing\BillingController@details' );
});
// ----------------------------------------------------------------------------------------
// -- dashboard/portal --------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/portal'], function() {
	Route::get('/page/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerId}'           , 'Api\Dashboard\Portal\PortalController@viewTable');
	Route::get('/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerId}/{field}/{value}', 'Api\Dashboard\Portal\PortalController@viewTable');

	Route::get('/personality/{orgID}', 'Api\Dashboard\Portal\PortalController@viewPersonality');

	Route::put('/new/{orgID}', 'Api\Dashboard\Portal\PortalController@insertRow');
	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\Portal\PortalController@editRow');

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Portal\PortalController@deleteRow');

	Route::get('/check/{portal}'   , 'Api\Dashboard\Portal\PortalController@checkPortal');	
	Route::get('/getPortalNumbers/', 'Api\Dashboard\Portal\PortalController@getPortalNumbers');

	Route::get('/getPortalOwner/'       , 'Api\Dashboard\Portal\PortalController@ownersList');
	Route::get('/getPortalOwner/{orgId}', 'Api\Dashboard\Portal\PortalController@ownersList');
	Route::get('/getPerson/{orgId}'     , 'Api\Dashboard\Portal\PortalController@getPerson');
	Route::get('/portals/{orgID}'       , 'Api\Dashboard\Portal\PortalController@portals' );
	Route::get('/getPortalLiveAgent/'       , 'Api\Dashboard\Portal\PortalController@getPortalLiveAgent');
	Route::get('/getPortalLiveAgent/{orgId}', 'Api\Dashboard\Portal\PortalController@getPortalLiveAgent');

	Route::get('/feedback/{portal_id}', 'Api\Dashboard\Portal\PortalController@getFeedbackItems');
	Route::put('/feedback/'           , 'Api\Dashboard\Portal\PortalController@setFeedbackItems');
});
Route::get('/prelaunch/{portal}', 'Api\Dashboard\Portal\PortalController@checkPortal');
// ----------------------------------------------------------------------------------------
// -- dashboard/charts --------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'charts'], function() {
	Route::get('/1/{flag}/{orgID}' , 'Api\Dashboard\Charts\ChartsController@chart1Data');
	Route::get('/2/{flag}/{orgID}' , 'Api\Dashboard\Charts\ChartsController@chart2Data');
	Route::get('/3/{flag}/{orgID}' , 'Api\Dashboard\Charts\ChartsController@chart3Data');
	Route::get('/4/{flag}/{orgID}' , 'Api\Dashboard\Charts\ChartsController@seeListData');
	
	Route::get('/5/{flag}/{orgID}' , 'Api\Dashboard\Charts\ChartsController@csvData');
	
	Route::get('/6/{flag}/{orgID}' , 'Api\Dashboard\Charts\ChartsController@chart4Data');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/kaas ----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/kaas'], function() {
    header('Access-Control-Allow-Credentials: true');

	Route::get('/structure', 'Api\Dashboard\KaaS\MappingController@getStructure');
	Route::group(['prefix' => 'setting'], function() {
		Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Dashboard\KaaS\SettingController@showPage' );
		Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\KaaS\SettingController@showPage' );
		Route::get('/portals/{orgID}', 'Api\Dashboard\Portal\PortalController@portals' );
		
		Route::put('/new/{orgID}'    , 'Api\Dashboard\KaaS\SettingController@insertRow');
		Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\KaaS\SettingController@deleteRow'  );

		Route::get('/organization/{orgID}', 'Api\Dashboard\KaaS\SettingController@listOrganization' );
		Route::get('/get/{portal_id}'     , 'Api\Dashboard\KaaS\SettingController@getSettingData'   );

		Route::put('/getkey', 'Api\Dashboard\KaaS\SettingController@getKey');
	});

	Route::group(['prefix' => 'mapping'], function() {
		Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Dashboard\KaaS\MappingController@showPage' );
		Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\KaaS\MappingController@showPage' );

		Route::put('/new/{orgID}', 'Api\Dashboard\KaaS\MappingController@insertRow');

		Route::post('/getmapped/', 'Api\Dashboard\KaaS\MappingController@getMappedData');
		Route::get('/getjson'    , 'Api\Dashboard\KaaS\MappingController@getJson');
		
		Route::put('/newrow'     , 'Api\Dashboard\KaaS\MappingController@newRow');
		Route::put('/editrow'    , 'Api\Dashboard\KaaS\MappingController@editRow');
		Route::put('/mapped'     , 'Api\Dashboard\KaaS\MappingController@mappedTo');
		Route::put('/getlayer'   , 'Api\Dashboard\KaaS\MappingController@getLayer');

		Route::delete('/deleterow', 'Api\Dashboard\KaaS\MappingController@deleteRow');

		Route::put('/Published/'       , 'Api\Dashboard\KaaS\MappingController@publishedMappStatus');
		Route::put('/Unpublished/'     , 'Api\Dashboard\KaaS\MappingController@unpublishedMappStatus');

		Route::delete('/delete/'       , 'Api\Dashboard\Kaas\MappingController@deleteMap');
	});

});
// ----------------------------------------------------------------------------------------
// -- dashboard/BotMessage ----------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/botmessage'], function() {

	Route::get('/getType/'        , 'Api\Dashboard\BotMessage\BotMessageController@getType');
	Route::get('/getLang/{orgID}' , 'Api\Dashboard\BotMessage\BotMessageController@langList');
	Route::get('/getOwner/'       , 'Api\Dashboard\BotMessage\BotMessageController@getOwner');
	Route::get('/getOwner/{orgId}', 'Api\Dashboard\BotMessage\BotMessageController@getOwner');

	Route::get('/getmsg/{parentId}/{lang}', 'Api\Dashboard\BotMessage\BotMessageController@getMasseage_');

	Route::post('/getmessage', 'Api\Dashboard\BotMessage\BotMessageController@getMasseage');

	Route::get(
		'/page/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerId}',
		'Api\Dashboard\BotMessage\BotMessageController@viewTable'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerId}/{field}/{value}',
		'Api\Dashboard\BotMessage\BotMessageController@viewTable'
	);
	Route::put('/new/{orgID}'      , 'Api\Dashboard\BotMessage\BotMessageController@insertRow');
	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\BotMessage\BotMessageController@editRow');
	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\BotMessage\BotMessageController@deleteRow');

	Route::post('/reset/{id}', 'Api\Dashboard\BotMessage\BotMessageController@resetRow');
});
// -- dashboard/AIMessage ----------------------------------------------------------------
Route::group(['prefix' => 'dashboard/aimessage'], function() {
	Route::get('/getOwner/'       , 'Api\Dashboard\BotMessage\BotMessageController@getOwner_AI');
	Route::get('/getLang/{orgID}' , 'Api\Dashboard\BotMessage\BotMessageController@langList');

	Route::post('/getmessage', 'Api\Dashboard\BotMessage\BotMessageController@getMasseage_AI');

	Route::get(
		'/page/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerId}',
		'Api\Dashboard\BotMessage\BotMessageController@viewTable_AI'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerId}/{field}/{value}',
		'Api\Dashboard\BotMessage\BotMessageController@viewTable_AI'
	);
	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\BotMessage\BotMessageController@editRow_AI');

	Route::post('/reset/{id}', 'Api\Dashboard\BotMessage\BotMessageController@resetRow_AI');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/linkkrtoterm --------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/linkkrtoterm'], function() {
	Route::get(
		'/owners/{orgID}',
		'Api\Dashboard\LinkKrToTerm\LinkKrToTermController@getOwners'
	);
	
	Route::get(
		'/page/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerID}/{showGlobal}/{defaultTrID}/{defaultKbID}',
		'Api\Dashboard\LinkKrToTerm\LinkKrToTermController@dataTable'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerID}/{showGlobal}/{defaultTrID}/{defaultKbID}/{field}/{value}',
		'Api\Dashboard\LinkKrToTerm\LinkKrToTermController@dataTable'
	);

	Route::get('/get_termlink/{orgID}', 'Api\Dashboard\LinkKrToTerm\LinkKrToTermController@getTermlink'  );


	Route::put('/new/{orgID}', 'Api\Dashboard\LinkKrToTerm\LinkKrToTermController@newRow');
	Route::put('/edit/{orgID}/{id}', 'Api\Dashboard\LinkKrToTerm\LinkKrToTermController@editRow');
	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\LinkKrToTerm\LinkKrToTermController@deleteRow');
});
// ----------------------------------------------------------------------------------------
// -- dashboard/live_agent ----------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/live_agent'], function() {
    header('Access-Control-Allow-Credentials: true');

	Route::get('/structure', 'Api\Dashboard\LiveAgent\MappingController@getStructure');
	Route::group(['prefix' => 'setting'], function() {
		Route::get('/page/{orgID}/{sort}/{order}/{perPage}/{page}', 'Api\Dashboard\LiveAgent\SettingController@showPage' );
		Route::get('/{orgID}/{sort}/{order}/{perPage}/{page}/{field}/{value}', 'Api\Dashboard\LiveAgent\SettingController@showPage' );
		Route::get('/portals/{orgID}', 'Api\Dashboard\Portal\PortalController@portals' );
		
		Route::put('/new/{orgID}'    , 'Api\Dashboard\LiveAgent\SettingController@insertRow');
		Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\LiveAgent\SettingController@deleteRow'  );

		Route::get('/organization/{orgID}', 'Api\Dashboard\LiveAgent\SettingController@listOrganization' );
		Route::get('/get/{portal_id}'     , 'Api\Dashboard\LiveAgent\SettingController@getSettingData'   );

		Route::put('/getkey', 'Api\Dashboard\LiveAgent\SettingController@getKey');
	});

	Route::group(['prefix' => 'mapping'], function() {
		Route::get(
			'/page/{orgID}/{sort}/{order}/{perPage}/{page}/{portalID}',
			'Api\Dashboard\LiveAgent\MappingController@showPage'
		);
		Route::get(
			'/{orgID}/{sort}/{order}/{perPage}/{page}/{portalID}/{field}/{value}',
			'Api\Dashboard\LiveAgent\MappingController@showPage'
		);

		Route::put('/new/{orgID}', 'Api\Dashboard\LiveAgent\MappingController@insertRow');

		Route::post('/getmapped/', 'Api\Dashboard\LiveAgent\MappingController@getMappedData');
		Route::get('/getjson'    , 'Api\Dashboard\LiveAgent\MappingController@getJson');
		
		Route::put('/newrow'     , 'Api\Dashboard\LiveAgent\MappingController@newRow');
		Route::put('/editrow'    , 'Api\Dashboard\LiveAgent\MappingController@editRow');
		Route::put('/mapped'     , 'Api\Dashboard\LiveAgent\MappingController@mappedTo');
		Route::put('/getlayer'   , 'Api\Dashboard\LiveAgent\MappingController@getLayer');

		Route::delete('/deleterow', 'Api\Dashboard\LiveAgent\MappingController@deleteRow');

		Route::put('/Published/'       , 'Api\Dashboard\LiveAgent\MappingController@publishedMappStatus');
		Route::put('/Unpublished/'     , 'Api\Dashboard\LiveAgent\MappingController@unpublishedMappStatus');
		Route::put('/handoffmessage/'  , 'Api\Dashboard\LiveAgent\MappingController@handOffMessage');

		Route::delete('/delete/'       , 'Api\Dashboard\LiveAgent\MappingController@deleteMap');
	});

});
// ----------------------------------------------------------------------------------------
// -- dashboard/org_emails_config ---------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/org_emails_config'], function() {
	Route::get(
		'/page/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerId}',
		'Api\Dashboard\OrgEmailsConfig\OrgEmailsConfigController@viewTable'
	);
	Route::get(
		'/{orgID}/{sort}/{order}/{perpage}/{page}/{ownerId}/{field}/{value}',
		'Api\Dashboard\OrgEmailsConfig\OrgEmailsConfigController@viewTable'
	);
	
	Route::put('/edit/{orgID}/{portalID}', 'Api\Dashboard\OrgEmailsConfig\OrgEmailsConfigController@editRow');
/*
	Route::get('/personality/{orgID}', 'Api\Dashboard\Portal\PortalController@viewPersonality');

	Route::put('/new/{orgID}', 'Api\Dashboard\Portal\PortalController@insertRow');
	

	Route::delete('/delete/{orgID}/{id}', 'Api\Dashboard\Portal\PortalController@deleteRow');

	Route::get('/check/{portal}'   , 'Api\Dashboard\Portal\PortalController@checkPortal');	
	Route::get('/getPortalNumbers/', 'Api\Dashboard\Portal\PortalController@getPortalNumbers');

	Route::get('/getPortalOwner/'       , 'Api\Dashboard\Portal\PortalController@ownersList');
	Route::get('/getPortalOwner/{orgId}', 'Api\Dashboard\Portal\PortalController@ownersList');
	Route::get('/getPerson/{orgId}'     , 'Api\Dashboard\Portal\PortalController@getPerson');
	Route::get('/portals/{orgID}'       , 'Api\Dashboard\Portal\PortalController@portals' );
	Route::get('/getPortalLiveAgent/'       , 'Api\Dashboard\Portal\PortalController@getPortalLiveAgent');
	Route::get('/getPortalLiveAgent/{orgId}', 'Api\Dashboard\Portal\PortalController@getPortalLiveAgent');
*/
});
// ----------------------------------------------------------------------------------------
// -- dashboard/rpa -----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'dashboard/rpa'], function() {
	Route::get("/structure/{bot_type_id}", 'Api\Dashboard\RPA\RPATypesController@getStructure');
	// ------------------------------------------------------------------------------------
	Route::group(['prefix' => '/types'], function() {
		Route::get(
			'/page/{orgID}/{sort}/{order}/{perpage}/{page}',
			'Api\Dashboard\RPA\RPATypesController@showPage'
		);
		Route::get(
			'/{orgID}/{sort}/{order}/{perpage}/{page}/{field}/{value}',
			'Api\Dashboard\RPA\RPATypesController@showPage'
		);
		
		Route::put("/new/{orgID}"      , 'Api\Dashboard\RPA\RPATypesController@addItem');
		Route::put("/edit/{orgID}/{id}", 'Api\Dashboard\RPA\RPATypesController@editItem');
		
		Route::get("/"      , 'Api\Dashboard\RPA\RPATypesController@getTypes');
	});
	// ------------------------------------------------------------------------------------
	Route::group(['prefix' => '/mapping'], function() {
		Route::get(
			'/page/{orgID}/{sort}/{order}/{perpage}/{page}/{portalID}',
			'Api\Dashboard\RPA\MappingController@showPage'
		);
		Route::get(
			'/{orgID}/{sort}/{order}/{perpage}/{page}/{portalID}/{field}/{value}',
			'Api\Dashboard\RPA\MappingController@showPage'
		);
		
		Route::post('/pre_handoff/get', 'Api\Dashboard\RPA\MappingController@preHandoffGet');
		
		Route::put("/new/{orgID}"     , 'Api\Dashboard\RPA\MappingController@addItem');
		Route::put("/editrow"         , 'Api\Dashboard\RPA\MappingController@editrow');
		Route::put("/mapped"          , 'Api\Dashboard\RPA\MappingController@mappedTo');
		Route::put('/pre_handoff/set' , 'Api\Dashboard\RPA\MappingController@preHandoffSet');
		Route::put('/sampleutterance/', 'Api\Dashboard\RPA\MappingController@sampleUtterance');
		Route::put('/Published'       , 'Api\Dashboard\RPA\MappingController@publishedMappStatus');
		Route::put('/Unpublished'     , 'Api\Dashboard\RPA\MappingController@unpublishedMappStatus');
		
		Route::delete('/delete', 'Api\Dashboard\RPA\MappingController@deleteMap');
		
		
	});
	// ------------------------------------------------------------------------------------
});
// ----------------------------------------------------------------------------------------
// -- sysamin/monitorig -----------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'sysamin/monitorig'], function() {
	Route::put("/save" , 'Api\Dashboard\SysAdmin\MonitoringController@setData');
	Route::put("/clear", 'Api\Dashboard\SysAdmin\MonitoringController@clearLog');
});
// ----------------------------------------------------------------------------------------
// -- feedback ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'feedback'], function() {
	Route::post("/" , 'Api\Dashboard\Feedback\FeedbackController@feedback');

	Route::get('/org_all', 'Api\Dashboard\Feedback\FeedbackController@org_all'   );
	Route::get(
		"/page/{orgid}/{sort}/{dir}/{per_page}/{page_no}/{searc_email}/{sTime}/{eTime}/ownerId/{ownerid}/showglobal/{showglobal}/{archvd}",
		'Api\Dashboard\Feedback\FeedbackController@getData'
	);
	Route::get(
		"/{orgid}/{sort}/{dir}/{per_page}/{page_no}/{searc_email}/{sTime}/{eTime}/{srch_val}/ownerId/{ownerid}/showglobal/{showglobal}/{archvd}",
		'Api\Dashboard\Feedback\FeedbackController@getDataS'
	);
	Route::get(
		'/chatlog/{chat_id}',
		'Api\Dashboard\Feedback\FeedbackController@getchatlog'
	);
	
	Route::post('/email/', 'Api\Dashboard\Feedback\FeedbackController@sendEmail');
	Route::post('/archive/', 'Api\Dashboard\Feedback\FeedbackController@setArchive');
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::post("set-menu/{id}", function($id){ return $id; });

// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
Route::group(['prefix' => 'extra_api'], function() {
	Route::group(['prefix' => '/term'], function() {
		Route::post("/" , 'Api\ExtraApi\ExtraApiController@term');
		Route::delete("/{term_id}" , 'Api\ExtraApi\ExtraApiController@clearTerm');
	});	
	Route::post("/get/apikey" , 'Api\ExtraApi\ExtraApiController@getApiKey');
});
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------
