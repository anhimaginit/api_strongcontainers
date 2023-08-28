<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
$Object = new Claim();

    $EXPECTED = array('token','ID','jwt','private_key');

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
    }else{
        $isAuth = $Object->auth($jwt,$private_key);
        $isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $ret_temp = $Object->getClaimTransaction($ID);
            $ret = array('ERROR'=>'','ClaimTransaction'=>$ret_temp,'AUTH'=>true);
        }else{
            $ret = array('ERROR'=>'','Claim'=>'','AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }


    }

    $Object->close_conn();
    echo json_encode($ret);

