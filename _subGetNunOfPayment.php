<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
$Object = new Orders();
$EXPECTED = array('token','order_id','balance','payment');

foreach ($EXPECTED AS $key) {
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    } else {
        ${$key} = NULL;
    }
}

//--- validate
$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    if(empty($payment)) {
        $payment =0;
    }

    $subscription = $_POST['subscription'];
    if(empty($subscription)){
        $subscription='{}';
    }
    $subscription = json_decode($subscription,true);
    $prcessingFreeOld =0;
    $numberOfPay =0;

        if(empty($order_id)){
            if(count($subscription)>0) $numberOfPay = $Object->numberOfPaymentForAdd($subscription);

        }else{
            if($payment >0 && $balance!=0){
                $old =$Object->getSub_OrderID($order_id);

                $oldSub = $old['subscription'];
                if(!isset($oldSub['numberOfPay'])) $oldSub['numberOfPay']=0;
                if(!isset($oldSub['processingFee'])) $oldSub['processingFee']=0;

                $sqlText ="select count(*) from payment_schedule
                    where orderID = '{$order_id}' AND invoiceID is NOT NULL AND
                    (inactive =0 || inactive IS NULL)";
                $num = $Object->totalRecords($sqlText,0);

                $prcessingFreeOld = $num*$oldSub['processingFee'];

                if(count($subscription)>0){
                    if($num==0){
                        $numberOfPay = $Object->numberOfPaymentForAdd($subscription);
                    }else{
                        $sub =  $Object->getNumberOfPay($order_id,$subscription, $oldSub);
                        if($sub['notchange']==""){
                            $numberOfPay = $oldSub['numberOfPay'];
                        }else{
                            $numberOfPay = $sub['numberOfPay'];
                        }
                    }
                }

            }else{
                if(count($subscription)>0) $numberOfPay = $Object->numberOfPaymentForAdd($subscription);
            }
        }




    $ret = array('ERROR'=>'','numberOfPay'=>$numberOfPay,'prcessingFreeOld'=>$prcessingFreeOld);

}

$Object->close_conn();
echo json_encode($ret);





