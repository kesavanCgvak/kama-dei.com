import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class RelationLink extends DataTable {
	//----------------------------------------------------------------------------
	constructor(data){
		super(data);
		this.leftStaticKR = leftStaticKR;
		this.showGlobal = true;
		this.terms = [];
		this.relation = [];
		this.pageSort = 'linkOrder';
		this.tableReorderRow = tableReorderRow;
		
		this.selectKRURL = apiURL+'/api/dashboard/relation/page/'+orgID+'/knowledgeRecordName/asc'+'/10/1/ownerId/-1/showglobal/1';
	
		this.getAllTerms();
		$('body').on('click', '#linkLeftKR .btn-close', (e) => { $("#linkLeftKR").modal('hide'); });
		$('body').on('click', '#selectList .btn-close', (e) => { $("#selectList").modal('hide'); });
		$('body').on('click', '#selectList .btn-select', (e) => { 
			let id = $("#selectList #lorKRid").val().trim();
			$("#"+id+"_txt").val( $("#selectList .hedarTXT").text() );
			$("#"+id       ).val( $("#selectList #tmpKRid").val() ).change();
			$("#selectList").modal('hide'); 
		});
		//$('body').on('click', '.link-item', (e) => { this.showLeftKR(e) });
		//$('body').on('click', '.link-item2', (e) => { this.showLinkDialogHandler(e) });
		//var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'Relations', class: 'link-item', 'data-onlyowner': 1 });
		//var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'Ext. Data Link', class: 'link-item2', 'data-onlyowner': 1 });
		//this.actionIcons = this.actionIcons.concat([icon1, icon2]);
		if(tableReorderRow){
			let promote = $('<a></a>').attr({ href:'#', 'data-desc':'Promote', class:'promote', 'data-onlyowner':1 });
			let demote  = $('<a></a>').attr({ href:'#', 'data-desc':'Demote' , class:'demote' , 'data-onlyowner':1 });
			this.actionIcons = this.actionIcons.concat([promote, demote]);
			$('body').on('click', '.promote', (e) => { this.callPromote(e) });
			$('body').on('click', '.demote' , (e) => { this.callDemote(e)  });
		}
		
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
				if($("#relationLinkOwnersList").val()==-1){ $("#relationLinkOwnersList").val(orgID); }
			}
			$("#relationLinkOwnersList").change();
		});
		$('body').on('change', '#relationLinkOwnersList', (e)=>{
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
		
		$('body').on('change', '#shwglbl', (e)=>{
			$("#selectListTBL").bootstrapTable("refresh");
		});
		$('body').on('change', '#ownerslctLst', (e)=>{
			$("#selectListTBL").bootstrapTable("refresh");
		});
	}
	//----------------------------------------------------------------------------
	get getURL() { 
		return this.apiURL + 
				'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' + this.pageNumber +
				'/ownerId/' + $("#relationLinkOwnersList").val()+'/showglobal/'+this.showGlobalStatus;
	}
	get searchURL() {
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
			this.search+ '/ownerId/'+$("#relationLinkOwnersList").val()+'/showglobal/'+this.showGlobalStatus;

	}
	//----------------------------------------------------------------------------
	getAllTerms(){
		$.get(apiURL+'/api/dashboard/relation_link/term/'+orgID, (obj) => {
			var tmpOptions = [];
			for(var i=0; i< obj.data.length; i++){ tmpOptions.push("<option value='"+obj.data[i].termId+"'>"+obj.data[i].termName+"</option>"); }
			this.terms = tmpOptions;
			$("select#linkTermId").append(this.terms);
		});
	}
	//----------------------------------------------------------------------------
	callDemote(e){
		let itemID    = $(e.currentTarget).data('itemid');
		let rows      = [];
		let linkOrder = 0;

		for(let i=0; i<this.rows.length; i++){ this.rows[i].linkOrder=(i+1)+(this.pageSize*(this.pageNumber-1)); }
		for(let i=0; i<this.rows.length; i++){ if(this.rows[i].relationLinkId==itemID){ linkOrder=this.rows[i].linkOrder; } }

		//if(linkOrder<this.totalAllResponse){ linkOrder++; }
		if(linkOrder<(this.rows.length+(this.pageSize*(this.pageNumber-1)))){ linkOrder++; }
		for(let i=0; i<this.rows.length; i++){
			if(this.rows[i].relationLinkId==itemID)
				{ rows.push({relationLinkId:this.rows[i].relationLinkId, linkOrder:linkOrder}); }
			else{
				if(this.rows[i].linkOrder==linkOrder)
					{ rows.push({relationLinkId:this.rows[i].relationLinkId, linkOrder:linkOrder-1}); }
				else
					{ rows.push({relationLinkId:this.rows[i].relationLinkId, linkOrder:this.rows[i].linkOrder}); }
			}
		}

		this.callReorderPromoteDemote(rows);
	}
	callPromote(e){
		let itemID    = $(e.currentTarget).data('itemid');
		let rows      = [];
		let linkOrder = 0;
		
		for(let i=0; i<this.rows.length; i++){ this.rows[i].linkOrder=(i+1)+(this.pageSize*(this.pageNumber-1)); }
		for(let i=0; i<this.rows.length; i++){ if(this.rows[i].relationLinkId==itemID){ linkOrder=this.rows[i].linkOrder; } }


		if(linkOrder!=1+(this.pageSize*(this.pageNumber-1))){ linkOrder--; }
		for(let i=0; i<this.rows.length; i++){
			if(this.rows[i].relationLinkId==itemID)
				{ rows.push({relationLinkId:this.rows[i].relationLinkId, linkOrder:linkOrder}); }
			else{
				if(this.rows[i].linkOrder==linkOrder)
					{ rows.push({relationLinkId:this.rows[i].relationLinkId, linkOrder:linkOrder+1}); }
				else
					{ rows.push({relationLinkId:this.rows[i].relationLinkId, linkOrder:this.rows[i].linkOrder}); }
			}
		}

		this.callReorderPromoteDemote(rows);
	}
	callReorderPromoteDemote(rows){
		$.ajax({
			url: apiURL+'/api/dashboard/relation_link/reorder',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify({rows:rows}),
			beforeSend: function(){
				$('body')
					.append(
						$("<div>")
							.attr({
								id: "relationLinkReorderWaitting",
								style: "position:fixed;left:0;top:0;bottom:0;right:0;background:#0000000f;"
							})
							.append("<i class='fa fa-refresh fa-spin fa-5x' style='margin:20% 50%;font-size:150px'></i>")
					);
			},
			complete: function(){ $('body #relationLinkReorderWaitting').remove(); },
			success: function(res){
				if(res.result == 0){
					showSuccess(res.msg);
					$("#relationLink table").bootstrapTable('refresh', {});
				}else{
					showError(res.msg);
				}
			},
			error: function(e){
				showError('Server error');
			}
		});
	}
	//----------------------------------------------------------------------------
	dataTableOnReorderRowsDrag(table, row){ return false; }
	dataTableOnReorderRowsDrop(table, row){ return false; }
	
	dataTableOnReorderRow(newData){
		//this.table.reload();
		//$("#relationLink table").bootstrapTable('refresh');
		let rows = [];
		for(let i=0; i<newData.length; i++){
			rows.push({relationLinkId:newData[i].relationLinkId, linkOrder:(i+1)+(this.pageSize*(this.pageNumber-1))});
			newData[i].linkOrder=(i+1)+(this.pageSize*(this.pageNumber-1));
		}

		$.ajax({
			url: apiURL+'/api/dashboard/relation_link/reorder',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify({rows:rows}),
			beforeSend: function(){
				$('body')
					.append(
						$("<div>")
							.attr({
								id: "relationLinkReorderWaitting",
								style: "position:fixed;left:0;top:0;bottom:0;right:0;background:#0000000f;"
							})
							.append("<i class='fa fa-refresh fa-spin fa-5x' style='margin:20% 50%;font-size:150px'></i>")
					);
			},
			complete: function(){ $('body #relationLinkReorderWaitting').remove(); },
			success: function(res){
				if(res.result == 0){
					showSuccess(res.msg);
					for(let i=0; i<newData.length; i++){ $("#relationLink table").bootstrapTable('updateRow', {index:i, rpw:newData[i]}); }
				}else{
					showError(res.msg);
				}
			},
			error: function(e){
				showError('Server error');
			}
		});
		//$("#relationLink table").bootstrapTable('sortBy', {field:"linkOrder", sortOrder:'asc'});
	}
	//----------------------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		$("#leftRelationId").val(0);
		$("#rightRelationId").val(0);
		$("#leftRelationId_txt").val("");
		$("#rightRelationId_txt").val("");
		if(this.leftStaticKR!=''){
			this.editItem['leftRelationId' ] = this.leftStaticKR;
			$("#leftRelationId" ).val(this.leftStaticKR);
			$("#leftRelationId_txt" ).val(leftStaticKRTXT);
		}
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

		$(".col-linkOrder").hide();
	}
	//----------------------------------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);
		if(this.baseItem==null){ this.baseItem={}; for(let i in this.editItem){ this.baseItem[i]=this.editItem[i]; } }
		$("#leftRelationId_txt").val(this.editItem.leftKRName);
		$("#rightRelationId_txt").val(this.editItem.rightKRName);
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
		
		$(".col-linkOrder").hide();
	}
	//----------------------------------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch(col){
			case 'leftRelationId':
			case 'rightRelationId':
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
														onclick:"openSelectList('"+label+"', '"+col+"')",
														style:"height:34px; vertical-align:top; float:right;padding:5px 8px"
											})
											.append("<i class='fa fa-list' style='font-size:20px;'></i>")
									   )
								.append( $("<input>").attr({id:col, name:col, type:"hidden"}) )
						);
			break;
			case 'linkTermId':
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
	rowActions(value, row, index, field) {
		//------------------------------------------------------------------------
		var icons = this.actionIcons;
		//------------------------------------------------------------------------
		for (var i in icons){ if( icons[i].attr('class')=='link-item' ){ icons[i].attr('data-leftid', row.leftRelationId); } }
		//------------------------------------------------------------------------
		return super.rowActions(value, row, index, field);
		//------------------------------------------------------------------------
	}
	//----------------------------------------------------------------------------
	showLeftKR(e){
		e.preventDefault();
		var itemID = $(e.currentTarget).data('itemid');
		for( var i in this.rows ){ if(this.rows[i].relationLinkId==itemID){ $("#linkLeftKRTitle").html( this.rows[i].leftKRName ); break; } }
		var leftRelationId = $(e.currentTarget).data('leftid');
		$("#linkLeftKRTable").bootstrapTable('refresh',{url:apiURL+'/api/dashboard/relation_link/alllinkleft/'+orgID+'/'+leftRelationId});//{
		$("#linkLeftKR").modal({show:true, keyboard: false, backdrop:'static'});
	}
	creatLeftKR(){
		//------------------------------------------------------------------------
		var div = ''+
			'<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="linkLeftKR" id="linkLeftKR" aria-hidden="true">'+
				'<div class="modal-dialog modal-lg">'+
					'<div class="modal-content">'+
						'<div class="modal-header">'+
							'<label>Left KR</label> : <span id="linkLeftKRTitle"></span>'+
						'</div>'+
						'<div class="modal-body">'+
							'<table id="linkLeftKRTable">'+
							'</table>'+
						'</div>'+
						'<div class="modal-footer">'+
							'<button type="button" class="btn btn-danger btn-close" >Close</button>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('body').append(div);
		//----------------------------------------------------------
	}
	//----------------------------------------------------------------------------
	tmpFormatter(value, row, index, field){
		if(field=='reserved' ){return table.checkCell    (value, row, field); }
		if(field=='showcheck' ){value = ((value>0) ?true :false); return table.checkCell    (value, row, field); }
		if(field=='ownership'){ return table.ownershipCell(value, row, field); }
		if(field=='organizationShortName'){
			if(row.ownerId!=null && row.ownerId!=0){ return row.organization.organizationShortName; }
			else{ return BASE_ORGANIZATION; }
		}
		if(field=='relationIsReserved'){ return table.checkCell(value, row, field); }
	}
	//----------------------------------------------------------------------------
	showLinkDialogHandler(e){
		e.preventDefault();
		console.dir($(e.currentTarget).data('itemid'));
		window.location.href=this.apiURLBase + '/panel/extend/extendedlink/1/'+$(e.currentTarget).data('itemid');
	}
	//----------------------------------------------------------------------------
	creatSelectList(){
		//------------------------------------------------------------------------
		var div = ''+
			'<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="selectList" id="selectList" aria-hidden="true">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
//						'<div class="modal-header" style="background:#00a6b4;border-radius:5px 0">'+
						'<div class="modal-header" style="border-radius:5px 0">'+
//							'<div>Link Knowledge Records ( <small class="hedarLBL">Left KR</small> )</div>'+
							'<div style="height:50px;">'+
								'<label>'+
									'Link Knowledge Records ('+
									'<small class="hedarLBL" style="font-weight:100;">Left KR</small>'+
									'):'+
								'</label>'+
//								'<span class="hedarTXT" style="color:yellow;margin-left:20px;"></span>'+
								'<span class="hedarTXT" style="margin-left:8px; font-size:small;"></span>'+
							'</div>'+
							'<div>'+
								'<div class="input-group m-b-1" style="margin:5px 0 15px;">'+
									'<span class="input-group-addon">Owner</span>'+
									'<select class="form-control" id="ownerslctLst">'+
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
									'<input type="checkbox" data-toggle="toggle" data-on="Yes" data-off="No" id="shwglbl" checked/>'+
								'</label>'+
							'</div>'+
							'<table id="selectListTBL">'+
							'</table>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('body').append(div);
		//------------------------------------------------------------------------
	}
	//----------------------------------------------------------------------------
}
//--------------------------------------------------------------------------------

//--------------------------------------------------------------------------------
var columns = [
	{ name:'relationLinkId', display:'ID', primary:true, sortable:false, width:'8%', hidden:true },
	
	{ name:'leftRelationId', display:'Left KR', hidden:true },
	{ name:'leftKRName',     display:'Left KR', editable:false, sortable:false },
	
	{ name:'linkTermId', display:'Link Term', hidden:true },
	{ name:'termName',   display:'Link Term', editable:false, sortable:false },
	
	{ name:'rightRelationId', display:'Right KR', hidden:true },
	{ name:'rightKRName',     display:'Right KR', editable:false, sortable:false },
	
	{ name:'linkOrder', display:'Order'    , sortable:true, search:false, hidden:false },
	{ name:'reserved' , display:'Reserved' , sortable:false, reserved:true },
	{ name:'ownerId'  , display:'Owner'    , hidden:true, onlyFor:0 },
	{ name:'ownership', display:'Ownership', sortable:false, default:'2', ownership:true },
	
	{ name: 'organizationShortName', display:'Owner', sortable:false, onlyFor:0, editable:false },
	
	{ name:'extDataLink', display:'Ext. Data Link',sortable:false, editable:false, reserved:true },
	
	{ name:'dateCreated', display:'Created',   sortable:false, editable:false, date:true },
	{ name:'lastUserId',  hidden:true, default:'1', editable:false },
];
var relationLinkColumns = new Columns(columns);

var data = {
  columns: relationLinkColumns,
  apiURL: apiURL + '/api/dashboard/relation_link'
}

if($("#relationLink").length != 0){
	var ownerList = $('<div id="ownerList"/>');
	var table = new RelationLink(data);
	table.createTable('relationLink');
	$('#relationLink').prepend(ownerList);
	table.creatLeftKR();
	$("#linkLeftKRTable").bootstrapTable({
		columns: [
			{sortable:false, searchable:true , title:'Link Term', field:'termName'             , width:'22%', align:'left   !important' },
			{sortable:false, searchable:true , title:'Right KR' , field:'rightKRName'          , width:'22%', align:'left   !important' },
			{sortable:false, searchable:false, title:'Order'    , field:'linkOrder'            , width:'10%', align:'center !important' },
			{sortable:false, searchable:false, title:'Reserved' , field:'reserved'             , width:'10%', align:'center !important', formatter:table.tmpFormatter },
			{sortable:false, searchable:false, title:'Ownership', field:'ownership'            , width:'10%', align:'center !important', formatter:table.tmpFormatter },
			{sortable:false, searchable:false, title:'Owner'    , field:'organizationShortName', width:'16%', align:'left   !important', formatter:table.tmpFormatter },
			{sortable:false, searchable:false, title:'Created'  , field:'dateCreated'          , width:'10%', align:'center !important' },
		],
		url         : apiURL+'/api/dashboard/relation_link/alllinkleft/'+orgID+'/'+'0',//+"<?=env('API_URL');?>/api/dashboard/personality_relation_value/allValue/"+orgID+"/"+detail.personalityRelationId,
		showRefresh : true,
//		smartDisplay: true,
		search      : true,
		pagination  : true,
	});
	table.creatSelectList();
	$("#selectListTBL").bootstrapTable({
		columns: [
			{sortable:true, searchable:true , title:'Knowledge Record', field:'knowledgeRecordName', width:'51%', align:'left   !important' },
			{sortable:true, searchable:false, title:'Ownership', field:'ownership'            , width:'17%', align:'left !important', formatter:table.tmpFormatter },
			{sortable:true, searchable:false, title:'Owner'    , field:'organizationShortName', width:'31%', align:'left !important', formatter:table.tmpFormatter },
			{sortable:false, searchable:false, title:'Reserved', field:'relationIsReserved', width:'31%', align:'center !important', formatter:table.tmpFormatter },
			

			{sortable:false, searchable:false , title:'relationId' , field:'relationId', width:'0', visible:false },
		],
		url         : table.selectKRURL,
		showRefresh : true,
//		smartDisplay: true,
		search      : true,
		pagination  : true,
		sidePagination: 'server',
		dataField: 'data',
		sortName: 'knowledgeRecordName',
		sortOrder: 'asc',
		rowStyle: function(row, index){ 
			if(row.relationId==$("#tmpKRid").val()){ 
				return { css:{ color:'red' } };
			}
			return { css:{ color:'#000' } };
		},
		queryParams:function(params){
			//----------------------------------------------------------
			let page = params.offset/params.limit;
			let shwglbl = (($('#shwglbl').prop('checked')) ?1 :0);
			let ownerId = $('#ownerslctLst').val();
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
										'/showglobal/'+shwglbl;
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
										'/showglobal/'+shwglbl;
			}
			this.url = table.selectKRURL;
			//----------------------------------------------------------
			return params;
			//----------------------------------------------------------
		}
	})
	.on('click-row.bs.table', function(e, row, a, b){
		$("#selectList .hedarTXT").text(row.knowledgeRecordName);
		$("#selectList #tmpKRid").val(row.relationId);
		$('#selectListTBL td').css('color', '#000');
		$(a[0]).find('td').css('color', 'red');
	})
	.on('dbl-click-row.bs.table', function(e, row, a, b){
		$("#selectList .hedarTXT").text(row.knowledgeRecordName);
		$("#selectList #tmpKRid").val(row.relationId);
		$('#selectListTBL td').css('color', '#000');
		$(a[0]).find('td').css('color', 'red');
		
		$("#selectList .btn-select").click();
	});
	$("#selectList .columns.columns-right.btn-group.pull-right")
								.prepend( 
										$("<button>").attr({
														class:'btn btn-info', 
														type:"button",
														onclick:"$('#selectListTBL').bootstrapTable('resetSearch', '')",
														style:"height:34px;padding:2px 5px;"
											})
											.append("clear search")
									   );
}
