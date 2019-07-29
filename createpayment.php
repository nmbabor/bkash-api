<?php
include("jwt.php");
session_start();

$data = JWT::decode($_GET['val'], 'leam@123456');
$data = json_decode($data);

$strJsonFileContents = file_get_contents("config.json");
$array = json_decode($strJsonFileContents, true);
$proxy = $array["proxy"];
$createpaybody=array(
           'amount'=>$data->amount,
           'currency'=>'BDT',
           'intent'=>"sale",
           'merchantInvoiceNumber'=>$data->invoice,
          // 'merchantAssociationInfo'=>$info
           );   

    $url = curl_init($array["createURL"]);

    $createpaybodyx = json_encode($createpaybody);
    $header=array(
        'Content-Type:application/json',
        'authorization:'.$_SESSION['token'],
        'x-app-key:'.$array["app_key"]
    );

    curl_setopt($url,CURLOPT_HTTPHEADER, $header);
	curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($url,CURLOPT_POSTFIELDS, $createpaybodyx);
    curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
    //curl_setopt($url, CURLOPT_PROXY, $proxy);
    
    $resultdata = curl_exec($url);
    curl_close($url);
    echo $resultdata;
    

?>
