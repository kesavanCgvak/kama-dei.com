<?php
@unlink("test.log");
$difficulty=1;
if(isset($_GET['difficulty']) && intval($_GET['difficulty']) && $_GET['difficulty']<4 && $_GET['difficulty']>0 )
	{ $difficulty=intval($_GET['difficulty']); }

@system("php bench.php --multiplier={$difficulty} > test.log");
echo "<pre>".str_replace("\n", "<br/>", file_get_contents("test.log"))."</pre>";

