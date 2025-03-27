<head>
  <meta http-equiv="refresh" content="60">
</head> 
<body>
<?php
if(file_exists(env('monitoring_location')."/monitoring.log")){
	$text = file_get_contents(env('monitoring_location')."/monitoring.log");
	echo str_replace("\n", "<br/>", str_replace(" local.INFO:", "", $text));
}
?>
</body>
<script type="text/javascript" src="{{ asset('public/js/jquery.js') }}"></script>
<script type="application/javascript">
	$(function(){ $("html, body").animate({ scrollTop: $(document).height() }, "fast"); })
</script>