<style>
div.card-detail{ height:80vh; }
</style>
<?php
$tmp = new \App\SitePages();
$tmpCaption = $tmp->getCaptionMenu($_SERVER['REQUEST_URI']); 
$tmpChilds  = $tmp->getchildsMenu ($_SERVER['REQUEST_URI']); 
echo "<h1>{$tmpCaption}</h1>";
foreach($tmpChilds as $chld){
	echo "<h3 style='margin-left:20px;'><a href='{$chld['url']}'>{$chld['caption']}</a></h3>";
}
?>
