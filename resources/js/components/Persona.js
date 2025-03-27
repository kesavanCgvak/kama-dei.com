import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class Persona extends DataTable {
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	constructor(data){
		super(data);
		this.showGlobal = true;
		$('body').on('click', '#cloneBtn', (e) => { this.showCloneDialogHandler() });
		this.createCloneDialog();
		
		let tmpThis = this;
		tmpThis.showGlobalStatus=1;
		$('body').on('change', '#showGlobal', (e) => { 
			if($(this).prop('checked')==true){ 
				tmpThis.showGlobalStatus=1;
				$(this).prop('checked', false);
			}else{ 
				tmpThis.showGlobalStatus=0;
				$(this).prop('checked', true);
				if($("#myOwnersList").val()==-1){ $("#myOwnersList").val(orgID); }
			}
			$("#myOwnersList").change();
		});

		$('body').on('change', '#ownerId', (e)=>{
			if($("#ownerId").val()==0){
				$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"0").click();
				$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
			}else{
				$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").click();
			}
		});
		
		$('body').on('change', '#cloneOwnerID', (e)=>{
			if($("#cloneOwnerID").val()==0){
				$("#cloneownership0").parent().removeClass('disabled');
				$("#cloneownership0").click();
				$("#cloneownership1").parent().addClass('disabled');
				$("#cloneownership2").parent().addClass('disabled');
			}else{
				$("#cloneownership0").parent().addClass('disabled');
				$("#cloneownership1").parent().addClass('disabled');
				$("#cloneownership2").parent().removeClass('disabled');
				$("#cloneownership2").click();
			}
		});
		
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	get getURL() { 
		return this.apiURL + 
				'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' + this.pageNumber +
				'/ownerId/' + $("#myOwnersList").val()+'/showglobal/'+this.showGlobalStatus;
	}
	get searchURL() {
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
			this.search+ '/ownerId/'+$("#myOwnersList").val()+'/showglobal/'+this.showGlobalStatus;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	tableToolbar(){
		var toolbar = $("<div>").attr({	id: 'tableToolbar' });
		toolbar
			.append( $("<button>Add Item</button>").attr({ id: 'insertBtn', class: 'btn btn-primary personalityBTN' }) )
			.append( $("<button>Clone</button>").attr({ id: 'cloneBtn', class: 'btn btn-info personalityBTN' }) )
			.append( $("<label>Show Global: </label>").attr({ style:'margin:0 5px 0 20px;' }) )
			.append(
				$("<input checked >").attr({
					id  : 'showGlobal',
					type: 'checkbox' ,
					'data-onstyle': 'info',
					'data-toggle' : 'toggle',
					'data-size'   : 'small',
					'data-on'     : 'Yes',
					'data-off'    : 'No'
				})
			);
		return toolbar;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	getOrganizations() {
		$.get(this.organizationURL, (res) => {
			this.organizations = this.createSelectOptions(res.data, 'organizationId', 'organizationShortName');
			this.ownerId = this.orgID;
			$("#orgID, #ownerId, #cloneOwnerID").append(this.organizations);
		});
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	getPersona(){
		$("#personaPersonalitySelect option").remove();
		$.get(apiURL+'/api/dashboard/personality/zeroPersonality/'+orgID+'/-1/personalityName/asc', (ret) => {
			$("#personaPersonalitySelect").append("<option value='0'>Select Persona</option>");
			for(var i in ret.data){
				$("#personaPersonalitySelect")
					.append("<option value='"+ret.data[i].personalityId+"' >"+ret.data[i].personalityName+"</option>");
			}
		});
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	getPersonaParents(){
		$("#clone2Select option").remove();
		$.get(apiURL+'/api/dashboard/personality/zeroPersonality/'+orgID+'/-1/personalityName/asc', (ret) => {
			$("#clone2Select").append("<option value=''>Select persona parent</option>");
			for(var i in ret.data){
				$("#clone2Select")
					.append("<option value='"+ret.data[i].personalityId+"'>"+ret.data[i].personalityName+"</option>");
			}
		});
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	createCloneDialog(){
		var div = ''+
			'<div class="modal fade cloneItem" tabindex="-1" role="dialog" aria-labelledby="cloneItem" aria-hidden="true" id="cloneItem">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header"><h3 style="margin:2px 10px">Clone a persona :</h3></div>'+
						'<div class="modal-body">'+
							'<div>'+
								"<label for='personaPersonalitySelect' class='personaPersonalitySelect'>Source Persona</label>"+
								"<select id='personaPersonalitySelect' class='form-control'>"+
									"<option value=''>Select ...</option>"+
								"</select>"+
							'</div>'+
							'<hr/>'+
							'<div>'+
								"<label style='margin-right:35px;'>Target :</label>"+
								"<input type='radio' name='personaPersonalityRadio' id='personaPersonalityRadio0'/>"+
								"&nbsp;<label for='personaPersonalityRadio0' style='cursor:pointer;margin-right:35px;'>Persona</label>"+
//								"<input type='radio' name='personaPersonalityRadio' id='personaPersonalityRadio1'/>"+
//								"&nbsp;<label for='personaPersonalityRadio1' style='cursor:pointer'>Personality</label>"+
							'</div>'+
							'<div class="targetName">'+
								'<br/>'+
								"<label for='cloneName'></label>"+
								"<input type='text' placeholder='Name' id='cloneName' value='' class='form-control' />"+
							'</div>'+
							'<div class="clone2Select">'+
								'<br/>'+
								'<label for="clone2Select">Target persona parent :</label>'+
								"<select id='clone2Select' class='form-control'></select>"+
							'</div>'+
							'<hr/>'+
							"<div class='half isAdminOnley'>"+
								"<label>Ownership</label>"+
								"<div style='text-align:left'>"+
									"<div class='btn-group' data-toggle='buttons'>"+
										"<label class='btn btn-default disabled'>"+
											"<input type='radio' name='cloneownership' id='cloneownership0' value='0' autoComplete='off' /> Public"+
										"</label>"+
										"<label class='btn btn-default disabled'>"+
											"<input type='radio' name='cloneownership' id='cloneownership1' value='1' autoComplete='off' /> Protected"+
										"</label>"+
										"<label class='btn btn-default'>"+
											"<input type='radio' name='cloneownership' id='cloneownership2' value='2' autoComplete='off' /> Private"+
										"</label>"+
									"</div>"+
								"</div>"+
							"</div>"+
							"<div class='half isAdminOnley'>"+
								"<label>Owner</label>"+
								"<select id='cloneOwnerID' class='form-control'>"+
									"<option value=''>Select ...</option>"+
								"</select>"+
							"</div>"+


						'</div>'+
						'<div class="modal-fotter">'+
							'<div style="width:100%;border-top:1px dotted #ccc;padding:10px;" align="right">'+
								'<button type="button" class="btn btn-info btn-clone" style="width:30%">Clone</button>'+
								'<button type="button" class="btn btn-default btn-no" style="float:left;width:30%;" data-dismiss="modal">Cancel</button>'+
							'</div>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('body').append(div);
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showCloneDialogHandler(){
		//----------------------------------------------------------
		$(".targetName"  ).hide();
		$(".clone2Select").hide();
		//----------------------------------------------------------
		$("#personaPersonalityRadio0").prop('checked', true).change();
		$("#personaPersonalityRadio1").prop('checked', false);
		//----------------------------------------------------------
		this.getPersona();
		this.getPersonaParents();
		//----------------------------------------------------------
		$("#cloneOwnerID").val('');
		$("#cloneName"   ).val('');
		//----------------------------------------------------------
		$("#cloneownership0").prop('checked', false);
		$("#cloneownership1").prop('checked', false);
		$("#cloneownership2").prop('checked', false);

		$("#cloneownership0").parent().removeClass('active');
		$("#cloneownership1").parent().removeClass('active');
		$("#cloneownership2").parent().removeClass('active');
		//----------------------------------------------------------
		$("#cloneItem")
			.modal({
				keyboard: false,
				backdrop: 'static',
				show: true
			  });
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showSuccess(msg){ showSuccess(msg); }
	showError  (msg){ showError  (msg); }
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	rowActions(value, row, index, field) {
		//----------------------------------------------------------
		if(NoPersonaID==row.personalityId) return '';
		//----------------------------------------------------------
		return super.rowActions(value, row, index, field);
		//----------------------------------------------------------
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		if(this.ownerId==0){
			$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").click();
			$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
		}
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);

		if(this.editItem.ownerId==0 || this.editItem.ownerId==null){
			$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").click();
			$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
		}
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
}
//------------------------------------------------------------------

//------------------------------------------------------------------
var columns = [
	{ name: 'personalityId', display: 'ID', primary: true, sortable: true, width: '8%' },
	{ name: 'personalityName', display: 'Name', sortable: true, search: true, editable: true },
	{ name: 'parentPersonaId', display: '', hidden: true, editable: false },
	
	{ name: 'personalityDescription', display: 'Description', sortable: true, search: true, editable: true  },
	{ name: 'ownerId', display: '', onlyFor: 0, hidden: true},
	{ name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
	{ name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
	{ name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
//------------------------------------------------------------------
var personaColumns = new Columns(columns);
//------------------------------------------------------------------
var data = {
  columns: personaColumns,
  apiURL: apiURL + '/api/dashboard/persona'
}
//------------------------------------------------------------------
if($("#persona").length != 0){
	table = new Persona(data);
	table.createTable('persona');
}
//------------------------------------------------------------------

//------------------------------------------------------------------
