<?php

namespace App\Http\Controllers;
use App\Model\StudentPayment;
use App\Model\UserInfo;
use Illuminate\Http\Request;
use App\JWT;
use App\Model\Branch;

class StudentPaymentController extends Controller
{
    public function index(){
        date_default_timezone_set('Asia/Dhaka');
        $userInfo = UserInfo::where('user_id',\Auth::user()->id)->first();
        $branch = Branch::findOrFail(\Auth::user()->branch);
        if($userInfo==''){
            return redirect('personal-info')->with('error','Please fill up the Registration Form first');
        }
        if($userInfo->final_step!=1){
            return redirect('personal-info')->with('error','Please fill up the Registration Form first');
        }
        $payments = StudentPayment::where('user_id',\Auth::user()->id)->get();
        $invoice = StudentPayment::max('id')+1;
        $invoice = 'PE-'.$branch->branch_id.'-'.\Auth::user()->id.'-'.$invoice;
        $apiData = ["amount"=>"1","invoice"=>$invoice,"redirect"=>\URL::to('/payment-received'),"start"=>date('Y-m-d H:i:s')];
        return view('backend.payment.index',compact('payments','apiData','userInfo','branch'));
    }

    public function paymentReceived(Request $request){

        try{
            if(isset($request->paymentID)){
                $payment_id = $request->paymentID;

                StudentPaymentController::bkash_Get_Token();
                $result = StudentPaymentController::paymentQuery($payment_id);
                $invoice =explode('-',$result->merchantInvoiceNumber);
                $user_id = $invoice[1];
                $user_id = \Auth::user()->id;

                $payment = StudentPayment::where('user_id',$user_id)->count('id');
                $userInfoUpdate = [];
                if($payment==0){
                    $userInfoUpdate['payable_amount']=12350;

                }
                $date = substr($result->createTime, 0, 19);
                $date = str_replace('T', ' ', $date);
                $date = date('Y-m-d H:i:s',strtotime($date));
                StudentPayment::create([
                    'invoice'=>$result->merchantInvoiceNumber,
                    'user_id'=>$user_id,
                    'amount'=>$result->amount,
                    'payment_date'=>$date,
                    'payment_id'=>$result->paymentID,
                    'transaction_status'=>$result->transactionStatus,
                    'trx_id'=>$result->trxID,
                    'created_by'=>\Auth::user()->id
                ]);
                $totalPayment = StudentPayment::where('user_id',$user_id)->sum('amount');
                $userInfoUpdate['total_paid'] = $totalPayment;
                UserInfo::where('user_id',$user_id)->update($userInfoUpdate);
                $userInfo = UserInfo::where('user_id',$user_id)->first();
                $due = $userInfo->payable_amount-$totalPayment;
                $number = \Auth::user()->mobile_no;

                $msg = 'Congrats! Your bKash payment of TK.'.$result->amount.' is successful. Your Dues: TK.'.$due.'. You can complete payment by paying again from https://patronus.com.bd';
                $result = \MyHelper::sms($number,$msg);

                $bug=0;
            }else{
                return redirect('payment');
            }
        }catch(Exception $e){
            $bug=$e->errorInfo[1];
        }
        if($bug==0){
            return redirect('payment')->with('success','Payment Successfully done');
        }else{
            return redirect('payment')->with('error',$bug);
        }


    }

    public function urlGenerate($amount){

        date_default_timezone_set('Asia/Dhaka');
        $branch = Branch::findOrFail(\Auth::user()->branch);
        $invoice = StudentPayment::max('id')+1;
        $invoice = 'PE-'.$branch->branch_id.'-'.\Auth::user()->id.'-'.$invoice;
        $apiData = ["amount"=>$amount,"invoice"=>$invoice,"redirect"=>\URL::to('/payment-received'),"start"=>date('Y-m-d H:i:s')];
        $encode =  JWT::encode(json_encode($apiData), 'leam@123456');
        return $encode;
    }
    public static function bkash_Get_Token(){
     $array = [
        "query"     => "https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/query/",
        "tokenURL"  => "https://checkout.pay.bka.sh/v1.2.0-beta/checkout/token/grant",
        "app_key"   => "61dm0ds0q2u078psjc27drd3on",
        "app_secret"=> "1oim7s046dcrco29jjdbdrbf5sm4gqs9k7u7mr8e560sk4te8jj",
        "username"  => "EDULEAM",
        "password"  => "E@4u0Ta7Pl2"
     ];


        $post_token=array(
            'app_key'=>$array["app_key"],
            'app_secret'=>$array["app_secret"]
        );

        $url=curl_init($array["tokenURL"]);
        $posttoken=json_encode($post_token);
        $header=array(
            'Content-Type:application/json',
            'password:'.$array["password"],
            'username:'.$array["username"]
        );

        curl_setopt($url,CURLOPT_HTTPHEADER, $header);
        curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url,CURLOPT_POSTFIELDS, $posttoken);
        curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($url, CURLOPT_PROXY, $proxy);
        $resultdata=curl_exec($url);
        curl_close($url);
        $result = json_decode($resultdata, true);
        \Session::put('id_token',$result['id_token']);

    }
    public static function paymentQuery($id){
     $array = [
        "query"      =>"https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/query/",
        "transaction"=>"https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/search/",
        "app_key"    => "61dm0ds0q2u078psjc27drd3on",
        "app_secret" => "1oim7s046dcrco29jjdbdrbf5sm4gqs9k7u7mr8e560sk4te8jj",
        "username"   => "EDULEAM",
        "password"   => "E@4u0Ta7Pl2"
     ];


     $url = curl_init($array["query"].$id);

     $header=array(
         'Content-Type:application/json',
         'authorization:'.\Session::get('id_token'),
         'x-app-key:'.$array["app_key"]
     );

     curl_setopt($url,CURLOPT_HTTPHEADER, $header);
     curl_setopt($url,CURLOPT_CUSTOMREQUEST, "GET");
     curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
     curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
     //curl_setopt($url, CURLOPT_PROXY, $proxy);

     $resultdatax=curl_exec($url);
     curl_close($url);
     return json_decode($resultdatax);

    }
    public static function transaction($id){
     $array = [
        "query"      =>"https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/query/",
        "transaction"=>"https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/search/",
        "app_key"    => "61dm0ds0q2u078psjc27drd3on",
        "app_secret" => "1oim7s046dcrco29jjdbdrbf5sm4gqs9k7u7mr8e560sk4te8jj",
        "username"   => "EDULEAM",
        "password"   => "E@4u0Ta7Pl2"
     ];


     $url = curl_init($array["transaction"].$id);

     $header=array(
         'Content-Type:application/json',
         'authorization:'.\Session::get('id_token'),
         'x-app-key:'.$array["app_key"]
     );

     curl_setopt($url,CURLOPT_HTTPHEADER, $header);
     curl_setopt($url,CURLOPT_CUSTOMREQUEST, "GET");
     curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
     curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
     //curl_setopt($url, CURLOPT_PROXY, $proxy);

     $resultdatax=curl_exec($url);
     curl_close($url);
     return $resultdatax;

    }
}
