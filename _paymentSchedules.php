<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.Subcription.php';
    $Object = new Subcription();

    $EXPECTED = array('token','order_id');

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
        $history_payment = $Object->historicalPayment($order_id);
        $schedule_payment = $Object->schedulePayment($order_id);
        $ret = array('AUTH'=>True,'ERROR'=>'','history_payment'=>$history_payment,'schedule_payment'=>$schedule_payment);
    }

    $Object->close_conn();
    echo json_encode($ret);




