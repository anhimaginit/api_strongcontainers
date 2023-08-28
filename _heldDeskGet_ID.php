<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.helpdesk.php';
    $Object = new Helpdesk();

    $EXPECTED = array('token','id');

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
    $ret = array('ERROR'=>'Authentication is failed','list'=>array());
}else{
    $result = $Object->getHelpDesk_ID($id);
    $notes=$Object->getNote("Help Desk",$id);
    $ret = array('list'=>$result,'notes'=>$notes,'ERROR'=>'');
}
    $Object->close_conn();
    echo json_encode($ret);




