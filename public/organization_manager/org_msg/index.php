<style>
	#botMessage table th{ font-size:smaller; }
	#botMessage table td{ font-size:small; }

	#botMessage table th:nth-child(1), #botMessage table td:nth-child(1)  { width:150px; min-width: 50px; }
	#botMessage table th:nth-child(2), #botMessage table td:nth-child(2)  { width:150px; min-width:100px; }
	#botMessage table th:nth-child(3), #botMessage table td:nth-child(3)  { width:150px; min-width: 50px; }
	#botMessage table th:nth-child(5), #botMessage table td:nth-child(5)  { width:120px; min-width: 50px; }
	#botMessage table th:last-child, #botMessage table td:last-child  { font-size:x-small; text-align:center; width:40px; }

	<?php if($orgID==0): ?>
	div.pull-right.search>select{ width:49%; display:inline-block; margin-right:1%; }
	div.pull-right.search>input{ width:49%; display:inline-block; margin-left:1%; }
	<?php endif; ?>
	.stv-radio-button {position: absolute;left: -9999em;top: -9999em;}

	.stv-radio-button + label {float: left;padding: 0.5em 1em;cursor: pointer;border: 1px solid #ccc;margin-right: -1px;color: #222;background-image: linear-gradient(#eeeeee, #fbfbfb,#eeeeee);}

	.stv-radio-button + label:first-of-type {border-radius: 10px 0px 0px 10px;}

	.stv-radio-button + label:last-of-type {border-radius: 0px 10px 10px 0px;}

	.stv-radio-button:checked + label {background-color: #5393c5;border: 1px solid #2373a5;background: #5393c5;font-weight: bold;color: #fff;}


	#tabDIV{ width: 100%; text-align: center; display: none;}
	#tabDIV button{ color: #000; background-color: #f8f8f8; border-color: #f0f0f0; font-weight: 100; width: 200px; }
	#tabDIV button.active{ color: #fff; background-color: #2a94d6; border-color: #2585c1; }
	<?php if($orgID==0): ?>
	ul.menu-actions.menu--right li:first-child{ display:none; }
	<?php endif; ?>
</style>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userId = "<?=\Session::get('userID');?>";
	var table;	
	var kaaSColumnHidden = true;
	var ownersList = "-1";
	var messageType = "bot";
</script>

<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<div id="tabDIV">
	<div class="btn-group btn-group-lg" role="group" aria-label="Basic example">
		<button type="button" class="btn active" data-message="bot">Front-End Messages</button>
		<button type="button" class="btn" data-message="ai">Back-End Messages</button>
	</div>
</div>
<div id="botMessage"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script src="/public/js/app.js"></script>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script type="application/javascript">
	<?php if($orgID==0): ?>
	$(function(){
		$("div.pull-right.search").prepend(
			'<select class="form-control" id="ownersList">'+
				'<option value="-1" selected="selected">All Owners . . .</option>'+
			'</select>'
		);
		
		$("#ownersList").on('change', function(){
			ownersList=$("#ownersList").val();
			$(table.table).bootstrapTable('refresh');
		});

		$(table.table).on('load-success.bs.table', function(){
			let ownersListVal = $("#ownersList").val();
			$("#ownersList option").remove();
			$("#ownersList").append('<option value="-1">All Owners . . .</option>');
			$.get(apiURL+'/api/dashboard/'+messageType+'message/getOwner/', function(res){
				if(res.result==0){
					for(let i in res.data){
						$("#ownersList").append('<option value="'+res.data[i].orgId+'">'+res.data[i].orgName+'</option>');
					}
					$("#ownersList").val(ownersListVal);
				}
			});
			if(messageType=='ai'){
				$('#insertBtn').text("Add Kama-DEI Messages");
				$("#botMessage table th:first-child").css("display", "none");
				$("#botMessage table td:first-child").css("display", "none");
//				$("#botMessage table th:nth-child(3)").css("display","none");
//				$("#botMessage table td:nth-child(3)").css("display","none");
				$("#botMessage table th:nth-child(5)").css("display","");
				$("#botMessage table td:nth-child(5)").css("display","");
			}else{
				$('#insertBtn').text("Add Chatbot Message");
				$("#botMessage table th:first-child").css("display", "");
				$("#botMessage table td:first-child").css("display", "");
//				$("#botMessage table th:nth-child(3)").css("display","");
//				$("#botMessage table td:nth-child(3)").css("display","");
				$("#botMessage table th:nth-child(5)").css("display","none");
				$("#botMessage table td:nth-child(5)").css("display","none");
			}
		})
	});
	<?php else: ?>
	$(function(){
		$(table.table).on('load-success.bs.table', function(){
			if(messageType=='ai'){
				$('#insertBtn').text("Add Kama-DEI Messages");
				$("#botMessage table th:first-child").css("display", "none");
				$("#botMessage table td:first-child").css("display", "none");
//				$("#botMessage table th:nth-child(3)").css("display","none");
//				$("#botMessage table td:nth-child(3)").css("display","none");
				$("#botMessage table th:nth-child(5)").css("display","");
				$("#botMessage table td:nth-child(5)").css("display","");
			}else{
				$('#insertBtn').text("Add Chatbot Message");
				$("#botMessage table th:first-child").css("display", "");
				$("#botMessage table td:first-child").css("display", "");
//				$("#botMessage table th:nth-child(3)").css("display","");
//				$("#botMessage table td:nth-child(3)").css("display","");
				$("#botMessage table th:nth-child(5)").css("display","none");
				$("#botMessage table td:nth-child(5)").css("display","none");
			}
		})
	});
	<?php endif; ?>
</script>
