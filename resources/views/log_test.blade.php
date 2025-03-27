<?php
/*ini_set('memory_limit','4096M');*/
$raw = '{"response":{"type":"radiobutton","message":"","messages":[{"attribute":"standardmessage","language":"en","value":"I found these results. Please select one for more information. "}],"userid":"11388","orgid":"1","state":802,"botState":"","intentName":"","slotName":"","utterance_orig":"","utterance_3PB":"","utterance_corr":"","language":{"decided":"en","detected":"en"},"contentType":"kr","slidebar":[],"buttons":[],"useKaas":0,"useLiveAgent":0,"mapping":"","answers":[{"text":"Filet mignon can reduce hunger","shortText":"","language":"en","atttype":"button","elementType":"button","elementOrder":"3","value":"*****0000005406*0*0000000000*0*0000008966"},{"text":"Big Mac can reduce hunger","shortText":"","language":"en","atttype":"button","elementType":"button","elementOrder":"3","value":"*****0000005407*0*0000000000*0*0000008966"},{"text":"Shawarma can reduce hunger","shortText":"","language":"en","atttype":"button","elementType":"button","elementOrder":"3","value":"*****0000005403*0*0000000000*0*0000008966"},{"text":"Moxie\'s Dry Ribs can reduce hunger","shortText":"","language":"en","atttype":"button","elementType":"button","elementOrder":"3","value":"*****0000005409*0*0000000000*0*0000008966"},{"text":"Peanuts can reduce hunger","shortText":"","language":"en","atttype":"button","elementType":"button","elementOrder":"3","value":"*****0000005405*0*0000000000*0*0000008966"},{"text":"Apple can reduce hunger","shortText":"","language":"en","atttype":"button","elementType":"button","elementOrder":"3","value":"*****0000005404*0*0000000000*0*0000008966"},{"text":"Popcorn can reduce hunger","shortText":"","language":"en","atttype":"button","elementType":"button","elementOrder":"3","value":"*****0000005408*0*0000000000*0*0000008966"}]}}';
$msg = '{"request": {"type": "text", "message": "i,am,hungry", "utterance": "i am hungry", "answers": [], "termSet": ["i", "am", "hungry"]}}';

$ins = 10;
?>
<!-- -------------------------------------- -->
<style>
	label{ width: 160px; display: inline-block; }
</style>
<!-- -------------------------------------- -->
<h1>kama_log</h1>
<!-- -------------------------------------- -->
<!-- -------------------------------------- -->
<h3>local</h3>
<!-- -------------------------------------- -->
<?php
$conn = "mysqllogold";
$s  = date("H:i:s");
$s1 = microtime(true);
$r  = \DB::connection($conn)->table("kama_log")->orderBy('timestamp', 'desc')->skip(0)->take(100)->get();
$e  = date("H:i:s");
$e1 = microtime(true);
?>
<label>select last <i>100</i> records:</label> <b>{{$s}}</b> - <b>{{$e}}</b> [<i>{{round($e1-$s1, 5)}} µs</i>]
<br/>
<!-- -------------------------------------- -->
<?php
$s  = date("H:i:s");
$s1 = microtime(true);
for($i=0; $i<$ins; $i++){
	\DB::connection($conn)->table("kama_log")->insert([
		'signin_id' => 0,
		'apikey'    => 0,
		'timestamp' => date("Y-m-d H:i:s"),
		'sender'    => 'test',
		'raw_msg'   => $raw,
		'msg'       => $msg
	]);
}
$e  = date("H:i:s");
$e1 = microtime(true);
\DB::connection($conn)->table("kama_log")->where('signin_id',0)->where('apikey',0)->where('sender','test')->delete();
?>
<label>insert <i>{{$ins}}</i> records:</label> <b>{{$s}}</b> - <b>{{$e}}</b> [<i>{{round($e1-$s1, 5)}} µs</i>]
<br/>
<!-- -------------------------------------- -->
<!-- -------------------------------------- -->
<br/>
<hr/>
<!-- -------------------------------------- -->
<!-- -------------------------------------- -->
<h3>dbserver with ebcryption</h3>
<!-- -------------------------------------- -->
<?php
$conn = "mysqllog";
$s = date("H:i:s");
$s1 = microtime(true);
$r  = \DB::connection($conn)->table("kama_log")->orderBy('timestamp', 'desc')->skip(0)->take(100)->get();
$e  = date("H:i:s");
$e1 = microtime(true);
?>
<label>select last <i>100</i> records:</label> <b>{{$s}}</b> - <b>{{$e}}</b> [<i>{{round($e1-$s1, 5)}} µs</i>]
<br/>
<!-- -------------------------------------- -->
<?php
//$s  = date("H:i:s");
$s  = date("H:i:s");
$s1 = microtime(true);
for($i=0; $i<$ins; $i++){
	\DB::connection($conn)->table("kama_log")->insert([
		'signin_id' => 0,
		'apikey'    => 0,
		'timestamp' => date("Y-m-d H:i:s"),
		'sender'    => 'test',
		'raw_msg'   => \Crypt::encryptString($raw),
		'msg'       => Crypt::encryptString($msg)
	]);
}
//$e  = date("H:i:s");
$e  = date("H:i:s");
$e1 = microtime(true);
\DB::connection($conn)->table("kama_log")->where('signin_id',0)->where('apikey',0)->where('sender','test')->delete();
?>
<label>insert <i>{{$ins}}</i> records:</label> <b>{{$s}}</b> - <b>{{$e}}</b> [<i>{{round($e1-$s1, 5)}} µs</i>]
<br/>
<!-- -------------------------------------- -->
<!-- -------------------------------------- -->