<style>
	#showError{
		position: fixed;
		top: 10px;
		left: 10px;
		color: #fff;
		background: #d25c5c;
		z-index: 9999;
		min-width: 150px;
		width: auto;
		padding: 8px 15px;
		border: 1px dotted #fff;
		border-radius: 8px;
		box-shadow: 0 0 0 3px #d25c5c;
		display:none;
	}

	#editUser, #addUser {
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

	#editUser > form, #addUser > form {
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

	#editUser.show, #addUser.show{ display: block; }
	#editUser > form input, #addUser > form input{ width: 250px; }

	.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
	.row-actions{ text-align: center; }
	.row-actions > a:first-child{ padding-right: 10px; }
/*
	#users td:nth-child(1)
		{ font-size:12px;vertical-align:middle; }

	#users td:nth-child(2),
	#users td:nth-child(3)
		{ font-size:13px;vertical-align:middle; }

	#users td:nth-child(4),
	#users td:nth-child(5),
	#users td:nth-child(6)
		{ font-size:12px;vertical-align:middle;width:140px;padding:1px 5px;word-break:break-word; }

	#users td:nth-child(7)
		{ font-size:12px;vertical-align:middle;width:50px; }

	#users td:nth-child(8),
	#users td:nth-child(9),
	#users td:nth-child(10)
		{ font-size:12px;vertical-align:middle;width:50px;text-align:center; }
*/
	div.th-inner.sortable.both{ padding-right:0 !important; }
	label.isactive:hover{ cursor:pointer;color:red; }

	#users th{ font-size:13px; }
	#users th:nth-child(1){ width:75px; }
	#users th>div:nth-child(1){ width:100%; }

	#users td:nth-child(1){ width:75px;font-size:12px; vertical-align: middle; }
	#users td:nth-child(2){ font-size:12px; vertical-align: middle; }
	#users td:nth-child(3){ width:100px;font-size:12px; vertical-align: middle; }
	#users td:nth-child(4){ width:160px;font-size:12px; vertical-align: middle; }
	#users td:nth-child(5){ width: 80px;font-size:12px; padding: 0; vertical-align: middle; text-align: center; }
	#users td:nth-child(6){ width: 80px;font-size:60%; padding: 0; vertical-align: middle; text-align: center; }

	#users td:nth-child(7){ width: 80px;font-size:80%; }
	#users td:nth-child(8){ width: 50px;font-size:12px; vertical-align: middle; }
	#users td:nth-child(9){ width: 50px;font-size:60%; text-align: center; vertical-align: middle; padding: 0; }
	#users td:nth-child(10){ width: 64px;font-size:12px; text-align: center; }
	
	#users th:last-child, #users td:last-child{ width: 30px; font-size: 70%; text-align: center; vertical-align: middle; padding: 0; }
	#users td:last-child a{display:block;margin:3px;padding:0; }

	#resetDialog,
	#sensitiveDialog{
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
	#resetDialog > div,
	#sensitiveDialog > div{
		position: absolute;
		width: 320px;
		height: 100px;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		margin: auto;
		background: white;
		padding: 10px;
		border-radius: 2px;
		font-size:16px;
	}
	#sensitiveDialog > div{ height:170px;background:#e2e9ec; }

	#resetDialog .resetActions,
	#sensitiveDialog .sensitiveActions{
		position: absolute;
		bottom: 10px;
		right: 5px;
	}
	#resetDialog .resetActions .btn,
	#sensitiveDialog .sensitiveActions .btn{ margin-right: 5px;width:80px; }
</style>

<select class="form-control" id="myOwnersList" style="display:none" onchange="setNewList($(this).val())">
	<option value="-1" selected="selected">Owner All</option>
</select>
<select class="form-control" id="myFilterBy" style="display:none" onchange="setNewList($(this).val())">
	<option value="0" selected="selected">Filter by: None</option>
</select>
<div id="users"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL    = "<?=env('API_URL');?>";
	var orgID     = "<?=$orgID;?>";
	var KAMARONID = '<?=Config::get('kama_dei.static.KAMARONID',0);?>';
	var userID    = "<?=session()->get('userID');?>";
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
//------------------------------------------------------------
$(function(){
	//--------------------------------------------------------
	var tmp = $("#myOwnersList");
	$(".pull-right.search").prepend(tmp);
	tmp = $("#myFilterBy");
	$(".pull-right.search").prepend(tmp);
	//--------------------------------------------------------
	$(".pull-right.search .form-control").css('width', '30%');
	$(".pull-right.search .form-control").css('margin-left', '3%');
	$(".pull-right.search .form-control").css('display', 'inline-block');
	$("#users table").bootstrapTable().on('refresh.bs.table', function(){ getOwnersList($("#myOwnersList").val()); });
	//--------------------------------------------------------
	getOwnersList(-1);
	getLevelsList();
	//--------------------------------------------------------
});
//------------------------------------------------------------
function getOwnersList(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/user/myowners/<?=$orgID;?>',
		function(retVal){
			$("#myOwnersList option").remove();
			$("#myOwnersList").append('<option value="-1">Owner All</option>');
			for(var i in retVal.data){ $("#myOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#myOwnersList").val(id)
		}
	);
}
//------------------------------------------------------------
function getLevelsList(){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/user/mylevels/<?=$orgID;?>',
		function(retVal){
			$("#myFilterBy option").remove();
			$("#myFilterBy").append('<option value="0">Filter by: None</option>');
			for(var i in retVal.data){ 
				$("#myFilterBy").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); 
			}
			$("#myFilterBy").val(0)
		}
	);
}
//------------------------------------------------------------
function setNewList(id){ $("#users table").bootstrapTable('refresh'); }
//------------------------------------------------------------
</script>