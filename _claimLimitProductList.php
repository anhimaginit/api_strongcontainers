<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
$Object = new Claim();

$EXPECTED = array('token','jwt','private_key');

foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key]))
        ${$key} = $Object->protect($_POST[$key]);
    else
        ${$key} = NULL;
}

$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
    return;
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    $isAuth['AUTH'] =true;
    if($isAuth['AUTH']){
        //$acl = $isAuth['acl_list'];
        $ret1 = $Object->claimlimits_productList();

        $ret1 = array("list"=>$ret1, "ERROR"=>"",'AUTH'=>true);

    }else{
        $ret1 = array('ERROR'=>$isAuth['ERROR']);
    }


    $Object->close_conn();
    echo json_encode($ret1);
}
