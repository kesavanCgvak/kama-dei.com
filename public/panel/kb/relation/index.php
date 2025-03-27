<style>
	#editRelation, #addRelation {
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

	#editRelation.show, #addRelation.show {
		display: block;
	}

	#editRelation > form, #addRelation > form {
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

	#editRelation > form input, #addRelation > form input, #editRelation > form select, #addRelation > form select, #insertItem {
		width: 250px !important;
	}
	#insertItem, #saveItem, .btn.btnclose { width: 225px !important; }
	#insertItem, #saveItem { float:right; }
	
	#editItem{ max-height:100vh; overflow:auto; }
	@media screen and (max-height: 600px){
		#editItem .action-form{ margin-top:1px; }
	}
	@media screen and (max-width: 500px){
		#insertItem, #saveItem, .btn.btnclose { width: 100% !important; display: block; margin-bottom: 5px; }
		.col-ownership, .col-ownershipEdit, .col-ownerId, .col-ownerIdEdit{ display: block; width: 100%; }
		
		.col-leftTermId, .col-leftTermIdEdit, .col-relationTypeId, .col-relationTypeIdEdit{
			width: 32%; margin-right:2%;
		}
		.col-rightTermId, .col-rightTermIdEdit{
			width: 32%; margin-right:0;
		}
		#rightTermIdTEMP{ width:100% !important; }
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
	.form-group i.fa-search:hover{color:red;cursor:pointer; }

	#relation table th{ font-size:14px; }
	#relation table th>div:nth-child(3){ padding-right:15px; }
	#relation table td:nth-child(1){ font-size:11px;vertical-align:middle;text-align:center;width:40px; }
	#relation table td:nth-child(2){ font-size:13px;vertical-align:middle; }
	#relation table td:nth-child(3){ font-size:11px;vertical-align:middle;text-align:left;  width:150px; word-break: break-all; }
	#relation table td:nth-child(4){ font-size:11px;vertical-align:middle;text-align:center;width:90px; }
	#relation table td:nth-child(5){ font-size:12px;vertical-align:middle;text-align:center;width:110px; }
	#relation table td:nth-child(6){ font-size:12px;vertical-align:middle;text-align:left;  width:170px; }
	#relation table td:nth-child(7){ font-size:11px;vertical-align:middle;text-align:center;width:100px; }

	#relation table th:nth-child(8) , #relation table td:nth-child(8),
	#relation table th:nth-child(9) , #relation table td:nth-child(9),
	#relation table th:nth-child(10), #relation table td:nth-child(10)
		{ font-size:11px;vertical-align:middle;width:100px;text-align:center; }
	#relation table th:nth-child(11), #relation table td:nth-child(11){ display:none; }

	#relation table td:last-child{ font-size:12px;vertical-align:middle;width:40px; }

	#relation .action-form{ max-width: 98%; }
</style>

<select class="form-control" id="relationOwnersList" style="display:none">
	<option value="-1" selected="selected">Owner All</option>
</select>

<!--
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="languagesItem" aria-hidden="true" id="languagesItems1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div style="width: 100%; text-align: left" id="languagesItem">

					<input type="hidden" id="langRelationId" value="0" />
					<div style="width: 100%; display: block">
						<select class="form-control" id="langLanguageCode"></select>
					</div>
					
					<div style="width: 100%; display: block; margin-top: 25px;">
						<label>Short Display Text (max:20 char. for FB Messenger)</label>
						<button class="btn btn-warning" id="btn-shortText" style="float:right; margin-top:-10px;">Remove</button>
						<input type="text" class="form-control" id="shortText" maxlength="20"/>
					</div>
					
					<div style="width: 100%; display: block; margin-top: 25px;">
						<label>Optional Display Text (max:1020 char.)</label>
						<button class="btn btn-warning" id="btn-optionalText" style="float:right; margin-top:-10px;">Remove</button>
						<input type="text" class="form-control" id="optionalText" maxlength="1024"/>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</div>
-->
<div id="languagesItems" style="display: none">
	<div 
		 style="width:100%; text-align:left; margin:30px 0; border:3px solid #ccc; border-radius:8px; padding:18px 12px;" id="languagesItem"
	>

		<input type="hidden" id="langRelationId" value="0" />
		<div style="width: 100%; display: block">
			<select class="form-control" id="langLanguageCode"></select>
		</div>

		<div style="width: 100%; display: block; margin-top: 25px;">
			<label>Optional Display Text (max:1020 char.)</label>
			<button class="btn btn-warning" id="btn-optionalText" style="float:right; margin-top:-10px;">Remove</button>
			<input type="text" class="form-control" id="optionalText" maxlength="1024"/>
		</div>

		<div style="width: 100%; display: block; margin-top: 25px;">
			<label>Short Display Text (max:20 char. for FB Messenger)</label>
			<div>
				<button class="btn btn-warning" id="btn-shortText" style="float:right; margin-top:-10px;">Remove</button>
				<input type="text" class="form-control" id="shortText" maxlength="20"/>
			</div>
		</div>

		<div style="width: 100%; display: block; margin-top: 25px;">
			<label>Validation Text (max:1020 char.)</label>
			<button class="btn btn-warning" id="btn-validationText" style="float:right; margin-top:-10px;">Remove</button>
			<input type="text" class="form-control" id="validationText" maxlength="1024"/>
		</div>

	</div>
</div>


<div id="relation"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL    = "<?=env('API_URL');?>";
	var orgID     = "<?=$orgID;?>";
	var userID    = "<?=session()->get('userID');?>";
	var userLevel = "<?=session()->get('levelID');?>";
	var table;
	var termPerPage = 100;
	var isAddKR = false;
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
$(function(){
	//--------------------------------------------------------
	var tmp = $("#relationOwnersList");
	$(".pull-right.search").prepend(tmp);
	$(".pull-right.search .form-control").css('width', '50%');
	$(".pull-right.search .form-control").css('display', 'inline-block');
	$("#relation table").bootstrapTable().on('refresh.bs.table', function(){ getOwnersList($("#relationOwnersList").val()); });
	getOwnersList(-1);
	//--------------------------------------------------------
	$("select#leftTermId, select#relationTypeId, select#rightTermId").on('change', function(){
		let lT = $("select#leftTermId option:selected").text();
		let Rt = $("select#relationTypeId option:selected").text();
		let rT = $("select#rightTermId option:selected").text();

		if($("select#leftTermId").val()==''){ lT="..."; }
		if($("select#relationTypeId").val()==''){ Rt="..."; }
		if($("select#rightTermId").val()==''){ rT="..."; }
		var tempVar = lT+" "+Rt+" "+rT;

		$("input#leftTermIdTEMP" ).val($("select#leftTermId option:selected" ).text());
		$("input#rightTermIdTEMP").val($("select#rightTermId option:selected").text());
		$("input#tempVar").val(tempVar);
	});
	//--------------------------------------------------------
	$("#searchItemText").on('change', function(){ table.searchTermByName($("#objID").val().trim(), $(this).val().trim()); });
	//--------------------------------------------------------
	$("#searchBox").on('shown.bs.modal', function(){ $("#searchItemText").focus(); /*$("#termOwnersList").val(-1);*/} );
	getTermOwners(-1);
	//--------------------------------------------------------
	$("input[name=shortText]").attr('maxlength','1000');
	//--------------------------------------------------------
});
//------------------------------------------------------------
function getOwnersList(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/relation/relationowners/<?=$orgID;?>',
		function(retVal){
			$("#relationOwnersList option").remove();
			$("#relationOwnersList").append('<option value="-1">Owner All</option>');
			for(var i in retVal.data){ $("#relationOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#relationOwnersList").val(id)
		}
	);
}
//-----------------------------------------------------------------------

//-----------------------------------------------------------------------
var leftORright = null;
function showSearchBox(objID, label){
	leftORright = objID;
	//--------------------------------------------------------
	$("#searchItemText").val('');
	$("#objID").val(objID);
//	$("#searchBox .modal-header").text(label);
//	$("#searchBox .modal-header").text('select Term');
	$("#searchBox .modal-header").text('Select '+label);
	//--------------------------------------------------------
	$("#termsList option").remove();
	$("#"+objID+" option").each(function(){
		var value = $(this).attr('value');
		var text  = $(this).text();
		if(value=="") return;
		$("#termsList").append("<option onDblClick='selectTermItem()' value='"+value+"' >"+text+"</option>");
	});
	 $('#searchBox .btn-yes').prop('disabled', true);
	//--------------------------------------------------------
	$("#searchBox").modal({backdrop:'static', keyboard:false});
	//--------------------------------------------------------
	$("#termsList").on('change', function(){
//		$("#"+$("#objID").val().trim()).val( $(this).val() ).change();
		 $('#searchBox .btn-yes').prop('disabled', false);
		$("#searchItemText").val( $(this).find('option:selected').text() );
	});
	//--------------------------------------------------------
}
//------------------------------------------------------------
function selectTermItem(){
	$("#"+$("#objID").val().trim()).val( $('#termsList').val() ).change();
	$("#searchBox").modal('hide');
}
//------------------------------------------------------------
function getTermOwners(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/term/termowners/<?=$orgID;?>',
		function(retVal){
			$("#termOwnersList option").remove();
			$("#termOwnersList").append('<option value="-1">Owner All</option>');
			for(var i in retVal.data){ $("#termOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#termOwnersList").val(id);
		}
	);
}
function setTermOwner(){ 
	$("#searchItemText").focus(); 
	$("#searchItemText").val('')
	table.getTerms(table.editItem.leftTermId , $("select#"+leftORright ), 'n'); 
}
//-----------------------------------------------------------------------
</script>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="cloneItem" aria-hidden="true" id="searchBox">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header"><h3 style="margin:2px 10px"></h3></div>
			<div class="modal-body">
				<div>
					<select class="form-control" id="termOwnersList" onchange="setTermOwner()">
						<option value="-1" selected="selected">Owner All</option>
					</select>
				</div>
				<div style="margin-top:20px;">
					<input class='form-control' id='searchItemText' value="" style="display:inline-block;width:90%;" />
					<button class="btn btn-info" id="findTermsBTN" onclick="$('#searchItemText').change()">go</button>
					<i class="fa fa-refresh fa-spin" id="wait4terms" style="display:none;font-size:22px;margin-left:10px;"></i>
					<input type='hidden' id='objID' value="" />
					
				</div>
				<div style="margin-top:20px;">
					<select size="10" class="form-control" id="termsList"></select>
				</div>
			</div>
			<div class="modal-fotter">
				<div style="width:100%;border-top:1px dotted #ccc;padding:12px 20px 16px;" align="right">
					<button type="button" class="btn btn-success btn-yes" style="width:100%;margin-bottom:5px;" onclick="selectTermItem()" >Select</button>
					<button type="button" class="btn btn-danger btn-no" style="width:100%;" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
</div>
