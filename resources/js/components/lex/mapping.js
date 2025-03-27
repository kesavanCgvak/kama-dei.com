//import "bootstrap-table-fixed-columns-pro"
import { DataTable, showError, showSuccess, showConfirm } from '../DataTable'
import Columns from '../Columns'
//import "bootstrap-table/dist/extensions/bootstrap-table-fixed-columns-pro/bootstrap-table-fixed-columns-pro.css"
//import "bootstrap-table/dist/extensions/bootstrap-table-fixed-columns-pro/bootstrap-table-fixed-columns-pro.js"
import {LEXBase} from './LEXBase'

class LEXMapping extends DataTable {
	//----------------------------------------------------
	constructor(data){
		super(data);
		this.pageSort = 'mappingName';
		this.defaultPersona = [];
		this.addBtnCaption = "New Mapping";
		this.showRefresh = true;
		this.personaID        = 0;
		this.lexPersonalityID = 0;
		this.lexUserID        = 0;
//		$("#personaId").ready(function(){ $("#personaId").append("<option value='' data-persona=''>Select Persona</option>"); });
		$("#ownerId").ready(function(){ 
			$("#ownerId").on('change', function(){ myLEX.getSetting($(this).val(), $("#ownerId option:selected").data('setting')); }); 
//			if(orgID!=0){ myLEX.getPersona(orgID, defaultPersona); }
		});
		this.lexBase = new LEXBase();
		this.isValid = false;
	}
	//----------------------------------------------------
	getOrganizations() {
		$.get(apiURL + '/api/dashboard/lex/setting/organization/'+orgID , (res) => {
			if(res.result==1){
				showError(res.msg);
				return;
			}
			for(var i in res.data ){ 
				var tmp;
				tmp = "<option value='"+res.data[i].org_id+"' data-setting='"+res.data[i].settingID+"'>"+res.data[i].organizationShortName+"</option>";
				$("#ownerId").append(tmp);
			}
			if(orgID==0){ $("#ownerId").prepend("<option value='' data-persona=''>Select Organization</option>"); }
			if(res.data.length==0){ 
				$("#personalityID").prop('disabled', true);
				$("#continueBTN").hide();
				showError('Setting not defined for this Organization'); 
			}
		});
	}
	getSetting(inOrgID, settingID) {
		var tmpThis = this;
		$("#personaName"    ).val('');
		$("#personalityName").val('');
		$("#lexUserName"    ).val('');

		tmpThis.personaID        = 0;
		tmpThis.lexPersonalityID = 0;
		tmpThis.lexUserID        = 0;
		
		if(inOrgID==''){ return; }
		$.get(apiURL + '/api/dashboard/lex/setting/get/'+settingID, (res) => {
			if(res.result==0){
				$("#personaName"    ).val(res.data.personaName);
				$("#personalityName").val(res.data.lexPersonalityName);
				$("#lexUserName"    ).val(res.data.lexUserName);

				tmpThis.personaID        = res.data.personalityId;
				tmpThis.lexPersonalityID = res.data.lexPersonalityID;
				tmpThis.lexUserID        = res.data.lexUserID;
			}else{ showError(res.msg); }
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
			case 'personaId':
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
		data.personaId        = this.personaID;
		data.lexPersonalityID = this.lexPersonalityID;
		data.lexUserID        = this.lexUserID;

		if(data.mappingName     ==""){ $("#mappingName").focus(); throw "Mapping Name required";  return {}; }
		if(data.botName         ==""){ $("#bot_name"   ).focus(); throw "Bot Name required";  return {}; }
		if(data.botAlias        ==""){ $("#bot_alias"  ).focus(); throw "Bot Alias required"; return {}; }
		if(data.ownerId         ==""){ $("#ownerId"    ).focus(); throw "Organization required";  return {}; }
		if(data.personaId       ==0 ){ $("#ownerId"    ).focus(); throw "Persona required";  return {}; }
		if(data.lexPersonalityID==0 ){ $("#ownerId"    ).focus(); throw "Personality required";  return {}; }
		if(data.lexUserID       ==0 ){ $("#ownerId"    ).focus(); throw "User required";  return {}; }
		return data;
	}
	//----------------------------------------------------
	showAddDialogHandler(){ 
		super.showAddDialogHandler();
		if(orgID==0){ $("#ownerId").val(""); }
		else{ $("#ownerId").val(orgID).change(); }
		$("#personaName, #personalityName, #lexUserName").prop('disabled', true);
	}
	addConfirmHandler(e){
		try{
			var tmp = this;
			var data =this.validateBot();
			data.userID = this.userID;
			this.lexBase.callBot(data.botName, data.botAlias, null,
				function(retVal, stack){ /*tmp.isValid=true; tmp.addConfirmHandler(e);*/
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
								window.location.href=apiURL + '/panel/lexjoint/mapping/'+res.id;
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
	//----------------------------------------------------
//	showAddDialogHandler(){ window.location.href=this.apiURLBase + '/panel/lexjoint/mapping/new'; }
	showEditDialogHandler(e){ window.location.href=this.apiURLBase + '/panel/lexjoint/mapping/'+$(e.currentTarget).data('itemid'); }
	showDeleteDialogHandler(e){
		var id = $(e.currentTarget).data('itemid');
		showConfirm(function(res){
			if(res){
				$.ajax({
					url: apiURL+'/api/dashboard/lex/mapping/delete',
					type: 'delete',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json'
					},
					data: JSON.stringify({id:id}),
					beforeSend: function(){},
					success: function(res){
						if(res.result == 0){
							$("#LEX_Mapping table").bootstrapTable('refresh');
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
		$("#LEX_Mapping table").css("min-width", width+"px");
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

if($("#LEX_Mapping").length != 0){
	var columns = [
		{ name: 'bot_id', display:'-', primary:true, hidden:true, sortable:false, search:false, editable:false },

		{ name: 'mappingName'     , display:'Mapping Name', sortable:true, search:true, width:200 },
		{ name: 'bot_name'        , display:'Bot Name'    , sortable:true, search:true, width:200 },
		{ name: 'bot_alias'       , display:'Bot Alias'   , sortable:true, search:true, width:150 },

		{ name: 'ownerId'              , display:'Organization', sortable:false, search:false, editable:true , hidden:true},
		{ name: 'organizationShortName', display:'Organization', sortable:true , search:true , editable:false, width:250 },

//		{ name: 'personaId'  , display:'Persona', sortable:false, search:false, editable:true , hidden:true},
		{ name: 'personaName'    , display:'Persona'    , sortable:true , search:true , editable:true,  width:250 },
		{ name: 'personalityName', display:'Personality', sortable:true , search:true , editable:true,  width:250 },
		{ name: 'lexUserName'    , display:'User'       , sortable:true , search:true , editable:true,  width:150 },
		
		{ name: 'publish_status', display:'Publish Status', sortable:true, search:true, width:120, editable:false },
		
		
		{ name: 'user_id', display:'-'   , hidden:true, sortable:false, search:false, editable:false },
		{ name: 'last'   , display:'Date', sortable:false ,editable:false, search:false, width:80 },

	];
	var lexSettingColumns = new Columns(columns);

	var data = {
		columns: lexSettingColumns,
		apiURL: apiURL + '/api/dashboard/lex/mapping'
	}
	myLEX = new LEXMapping(data);
	myLEX.createTable('LEX_Mapping');
	myLEX.setTableWidth();
}