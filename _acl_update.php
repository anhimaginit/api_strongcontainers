<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.acl.php';

$Object = new ACL();
$EXPECTED = array('token','g_id','u_id',
    'jwt','private_key');

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
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    if($isAuth['AUTH']){
        $acl =$_POST['acl'];
        $ret = $Object->updateACL($g_id,$private_key,$acl);
    }else{
        $ret = array('ERROR'=>'Authentication is failed');
    }
}

$Object->close_conn();
echo json_encode($ret);
//http_response_code($code);




