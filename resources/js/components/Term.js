import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class Term extends DataTable {
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	constructor(data){
		super(data);
		let tmpThis = this;
		this.showGlobal = true;
//		this.showGlobal = ((orgID==0) ?false :true);
		this.pageSort = 'termName';
		var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'Ext. Data Link', class: 'link-item', 'data-onlyowner': 1 });
		var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'KR-Term Link', class: 'krtermlink-item', 'data-onlyowner': 1 });
		this.actionIcons = this.actionIcons.concat([icon1, icon2]);
		
		$('body').on('click', '.link-item', (e) => { this.showLinkDialogHandler(e) });
		$('body').on('click', '.krtermlink-item', (e) => { this.showKRTermLink(e) });

		tmpThis.showGlobalStatus=1;
		$("#showGlobal").prop('checked', true);
		$('body').on('change', '#showGlobal', (e) => { 
			if($(this).prop('checked')==true){ //Show global NO
				tmpThis.showGlobalStatus=1;
				$(this).prop('checked', false);
			}else{ //Show global YES
				tmpThis.showGlobalStatus=0;
				$(this).prop('checked', true);
				if($("#termOwnersList").val()==-1){ $("#termOwnersList").val(orgID); }
			}
			$("#termOwnersList").change();
		});

		$('body').on('change', '#termOwnersList', (e) => { 
			$(tmpThis.table).bootstrapTable('selectPage', 1);
		});

		$('body').on('change', '#show_system_terms', (e) => { 
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
		$('body').on('click', '#deleteDialog .btn.btn-no', (e)=>{
			$(this.table).bootstrapTable('uncheckAll')
			$('#deleteDialog').fadeOut();
		});
		
		this.tmpDelIDs = [];
		this.haveResRow = false;
		
		$("#show_system_terms").ready(function(){
			if(userLevel!=1){ $("#show_system_terms").parent().hide(); }
		});
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		if(this.ownerId==0){
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
		}
		if(userLevel==1){ $(".col-IsSystemTermOnly").hide(); }
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);

		if(this.editItem.ownerId==0 || this.editItem.ownerId==null){
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
		}
		if(userLevel==1){
			$(".col-IsSystemTermOnly").hide();
			$("#IsSystemTermOnly").bootstrapToggle('off');
			if(this.editItem.systemTerm){
				$(".col-IsSystemTermOnly").show();
				if($("#IsSystemTermOnly").val()==1){ $("#IsSystemTermOnly").bootstrapToggle('on'); }
			}
		}
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showDeleteDialogHandler(e){
		let tmpRow = {};
		let rsvDelIDs = [];
		$.each(this.rows, (i, item) => {
			if(item[this.columns.primaryColumn] == $(e.currentTarget).data('itemid')){
				tmpRow = item;
				
				let chkValues = [];
				chkValues.push(item.termId);
				$(this.table).bootstrapTable('checkBy', {field: 'termId', values: chkValues})
				return false;
			}
		});
		this.tmpDelIDs = [];
		this.haveResRow = false;
		$.each(this.rows, (i, item) => {
			if(item['checkBox']){
				if(this.ownerId==0 && userLevel==1){ 
					this.tmpDelIDs.push(item[this.columns.primaryColumn]); 
					if(item.termIsReserved==1) rsvDelIDs.push(item[this.columns.primaryColumn]); 
				}else{
					if(item.ownerId==this.ownerId){ 
						this.tmpDelIDs.push(item[this.columns.primaryColumn]); 
						if(item.termIsReserved==1) rsvDelIDs.push(item[this.columns.primaryColumn]); 
					}
					if(item.ownerId==null && this.ownerId==0){ 
						this.tmpDelIDs.push(item[this.columns.primaryColumn]); 
						if(item.termIsReserved==1) rsvDelIDs.push(item[this.columns.primaryColumn]); 
					}
				}
			}
		});
		if(rsvDelIDs.length!=0){
			this.haveResRow = true;
			if(userLevel!=1){
				$("#deleteDialog .msgDIV")
					.html("This Delete request included <b>Reserved</b> records. You do not have authorization to delete <b>Reserved</b> records");
				$("#deleteDialog  .msgInner").css('height', '140px');
				$("#deleteDialog  .btn-yes" ).hide();
				$("#deleteDialog  .btn-no"  ).css('float', 'right');
				$("#deleteDialog  .btn-no"  ).text('Close');
			}else{
				$("#deleteDialog  .msgInner").css('height', '220px');
				$("#deleteDialog  .btn-yes" ).show();
				$("#deleteDialog  .btn-no"  ).css('float', 'left');
				$("#deleteDialog  .btn-no"  ).text('No');
				$("#deleteDialog .msgDIV")
					.html(
						this.tmpDelIDs.length+" records will be deleted<br/>"+
						"including "+rsvDelIDs.length+" <b>Reserved</b> records.<br/><br/>"+
						"Enter your <i>password</i> and select Yes to Delete<br/>click No to Cancel"+
						"<input id='delPass' type='password' class='form-control' value='' placeholder='password' autocomplete='off' />"
					);
			}
		}else{
			$("#deleteDialog .msgDIV").html( 
				this.tmpDelIDs.length+" records will be deleted<br/>"+
				"Are you sure you want to delete this items?"
			);
			$("#deleteDialog  .msgInner").css('height', '140px');
			$("#deleteDialog  .btn-yes" ).show();
			$("#deleteDialog  .btn-no"  ).css('float', 'left');
			$("#deleteDialog  .btn-no"  ).text('No');
		}
		super.showDeleteDialogHandler(e);
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	deleteDialog() {
		var dialog = "<div id='deleteDialog' style='display:none'></div>";
		var inner = "<div class='msgInner'></div>";
		var msg = "<div class='msgDIV' style='line-height:22px'></div>";
		var actions = $("<div>").attr({
			class:'deleteActions',
			style:"width:100%; text-align:right; padding:5px 5px 5px 20px;"
		});
		var yes = $("<button>Yes</button>").attr({
			id: 'delete-confirm',
			class: 'btn btn-primary btn-yes',
			style:"width:80px;"
		});
		var no = $("<button>Cancel</button>").attr({
			class: 'btn btn-danger btn-no',
			style:"width:80px;float:left;"
		});
		actions = $(actions).append(yes).append(no);
		inner = $(inner).append(msg).append(actions);
		dialog = $(dialog).append(inner);
		return dialog;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	get deleteURLs() { return this.apiURL + "/delete/"; }
	deleteConfirmHandler(){
		let delPass = "";
		if($("#deleteDialog #delPass").length!=0){
			delPass = $("#deleteDialog #delPass").val().trim();
			if(delPass==''){ 
				$("#deleteDialog #delPass").val(delPass);
				showError('Enter your password');
				return;
			}
		}
		if(!this.haveResRow && this.tmpDelIDs.length==1){ super.deleteConfirmHandler(); }
		else{
			var thisTable = this.table;
			var data = {};
			data.userID = userID;
			data.pass   = delPass;
			data.IDs    = this.tmpDelIDs;
			$.ajax({
				url: this.deleteURLs,
				data: JSON.stringify(data),
				type: 'delete',
				dataType: 'json',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json'
				},
				success: function(res){
					if(res.result == 1){ showError(res.msg); }
					else{
						$(thisTable).bootstrapTable('uncheckAll')
						$("#deleteDialog").fadeOut();
						showSuccess('Deleted successfully');
						$(thisTable).bootstrapTable('refresh');
					}
				},
				error: function(e){ showError('Server Error'); }
			})
		}
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	get getColumns() {
		var columns = [];
		for(var x in this.columns.data){
			var column = this.columns.data[x];
			if(column.hidden === true && column.primary !== true) continue;
			if(column.name=='checkBox'){
				var obj = {
					//        editable: true,
					field: column.name,
					title: column.display,
					sortable: (column.sortable)? true:false,
					visible: (column.hidden === true)? false:true,
					formatter: (value, row, index, field) => this.checkBoxRender(value, row, index, field),
					checkbox: true
				}
			}else{
				var obj = {
					//        editable: true,
					field: column.name,
					title: column.display,
					sortable: (column.sortable)? true:false,
					visible: (column.hidden === true)? false:true,
					formatter: (value, row, index, field) => this.cellRenderer(value, row, index, field),
					checkbox: false
				}
			}
			if(column.width !== null) obj.width = column.width;
			columns.push(obj);
		}
		if(this.hasRowActions) {
			columns.push({
				field: 'actions',
				formatter: (value, row, index, field) => this.rowActions(value, row, index, field)
			})
		}
		return columns;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showLinkDialogHandler(e){
		e.preventDefault();
		window.location.href=this.apiURLBase + '/panel/extend/extendedlink/0/'+$(e.currentTarget).data('itemid');
	}
	showKRTermLink(e){
		window.location.href=this.apiURLBase + '/panel/kb/link_kr_to_term/t/'+$(e.currentTarget).data('itemid');
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	get getURL() {
		return this.apiURL + '/' +
			'page/' +
			((this.orgID) ?(this.orgID+'/') :'') +
			this.pageSort + '/' +
			this.pageOrder + '/' +
			this.pageSize + '/' +
			this.pageNumber + '/' +
			'ownerId/' + $("#termOwnersList").val() + '/' +
			'showglobal/'+this.showGlobalStatus + '/' +
			(($("#show_system_terms").prop('checked')==true) ?1 :0);
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	get searchURL() {
		return this.apiURL + '/' +
			((this.orgID) ? (this.orgID + '/') : '') +
			this.pageSort + '/' +
			this.pageOrder + '/' +
			this.pageSize +'/' +
			this.pageNumber + '/' +
			this.columns.searchColumn + '/' +
			this.search + '/' +
			'ownerId/' + $("#termOwnersList").val() + '/' +
			'showglobal/'+this.showGlobalStatus + '/' +
			(($("#show_system_terms").prop('checked')==true) ?1 :0);
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	checkBoxRender(value, row, index, field){
		if(this.ownerId!=0){
			if(row.ownerId!=this.ownerId){ return { disabled: true }; }
		}
		return value;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	tableToolbar(){
		let toolbar = super.tableToolbar();

		let display = "";
		if(userLevel!=1){ display="display:none;"; }
		toolbar.append(
			$("<label>Show System Only Terms: </label>").attr({
				style:'margin:0 5px 0 20px;'+display
			})
		);
		toolbar.append(
			$("<input>").attr({
				id  : 'show_system_terms',
				type: 'checkbox' ,
				'data-onstyle': 'info',
				'data-toggle' : 'toggle',
				'data-size'   : 'small',
				'data-on'     : 'Yes',
				'data-off'    : 'No',
				style         : display
			})
		);
		
		return toolbar;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'IsSystemTermOnly':
				if(userLevel==1){
					input = $("<div>")
						.attr({
							class: "col-" + col + " form-group",
							style: "vertical-align:top;display:none",
						})
						.append( $("<label>"+label+"</label>") )
						.append(
							$('<div>').append(
								$('<input>').attr({ id:col, name:col, 'data-toggle':'toggle', type:"checkbox" })
							)
						);
				}
				break;

			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	cellRenderer(value, row, index, field){
		let retVal = super.cellRenderer(value, row, index, field);
		if( field=='systemTerm'){
			if(value>0){ return "<span class='glyphicon glyphicon-ok' style='color:green'></span>"; }
			else{ return "<span class='glyphicon glyphicon-minus' style='color: #adadad'></span>"; }
		}
		return retVal;
	}
	//----------------------------------------------------
}
//------------------------------------------------------------------

//------------------------------------------------------------------
var columns = [
	{ name: 'checkBox', display: '', sortable: false, editable: false },
	{ name: 'termId', display: 'ID', primary: true, sortable: true },
	{ name: 'termName', display: 'Name', sortable: true, search: true },
	
	{ name: 'IsSystemTermOnly', display:'Is System Term Only', sortable:false, editable:true, search:false, hidden: true  },
	{ name: 'systemTerm', display:'System Term', sortable: true, editable:false, search:false, hidden:((userLevel==1)?false:true)  },

	{ name: 'termIsReserved', display: 'Reserved', sortable: true, reserved: true },
	{ name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
	{ name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true, ownerId: true },
	{ name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
	{ name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
var termColumns = new Columns(columns);

var data = {
	columns: termColumns,
	apiURL: apiURL + '/api/dashboard/term'
}

if($("#term").length != 0){
	var table = new Term(data);
	table.createTable('term');
}
//------------------------------------------------------------------

//------------------------------------------------------------------
