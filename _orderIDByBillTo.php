<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
$Object = new Orders();

    $EXPECTED = array('token','bill_to','order_id');

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
        if(isset($_POST['balance'])){
            if(is_numeric($_POST['balance'])){
               $balance= $_POST['balance'];
                $ret = $Object->getOrderID_byBillTo($bill_to,$order_id,$balance);
            }else{
                $ret = $Object->getOrderID_byBillTo($bill_to);
            }
        }else{
            $ret = $Object->getOrderID_byBillTo($bill_to);
        }
    }

    $Object->close_conn();
    echo json_encode($ret);

