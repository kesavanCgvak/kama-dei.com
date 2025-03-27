import { DataTable, showError, showSuccess } from './../DataTable'
import Columns from './../Columns'
//----------------------------------------------------------------
class RPA extends DataTable{
	//------------------------------------------------------------
	constructor(data){
		super(data);
		this.pageSort = 'show_order';
		let icon1 = $('<a></a>').attr({
			href: '#',
			style: "color:#2196f3;",
			class: 'edit-item',
			'data-desc': 'Edit',
			'data-onlyowner': 0
		});
		this.actionIcons = [icon1];
	}
	//------------------------------------------------------------
	showAddDialogHandler(e){
		super.showAddDialogHandler(e);
		$("#editItem #show_order").prop("disabled", false);
	}
	//------------------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);
		if(this.baseItem==null){ this.baseItem={}; for(let i in this.editItem){ this.baseItem[i]=this.editItem[i]; } }
		this.baseItem.organization_id = (this.baseItem.organization_id==null) ?0 :this.baseItem.organization_id;
		
		$("#editItem #show_order").prop("disabled", false);
		if(this.baseItem.id<4){ $("#editItem #show_order").prop("disabled", true); }
	}
	//------------------------------------------------------------
}

//----------------------------------------------------------------
var rpaColumns = new Columns([
		{ name: 'id'        , display:"ID"   , editable:false, sortable:true, search:true  ,primary: true },
		{ name: 'name'      , display:"Name" , editable:true , sortable:true, search:true  },
		{ name: 'show_order', display:"Order", editable:true , sortable:true, search:false },
	]);
var data = {
	columns: rpaColumns,
	apiURL: apiURL + '/api/dashboard/rpa/types'
}
//----------------------------------------------------------------
if($("#rpa_types").length != 0){
	table = new RPA(data);
	table.createTable('rpa_types');
}
//----------------------------------------------------------------
