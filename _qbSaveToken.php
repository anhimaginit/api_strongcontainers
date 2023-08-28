<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
    include_once './lib/class.quickbookconnect.php';
    $Object = new Quickbookconnect();

    $EXPECTED = array('token','phone','accessTokenKey','refreshTokenKey',
        'jwt','private_key');
    
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
    //print_r($accessTokenKey); die();
    $ID= $Object->checkForIns_UpdToken();
    if(!empty($ID)){
        $ID = $Object->updatetoken($ID,$accessTokenKey,$refreshTokenKey);

    }else{
        $ID = $Object->insertToken($accessTokenKey,$refreshTokenKey);
    }

    $ret = array('ERROR'=>'','ID'=>$ID);

    $Object->close_conn();
    echo json_encode($ret);
}

