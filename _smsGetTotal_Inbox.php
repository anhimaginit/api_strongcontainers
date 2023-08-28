<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/class.receiveSMS.php';


    $Object = new ReceiveSMS();

    $EXPECTED = array('phone','contact_ID');
    
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
    $ret = array('ERROR'=>'Authentication is failed','list'=>array());
    $Object->close_conn();
    echo json_encode($ret);
}else{

    $rsl= $Object->getTotalInbox($phone,$contact_ID);

    //die();
    $ret = array('ERROR'=>'','list'=>$rsl);

    $Object->close_conn();
    //print_r($rsl);
    //die();
    echo json_encode($ret);
    //echo $rsl;
}

