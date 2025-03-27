import { DataTable, showError, showSuccess } from '../DataTable'
import Columns from '../Columns'

class LEXSetting extends DataTable {
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
		lexSet = this;
		lexSet.pageSort = 'organizationShortName';
		lexSet.persona = [];
		$("#personalityId").ready(function(){ lexSet.getPersona(); });
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
	setPersona(inID, pID='') {
		$("#personalityId option").remove();
		$("#personalityId").append( "<option value=''>Select Persona</option>" );
		if(inID==''){ return; }
		$.get(this.apiURLBase + '/api/dashboard/personality/zeroPersonality/'+inID+'/-1/personalityName/asc', (res) => {
			for(var i in res.data){ 
				lexSet.persona.push(
					{
						id:res.data[i]['personalityId'], 
					 	caption:res.data[i]['personalityName'],
						ownerId:res.data[i]['ownerId'], 
					 	ownership:res.data[i]['ownership']
					}
				);
				$("#personalityId").append( "<option value='"+lexSet.persona[i]['id']+"'>"+lexSet.persona[i]['caption']+"</option>" );
			}
			$("#personalityId").val(pID);
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
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);
		lexSet.setPersona(lexSet.editItem['org_id'], lexSet.editItem['personalityId']);
		$("#org_id, #personalityId").prop('disabled', true);
		$(".col-apiKey").show();
		$("#apiKey").prop('disabled', true);
		$("#saveItem").hide();
	}
	//----------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		lexSet.setPersona("");
		$("#org_id, #personalityId").prop('disabled', false);
		$(".col-apiKey").hide();
		$("#apiKey").prop('disabled', true);
		$("#insertItem").show();
	}
	//----------------------------------------------------
	setTableWidth(){
		var width = 0;
		for(var i in this.columns.data){ if( !isNaN(this.columns.data[i].width) ){ width+=this.columns.data[i].width; } }
		$("#LEX_Setting table").css("min-width", width+"px");
	}
	//----------------------------------------------------
}

var columns = [
	{ name: 'id', display:'-', primary:true, hidden:true, sortable:false, search:false, editable:false },

	{ name: 'organizationShortName', display:'Organization', sortable:true,  search:true , editable:false },
	{ name: 'org_id'               , display:'Org ID',       sortable:false, search:false, editable:true, width:60 },

	{ name: 'personalityId'  , display:'Persona', sortable:false, search:false, hidden:true  },
	{ name: 'personalityName', display:'Persona', sortable:true , search:true , editable:false, width:180 },

	{ name: 'lexPersonalityName', display:'Personality', sortable:true , search:true , editable:false, width:180 },

	{ name: 'lexUserName', display:'User'   , sortable:true , search:true , editable:false, width:85 },
	{ name: 'lexUserID'  , display:'User ID', sortable:false, search:false, editable:false, width:70 },

	{ name: 'apiKey', display:'API KEY', sortable:false, search:false, width:260 },

	{ name: 'user_id', display:'-'   , hidden:true, sortable:false, search:false, editable:false },
	{ name: 'last'   , display:'Date', sortable:false ,editable:false, search:false, width:80 },
];
var lexSettingColumns = new Columns(columns);

var data = {
	columns: lexSettingColumns,
	apiURL: apiURL + '/api/dashboard/lex/setting'
}

if($("#LEX_Setting").length != 0){
	lexSet = new LEXSetting(data);
	lexSet.createTable('LEX_Setting');
	lexSet.setTableWidth();
}
