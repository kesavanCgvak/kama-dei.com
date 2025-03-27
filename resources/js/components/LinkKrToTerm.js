import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class LinkKrToTerm extends DataTable {
	//----------------------------------------------------
	constructor(data){
		super(data);
		let that = this;
		this.showGlobal = true;
		this.pageSort = 'knowledgeRecord';

		this.ownerList();
		
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
		$('body').on('change', '#showGlobal', (e)=>{
			if( $("#showGlobal").prop('checked')==false){
				$("#linkkrtotermOwnersList").val(orgID).change();
			}
		});
		

		this.selectKRURL = apiURL+'/api/dashboard/relation/page/'+orgID+'/knowledgeRecordName/asc/10/1/ownerId/-1/showglobal/1';
		$('body').on('click', '#selectListKR .btn-close', (e) => { $("#selectListKR").modal('hide'); });
		$('body').on('click', '#selectListKR .btn-select', (e) => { 
			let id = $("#selectListKR #lorKRid").val().trim();
			$("#"+id+"_txt").val( $("#selectListKR .hedarTXT").text() );
			$("#"+id       ).val( $("#selectListKR #tmpKRid").val() ).change();
			$("#selectListKR").modal('hide'); 
		});
		$('body').on('change', '#shwglblKR', (e)=>{
			$("#selectListKRTBL").bootstrapTable("refresh");
		});
		$('body').on('change', '#ownerslctLstKR', (e)=>{
			$("#selectListKRTBL").bootstrapTable("refresh");
		});
		$("#ownerslctLstKR").ready(function(){
			$("#ownerslctLstKR option").remove();
			$("#ownerslctLstKR").append('<option value="-1" selected="selected">All</option>');
			$.get(apiURL+'/api/dashboard/relation/relationowners/'+orgID, function(ret){
				if(ret.result==0){
					for(let i in ret.data){
						let val = ret.data[i].id;
						let txt = ret.data[i].text;
						$("#ownerslctLstKR").append('<option value="'+val+'">'+txt+'</option>');
					}
				}
			})
		});
		
		
		
		this.selectTRURL = apiURL+'/api/dashboard/term/page/'+orgID+'/termName/asc/10/1/ownerId/-1/showglobal/1';
		$('body').on('click', '#selectListTR .btn-close', (e) => { $("#selectListTR").modal('hide'); });
		$('body').on('click', '#selectListTR .btn-select', (e) => { 
			let id = $("#selectListTR #lorTRid").val().trim();
			$("#"+id+"_txt").val( $("#selectListTR .hedarTXT").text() );
			$("#"+id       ).val( $("#selectListTR #tmpTRid").val() ).change();
			$("#selectListTR").modal('hide'); 
		});
		$('body').on('change', '#shwglblTR', (e)=>{
			$("#selectListTRTBL").bootstrapTable("refresh");
		});
		$('body').on('change', '#ownerslctLstTR', (e)=>{
			$("#selectListTRTBL").bootstrapTable("refresh");
		});
		$("#ownerslctLstTR").ready(function(){
			$("#ownerslctLstTR option").remove();
			$("#ownerslctLstTR").append('<option value="-1" selected="selected">All</option>');
			$.get(apiURL+'/api/dashboard/term/termowners/'+orgID, function(ret){
				if(ret.result==0){
					for(let i in ret.data){
						let val = ret.data[i].id;
						let txt = ret.data[i].text;
						$("#ownerslctLstTR").append('<option value="'+val+'">'+txt+'</option>');
					}
				}
			})
		});


		$('.pull-right').ready(function(){
			$('#link_kr_to_term .pull-right.search').prepend($("#linkkrtotermOwnersList"));
			$('#link_kr_to_term .pull-right.search .form-control').css('display', 'inline-block');
			$('#link_kr_to_term .pull-right.search .form-control').css('width', '49%');
			$('#link_kr_to_term .pull-right.search select').css('margin-right', '1.5%');
		});
		$('body').on('change', '#linkkrtotermOwnersList', (e)=>{
			$(that.table).bootstrapTable('selectPage', 1);
		});
		
		$("#krtermLinkId").ready(function(){
			$("#krtermLinkId option").remove();
			$("#krtermLinkId").append('<option value="">Select . . .</option>');
			$.get(apiURL+'/api/dashboard/linkkrtoterm/get_termlink/'+orgID, function(ret){
				if(ret.result==0){
					for(let i in ret.data){
						let val = ret.data[i].termId;
						let txt = ret.data[i].termName;
						$("#krtermLinkId").append('<option value="'+val+'">'+txt+'</option>');
					}
				}
			})
		});
	}
	//----------------------------------------------------
	get getURL() {
		let ownerId = $("#linkkrtotermOwnersList").val();
		let showGlobal = 1;
		if( $("#showGlobal").prop('checked')==false){ showGlobal=0; }
		return this.apiURL + 
			'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' + this.pageNumber +
			'/' + ownerId +
			'/' + showGlobal +
			'/' + defaultTrID +
			'/' + defaultKbID;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	get searchURL() {
		let ownerId = $("#linkkrtotermOwnersList").val();
		let showGlobal = 1;
		if( $("#showGlobal").prop('checked')==false){ showGlobal=0; }
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber +

			'/' + ownerId +
			'/' + showGlobal +
			'/' + defaultTrID +
			'/' + defaultKbID +
			
			'/' + this.columns.searchColumn +
			'/' + this.search;
	}
	//--------------------------------------------------------------

	//----------------------------------------------------
	ownerList(){
		$("#linkkrtotermOwnersList option").remove();
		$("#linkkrtotermOwnersList").append('<option value="-1" selected="selected">Owner All</option>');
		$.get(apiURL+'/api/dashboard/linkkrtoterm/owners/'+orgID, function(ret){
			if(ret.result==1){ showError(ret.msg); }
			else{
				for(let i in ret.data){
					let val = ret.data[i].id;
					let txt = ret.data[i].name;
					$("#linkkrtotermOwnersList").append('<option value="'+val+'" >'+txt+'</option>');
				}
			}
		});
		$("#linkkrtotermOwnersList").show();
	}
	//----------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();

		$("#relationId").val(0);
		$("#relationId_txt").val("");

		$("#termId"    ).val(0);
		$("#termId_txt").val("");

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
		
		if(defaultTrID!=0){
			$(".col-termId button").prop("disabled", true);
			$("#termId").val(defaultTrID).change();
			$("#termId_txt").val(defaultTrTX);
		}
		if(defaultKbID!=0){
			$(".col-relationId button").prop("disabled", true);
			$("#relationId").val(defaultKbID).change();
			$("#relationId_txt").val(defaultKbTX);
		}
	}
	//----------------------------------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);
		if(this.baseItem==null){ this.baseItem={}; for(let i in this.editItem){ this.baseItem[i]=this.editItem[i]; } }
		
		$("#relationId_txt").val(this.editItem.knowledgeRecord);
		$("#termId_txt"    ).val(this.editItem.termName       );

		if(this.editItem.ownerId==0 || this.editItem.ownerId==null){
			$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
		}
		if(defaultTrID!=0){
			$(".col-termId button").prop("disabled", true);
		}
		if(defaultKbID!=0){
			$(".col-relationId button").prop("disabled", true);
		}
	}
	//----------------------------------------------------------------------------
	tmpFormatter(value, row, index, field){
		if(field=='reserved' ){ return table.checkCell    (value, row, field); }
		if(field=='ownership'){ return table.ownershipCell(value, row, field); }
		if(field=='organizationShortName'){
			if(row.ownerId!=null && row.ownerId!=0){ return row.organization.organizationShortName; }
			else{ return BASE_ORGANIZATION; }
		}
		if(field=='relationIsReserved'){ return table.checkCell(value, row, field); }
		if(field=='termIsReserved'    ){ return table.checkCell(value, row, field); }
	}
	//----------------------------------------------------------------------------
	knowledgeRecordModal(){
		//------------------------------------------------------------------------
		var div = ''+
			'<div class="modal fade" tabindex="-1" role="dialog" id="selectListKR" aria-hidden="true">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header" style="border-radius:5px 0">'+
							'<div style="height:50px;">'+
								'<label>'+
									'Knowledge Record'+
								'</label>'+
								'<span class="hedarTXT" style="margin-left:8px; font-size:small;"></span>'+
							'</div>'+
							'<div>'+
								'<div class="input-group m-b-1" style="margin:5px 0 15px;">'+
									'<span class="input-group-addon">Owner</span>'+
									'<select class="form-control" id="ownerslctLstKR">'+
										'<option value="-1">All</option>'+
									'</select>'+
								'</div>'+
							'</div>'+
							'<input type="hidden" id="tmpKRid" value="0"/>'+
							'<input type="hidden" id="lorKRid" value="0"/>'+
						'</div>'+
						'<div class="modal-body">'+
							'<div style="width:100%;text-align:left;">'+
								'<button type="button" class="btn btn-danger btn-close" style="width:40%;" >Cancel</button>'+
								'<button type="button" class="btn btn-info btn-select" style="width:40%;float:right" >Select</button>'+
							'</div>'+
						'</div>'+
						'<div class="modal-footer">'+
							'<div style="float: left; margin-top: 10px;">'+
								'<label>'+
									'Show global '+
									'<input type="checkbox" data-toggle="toggle" data-on="Yes" data-off="No" id="shwglblKR" checked/>'+
								'</label>'+
							'</div>'+
							'<table id="selectListKRTBL">'+
							'</table>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('body').append(div);
		//------------------------------------------------------------------------
	}
	//----------------------------------------------------------------------------
	termModal(){
		//------------------------------------------------------------------------
		var div = ''+
			'<div class="modal fade" tabindex="-1" role="dialog" id="selectListTR" aria-hidden="true">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header" style="border-radius:5px 0">'+
							'<div style="height:50px;">'+
								'<label>'+
									'Term'+
								'</label>'+
								'<span class="hedarTXT" style="margin-left:8px; font-size:small;"></span>'+
							'</div>'+
							'<div>'+
								'<div class="input-group m-b-1" style="margin:5px 0 15px;">'+
									'<span class="input-group-addon">Owner</span>'+
									'<select class="form-control" id="ownerslctLstTR">'+
										'<option value="-1">All</option>'+
									'</select>'+
								'</div>'+
							'</div>'+
							'<input type="hidden" id="tmpTRid" value="0"/>'+
							'<input type="hidden" id="lorTRid" value="0"/>'+
						'</div>'+
						'<div class="modal-body">'+
							'<div style="width:100%;text-align:left;">'+
								'<button type="button" class="btn btn-danger btn-close" style="width:40%;" >Cancel</button>'+
								'<button type="button" class="btn btn-info btn-select" style="width:40%;float:right" >Select</button>'+
							'</div>'+
						'</div>'+
						'<div class="modal-footer">'+
							'<div style="float: left; margin-top: 10px;">'+
								'<label>'+
									'Show global '+
									'<input type="checkbox" data-toggle="toggle" data-on="Yes" data-off="No" id="shwglblTR" checked/>'+
								'</label>'+
							'</div>'+
							'<table id="selectListTRTBL">'+
							'</table>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('body').append(div);
		//------------------------------------------------------------------------
	}
	//----------------------------------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch(col){
			case 'relationId':
				input = 
					$("<div>")
						.attr({ class: "col-" + col + " form-group" })
						.append( $("<label>"+label+"</label>") )
						.append( $("<div>")
								.append( $("<input>").attr({
														id:col+"_txt", 
														class:'form-control', 
														style:"max-width:92%; display:inline-block;",
														disabled:"",
														value:""
								}) ) 
								.append( 
										$("<button>").attr({
														id:col+"_btn", 
														class:'btn btn-default', 
														type:"button",
														onclick:"openselectListKR('"+label+"', '"+col+"')",
														style:"height:34px; vertical-align:top; float:right;padding:5px 8px"
											})
											.append("<i class='fa fa-list' style='font-size:20px;'></i>")
									   )
								.append( $("<input>").attr({id:col, name:col, type:"hidden"}) )
						);
			break;
			case 'termId':
				input = 
					$("<div>")
						.attr({ class: "col-" + col + " form-group" })
						.append( $("<label>"+label+"</label>") )
						.append( $("<div>")
								.append( $("<input>").attr({
														id:col+"_txt", 
														class:'form-control', 
														style:"max-width:92%; display:inline-block;",
														disabled:"",
														value:""
								}) ) 
								.append( 
										$("<button>").attr({
														id:col+"_btn", 
														class:'btn btn-default', 
														type:"button",
														onclick:"openselectListTR('"+label+"', '"+col+"')",
														style:"height:34px; vertical-align:top; float:right;padding:5px 8px"
											})
											.append("<i class='fa fa-list' style='font-size:20px;'></i>")
									   )
								.append( $("<input>").attr({id:col, name:col, type:"hidden"}) )
						);
			break;
			case 'krtermLinkId':
				input = 
					$("<div>")
						.attr({ class: "col-" + col + " form-group" })
						.append( $("<label>"+label+"</label>") )
						.append( $("<div>").append( $("<select>").attr({ id: col, name: col, class: 'form-control' }) ) );
			break;
			default:
				input = super.getActionFormInput(col, label);
			}
		return input;
	}
	//----------------------------------------------------------------------------
	addConfirmHandler(e){
		super.addConfirmHandler(e);
		this.ownerList();
	}

}
//--------------------------------------------------------------------------------

//--------------------------------------------------------------------------------
var columns = [
	{ name: 'relationTermLinkId', display: '', primary: true, hidden: true, search:false, editable: false, sortable: false },
	{ name: 'lastUserId', hidden: true, editable: false, default: '1'},
	
	{ name: 'relationId'      , display: 'Knowledge Record', sortable: false, search: false, editable: true  , hidden: true  },
	{ name: 'knowledgeRecord' , display: 'Knowledge Record', sortable: true , search: true , editable: false , hidden: false },

	{ name: 'krtermLinkId', display: 'Link Type', sortable: false, search: false, editable: true  , hidden: true  },
	{ name: 'linkTypeName', display: 'Link Type', sortable: true , search: true , editable: false , hidden: false },
	
	{ name: 'termId'  , display: 'Term', sortable: false, search: false, editable: true  , hidden: true  },
	{ name: 'termName', display: 'Term', sortable: true , search: true , editable: false , hidden: false },

	{ name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
	
	{ name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true, ownerId: true },
	{ name: 'orgName', display: 'Owner', onlyFor: 0, sortable: true,  editable: false },

	{ name:'reserved' , display:'Reserved' , sortable:true, reserved:true },
	
	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true }
];
var linkKrToTermColumns = new Columns(columns);

var data = {
	columns: linkKrToTermColumns,
	apiURL: apiURL + '/api/dashboard/linkkrtoterm'
}
//--------------------------------------------------------------------------------

//--------------------------------------------------------------------------------
if($("#link_kr_to_term").length != 0){
	var table = new LinkKrToTerm(data);
	table.createTable('link_kr_to_term');
	//----------------------------------------------------------------------------
	table.knowledgeRecordModal();
	$("#selectListKRTBL").bootstrapTable({
		columns: [
			{sortable:true, searchable:true , title:'Knowledge Record', field:'knowledgeRecordName', width:'51%', align:'left   !important' },
			{sortable:true, searchable:false, title:'Ownership', field:'ownership'            , width:'17%', align:'left !important', formatter:table.tmpFormatter },
			{sortable:true, searchable:false, title:'Owner'    , field:'organizationShortName', width:'31%', align:'left !important', formatter:table.tmpFormatter },
			{sortable:false, searchable:false, title:'Reserved', field:'relationIsReserved', width:'31%', align:'center !important', formatter:table.tmpFormatter },
			

			{sortable:false, searchable:false , title:'relationId' , field:'relationId', width:'0', visible:false },
		],
		url         : table.selectKRURL,
		showRefresh : true,
		search      : true,
		pagination  : true,
		sidePagination: 'server',
		dataField: 'data',
		sortName: 'knowledgeRecordName',
		sortOrder: 'asc',
		rowStyle: function(row, index){ 
			if(row.relationId==$("#tmpKRid").val()){ return { css:{ color:'red' } }; }
			return { css:{ color:'#000' } };
		},
		queryParams:function(params){
			//----------------------------------------------------------
			let page = params.offset/params.limit;
			let shwglblKR = (($('#shwglblKR').prop('checked')) ?1 :0);
			let ownerId = $('#ownerslctLstKR').val();
			page++;
			if(params.search == '' || params.search == null || typeof(params.search) == 'undefined'){
				table.selectKRURL = apiURL+
										'/api/dashboard/relation/page'+
										'/'+orgID+
										'/'+params.sort+
										'/'+params.order+
										'/'+params.limit+
										'/'+page+
										'/ownerId/'+ownerId+
										'/showglobal/'+shwglblKR;
			}else{
				table.selectKRURL = apiURL+
										'/api/dashboard/relation'+
										'/'+orgID+
										'/'+params.sort+
										'/'+params.order+
										'/'+params.limit+
										'/'+page+
										'/allFields'+
										'/'+params.search+
										'/ownerId/'+ownerId+
										'/showglobal/'+shwglblKR;
			}
			this.url = table.selectKRURL;
			//----------------------------------------------------------
			return params;
			//----------------------------------------------------------
		}
	})
	.on('load-success.bs.table', function(xhr, data){
	})
	.on('refresh.bs.table', function(params){
		$("#selectListKRTBL").bootstrapTable("showLoading");
	})
	.on('click-row.bs.table', function(e, row, a, b){
		$("#selectListKR .hedarTXT").text(row.knowledgeRecordName);
		$("#selectListKR #tmpKRid").val(row.relationId);
		$('#selectListKRTBL td').css('color', '#000');
		$(a[0]).find('td').css('color', 'red');
	})
	.on('dbl-click-row.bs.table', function(e, row, a, b){
		$("#selectListKR .hedarTXT").text(row.knowledgeRecordName);
		$("#selectListKR #tmpKRid").val(row.relationId);	
		$('#selectListKRTBL td').css('color', '#000');
		$(a[0]).find('td').css('color', 'red');
		
		$("#selectListKR .btn-select").click();
	});
	$("#selectListKR .columns.columns-right.btn-group.pull-right")
								.prepend( 
										$("<button>").attr({
														class:'btn btn-info', 
														type:"button",
														onclick:"$('#selectListKRTBL').bootstrapTable('resetSearch', '')",
														style:"height:34px;padding:2px 5px;"
											})
											.append("clear search")
									   );
	//----------------------------------------------------------------------------
	table.termModal();
	$("#selectListTRTBL").bootstrapTable({
		columns: [
			{sortable:true, searchable:true , title:'Term'     , field:'termName' , width:'51%', align:'left !important' },
			{sortable:true, searchable:false, title:'Ownership', field:'ownership', width:'17%', align:'left !important', formatter:table.tmpFormatter },
			{sortable:true, searchable:false, title:'Owner'    , field:'organizationShortName', width:'31%', align:'left !important', formatter:table.tmpFormatter },
			{sortable:false, searchable:false, title:'Reserved', field:'termIsReserved', width:'31%', align:'center !important', formatter:table.tmpFormatter },
			

			{sortable:false, searchable:false , title:'termId' , field:'termId', width:'0', visible:false },
		],
		url         : table.selectKRURL,
		showRefresh : true,
		search      : true,
		pagination  : true,
		sidePagination: 'server',
		dataField: 'data',
		sortName: 'termName',
		sortOrder: 'asc',
		rowStyle: function(row, index){ 
			if(row.termId==$("#tmpTRid").val()){ return { css:{ color:'red' } }; }
			return { css:{ color:'#000' } };
		},
		queryParams:function(params){
			//----------------------------------------------------------
			let page = params.offset/params.limit;
			let shwglblTR = (($('#shwglblTR').prop('checked')) ?1 :0);
			let ownerId = $('#ownerslctLstTR').val();
			page++;
			if(params.search == '' || params.search == null || typeof(params.search) == 'undefined'){
				table.selectTRURL = apiURL+
										'/api/dashboard/term/page'+
										'/'+orgID+
										'/'+params.sort+
										'/'+params.order+
										'/'+params.limit+
										'/'+page+
										'/ownerId/'+ownerId+
										'/showglobal/'+shwglblTR;
			}else{
				table.selectTRURL = apiURL+
										'/api/dashboard/term'+
										'/'+orgID+
										'/'+params.sort+
										'/'+params.order+
										'/'+params.limit+
										'/'+page+
										'/termName'+
										'/'+params.search+
										'/ownerId/'+ownerId+
										'/showglobal/'+shwglblTR;
			}
			this.url = table.selectTRURL;
			//----------------------------------------------------------
			return params;
			//----------------------------------------------------------
		}
	})
	.on('load-success.bs.table', function(xhr, data){
	})
	.on('refresh.bs.table', function(params){
		$("#selectListTRTBL").bootstrapTable("showLoading");
	})
	.on('click-row.bs.table', function(e, row, a, b){
		$("#selectListTR .hedarTXT").text(row.termName);
		$("#selectListTR #tmpTRid").val(row.termId);
		$('#selectListTRTBL td').css('color', '#000');
		$(a[0]).find('td').css('color', 'red');
	})
	.on('dbl-click-row.bs.table', function(e, row, a, b){
		$("#selectListTR .hedarTXT").text(row.termName);
		$("#selectListTR #tmpTRid").val(row.termId);	
		$('#selectListTRTBL td').css('color', '#000');
		$(a[0]).find('td').css('color', 'red');
		
		$("#selectListTR .btn-select").click();
	});
	$("#selectListTR .columns.columns-right.btn-group.pull-right")
								.prepend( 
										$("<button>").attr({
														class:'btn btn-info', 
														type:"button",
														onclick:"$('#selectListTRTBL').bootstrapTable('resetSearch', '')",
														style:"height:34px;padding:2px 5px;"
											})
											.append("clear search")
									   );
	//----------------------------------------------------------------------------
}
