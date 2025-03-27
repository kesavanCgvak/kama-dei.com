<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script>
	$(function(){
		

		
				$.ajax({
					url: 'https://staging_py.kama-dei.com/list_collections_of_org/v1',
					//url: 'https://preprod_py.kama-dei.com/list_collections_of_org/v1',
					
					method: 'POST',
					
					timeout: 0,
					processData: false,
					mimeType: "multipart/form-data",
					contentType: false,
					
					dataType: "json",
					cache: false,
					traditional: true, 
					crossDomain: true,
					'Access-Control-Allow-Origin': '*',
					headers: {
						'Access-Control-Allow-Origin': '*',
						"Content-Type": "application/json"
					},
					data: {org: 1},
					beforeSend: function(xhr) { 
						xhr.setRequestHeader('Access-Control-Allow-Origin', '*'); 
					},
					success: function(res){
console.log(res);
					},
					error: function(xhr){
console.log(xhr);
					}
				});

		
		
	});
</script>