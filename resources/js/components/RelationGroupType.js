import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class RelationGroupType extends DataTable {
	//------------------------------------------------------------
	constructor(data){
		super(data);
		
		this.pageSort = 'relationGroupType';
		
//		this.terms = [];
//		this.relationTypes = [];
		
//		this.getAllRelationTypes();
	}
	//------------------------------------------------------------
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
	/*
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'rightTermId':
			case 'leftTermId':
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label>"+label+"</label>") )
					.append(
						$("<div>")
						.append(
							$("<select>").attr({
								id: col,
								name: col,
								class: 'form-control',
								style: 'display:none'
							})
						)
						.append(
							$("<input>").attr({
								id: col+'TEMP',
								class: 'form-control',
								disabled: true,
								style: "width:160px !important;display:inline-block;margin-right:5px;"
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
			case 'relationTypeId':
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

			case 'tempVar':
				input = $("<div>")
					.attr({ class: 'col-tmpVal form-group' })
					.append($("<div>")
						.append($("<input>").attr({
							disabled: 'disabled',
							name: 'tempVar',
							id: 'tempVar',
							placeholder: '',
							value:
								$("select#leftTermId option:selected").text()+' '+
								$("select#relationTypeId option:selected").text()+' '+
								$("select#rightTermId option:selected").text(),
							class: 'form-control'
						})
					));
				break;

			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	*/
	//----------------------------------------------------
}
//----------------------------------------------------------------
var columns = [
	{ name: 'relationId', primary: true, hidden: true },
	{ name: 'termName', display: 'Term Name', hidden: true, editable: true },
	{ name: 'relationGroupType', display: 'Relation Group Type', sortable: true, editable: false },

	{ name: 'ownership', display: 'Ownership', default: '2', ownership: true, sortable: true, editable: false },

	{ name: 'organizationShortName', display: 'Owner', sortable: true, editable: false },
	{ name: 'ownerId', display: 'Owner', hidden: true},

	{ name: 'lastUserId', display: 'User', hidden: true, editable: false, default: '1'},
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
var relationColumns = new Columns(columns);
//----------------------------------------------------------------
var data = {
	columns: relationColumns,
	apiURL: apiURL + '/api/dashboard/relation_group_type'
}
//----------------------------------------------------------------
if($("#relationGroupType").length != 0){
	table = new RelationGroupType(data);
	table.createTable('relationGroupType');
}
//----------------------------------------------------------------
