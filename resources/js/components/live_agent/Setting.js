import { DataTable, showError, showSuccess } from '../DataTable'
import Columns from '../Columns'

class LiveAgentSetting extends DataTable {
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
		myDataSet = this;
		myDataSet.pageSort = 'organizationShortName';
		myDataSet.portals = [];
		$("#portal_id").ready(function(){ myDataSet.getPersona(); });
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
		$.get(this.apiURLBase + '/api/dashboard/live_agent/setting/portals/'+inID, (res) => {
			for(var i in res.data){ 
				
				myDataSet.portals.push(
					{
						id:res.data[i]['id'], 
					 	caption:res.data[i]['name']
					}
				);
				
				$("#portal_id").append( "<option value='"+myDataSet.portals[i]['id']+"'>"+myDataSet.portals[i]['caption']+"</option>" );
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
		myDataSet.setPortal(myDataSet.editItem['org_id'], myDataSet.editItem['portal_id']);
		$("#org_id, #portal_id").prop('disabled', true);
		$(".col-apiKey").show();
		$("#apiKey").prop('disabled', true);
		$("#saveItem").hide();
	}
	//----------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		myDataSet.setPortal("");
		$("#org_id, #portal_id").prop('disabled', false);
		$(".col-apiKey").hide();
		$("#apiKey").prop('disabled', true);
		$("#insertItem").show();
	}
	//----------------------------------------------------
	setTableWidth(){
		var width = 0;
		for(var i in this.columns.data){ if( !isNaN(this.columns.data[i].width) ){ width+=this.columns.data[i].width; } }
		$("#LIVEAGENT_Setting table").css("min-width", width+"px");
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

	{ name: 'user_id', display:'-'   , hidden:true, sortable:false, search:false, editable:false },
	{ name: 'last'   , display:'Date', sortable:false ,editable:false, search:false, width:80 },
];
var mySettingColumns = new Columns(columns);

var data = {
	columns: mySettingColumns,
	apiURL: apiURL + '/api/dashboard/live_agent/setting'
}

if($("#LIVEAGENT_Setting").length != 0){
	myDataSet = new LiveAgentSetting(data);
	myDataSet.createTable('LIVEAGENT_Setting');
	myDataSet.setTableWidth();
}
