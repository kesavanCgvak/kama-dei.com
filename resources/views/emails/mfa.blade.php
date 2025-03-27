<!DOCTYPE html>
<?php
$mfa_code   = rand(100000, 999999);
$mfa_valid_until = time()+(env("max_mfa_validation", 10)*60);
\App\User::where('id', $user->id)
	->update([
		"mfa_code"   => $user->hash($mfa_code),
		"mfa_valid_until" => $mfa_valid_until
	]);
?>
<html>
<head>
	<title><?=env('mfa_mail_subject');?></title>
</head>
<body>
	<h3>Hello <?=$user->userName;?></h3>
	<h3>Multi-Factor Authentication</h3>
	<ul>
		<li><label>You are a user for Organization</label>: <?php $tmp = new \App\Organization; echo $tmp->getName($user->orgID);?></li>
		<li><label>Access Level</label>: <?php $tmp = new \App\Level; echo $tmp->getName($user->levelID);?></li>
		<li><label>Verification code</label>: <b><?=$mfa_code;?></b></li>
		<li><label>Valid for <b>{{env("max_mfa_validation", 10)}}</b> minutes</label></li>
	</ul>
	<br/>
	<br/>
	<a href="<?=env('API_URL', $_SERVER['SERVER_NAME']);?>">
		<img src="<?=env('API_URL', $_SERVER['SERVER_NAME']);?>/public/dist/images/logo_mfa.jpg"/>
	</a>
</body>
</html>
