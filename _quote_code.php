<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.depot.php';
    $Object = new Depot();

    $EXPECTED = array('token','code');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    //--- validate
$isAuth =true;//$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('error'=>'Authentication is failed');
}else{
    $ret = $Object->getQuoteTemp($code);
    $ret["more_info"]= $Object->quoteTemp($code);
}
    $Object->close_conn();
    echo json_encode($ret);




