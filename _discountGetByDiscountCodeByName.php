<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

           
include_once './lib/class.common.php';
    $Object = new Common();

    $EXPECTED = array('token','discount_name');

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
    $ret = array('item'=>'','ERROR'=>'Authentication is failed');
}else{
    $rsl = $Object->getDiscount_name($discount_name);

    $ret = array('item'=>$rsl,'ERROR'=>'');
}
    $Object->close_conn();
    echo json_encode($ret);




