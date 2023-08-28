<?php
 $origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/class.login.php';

    $Object = new Login();

    $EXPECTED = array('token','refresh_token','private_key');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }



    $isAuth =$Object->basicAuth($token);
    if(!$isAuth || empty($refresh_token)){
        $ret = array('AUTH'=>false,'ERROR'=>'Authentication is failed');
    }else{
        $rst_refresh_token = $Object->resetFreshToken($refresh_token,$private_key);
        if(isset($rst_refresh_token["ERROR"])){
            $ret = array('AUTH'=>false,'ERROR'=>$rst_refresh_token["ERROR"]);
        }else{
            $jwt_refresh = $Object->resetToken($refresh_token,$private_key);
            
            if(isset($jwt_refresh["ERROR"])){
                $ret = array('AUTH'=>false,'ERROR'=>$jwt_refresh["ERROR"]);
            }else{
                $ret = array('AUTH'=>true,'ERROR'=>'','jwt_refresh'=>$rst_refresh_token,'jwt'=>$jwt_refresh);
            }
        }

    }

    $Object->close_conn();
    echo json_encode($ret);



