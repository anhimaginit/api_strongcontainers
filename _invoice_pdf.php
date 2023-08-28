<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.invoice_sendmail.php';
    $Object = new InvoiceMail();

    $EXPECTED = array('token','invoice_num','jwt','private_key');

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
    $ret = array('html'=>'','ERROR'=>'Authentication is failed');
}else{
    $isAuth = $Object->auth($jwt,$private_key);

    $isAuth['AUTH']=true;
    $errObj['errorMsg']="Authentication is failed";
    if($isAuth['AUTH']){
        //get admin info
        $result = $Object->getInvInfo_invID($invoice_num);
        $ret = array('html'=>$result,'AUTH'=>true);
    }else{
        $ret = array('html'=>'','AUTH'=>false);
    }
}
    $Object->close_conn();
    echo json_encode($ret);




