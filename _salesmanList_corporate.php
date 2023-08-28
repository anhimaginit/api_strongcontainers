<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/class.salesman.php';

    $Object = new Salesman();
    $EXPECTED = array('token','state','corporate');


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
        $Object->close_conn();
        echo json_encode($ret);
    }else{
        $result = $Object->getSalesmanList($state,$corporate);
        $Object->close_conn();
        echo json_encode($result);
    }

