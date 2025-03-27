import { DataTable, showError, showSuccess } from '../DataTable'
import Columns from '../Columns'

class KAASSetting extends DataTable {
	//----------------------------------------------------
	constructor(data){
		super(data);
		var viewIcon = $('<a></a>').attr({
			href: '#',
			style: "color:#2196f3;",
			class: 'edit-item',
			'data-desc': 'View',
			'data-onlyowner': 1
		});
		var deleteIcon = $("<a></a>").attr({
			href: '#',
			style: "color:#f3ae4e;",
			class: 'delete-item',
			'data-desc': 'Delete',
			'data-onlyowner': 1
		});
		this.actionIcons = [deleteIcon, viewIcon];
		kaasSet = this;
		kaasSet.pageSort = 'organizationShortName';
		kaasSet.portals = [];
		$("#portal_id").ready(function(){ kaasSet.getPersona(); });
	}
	//----------------------------------------------------
	getOrganizations() {
		$.get(this.organizationURL, (res) => {
			var options=[];
			for(var i in res.data){ 
				if( res.data[i]['organizationId']!="0" ){ 
					options.push("<option value='"+res.data[i]['organizationId']+"'>"+res.data[i]['organizationShortName']+"</option>"); 
				}
			}
			$("#org_id").append( "<option value=''>Select Organization</option>" );
			$("#org_id").append(options);
		});
	}
	//----------------------------------------------------
	getPersona() {
	}
	setPortal(inID, pID='') {
		$("#portal_id option").remove();
		$("#portal_id").append( "<option value=''>Select Portal</option>" );
		if(inID==''){ return; }
		$.get(this.apiURLBase + '/api/dashboard/kaas/setting/portals/'+inID, (res) => {
			for(var i in res.data){ 
				
				kaasSet.portals.push(
					{
						id:res.data[i]['id'], 
					 	caption:res.data[i]['name']
					}
				);
				
				$("#portal_id").append( "<option value='"+kaasSet.portals[i]['id']+"'>"+kaasSet.portals[i]['caption']+"</option>" );
			}
			$("#portal_id").val(pID);
		});
	}
	//----------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'org_id':
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<label>').text('Organization') )
							.append(
								$('<div>').append(
									$('<select>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
			break;
			case 'portal_id':
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<select>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
			break;
/*
			case 'personalityId':
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<select>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
			break;
*/
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);
		kaasSet.setPortal(kaasSet.editItem['org_id'], kaasSet.editItem['portal_id']);
		$("#org_id, #portal_id").prop('disabled', true);
		$(".col-apiKey").show();
		$("#apiKey").prop('disabled', true);
		$("#saveItem").hide();
	}
	//----------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		kaasSet.setPortal("");
		$("#org_id, #portal_id").prop('disabled', false);
		$(".col-apiKey").hide();
		$("#apiKey").prop('disabled', true);
		$("#insertItem").show();
	}
	//----------------------------------------------------
	setTableWidth(){
		var width = 0;
		for(var i in this.columns.data){ if( !isNaN(this.columns.data[i].width) ){ width+=this.columns.data[i].width; } }
		$("#KAAS_Setting table").css("min-width", width+"px");
	}
	//----------------------------------------------------
}

var columns = [
	{ name: 'id', display:'-', primary:true, hidden:true, sortable:false, search:false, editable:false },

	{ name: 'organizationShortName', display:'Organization', sortable:true,  search:true , editable:false },
	{ name: 'org_id'               , display:'Org ID',       sortable:false, search:false, editable:true, hidden:true },

	{ name: 'portalName', display:'Portal'     , sortable:true,  search:true , editable:false },
	{ name: 'portalCode', display:'Portal Code', sortable:true,  search:true , editable:false, width:120 },
	{ name: 'portal_id' , display:'Portal'     , sortable:false, search:false, editable:true, hidden:true },
	
	{ name: 'personalityName', display:'Persona', sortable:true , search:true , editable:false, width:180 },
/*
	{ name: 'personalityId'  , display:'Persona', sortable:false, search:false, hidden:true  },
	{ name: 'personalityName', display:'Persona', sortable:true , search:true , editable:false, width:180 },

	{ name: 'kaasPersonalityName', display:'Personality', sortable:true , search:true , editable:false, width:180 },

	{ name: 'kaasUserName', display:'User'   , sortable:true , search:true , editable:false, width:85 },
	{ name: 'kaasUserID'  , display:'User ID', sortable:false, search:false, editable:false, width:70 },

	{ name: 'apiKey', display:'API KEY', sortable:false, search:false, width:260 },
*/
	{ name: 'user_id', display:'-'   , hidden:true, sortable:false, search:false, editable:false },
	{ name: 'last'   , display:'Date', sortable:false ,editable:false, search:false, width:80 },
];
var kaasSettingColumns = new Columns(columns);

var data = {
	columns: kaasSettingColumns,
	apiURL: apiURL + '/api/dashboard/kaas/setting'
}

if($("#KAAS_Setting").length != 0){
	kaasSet = new KAASSetting(data);
	kaasSet.createTable('KAAS_Setting');
	kaasSet.setTableWidth();
}
