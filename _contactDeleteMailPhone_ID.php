<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.contact.php';
    $Object = new Contact();
    $EXPECTED = array('token','contactID','primary_email','primary_phone','jwt','private_key');

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
        $ret = array('DELETE'=>'','ERROR'=>'Authentication is failed');
    }else{
        $isAuth = $Object->auth($jwt,$private_key);

        if($isAuth['AUTH']){
            $rsl = $Object->deleteEmailOrPhone($contactID,$primary_email,$primary_phone);
            if(is_numeric($rsl) && !empty($rsl)){
                $ret = array('DELETE'=>'SUCCESS','AUTH'=>true,'ERROR'=>'');
            }else{
                $ret = array('DELETE'=>'failed','AUTH'=>true,'ERROR'=>$rsl);
            }
        }else{
            $ret = array('DELETE'=>'','AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }
    $Object->close_conn();
    echo json_encode($ret);





