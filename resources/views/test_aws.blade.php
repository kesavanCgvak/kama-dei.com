<?php
$LexClient = new \Aws\LexRuntimeService\LexRuntimeServiceClient([
   'region' => env('AWS_REGION'),
   'version' => '2016-11-28',
   'credentials' => [
   'key'      => env('AWS_KEY'),
   'secret'   => env('AWS_SECRET_KEY'),
	]
]);



try {
	$AWSinput  = "hello";
	$result = $LexClient->postText([
		'activeContexts' => [],
		'botAlias' => '$LATEST', // REQUIRED
		'botName' => 'rbcQuote', // REQUIRED
		'inputText' => $AWSinput, // REQUIRED
		'requestAttributes' => [],
		'sessionAttributes' => [],
		'userId' => 'chatuser0002', // REQUIRED
	]);
	?><h3><?="inputText: {$AWSinput}";?></h3><pre><?=print_r($result);?></pre><?php

	$AWSinput  = "i need book";
	$result = $LexClient->postText([
		'activeContexts' => [],
		'botAlias' => '$LATEST', // REQUIRED
		'botName' => 'rbcQuote', // REQUIRED
		'inputText' => $AWSinput, // REQUIRED
		'requestAttributes' => [],
		'sessionAttributes' => [],
		'userId' => 'chatuser0002', // REQUIRED
	]);
	?><h3><?="inputText: {$AWSinput}";?></h3><pre><?=print_r($result);?></pre><?php
}catch(\Throwable $e){
	echo $e->getMessage();
}
