<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/class.mergeitem.php';

    $Object = new Mergeitem();

    $EXPECTED = array('token','jwt','private_key');
    
    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        }else if (!empty($_GET[$key])) {
            ${$key} = $Object->protect($_GET[$key]);
        } else {
            ${$key} = NULL;
        }
    }

$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed','contact_merge'=>'');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    if($isAuth['AUTH']){
        $data =$_POST['data_merge'];
        $rsl = $Object->mergeClaim($data);
        $ret = array('ERROR'=>'','merge'=>$rsl);
    }else{
        $ret = array('ERROR'=>'Authentication is failed','contact_merge'=>'');
    }

    $Object->close_conn();
    echo json_encode($rsl);
}

