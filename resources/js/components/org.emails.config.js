import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
//--------------------------------------------------------
class OrganizationEmails extends DataTable {
	//----------------------------------------------------
	constructor(data){
		super(data);
		this.showGlobal = false;
		this.pageSort   = 'orgName';
		let icon1 = $('<a></a>')
						.attr({
							href:'#',
							'data-desc':'Config',
							class:'email-config',
							'data-onlyowner':0,
							style:'color:#2fe02f'
						});
		this.actionIcons = this.actionIcons = [icon1];
		$('body').on('click', '.email-config', (e) => { this.emailConfigDialog(e) });
		
		$('body').on('change', '#myOwnersList', (e) => {
			$(this.table).bootstrapTable('refresh');
		});
	}
	//----------------------------------------------------
	get getURL() {
		let myOwnersList = $("#myOwnersList").val();
		if(typeof myOwnersList=='undefined'){
			if(orgID==0){ myOwnersList=-1; }
			else{ myOwnersList=orgID; }
		}
		return this.apiURL + 
				'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' +
				this.pageNumber + '/' +
				myOwnersList;
	}
	//----------------------------------------------------
	get searchURL() {
		let myOwnersList = $("#myOwnersList").val();
		if(typeof myOwnersList=='undefined'){
			if(orgID==0){ myOwnersList=-1; }
			else{ myOwnersList=orgID; }
		}
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + myOwnersList + '/' +
			this.columns.searchColumn + '/' + this.search ;
	}
	//----------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'body':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<textarea>').attr({
										id: col,
										name: col,
										class: 'form-control fixed body'
									})
								)
						);
				break;
			}
			case 'send_format':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<select>')
										.attr({
											id: col,
											name: col,
											class: 'form-control '
										})
										.append("<option value='0' selected>Select ...</option>")
										.append("<option value='1'>CSV</option>")
										.append("<option value='2'>PDF</option>")
										.append("<option value='3'>HTML - email</option>")
								)
						);
				break;
			}
			case 'emails':
			case 'subject':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id: col, name: col, class:'form-control', maxLength:1000 })
								)
						);
				break;
			}
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------
	emailConfigDialog(e){
		super.showEditDialogHandler(e);
		if(this.baseItem==null){ this.baseItem={}; for(let i in this.editItem){ this.baseItem[i]=this.editItem[i]; } }
		
		if(this.baseItem.send_format==null){
			$("#send_format").val(0).change();
		}
	}
	//------------------------------------------------------------
}
//--------------------------------------------------------
var columns = [
	{ name:'id'             , display:'Portal ID'        , hidden:true , editable:false, sortable:false, search:false, primary:true},
	{ name:'organization_id', display:'Org ID'           , hidden:true , editable:false, sortable:false, search:false},
	{ name:'orgName'        , display:'Organization Name', hidden:false, editable:false, sortable:true , search:true },
	{ name:'emails'         , display:'Email Address(es)', hidden:false, editable:true , sortable:false, search:false},
	{ name:'name'           , display:'Portal'           , hidden:false, editable:false, sortable:true , search:true },
	{ name:'portalType'     , display:'Portal Type'      , hidden:false, editable:false, sortable:true , search:true },
	{ name:'subject'        , display:'Subject'          , hidden:true , editable:true , sortable:false, search:false},
	{ name:'body'           , display:'Body'             , hidden:true , editable:true , sortable:false, search:false},
	{ name:'send_format'    , display:'Format'           , hidden:true , editable:true , sortable:false, search:false},
	
];
//--------------------------------------------------------
var organizationColumns = new Columns(columns);
var data = {
	columns: organizationColumns,
	apiURL: apiURL + '/api/dashboard/org_emails_config'
}
//--------------------------------------------------------
if($("#org_emails_config").length != 0){
	table = new OrganizationEmails(data);
}
//--------------------------------------------------------
