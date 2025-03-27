import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class RelationTypeSynonym extends DataTable {
	//------------------------------------------------------------
	constructor(data){
		super(data);
		this.showGlobal = true;
		this.pageSort = 'rtSynonymRelationTypeName';
		this.isEditMode = false;
		this.terms = [];
		this.tense = [];
		this.relationTypes = [];
//    	this.getAllTerms();
		this.getAllTense();
		this.getAllRelationTypes();
		
		let tmpThis = this;
		tmpThis.showGlobalStatus=1;
		$("#showGlobal").prop('checked', true);
		$('body').on('change', '#showGlobal', (e) => { 
			if($(this).prop('checked')==true){ 
				tmpThis.showGlobalStatus=1;
				$(this).prop('checked', false);
			}else{ 
				tmpThis.showGlobalStatus=0;
				$(this).prop('checked', true);
				if($("#relationTypeSynonymOwnersList").val()==-1){ $("#relationTypeSynonymOwnersList").val(orgID); }
			}
			$("#relationTypeSynonymOwnersList").change();
		});
		$('body').on('change', '#relationTypeSynonymOwnersList', (e)=>{
			$(tmpThis.table).bootstrapTable('selectPage', 1);
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
		});
	}
	//------------------------------------------------------------
	get getURL() { 
		return this.apiURL + 
				'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' + this.pageNumber +
				'/ownerId/' + $("#relationTypeSynonymOwnersList").val()+'/showglobal/'+this.showGlobalStatus;
	}
	get searchURL() {
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
			this.search+ '/ownerId/'+$("#relationTypeSynonymOwnersList").val()+'/showglobal/'+this.showGlobalStatus;

	}
	//------------------------------------------------------------
	searchTermByName(objID, val){
		$("#findTermsBTN").hide();
		$("#wait4terms").show();
		val = val.trim();
//		if(val.length<3){ return; }
		if(val==''){ return; }
//		var ID = objID.replace('-search', '');
		var obj = $("select#"+objID);
//		$("#"+objID).hide();
//		$("#"+objID).val('');
		$("select#"+objID+" option").remove();
		$("#termsList option").remove();
/*	
		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+val+'/'+termPerPage+'/ownerId/'+$('#termOwnersList').val(), (dataIn) => {
*/
		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+val+'/'+termPerPage+'/ownerId/'+"-1", (dataIn) => {
			
			for(var i=0; i<dataIn.data.length; i++){
				var tmp = "<option data-prev='0' onDblClick='selectTermItem()' data-next='0' value='"+dataIn.data[i].termId+"' >"+dataIn.data[i].termName+"</option>";
				$(obj).append(tmp);
				$("#termsList").append(tmp);
			}
//			if(dataIn.data.length!=0){ $(obj).val(dataIn.data[0].termId).change(); }
		})
		.always(function() {
			$("#wait4terms").hide();
			$("#findTermsBTN").show();
		});
		$("#termsList").focus();
		$(obj).focus();
	}
	//------------------------------------------------------------
	getTerms(id, obj, direction){
		$("#findTermsBTN").hide();
		$("#wait4terms").show();
		$(obj).find("option").remove();
		$("#termsList option").remove();
/*		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+id+'/'+termPerPage+'/'+direction+'/ownerId/'+$('#termOwnersList').val(), (dataIn) => {
*/
		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+id+'/'+termPerPage+'/'+direction+'/ownerId/'+"-1", (dataIn) => {
			for(var i=0; i<dataIn.data.length; i++){ 
				var tmp = "<option data-prev='0' onDblClick='selectTermItem()' data-next='0' value='"+dataIn.data[i].termId+"' >"+dataIn.data[i].termName+"</option>";
				if(direction=='n'){ 
					$(obj).append(tmp); 
					$("#termsList").append(tmp); 
				}else{ 
					$(obj).prepend(tmp); 
					$("#termsList").prepend(tmp); 
				}
			}
			if(id==0){ $("input#rtSynonymTermIdTEMP").val(''); }
			else{ $(obj).val(id); $("input#rtSynonymTermIdTEMP").val($(obj).find("option:selected").text()); }
		})
		.always(function() {
			$("#wait4terms").hide();
			$("#findTermsBTN").show();
		});
	}	
	//------------------------------------------------------------
	getAllTense(){
		$.get(apiURL+'/api/dashboard/term/tense/'+orgID+'/termName/asc', (obj) => {
			var termOptions = [];
			for(var i=0; i< obj.data.length; i++){ termOptions.push("<option value='"+obj.data[i].termId+"'>"+obj.data[i].termName+"</option>"); }
			this.tense = termOptions;
			$("select#rtSynonymTenseId").append(this.tense);
		});
	}
	//------------------------------------------------------------
	getAllRelationTypes(){
		$.get(apiURL+'/api/dashboard/relation_type/all/'+orgID+'/relationTypeName/asc', (obj) => {
			var relationTypeOptions = [];
			for(var i=0;i<obj.data.length;i++){ relationTypeOptions.push("<option value='"+obj.data[i].relationTypeId+"'>"+obj.data[i].relationTypeName+"</option>"); }
			this.relationTypes = relationTypeOptions;
			$("select#rtSynonymRelationTypeId").append(this.relationTypes);
		});
	}
	//------------------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'rtSynonymTenseId':
			case 'rtSynonymRelationTypeId':
				input = $("<div>")
					.attr({ class: "col-" + col + " form-group"})
					.append($("<label>"+label+"</label>"))
					.append($("<div>").append($("<select>").attr({id: col,name: col,class: 'form-control'})));
				break;
		
			case 'rtSynonymTermId':
				input = $("<div>")
					.attr({class: "col-" + col + " form-group"})
					.append($("<label>"+label+"</label>"))
					.append( $("<div>")
						.append($("<select onchange='getSelectTerm(this)'>").attr({id:col, name:col, class:'form-control', style:'display:none'}))
						.append(
							$("<input>").attr({
								id: col+'TEMP',
								class: 'form-control',
								disabled: true,
								style: "width:96% !important;display:inline-block;margin-right:1%;"
							})
						)
						.append(
							$("<i>").attr({
								id: col+'-btn-search',
								class: 'fa fa-search',
								style: '',
								onclick: "showSearchBox('"+col+"', '"+label+"')"
							})
						)
					);
				break;
	
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//------------------------------------------------------------
	showEditDialogHandler(e){
		$("#termOwnersList").val(-1);
		
		this.isEditMode = true;
		super.showEditDialogHandler(e);
		this.getTerms(this.editItem.rtSynonymTermId, $("select#rtSynonymTermId"), 'n');
/*
		if(orgID==0){
			$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
		}
*/
		if(this.editItem.ownerId==0 || this.editItem.ownerId==null){
			$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
		}
	}
	//------------------------------------------------------------
	showAddDialogHandler(){
		$("#termOwnersList").val(-1);

		this.isEditMode = false;
		this.getTerms(0, $("select#rtSynonymTermId"), 'n');
		super.showAddDialogHandler();
/*
		if(this.ownerId==0){
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").click();
		}
*/
		if(orgID==0){
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
	//------------------------------------------------------------
}
//----------------------------------------------------------------
//----------------------------------------------------------------
var columns = [
	{ name: 'rtSynonymId', primary: true, hidden: true },
	
	{ name: 'rtSynonymRelationTypeId', display: 'Relation Type', hidden: true },
	{ name: 'rtSynonymRelationTypeName', display: 'Relation Type', sortable: true, editable: false },
	
	{ name: 'rtSynonymTenseId', display: 'Tense', hidden: true },
	{ name: 'rtSynonymTenseName', display: 'Tense', sortable: true, editable: false },
	
	{ name: 'rtSynonymTermId', display: 'Term', hidden: true },
	{ name: 'rtSynonymTermName', display: 'Term', sortable: true, editable: false },
	
	{ name: 'rtSynonymDisplayName', display: 'Name', sortable: true, editable: true },
	{ name: 'rtSynonymDescription', display: 'Description', sortable: true, editable: true },
	
	{ name: 'rtIsReserved', display: 'Reserved', sortable: true, reserved: true },
	{ name: 'ownerId', display: 'Owner', hidden: true, onlyFor:0 },
	{ name: 'ownership', display: 'Ownership', default: '2', ownership: true, sortable: true },
	{ name: 'organizationShortName', display: 'Owner', sortable: true, editable: false },
	{ name: 'lastUserId', display: 'User', hidden: true, editable: false, default: '1'},
	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
];
//----------------------------------------------------------------
//----------------------------------------------------------------
var relationTypeSynonymColumns = new Columns(columns);
//----------------------------------------------------------------
var data = {
	columns: relationTypeSynonymColumns,
	apiURL: apiURL + '/api/dashboard/relation_type_synonym'
}
//----------------------------------------------------------------
if($("#relation_type_synonym").length != 0){
	table = new RelationTypeSynonym(data);
	table.createTable('relation_type_synonym');
}
//----------------------------------------------------------------
//----------------------------------------------------------------
