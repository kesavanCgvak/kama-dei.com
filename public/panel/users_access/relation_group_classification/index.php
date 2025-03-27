<style>
#editRelationGroupClassification, #addRelationGroupClassification {
	display: none;
	position: fixed;
	z-index: 1000;
	background: rgba(0, 0, 0, 0.6);
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	margin: auto;
}

#editRelationGroupClassification.show, #addRelationGroupClassification.show {
	display: block;
}

#editRelationGroupClassification > form, #addRelationGroupClassification > form {
	position: absolute;
	margin: auto;
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	width: 280px;
	height: 370px;
	width: fit-content;
	height: fit-content;
	background: white;
	padding: 15px;
}

#editRelationGroupClassification > form input, #addRelationGroupClassification > form input, #editRelationGroupClassification > form select, #addRelationGroupClassification > form select, #insertItem {
	width: 250px !important;
}
#insertItem, #saveItem { width: 250px !important; float:right; }
}

.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }

.row-actions{ text-align: center; }

.row-actions > a:first-child{ padding-right: 10px; }
#relationGroupClassification table td:nth-child(1){ font-size:13px; }
#relationGroupClassification table td:nth-child(2){ width:100px !important;font-size:12px; }
#relationGroupClassification table td:nth-child(3){ width:25%   !important;font-size:12px; }
#relationGroupClassification table td:nth-child(4){ width:100px !important;font-size:12px;text-align:center; }
#relationGroupClassification table td:nth-child(5){ width:30px  !important;font-size:11px; }

#relationGroupClassification table td:nth-child(5) a{ display:block;margin-bottom:10px;padding:0; }
.col-relationTypeId{ width:100%; }

</style>
<div id="relationGroupClassification"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
$(function(){
	//--------------------------------------------------------
//	$("span.content-header-title>span").text('Organization '+$("span.content-header-title>span").text());
	//--------------------------------------------------------
});
</script>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
	var table;
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
$(function(){
	//--------------------------------------------------------
	//--------------------------------------------------------
});
</script>
