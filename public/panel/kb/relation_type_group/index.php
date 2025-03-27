<?php if($levelID!=1){ ?><script type="text/javascript">window.location='/panel/dashboard';</script><?php return; } ?>
<style>
<?php if($orgID!=0): ?>
	.col-ownership.form-group .btn-group label:first-child{ display: none; }
<?php endif; ?>
#editRelationTypeGroup, #addRelationTypeGroup {
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

#editRelationTypeGroup.show, #addRelationTypeGroup.show{ display: block; }
#editRelationTypeGroup > form, #addRelationTypeGroup > form {
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

#editRelationTypeGroup > form input, #addRelationTypeGroup > form input, #editRelationTypeGroup > form select, #addRelationTypeGroup > form select, #insertItem {
	width: 250px !important;
}
#insertItem, #saveItem { width: 250px !important; float:right; }
}

.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
.row-actions{ text-align: center; }
.row-actions > a:first-child{ padding-right: 10px; }

#relationTypeGroup table td{ vertical-align:middle; }

#relationTypeGroup table th:nth-child(1),
#relationTypeGroup table td:nth-child(1){ font-size:13px; }

#relationTypeGroup table th:nth-child(2),
#relationTypeGroup table td:nth-child(2){ width:150px !important; font-size:13px; }

#relationTypeGroup table th:nth-child(3),
#relationTypeGroup table td:nth-child(3){ width:100px !important; font-size:12px; }
#relationTypeGroup table td:nth-child(3){ text-align:center !important; }

#relationTypeGroup table th:nth-child(4),
#relationTypeGroup table td:nth-child(4){ width:100px !important; font-size:12px; }
#relationTypeGroup table td:nth-child(4){ text-align:center !important; }

#relationTypeGroup table th:nth-child(5),
#relationTypeGroup table td:nth-child(5){ width:150px !important; font-size:13px; }

#relationTypeGroup table th:nth-child(6),
#relationTypeGroup table td:nth-child(6){ width:90px !important; font-size:12px; }
#relationTypeGroup table td:nth-child(6){ text-align:center !important; }

#relationTypeGroup table td:nth-child(7){ width:40px !important; font-size:12px; }

div.col-relationTypeId.form-group{ width:100% !important; }
textarea#description{
	width:100%;min-width:100%;max-width:100%;
	height:100px;min-height:100px;max-height:100px;
}
</style>
<select class="form-control" id="relationTypeGroupOwnersList" style="display:none">
	<option value="-1" selected="selected">Owners . . .</option>
</select>
<div id="relationTypeGroup"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
	var table;
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
//------------------------------------------------------------
function getOwnersList(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/relation_type_group/relationtypegroupowners/<?=$orgID;?>',
		function(retVal){
			$("#relationTypeGroupOwnersList option").remove();
			$("#relationTypeGroupOwnersList").append('<option value="-1">Owners . . .</option>');
			for(var i in retVal.data){ $("#relationTypeGroupOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#relationTypeGroupOwnersList").val(id)
		}
	);
}
//------------------------------------------------------------
$(function(){
	//--------------------------------------------------------
	var tmp = $("#relationTypeGroupOwnersList");
	$(".pull-right.search").prepend(tmp);
	$(".pull-right.search .form-control").css('width', '50%');
	$(".pull-right.search .form-control").css('display', 'inline-block');
	$("#relationTypeGroup table").bootstrapTable().on('refresh.bs.table', function(){ getOwnersList($("#relationTypeGroupOwnersList").val()); });
	getOwnersList(-1);
	//--------------------------------------------------------
});
/*
$(function(){
	//--------------------------------------------------------
	$("select#leftTermId, select#relationTypeGroupTypeId, select#rightTermId").on('change', function(){
/ *
		if($(this).attr('id')!='relationTypeGroupTypeId'){
			var prev = $(this).find("option:selected").data('prev');
			var next = $(this).find("option:selected").data('next');
			if( $(this).find("option:selected").data('prev')!='0' ){
				table.getTerms(prev, $(this), 'p');
				return; 
			}
			if( $(this).find("option:selected").data('next')!='0' ){
				table.getTerms(next, $(this), 'n');
				return; 
			}
		}
* /
		var tempVar = 
				$("select#leftTermId option:selected").text()+' '+
				$("select#relationTypeGroupTypeId option:selected").text()+' '+
				$("select#rightTermId option:selected").text();
		$("input#leftTermIdTEMP" ).val($("select#leftTermId option:selected" ).text());
		$("input#rightTermIdTEMP").val($("select#rightTermId option:selected").text());
		$("input#tempVar").val(tempVar);
	});
	//--------------------------------------------------------
	/ *
	$(".form-group input.searchBox").on('keypress', function(e){ if(e.keyCode==13){ table.searchTermByName($(this).attr('id'), $(this).val().trim());  } });
	$(".form-group input.searchBox").on('change', function(){ table.searchTermByName($(this).attr('id'), $(this).val().trim()); });
	$(".form-group input.searchBox").on('change', function(){ table.searchTermByName($(this).attr('id'), $(this).val().trim()); });
	* /
	$("#searchItemText").on('change', function(){ table.searchTermByName($("#objID").val().trim(), $(this).val().trim()); });
//	$("#searchItemText").on('change', function(){ table.searchTermByName('leftTermId-search', $(this).val().trim()); });
	
	//--------------------------------------------------------
//	$("#editItem").on('hide.bs.modal', function(){ $("#saveItem, #insertItem").attr('type', 'submit');} );
	$("#searchBox").on('shown.bs.modal', function(){ $("#searchItemText").focus();} );

	//--------------------------------------------------------
});
//------------------------------------------------------------
function showSearchBox(objID, label){
	//--------------------------------------------------------
	$("#searchItemText").val('');
	$("#objID").val(objID);
//	$("#searchBox .modal-header").text(label);
	$("#searchBox .modal-header").text('enter term');
	//--------------------------------------------------------
	$("#termsList option").remove();
	$("#"+objID+" option").each(function(){
		var value = $(this).attr('value');
		var text  = $(this).text();
		$("#termsList").append("<option value='"+value+"' >"+text+"</option>");
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
}
//------------------------------------------------------------
function selectTermItem(){
	$("#"+$("#objID").val().trim()).val( $('#termsList').val() ).change();
	$("#searchBox").modal('hide');
}
//------------------------------------------------------------
*/

//------------------------------------------------------------
function showEditDialog(){
	alert(0);
	return false;
}
//------------------------------------------------------------
</script>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="cloneItem" aria-hidden="true" id="searchBox">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header"><h3 style="margin:2px 10px"></h3></div>
			<div class="modal-body">
				<div>
					<input class='form-control' id='searchItemText' value="" style="display:inline-block;width:80%;" />
					<button class="btn btn-info" onclick="$('#searchItemText').change()">go</button>
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
