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

#editPersonality, #addPersonality {
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

#editPersonality.show, #addPersonality.show {
	display: block;
}

#editPersonality > form, #addPersonality > form {
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

#editPersonality > form input, #addPersonality > form input{ width: 250px; }

.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }

.row-actions{ text-align: center; }

.row-actions > a:first-child{ padding-right: 10px; }

button.btn.personalityBTN{ width:110px;margin-right:10px }
div.half{ width:50%;display:inline-block;padding:0 2px;text-align:left; }
a.callGetUser{ color:blue;text-decoration:none; }
a.callGetUser:hover{ color:red;cursor:pointer; }

#personality table th{ font-size:13px; }
#personality table th>div:nth-child(1){ padding-right:10px; }

#personality table td:nth-child(1){ vertical-align:middle;font-size:12px;width:50px;text-align:right; }
#personality table td:nth-child(2){ vertical-align:middle;font-size:13px;width:180px;text-align:left; }
#personality table td:nth-child(4){ vertical-align:middle;font-size:13px;width:130px;text-align:left; }
#personality table td:nth-child(5){ vertical-align:middle;font-size:12px;text-align:left; }
#personality table td:nth-child(6){ vertical-align:middle;font-size:12px;width:100px;text-align:center; }
#personality table td:nth-child(7){ vertical-align:middle;font-size:12px;width:150px;text-align:left; }
#personality table td:nth-child(8){ vertical-align:middle;font-size:12px;width: 80px;text-align:center; }
#personality table td:nth-child(9){ vertical-align:middle;font-size:12px;width: 40px;text-align:center; }

#personality table td:nth-child(3)
	{ vertical-align:middle;font-size:12px;min-width:100px;text-align:left;max-width:150px;word-break:break-all; }

.col-ownerId{ width: 100% !important; }
<?php if($orgID!=0): ?>
div.isAdminOnley{ display:none !important; }
<?php endif; ?>
</style>
<select class="form-control" id="myOwnersList" style="display:none">
	<option value="-1" selected="selected">Owner All</option>
</select>
<div id="personality"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
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
	$("#personality table").bootstrapTable().on('refresh.bs.table', function(){ getOwnersList($("#myOwnersList").val()); });
	getOwnersList(-1);
	//-------------------------------------------------
	$("#personaPersonalitySelect")
		.on("change", function(){
			if($(this).val()==0){
				$("#personaPersonalityRadio0").prop('disabled', true);
				$("#personaPersonalityRadio1").prop('disabled', true);
				
				$("#personaPersonalityRadio1").prop('checked', false).change();
				$("#personaPersonalityRadio0").prop('checked', false).change();

				$(".targetName label").text('');
				$(".targetName"  ).hide();
				$(".clone2Select").hide();
				$("#cloneName"   ).val('');
			}else{
				$("#personaPersonalityRadio0").prop('disabled', false);
				$("#personaPersonalityRadio1").prop('disabled', false);
				$("#clone2Select").val($("#personaPersonalitySelect option:selected").data('parentid'));
			}
			$("#clone2Select").prop('disabled', true);
		});
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
			$("#clone2Select").val($("#personaPersonalitySelect option:selected").data('parentid'));
			$("#clone2Select").prop('disabled', true);
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
/*
				if( $("#cloneownership0").prop('checked') ){ data.ownership=0; }
				if( $("#cloneownership1").prop('checked') ){ data.ownership=1; }
				if( $("#cloneownership2").prop('checked') ){ data.ownership=2; }
*/
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
				if( data.sourcePersonalityId=='' ){ 
					table.showError("Please select Persona/Personality");
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
/*
				if( data.ownership==-1 ){ 
					table.showError("Please select Ownership");
					$("#cloneName").focus();
					return;
				}
*/
				//-------------------------------------
				$.ajax({
					url:apiURL+'/api/dashboard/personality/clone',
					method:'put',
					data:data,
					success:function(ret){
						if(ret.result==1){ table.showError(ret.msg); }
						else{ 
							table.showSuccess(ret.msg);
							$("#cloneItem").modal('hide');
							$('#personality table').bootstrapTable('refresh');
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
		'<?=env('API_URL');?>/api/dashboard/personality/personalityowners/<?=$orgID;?>',
		function(retVal){
			$("#myOwnersList option").remove();
			$("#myOwnersList").append('<option value="-1">Owner All</option>');
			for(var i in retVal.data){ $("#myOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#myOwnersList").val(id)
		}
	);
}
//-----------------------------------------------------
function callGetUser(id){
	$("#callGetUser #userName").val('');
	$("#callGetUser #userEmail").val('');
	$.ajax({
		url:apiURL+'/api/dashboard/personality/getuserdate/'+id,
		method:'get',
		data:{},
		success:function(ret){
			if(ret.result==1){ table.showError(ret.msg); }
			else{
				$("#callGetUser #userName").val(ret.name);
				$("#callGetUser #userEmail").val(ret.email);
				$("#callGetUser").modal({backdrop:'static', keyboard:false});
			}
		},
		error: function(xhr, s, t){ table.showError(xhr.status+": "+t); }
	});
}
//-----------------------------------------------------
</script>
<div class="modal fade cloneItem" tabindex="-1" role="dialog" aria-labelledby="cloneItem" aria-hidden="true" id="callGetUser">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header"><h3 style="margin:2px 10px"></h3></div>
			<div class="modal-body">
				<div>
					<label>Name</label>
					<input class='form-control' id='userName' value="" disabled="disabled"/>
				</div>
				<div style="margin-top:20px;">
					<label>Email</label>
					<input class='form-control' id='userEmail' value="" disabled="disabled"/>
				</div>
			</div>
			<div class="modal-fotter">
				<div style="width:100%;border-top:1px dotted #ccc;padding:12px 20px 16px;" align="right">
					<button type="button" class="btn btn-default btn-no" style="width:100%;" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</div>
