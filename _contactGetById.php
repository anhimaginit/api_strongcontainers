<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
include_once './lib/class.contact.php';
    $Object = new Contact();

    $EXPECTED = array('token','ID');

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
        $ret = array('AUTH'=>false,'ERROR'=>'Authentication is failed');
    }else{
        $ret = $Object->getContact_ID($ID);
        $api_domain = $Object->api_domain;
        $driver_avatar_path = $api_domain.$Object->driver_avatar;
        //$driver_avatar_path ='C:/xampp/htdocs/CRMAPI/'.$Object->driver_avatar;
        //$target_path = $driver_avatar_path.$f_name;
        if($ret[0]["driver_avatar"] !='' && $ret[0]["driver_avatar"] !=null){
            $ret[0]["driver_avatar"] = $driver_avatar_path.$ret[0]["driver_avatar"];
        }

        $ret[0]["doc"] =$Object->getContactDoc_ID($ID);
        $ret[0]["track_mail"] =$Object->getTrackEmail_contactID($ID);

    }

    $Object->close_conn();
    echo json_encode($ret);

