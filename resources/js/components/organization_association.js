import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
//import Slider from 'jquery-ui-bundle'
//import 'jquery-ui-bundle/jquery-ui.min.css'

class OrgRelationType extends DataTable {
	//-----------------------------------------
	constructor(data){
		super(data);
		this.pageSort = 'leftOrgName';
	    $('body').on(
					'change', 
					'select#leftOrgId', 
					() => { 
						this.getAllOrganizations($("#leftOrgId").val(), 0); 
//						this.getMyRelationTypes($("#leftOrgId").val(), 0); 
					});
		this.getMyRelationTypes(this.orgID, 0); 
	}
	//-----------------------------------------
	getOrganizations() {
		$.get(this.organizationURL, (res) => {
			this.organizations = this.createSelectOptions(res.data, 'organizationId', 'organizationShortName');
			this.ownerId = this.orgID;
			$("#leftOrgId").append(this.organizations);
		});
	}
	//-----------------------------------------
	getAllOrganizations(tmp, flag){
		$("select#rightOrgId option").remove();
		$("select#rightOrgId").append("<option value=''>Select . . .</option>");
		$.get(apiURL+'/api/dashboard/organization/all/0', (obj) => {
			var relationTypeOptions = [];
			for(var i=0;i<obj.data.length;i++){ 
				if(obj.data[i].organizationId==0  ) continue;
				if(obj.data[i].organizationId==tmp) continue;
				relationTypeOptions.push("<option value='"+obj.data[i].organizationId+"'>"+obj.data[i].organizationShortName+"</option>"); 
			}
			$("select#rightOrgId").append(relationTypeOptions);
			$("#rightOrgId").val($("#rightOrgId option:first").val());
			if(flag==1){
				tmp = ( (this.editItem==null) ?0 :this.editItem['rightOrgId'] );
				if(tmp!=0){ $("select#rightOrgId").val(tmp); }
			}
		});
	}
	//-----------------------------------------
	getMyRelationTypes(tmp, flag){
		$("select#relationTypeGroupId option").remove();
		$.get(apiURL+'/api/dashboard/relation_type_group/all/'+tmp, (obj) => {
			var tmpOptions = [];
			$("select#relationTypeGroupId").append("<option value=''>Select . . .</option>");
			for(var i=0;i<obj.data.length;i++){ 
				tmpOptions.push("<option value='"+obj.data[i].relationTypeGroupId+"'>"+obj.data[i].relationTypeGroupName+"</option>"); 
			}
			$("select#relationTypeGroupId").append(tmpOptions);
			if(flag==1){
				tmp = ( (this.editItem==null) ?0 :this.editItem['relationTypeGroupId'] );
				if(tmp!=0){ $("select#relationTypeGroupId").val(tmp); }
			}
			if($("select#relationTypeGroupId option").length<=1){
				$("#insertItem").prop('disabled', true);
				$("#saveItem"  ).prop('disabled', true);
			}else{
				$("#insertItem").prop('disabled', false);
				$("#saveItem"  ).prop('disabled', false);
			}
		});
	}
	//-----------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'leftOrgId':
			case 'rightOrgId':
			case 'relationTypeGroupId':
				input = $("<div>")
					.attr({ class: "col-" + col + " form-group"})
					.append($("<label>"+label+"</label>"))
					.append($("<div>").append($("<select>").attr({id: col,name: col,class: 'form-control'})));
				break;
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//-----------------------------------------
	showEditDialogHandler(e){
		$("#saveItem"  ).prop('disabled', false);
		super.showEditDialogHandler(e);
		
		this.editItem['leftOrgId'] = ((this.editItem['leftOrgId']==null) ?this.orgID :this.editItem['leftOrgId']);
		$("select#leftOrgId").val(this.editItem['leftOrgId']);
		
		this.getMyRelationTypes($("#leftOrgId").val(), 1);
		this.getAllOrganizations($("#leftOrgId").val(), 1);
	}
	//-----------------------------------------
	showAddDialogHandler(){
		$("#insertItem").prop('disabled', false);
		this.editItem=null;
		
		super.showAddDialogHandler();

		this.getMyRelationTypes(this.orgID, 0);
		this.getAllOrganizations(this.orgID, 0);
	}
	//-----------------------------------------
	rowActions(value, row, index, field) {
		//----------------------------------------------------------
		var icons = this.actionIcons;
		$("[data-menu-toggle='#actions-menu-"+index+"']").remove();
		//----------------------------------------------------------
		if( orgID!=0 ){
			if( orgID!=row.leftOrgId ){
				var tmpICN = [];
				var icons = this.actionIcons;
				for (var i in icons){ if(icons[i].attr('class')!='delete-item' && icons[i].attr('class')!='edit-item'){ tmpICN.push(icons[i]); } }
				icons = tmpICN;
			}
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
	//--------------------------------------------------------------
}
//---------------------------------------------

//---------------------------------------------
var columns = [
	{ name: 'orgAssociationId', display: 'ID', primary: true, hidden: true },

	{ name: 'leftOrgId'  , display: 'Left Organization', sortable: true, hidden: true },
	{ name: 'leftOrgName', display: 'Left Organization', sortable: true, search: true, editable: false },

	{ name: 'relationTypeGroupId'  , display: 'Relation Type', sortable: true, hidden: true },
	{ name: 'relationTypeGroupName', display: 'Relation Type', sortable: true, search: true, editable: false },

	{ name: 'rightOrgId'  , display: 'Right Organization', sortable: true, hidden: true },
	{ name: 'rightOrgName', display: 'Right Organization', sortable: true, search: true, editable: false },

	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
	{ name: 'lastUserId', hidden: true, editable: false, default: '1'},
/*
	{ name: 'termIsReserved', display: 'Reserved', sortable: true, reserved: true },
	{ name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
	{ name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true},
	{ name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
*/
];
//---------------------------------------------
var termColumns = new Columns(columns);
//---------------------------------------------
var data = {
	columns: termColumns,
	apiURL: apiURL + '/api/dashboard/organization_association'
}
//---------------------------------------------
if($("#organizationAssociation").length != 0){
	table = new OrgRelationType(data);
	table.createTable('organizationAssociation');
}
//---------------------------------------------
