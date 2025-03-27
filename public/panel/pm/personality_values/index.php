<style>
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
.react-bs-table-bordered, .react-bs-container-body {
	height: auto !important;
}

.row-actions {
	text-align: center;
}

.row-actions > a:first-child {
	padding-right: 10px;
}
#editItem #scalarValue{ display:none; }
#custom-handle,
.ui-slider-handle {
	margin-top: -.32em;
	text-align: center;
    font-size: 10px;
    width: 1.9em;
    height: 1.9em;
    background: #50051f;
    color: #ffffff;
	
}
.sliderTopValue{ text-align:center;margin-top:5px;font-size:12px; }
.sliderTopValue>#min{ float:left;margin-left:0px; }
.sliderTopValue>#max{ float:right;margin-right:-5px; }

.sliderTopValueOnTable{ text-align:center;margin-bottom:1px;font-size:12px; }
.sliderTopValueOnTable>#min{ float:left;margin-left:-5px; }
.sliderTopValueOnTable>#max{ float:right;margin-right:-9px; }

.slider-onTable > .ui-slider-handle{ margin-top:-1px;font-size:10px;width:1.9em;height:1.9em;background:#50051f;color:#ffffff; }

.sliderHolder{ margin-left:5px;margin-right:5px; }

.col-personalityId.form-group{ display:none; }
div#tableToolbar{ display:none; }
div#personalityValuesList>span{ display:inline-block; }
div#personalityValuesList>span.left{}
div#personalityValuesList>span.right{ width:70%;max-width:700px;;display:inline-block;float:right;text-align:right; }
div#personalityValuesList>span>label{ cursor:pointer; }
div#personalityValuesList>span.all{ width:100%; }
div#personalityValuesList select#ownerList{ width:300px;display:inline-block;margin-left:10px; }

div#personalityValuesList{ text-align:left; }
#personalityList{ width:calc(100% - 90px);display:inline-block; max-width:300px; }

ul.personalityFilter{ text-align:left;list-style:none;margin:0;padding:0; }
ul.personalityFilter label{ cursor:pointer; }
#valuesFor{ font-size:130%;margin-top:15px;margin-bottom:5px; }
#valuesFor>span{
	font-weight: bold;
	max-width: calc( 100% - 100px);
	overflow: auto;
	white-space: nowrap;
	display: inline-block;
	vertical-align: top;
}

.bs-bars.pull-left,
.pull-right.search
	{ margin-top:0;margin-bottom:5px; }
	
.table.table-hover th:nth-child(2),
#personalityValues table td:nth-child(2)
	{ width:250px !important; }
.table.table-hover th:nth-child(3),
.table.table-hover th:nth-child(4),
.table.table-hover th:nth-child(5),
#personalityValues table td:nth-child(3),
#personalityValues table td:nth-child(4),
#personalityValues table td:nth-child(5){ width:115px !important;font-size:80%; }
#personalityValues table td:nth-child(3),
#personalityValues table td:nth-child(4),
#personalityValues table td:nth-child(5){ text-align:center; }
.table.table-hover th:nth-child(6),
#personalityValues table td:nth-child(6){ width:40px !important;text-align:center;font-size:12px;vertical-align:middle; }
.columns.columns-right.btn-group.pull-right{ margin-top:0;margin-bottom:5px; }
.columns.columns-right.btn-group.pull-right>button{ height:34px; }
#personTermIdTable td{ color:#000; font-size:12px; }
#personTermIdTable tr:hover td{ color:red; cursor:pointer; }
#personTermIdTable th{font-size:13px; }
</style>
<div id="personalityValues"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
	var lsetUrl = '';
	var table;
	var BASE_ORGANIZATION = '<?=env('BASE_ORGANIZATION');?>';

	function setPersonality(){
		if( $("#personalityFilter0").prop('checked') ){ personalityCheck(0); }
		if( $("#personalityFilter1").prop('checked') ){ personalityCheck(1); }
		if( $("#personalityFilter2").prop('checked') ){ personalityCheck(2); }
	}
	function selectPersonality(){
		$("#personalityValuesTable table").bootstrapTable('selectPage',1);	
		table.refreshOptions();
		if( $("#personalityList").val()!=0){ 
			$("div#tableToolbar").show(); 
			$("#valuesFor>span").text($("#personalityList>option:selected").data('full'));
			$("#valuesFor>span").css("overflow", "hidden");
			let aW = $("#valuesFor>span").width();
			let bW= $("#valuesFor").width();
			if(aW+100>=bW-10){ $("#valuesFor>span").css("overflow", "auto"); }
		}
		else{ 
			$("div#tableToolbar").hide(); 
			$("#valuesFor>span").text('');
		}
	}
	var getPersonalityListAjax = null;
	function personalityCheck(flag){
		$("select#personalityList option").remove();
		if(getPersonalityListAjax!=null){ getPersonalityListAjax.abort(); }
		switch(flag){
			case 1:{
				$("select#personalityList").parent().find('label').text('Persona');
				$("select#personalityList").append('<option value="0" selected="selected">Select Persona</option>');
				$("#personalityListSrch").attr("placeholder", "Search Persona")
				break;
			}
			default:{
				$("select#personalityList").parent().find('label').text('Personality');
				$("select#personalityList").append('<option value="0" selected="selected">Select Personality</option>');
				$("#personalityListSrch").attr("placeholder", "Search Personality")
				break;
			}
		}
		$("select#personalityList").val(0);
		$("select#personalityList").focus();
		selectPersonality();
		var tmpOrgID = $("#ownerList").val();
		var url = apiURL+'/api/dashboard/personality/allPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc';
		let personalityListSrch = $("#personalityListSrch").val().trim();
		if( flag==1 ){ url = apiURL+'/api/dashboard/personality/zeroPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc' }
		if( flag==2 ){ url = apiURL+'/api/dashboard/personality/nonzeroPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc' }
		url+= "/"+personalityListSrch;
		$.ajax({
			url     : url,
			data    : {},
			dataType: 'json',
			complete: function(){ getPersonalityListIsBusy=null; },
			beforeSend: function(xhr, opt){
				$("span#personalityListTotal").html("<i class='fa fa-refresh fa-spin'></i>");
				$("span#personalityListMsg").html("");
			},
			error: function(xhr){
				$("span#personalityListTotal").html("<b style='color:red'>Error code: ["+xhr.status+"]</b>");
				$("span#personalityListMsg").html(xhr.statusText);
			},
			success: 
				function( response ){
					if(response.result==0){
						for( var i in response.data ){
							let tmpOwner = (response.data[i].ownerId==null) ?0 :response.data[i].ownerId;
							if(response.data[i].parentPersonaId!=0){
								var email = "";
								if(response.data[i].get_consumer_user!=null ){
									if(response.data[i].get_consumer_user.email==null){ email=''; }
									else{ email = " - " + response.data[i].get_consumer_user.email; }
								}
								let fullText = response.data[i].personalityName +
												' | ' +
												response.data[i].parent_persona.personalityName +
												email;
								let showText = fullText;
								if(showText.length>50){ showText = showText.substr(0, 47)+"..."; }
								$("select#personalityList")
									.append(
										"<option "+
												"data-owner='"+tmpOwner+"' "+
												"data-parent='"+response.data[i].parentPersonaId+"' "+
												"data-full='"+fullText+"' "+
												"value='"+response.data[i].personalityId+"'"+
										">"+
											showText+
/*
											response.data[i].personalityName + ' - '+
											response.data[i].parent_persona.personalityName +
											email +
*/
										"</option>"
								);
							}
							else{
								let fullText = response.data[i].personalityName;
								let showText = fullText;
								if(showText.length>50){ showText = showText.substr(0, 47)+"..."; }
								$("select#personalityList")
									.append(
										"<option "+
												"data-owner='"+tmpOwner+"' "+
												"data-parent='0' "+
												"data-full='"+fullText+"' "+
												"value='"+response.data[i].personalityId+"'"+
										">"+
											showText+
//											response.data[i].personalityName+
										"</option>"
								);
							}
						}
						$("select#personalityList").val(0);
						$("#valuesFor>span").text('');
						$("span#personalityListTotal").html("Records: "+response.total);
						if(response.total>response.limit){
							$("span#personalityListMsg").html("Displaying the first "+response.limit+" records.");
						}
					}
				}
		});
	}
	
	
	function scalarValue(value, row, index, field){ return index; }
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
$(function(){
	$('#personalityValuesTable').on('load-success.bs.table',function(data){
		$(".slider-onTable").each(function(){
			$(this).slider({
				max  : 10,
				value:  $(this).attr('data'),
				min  : 0,
				slide: function( event, ui ){ $(this).find(".ui-slider-handle").text( ui.value ); <?php /*$("#scalarValue").val(ui.value).change();*/?> },
				stop: function( event, ui ) { callEitScalarValue(this, ui.value, $(this).data('add')); }
			});
		});
	});
});

function callEitScalarValue( obj, scalarValue, isParent ){
	var data = {};
	data.scalarValue = scalarValue;
	data.userID   = userID;
	data.isparent = isParent;
	$.ajax({
		method  :'put',
		url     : apiURL+'/api/dashboard/personality_value/scalarvalue/'+orgID+'/'+$(obj).attr('data-id'),
		data    : data,
		dataType: 'json',
		success: 
			function( response ){
				if(response.result==0){ 
					$("#personalityValuesTable table").bootstrapTable('refresh'); 
				}else{
					$(obj).slider('value', $(obj).attr('data'));
					$(obj).find(".ui-slider-handle").text( $(obj).attr('data') );
				}
			}
	});
}
</script>
