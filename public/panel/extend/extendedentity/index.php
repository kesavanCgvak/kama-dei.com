<?php
$userKey  = "-";
$userKeyR = \App\UserKey::find(session()->get('userID'));
if($userKeyR!==null){ $userKey = $userKeyR->userKey; }
?>
<style>
	#resultPreview [data-title]:after {
		content: attr(data-title);
		/*
		color: #111;
		background: #fff;
		padding: 1px 5px 2px 5px;
		bottom: 0.6em;
		left: 100%;
		*/
		white-space: nowrap;
		box-shadow: 1px 1px 3px #222222;
		opacity: 0;
		/*border: 1px solid #111111;*/
		z-index: 99999;
		visibility: hidden;
		position: absolute;
	}
	#resultPreview [data-title]:hover {
		/*position: absolute;*/
	}
	#resultPreview a{ position: relative; }
	#resultPreview [data-title]:hover::after{
		white-space: pre;
		z-index: 99999;
		padding: 8px 20px;
		background-color:lightblue;
		color: blue;
		border: 1px solid blue;
		border-radius: 8px;
		position: absolute;
		left: 0px;
		/*
		bottom: 12px;
		*/
		display: block;
		z-index: 99999;
/*
	}
	#resultPreview [data-title]:hover::after{
*/
		opacity: 1.0;
		transition: all 0.5s ease 0.5s;
		visibility: visible;
	}
    #searchWhere,.where-group{
        display: inline-block;
        line-height: 10px;
        margin: 0px 50px 0px 10px;
    }
    .where-group select{
        width: 176px;
        height: 34px;

        display: block;
        height: 34px;
        padding: 6px 14px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #000;
        background-color: #fff;
        background-image: none;
        border: 1px solid #cfd0d2;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        -webkit-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    }

    #searchWhere{
        float: right;
        margin-right: 186px;
    }
    .where-group label{
        display: none;
    }
    .pull-left{
        width: 100%;
    }
    .pull-right{
        top: -55px;
    }
    .fixed-table-container{
        top: -55px;
    }

#showError{
    position: fixed;
    top: 10px;
    left: 10px;
    color: #fff;
    background: #d25c5c;
    z-index: 9999;
    min-width: 150px;
    width: auto;
    padding: 8px 15px;
    border: 1px dotted #fff;
    border-radius: 8px;
    box-shadow: 0 0 0 3px #d25c5c;
	display:none;
}

#showError>i{ margin-right:5px; }
#showError>i:hover{ cursor:pointer;color:yellow; }

#editExtendedEntity, #addExtendedEntity {
	display: none;
	position: fixed;
	z-index: 1000;
	background: rgba(0, 0, 0, 0.6);
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	margin: auto;
}

#editExtendedEntity.show, #addExtendedEntity.show {
	display: block;
}

#editExtendedEntity > form, #addExtendedEntity > form {
	position: absolute;
	margin: auto;
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	width: 280px;
	height: 370px;
	width: fit-content;
	height: fit-content;
	background: white;
	padding: 15px;
}

#editExtendedEntity > form input, #addExtendedEntity > form input {
	width: 250px;
}

.react-bs-table-bordered, .react-bs-container-body {
	height: auto !important;
}

.row-actions {
	text-align: center;
}

.row-actions > a {
	padding-right: 10px;
}
#editEavItem {
        display: none;
        position: fixed;
        z-index: 1000;
        background: rgba(0, 0, 0, 0.6);
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
        margin: auto;
}
#editEavItem > form {
        position: absolute;
        margin: auto;
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
        width: 280px;
        width: fit-content;
        height: fit-content;
        background: white;
        padding: 15px;
        min-height: 470px;
        max-height: 90vh;
}
#editEavItem > form.action-form > .col-attributes.form-group{ max-height: 70vh; overflow: auto; min-height:370px; }

#extendedentity table th{ font-size:13px; }

#extendedentity table td:nth-child(1){ font-size:12px;text-align:right;vertical-align:middle;width:60px; }
#extendedentity table td:nth-child(2){ font-size:13px;text-align:left;vertical-align:middle; }
#extendedentity table td:nth-child(3){ font-size:12px;text-align:center;vertical-align:middle;width:100px; }
#extendedentity table td:nth-child(4){ font-size:12px;text-align:left;vertical-align:middle;width:150px; }
#extendedentity table td:nth-child(5){ font-size:12px;text-align:left;vertical-align:middle;width:130px; }
#extendedentity table td:nth-child(6){ font-size:12px;text-align:center;vertical-align:middle;width:100px; }
#extendedentity table td:nth-child(7){ font-size:12px;text-align:center;vertical-align:middle;width:100px; }
#extendedentity table td:nth-child(8){ font-size:12px;text-align:center;vertical-align:middle;width:100px; }
#extendedentity table td:nth-child(9){ font-size:12px;text-align:center;vertical-align:middle;width:40px; }

	#extendedentity textarea.iput_stringvalue{
		max-height:150px;min-height:150px;height:150px;
		max-width:100%;min-width:100%;
		margin-bottom:15px !important;
	}
	
    .reorder_rows_onDragClass td {
        background-color: #eee;
        -webkit-box-shadow: 11px 5px 12px 2px #333, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset;
        -webkit-box-shadow: 20px 3px 7px -4px #4dbfb5, 18px 3px 9px #35afa2, 19px -1px 14px 0px #3291e0;
        -moz-box-shadow: 6px 4px 5px 1px #555, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset;
        -box-shadow: 6px 4px 5px 1px #555, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset;
    }

    .reorder_rows_onDragClass td:last-child {
        -webkit-box-shadow: 8px 7px 12px 0 #333, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset;
        -webkit-box-shadow: 20px 3px 7px -4px #4dbfb5, 18px 3px 9px #35afa2, 19px -1px 14px 0px #3291e0;
        -moz-box-shadow: 0 9px 4px -4px #555, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset, -1px 0 0 #ccc inset;
        -box-shadow: 0 9px 4px -4px #555, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset, -1px 0 0 #ccc inset;
    }
	
	div.reviewed_by{ margin-bottom:20px; text-align: right; margin-right:10px; }
	div.reviewed_by>input,
	div.reviewed_by>label
		{ cursor: pointer; }
	/*
	div.reviewed_by>label
		{ vertical-align: middle; }
	*/
	.draftItemAll{
		width: calc(100% - 0px);
		margin-right: 5px;
	}
	.draftItemBorder{
		border: 0px solid #eee;
		padding: .5rem;
		padding-bottom: 0;
	}
	.draftItemSearch{
		width: 100%;
		/*padding: 2.5px 5px;*/
	}
	.draftItem{ margin-bottom: 15px; }
	.draftItem>label{ display: block; }
	
	#searchBTN{ height:33.6px; }
	#urlText{ width: calc(100% - 0px); display: inline-block; margin: auto; }
	#inquiry{
		height: 90px; max-height: 90px; min-height: 90px;
		width: 100%; min-width: 100%; max-width: 100%; 
	}
	#result{
		height: 154px; max-height: 154px; min-height: 154px;
		width: 100%; min-width: 100%; max-width: 100%; 
	}
	#notes{
		height: 120px; max-height: 120px; min-height: 120px;
		width: 100%; min-width: 100%; max-width: 100%; 
	}
	label.radioItems:not(.disabled):hover{ cursor: pointer; color: red; }
	label.radioItems.disabled{ color: #ccc; cursor: not-allowed; }
	
	label.certify{ display:inline-block; }
	label.certify:hover{ color: red; cursor: pointer; }
	
	.previewResult{
		float: right;
		margin-bottom: 5px;
		margin-top: -5px;
		padding: 2px 5px;
	}
	
	#showPrevBtn, #showEditBtn{ width:70px; padding:3px 5px; font-size: 90%; font-weight: 300; }
	#resultPreview>p{ height:154px; overflow: auto; padding:10px 5px 2px; border:1px solid #eee; border-radius:3px;text-align:justify; }
	#lang_entity_div button{ font-size: 80%; padding: 3px 6px; }
	#review_by{ 
		/*width:calc(100% - 180px) !important;*/
	}
	/*
	.fixed-table-pagination{ display: flex; }
	.fixed-table-pagination .pagination-detail{ width: 50%; text-align: left; max-width: 240px; display: inline-block; }
	.fixed-table-pagination .pagination{ width: 50%; text-align: right; min-width: calc(100% - 240px); display: inline-block; }
	*/
	.fixed-table-pagination .pagination{ margin-top: -25px !important; }
	#notesA{ width: 100%; max-width: 100%; min-width: 100%; height: 90px; min-height: 90px; max-height: 90px; }
	
	
	
	#notesModal .bootstrap-table{
		width: 100%;
		margin: -15px 0 0;
	}
	#notesModal .fixed-table-pagination{ font-size: 12px !important; margin: -50px 0 -10px 0; }
	#notesModal .fixed-table-pagination .btn.btn-default.dropdown-toggle{ font-size: 12px; }
	#notesModal .bootstrap-table th,
	#notesModal .bootstrap-table td{
		font-size: 12px !important;
		background: #fff;
		border: 1px solid #d7d7d7 !important;
	}
	#notesModal .bootstrap-table th{ background: #03A9F4; text-align: center; color: white; }
	#notesModal .bootstrap-table th:nth-child(1),
	#notesModal .bootstrap-table td:nth-child(1){ width: 150px; }
	#notesModal .bootstrap-table th:nth-child(2),
	#notesModal .bootstrap-table td:nth-child(2){ width: calc(100% - 200px); }
	#notesModal .bootstrap-table th:nth-child(3),
	#notesModal .bootstrap-table td:nth-child(3){ width: 50px !important; text-align: center; }
	#notesModal .bootstrap-table th:nth-child(4),
	#notesModal .bootstrap-table td:nth-child(4){ width: 0px !important; display: none; }
	#clear_review_by{ border-radius: 4px !important; }
	
	
	.draft_DS_LLM_CLL{
		display: flex;
		border: 1px solid #ccc;
		border-radius: 5px;
		height: 210px;
		margin-bottom: 5px;
	}
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">

<div id="extendedentity"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL    = "<?=env('API_URL');?>";
	var orgID     = "<?=$orgID;?>";
	var userID    = "<?=session()->get('userID');?>";
	var review_by = null;
	var draftSearchURL            = "<?=env('extendedentity_draft_search');?>";
	var draftSearchURL_enterprise = "<?=env('extendedentity_draft_enterprise_rag_chat');?>";
	
	var resultDraftRES     = '';
	var resultDraftURL     = '';
	var maxIputStringValue = 5000;
	var maxInquiryLen      = 250;
	var enterpriseOrgID    = '';
</script>

<link  href="/public/layui/css/layui.css" rel="stylesheet">
<!--采用模块化方式-->
<script  src="/public/layui/layui.js"></script>
<script  src="/public/js/jquery.js"></script>
<script  src="/public/js/app.js"></script>

<div id="draftModal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg" style="max-width:750px;">

		<div class="modal-content">
			<div class="modal-header" style="background:#03A9F4;">
				<h4 class="modal-title" style="color: #fff;">Draft Assistant</h4>
			</div>
			
			<div class="modal-body" style="display:flex; padding-bottom: 0">

				<div class="draftItemAll">
					<div class="draftItemBorder" style="margin-bottom:5px;">
				
						<div class="draftItem" style="display: flex">
							<!-- <div style="width: 69%; margin-right: 1%"> -->
							<div style="width:100%;">
								<label for="inquiry">Inquiry</label>
								<!--
								<input maxlength="40" id="inquiry" autofocus class="form-control"/>
								-->
								<textarea id="inquiry" autofocus class="form-control"></textarea>
								<small id="maxInquiryLen"></small>
							</div>
							
							<!-- <div style="width: 30%"> -->
							<div style="width:0%; display:none;">
								<label for="charMax">Character Max</label>
								<input maxlength="4" id="charMax" class="form-control" value="1000"/>
							</div>
						</div>

						<div class="draft_DS_LLM_CLL" style="">
							<div style="width: 20%; min-width: 100px; border-right:1px solid #cdcdcd;">
								<h4 style="width: 100%; border-bottom: 1px solid #cdcdcd; padding:10px"><b>Data Source</b></h4>
								<div class="draftItem" style="height:calc(100% - 75px); padding: 8px;">
									<div style="width: 100%;">
										<div>
											<input type="radio" id="internet" name="radioItems" value=""/>
											<label class="radioItems" for="internet">Internet</label>
										</div>
										<div>
											<input type="radio" id="url" name="radioItems"/>
											<label class="radioItems" for="url">URL</label>
										</div>
										<div>
											<input type="radio" id="enterprise" name="radioItems"/>
											<label class="radioItems" for="enterprise">Enterprise</label>
										</div>
										<div style="display: none">
											<input type="radio" id="kamaDEI" name="radioItems" disabled/>
											<label class="radioItems disabled" for="kamaDEI">Kama-DEI</label>
										</div>
	<!--
										<div>
											<input type="radio" id="sharepoint" name="radioItems" disabled/>
											<label class="radioItems disabled" for="sharepoint">SharePoint</label>
										</div>
	-->
									</div>
								</div>
							</div>
							<div style="width: 45%; min-width: 100px; border-right:1px solid #cdcdcd;">
								<h4 style="width: 100%; border-bottom: 1px solid #cdcdcd; padding: 10px"><b>LLM Models</b></h4>
								<div style="height:calc(100% - 35px);">
									<div style="width:100%; min-width: 100px;padding:8px; height: 100%;">
										<div class="enterpriseLLM radioItemsElemans" style="display:none;height:100%;overflow:auto;">
										</div>
									</div>
								</div>
							</div>
							<div style="width: 35%">
								<h4 style="width: 100%; border-bottom: 1px solid #cdcdcd; padding: 10px">
									<b id="collectionsTTL">&nbsp;</b>
								</h4>
								<div style="height:calc(100% - 35px);">
									<div style="width:100%; min-width: 100px;padding:8px; height:100%">
										<div class="enterprise radioItemsElemans" style="display:none;height:100%;overflow:auto;"></div>
										<div class="url radioItemsElemans" style="display:none">
											<input
												class="form-control"
												id="urlText"
												maxlength="300"
												placeholder="http://www.example.com/interestingpage"
											/>
										</div>
										
									</div>
								</div>
							</div>
						
						</div>
						<div style="width:100%; display:block; text-align:center;">
							<div class="draftItemSearch" style="">
								<button class="btn btn-info btnSearch" style="width:120px;" id="searchBTN">Search</button>
							</div>
						</div>

						<div class="draftItem" style="margin-bottom:5px">
<!--
							<div style="width: 100%; text-align: right; margin-bottom: -15px;">
								<input type="checkbox" id="prevEdit"
									   data-onstyle='info'
									   data-offstyle='info'
									   data-size='mini'
									   data-on='Edit'
									   data-off='Preview'
									   data-toggle='toggle'
									   data-width="100"
								/>
								<button id="prev"
							</div>
-->
							<div style="width: 100%; text-align: right; margin-bottom:-20px; display:none">
								<div class="btn-group" role="group" aria-label="Basic example">
									<button type="button" id="showEditBtn" class="btn btn-info">Code</button>
									<button type="button" id="showPrevBtn" class="btn btn-default">Preview</button>
								</div>								
							</div>								
							<div id="resultResult">
								<label for="result">Result</label>
								<textarea id="result" class="form-control"></textarea>
							</div>
							<div id="resultPreview">
								<label>Preview</label>
								<p></p>
							</div>
						</div>
					</div>
				
					<div class="draftItem" style="margin-bottom:5px; display: none">
						<label for="certify" class="certify">
							<input type="checkbox" id="certify" />
							I am responsible to ensure the authorized use of this information.
							<small style="font-weight: normal">(Add necessary notes below)</small>
						</label>
<!--
						<label for="certify" class="certify">
							I certify that I have checked the result and edited it.
							<small style="font-weight: normal">(Add necessary notes below)</small>
						</label>
-->
					</div>
					<div class="draftItem" style="margin-bottom: 0; display: none">
						<label for="notes">Notes</label>
						<textarea id="notes" class="form-control"></textarea>
					</div>
				</div>
				
			</div>

			<div class="modal-footer" style="display:flex; flex:1; flex-wrap:wrap; padding:5px 10px 8px; border-top:none;">
<!--
				<div style="width: 33%; text-align: left">
					<button style="width:80px;" type="button" id="copyDraft" class="btn btn-success">Copy</button>
				</div>
-->
				<div style="width: 50%; text-align: left">
					<button style="width:80px;" type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
				</div>
				<div style="width: 50%; text-align: right">
					<button style="width:80px;" type="button" id="saveDraft" class="btn btn-info">Apply</button>
				</div>
			</div>
		</div>

	</div>
</div>


<div id="notesModal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header" style="background:#03A9F4;">
				<h4 class="modal-title" style="color: #fff;">Notes</h4>
			</div>
			
			<div class="modal-body" style="padding:20px 8px;">
				<table
					style="width:100%"
					id="notesRecords"
					data-show-refresh=true
					data-toggle="table" 
					data-smart-display=true
					data-search=true
					data-data-field='data'
					data-url=''
					data-method='post'
					data-pagination=true
					data-page-list="[5, 10]"
					data-page-size="5"
<?php /*
					data-sort-name="created_on" 
					data-sort-order="asc"
					data-detail-view=true
					data-url='<?=env('API_URL');?>/api/dashboard/personality_relation_value/all/<?=$orgID;?>/-1/0'
					data-data-field='data'
					data-unique-id = "personalityRelationId"
*/ ?>
				>
					<thead>
						<th data-field='email'>User</th>
						<th data-field='note'>Note</th>
						<th data-field='created_on'>Date</th>
						<th data-field='id'></th>
					</thead>
				</table>
				
			</div>

			<div class="modal-footer" style="display: flex; flex: 1; flex-wrap: wrap; padding: 5px 10px 8px;">
				<div style="width: 100%; text-align: left">
					<button style="width:80px;" type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>

	</div>
</div>

<script type="application/javascript">
	$(function(){
		$("input[name='radioItems']").on("click", function(){
			let id = $(this).attr("id");
			$(".radioItemsElemans").hide();
			$("."+id+".radioItemsElemans").show();
			$("#collectionsTTL").html("&nbsp;");
		
			if(id=="url"){
				$("#collectionsTTL").text("URL");
				$("#urlText").focus();
			}

			if(
				id=="url" ||
				id=="internet" ||
				id=="enterprise"
			){
				$(".enterprise.radioItemsElemans>div").remove();
				$(".enterpriseLLM.radioItemsElemans>div").remove();
				
				$(".enterpriseLLM.radioItemsElemans").show();

				$.ajax({
					url: '<?=env('extendedentity_draft_get_system_llm_models', '')?>',
					method: "POST",
					headers:{
						apikey: "123"
					},
					//processData: false,
					//contentType: false,
					data: {userkey:"<?=$userKey;?>"},
					success: function(res){
						if(res.length==0){
							$(".enterpriseLLM.radioItemsElemans").append(
								'<div>'+
									'<b>You have no LLM Models set up yet.</b>'+
								'</div>'
							);
						}
						for(let i in res){
							let llmM = res[i];
							let llmModels = "<div>";
							llmModels += '<b style="display:block;margin:10px 0 4px 0;">'+i+'</b>';
							llmModels += '<ul style="padding-left:10px">';
							for(let j in llmM){
								let llm = llmM[j];
								llmModels += '<li>';
								llmModels += '<input type="radio" id="'+llm+'" name="LLM_Models" class="LLM_Models" value="'+i+'"/>';
								llmModels += '<label class="radioItems" for="'+llm+'" style="text-transform:capitalize;margin-left:5px">';
								llmModels += llm;
								llmModels += '</label>';
								llmModels += '</li>';
							}
							llmModels += '</ul>';
							llmModels += '</div>';
							$(".enterpriseLLM.radioItemsElemans").append( llmModels );
						}
					},
					error: function(xhr){
						let errText = "";
						try{ errText = "Error: "+xhr.state(); }
						catch(ex){}
						if(errText==""){ errText = xhr.statusText; }
						$(".enterpriseLLM.radioItemsElemans").append(
							'<div style="color:red">'+
								'<i>'+xhr.status+'</i>'+
								'<b style="margin-left:10px">'+errText+'</b>'+
							'</div>'
						);
					}
				});
				
				if(id!="enterprise"){ return; }
				$("#collectionsTTL").text("Collections");

				$.post(
					'<?=env('extendedentity_draft_list_collections', '')?>',
					{org: enterpriseOrgID, userkey:"<?=$userKey;?>"},
					function(res){
						if(res.length==0){
							$(".enterprise.radioItemsElemans").append(
								'<div>'+
									'<b>You have no collection set up yet.</b>'+
								'</div>'
							);
						}
						for(let i in res){
							let row = res[i];
							$(".enterprise.radioItemsElemans").append(
								'<div>'+
									'<input type="radio" id="'+row.collection_name+'" name="enterpriseCllctn" class="enterpriseCllctns"/>'+
									'<label class="radioItems" for="'+row.collection_name+'" style="text-transform:capitalize;margin-left:5px">'+
										row.collection_name+
									'</label>'+
								'</div>'
							);
						}
					}
				).fail(function(xhr){
					let errText = "";
					let errCode = "";
					
					try{ errText = "Error: "+xhr.responseJSON.detail.message; }catch(ex){}
					
					if(errText==""){
						try{ errText = "Error: "+xhr.state(); }catch(ex){}
						errCode = xhr.status;
					}
					if(errText==""){
						errText = xhr.statusText;
						errCode = xhr.status;
					}
					$(".enterprise.radioItemsElemans").append(
						'<div style="color:red">'+
							'<i>'+errCode+'</i>'+
							'<b style="margin-left:10px">'+errText+'</b>'+
						'</div>'
					);
				});
			}
		});
	});
	//----------------------------------------------
	function setShowTitle(){
		$(".asdLink").on({
			mouseenter: function (ev) {
/*
				let that = this;
				setTimeout(1500,function(){
					console.log("tooltip width:"+$(that).width());
				});
				console.log("tooltip outerWidth:"+$("#resultPreview [data-title]").outerWidth(true));
//				console.log("tooltip T:"+$("#resultPreview [data-title]").offsetWidth());
				
				console.log("tooltip right:"+$("#resultPreview [data-title]").position().right);
				console.log("div T:"+$("#resultPreview").width());
				console.log($(this).offset().top);
				console.log($("#resultPreview [data-title]").height(true));
				console.log($("#resultPreview [data-title]").outerHeight(true));
				console.log("------------------------");
				console.log("tooltip T:"+$("#resultPreview [data-title]").offset().top);
				console.log("tooltip B:"+$("#resultPreview [data-title]").offset().bottom);
				console.log("------------------------");
				console.log("div T:"+$("#resultPreview").offset().top);
				console.log("div B:"+$("#resultPreview").offset().bottom);
//#resultPreview [data-title]:hover::after				
				if($("#resultPreview [data-title]").offset().bottom>$("#resultPreview").offset().bottom){
					let top = $("#resultPreview [data-title]").offset().top + $("#resultPreview [data-title]").outerHeight()+5;
					$("#resultPreview [data-title]").css("bottom", top+"px");
						
				}
*/
			},
			mouseleave: function (event) {
			}
		});		
	}
</script>