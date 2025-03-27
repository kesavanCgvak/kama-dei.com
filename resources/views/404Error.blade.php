<html>
<head>
	<title>404 page not found</title>
	<link rel="icon" href="{{ asset('public/dist/images/kama-favicon.jpg') }}"/>
</head>
<body style="background:#eee7e763; text-align: center; padding-top: 50px;">
	<?php
	$lCaption = \App\Level::find($level);
	if($lCaption!=null){ $lCaption = $lCaption->levelName; }

	?>
	<img src="{{ asset('public/dist/images/404.png') }}" style="margin: auto" />
	<div>
		 Invalid url [<b><?=env('API_URL').$page;?></b>], contact Kama-DEI administrator.
	</div>
	<a href="{{env('API_URL')}}">Back to Dashboard</a>
</body>
</html>