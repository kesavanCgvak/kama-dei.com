<style>
	.filter-table .quick { margin-left: 0.5em; font-size: 0.8em; text-decoration: none; }
	.fitler-table .quick:hover { text-decoration: underline; }
	td.alt { background-color: #ffc; background-color: rgba(255, 255, 0, 0.4); }
	
	#filterTable th, #filterTable td{ text-align:center;padding:2px 4px;border:1px dotted; }
	#filterTable thead>tr{ border:1px solid black; }
	#filterTable tr{ border-bottom:1px solid gray; }
	#filterTable tr>th:nth-child(1),
	#filterTable tr>td:nth-child(1)
		{ text-align:center;min-width:40px; }
	#filterTable tr>th:nth-child(2),
	#filterTable tr>td:nth-child(2)
		{ text-align:right;width:60px;font-size:12px; }
	#filterTable tr>th:nth-child(3),
	#filterTable tr>td:nth-child(3)
		{ width:150px;text-align:left; }
	#filterTable tr>th:nth-child(4),
	#filterTable tr>td:nth-child(4)
		{ width:250px;text-align:left; }
	#filterTable thead th{ text-align:center !important; }
	#filterTable thead, #filterTable tbody{ display:block; }
	#filterTable tbody{ max-height:150px;overflow:auto; }
</style> 
<table id="filterTable">
	<thead>
		<tr>
			<th scope="col"><i class="fa fa-square-o"></i></th>
			<th scope="col" title="President Number">Id</th>
			<th scope="col">Name</th>
			<th scope="col">Description</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>
<script type="text/javascript" src="/public/dist/dashboard/jquery.filtertable.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
		$.ajax({
			url     : '<?=env('API_URL');?>/api/dashboard/term/all/0',
			cache   : true,
			method  : 'get',
			dataType: 'json',
			beforeSend: 
				function(){ 
					$("#filterTable tbody").html('<tr><td colspan="4" style="width:500px;text-align:center"><i class="fa fa-refresh fa-spin"></i></td></tr>'); 
				},
			success:
				function(data){
					//------------------------------------------------
					if(data.result!=0){
						$("#filterTable tbody").html('<tr><td colspan="4" style="width:500px;text-align:center;color:red;">'+data.msg+'</td></tr>'); 
					}else{
						if(data.total==0){
							$("#filterTable tbody").html('<tr><td colspan="4" style="width:500px;text-align:center;">'+data.msg+'</td></tr>'); 
						}else{
							$("#filterTable tbody").html(''); 
							for(row in data.data){
								var tr = $("<tr>");
								$(tr).append("<td><i class='fa fa-square-o'></i></td>");
								$(tr).append("<td>"+data.data[row].termId+"</td>");
								$(tr).append("<td>"+data.data[row].termName+"</td>");
								$(tr).append("<td>-</td>");
								$("#filterTable tbody").append($(tr)); 
							}
						}
					}
					//------------------------------------------------
					var indx=1;
					$("#filterTable tr>th").each(function(){ $(this).css('width', $("#filterTable tr>td:nth-child("+(indx++)+")").css('width')); });
					$("#filterTable tbody>tr").each(function(){ 
						indx=1;
						$(this).find('td').each( function(){ $(this).css('width', $("#filterTable tr>th:nth-child("+(indx++)+")").css('width')+' !important'); });
					});
//					$("#filterTable tr>td").each(function(){ $(this).css('width', $("#filterTable tr>th:nth-child("+(indx++)+")").css('width')+' !important'); });
					//------------------------------------------------
					$("#filterTable").filterTable();
				},
		});
	});
</script>

<link rel="stylesheet" type="text/css" href="/public/dist/dashboard/jquery.inputpicker.css">
<script type="text/javascript" src="/public/dist/dashboard/jquery.inputpicker.js"></script>
<br />
<br />
<hr />
<br />
<input class="form-control" id="demo" value=""/>
<script type="text/javascript">
$('#demo').inputpicker({
	url: '<?=env('API_URL');?>/api/dashboard/term/all/0',
	fields:[
		{name:'termId',text:'Id'},
		{name:'termName',text:'Name'}
	],
/*
	data:[
		{value:"1",text:"Text a", description: "This is the description of the text x."},
		{value:"2",text:"Text b", description: "This is the description of the text y."},
		{value:"3",text:"Text c", description: "This is the description of the text z."}
	],
	fields:[
		{name:'value',text:'Id'},
		{name:'text',text:'Title'},
		{name:'description',text:'Description'}
	],
*/
	autoOpen: true,
	headShow: true,
	fieldText : 'termName',
	fieldValue: 'termId',
	filterOpen: true
});
//inputpicker-wrapped-list
</script>
