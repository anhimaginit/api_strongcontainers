<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
$Object = new Claim();

$EXPECTED = array('token','contact_name');

foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key]))
        ${$key} = $Object->protect($_POST[$key]);
    else
        ${$key} = NULL;
}

$list = array();
$isAuth =$Object->basicAuth($token);

if(!$isAuth){
    $ret = array("list"=>[],'ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    $list = $Object->getEmployee($contact_name);
    $Object->close_conn();

    $ret = array("list"=>$list,'AUTH'=>true);


    echo json_encode($ret);
}


