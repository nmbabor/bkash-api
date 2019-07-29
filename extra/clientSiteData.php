<?php
$apiData = ["amount"=>"1","invoice"=>$invoice,"redirect"=>\URL::to('/payment-received'),"start"=>date('Y-m-d H:i:s')];

//View 

$encode =  App\JWT::encode(json_encode($apiData), 'leam@123456');

/*
<a href="https://bkash.patronus.com.bd/?val={{$encode}}"> Pay Now </a>
*/