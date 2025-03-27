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

#editPersona, #addPersona {
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

#editPersona.show, #addPersona.show {
	display: block;
}

#editPersona > form, #addPersona > form {
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

#editPersona > form input, #addPersona > form input {
	width: 250px;
}

.react-bs-table-bordered, .react-bs-container-body {
	height: auto !important;
}

.row-actions {
	text-align: center;
}

.row-actions > a:first-child {padding-right: 10px; }

button.btn.personalityBTN{ width:110px;margin-right:10px }

div.half{ width:50%;display:inline-block;padding:0 2px;text-align:left; }
<?php if($orgID!=0): ?>
div.isAdminOnley{ display:none !important; }
<?php endif; ?>

#persona table th{ font-size:13px; }
#persona table td:nth-child(2),
#persona table td:nth-child(3),
#persona table td:nth-child(7){ font-size:13px !important;}
#persona table td:nth-child(4),
#persona table td:nth-child(5),
#persona table td:nth-child(1),
#persona table td:nth-child(6){ font-size:12px !important;}
#persona table td:nth-child(1){ text-align:right !important;}
#persona table td:nth-child(4),
#persona table td:nth-child(6),
#persona table td:nth-child(7){ text-align:center !important;}
#persona table td:nth-child(4),
#persona table td:nth-child(6),
#persona table th:nth-child(4),
#persona table th:nth-child(6){ width:60px !important;}
#persona table td:nth-child(2){ width:250px !important;}
#persona table td:nth-child(3){ width:200px !important;}
#persona table td:nth-child(7){ width:40px !important;}
</style>
<select class="form-control" id="myOwnersList" style="display:none" onchange="setNewList($(this).val())">
	<option value="-1" selected="selected">Owner All</option>
</select>
<div id="persona"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
	var NoPersonaID = '<?=Config::get('kama_dei.static.No_Persona',0);?>';
	var table;
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
//-----------------------------------------------------
$(function(){
	//-------------------------------------------------
	var tmp = $("#myOwnersList");
	$(".pull-right.search").prepend(tmp);
	$(".pull-right.search .form-control").css('width', '50%');
	$(".pull-right.search .form-control").css('display', 'inline-block');
	$("#persona table").bootstrapTable().on('refresh.bs.table', function(){ getOwnersList($("#myOwnersList").val()); });
	getOwnersList(-1);
	//-------------------------------------------------
	$("#personaPersonalityRadio0")
		.on('change', function(){
			$(".targetName label").text('Target persona name :');
			$(".targetName"  ).show();
			$(".clone2Select").hide();
			$("#cloneName"   ).focus();
		});
	$("#personaPersonalityRadio1")
		.on('change', function(){
			$(".targetName label").text('Target personality name :');
			$(".targetName"  ).show();
			$(".clone2Select").show();
			$("#cloneName"   ).focus();
		});
	//-------------------------------------------------
	$("#cloneItem .btn-clone")
		.on('click', 
			function(){
				//-------------------------------------
				if($("#personaPersonalityRadio0").prop('checked')==false && $("#personaPersonalityRadio1").prop('checked')==false ){
					table.showError("Please select Target [Persona/Personality]");
					return;
				}
				//-------------------------------------
				var data = {};
				data.sourcePersonalityId = $("#personaPersonalitySelect").val();
				data.personalityName     = $("#cloneName").val().trim();
				data.orgId               = orgID;
				data.userId              = userID;
				<?php if($orgID==0): ?>
				data.ownerId   = $("#cloneOwnerID").val().trim();
				data.ownership = -1;
				if( $("#cloneownership0").prop('checked') ){ data.ownership=0; }
				if( $("#cloneownership1").prop('checked') ){ data.ownership=1; }
				if( $("#cloneownership2").prop('checked') ){ data.ownership=2; }
				<?php else: ?>
				data.ownerId   = orgID;
				data.ownership = 2;
				<?php endif; ?>
				//-------------------------------------
				if($("#personaPersonalityRadio0").prop('checked')==true){ data.parentPersonaId = '0'; }
				if($("#personaPersonalityRadio1").prop('checked')==true){ data.parentPersonaId = $("#clone2Select").val(); }
				//-------------------------------------
				if(data.parentPersonaId==''){
					table.showError("Please select persona parent");
					return;
				}
				//-------------------------------------
				if( data.sourcePersonalityId==0 ){ 
					table.showError("Please select Persona");
					$("#personaPersonalitySelect").focus();
					return;
				}
				//-------------------------------------
				if( data.personalityName=='' ){ 
					table.showError("Please enter Name");
					$("#cloneName").focus();
					return;
				}
				//-------------------------------------
				if( data.ownerId=='' ){ 
					table.showError("Please select Owner");
					$("#cloneName").focus();
					return;
				}
				//-------------------------------------
				if( data.ownership==-1 ){ 
					table.showError("Please select Ownership");
					$("#cloneName").focus();
					return;
				}
				//-------------------------------------
				$.ajax({
					url:apiURL+'/api/dashboard/persona/clone',
					method:'put',
					data:data,
					success:function(ret){
						if(ret.result==1){ table.showError(ret.msg); }
						else{ 
							table.showSuccess(ret.msg);
							$("#cloneItem").modal('hide');
							$('#persona table').bootstrapTable('refresh');
						}
					},
					error: function(xhr, s, t){ table.showError(xhr.status+": "+t); }
				});
				//-------------------------------------
			}
		);
});
//-----------------------------------------------------
function getOwnersList(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/persona/personaowners/<?=$orgID;?>',
		function(retVal){
			$("#myOwnersList option").remove();
			$("#myOwnersList").append('<option value="-1">Owner All</option>');
			for(var i in retVal.data){ $("#myOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#myOwnersList").val(id)
		}
	);
}
//-----------------------------------------------------
function setNewList(id){ $("#persona table").bootstrapTable('refresh'); }
//-----------------------------------------------------
</script>
