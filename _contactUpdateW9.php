<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
include_once './lib/class.contact.php';
    $Object = new Contact();

    $EXPECTED = array('token','contactID','w9_exp','jwt','private_key');

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
        $isAuth = $Object->auth($jwt,$private_key);
        if($isAuth['AUTH']){
            $rsl = $Object->updateW9Contact_ID($contactID,$w9_exp);
            $ret = array('SAVE'=>true,'AUTH'=>true,'ERROR'=>$rsl);
        }else{
            $ret = array('SAVE'=>false,'AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }

    $Object->close_conn();
    echo json_encode($ret);

