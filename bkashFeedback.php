<?php 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once './config.php';
$secret = isset($_GET['secret']) ? $_GET['secret'] : '';
if ($secret === 'Edu@Leam') {
	// get posted data
	$data = json_decode(file_get_contents("php://input"));
	if(
	    // !empty($data->invoice) &&
	    // !empty($data->payment_id) &&
	    // !empty($data->amount) &&
	    // !empty($data->transaction_status) &&
	    // !empty($data->payment_date) &&
	    // !empty($data->trx_id)
	    $data
	){
		$date = date('Y-m-d',strtotime($data->payment_date));
		$details = json_encode($data);
		/*$sql = "INSERT INTO payment (invoice, customer_number, payment_id, amount, trx_id, transaction_status, payment_date,details)
	VALUES ('$data->invoice','$data->customer_number','$data->payment_id','$data->amount','$data->trx_id', '$data->transaction_status','$date','$details')";*/
	$sql = "INSERT INTO payment (details) VALUES ('$details')";
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



	
}else{
	http_response_code(403);
	$result = json_encode(["message" =>'Secret is not valid.']);
	echo($result);
}

function productCreate($data){
	if(empty($data->customer_number)){
		$data->customer_number = "";
	}
	$sql = "INSERT INTO payment (invoice, customer_number, payment_id, amount, trx_id, transaction_status, payment_date)
	VALUES ($data->invoice,$data->customer_number,$data->payment_id,$data->amount,$data->trx_id, $data->transaction_status)";

	return $conn->query($sql);
}








$conn->close();