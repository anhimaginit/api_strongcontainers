<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.contact.php';
    $Object = new Contact();

    $EXPECTED = array('token',
        'primary_email','ID');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed');
        $Object->close_conn();
        echo json_encode($ret);
    }else{
        if(!empty($ID)){
            $result = $Object->existingEmail_User($primary_email,$ID);
        }else{
            $result = $Object->existingEmail_User($primary_email);
        }

        $Object->close_conn();
        echo json_encode($result);
    }



