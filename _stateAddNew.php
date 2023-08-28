<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/class.state.php';

    $Object = new State();
    $EXPECTED = array('token', 'city','state','zip','state_name','jwt','private_key');
    
    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed','SAVE'=>'failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    if($isAuth['AUTH']){
        $errObj = $Object->validate_state_fields($city,$state,$zip);
        if(!$errObj['error']){
           $rsl = $Object->addNewState($city,$state,$state_name,$zip);
            if($rsl==1){
                $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'');
            }else{
                $ret = array('AUTH'=>true,'SAVE'=>'failed','ERROR'=>$rsl);
            }
        }else{
            $ret = array('AUTH'=>true,'ERROR'=>$errObj['error'],'SAVE'=>'failed');
        }
    }else{
        $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR'],'SAVE'=>'failed');
    }

    $Object->close_conn();
    echo json_encode($ret);
}

