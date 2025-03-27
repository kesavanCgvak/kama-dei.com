<style>
	#dataClassification table th{ font-size:13px; }

	#dataClassification table td:nth-child(1){ font-size:12px; text-align:left;  vertical-align:top;                }
	#dataClassification table td:nth-child(2){ font-size:11px; text-align:center;vertical-align:middle;width:90px   }
	#dataClassification table td:nth-child(3){ font-size:11px; text-align:center;vertical-align:middle;width:90px   }
	#dataClassification table td:nth-child(4){ font-size:12px; text-align:left;  vertical-align:middle;width:150px; }
	#dataClassification table td:nth-child(5){ font-size:12px; text-align:left;  vertical-align:middle;width:250px; }
	#dataClassification table td:nth-child(6){ font-size:11px; text-align:center;vertical-align:middle;width:90px   }
	#dataClassification table td:nth-child(7){ font-size:12px; text-align:center;vertical-align:middle;width:40px;  }
</style>
<select class="form-control" id="myTableList" style="display:none" onchange="setNewList($(this).val())">
	<option value="-1" selected="selected">Table All</option>
</select>
<div id="dataClassification"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL    = "<?=env('API_URL');?>";
	var orgID     = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
	var table;
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
//------------------------------------------------------------

//------------------------------------------------------------
$(function(){
	//--------------------------------------------------------
	var tmp = $("#myTableList");
	$(".pull-right.search").prepend(tmp);
	$(".pull-right.search .form-control").css('width', '48%');
	$(".pull-right.search .form-control").css('margin-right', '2%');
	$(".pull-right.search .form-control").css('display', 'inline-block');
	$("#dataClassification table").bootstrapTable().on('refresh.bs.table', function(){ getTableList($("#myTableList").val()); });
	getTableList(-1);
	//--------------------------------------------------------
	$("#isVisible").on('change', function(){
		if($(this).prop('checked')==false){ 
			table.editItem['isVisible'           ]=0;
			table.editItem['isEditableByPassword']=0;
			$("#isEditableByPassword").prop('checked', false);
			$("#isEditableByPassword").prop('disabled', true);
		}else{
			$("#isEditableByPassword").prop('disabled', false);
		}
	});
	//--------------------------------------------------------
	$("#tableNames").on('change', function(){ getFieldist($(this).val(), ''); });
	//--------------------------------------------------------
});
//------------------------------------------------------------

//------------------------------------------------------------
function getTableList(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/data_classification/alltables/',
		function(retVal){
			$("#myTableList option").remove();
			$("#tableNames option").remove();
			$("#myTableList").append('<option value="-1">Table All</option>');
			for(var i in retVal.data){ 
				$("#myTableList").append('<option value="'+retVal.data[i]+'">'+retVal.data[i]+'</option>'); 
				$("#tableNames").append('<option value="'+retVal.data[i]+'">'+retVal.data[i]+'</option>'); 
			}
			$("#myTableList").val(id)
		}
	);
}
//------------------------------------------------------------

//------------------------------------------------------------
function setNewList(id){ $("#dataClassification table").bootstrapTable('refresh'); }
//------------------------------------------------------------

//------------------------------------------------------------
function getFieldist(tableName, fieldName){
	if(tableName==''){
		$("#fieldName option").remove();
		$("#fieldName option").remove();
		$("#fieldName").append('<option value="">Select . . .</option>');
		return;
	}
	$.get(
		'<?=env('API_URL');?>/api/dashboard/data_classification/allfileds/'+tableName,
		function(retVal){
			$("#fieldName option").remove();
			$("#fieldName option").remove();
			$("#fieldName").append('<option value="">Select . . .</option>');
			for(var i in retVal.data){ 
				$("#fieldName").append('<option value="'+retVal.data[i]+'">'+retVal.data[i]+'</option>'); 
			}
			$("#fieldName").val(fieldName)
		}
	);
}
//------------------------------------------------------------

//------------------------------------------------------------
</script>