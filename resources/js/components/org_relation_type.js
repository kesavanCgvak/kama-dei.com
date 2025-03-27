import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
//import Slider from 'jquery-ui-bundle'
//import 'jquery-ui-bundle/jquery-ui.min.css'

class OrgRelationType extends DataTable {
	//-----------------------------------------
	constructor(data){
		super(data);
		this.relationTypes = [];
		this.pageSort = 'orgName';
		this.getAllRelationTypes();
	}
	//-----------------------------------------
	getAllRelationTypes(){
		$.get(apiURL+'/api/dashboard/relation_type/all/'+orgID+'/relationTypeName/asc', (obj) => {
			var relationTypeOptions = [];
			for(var i=0;i<obj.data.length;i++){ relationTypeOptions.push("<option value='"+obj.data[i].relationTypeId+"'>"+obj.data[i].relationTypeName+"</option>"); }
			this.relationTypes = relationTypeOptions;
			$("select#relationTypeId").append(this.relationTypes);
		});
	}
	//-----------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'orgId':
			case 'relationTypeId':
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
		super.showEditDialogHandler(e);
		
		this.editItem['orgId'] = ((this.editItem['orgId']==null) ?0 :this.editItem['orgId']);
		$("select#orgId").val(this.editItem['orgId']);
	}
	//-----------------------------------------
}
//---------------------------------------------

//---------------------------------------------
var columns = [
	{ name: 'orgRelationTypeId', display: 'ID', primary: true, hidden: true },

	{ name: 'orgId'  , display: 'Organization', sortable: true, hidden: true },
	{ name: 'orgName', display: 'Organization', sortable: true, search: true, editable: false },

	{ name: 'relationTypeId'  , display: 'Relation type', sortable: true, hidden: true },
	{ name: 'relationTypeName', display: 'Relation type', sortable: true, search: true, editable: false },

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
	apiURL: apiURL + '/api/dashboard/org_relation_type'
}
//---------------------------------------------
if($("#orgRelationType").length != 0){
	table = new OrgRelationType(data);
	table.createTable('orgRelationType');
}
//---------------------------------------------
