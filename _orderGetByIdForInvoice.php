<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
//include_once './lib/class.invoice.php';
include_once './lib/class.Subcription.php';
$Object = new Orders();

    $EXPECTED = array('token','order_id','invoiceID');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed');
    }else{
        $ret_temp = $Object->getOrderID_invoice($order_id,$invoiceID);

        $Object->close_conn();
        unset($Object);
        $ret = array();
        if(count($ret_temp)>0){
            $t =  json_decode($ret_temp[0]['products_ordered'],true);
            unset($ret_temp[0]['products_ordered']);
            $ret_temp[0]['products_ordered'] =$t;

            $ret["order"] = $ret_temp[0];
            $ret["invoice"] = [];

            $Object1 = new Subcription();
            $history_payment = $Object1->historicalPayment($order_id);

            $schedule_payment = $Object1->schedulePayment($order_id);

            $Object1->close_conn();
            unset($Object1);

            $ret['history_payment']=$history_payment;
            $ret['schedule_payment']=$schedule_payment;
        }

    }


    echo json_encode($ret);

