<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.acl.php';
    $Object = new ACL();

    $EXPECTED = array('token','group_id');

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
        $ret = array('acl'=>'','ERROR'=>'Authentication is failed');
    }else{
        $result = $Object->get_group_id($group_id);
        $ret = array('acl'=>$result,'ERROR'=>'');
    }
    $Object->close_conn();
    echo json_encode($ret);




