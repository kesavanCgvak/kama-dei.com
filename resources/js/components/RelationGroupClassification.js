import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
//import Slider from 'jquery-ui-bundle'
//import 'jquery-ui-bundle/jquery-ui.min.css'

class RelationGroupClassification extends DataTable {
	//-----------------------------------------
	constructor(data){
		super(data);
		this.pageSort = 'relationGroupType';
//		this.getAllOrganizations(0);
//		this.getMyRelationTypes(0);
	}
	//-----------------------------------------
	getMyRelationGroupTypes(tmp, flag){
//		var tmp = ( (typeof $("#leftOrgId").val() === 'undefined') ?this.orgID :$("#leftOrgId").val() );
		$("select#relationGroupId option").remove();
		$.get(apiURL+'/api/dashboard/relation_group_type/allrelationgrouptypes/'+tmp, (obj) => {
			var tempOptions = [];
			$("select#relationGroupId").append("<option value=''>Select . . .</option>");
			for(var i=0;i<obj.data.length;i++){ tempOptions.push("<option value='"+obj.data[i].relationId+"'>"+obj.data[i].relationGroupType+"</option>"); }
			$("select#relationGroupId").append(tempOptions);
			$("#relationGroupId").val($("#relationGroupId option:first").val());
			if(flag==1){
				tmp = ( (this.editItem==null) ?0 :this.editItem['relationGroupId'] );
				if(tmp!=0){ $("select#relationGroupId").val(tmp); }
			}else{}

			if($("select#relationGroupId option").length==0){
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
			case 'ownerId':
			case 'relationGroupId':
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
		
//		this.editItem['relationGroupId'] = ((this.editItem['relationGroupId']==null) ?this.orgID :this.editItem['relationGroupId']);
		$("select#relationGroupId").val(this.editItem['relationGroupId']);
		
		this.getMyRelationGroupTypes(this.orgID, 1);
	}
	//-----------------------------------------
	showAddDialogHandler(){
		$("#insertItem").prop('disabled', false);
		this.editItem=null;
		
		super.showAddDialogHandler();
		$("#ownership1").click();
		this.editItem[this.columns.ownershipColumn] = '1';

		this.getMyRelationGroupTypes(this.orgID, 0);
	}
	//-----------------------------------------
}
//---------------------------------------------

//---------------------------------------------
var columns = [
	{ name: 'relationGroupClassficationId', display: 'ID', primary: true, hidden: true },

	{ name: 'relationGroupId'  , display: 'Relation Group Type', sortable: true, hidden: true },
	{ name: 'relationGroupType', display: 'Relation Group Type', sortable: true, search: true, editable: false },

	{ name: 'ownership', display: 'Ownership', sortable: true, default: '1', ownership: true },
	{ name: 'ownerId', display: '', onlyFor: 0, hidden: true},
	{ name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
	{ name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
//---------------------------------------------
var termColumns = new Columns(columns);
//---------------------------------------------
var data = {
	columns: termColumns,
	apiURL: apiURL + '/api/dashboard/relation_group_classification'
}
//---------------------------------------------
if($("#relationGroupClassification").length != 0){
	table = new RelationGroupClassification(data);
	table.createTable('relationGroupClassification');
}
//---------------------------------------------
