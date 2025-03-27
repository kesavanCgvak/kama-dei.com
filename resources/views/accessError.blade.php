<html>
<head>
	<title>Access Denied</title>
	<link rel="icon" href="{{ asset('public/dist/images/kama-favicon.jpg') }}"/>
</head>
<body style="background:#eee7e763; text-align: center; padding-top: 50px;">
	<?php
	$lCaption = \App\Level::find($level);
	if($lCaption!=null){ $lCaption = $lCaption->levelName; }

	$pCaption = \App\SitePages::find($page);
	if($pCaption!=null){ $pCaption = $pCaption->pageCaption; }

	?>
	<img src="{{ asset('public/dist/images/access_denied.png') }}" style="margin: auto" />
	<div>
		 Your access level "<b><?=$lCaption;?></b>" does not have access to this page [<i><?=$pCaption;?></i>], contact Kama-DEI administrator.
	</div>
	<a href="{{env('API_URL')}}">Back to Dashboard</a>
</body>
</html>