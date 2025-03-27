<!DOCTYPE html>
<html>
<head>
	<title>Kama-DEI</title>
</head>
<body>
	<h1>Chat log</h1>
	<?php $kamaOrg   = \App\Organization::find($orgID); ?>
	<div style="margin-bottom:10px;"><?=$kamaOrg->organizationShortName?></div>
	<p>User Id: <b><?=$uLog->user_id;?></b></p>
	<p>Name: <b><?=$uLog->user_name;?></b></p>
	<?php if($body==''): ?>
	<p style="margin-bottom:10px;">Log for: <?=$uLog->timestamp?></div>
	<p style="margin-bottom:10px;">Total files attached: <?=$attached?></div>
	<?php else: ?>
	<?php $body = str_replace("\n", "<br/>", $body); ?>
	<div>{!! $body !!}</div>
	<?php endif; ?>
</body>
</html>
