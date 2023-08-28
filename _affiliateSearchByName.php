<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/class.affiliate.php';

    $Object = new Affiliate();
    $EXPECTED = array('token','affilate_name');


    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed','list'=>array());
        $Object->close_conn();
        echo json_encode($ret);
    }else{
        $result = $Object->searchAffilitate($affilate_name);
        $Object->close_conn();

        $ret = array('ERROR'=>'','list'=>$result);
        echo json_encode($ret);
        
    }

