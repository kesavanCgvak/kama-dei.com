<div id="updateBotDLG" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header" >
				<div class="input-group m-b-1">
					<span class="input-group-addon">BOT</span>
					<span class="form-control " disabled><?=$mapBotRecord->bot_name;?></span>
				</div>
			</div>
			<div class="modal-body">
				<div class="input-group m-b-1">
					<span class="input-group-addon">BOT Alias</span>
					<input class="form-control" id="botAliasUpdate" value="<?=$mapBotRecord->bot_alias;?>" maxlength="200" />
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-danger" style="float:left;width:91px;" onClick="closeModal('updateBotDLG')">Cancel</button>
				<button class="btn btn-info" style="width:91px;" onClick="updateBot()">Update</button>
			</div>
		</div>
	</div>
</div>
<script type="application/javascript">
	//---------------------------------------------------------------
	$(function(){
		$("#updateBotDLG").on("shown.bs.modal", function(){ $("#botAliasUpdate").focus(); });
	});
	//---------------------------------------------------------------
	function openUpdate(){
		$("#updateBotDLG").modal({backdrop:'static', keyboard:false});
	}
	//---------------------------------------------------------------
	function updateBot(){
		$("#updateBotDLG button, #botAliasUpdate").prop('disabled', true);
		var botAlias = $("#botAliasUpdate").val().trim();
		myLEX.lexBase.callBot('<?=$mapBotRecord->bot_name;?>', botAlias, null,
			//---------------------
			function(retVal, stack){
				myLEX.showConfirm(function(res){
					if(res){
						$.ajax({
							url: apiURL+'/api/dashboard/lex/mapping/clearjson/',
							type: 'delete',
							headers: {
//								'Accept': 'application/json',
//								'Content-Type': 'application/json'
							},
							data: {mapID:<?=$bot_id;?>, botAlias:botAlias },
							beforeSend: function(){},
							success: function(res){
								if(res.result == 0){ window.location.reload(); }
								else{ myLEX.showError(res.msg); }
							},
							error: function(e){ myLEX.showError('Server error'); }
						});
					}
					closeModal('updateBotDLG');
				},"Are you sure?");
				$("#updateBotDLG button, #botAliasUpdate").prop('disabled', false);
			},
			//---------------------
			function(xhr){
				myLEX.showError(xhr.responseJSON.message);
				$("#updateBotDLG button, #botAliasUpdate").prop('disabled', false);
			}		  
			//---------------------
		);
	}
	//---------------------------------------------------------------
</script>