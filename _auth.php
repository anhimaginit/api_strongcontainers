<?php
 $origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/class.login.php';

    $Object = new Login();

    $EXPECTED = array('token','jwt','private_key');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }



    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('AUTH'=>false,'ERROR'=>'Authentication is failed');
    }else{
        $userType ="";
        $ret = $Object->auth($jwt,$private_key);
        //if(isset($ret['acl_list']))  $userType = $Object->acl($ret['acl_list'], "ContactForm");
        //print_r($ret);
        //die();
    }

    $Object->close_conn();
    echo json_encode($ret);



