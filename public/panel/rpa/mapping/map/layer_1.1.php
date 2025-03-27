<?php $rootType       = \App\RPAType::where('parent_id', 0)->where('structure_id', $mapBotRecord->structure_id)->first(); ?>
<?php $detailRootType = \App\RPAMapDetail::getData(0, $bot_id)->first(); ?>

<?php $rootType = \App\RPAType::find($rootType->id); ?>
<?php
$timeoutSwitch  = (($detailRootType!=null) ?$detailRootType->timeoutSwitch :0);
$lastID         = (($detailRootType!=null) ?$detailRootType->id :0);
$intentName     = (($detailRootType!=null) ?$detailRootType->intentName :'');
$apiVersion     = (($detailRootType!=null) ?$detailRootType->apiVersion :'');
$apiUrl         = (($detailRootType!=null) ?$detailRootType->apiUrl :'');
$organizationId = (($detailRootType!=null) ?$detailRootType->organizationId :'');
$deploymentId   = (($detailRootType!=null) ?$detailRootType->deploymentId :'');
$buttonId       = (($detailRootType!=null) ?$detailRootType->buttonId :'');
$timeout        = (($detailRootType!=null) ?$detailRootType->timeout :'');
$type_id        = $rootType->id;
?>

<div id="intentTabs">
	<div id="type0">
		<ul class="nav nav-tabs">
			<?php
			if($detailRootType!=null){
				?>
				<li id="li_<?=$detailRootType->id;?>" class="type_<?=$detailRootType->id;?>">
					<a data-toggle="tab" href="#type__<?=$detailRootType->id;?>" style="text-transform: capitalize">Mapping</a>
				</li>
				<?php
			}
			?>
		</ul>
	</div>

	<div class="tab-content type0">
		<?php
		if($detailRootType!=null){
			?>
			<div id="type__<?=$detailRootType->id;?>" class="tab-pane fade">
				<div class="intent_kr">
					<h3>Kama-DEI Knowledge Records</h3>
					<div id="liveAgentMapKRs">
						<?php
						$liveAgentMapKRs = \App\RPAMapKR::getData($detailRootType->id)->get();
						if(!$liveAgentMapKRs->isEmpty()):
						?>
						<div style="width:100%; padding-right:20px;">
						<table style="width:100%; border:1px solid #ddd;" id="knowledgeRecordTable">
						<thead style="height:50px">
							<th style="text-align: center; border-right: none">Knowledge Record</th>
							<th style="border-left: none"></th>
							<th style="text-align: center;border-right: none">Sample Utterance</th>
							<th style="text-align: center;">Pre-handoff Message</th>
							<th style="border-left: none"></th>
						</thead>
						<tbody>
						<?php
						foreach($liveAgentMapKRs as $tpMpKR):
						?>
							<tr id="row_<?=$tpMpKR->mapping_kr_id;?>">
							<td style="width:33%;border-right: none">
								<p class="form-control krVAL" id="krVAL_<?=$tpMpKR->mapping_kr_id;?>"><?=$tpMpKR->mappedTo;?></p>
							</td>
							<td style="width:102px;border-left: none">
								<button class="btn btn-info" style="height:35px;" 
									onClick='callSelectKR(<?="{$tpMpKR->kr_id},{$tpMpKR->mapping_detail_id},{$tpMpKR->mapping_kr_id}";?>)'>
									<small>Select KR</small>
								</button>
							</td>
							<td style="width:33%;border-right: none">
								<input class="form-control krsuV" 
									   data-id="<?=$tpMpKR->mapping_kr_id;?>" 
									   maxlength="128"
									   style="width:calc(100% - 55px); display:inline-block"
									   value="<?=$tpMpKR->sampleUtterance;?>"
									   readonly
								/>
								<button class="btn btn-link krsuE" onClick="krsuEC(this)" style="margin-left: 10px;" >
									<i class='fa fa-edit fa-2x'></i>
								</button>
								<button class="btn btn-link krsuS" onClick="krsuCC(this)" style="float:right;display:none;margin-left:8px;">
									<i class='fa fa-close fa-2x' style="color:red"></i>
								</button>
								<button class="btn btn-link krsuS" onClick="krsuSC(this)" style="float:right;display:none;">
									<i class='fa fa-save fa-2x'></i>
								</button>
							</td>
							<td style="width:33%;">
								<?php
									$pre_handoff_message = $tpMpKR->en()->first();
									if($pre_handoff_message==null){ $pre_handoff_messageEN=null; }
									else{ $pre_handoff_messageEN = $pre_handoff_message->pre_handoff_message; }
								?>
								<div id="krMSG_SHOW_<?=$tpMpKR->mapping_kr_id;?>" class="krMSG_SHOW">
									<div style="text-align: left">
										<table cellpadding="3" class="handoffLangs">
											<?php
											$lngs = [];
											$langs = \App\Language::whereRaw(
												"code in ( SELECT language FROM `organization_language` WHERE org_id=? ) or code=?",
												[$mapBotRecord->ownerId, 'en']
											)->get();
			
											if($langs->isEmpty()){ $lngs[] = ['code'=>'en', 'name'=>'English']; }
											else{ foreach($langs as $lang){ $lngs[] = ['code'=>$lang->code, 'name'=>$lang->name]; } }
											foreach($lngs as $lng):
											?>
											<tr class="handoff <?=(($lng['code']=='en')?'active':"deactive");?>"
												data-code="<?=$lng['code'];?>"
												data-mappingkrid="<?=$tpMpKR->mapping_kr_id;?>"
											>
												<td style="text-transform:capitalize;vertical-align:middle; border:none; width:100%">
													<?=$lng['name'];?>
													<i class="fa fa-spin fa-refresh fa-2x" style="float:right; display:none"></i>
												</td>
												<td style="border:none;">
													<button class="btn btn-link edit"
														onClick="showHideMSG(<?=$tpMpKR->mapping_kr_id;?>,0,'<?=$lng['code'];?>')"
													>
														<i class="fa fa-edit fa-2x"></i>
													</button>
													<button class="btn btn-link save"
														onClick="saveMSG(<?="{$tpMpKR->mapping_kr_id},0,'{$lng['code']}',{$tpMpKR->mapping_detail_id}"?>)"
													>
														<i class="fa fa-save fa-2x"></i>
													</button>
												</td>
												<td style="border:none;">
													<button class="btn btn-link delete" style="color: red"
														onClick="saveMSG(<?="{$tpMpKR->mapping_kr_id},1,'{$lng['code']}',{$tpMpKR->mapping_detail_id}"?>)"
													>
														<i class="fa fa-trash fa-2x"></i>
													</button>
													<button class="btn btn-link cancel" style="color: red"
														onClick="showHideMSG(<?=$tpMpKR->mapping_kr_id;?>,1,'<?=$lng['code'];?>')"
													>
														<i class="fa fa-close fa-2x"></i>
													</button>
												</td>
											</tr>
											<?php
											endforeach;
											?>
										</table>
									</div>
									<textarea
										id="krMSGTXT_<?=$tpMpKR->mapping_kr_id;?>"
										class="form-control krMSG"
										disabled><?=$pre_handoff_messageEN;?></textarea>
								</div>
							</td>
							<td style="width:43px;border-left: none">
								<button class="btn btn-danger" style="height: 35px;" title="delete"
										onClick='sendToController_mapping(<?="{$tpMpKR->mapping_kr_id},0,0";?>)'>
									<i class='fa fa-trash'></i>
								</button>
							</td>
							</tr>
						<?php
						endforeach;
						?>
						</tbody>
						</table>
						<?php
						endif;
						?>
					</div>
					<hr width="90%" style="margin-top:0">
					<div style="text-align: right">
						<button class="btn btn-success" style="height:35px; margin-right:20px;" id="newRowBTN"
								onClick='sendToController_mapping(<?="0, {$detailRootType->id},0";?>)'>
							New Row
						</button>
					</div>
				</div>
			</div>
			<?php
			}
		?>
	</div>

</div>

<script type="application/javascript">
	$("#timeout").on("keyup", function(key){
		let reg     = /^[0-9]+$/;
		let timeout = $("#timeout").val().trim();
		if(timeout==''){ return; }
		if( !reg.test(timeout) ){
			myClass.showError("Invalid Timeout");
		}
	});
	$("#timeoutSwitch").on("change", function(){
		$("#timeout").prop('disabled', !($("#timeoutSwitch").prop('checked')));
		if($("#timeoutSwitch").prop('checked')==false){
			$("#timeout").prop('disabled', true);
			$("#timeout").val('');
		}else{
			$("#timeout").focus();
		}
	});
	//---------------------------------------------------------
	let lastId = <?=$lastID;?>;
	//---------------------------------------------------------
	function showHideMSG( mapping_kr_id, flag, lang){
		if(flag==0){
			let data = {
				mapping_kr_id: mapping_kr_id,
				lang_code    : lang
			};

			$.ajax({
				url: apiURL+'/api/dashboard/rpa/mapping/pre_handoff/get',
				type: 'post',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json'
				},
				data: JSON.stringify(data),
				beforeSend: function(){
					$("tr.handoff i.fa-spin").hide();
					$("tr.handoff").each(function(){
						if($(this).data('mappingkrid')==mapping_kr_id){
							$(this).removeClass("active");
							$(this).addClass("deactive");
							if($(this).data('code')==lang){
								$(this).removeClass("deactive");
								$(this).addClass("active");
								$(this).find("i.fa-spin").show();
							}
						}
					});
				},
				complete: function(){ $("tr.handoff i.fa-spin").hide(); },
				success: function(res){
					if(res.result == 0){
						$("#krMSGTXT_"+mapping_kr_id).val("");
						if(res.data!=null){ $("#krMSGTXT_"+mapping_kr_id).val(res.data.pre_handoff_message); }
						$("#krMSGTXT_"+mapping_kr_id).prop('disabled', false);
						$("#krMSGTXT_"+mapping_kr_id).focus();
						
						$("tr.handoff").each(function(){
							let mappingkrid = $(this).data('mappingkrid');
							if(mappingkrid==mapping_kr_id){
								$(this).removeClass("active");
								$(this).addClass("deactive");
								$(this).find(".btn").prop("disabled", true);
								$(this).find(".btn.edit"  ).show();
								$(this).find(".btn.delete").show();
								$(this).find(".btn.save"  ).hide();
								$(this).find(".btn.cancel").hide();
								let code = $(this).data('code');
								if(code==lang){
									$(this).removeClass("deactive");
									$(this).addClass("active");
									$(this).find(".btn").prop("disabled", false);
									$(this).find(".btn.edit"  ).hide();
									$(this).find(".btn.delete").hide();
									$(this).find(".btn.save"  ).show();
									$(this).find(".btn.cancel").show();
								}
							}
						});
						
					}else{ myClass.showError(res.msg); }
				},
				error: function(e){ myClass.showError(e.status+" : "+e.statusText); }
			});
		}
		if(flag==1){
			$("#krMSGTXT_"+mapping_kr_id).prop('disabled', true);
			$("tr.handoff").each(function(){
				let mappingkrid = $(this).data('mappingkrid');
				if(mappingkrid==mapping_kr_id){
					$(this).removeClass("active");
					$(this).addClass("deactive");
					$(this).find(".btn").prop("disabled", false);
					$(this).find(".btn.edit"  ).show();
					$(this).find(".btn.delete").show();
					$(this).find(".btn.save"  ).hide();
					$(this).find(".btn.cancel").hide();
					let code = $(this).data('code');
					if(code==lang){
						$(this).removeClass("deactive");
						$(this).addClass("active");
						$(this).find(".btn.edit"  ).show();
						$(this).find(".btn.delete").show();
						$(this).find(".btn.save"  ).hide();
						$(this).find(".btn.cancel").hide();
					}
				}
			});
		}
	}
//------------------------------------------------------------------------------------------
	function saveMSG(mapping_kr_id, flag, lang, mappingBot_id){
		let data = {
			mapping_kr_id       : mapping_kr_id,
			mappingBot_id       : mappingBot_id,
			lang_code           : lang,
			pre_handoff_message : $("#krMSGTXT_"+mapping_kr_id).val().trim(),
			data_deleted        : flag
		};
		if(data.data_deleted==1){ data.pre_handoff_message="-"; }
		$.ajax({
			url: apiURL+'/api/dashboard/rpa/mapping/pre_handoff/set',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){
				$("tr.handoff i.fa-spin").hide();
				$("tr.handoff").each(function(){
					if($(this).data('mappingkrid')==mapping_kr_id){
						$(this).removeClass("active");
						$(this).addClass("deactive");
						if($(this).data('code')==lang){
							$(this).removeClass("deactive");
							$(this).addClass("active");
							$(this).find("i.fa-spin").show();
						}
					}
				});
			},
			complete: function(){ $("tr.handoff i.fa-spin").hide(); },
			success: function(res){
				if(res.result == 0){
					if(flag==1){ $("#krMSGTXT_"+mapping_kr_id).val(""); }
					showHideMSG( mapping_kr_id, 1, lang);
				}else{ myClass.showError(res.msg); }
			},
			error: function(e){ myClass.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	function krsuEC(obj){
		$(obj).hide();
		$(obj).parent().find('.krsuS').show();
		$(obj).parent().find('.krsuV').prop('readonly', false).focus();
	}

	function krsuCC(obj){
		$(obj).parent().find('.krsuS').hide();
		$(obj).parent().find('.krsuE').show();
		$(obj).parent().find('.krsuV').prop('readonly', true);
	}
	function krsuSC(obj){
		let data = {
			mapping_kr_id  : $(obj).parent().find('.krsuV').data('id'),
			sampleUtterance: $(obj).parent().find('.krsuV').val().trim()
		};

		$.ajax({
			url: apiURL+'/api/dashboard/rpa/mapping/sampleutterance',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){},
			success: function(res){
				if(res.result == 0){
					$(obj).parent().find('.krsuS').hide();
					$(obj).parent().find('.krsuE').show();
					$(obj).parent().find('.krsuV').prop('readonly', true);
				}else{ myClass.showError(res.msg); }
			},
			error: function(e){ myClass.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	$(function(){
		$(".krLng").on('change', function(){
			let data = {
				mapping_kr_id: $(this).data('id'),
				lang_code    : $(this).val()
			};

			$.ajax({
				url: apiURL+'/api/dashboard/rpa/mapping/pre_handoff/get',
				type: 'post',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json'
				},
				data: JSON.stringify(data),
				beforeSend: function(){},
				success: function(res){
					if(res.result == 0){
						$("#krMSG_SHOW_"+data.mapping_kr_id+" textarea").val(res.data.pre_handoff_message);
						$("#krMSG_EDIT_"+data.mapping_kr_id+" textarea").val(res.data.pre_handoff_message);
						$("#krLngShow_"+data.mapping_kr_id).text(data.lang_code);
					}else{ myClass.showError(res.msg); }
				},
				error: function(e){ myClass.showError(e.status+" : "+e.statusText); }
			});
		});
	})
</script>
