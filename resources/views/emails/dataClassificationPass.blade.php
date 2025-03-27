<!DOCTYPE html>
<html>
<head>
	<title>Kama-DEI Data Classification</title>
</head>
<body>
	<h3>Hello <?=$user->userName;?></h3>
	<h3>Data classification</h3>
	<ul>
		<li><label>You are a user for Organization</label> :<?php $tmp = new \App\Organization; echo $tmp->getName($user->orgID);?></li>
		<li><label>Level</label> :<?php $tmp = new \App\Level; echo $tmp->getName($user->levelID);?></li>
		<li><label>Data Classification Password</label> :<?=$sensitivePassword;?></li>
	</ul>
	<br/>
	<br/>
	<a href="<?=env('API_URL', $_SERVER['SERVER_NAME']);?>"><img src="<?=env('API_URL', $_SERVER['SERVER_NAME']);?>/public/dist/images/logo2.jpeg"/></a>
</body>
</html>
