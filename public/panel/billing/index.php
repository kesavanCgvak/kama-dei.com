<style>
	div.card-detail{ min-height:80vh; }

	#billing .table th{ font-size:13px; }
	#billing .table td{ font-size:12px; }

	#billing .table th:first-child,
	#billing .table td:first-child{ width:36px; text-align:center; font-size:11px; }

	#billing .table th:nth-child(2),
	#billing .table td:nth-child(2){ width:100px !important; text-align:left; font-size:12px; }

	#billingTableRoot th:nth-child(4),
	#billingTableRoot td:nth-child(4){ width:150px !important; text-align:center; font-size:12px; }
	#billingTableRoot td:nth-child(4){ text-align:right; }
	
	#billingTableRoot tbody tr.detail-view>td:hover{ background-color:#fff; }
	#billingTableRoot tbody tr.detail-view table thead{ background-color:#faebd7; }
	#billingTableRoot tbody tr.detail-view table tbody>tr>td{ background-color:#fff; }
	#billingTableRoot tbody tr.detail-view table tbody>tr:hover{ background-color:#f0f8ff; }
	
	.tblChild td:nth-child(3){ text-align:right; }
	.tblChild td:nth-child(4),
	.tblChild td:nth-child(5){ text-align:center !important; width:50px !important;  }

	span.orgBill, span.orgDetail{ color:blue; }
	span.orgBill:hover, span.orgDetail:hover{ color:red; cursor:pointer; }
	
	#openBill iframe{ border: none; }
</style>
<?php
?>
<div id="billing"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL  = "<?=env('API_URL');?>";
	var orgID   = "<?=$orgID;?>";
	var userID  = "<?=session()->get('userID');?>";
	var levelID = "<?=session()->get('levelID');?>";
	var billingClass;
</script>
<script src="/public/js/app.js"></script>

<div class="modal" tabindex="-1" role="dialog" id="openBill">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" style="display: none">Ok</button>
				<button type="button" class="btn btn-danger" style="float: left;" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
