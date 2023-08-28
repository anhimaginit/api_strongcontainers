<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.mail.php';
$Object = new LocalEmail();

$EXPECTED = array('jwt','private_key','e_department','email_id','email');

foreach ($EXPECTED AS $key) {
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    }else if (!empty($_GET[$key])) {
        ${$key} = $Object->protect($_GET[$key]);
    } else {
        ${$key} = NULL;
    }
}

$http_code=200;
//$isAuth =$Object->basicAuth($token);
$isAuth=true;
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    $result = $Object->updateMailDepartment($e_department,$email_id,$email);

    if(is_numeric($result) &&  $result){
        $ret = array('SAVE'=>'SUCCESS','ERROR'=>'');
    } else {
    //    $Object->err_log("ClaimTask",$info,$id);

        $ret = array('SAVE'=>'FAIL','ERROR'=>$result);
    }
    $Object->close_conn();
    echo json_encode($ret);
    http_response_code($code);
}

