//import "bootstrap-table-fixed-columns-pro"
import { DataTable, showError, showSuccess, showConfirm } from '../DataTable'
import Columns from '../Columns'
//import "bootstrap-table/dist/extensions/bootstrap-table-fixed-columns-pro/bootstrap-table-fixed-columns-pro.css"
//import "bootstrap-table/dist/extensions/bootstrap-table-fixed-columns-pro/bootstrap-table-fixed-columns-pro.js"
//import {KAASBase} from './KAASBase'

class KAASMapping extends DataTable {
	//----------------------------------------------------
	constructor(data){
		super(data);
		this.pageSort = 'mappingName';
		this.defaultPersona = [];
		this.addBtnCaption = "Add Mapping";
		this.showRefresh = true;

		$("#ownerId").ready(function(){ 
			$("#portal_id option").remove();
			$("#portal_id").append("<option value=''>Select Portal</option>");
			$("#ownerId").on('change', function(){
				myKAAS.getPortals($(this).val(), $("#ownerId option:selected").data('portal'));
			}); 
		});
		$("#portal_id").ready(function(){ 
			$("#portal_id").on('change', function(){
				myKAAS.getPerson($(this).val());
			}); 
		});
		$("#structure_id").ready(function(){ 
			myKAAS.getStructure();
		});
//		this.kaasBase = new KAASBase();
		this.isValid = false;
	}
	//----------------------------------------------------
	getOrganizations() {
		$.get(apiURL + '/api/dashboard/portal/getPortalOwner/'+orgID, (res) => {
			if(res.result==1){
				showError(res.msg);
				return;
			}
			for(var i in res.data ){ 
				$("#ownerId")
					.append("<option "+
								"value='"+res.data[i].orgId+"' "+
								"data-portal=''"+
							">"+
								res.data[i].orgName+
							"</option>");
			}
			if(orgID==0){ $("#ownerId").prepend("<option value='' data-portal=''>Select Organization</option>"); }
			else{ $("#ownerId").prop("disabled", true); }
			if(res.data.length==0){ 
				$("#personalityID").prop('disabled', true);
				$("#continueBTN").hide();
				//showError('Setting not defined for this Organization'); 
				$("#insertBtn, #KAAS_Mapping table, #KAAS_Mapping input, #KAAS_Mapping button").hide();
				$("#insertBtn").parent()
					.append("<div style='color:red'>KaaS has not been set for this organization, contact kama.ai super-admin to set up KaaS</div>");
			}
		});
	}
	getStructure(){
		$("#structure_id option").remove();
		$("#structure_id"  ).append("<option value=''>Select Structure</option>");
		$.get(apiURL + '/api/dashboard/kaas/structure' , (res) => {
			if(res.result==1){
				showError(res.msg);
				return;
			}
			for(var i in res.data ){ $("#structure_id").append("<option value='"+res.data[i].id+"'>"+res.data[i].name+"</option>"); }
		});
	}
	getPortals(org_id, portal_id) {
		$("#portal_id option").remove();
		$("#portal_id"  ).append("<option value=''>Select Portal</option>");
		$("#portal_id").val('').change();
//		$("#personaName").val('').change();
		if(org_id==''){ return; }
		$.get(apiURL + '/api/dashboard/portal/portals/'+org_id , (res) => {
			if(res.result==1){ showError(res.msg); return; }
			for(var i in res.data ){ 
				$("#portal_id").append("<option value='"+res.data[i].id+"'>"+res.data[i].name+"</option>");
			}
			$("#portal_id").val(portal_id).change();
		});
	}
	getPerson(inOrgID) {
		var tmpThis = this;
		$("#personaName").val('');
		$("#insertItem").show();
		
		if(inOrgID==''){ return; }
		$.get(apiURL + '/api/dashboard/portal/getPerson/'+inOrgID, (res) => {
			if(res.result==0){
				$("#personaName").val(res.data.personaName);
			}else{
				$("#insertItem").hide();
				showError(res.msg);
			}
		});
	}
	//----------------------------------------------------
	createTable(id){
		this.container = "#" + id;
		$(this.container).html('<table></table>');
		$(this.container).append(this.deleteDialog());
		$(this.container).append(this.actionForm());
		$(this.container).append(this.tableToolbar());
		
		this.table = "#" + id + " table";
		var DataTableConstant = this;
		$(this.table).bootstrapTable({
			url: this.getURL,
			columns: this.getColumns,
			sidePagination: 'server',
			pagination: this.hasPagination,
			silentSort: false,
			cache: false,
			search: this.hasSearch,
			toolbar: "#tableToolbar",
			pageSize: this.pageSize,
			pageNumber: this.pageNumber,
			sortName: this.pageSort,
			showRefresh: true,
//			height:600,
//			width:2700,
//			showExport: true,
//			exportDataType: 'all',
			queryParams: function(params){ DataTableConstant.queryParams(params, this); },
			responseHandler: (res) => this.responseHandler(res),

//fixedColumns: true,
//fixedFrom: 'left',
//fixedNumber: 2
		});
	}
	//----------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'portal_id':
			case 'structure_id':
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
//							.append( $('<label>').text('Organization Name') )
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<select>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
			break;
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------
	validateBot(){
		var data = {};
		data.botName          = $("#bot_name"   ).val().trim();
		data.botAlias         = $("#bot_alias"  ).val().trim();
		data.mappingName      = $("#mappingName").val().trim();
		data.ownerId          = $("#ownerId"    ).val().trim();

		if(data.mappingName     ==""){ $("#mappingName").focus(); throw "Mapping Name required";  return {}; }
		if(data.botName         ==""){ $("#bot_name"   ).focus(); throw "Bot Name required";  return {}; }
		if(data.botAlias        ==""){ $("#bot_alias"  ).focus(); throw "Bot Alias required"; return {}; }
		if(data.ownerId         ==""){ $("#ownerId"    ).focus(); throw "Organization required";  return {}; }
		return data;
	}
	//----------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		if(orgID==0){ $("#ownerId").val("").change(); }
		else{ $("#ownerId").val(orgID).change(); }
		$("#personaName").prop('disabled', true);
	}
/*
	addConfirmHandler(e){
		try{
			var tmp = this;
			var data =this.validateBot();
			data.userID = this.userID;
			this.kaasBase.callBot(data.botName, data.botAlias, null,
				function(retVal, stack){ / *tmp.isValid=true; tmp.addConfirmHandler(e);* /
					$.ajax({
						url: tmp.addURL,
						type: 'put',
						headers: {
							'Accept': 'application/json',
							'Content-Type': 'application/json'
						},
						data: JSON.stringify(data),
						beforeSend: function(){ $("#editItem #insertItem").prop('disabled', true); },
						success: function(res){
							if(res.result == 0){
								$("#editItem").fadeOut(function(){ $("#editItem #insertItem").prop('disabled', false); });
								showSuccess('Added successfully.');
								window.location.href=apiURL + '/panel/kaasmapping/mapping/'+res.id;
							}else{
								showError(res.msg);
								$("#editItem #insertItem").prop('disabled', false);
							}
						},
						error: function(e){
							showError('Server error');
							$("#editItem #insertItem").prop('disabled', false);
						}
					});
				});
		}catch(e){ showError(e); }
	}
*/
	//----------------------------------------------------
//	showAddDialogHandler(){ window.location.href=this.apiURLBase + '/panel/kaasmapping/mapping/new'; }
	showEditDialogHandler(e){ 
		window.location.href=this.apiURLBase + '/panel/kaasmapping/mapping/'+$(e.currentTarget).data('itemid'); 
	}
	showDeleteDialogHandler(e){
		var id = $(e.currentTarget).data('itemid');
		showConfirm(function(res){
			if(res){
				$.ajax({
					url: apiURL+'/api/dashboard/kaas/mapping/delete',
					type: 'delete',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json'
					},
					data: JSON.stringify({id:id}),
					beforeSend: function(){},
					success: function(res){
						if(res.result == 0){
							$("#KAAS_Mapping table").bootstrapTable('refresh');
						}else{ showError(res.msg); }
					},
					error: function(e){ showError('Server error'); }
				});
			}
		},"Are you sure?");
	}
	//----------------------------------------------------
	setTableWidth(){
		var width = 0;
		for(var i in this.columns.data){ if( !isNaN(this.columns.data[i].width) ){ width+=this.columns.data[i].width; } }
		$("#KAAS_Mapping table").css("min-width", width+"px");
	}
	//----------------------------------------------------
	rowActions(value, row, index, field){
		//----------------------------------------------------------
		var icons = this.actionIcons;
		$("[data-menu-toggle='#actions-menu-"+index+"']").remove();
		//----------------------------------------------------------
		if( orgID!=0 ){
			if( row.ownerId==null || orgID!=row.ownerId ){
				var tmpICN = [];
				var icons = this.actionIcons;
				for (var i in icons){ if(icons[i].data('onlyowner')!=1){ tmpICN.push(icons[i]); } }
				icons = tmpICN;
			}
		}
		{
			var tmpICN = [];
			var icons = this.actionIcons;
			for (var i in icons){ 
				if(row.publish_status=="Published" && icons[i].hasClass('delete-item')) continue;
				tmpICN.push(icons[i]);
			}
			icons = tmpICN;
		}
		if(icons.length==0){ return ''; }
		//----------------------------------------------------------
		var rowAction = '<div class="row-actions"></div>';
		//----------------------------------------------------------
		var others = '<ul class="menu-actions" data-menu data-menu-toggle="#actions-menu-'+index+'" style="font-size:12px;"></ul>';
		for (var i in icons){
			icons[i].attr('data-itemid', row[this.columns.primaryColumn]);
			var $icon = icons[i].clone();
			$icon = $icon.append('&nbsp;&nbsp;'+$icon.data('desc'));
			others = $(others).append('<li>'+$icon[0].outerHTML+'</li>');
		}
		var toggle = '<a href="#" class="toggle" id="actions-menu-'+index+'" style="color:dimgray"><small class="glyphicon glyphicon-chevron-down"></small></a>';
		var othersIcon = '<span>'+toggle+'</span>';
		rowAction = $(rowAction).append(othersIcon);
		$("body").append(others);
		$(document).ready(function(e){ $("[data-menu]").menu(); });
		//----------------------------------------------------------
		return $(rowAction)[0].outerHTML;
	}
	//----------------------------------------------------
}

if($("#KAAS_Mapping").length != 0){
	var columns = [
		{ name: 'bot_id', display:'-', primary:true, hidden:true, sortable:false, search:false, editable:false },

		{ name: 'mappingName', display:'Mapping Name', sortable:true, search:true },
		{ name: 'bot_name'   , display:'Bot Name'    , sortable:true, search:true },
		{ name: 'bot_alias'  , display:'Bot Alias'   , sortable:true, search:true },

		{ name: 'ownerId'              , display:'Organization', sortable:false, search:false, editable:true , hidden:true},
		{ name: 'organizationShortName', display:'Organization', sortable:true , search:true , editable:false },

		{ name: 'portalName' , display:'Portal' , sortable:true , search:true , editable:false },
		{ name: 'portal_id'  , display:'Portal' , sortable:false, search:false, editable:true ,hidden:true },
		{ name: 'personaName', display:'Persona', sortable:true , search:true , editable:true  },

		{ name: 'structureName', display:'Structure', sortable:true , search:true , editable:false  },
		{ name: 'structure_id' , display:'Structure', sortable:false, search:false, editable:true , hidden:true},
		
		{ name: 'publish_status', display:'Publish Status', sortable:true, search:true, width:120, editable:false },
		
		
		{ name: 'user_id', display:'-'   , hidden:true, sortable:false, search:false, editable:false },
		{ name: 'last'   , display:'Date', sortable:false ,editable:false, search:false },

	];
	var kaaSMappingColumns = new Columns(columns);

	var data = {
		columns: kaaSMappingColumns,
		apiURL: apiURL + '/api/dashboard/kaas/mapping'
	}
	myKAAS = new KAASMapping(data);
	myKAAS.createTable('KAAS_Mapping');
	myKAAS.setTableWidth();
}
