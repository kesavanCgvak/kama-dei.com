import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class Personality extends DataTable {
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	constructor(data){
		super(data);
		this.parents = [];
		this.showGlobal = true;
		$('body').on('click', '#cloneBtn', (e) => { this.showCloneDialogHandler() });
		this.createCloneDialog();
		
		let that = this;
		that.showGlobalStatus=1;
		$('body').on('change', '#showGlobal', (e) => { 
			if($(this).prop('checked')==true){ 
				that.showGlobalStatus=1;
				$(this).prop('checked', false);
			}else{ 
				that.showGlobalStatus=0;
				$(this).prop('checked', true);
				if($("#myOwnersList").val()==-1){ $("#myOwnersList").val(orgID); }
			}
			$("#myOwnersList").change();
		});
		$('body').on('change', '#myOwnersList', (e)=>{
			$(that.table).bootstrapTable('selectPage', 1);
		});
		$('body').on('change', '#ownerId', (e)=>{
			if($("#ownerId").val()==0){
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
			that.getAllParent($("#ownerId").val());
		});
	}
	//--------------------------------------------------------------
	rowActions(value, row, index, field) {
		//----------------------------------------------------------
		var icons = this.actionIcons;
		var tmpICN = [];
		//----------------------------------------------------------
		for (var i in icons){
			if( icons[i].attr('class')=='delete-item' ){ icons[i][0].dataset.desc="Reset personality"; }
		}
		//----------------------------------------------------------
		return super.rowActions(value, row, index, field);
		//----------------------------------------------------------
	}
	deleteDialog() {
		var dialog = "<div id='deleteDialog' style='display:none'></div>";
		var inner = "<div></div>";
		var msg = "<div>Are you sure you want to reset this item?</div>";
		var actions = "<div class='deleteActions'></div>";
		var yes = $("<button>Yes</button>").attr({
			id: 'delete-confirm',
			class: 'btn btn-primary'
		});
		var no = $("<button>Cancel</button>").attr({
			class: 'btn btn-danger',
			onClick: "$('#deleteDialog').fadeOut()"
		});
		actions = $(actions).append(yes).append(no);
		inner = $(inner).append(msg).append(actions);
		dialog = $(dialog).append(inner);
		return dialog;
	}
	deleteConfirmHandler(){
		$("#deleteDialog").fadeOut();
		var table = this.table;

		$.ajax({
			url: this.deleteURL,
			type: 'delete',
			dataType: 'json',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			success: function(res){
				if(res.result == 1){ showError(res.msg); }
				else{
					showSuccess('Reset successfully');
					$(table).bootstrapTable('refresh');
				}
			},
			error: function(e){ showError('Server Error'); }
		})
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
	getAllParent(inOrgId){
		$("#parentPersonaId option").remove();
		$.get(apiURL+'/api/dashboard/personality/parents/'+inOrgId, (obj) => {
			var parentsOptions = [];
			for(var i in obj.data){
				let validOrgId = inOrgId;
				if(inOrgId==0){ validOrgId=null; }
				if(obj.data[i].ownerId!=validOrgId){ continue; }
				parentsOptions.push("<option value='"+obj.data[i].personalityId+"'>"+obj.data[i].personalityName+"</option>");
			}
			this.parents = parentsOptions;
			$("#parentPersonaId").append(this.parents);
		});
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col){
			case 'parentPersonaId':
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<lebel>').text(label) )
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
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	tableToolbar(){
		var toolbar = $("<div>").attr({
			id: 'tableToolbar'
		});
		toolbar
			.append(
				$("<button>Add Item</button>")
					.attr({
						id: 'insertBtn',
						class: 'btn btn-primary personalityBTN'
					})
			)
			.append(
				$("<button>Clone</button>")
					.attr({
						id: 'cloneBtn',
						class: 'btn btn-info personalityBTN'
					})
			)
			.append(
				$("<label>Show Global: </label>").attr({
					style:'margin:0 5px 0 20px;'
				})
			)
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
	getPersonality(){
		$("#personaPersonalitySelect option").remove();
		$.get(apiURL+'/api/dashboard/personality/nonzeroPersonality/'+orgID+'/-1/personalityName/asc', (ret) => {
			$("#personaPersonalitySelect").append("<option value='0' data-parentID='0'>Select Personality</option>");
			for(let i in ret.data){
				let email    = "";
				let text     = "";
				let parentID = 0;
				if(ret.data[i].parentPersonaId!=0 ){
					if(ret.data[i].get_consumer_user!=null ){
						if(ret.data[i].get_consumer_user.email==null){ email=''; }
						else{ email = " | " + ret.data[i].get_consumer_user.email; }
					}
					parentID = ret.data[i].parentPersonaId;
					text = ret.data[i].personalityName+" | "+ret.data[i].parent_persona.personalityName+email;
				}else{
					text = ret.data[i].personalityName;
				}
				if(text.length>70){ text = text.substr(0,67)+"..."; }
				$("#personaPersonalitySelect")
					.append("<option value='"+ret.data[i].personalityId+"' data-parentID='"+parentID+"' >"+text+"</option>");
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
	showCloneDialogHandler(){
		$(".targetName"  ).hide();
		$(".clone2Select").hide();
		//----------------------------------------------------------
		$("#personaPersonalityRadio0").prop('checked', false);
		$("#personaPersonalityRadio1").prop('checked', false);
		$("#personaPersonalityRadio0").prop('disabled', true);
		$("#personaPersonalityRadio1").prop('disabled', true);
		//----------------------------------------------------------
		this.getPersonality();
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
	getOrganizations() {
		let that = this;
		$.get(this.organizationURL, (res) => {
			this.organizations = this.createSelectOptions(res.data, 'organizationId', 'organizationShortName');
			this.ownerId = this.orgID;
			$("#orgID, #ownerId, #cloneOwnerID").append(this.organizations);
			
			that.getAllParent(this.ownerId);
		});
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	createCloneDialog(){
		var div = ''+
			'<div class="modal fade cloneItem" tabindex="-1" role="dialog" aria-labelledby="cloneItem" aria-hidden="true" id="cloneItem">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header"><h3 style="margin:2px 10px">Clone a personality :</h3></div>'+
						'<div class="modal-body">'+
							'<div>'+
								"<label for='personaPersonalitySelect' class='personaPersonalitySelect'>Source Personality</label>"+
								"<select id='personaPersonalitySelect' class='form-control'>"+
									"<option value=''>Select ...</option>"+
								"</select>"+
							'</div>'+
							'<hr/>'+
							'<div>'+
								"<label style='margin-right:35px;'>Target :</label>"+
								"<input type='radio' name='personaPersonalityRadio' id='personaPersonalityRadio0'/>"+
								"&nbsp;<label for='personaPersonalityRadio0' style='cursor:pointer;margin-right:35px;'>Persona</label>"+
								"<input type='radio' name='personaPersonalityRadio' id='personaPersonalityRadio1'/>"+
								"&nbsp;<label for='personaPersonalityRadio1' style='cursor:pointer'>Personality</label>"+
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
										"<label class='btn btn-default'>"+
											"<input type='radio' name='cloneownership' id='cloneownership0' value='0' autoComplete='off' /> Public"+
										"</label>"+
										"<label class='btn btn-default'>"+
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
								'<button type="button" class="btn btn-danger btn-no" style="float:left;width:30%;" data-dismiss="modal">Cancel</button>'+
							'</div>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('body').append(div);
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showSuccess(msg){ showSuccess(msg); }
	showError  (msg){ showError  (msg); }
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
	{ name: 'personalityId', display: 'ID', primary: true, sortable: true },
	{ name: 'personalityName', display: 'Name', sortable: true, search: true, editable: true },
	
	{ name: 'personalityUsers', display: 'User<small style="font-size:70%">(s)</small>', sortable: false, search: false, editable: false },
	
	{ name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true},

	{ name: 'parentPersonaId', display: 'Parent', hidden:true },
	{ name: 'parentPersonaName', display: 'Parent', sortable: true,editable: false, search:false },
	
	{ name: 'personalityDescription', display: 'Description', sortable: true, search: true, editable: true  },
//	{ name: 'ownerId', display: '', onlyFor: 0, hidden: true},
	{ name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
	{ name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false, search: true },
	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
	{ name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
//------------------------------------------------------------------
var personalityColumns = new Columns(columns);
var data = {
  columns: personalityColumns,
  apiURL: apiURL + '/api/dashboard/personality'
}
//------------------------------------------------------------------
if($("#personality").length != 0){
	table = new Personality(data);
	table.createTable('personality');
}
//------------------------------------------------------------------

//------------------------------------------------------------------
