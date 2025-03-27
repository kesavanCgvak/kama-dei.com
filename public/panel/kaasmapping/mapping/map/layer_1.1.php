<?php $rootType       = \App\KaasType::where('parent_id', 0)->where('structure_id', $mapBotRecord->structure_id)->first(); ?>
<?php $detailRootType = \App\KaasMapDetail::getData(0, $bot_id)->get(); ?>

<div id="intentTabs">
	<div id="type0">
		<ul class="nav nav-tabs">
			<?php
			if(!$detailRootType->isEmpty()){
				foreach($detailRootType as $tmp){
					?>
					<li id="li_<?=$tmp->id;?>" class="type_<?=$tmp->id;?>">
						<a data-toggle="tab" href="#type_<?=$tmp->id;?>" style="text-transform: capitalize"><?=$tmp->val1;?></a>
					</li>
					<?php
				}
			}
			?>
			<li class="active" style="float:right">
				<a data-toggle="tab" href="#newType0" style="text-transform: capitalize">New <?=$rootType->name;?></a>
			</li>
		</ul>
	</div>

	<div class="tab-content type0">
		<?php
		if(!$detailRootType->isEmpty()){
			$rootType = \App\KaasType::find($detailRootType[0]->type_id);
			foreach($detailRootType as $tmp){
			?>
			<div id="type_<?=$tmp->id;?>" class="tab-pane fade">
				<div class="intent_data">
					<h3 style="text-transform:capitalize"><?=$rootType->name;?></h3>
					<hr width="90%" style="margin-top:0">
					<div style="width:90%;margin:auto;">
						<div class="input-group m-b-1" style="margin-bottom:15px">
							<span class="input-group-addon"><?=$rootType->name;?> Name</span>
							<input id="val1_<?=$tmp->id;?>" class="form-control" value="<?=$tmp->val1;?>" />
						</div>
						<div class="input-group m-b-1" style="margin-bottom:15px; display: none">
							<span class="input-group-addon"><?=$rootType->name;?> Versions / Alias</span>
							<input id="val2_<?=$tmp->id;?>" class="form-control" value="<?=$tmp->val2;?>" />
						</div>
						<div class="input-group m-b-1" style="margin-bottom:15px">
							<span class="input-group-addon" style="vertical-align: top">Sample Utterance</span>
							<textarea class="form-control sampleUtterance" id="val3_<?=$tmp->id;?>"><?=$tmp->val3;?></textarea>
						</div>
						<button class="btn btn-info" style="float:right;" onClick='callEdit(<?=$tmp->id;?>)'>
							<small>Save Changes</small>
						</button>
						<button class="btn btn-danger" style="float:left; text-transform: capitalize" onClick='callDelete(<?=$tmp->id;?>)'>
							<small>Delete <?=$rootType->name;?></small>
						</button>
					</div>
				</div>

				<div class="intent_kr">
					<h3>Kama-DEI Knowledge Records</h3>
					<hr width="90%" style="margin-top:0">
					<div>
						<div style="width:100%">
							<div class="krVAL_DIV">
								<div class="input-group m-b-1">
									<span class="input-group-addon">Knowledge Record</span>
									<p class="form-control krVAL" id="krVAL_<?=$tmp->id;?>"><?=$tmp->mappedTo;?></p>
								</div>
							</div>
							<button class="btn btn-info" style="float:right;" onClick='callSelectKR(<?=$tmp->kr_id;?>, <?=$tmp->id;?>)'>
								<small>Select KR</small>
							</button>
						</div>
					</div>
				</div>

				<?php
				if(\App\KaasType::where('parent_id', $rootType->id)->where('structure_id', $mapBotRecord->structure_id)->count()>0){
					$baseTypeID = $rootType->id;
					$baseDetail = $tmp;
//					$baseDetailID = $tmp->id;
					include('layer_1.2.php');
				}
				?>
			</div>
			<?php
			}
		}
		?>
		<?php $rootType = \App\KaasType::where('parent_id', 0)->where('structure_id', $mapBotRecord->structure_id)->first(); ?>
		<div id="newType0" class="tab-pane fade in active">
			<h4 style="text-transform:capitalize; padding-left:10px; padding-top:10px; margin-bottom:-5px;">
				New <?=$rootType->name;?>:
			</h4>
			<input type="hidden" id="newMappingBot_id0" value="<?=$bot_id;?>" />
			<input type="hidden" id="newType_id0" value="<?=$rootType->id;?>" />
			<div style="max-width:70%; margin:5px 0; display:inline-block;">
				<div class="input-group m-b-1" style="margin:5px 0 10px">
					<span class="input-group-addon"><?=$rootType->name;?> name</span>
					<input class="form-control" id="newVal10" value="" maxlength="200" />
				</div>
				<div class="input-group m-b-1" style="margin:5px 0 10px; display: none">
					<span class="input-group-addon"><?=$rootType->name;?> Versions / Alias</span>
					<input class="form-control" id="newVal20" value="2" maxlength="200" />
				</div>
				<div class="input-group m-b-1" style="margin:5px 0 10px">
					<span class="input-group-addon">Sample Utterance</span>
					<input class="form-control" id="newVal30" value="" maxlength="200" />
				</div>
			</div>
			<div style="width:20%; display:inline-block; vertical-align:top; text-align:right; margin: 10px 0;">
				<button style="width:70%;" class="btn btn-success" onClick="callAddNew(0)">Add</button>
			</div>
		</div>
	</div>

</div>

<script type="application/javascript">
	//---------------------------------------------------------
	function callEdit(id){
		var data = {};
		data.id      = id;
		data.val1    = $("#val1_"+id).val().trim();
		data.val2    = null;
		data.val3    = $("#val3_"+id).val();
		data.user_id = "<?=$session->get('userID');?>";

		if(data.val1==""){
			myKAAS.showError("invalid value");
			$("#val1"+id).focus();
			return;
		}
		$.ajax({
			url: apiURL+'/api/dashboard/kaas/mapping/editrow',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ },
			success: function(res){
				if(res.result == 0){
					$("li."+res.tag+">a").text(data.val1);
					$("span."+res.tag).text(data.val1);
					myKAAS.showSuccess("data changed");
				}else{ myKAAS.showError(res.msg); }
			},
			error: function(e){ myKAAS.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	function callDelete(id, hide=true){
		var data = {};
		data.id  = id;
		$.ajax({
			url: apiURL+'/api/dashboard/kaas/mapping/deleterow',
			type: 'delete',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ },
			success: function(res){
				if(res.result == 0){
					$("#"+res.tag).remove();
					$("li."+res.tag).remove();
					if(hide){ $(".value_data").html(""); }
					myKAAS.showSuccess("data deleted");
				}else{ myKAAS.showError(res.msg); }
			},
			error: function(e){ myKAAS.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	function callAddLayer(){
		var data = {};
		data.parent_id     = $("#newVal1_V_parent_id").val().trim();
		data.mappingBot_id = $("#newVal1_V_bot_id"   ).val().trim();
		data.type_id       = $("#newVal1_V_type_id"  ).val().trim();
		data.val1          = $("#newVal1_V_Val"      ).val().trim();
		data.val2          = null;
		data.val3          = null;
		data.user_id       = "<?=$session->get('userID');?>";;

		if(data.val1==""){
			myKAAS.showError("invalid value");
			$("#newVal1_V_"+parent_id).focus();
			return;
		}
		$.ajax({
			url: apiURL+'/api/dashboard/kaas/mapping/newrow',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ },
			success: function(res){
				if(res.result == 0){
					console.log(res);
					$("#newVal1_V_Val").val('');
					if($("#newVal1_V_layer").val().trim()==2){
						$("#tbl_"+data.parent_id+" tbody").append(
							$('<tr id="type_'+res.id+'">')
								.append(
									$('<td>')
										.append('<input class="form-control" id="val1_'+res.id+'" value="'+data.val1+'" style="width:calc(100% - 150px); display: inline-block;"/>')
										.append('<button class="btn btn-info" style="float:right; margin-right:5px;" onClick="callEdit('+res.id+')"><small>Save Changes</small></button>')
								)
								.append(
									$('<td>')
										.append('<button class="btn btn-danger" style="float:left; text-transform: capitalize; margin-left:5px" onClick="callDelete('+res.id+')"><small>Delete</small></button>')
										.append('<button class="btn btn-info" style="float:right; margin-right:5px;" onClick="loadLayer3('+data.parent_id+','+res.id+','+data.mappingBot_id+','+res.childLayer+')"><small>Show Values</small></button>')
								)
						);
					}else{
						$("#tbl_"+data.parent_id+" tbody").append(
							$('<tr id="type_'+res.id+'">')
								.append(
									$('<td>')
										.append('<input class="form-control" id="val1_'+res.id+'" value="'+data.val1+'" />')
										.append('<input style="display: none" id="val2_'+res.id+'" value="'+data.val2+'" />')
										.append('<button class="btn btn-info" style="float:right;" onClick="callEdit('+res.id+')"><small>Save Changes</small></button>')
										.append('<button class="btn btn-danger" style="float:left; text-transform: capitalize" onClick="callDelete('+res.id+', false)"><small>Delete</small></button>')
								)
								.append(
									$('<td>')
										.append('<p class="form-control krVAL" id="krVAL_'+res.id+'"></p>')
										.append('<button class="btn btn-info" style="float:right;" onClick="callSelectKR(0, '+res.id+')"><small>Select KR</small></button>')
								)
						);
					}
					$("#addLayer2").modal("hide");
				}else{ myKAAS.showError(res.msg); }
			},
			error: function(e){ myKAAS.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	function callAddLayerModal(baseDetailID, layer2ID, bot_id, layer2Name, layer=2){
		$("#addLayer2 .modal-title").text("add "+layer2Name);
		$("#addLayer2 .layerName").text(layer2Name+" name");
		$("#newVal1_V_parent_id").val(baseDetailID);
		$("#newVal1_V_bot_id"   ).val(bot_id      );
		$("#newVal1_V_type_id"  ).val(layer2ID    );
		$("#newVal1_V_layer"    ).val(layer       );
		$("#newVal1_V_Val"      ).val('');
		
		$("#addLayer2").modal({show:true, keyboard: false, backdrop:"static"});
	}
	//---------------------------------------------------------
	function loadLayer3(baseDetail_id, parent_id, bot_id, layer_id){
		$("#valueData"+baseDetail_id).html("");
		let div = $("<div>").attr({style:"width:100%; text-align:center"});
		let i   = $("<i>").attr({class:"fa fa-spin fa-refresh fa-5x"});
		$(div).append(i);
		$("#valueData"+baseDetail_id).append(div);
		
		var data = {};
		data.parent_id = parent_id;
		data.bot_id    = bot_id;
		data.layer_id  = layer_id;

		if(data.val1==""){
			myKAAS.showError("invalid value");
			$("#newVal1_V_"+parent_id).focus();
			return;
		}
		$.ajax({
			url: apiURL+'/api/dashboard/kaas/mapping/getlayer',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ },
			complete: function(){  },
			success: function(res){
				$("#valueData"+baseDetail_id).html("");
				if(res.result == 0){
					let tbody = $("<tbody>");
					for(let j in res.data){
						$(tbody)
							.append(
								$("<tr id='type_"+res.data[j].id+"'>")
									.append(
										$("<td>")
											.append('<input class="form-control" id="val1_'+res.data[j].id+'" value="'+res.data[j].val1+'" />')
											.append('<button class="btn btn-info" style="float:right;" onClick="callEdit('+res.data[j].id+')"><small>Save Changes</small></button>')
											.append('<button class="btn btn-danger" style="float:left; text-transform: capitalize" onClick="callDelete('+res.data[j].id+', false)"><small>Delete</small></button>')
									)
									.append(
										$("<td>")
											.append('<p class="form-control krVAL" id="krVAL_'+res.data[j].id+'"></p>')
											.append('<button class="btn btn-info" style="float:right;" onClick="callSelectKR(0, '+res.data[j].id+')"><small>Select KR</small></button>')
									)
							);
					}
					let tbl = 
						$(
							$("<table>")
								.attr({class:"valueTBL", id:"tbl_"+parent_id})
								.append(
									$("<thead>")
										.append(
											$("<tr>")
												.attr({style: "height: 40px;"})
												.append("<th style='width:40%; text-transform:capitalize; font-size:small; padding-left: 10px'>"+res.parentLayer.name+": <span class='type_"+res.parentData.id+"' style='font-weight: 100'>"+res.parentData.val1+"</span></th>")
												.append("<th style='width:60%; text-transform:capitalize; font-size:small; padding-left: 10px'>Kama-DEI Knowledge Records</th>")
										)
								)
								.append(tbody)
						);
					let div= $(
							$("<div>")
								.attr({style: "width:90%;margin:auto;"})
								.append(
									$("<div>")
										.attr({style: "padding-bottom: 4px;", class: "fixed-table-container"})
										.append(
											$("<div>")
												.attr({style: "margin-right: 0px;", class: "fixed-table-header"})
												.append( tbl )
										)
								)
							);
					$("#valueData"+baseDetail_id)
						.append('<h3 style="text-transform:capitalize">'+res.layer.name+'</h3>')
						.append(
							$('<div>')
								.attr({style: 'width:100px; display:inline-block; vertical-align:top; text-align:right; margin: -40px 0 10px; float: right'})
								.append(
									$('<button>')
										.attr({
											style: "width:100%;",
											class: "btn btn-success",
											onClick: "callAddLayerModal("+parent_id+","+layer_id+", "+bot_id+", '"+res.layer.name+"', 3)"
										})
										.append('<small style="text-transform:capitalize;">Add '+res.layer.name+'</small>')
								)
						)
						.append('<hr width="100%" style="margin-top:0">')
						.append(div)
						.append('');
				}else{ myKAAS.showError(res.msg); }
			},
			error: function(e){
				$("#valueData"+baseDetail_id).html("");
				myKAAS.showError(e.status+" : "+e.statusText);
			}
		});
	}
	//---------------------------------------------------------
</script>

<div id="addLayer2" class="modal fade" role="dialog">
	<div class="modal-dialog">

	<!-- Modal content-->
	<div class="modal-content">
		<div class="modal-header" style="background:#00a6b4; border-radius:5px 0;">
			<h4 class="modal-title" style="text-transform: capitalize">Modal Header</h4>
		</div>
		<div class="modal-body">
			<input type="hidden" id="newVal1_V_parent_id" value="" />
			<input type="hidden" id="newVal1_V_type_id" value="" />
			<input type="hidden" id="newVal1_V_bot_id" value="" />
			<input type="hidden" id="newVal1_V_layer" value="" />
			
			<div class="input-group m-b-1" style="margin:5px 0 10px">
				<span class="input-group-addon layerName"></span>
				<input class="form-control" id="newVal1_V_Val" value="" maxlength="200" />
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-info" style="width:70px;" onClick="callAddLayer()">Add</button>
			<button type="button" class="btn btn-danger" data-dismiss="modal" style="float:left; width:70px;">Close</button>
		</div>
	</div>

	</div>
</div>
