<style>
#editRelationType, #addRelationType {
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
#editRelationType.show, #addRelationType.show{ display: block; }
#editRelationType > form, #addRelationType > form {
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
#editRelationType > form input, #addRelationType > form input{ width: 250px; }
.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
.row-actions{ text-align: center; }
.row-actions > a:first-child{ padding-right: 10px; }

#relationType th{ font-size:14px; }
#relationType td:nth-child(2){ font-size:13px;vertical-align:middle;width:250px; }
#relationType td:nth-child(3){ font-size:12px;text-align:center;vertical-align:middle; }
#relationType td:nth-child(4){ font-size:12px;text-align:center;vertical-align:middle; }
#relationType td:nth-child(5){ font-size:12px;vertical-align:middle;width:150px; }
#relationType td:nth-child(6){ font-size:12px;text-align:center;vertical-align:middle; }
#relationType td:nth-child(7){ font-size:12px;text-align:center;vertical-align:middle;width:40px; }

</style>
<select class="form-control" id="relationTypeOwnersList" style="display:none">
	<option value="-1" selected="selected">Owners . . .</option>
</select>
<div id="relationType"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
$(function(){
	//--------------------------------------------------------
	var tmp = $("#relationTypeOwnersList");
	$(".pull-right.search").prepend(tmp);
	$(".pull-right.search .form-control").css('width', '50%');
	$(".pull-right.search .form-control").css('display', 'inline-block');
	$("#relationType table").bootstrapTable().on('refresh.bs.table', function(){ getOwnersList($("#relationTypeOwnersList").val()); });
	getOwnersList(-1);
	//--------------------------------------------------------
});
//------------------------------------------------------------
function getOwnersList(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/relation_type/relationtypeowners/<?=$orgID;?>',
		function(retVal){
			$("#relationTypeOwnersList option").remove();
			$("#relationTypeOwnersList").append('<option value="-1">Owners . . .</option>');
			for(var i in retVal.data){ $("#relationTypeOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#relationTypeOwnersList").val(id)
		}
	);
}
//-----------------------------------------------------------------------
</script>