import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class Relation extends DataTable {
	//------------------------------------------------------------
	constructor(data){
		super(data);
		let tmpThis = this;
		this.showGlobal = true;
		this.pageSort = 'knowledgeRecordName';
		
		this.terms = [];
		this.relationTypes = [];
		
		this.getAllRelationTypes();
//		this.getAllTerms(1);
//		this.termLoadingCompleted = false;

//		var icon0 = $('<a></a>').attr({ href: '#', 'data-desc': 'Languages', class: 'link-language', 'data-onlyowner': 1 });
		var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'Ext. Data Link', class: 'link-item', 'data-onlyowner': 1 });
		var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'KR-KR Link', class: 'link-kr', 'data-onlyowner': 1 });
		var icon3 = $('<a></a>').attr({ href: '#', 'data-desc': 'KR-Term Link', class: 'link-krtoterm', 'data-onlyowner': 1 });
		this.actionIcons = this.actionIcons.concat([icon1, icon2, icon3]);

//		$('body').on('click', '.link-language', (e) => { this.showLanguageModal(e) });
		$('body').on('click', '.link-item', (e) => { this.showLinkDialogHandler(e) });
		$('body').on('click', '.link-kr', (e) => { this.showRLDialogHandler(e) });
		$('body').on('click', '.link-krtoterm', (e) => { this.krtoterm(e) });
/*
		$('body').on('keypress', '#shortText', () => { 
			$("#btn-shortText").prop("disabled", false); 
			if($("#shortText").val().trim()==""){$("#btn-shortText").prop("disabled", true); } 
		});
*/	
		
		tmpThis.showGlobalStatus=1;
		$("#showGlobal").prop('checked', true);
		
		$('body').on('change', '#showGlobal', (e) => { 
			if($(this).prop('checked')==true){ 
				tmpThis.showGlobalStatus=1;
				$(this).prop('checked', false);
			}else{ 
				tmpThis.showGlobalStatus=0;
				$(this).prop('checked', true);
				if($("#relationOwnersList").val()==-1){ $("#relationOwnersList").val(orgID); }
			}
			$("#relationOwnersList").change();
		});

		$('body').on('change', '#relationOwnersList', (e) => { 
			$(tmpThis.table).bootstrapTable('selectPage', 1);
		});
		
		$('body').on('change', '#ownerId', (e)=>{
			if($("#ownerId").val()==0){
				$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"0").click();
				$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
			}else{
				$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
				if(this.baseItem==null){ $("#"+this.columns.ownershipColumn+"2").click(); }
				else{ $("#"+this.columns.ownershipColumn+this.baseItem.ownership).click(); }
			}
			
			$("#langLanguageCode option").remove();
			$.get(apiURL+'/api/dashboard/organization/get/language/'+$('#ownerId').val(), function(res){
				if(res.result==0){
					for(let i in res.data){
						if(res.data[i].isActive==1 || res.data[i].code=='en'){
							let code = res.data[i].code;
							let caption = res.data[i].name;
							$("#langLanguageCode").append('<option value="'+code+'">'+caption+'</option>');
						}
					}
					$("#langLanguageCode").val('en').change();
				}
			});
		});
		if(orgID!=0){
			$("#langLanguageCode option").remove();
			$.get(apiURL+'/api/dashboard/organization/get/language/'+orgID, function(res){
				if(res.result==0){
					for(let i in res.data){
						if(res.data[i].isActive==1 || res.data[i].code=='en'){
							let code = res.data[i].code;
							let caption = res.data[i].name;
							$("#langLanguageCode").append('<option value="'+code+'">'+caption+'</option>');
						}
					}
					$("#langLanguageCode").val('en').change();
				}
			});
		}
		$('body').on('click', '#deleteDialog .btn.btn-no', (e)=>{
			$(this.table).bootstrapTable('uncheckAll')
			$('#deleteDialog').fadeOut();
		});

		this.tmpDelIDs = [];
		this.haveResRow = false;
		//--------------------------------------------------------
		$('body').on('change', '#languagesItem #langLanguageCode', (e)=>{
			let tmpOrgId = $('#ownerId').val();
			if(orgID!=0){ tmpOrgId = orgID; }
			let url = this.apiURL+'/relation_language/' +
				$('#languagesItem #langRelationId'  ).val() + "/" +
				tmpOrgId + "/" +
				$('#languagesItem #langLanguageCode').val();
			$.get(url, function(res){
				let shortText    = "";
				let optionalText = "";
				if(res.result==0){
					if(res.data!=null){
						shortText    = res.data.shortText;
						optionalText = res.data.optionalText;
					}
				}
				$("#languagesItem #shortText"   ).val(shortText   ).change();
				$("#languagesItem #optionalText").val(optionalText).change();
			});
			
			$("#languageCode1").val($('#languagesItem #langLanguageCode').val().trim()).change();
		});
		//--------------------------------------------------------
		$('body').on('change', '#languagesItem #shortText', (e)=>{
			let val = $('#languagesItem #shortText').val().trim();
			$('#languagesItem #btn-shortText').prop('disabled', false);
			if(val.length==0){ $('#languagesItem #btn-shortText').prop('disabled', true); }
			
			$("#shortText1").val($('#languagesItem #shortText').val()).change();
		});
		$('body').on('change', '#languagesItem #optionalText', (e)=>{
			let val = $('#languagesItem #optionalText').val().trim();
			$('#languagesItem #btn-optionalText').prop('disabled', false);
			if(val.length==0){ $('#languagesItem #btn-optionalText').prop('disabled', true); }
			
			$("#optionalText1").val($('#languagesItem #optionalText').val().trim()).change();
		});
		//--------------------------------------------------------
		$('body').on('keypress', '#languagesItem #shortText', (e)=>{
			let val = $('#languagesItem #shortText').val().trim();
			$('#languagesItem #btn-shortText').prop('disabled', false);
			if(val.length==0){ $('#languagesItem #btn-shortText').prop('disabled', true); }
			
			$("#shortText1").val($('#languagesItem #shortText').val()).change();
		});
		$('body').on('keypress', '#languagesItem #optionalText', (e)=>{
			let val = $('#languagesItem #optionalText').val().trim();
			$('#languagesItem #btn-optionalText').prop('disabled', false);
			if(val.length==0){ $('#languagesItem #btn-optionalText').prop('disabled', true); }
			
			$("#optionalText1").val($('#languagesItem #optionalText').val().trim()).change();
		});
		//--------------------------------------------------------
		$('body').on('click', '#languagesItem #btn-shortText', (e)=>{
			$('#languagesItem #shortText').val('').change();
		});
		$('body').on('click', '#languagesItem #btn-optionalText', (e)=>{
			$('#languagesItem #optionalText').val('').change();
		});
		//--------------------------------------------------------
/*
		$('body').on('click', '#languagesItem .btn-saveLang', (e)=>{
			let data = {
				relationId   : $('#languagesItem #langRelationId'  ).val(),
				language_code: $('#languagesItem #langLanguageCode').val(),
				orgId        : $('#ownerId'                        ).val(),
				optionalText : $('#languagesItem #optionalText'    ).val().trim(),
				shortText    : $('#languagesItem #shortText'       ).val().trim()
			};
			let url = this.apiURL+'/relation_language';
			$.ajax({
				url: url,
				method: 'put',
				data: data,
				beforeSend: function(){
					$('#languagesItem input' ).prop('disabled', true);
					$('#languagesItem select').prop('disabled', true);
					$('#languagesItem button').prop('disabled', true);
				},
				complete: function(){
					$('#languagesItem input' ).prop('disabled', false);
					$('#languagesItem select').prop('disabled', false);
					$('#languagesItem button').prop('disabled', false);
				},
				success: function(res){
					if(res.result==0){
						$('#languagesItem').modal('hide');
						showSuccess('Item Saved');
					}else{
						showError(res.msg);
					}
				},
				error: function(xhr){
					showError(xhr.status+': '+xhr.statusText);
				}
			});
		});
*/
	}
	//------------------------------------------------------------
	get getURL() { 
		return this.apiURL + 
				'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' + this.pageNumber +
				'/ownerId/' + $("#relationOwnersList").val()+'/showglobal/'+this.showGlobalStatus;
	}
	get searchURL() {
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
			this.search+ '/ownerId/'+$("#relationOwnersList").val()+'/showglobal/'+this.showGlobalStatus;
	}
	//------------------------------------------------------------
	searchTermByName(objID, val){
		$("#findTermsBTN").hide();
		$("#wait4terms").show();
		val = val.trim();
//		if(val.length<3){ return; }
		if(val==''){ return; }
//		var ID = objID.replace('-search', '');
		var obj = $("select#"+objID);
//		$("#"+objID).hide();
//		$("#"+objID).val('');
		$("select#"+objID+" option").remove();
		$("#termsList option").remove();
		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+val+'/'+termPerPage+'/ownerId/'+$('#termOwnersList').val(), (dataIn) => {
			$("#termsList option").remove();
			$("select#"+objID+" option").remove();
			for(var i=0; i<dataIn.data.length; i++){
				var tmp = "<option data-prev='0' onDblClick='selectTermItem()' data-next='0' value='"+dataIn.data[i].termId+"' >"+dataIn.data[i].termName+"</option>";
				$(obj).append(tmp);
				$("#termsList").append(tmp);
			}
//			if(dataIn.data.length!=0){ $(obj).val(dataIn.data[0].termId).change(); }
		})
		.always(function() {
			$("#wait4terms").hide();
			$("#findTermsBTN").show();
		});
		$("#termsList").focus();
		$(obj).focus();
	}
	//------------------------------------------------------------
	getTerms(id, obj, direction){
		$("#findTermsBTN").hide();
		$("#wait4terms").show();
		$(obj).find("option").remove();
		$("#termsList option").remove();
		if(id==''){ id=0; }
		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+id+'/'+termPerPage+'/'+direction+'/ownerId/'+$('#termOwnersList').val(), (dataIn) => {
			for(var i=0; i<dataIn.data.length; i++){ 
				var tmp = "<option data-prev='0' onDblClick='selectTermItem()' data-next='0' value='"+dataIn.data[i].termId+"' >"+dataIn.data[i].termName+"</option>";
				if(direction=='n'){ 
					$(obj).append(tmp); 
					$("#termsList").append(tmp); 
				}else{ 
					$(obj).prepend(tmp); 
					$("#termsList").prepend(tmp); 
				}
			}
			if( id!=0 ){ $(obj).val(id).change(); }
			else{
				let lbl = $(obj).parent().parent().find("label").text();
				$(obj).prepend("<option data-prev='0' value='' selected='selected' >Select "+lbl+"</option>").change();
			}
		})
		.always(function() {
			$("#wait4terms").hide();
			$("#findTermsBTN").show();
		});
	}	
	//------------------------------------------------------------
	getAllRelationTypes(){
		$.get(apiURL+'/api/dashboard/relation_type/all/'+orgID+'/relationTypeName/asc', (obj) => {
			var relationTypeOptions = [];
			for(var i=0; i< obj.data.length; i++){ 
				relationTypeOptions.push("<option value='"+obj.data[i].relationTypeId+"'>"+obj.data[i].relationTypeName+"</option>");
			}
			this.relationTypes = relationTypeOptions;
			$("select#relationTypeId").append(this.relationTypes);
			$("select#relationTypeId").prepend("<option value='' selected='selected' >Select Relation Type</option>");
		});
	}

	//------------------------------------------------------------
	showEditDialogHandler(e){
		isAddKR = false;
		$("#termOwnersList").val(-1);
//		if(this.baseItem!=null){ for(let i in this.baseItem){ this.editItem[i]=this.baseItem[i]; } }
		super.showEditDialogHandler(e);
		if(this.baseItem==null){ this.baseItem={}; for(let i in this.editItem){ this.baseItem[i]=this.editItem[i]; } }
		this.getTerms(this.editItem.leftTermId , $("select#leftTermId" ), 'n');
		this.getTerms(this.editItem.rightTermId, $("select#rightTermId"), 'n');
		var tempVar =
				$("select#leftTermId option:selected").text()+' '+
				$("select#relationTypeId option:selected").text()+' '+
				$("select#rightTermId option:selected").text();
		$("input#tempVar").val(tempVar);
/*
		$("input#shortText").val(this.editItem.shortText);
		$("#btn-shortText").prop("disabled", true);
		if($("#shortText").val().trim()!=""){$("#btn-shortText").prop("disabled", false); }
*/
		$('#ownerId').change();
		if(this.editItem.ownerId==0 || this.editItem.ownerId==null){
			$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
			
			$("#"+this.columns.ownershipColumn+this.editItem.ownership).click();
		}
		$('#languagesItem #langRelationId'  ).val(this.editItem.relationId);
		$('#languagesItem #langLanguageCode').prop('disabled', false).change();
		
		$(".col-languageCode1").hide();
		$(".col-shortText1"   ).hide();
		$(".col-optionalText1").hide();
	}
	//----------------------------------------------------
	showAddDialogHandler(){
		isAddKR = true;
		$("#termOwnersList").val(-1);
		this.getTerms(0, $("select#leftTermId" ), 'n');
		this.getTerms(0, $("select#rightTermId"), 'n');
		super.showAddDialogHandler();
		$("#insertItem").prop('disabled',false);
		$("#saveItem"  ).prop('disabled',false);

		$("input#tempVar"        ).val("");
		$("input#leftTermIdTEMP" ).val($("select#leftTermId option:selected").text());
		$("input#rightTermIdTEMP").val($("select#rightTermId option:selected").text());
//		$("#btn-shortText"       ).prop("disabled", true);

		if(this.ownerId==0){
			$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").click();
			$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
		}
		$('#ownerId').change();
		$('#languagesItem #langRelationId'  ).val(0);
		$('#languagesItem #langLanguageCode').prop('disabled', true);
		
		$(".col-languageCode1").hide();
		$(".col-shortText1"   ).hide();
		$(".col-optionalText1").hide();
	}
	//----------------------------------------------------
	showLinkDialogHandler(e){
		e.preventDefault();
		let tmpItemId = $(e.currentTarget).data('itemid');
		let canContinue = false;
		$.each(this.rows, (i, item) => { if(item['relationId']==tmpItemId && item['extDataLink']>0 ){ canContinue=true; } });
		if(/*canContinue*/1){
			//window.open(this.apiURLBase + '/panel/extend/extendedlink/'+$(e.currentTarget).data('itemid'))
			//window.open(this.apiURLBase + '/panel/extend/extendedlink/2/'+$(e.currentTarget).data('itemid'))
			window.location.href=this.apiURLBase + '/panel/extend/extendedlink/2/'+$(e.currentTarget).data('itemid');
		}else{ $(e.currentTarget).css('color', 'lightgray'); }
	}
	//----------------------------------------------------
	showRLDialogHandler(e){
		e.preventDefault();
		let tmpItemId = $(e.currentTarget).data('itemid');
		let canContinue = false;
		$.each(this.rows, (i, item) => { if(item['relationId']==tmpItemId && item['linkingKR']>0 ){ canContinue=true; } });
		if(/*canContinue*/1){
			//window.open(this.apiURLBase + '/panel/extend/extendedlink/'+$(e.currentTarget).data('itemid'))
			//window.open(this.apiURLBase + '/panel/extend/extendedlink/2/'+$(e.currentTarget).data('itemid'))
			window.location.href=this.apiURLBase + '/panel/kb/relation_link/'+$(e.currentTarget).data('itemid');
		}else{ $(e.currentTarget).css('color', 'lightgray'); }
	}
	krtoterm(e){
		e.preventDefault();
		let tmpItemId = $(e.currentTarget).data('itemid');
		let canContinue = false;
		window.location.href=this.apiURLBase + '/panel/kb/link_kr_to_term/r/'+$(e.currentTarget).data('itemid');
	}
	//----------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'rightTermId':
			case 'leftTermId':
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label>"+label+"</label>") )
					.append(
						$("<div>")
						.append(
							$("<select>").attr({
								id: col,
								name: col,
								class: 'form-control',
								style: 'display:none'
							})
						)
						.append(
							$("<input>").attr({
								id: col+'TEMP',
								class: 'form-control',
								disabled: true,
								style: "width:160px !important;display:inline-block;margin-right:5px;"
							})
						)
						.append(
							$("<i>").attr({
								id: col+'-btn-search',
								class: 'fa fa-search',
								style: '',
								onclick: "showSearchBox('"+col+"', '"+label+"')"
							})
						)
					);
				break;
			case 'relationTypeId':
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label>"+label+"</label>") )
					.append(
						$("<div>").append(
							$("<select>").attr({
								id: col,
								name: col,
								class: 'form-control'
							})
						)
					);
				break;

			case 'tempVar':
				input = $("<div>")
					.attr({ class: 'col-tmpVal form-group' })
					.append($("<div>")
						.append($("<input>").attr({
							disabled: 'disabled',
							name: 'tempVar',
							id: 'tempVar',
							placeholder: '',
							value:
								$("select#leftTermId option:selected").text()+' '+
								$("select#relationTypeId option:selected").text()+' '+
								$("select#rightTermId option:selected").text(),
							class: 'form-control'
						})
					));
				break;
/*
			case 'shortText':
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label>").text(label) )
					.append( 
						$("<button>")
							.text("Remove")
							.attr({
								type:"button", class:"btn btn-warning", style:"float:right;padding:2px 5px;",id:"btn-"+col,
								onclick:'$("#'+col+'").val("").change()'
							})
					)
					.append(
						$("<div>").append(
							$("<input>").attr({
								id: col,
								name: col,
								class: 'form-control'
							})
						)
					);
				break;
*/
			case 'relationIsReserved':
				input = super.getActionFormInput(col, label) + $("#languagesItems").html();
				$("#languagesItems div").remove();
				break;
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------
	cellRenderer(value, row, index, field){
		let retVal = super.cellRenderer(value, row, index, field);
		if( field=='linkingKR' || field=='extDataLink' || field=='PKR' || field=='KRTermLink' ){
			if(value>0){ return "<span class='glyphicon glyphicon-ok' style='color:green'></span>"; }
			else{ return "<span class='glyphicon glyphicon-minus' style='color: #adadad'></span>"; }
		}
		return retVal;
	}
	//----------------------------------------------------
	get getColumns() {
		var columns = [];
		for(var x in this.columns.data){
			var column = this.columns.data[x];
			if(column.hidden === true && column.primary !== true) continue;
			if(column.name=='checkBox'){
				var obj = {
					//        editable: true,
					field: column.name,
					title: column.display,
					sortable: (column.sortable)? true:false,
					visible: (column.hidden === true)? false:true,
					formatter: (value, row, index, field) => this.checkBoxRender(value, row, index, field),
					checkbox: true
				}
			}else{
				var obj = {
					//        editable: true,
					field: column.name,
					title: column.display,
					sortable: (column.sortable)? true:false,
					visible: (column.hidden === true)? false:true,
					formatter: (value, row, index, field) => this.cellRenderer(value, row, index, field),
					checkbox: false
				}
			}
			if(column.width !== null) obj.width = column.width;
			columns.push(obj);
		}
		if(this.hasRowActions) {
			columns.push({
				field: 'actions',
				formatter: (value, row, index, field) => this.rowActions(value, row, index, field)
			})
		}
		return columns;
	}
	checkBoxRender(value, row, index, field){
		if(this.ownerId!=0){
			if(row.ownerId!=this.ownerId){ return { disabled: true }; }
		}
		return value;
	}
	//----------------------------------------------------
	deleteDialog() {
		var dialog = "<div id='deleteDialog' style='display:none'></div>";
		var inner = "<div class='msgInner'></div>";
		var msg = "<div class='msgDIV' style='line-height:22px'></div>";
		var actions = $("<div>").attr({
			class:'deleteActions',
			style:"width:100%; text-align:right; padding:5px 5px 5px 20px;"
		});
		var yes = $("<button>Yes</button>").attr({
			id: 'delete-confirm',
			class: 'btn btn-primary btn-yes',
			style:"width:80px;"
		});
		var no = $("<button>Cancel</button>").attr({
			class: 'btn btn-danger btn-no',
			style:"width:80px;float:left;"
		});
		actions = $(actions).append(yes).append(no);
		inner = $(inner).append(msg).append(actions);
		dialog = $(dialog).append(inner);
		return dialog;
	}
	//----------------------------------------------------
	showDeleteDialogHandler(e){
		$.each(this.rows, (i, item) => {
		  if(item[this.columns.primaryColumn] == $(e.currentTarget).data('itemid')){
			this.editItem = item;
			return false;
		  }
		});
		
		if(this.editItem.KRTermLink!=0){
			showError("This KR is being used in a KR Term Link and cannot be deleted."); 
			return;
		}
		if(this.editItem.linkingKR!=0){
			showError("This KR is being used in a KR-KR link and cannot be deleted."); 
			return;
		}
		if(this.editItem.PKR!=0 || this.editItem.extDataLink!=0){
			showError("This knowledge record is used in a personality rating, or Extended data, and can not be deleted."); 
			return;
		}
//			{ showError("This knowledge record is used in a personality rating and can not be deleted."); return; }
//			{ showError("This knowledge record is used in a Ext. Data Link and can not be deleted."); return; }
//			{ showError("This knowledge record is used in a Linking KR and can not be deleted."); return; }

		let tmpRow = {};
		let rsvDelIDs = [];
		$.each(this.rows, (i, item) => {
			if(item[this.columns.primaryColumn] == $(e.currentTarget).data('itemid')){
				tmpRow = item;
				
				let chkValues = [];
				chkValues.push(item.relationId);
				$(this.table).bootstrapTable('checkBy', {field: 'relationId', values: chkValues})
				return false;
			}
		});
		this.tmpDelIDs = [];
		this.haveResRow = false;
		$.each(this.rows, (i, item) => {
			if(item['checkBox']){
				if(this.ownerId==0 && userLevel==1){ 
					this.tmpDelIDs.push(item[this.columns.primaryColumn]); 
					if(item.relationIsReserved==1) rsvDelIDs.push(item[this.columns.primaryColumn]); 
				}else{
					if(item.ownerId==this.ownerId){ 
						this.tmpDelIDs.push(item[this.columns.primaryColumn]); 
						if(item.relationIsReserved==1) rsvDelIDs.push(item[this.columns.primaryColumn]); 
					}
					if(item.ownerId==null && this.ownerId==0){ 
						this.tmpDelIDs.push(item[this.columns.primaryColumn]); 
						if(item.relationIsReserved==1) rsvDelIDs.push(item[this.columns.primaryColumn]); 
					}
				}
			}
		});
		if(rsvDelIDs.length!=0){
			if(userLevel!=1){
				$("#deleteDialog .msgDIV")
					.html("This Delete request included <b>Reserved</b> records. You do not have authorization to delete <b>Reserved</b> records");
				$("#deleteDialog  .msgInner").css('height', '140px');
				$("#deleteDialog  .btn-yes" ).hide();
				$("#deleteDialog  .btn-no"  ).css('float', 'right');
				$("#deleteDialog  .btn-no"  ).text('Close');
			}else{
				$("#deleteDialog  .msgInner").css('height', '220px');
				$("#deleteDialog  .btn-yes" ).show();
				$("#deleteDialog  .btn-no"  ).css('float', 'left');
				$("#deleteDialog  .btn-no"  ).text('No');
				$("#deleteDialog .msgDIV")
					.html(
						this.tmpDelIDs.length+" records will be deleted<br/>"+
						"including "+rsvDelIDs.length+" <b>Reserved</b> records.<br/><br/>"+
						"Enter your <i>password</i> and select Yes to Delete<br/>click No to Cancel"+
						"<input id='delPass' type='password' class='form-control' value='' placeholder='password' autocomplete='off' />"
					);
			}
		}else{
			$("#deleteDialog .msgDIV").html( 
				this.tmpDelIDs.length+" records will be deleted<br/>"+
				"Are you sure you want to delete this items?"
			);
			$("#deleteDialog  .msgInner").css('height', '140px');
			$("#deleteDialog  .btn-yes" ).show();
			$("#deleteDialog  .btn-no"  ).css('float', 'left');
			$("#deleteDialog  .btn-no"  ).text('No');
		}
		super.showDeleteDialogHandler(e);
	}
	//----------------------------------------------------
	get deleteURLs() { return this.apiURL + "/delete/"; }
	deleteConfirmHandler(){
		let delPass = "";
		if($("#deleteDialog #delPass").length!=0){
			delPass = $("#deleteDialog #delPass").val().trim();
			if(delPass==''){ 
				$("#deleteDialog #delPass").val(delPass);
				showError('Enter your password');
				return;
			}
		}
		if(!this.haveResRow && this.tmpDelIDs.length==1){ super.deleteConfirmHandler(); }
		else{
			var thisTable = this.table;
			var data = {};
			data.userID = userID;
			data.pass   = delPass;
			data.IDs    = this.tmpDelIDs;
			$.ajax({
				url: this.deleteURLs,
				data: JSON.stringify(data),
				type: 'delete',
				dataType: 'json',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json'
				},
				success: function(res){
					if(res.result == 1){ showError(res.msg); }
					else{
						$(thisTable).bootstrapTable('uncheckAll')
						$("#deleteDialog").fadeOut();
						showSuccess('Deleted successfully');
						$(thisTable).bootstrapTable('refresh');
					}
				},
				error: function(e){ showError('Server Error'); }
			})
		}
	}
	//----------------------------------------------------
}
//----------------------------------------------------------------
var columns = [
	{ name: 'checkBox', display: '', sortable: false, editable: false },

	{ name: 'relationId', primary: true, hidden: true },
	{ name: 'leftTermId', display: 'Left Term', hidden: true },
//  { name: 'leftTermName', display: 'Left Term', sortable: true, editable: false },
	{ name: 'relationTypeId', display: 'Relation Type', hidden: true },
	//  { name: 'relationTypeName', display: 'Relation Type', sortable: true, editable: false },
	{ name: 'rightTermId', display: 'Right Term', hidden: true },
//  { name: 'rightTermName', display: 'Right Term', sortable: true, editable: false },
	{ name: 'relationOperand', display: 'Operand', hidden: true, editable: false },
	
	{ name: 'knowledgeRecordName', display: 'Knowledge Record', sortable: true, editable: false },
	{ name: 'tempVar', display: 'tempVar', hidden: true, editable: true },

	{ name: 'optionalText', display: 'Optional text', hidden: false, editable: false, sortable: false },

	{ name: 'relationIsReserved', display: 'Reserved', sortable: true, reserved: true },
	{ name: 'ownerId', display: 'Owner', hidden: true, onlyFor:0 },
	{ name: 'ownership', display: 'Ownership', default: '2', ownership: true, sortable: true },
	{ name: 'organizationShortName', display: 'Owner', sortable: true, editable: false },
	{ name: 'lastUserId', display: 'User', hidden: true, editable: false, default: '1'},
	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
	
	{ name: 'extDataLink', display: 'Ext. Data Link', sortable: true, editable: false },
	{ name: 'linkingKR'  , display: 'KR-KR Link'    , sortable: true, editable: false },
	{ name: 'KRTermLink' , display: 'KR-Term Link'  , sortable: true, editable: false },
	{ name: 'PKR'        , display: 'P.K.R'         , sortable: true, editable: false, hidden: false },
	
	{ name: 'languageCode1', display: '', sortable: false, editable: true , hidden:true },
	{ name: 'optionalText1', display: '', sortable: false, editable: true , hidden:true },
	{ name: 'shortText1'   , display: '', sortable: false, editable: true , hidden:true },

	//{ name: 'shortText', display: 'Optional Display Text (max:20 char. for FB Messenger)', hidden: true, editable: true },
];
//----------------------------------------------------------------
var relationColumns = new Columns(columns);
//----------------------------------------------------------------
var data = {
	columns: relationColumns,
	apiURL: apiURL + '/api/dashboard/relation'
}
//----------------------------------------------------------------
if($("#relation").length != 0){
	table = new Relation(data);
	table.createTable('relation');
}
//----------------------------------------------------------------
