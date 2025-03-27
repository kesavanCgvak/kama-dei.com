<?php
//--------------------------------------------------------------------------------------
$conn = mysqli_connect( 'localhost', "kamadeikb_user", 'IdgfdvIg24cI9OA9', 'kamalogs' );
mysqli_query($conn, "SET NAMES 'utf8'");
//--------------------------------------------------------------------------------------
$total = 0;
$result = mysqli_query( $conn, "select count(*) as cnt from kama_usage");
while( $row = mysqli_fetch_assoc( $result ) ){ $total = $row['cnt']; }

$uploaded = 0;
$result = mysqli_query( $conn, "select count(*) as cnt from kama_usage where send=1");
while( $row = mysqli_fetch_assoc( $result ) ){ $uploaded = $row['cnt']; }

$remind = 0;
$result = mysqli_query( $conn, "select count(*) as cnt from kama_usage where send=0");
while( $row = mysqli_fetch_assoc( $result ) ){ $remind = $row['cnt']; }
?>
<html lang="en">
	<head>
		<title>KAMA-DEI - {{\Session('userName')}}</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	</head>
	<body style="background: beige">
	<div style="width:100%; max-width:500px; margin:5% auto; box-shadow:10px 5px 15px #999;border-radius: 15px;">
		<div class="panel panel-info" style="border-radius: 15px;">
			<div class="panel-heading" style="border-radius:15px 15px 0 0;">Local logs copy to remote server</div>
			<div class="panel-body">
				@if(env('DB_HOST_LOG')!='127.0.0.1')
				<div class="panel panel-default">
					<div class="panel-heading">Local</div>
					<div class="panel-body">
						<div>
							<label style="width:82px;">Total:</label> <span>{{number_format($total,0,'', ',')}}</span>
						</div>
						<div>
							<label style="width:82px;">Uploaded:</label> <span id="uploaded">{{number_format($uploaded,0,'', ',')}}</span>
						</div>
						<div>
							<label style="width:82px;">On process:</label> <span id='remind'>{{number_format($remind,0,'', ',')}}</span>
						</div>
					</div>
					<div class="panel-fotter" align="right" style="padding: 0 5px 5px 0">
						<button class="btn btn-success" onClick="process()" id="pBTN">Process</button>
					</div>
				</div>
				@else
				<span>kamalogs >>> <b style="color:red">{{ env('DB_HOST_LOG') }}</b></span>
				@endif
			</div>
		</div>
	</div>
	</body>
	<script type="application/javascript">
		function process(){
			$("#pBTN").prop('disabled', true).text('Processing...');
			$.get("/logs/load", function(res){
				$("#pBTN").prop('disabled', false).text('Process');
				if(res.result==0){
					$("#uploaded").text(res.uploaded);
					$("#remind"  ).text(res.remind);
					if(res.end!=0){ process(); }
				}else{
					alert(res.msg);
				}
			})
		}
	</script>
</html>
	<?php
//print_r( \Session::all() );
/*
echo \Session('userID');
echo "<br/>";
echo \Session('isLogin');
echo "<br/>";
echo \Session('isAdmin');
*/