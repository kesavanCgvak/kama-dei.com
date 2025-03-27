import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class RelationTypeGroup extends DataTable {
	//------------------------------------------------------------
	constructor(data){
		super(data);
		this.showGlobal = true;
		this.pageSort = 'relationTypeGroupName';
		this.getRelationTypes();
		this.getMyTerms();
		
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
				if($("#relationTypeGroupOwnersList").val()==-1){ $("#relationTypeGroupOwnersList").val(orgID); }
			}
			$("#relationTypeGroupOwnersList").change();
		});
		$('body').on('change', '#relationTypeGroupOwnersList', (e)=>{
			$(tmpThis.table).bootstrapTable('selectPage', 1);
		});
		$('body').on('change', '#ownerId', (e)=>{
			if($("#ownerId").val()==0){
				$("#"+this.columns.ownershipColumn+"0").click();
				$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
			}else{
				$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
			}
		});
	}
	//------------------------------------------------------------
	get getURL() { 
		return this.apiURL + 
				'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' + this.pageNumber +
				'/ownerId/' + $("#relationTypeGroupOwnersList").val()+'/showglobal/'+this.showGlobalStatus;
	}
	get searchURL() {
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
			this.search+ '/ownerId/'+$("#relationTypeGroupOwnersList").val()+'/showglobal/'+this.showGlobalStatus;

	}
	//------------------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);

		var tmp = $("form.action-form>div.col-relationTypeId.form-group");
		$("form.action-form>div.col-relationTypeId.form-group").remove;
		$("form.action-form").prepend(tmp);

		if(this.editItem.ownerId==0 || this.editItem.ownerId==null){
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
		}
	}
	//------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();

		var tmp = $("form.action-form>div.col-relationTypeId.form-group");
		$("form.action-form>div.col-relationTypeId.form-group").remove;
		$("form.action-form").prepend(tmp);

		if(this.ownerId==0){
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
		}
	}
	//------------------------------------------------------------
	getRelationTypes(){
		$("select#relationTypeId option").remove();
		$.get(apiURL+'/api/dashboard/relation_type/all/'+orgID, (dataIn) => {
			$("select#relationTypeId").append("<option value='0' >Select . . .</option>");
			for(var i=0; i<dataIn.data.length; i++){ 
				var tmp = "<option value='"+dataIn.data[i].relationTypeId+"' >"+dataIn.data[i].relationTypeName+"</option>";
				$("select#relationTypeId").append(tmp);
			}
		});
	}
	//------------------------------------------------------------
//	getMyTerms(lastTermID){
	getMyTerms(){
		$("select#relationAssociationTermId option").remove();
//		var tmp = $("select#relationTypeId").val();
		$.get(apiURL+'/api/dashboard/relation_type_group/myterms/'+orgID+'/0', (dataIn) => {
			$("select#relationAssociationTermId").append("<option value='0' >Select . . .</option>");
			for(var i=0; i<dataIn.data.length; i++){ 
				var tmp = "<option value='"+dataIn.data[i].termId+"' >"+dataIn.data[i].relationAssociationTermName+"</option>";
				$("select#relationAssociationTermId").append(tmp);
			}
//			$("select#relationAssociationTermId").val(lastTermID);
		});
	}
	/*
	rowActions(value, row, index, field) {
		if(row.ownerId == 0 && this.orgID != 0) return;
		//----------------------------------------------------------
		var rowAction = '<div class="row-actions"></div>';
		var deleteIcon = $("<a><small class='glyphicon glyphicon-trash'></small></a>").attr({
			href: '#',
			style: "color:#f3ae4e",
			class: 'delete-item',
			'data-itemid': row[this.columns.primaryColumn]
		});
		return $(rowAction).append(deleteIcon)[0].outerHTML;
	}
	*/
	//--------------------------------------------------------------
	/*
	searchTermByName(objID, val){
		val = val.trim();
		if(val==''){ return; }
		var obj = $("select#"+objID);
		$("select#"+objID+" option").remove();
		$("#termsList option").remove();
		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+val+'/'+termPerPage, (dataIn) => {
			for(var i=0; i<dataIn.data.length; i++){
				var tmp = "<option data-prev='0' data-next='0' value='"+dataIn.data[i].termId+"' >"+dataIn.data[i].termName+"</option>";
				$(obj).append(tmp);
				$("#termsList").append(tmp);
			}
		});
		$("#termsList").focus();
		$(obj).focus();
	}
	*/
	//------------------------------------------------------------
	/*
	getTerms(id, obj, direction){
		$(obj).find("option").remove();
		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+id+'/'+termPerPage+'/'+direction, (dataIn) => {
			for(var i=0; i<dataIn.data.length; i++){ 
				var tmp = "<option data-prev='0' data-next='0' value='"+dataIn.data[i].termId+"' >"+dataIn.data[i].termName+"</option>";
				if(direction=='n'){ $(obj).append(tmp); }
				else{ $(obj).prepend(tmp); }
//				$(obj).append(tmp);
			}
			if( id!=0 ){ $(obj).val(id).change(); }
			else{ $(obj).change(); }
		});
	}
	*/
	//------------------------------------------------------------
	/*
	getAllRelationTypes(){
		$.get(apiURL+'/api/dashboard/relation_type/all/'+orgID+'/relationTypeName/asc', (obj) => {
			var relationTypeOptions = [];
			for(var i=0; i< obj.data.length; i++){ 
				relationTypeOptions.push("<option value='"+obj.data[i].relationTypeId+"'>"+obj.data[i].relationTypeName+"</option>");
			}
			this.relationTypes = relationTypeOptions;
			$("select#relationTypeId").append(this.relationTypes);
		});
	}
	*/
	//------------------------------------------------------------
	/*
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);
		this.getTerms(this.editItem.leftTermId , $("select#leftTermId" ), 'n');
		this.getTerms(this.editItem.rightTermId, $("select#rightTermId"), 'n');
		var tempVar =
				$("select#leftTermId option:selected").text()+' '+
				$("select#relationTypeId option:selected").text()+' '+
				$("select#rightTermId option:selected").text();
		$("input#tempVar").val(tempVar);
	}
	*/
	//----------------------------------------------------
	/*
	showAddDialogHandler(){
		this.getTerms(0, $("select#leftTermId" ), 'n');
		this.getTerms(0, $("select#rightTermId"), 'n');
		super.showAddDialogHandler();
		$("#insertItem").prop('disabled',false);
		$("#saveItem"  ).prop('disabled',false);
		var tempVar =
				$("select#leftTermId option:selected").text()+' '+
				$("select#relationTypeId option:selected").text()+' '+
				$("select#rightTermId option:selected").text();
		$("input#tempVar").val(tempVar);

		$("input#leftTermIdTEMP").val($("select#leftTermId option:selected").text());
		$("input#rightTermIdTEMP").val($("select#rightTermId option:selected").text());
	}
	*/
	//----------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'relationTypeId':
			case 'relationAssociationTermId':
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label>"+label+"</label>") )
					.append(
						$("<div>").append(
							$("<select>").attr({
								id: col,
								name: col,
								class: 'form-control'
							})
						)
					);
				break;
			case 'description':
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label>"+label+"</label>") )
					.append(
						$("<div>").append(
							$("<textarea>").attr({
								id: col,
								name: col,
								class: 'form-control'
							})
						)
					);
				break;
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------
}
//----------------------------------------------------------------
var columns = [
	{ name: 'relationTypeGroupId', primary: true, hidden: true },
	{ name: 'relationAssociationTermId', display: 'Relation Type Group', hidden: true, editable: true },
//	{ name: 'relationAssociationTermId', display: 'Relation Type Groups', sortable: true, editable: false },
	{ name: 'relationTypeGroupName', display: 'Relation Type Group', sortable: true, editable: false },

	{ name: 'relationTypeId', display: 'Relation Type', hidden: true },
	{ name: 'relationTypeName', display: 'Relation Type', sortable: true, editable: false },

	{ name: 'description', display: 'Description', hidden: true },

	{ name: 'ssReserved', display: 'Reserved', sortable: true, reserved: true },

	{ name: 'ownership', display: 'Ownership', default: '1', ownership: true, sortable: true, editable: true },

	{ name: 'organizationShortName', display: 'Owner', sortable: true, editable: false },
	{ name: 'ownerId', display: 'Owner', hidden: true, onlyFor:0 },

	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
/*
	{ name: 'leftTermId', display: 'Left Term', hidden: true },
//  { name: 'leftTermName', display: 'Left Term', sortable: true, editable: false },
	{ name: 'relationTypeId', display: 'Relation Type', hidden: true },
	//  { name: 'relationTypeName', display: 'Relation Type', sortable: true, editable: false },
	{ name: 'rightTermId', display: 'Right Term', hidden: true },
//  { name: 'rightTermName', display: 'Right Term', sortable: true, editable: false },
	{ name: 'relationOperand', display: 'Operand', hidden: true, editable: false },
	
	{ name: 'tempVar', display: 'tempVar', hidden: true, editable: true },
	
	{ name: 'organizationShortName', display: 'Owner', sortable: true, editable: false },
	{ name: 'relationIsReserved', display: 'Reserved', sortable: true, reserved: true },
	*/
];
//----------------------------------------------------------------
var myColumns = new Columns(columns);
//----------------------------------------------------------------
var data = {
	columns: myColumns,
	apiURL: apiURL + '/api/dashboard/relation_type_group'
}
//----------------------------------------------------------------
if($("#relationTypeGroup").length != 0){
	table = new RelationTypeGroup(data);
	table.createTable('relationTypeGroup');
}
//----------------------------------------------------------------
