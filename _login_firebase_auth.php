<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.login.php';

$Object = new Login();
$user_agent="";
if(isset($_SERVER['HTTP_USER_AGENT'])){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
}

$data = $_POST['data'];
$token = $data['token'];
$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('AUTH'=>false,'ERROR'=>'Authentication is failed');
}else{

    $rsl = $Object->loginFirebaseAuth($data);

    $type_login="";
    $email=$data['email'];
    $phoneNumber=$data['phoneNumber'];
    if(!empty($email)){
        $type_login=$email;
    }elseif(!empty($phoneNumber)){
        $type_login=$phoneNumber;
    }

    //print_r($rsl);die();
    $ip = $data['IP'];
    if(count($rsl)>0){
        $Object->log("login",$user_agent,$type_login,$ip);
        $had_login =$Object->getLogLogin($user_agent,$type_login);
        if(empty($had_login)){
            $Object->sendEmail($user_agent,$type_login, $ip);
        }
        $ret = array('AUTH'=>true,'contact'=>$rsl[0]);
    }else{
        $ret = array('AUTH'=>false,'ERROR'=>'Authentication is failed');
    }

}

$Object->close_conn();
echo json_encode($ret);