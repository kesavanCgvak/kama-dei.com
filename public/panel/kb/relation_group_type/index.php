<?php if($levelID!=1){ ?><script type="text/javascript">window.location='/panel/dashboard';</script><?php return; } ?>
<style>
#editRelationGroupType, #addRelationGroupType {
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

#editRelationGroupType.show, #addRelationGroupType.show{ display: block; }
#editRelationGroupType > form, #addRelationGroupType > form {
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

#editRelationGroupType > form input, #addRelationGroupType > form input, #editRelationGroupType > form select, #addRelationGroupType > form select, #insertItem {
	width: 250px !important;
}
#insertItem, #saveItem { width: 250px !important; float:right; }
}

.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
.row-actions{ text-align: center; }
.row-actions > a:first-child{ padding-right: 10px; }

#relationGroupType table td:nth-child(1){ font-size:13px; }
#relationGroupType table th:nth-child(2),
#relationGroupType table td:nth-child(2)
	{ width:80px !important; font-size:12px; }
#relationGroupType table th:nth-child(3),
#relationGroupType table td:nth-child(3)
	{ width:150px !important; font-size:12px; }
#relationGroupType table th:nth-child(4),
#relationGroupType table td:nth-child(4)
	{ width:80px !important; font-size:12px; }
#relationGroupType table td:nth-child(5)
	{ width:30px !important; font-size:12px; }

</style>
<div id="relationGroupType"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
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
	$("select#leftTermId, select#relationGroupTypeTypeId, select#rightTermId").on('change', function(){
/*
		if($(this).attr('id')!='relationGroupTypeTypeId'){
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
*/
		var tempVar = 
				$("select#leftTermId option:selected").text()+' '+
				$("select#relationGroupTypeTypeId option:selected").text()+' '+
				$("select#rightTermId option:selected").text();
		$("input#leftTermIdTEMP" ).val($("select#leftTermId option:selected" ).text());
		$("input#rightTermIdTEMP").val($("select#rightTermId option:selected").text());
		$("input#tempVar").val(tempVar);
	});
	//--------------------------------------------------------
	/*
	$(".form-group input.searchBox").on('keypress', function(e){ if(e.keyCode==13){ table.searchTermByName($(this).attr('id'), $(this).val().trim());  } });
	$(".form-group input.searchBox").on('change', function(){ table.searchTermByName($(this).attr('id'), $(this).val().trim()); });
	$(".form-group input.searchBox").on('change', function(){ table.searchTermByName($(this).attr('id'), $(this).val().trim()); });
	*/
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
