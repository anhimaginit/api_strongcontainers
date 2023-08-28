<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
include_once './lib/class.cus_warranty.php';
    $Object = new CustomerWarranty();

    $EXPECTED = array('token','email');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('AUTH'=>false,'ERROR'=>'Authentication is failed');
    }else{
        $ret = $Object->getContact_Email($email);

    }

    $Object->close_conn();
    echo json_encode($ret);

