<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.contact.php';

    $Object = new Contact();
    $EXPECTED = array('token','contact_id');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }
    //die();
    //--- validate
$isAuth =$Object->basicAuth($token);
$code=200;
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    $ret = $Object->get_driver($contact_id);
    $ret['pay_list']  = $Object->get_total_payment_driver($contact_id);
    $api_domain = $Object->api_domain;
    $driver_avatar_path = $api_domain.$Object->driver_avatar;
    //$driver_avatar_path ='http://localhost/CRMAPI/'.$Object->driver_avatar;

    if(isset($ret["driver_avatar"])){
        if($ret["driver_avatar"] !='' && $ret["driver_avatar"] !=null){
            $ret["driver_avatar"] = $driver_avatar_path.$ret["driver_avatar"];
        }
    }

}
    $Object->close_conn();
    echo json_encode($ret);
    http_response_code($code);




