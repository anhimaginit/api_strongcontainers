<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.payment.php';
    $Object = new Payment();

    $EXPECTED = array('token','pay_task');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $rsl = array('ERROR'=>'Authentication is failed','payments'=>array());
    }else{
        $rsl = $Object->get_payment_task($pay_task);
    }

$Object->close_conn();
echo json_encode($rsl);



