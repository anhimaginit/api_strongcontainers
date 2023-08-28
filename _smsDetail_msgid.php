<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/class.receiveSMS.php';


    $Object = new ReceiveSMS();

    $EXPECTED = array('ID','jwt','private_key');
    
    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        }else if (!empty($_GET[$key])) {
            ${$key} = $Object->protect($_GET[$key]);
        } else {
            ${$key} = NULL;
        }
    }

//$isAuth =$Object->basicAuth($token);
$isAuth=true;
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    $rsl= $Object->getInboundSMS_MsgID($ID);
    $ret = array('ERROR'=>'','detail'=>$rsl);

    $Object->close_conn();
    echo json_encode($ret);
}

