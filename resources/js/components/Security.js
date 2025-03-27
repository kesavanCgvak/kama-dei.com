import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
import "./css/bootstrap-table.1-21-4.min.css"
//import "bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js"
//import "bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.css"

class Security extends DataTable {
	//--------------------------------------------------------------
	constructor(data){
		super(data);

		this.levels = [];
		this.levelsLoaded = false;

		this.hasSearch = false;
		this.hasInsertRow = false;
		this.hasPagination = false;
		this.hasRowActions = false;

		this.pageSort = 'pageID';

		$('body').on('click', '.active-level', (e) => this.deletePageLevel(e));
		$('body').on('click', '.inactive-level', (e) => this.createPageLevel(e));

		this.getAllLevels();
	}
	//--------------------------------------------------------------
	get getURL(){ return this.apiURL+'/page/'+ this.orgID + '/pageID/asc/0/0'; }
	get searchURL(){ return this.apiURL+'/page/'+ this.orgID + '/pageID/asc/0/0'; }
	//--------------------------------------------------------------
	createTable(id){
		this.container = "#" + id;
		$(this.container).html('<table></table>');
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
			search: this.hasSearch,
			toolbar: "#tableToolbar",
			pageSize: this.pageSize,
			pageNumber: this.pageNumber,
			sortName: this.pageSort,
			sortOrder: this.pageOrder,
			showRefresh: this.showRefresh,
			queryParams: function(params){ DataTableConstant.queryParams(params, this); },
			responseHandler: (res) => this.responseHandler(res),
			
      		//stickyHeader: true,
			//virtualScroll: true,
			height:$("body>.container>.row").height()-($("body>.container>.row>.content>.content-header").height()*2),
			//stickyHeaderOffsetLeft: parseInt($('body').css('padding-left'), 10),
			//stickyHeaderOffsetRight: parseInt($('body').css('padding-right'), 10),
			//theadClasses: 'thead-light'
		});
	}
	//--------------------------------------------------------------
	get getColumns(){
		var columns = super.getColumns;
		for (var x in this.levels){
			let extData = {
				roleID   : this.levels[x].id,
				roleOrder: this.levels[x].order,
				x:x
			};

		let obj = {
			filed: 'levelCol',
			title: this.levels[x].levelName,
			formatter: (value, row, index, field) => this.levelCellRenderer(value, row, index, field, extData)
		}

		columns.push(obj);
	}

	return columns;
	}
	//--------------------------------------------------------------
	getAllLevels() {
		var url = apiURL+'/api/dashboard/level/list/' + this.orgID;
		$.get(url, (res) => {
			this.levels = res.data;
			$(this.table).bootstrapTable('refreshOptions', {columns: this.getColumns});
		});
	}
	//--------------------------------------------------------------
	levelCellRenderer(value, row, index, field, extraData){
		var isActives = row.levelCol;
		var rolID = extraData.roleID;
		var isActive = isActives[extraData.x];

		if(rolID==1){//admin role
			if(row.isAdmin){
				if(row.orgID==0){ return this.showFixedCell(); }
				else{
					if(this.orgID==row.orgID || this.orgID==0){ return this.showFixedCell(); }
					else{ return this.showBlankCell(); }
				}
			}else{
				if(row.orgID==0){ return this.showFixedCell(); }
				else{
					if(this.orgID==row.orgID || this.orgID==0){ return this.showFixedCell(); }
					else{ return this.showBlankCell(); }
				}
			}
		}else{//other roles
			if(row.isAdmin){ return this.showBlankCell();	}
			else{
				if(row.isGeneral==1){ return this.showFixedCell(); }
				else{
					if(row.orgID==0){ return this.showCellAction(isActive, row.id, rolID); }
					else{
						if(this.orgID==row.orgID || this.orgID==0){ return this.showCellAction(isActive, row.id, rolID); }
						else{ return this.showBlankCell(); }
					}
				}
			}
		}

		return value;
	}
	//--------------------------------------------------------------
	showBlankCell(){ return "<span class='fa fa fa-square-o' style='color:lightgray'></span>"; }
	//--------------------------------------------------------------
	showFixedCell(){ return "<span class='fa fa-check' style='color:lightgreen'></span>"; }
	//--------------------------------------------------------------
	showCellAction(isActive, pageID, rolID){
		var delURL = apiURL+"/api/dashboard/pages/delete/" + this.orgID + '/' + pageID + '/'+rolID;
		var addURL = apiURL+"/api/dashboard/pages/create/" + this.orgID + '/' + pageID + '/'+rolID;
		if(isActive==1 ){ return "<a href='"+delURL+"' class='active-level green' ><i class='fa fa-check'></i></a>"; }
		else{ return "<a href='"+addURL+"' class='inactive-level gray' ><i class='fa fa-square-o'></i></a>"; }
	}
	//--------------------------------------------------------------
	deletePageLevel(e){
		let curItem = null;
		e.preventDefault();
		var target = e.currentTarget;
		$.ajax({
			url: target.href,
			type: 'delete',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			beforeSend: function(){
				curItem = $(target).find('i').attr('class');
				$(target).find('i').attr('class','fa fa-spinner fa-spin');
			},
			error: function(){ $(target).find('i').attr('class',curItem); },
			success: (res) => {
				$(this.table).bootstrapTable('refresh', {silent: true});
			}
		});
	}
	//--------------------------------------------------------------
	createPageLevel(e){
		let curItem = null;
		e.preventDefault();
		var target = e.currentTarget;
		$.ajax({
			url: target.href,
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			beforeSend: function(){
				curItem = $(target).find('i').attr('class');
				$(target).find('i').attr('class','fa fa-spinner fa-spin');
			},
			error: function(){ $(target).find('i').attr('class',curItem); },
			success: (res) => {
				$(this.table).bootstrapTable('refresh', {silent: true});
			}
		});
	}
	//--------------------------------------------------------------
}

var columns = [
  { name: 'id', display: 'ID', primary: true, hidden: true },
  { name: 'pageCaption', display: 'Page' },
  { name: 'levelCol', hidden: true }
];
var securityColumns = new Columns(columns);

var data = {
  columns: securityColumns,
  apiURL: apiURL + '/api/dashboard/security'
}

if($("#security").length != 0){
  var table = new Security(data);
  table.createTable('security');
}
