<?php 


include_once './config.php';
$data = (object) $_POST;
	if($data){
		$date = substr($data->createTime, 0, 19);
		$date = str_replace('T', ' ', $date);
		$date = date('Y-m-d H:i:s',strtotime($date));
		$sql = "INSERT INTO invoice_list (invoice, payment_id, amount, trx_id, transaction_status, payment_date)
	VALUES ('$data->merchantInvoiceNumber','$data->paymentID','$data->amount','$data->trxID', '$data->transactionStatus','$date')";
		// create the product
	    if($conn->query($sql)){
	        http_response_code(201);
			$result = json_encode(["message"=>"Successfully Send Notification.", "data" => $data]);
			echo($result);
	    }
	    else{
	        http_response_code(503);
	        echo json_encode(array("message" => "Unable to send.","error"=>$conn->error));
	    }
	}else{
	    http_response_code(400);
	    echo json_encode(array("message" => "Unable to send. Data is incomplete."));
	}










$conn->close();