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
#showError>i{ margin-right:5px; }
#showError>i:hover{ cursor:pointer;color:yellow; }

#editTerm, #addTerm {
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

#editTerm.show, #addTerm.show {
	display: block;
}

#editTerm > form, #addTerm > form {
	position: absolute;
	margin: auto;
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	width: 280px;
	height: 250px;
	width: fit-content;
	height: fit-content;
	background: white;
	padding: 15px;
}

#editTerm > form input, #addTerm > form input {
	width: 250px;
}

.react-bs-table-bordered, .react-bs-container-body {
	height: auto !important;
}

.row-actions {
	text-align: center;
}

.row-actions > a:first-child {
	padding-right: 10px;
}
#term th{ font-size:13px; }
#term td:nth-child(1){ font-size:11px;text-align:center;vertical-align:middle;width:40px; }
#term td:nth-child(2){ font-size:13px;text-align:right;vertical-align:middle;width:80px; }
#term td:nth-child(3){ font-size:13px; }
#term td:nth-child(4){ font-size:11px;text-align:center;vertical-align:middle;width:120px; }
#term td:nth-child(5){ font-size:11px;text-align:center;vertical-align:middle;width:120px; }
#term td:nth-child(6){ font-size:12px;text-align:center;vertical-align:middle;width:120px; }	
#term td:nth-child(7){ font-size:12px;text-align:left;vertical-align:middle;width:150px; }	
#term td:nth-child(8){ font-size:11px;text-align:center;vertical-align:middle;width:120px; }	
#term td:last-child{ font-size:12px;vertical-align:middle;width:40px; }	
</style>

<select class="form-control" id="termOwnersList" style="display:none">
	<option value="-1" selected="selected">Owners . . .</option>
</select>
<div id="term"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL    = "<?=env('API_URL');?>";
	var orgID     = "<?=$orgID;?>";
	var userID    = "<?=session()->get('userID');?>";
	var userLevel = "<?=session()->get('levelID');?>";
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
$(function(){
	let tmp = $("#termOwnersList");
	$(".pull-right.search").prepend(tmp);

	$(".pull-right.search .form-control").css('width', '49%');
	$(".pull-right.search .form-control").css('display', 'inline-block');

	$(".pull-right.search select").css('margin-right', '1.8%');

	$("#term table").bootstrapTable().on('refresh.bs.table', function(){ getOwnersList($("#termOwnersList").val()); });
	getOwnersList(-1);
});
//-----------------------------------------------------------------------
function getOwnersList(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/term/termowners/<?=$orgID;?>',
		function(retVal){
			$("#termOwnersList option").remove();
			$("#termOwnersList").append('<option value="-1">Owners . . .</option>');
			for(var i in retVal.data){ $("#termOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#termOwnersList").val(id)
		}
	);
}
//-----------------------------------------------------------------------
</script>
