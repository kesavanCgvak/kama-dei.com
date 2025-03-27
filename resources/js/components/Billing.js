//--------------------------------------------------------

//--------------------------------------------------------
import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
//--------------------------------------------------------

//--------------------------------------------------------
class Billing extends DataTable {
	//----------------------------------------------------
	constructor(data){
		super(data);
		this.childColumns = data.childColumns;
		this.pageSort = 'id';
		this.hasRowActions = false;
		this.lastExpandRow = null;
		this.lastOrgID = 0;
		let that = this;
		$('body').ready(function(){
			$('#insertBtn').remove();
			$('body').on('expand-row.bs.table', '#billingTableRoot', function(e, i , d){ that.expandRow(i, d); });
			$('body').on('collapse-row.bs.table', '#billingTableRoot', function(e, i , d){ that.lastExpandRow = null; });
		});
		$('body').on('click', '.orgBill', (e) => { this.orgBill(e); });
		$('body').on('click', '.orgDetail', (e) => { this.orgDetail(e); });
	}
	//----------------------------------------------------
	orgBill(e){
		let ID   = $(e.currentTarget).data('id');
		let dt   = $(e.currentTarget).data('date');
		let orgN = $(e.currentTarget).data('org');
		let mnth = $(e.currentTarget).data('month');
		let yr   = $(e.currentTarget).data('year');
		$("#openBill .modal-title").html("<b>"+orgN+"</b> "+mnth+" "+yr);
		$("#openBill .modal-body")
			.html('<iframe src="/billing/bill/'+ID+'/'+dt+'" width="100%" height="550"></iframe>');
		$("#openBill").modal({backdrop:'static'});
	}
	//----------------------------------------------------
	orgDetail(e){
		let ID   = $(e.currentTarget).data('id');
		let dt   = $(e.currentTarget).data('date');
		let orgN = $(e.currentTarget).data('org');
		let mnth = $(e.currentTarget).data('month');
		let yr   = $(e.currentTarget).data('year');
		$("#openBill .modal-title").html("<b>"+orgN+"</b> "+mnth+" "+yr);
		$("#openBill .modal-body")
			.html('<iframe src="/billing/detail/'+ID+'/'+dt+'" width="100%" height="550"></iframe>');
		$("#openBill").modal({backdrop:'static'});
	}
	//----------------------------------------------------
	expandRow(index, data){
		$('#billingTableRoot').bootstrapTable('collapseRow',this.lastExpandRow);
		this.lastExpandRow = index;
		this.lastOrgID = data.id;
		let that = this;
		$("#billingTableRoot tbody tr.detail-view td")
			.html('<div style="width:100%; max-width:350px;"><table class="tblChild" id="billingTableRoot_'+index+'"></table></div>')
		$('#billingTableRoot_'+index).bootstrapTable({
			url: that.getChildURL,
			columns: that.getChildColumns,
			sidePagination: 'server',
			pagination: false,
			silentSort: false,
			cache: false,
			search: false,
			toolbar: "#tableToolbar"+index,
			pageSize: that.pageSize,
			pageNumber: that.pageNumber,
			sortName: that.pageSort,
			sortOrder: that.pageOrder,
			showRefresh: true,
			detailView: false,
			onlyInfoPagination: true,
			method: 'POST',
		});
	}
	//----------------------------------------------------
	createTable(id){
		let url = this.getURL;
		this.container = "#" + id;
		$(this.container).html('<table id="billingTableRoot"></table>');
		$(this.container).append(this.deleteDialog());
		$(this.container).append(this.actionForm());
		$(this.container).append(this.tableToolbar());
		
		this.table = "#" + id + " table";
		var DataTableConstant = this;
		$(this.table).bootstrapTable({
			url: this.getURL,
			columns: this.getColumns,
			sidePagination: 'server',
			pagination: this.hasPagination,
			silentSort: false,
			cache: false,
			search: false,//this.hasSearch,
			toolbar: "#tableToolbar",
			pageSize: this.pageSize,
			pageNumber: this.pageNumber,
			sortName: this.pageSort,
			sortOrder: this.pageOrder,
			showRefresh: this.showRefresh,
			queryParams: function(params){ DataTableConstant.queryParams(params, this); },
			responseHandler: (res) => this.responseHandler(res),
			detailView: true,
			onlyInfoPagination: true,
			method: 'POST',
		});
	}
	//----------------------------------------------------
	getOrganizations(){}
	get getURL(){ 
		return this.apiURL+'/'+ ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' + this.pageOrder + '/';
	}
	get searchURL(){
		return this.getURL;
	}
	get getChildURL(){ 
		return this.apiURL+'/details/'+this.lastOrgID+'/'+this.pageSort+'/'+this.pageOrder+'/';
	}
	//----------------------------------------------------
	get getChildColumns() {
		var columns = [];
		for(var x in this.childColumns.data){
			var column = this.childColumns.data[x];
			if(column.hidden === true && column.primary !== true) continue;
			var obj = {
				//        editable: true,
				field: column.name,
				title: column.display,
				sortable: (column.sortable)? true:false,
				visible: (column.hidden === true)? false:true,
//				formatter: (value, row, index, field) => this.cellRenderer(value, row, index, field)
			}
			if(column.width !== null) obj.width = column.width;
			columns.push(obj);
		}
		if(this.hasRowActions) {
			columns.push({
				field: 'actions',
//				formatter: (value, row, index, field) => this.rowActions(value, row, index, field)
			})
		}
		return columns;
	}
	//----------------------------------------------------
}
//--------------------------------------------------------

//--------------------------------------------------------
var columnsRoot = [
	{ name: 'id', display:'ID', primary:true, sortable:false, search:false, editable:false, width:'80px', class:'right' },

	{ name:'organizationShortName', display:'Organization Name' , hidden:false, editable:false , sortable:false, search:false },
	{ name:'totalLogRow'          , display:'Total Logs'        , hidden:false, editable:false , sortable:false, search:false },
];
var columnsChild = [
	{ name: 'year', display:'Year', primary:true, sortable:false, search:false, editable:false, width:'80px', class:'right' },

	{ name:'month', display:'Month' , hidden:false, editable:false , sortable:false, search:false },
	{ name:'totalLogRow'          , display:'Total Logs'        , hidden:false, editable:false , sortable:false, search:false },

	{ name:'bill'          , display:''        , hidden:false, editable:false , sortable:false, search:false },
];
//--------------------------------------------------------
var data = {
	columns: new Columns(columnsRoot),
	childColumns: new Columns(columnsChild),
	apiURL: apiURL + '/api/dashboard/billing'
}
//--------------------------------------------------------
if($("#billing").length != 0){
	billingClass = new Billing(data);
	billingClass.createTable('billing');
}
//--------------------------------------------------------

//--------------------------------------------------------
