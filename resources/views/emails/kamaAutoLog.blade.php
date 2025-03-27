<!DOCTYPE html>
<html>
<head>
	<title>Kama Log</title>
</head>
<body>
	<h1>Kama-DEI chat logs</h1>
	<?php $kamaOrg   = \App\Organization::find($orgID); ?>
	<div style="margin-bottom:10px;"><?=$kamaOrg->organizationShortName?></div>
	<p style="margin-bottom:10px;">Logs before: <?=$timestamp?></div>
	<p style="margin-bottom:10px;">Total files attached: <?=$attached?></div>
</body>
</html>