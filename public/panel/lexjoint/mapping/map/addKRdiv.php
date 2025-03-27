<style>
	.col-leftTermId, .col-leftTermIdEdit, .col-relationTypeId, .col-relationTypeIdEdit, .col-rightTermId, .col-rightTermIdEdit{width:30%;}
	.col-ownership, .col-ownershipEdit, .col-ownerId, .col-ownerIdEdit{width:49%;}
	#rightTermId-btn-search:hover, #leftTermId-btn-search:hover{ color:red; cursor:pointer; }
</style>
<div id="addKRItem" class="modal fade" role="dialog">
	<input id="itemID4newKR" value="" type="hidden" />
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header"></div>
			<div class="modal-body">
				<div class="col-leftTermId form-group" style="vertical-align:top;float:left;text-align:left">
					<label>Left Term</label>
					<div>
						<select id="leftTermId" class="form-control" style="display:none"></select>
						<input id="leftTermIdTEMP" class="form-control" disabled style="width:83% !important;display:inline-block;margin-right:1%;">
						<i id="leftTermId-btn-search" class="fa fa-search" style="" onclick="showSearchBox('leftTermId', 'Left Term')"></i>
					</div>
				</div>
				<div class="col-relationTypeId form-group" style="vertical-align:top;margin:0 4%;text-align:left">
					<label>Relation Type</label>
					<div>
						<select id="relationTypeId" class="form-control"></select>
					</div>
				</div>
				<div class="col-rightTermId form-group" style="vertical-align:top;float:right;text-align:left">
					<label>Right Term</label>
					<div>
						<select id="rightTermId" class="form-control" style="display:none"></select>
						<input id="rightTermIdTEMP" class="form-control" disabled style="width:83% !important;display:inline-block;margin-right:1%;">
						<i id="rightTermId-btn-search" class="fa fa-search" style="" onclick="showSearchBox('rightTermId', 'Right Term')"></i>
					</div>
				</div>
				<div class="col-tmpVal form-group" style="width:98%">
					<div>
						<input disabled="disabled" id="tempVar" placeholder="" value="  " class="form-control">
					</div>
				</div>
				<div class="col-ownership form-group">
					<label>Ownership</label>
					<div style="text-align:right">
						<div class="btn-group" data-toggle="buttons">
							<label class="btn btn-default" onClick="$('#ownership0').prop('checked', true)">
								<input type="radio" name="ownership" id="ownership0" value="0" autocomplete="off"> Public
							</label>
							<label class="btn btn-default" onClick="$('#ownership1').prop('checked', true)">
								<input type="radio" name="ownership" id="ownership1" value="1" autocomplete="off"> Protected
							</label>
							<label class="btn btn-default active" onClick="$('#ownership2').prop('checked', true)">
								<input type="radio" name="ownership" id="ownership2" value="2" autocomplete="off" checked> Private
							</label>
						</div>
					</div>
				</div>
				<div class="col-ownerId form-group">
					<label>Owner</label>
					<div>
						<select id="ownerId" name="ownerId" value="" class="form-control"></select>
					</div>
				</div>
				<div class="col-relationisreserved form-group">
					<input type="checkbox" id="relationIsReserved" style="width:auto">
					<label style="margin-left:5px" for="relationIsReserved">Reserved</label>
				</div>
			</div>
			<div class="modal-footer" style="text-align:left">
		        <button type="button" class="btn btn-danger" style="width:120px;" onClick="closeModal('addKRItem')">Cancel</button>
		        <button type="button" class="btn btn-info"   style="width:120px;float:right" onClick="addNewKR()" >Add New KR</button>
			</div>
			
		</div>
	</div>
</div>
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
					<button type="button" class="btn btn-danger btn-no" style="width:100%;" onClick="closeModal('searchBox')">Cancel</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="application/javascript">
	var leftORright = null;
	//---------------------------------------------------------
	$(function(){
		//-----------------------------------------------------
		$("#searchItemText").on('change', function(){ myLEX.searchTermByName($("#objID").val().trim(), $(this).val().trim()); });
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
		$("#searchBox").on('shown.bs.modal', function(){ $("#searchItemText").focus(); });
		//-----------------------------------------------------
	});
	//---------------------------------------------------------
	function showSearchBox(objID, label){
		leftORright = objID;
		//--------------------------------------------------------
		$("#searchItemText").val('');
		$("#objID").val(objID);
//		$("#searchBox .modal-header").text('select Term');
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
			 $('#searchBox .btn-yes').prop('disabled', false);
			$("#searchItemText").val( $(this).find('option:selected').text() );
		});
		//--------------------------------------------------------
	}
	//---------------------------------------------------------
	function setTermOwner(){ 
		$("#searchItemText").focus(); 
		$("#searchItemText").val('')
		myLEX.getTerms(0 , $("select#"+leftORright ), 'n'); 
	}
	//---------------------------------------------------------
	function selectTermItem(){
		$("#"+$("#objID").val().trim()).val( $('#termsList').val() ).change();
		$("#searchBox").modal('hide');
	}
	//---------------------------------------------------------
	function callAddNewKR(itemID, headName){
		$("#ownerId").val(defaultOrgID);
		$("#ownerId").prop('disabled', true);
//		if(orgID==0){ $("#addKRItem #ownerId").prop('disabled', false); }

//		$("#termOwnersList"    ).val(-1);
		$("#termOwnersList"    ).val('<?=$mapBotRecord->ownerId;?>');
		$('#relationIsReserved').prop('checked', false);
		$('#ownership2'        ).prop('checked', true);
		$("#relationTypeId"    ).val($("#relationTypeId option:first").val());
		$(".col-ownership label").removeClass("active");
		$(".col-ownership label:nth-child(3)").addClass("active");
		
		myLEX.getTerms(0, $("select#leftTermId" ), 'n');
		myLEX.getTerms(0, $("select#rightTermId"), 'n');
		
		$("#itemID4newKR").val(itemID);
		
		$("#addKRItem .modal-header").html('<h6>'+headName+'</h6>');
		$("#addKRItem").modal({backdrop:'static', keyboard:false});
	}
	//---------------------------------------------------------
	function addNewKR(){
		var data = {};
		data.userID             = userID;
		data.lastUserId         = userID;
		data.leftTermId         = $('#leftTermId'    ).val();
		data.relationTypeId     = $('#relationTypeId').val();
		data.rightTermId        = $('#rightTermId'   ).val();
		data.ownerId            = $('#ownerId'       ).val();
		data.ownership          = 0;
		data.ownership          = ($('#ownership1').prop('checked') ?1 :data.ownership);
		data.ownership          = ($('#ownership2').prop('checked') ?2 :data.ownership);
		data.relationIsReserved = ($('#relationIsReserved').prop('checked') ?1 :0);
		data.relationOperand    = '';
		var itemID = $("#itemID4newKR").val();
		myLEX.saveNewKR(itemID, data);
	}
	//---------------------------------------------------------
</script>
	 