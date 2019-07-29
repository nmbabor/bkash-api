
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Bkash Payment</title>
    <link rel="shortcut icon" href="https://www.bkash.com/sites/default/files/bkash_favicon.ico" type="image/vnd.microsoft.icon" />
    <meta name="viewport" content="width=device-width" ,="" initial-scale="1.0/">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrom=1">
	<script src="js/jquery-1.8.3.min.js"></script>
    <!-- <script id = "myScript" src="https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js"></script> -->
    <script id = "myScript" src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

 
</head>
 <style type="text/css">
    body{
        background-image: url('./bkash1.png');
        background-color: #fff;
    }
    .main{
        text-align: center;width: 365px;
        height: 335px;margin: 0 auto; 
        background-color: #fff;
        background-image: url(./bg.png);
        border: 1px solid #ee286a;
        background-repeat: no-repeat;
        background-size: 100% 100%;
    }
    .content{margin-top: 115px;color: #fff;}
    .content h3{text-align: center;}
    #bKash_button{margin-top: 40px;
    padding: 10px 30px;
    background: #0c67ef;
    border: 0;
    color: #fff;
    font-size: 20px;
    border-radius: 4px;
    cursor: pointer;}
</style>

<body>
<?php
include("jwt.php");
date_default_timezone_set('Asia/Dhaka');
if(!$_GET['val']){
        echo "Invalid token!";
        exit;
    }
$main = $_GET['val'];
if(JWT::decode($main, 'leam@123456')){
    $data = JWT::decode($main, 'leam@123456');
    
    $data = json_decode($data);
    if(!$data->amount && !$data->invoice && !$data->redirect && !$data->start){ ?>
            <div class="main">
                <div class="content">
                    <h3> Invalid token! </h3>
                </div>
            </div>
       <?php
            exit;
        }
    $date = date('Y-m-d H:i:s');
    $start_date = new DateTime($data->start);
    $since_start = $start_date->diff(new DateTime($date));
    $minutes = $since_start->days * 24 * 60;
    $minutes += $since_start->h * 60;
    $minutes += $since_start->i;
    $encode =  JWT::encode(json_encode(["amount"=>$data->amount,"invoice"=>$data->invoice]), 'leam@123456');
    if($minutes<=5){ ?>
        <div class="main">
            <div class="content">
                <h3> Invoice No: <?php echo $data->invoice ?> </h3>
                <h3> Amount: <?php echo $data->amount ?> </h3>
                <span class="text-danger" style="display: block;"> <b id="bkash-notification"> </b> </span>
                <button id="bKash_button">Pay Now</button> 
            </div>
        </div>
        <script type="text/javascript">
 
    var accessToken='';
    $(document).ready(function(){
        var amount = "<?php echo $data->amount ?>";
        var invoice = "<?php echo $data->invoice ?>";
        var info = "<?php echo isset($data->info)?$data->info:'' ?>";
        var redirect = "<?php echo $data->redirect ?>";
        $.ajax({
            url: "token.php",
            type: 'POST',
            contentType: 'application/json',
            success: function (data) {
                console.log('got data from token  ..');
                console.log(JSON.stringify(data));
                accessToken=JSON.stringify(data);
                $('#bKash_button').click()
            },
            error: function(){
                        console.log('error');
                        
            }
        });

        var paymentConfig={
            createCheckoutURL:"createpayment.php",
            executeCheckoutURL:"executepayment.php",
        };

        
        var paymentRequest = { amount, invoice,info };
        console.log(JSON.stringify(paymentRequest));

        bKash.init({
            paymentMode: 'checkout',
            paymentRequest: paymentRequest,
            createRequest: function(request){
                console.log('=> createRequest (request) :: ');
                console.log(request);
                
                $.ajax({
                    url: paymentConfig.createCheckoutURL+"?val="+"<?php echo $encode?>",
                    type:'GET',
                    contentType: 'application/json',
                    success: function(data) {
                        console.log('got data from create  ..');
                        console.log('data ::=>');
                        console.log(JSON.stringify(data));
                       
                        localStorage.setItem("createpayment", JSON.stringify(data));
                        var obj = JSON.parse(data);
                        
                        if(data && obj.paymentID != null){
                            paymentID = obj.paymentID;
                            bKash.create().onSuccess(obj);
                        }
                        else {
                            console.log('error');
                            bKash.create().onError();
                        }
                    },
                    error: function(){
                        console.log('error');
                        bKash.create().onError();
                    }
                });
            },
            
            executeRequestOnAuthorization: function(){
                console.log('=> executeRequestOnAuthorization');
                $.ajax({
                    url: paymentConfig.executeCheckoutURL+"?paymentID="+paymentID,
                    type: 'GET',
                    contentType:'application/json',
                    success: function(data){
                        console.log('got data from execute  ..');
                        console.log('data ::=>');
                        console.log(JSON.stringify(data));
                        
                        data = JSON.parse(data);
                        if(data && data.paymentID != null){
                            /* Store into this system */
                            $.ajax({
                                url: "storePayment.php",
                                type: 'POST',
                                data:data,
                                success: function(storeData){
                                    window.location.href = redirect+"?paymentID="+paymentID;
                                }

                            })

                             localStorage.setItem("executepayment", JSON.stringify(data));
                            //alert('[SUCCESS] data : ' + JSON.stringify(data));
                                                       
                        }
                        else {
                            if(data.errorMessage!=''){
                                $('#bkash-notification').html(data.errorMessage)
                                Swal.fire({
                                  type: 'error',
                                  title: 'Oops...',
                                  text: data.errorMessage,
                                })
                            }
                            localStorage.setItem("executepayment", JSON.stringify(data));
                            bKash.execute().onError();
                        }
                    },
                    error: function(data){
                        console.log('errornmb:');
                        console.log(data);
                        localStorage.setItem("executepayment", 'nmb:'+JSON.stringify(data));
                        bKash.execute().onError();
                    }
                });
            }
        });
        
        
    
        
    });
    
    function callReconfigure(val){
        bKash.reconfigure(val);
    }

    function clickPayButton(){
        $("#bKash_button").trigger('click');
    }


</script>
    


    <?php }else{ ?>
         <div class="main">
            <div class="content">
                <h3> Invalid token! </h3>
            </div>
        </div>
<?php }
}else { ?>

<div class="main">
    <div class="content">
        <h3> Invalid token! </h3>
    </div>
</div>

<?php } ?>

    
</body>
</html>
