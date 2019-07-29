<?php
include("jwt.php");
date_default_timezone_set('Asia/Dhaka');
// $demo = json_encode(["amount"=>"1","invoice"=>"bcic-123321456","redirect"=>"https=>//api.edusys.live","start"=>date('Y-m-d H:i:s')]);
// $encode =  JWT::encode($demo, 'leam@123456');
// echo $encode;
// $main = $_GET['val'];

// if(JWT::decode($main, 'leam@123456')){
// 	$data =  JWT::decode($main, 'leam@123456');
// 	$data = json_decode($data);
// 	$date = date('Y-m-d H:i:s');
// 	$start_date = new DateTime($data->start);
// 	$since_start = $start_date->diff(new DateTime($date));
// 	$minutes = $since_start->days * 24 * 60;
// 	$minutes += $since_start->h * 60;
// 	$minutes += $since_start->i;
// 	if($minutes<=5){
// 		echo $data->amount;
// 		exit;

// 	}
// }



 ?>


<script src="js/jquery-1.8.3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script type="text/javascript">
	$(document).ready(function(){
		Swal.fire({
		  type: 'error',
		  title: 'Oops...',
		  text: 'Something went wrong!',
		})
	})
	// $(document).ready(function(){
	// 	 $.ajax({
 //            url: "storePayment.php",
 //            type: 'POST',
 //            data:{"paymentID":"BXP1F0H1557817529154","createTime":"2019-05-14T07:05:29:283 GMT+0000","updateTime":"2019-05-14T07:06:07:053 GMT+0000","trxID":"6EE6G0GAS6","transactionStatus":"Completed","amount":"1","currency":"BDT","intent":"sale","merchantInvoiceNumber":"bcic-343871456"},
 //            success: function(storeData){
 //                console.log(storeData);
 //            },
 //            error: function(){
	// 			console.log('error');
 //            }

 //        })
	// })
</script>