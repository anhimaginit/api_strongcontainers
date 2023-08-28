<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
$Object = new Orders();
    $EXPECTED = array('token','order_id','balance','bill_to','note','payment',
        'ship_to','salesperson','total','order_total','warranty','order_title','discount_code',
        'jwt','private_key');

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
        $ret = array('SAVE'=>false,'ERROR'=>'Authentication is failed');
    }else if(!empty($order_id)){
        $isAuth = $Object->auth($jwt,$private_key);
        $isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $errObj = $Object->validate_order_fields($token,$bill_to,$salesperson);

            if(!$errObj['error']){
                $value_Array = $_POST['products_ordered'];
                //print_r($value_Array);die();
                if(empty($total)) {
                    $total =0;
                }

                if(empty($payment)) {
                    $payment =0;
                }

                if(empty($balance)) {
                    $balance =0;
                }

                if(empty($warranty)) {
                    $warranty =0;
                }

                $notes =array();
                if(isset($_POST['notes'])){
                    $notes=$_POST['notes'];
                }

                $subscription = $_POST['subscription'];
                if(empty($subscription)){
                    $subscription='{}';
                }

                $subscription = json_decode($subscription,true);
                $prcessingFreeOld =0;
                if($payment >0 && $balance!=0){
                    $old =$Object->getSub_OrderID($order_id);

                    $oldSub = $old['subscription'];

                    $sqlText ="select count(*) from payment_schedule
                    where orderID = '{$order_id}' AND invoiceID is NOT NULL AND
                    (inactive =0 || inactive IS NULL)";
                    $num = $Object->totalRecords($sqlText,0);
                    if(!isset($oldSub['processingFee'])) $oldSub['processingFee']=0;
                    $prcessingFreeOld = $num*$oldSub['processingFee'];

                    $total1 =$total -$payment + $prcessingFreeOld;

                    if($num==0){
                        $sub =  $Object->initialAmountInvoice_date($subscription, $total);
                        $amount = $sub['init_amount'] ;
                        $sub['notchange']=1;
                    }else{
                        $sub =  $Object->getNumberOfPayInvoice_date($order_id,$subscription, $oldSub,$total1);
                        $amount = $sub['paymentAmount'];
                    }

                }else{
                    $sub =  $Object->initialAmountInvoice_date($subscription, $total);
                    $amount = $sub['init_amount'] ;
                    $sub['notchange']=1;
                }

                $invDate =$sub['invDate'];

                $subscription['numberOfPay'] = $sub['numberOfPay'];
                $subscription['paymentAmount'] = $sub['paymentAmount'];
                $subscription['endDate'] = $sub['endDate'];

                $order_total =$total + $sub['numberOfPay']* $subscription['processingFee'] + $subscription['initiedFee'] +$prcessingFreeOld;
                $subscription = json_encode($subscription);

                if($order_total==0){
                    $result ="Total or Balance must be greater than 0";
                }else{
                    $result = $Object->updateOrder($order_id,$value_Array,$bill_to,$note,
                        $salesperson,$order_total,$warranty,$notes,$order_title,$subscription,$discount_code);
                }

                if(is_numeric($result) && $result) {
                    $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'');
                    //updatePaymentSchedule
                    $orderID = $order_id;
                    $billToID = $bill_to;
                    if($sub['notchange']==1 ) $Object->update_PaymentSchedule($order_id,$amount,$invDate);

                }else{
                    //log errors
                    $info ="Order -- order_id: ".$order_id.", products_ordered: , bill_to: ".$bill_to.
                        ", salesperson ".$salesperson. ", err: ".$result;

                    $Object->err_log("Orders",$info,$order_id);

                    $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$result);
                }

            } else {
                $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
            }
        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }



    }else{
    $ret = array('SAVE'=>'FAIL','ERROR'=>'The Order is not already');

  }

    $Object->close_conn();
    echo json_encode($ret);





