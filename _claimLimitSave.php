<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
    $Object = new Claim();

    $EXPECTED = array('token','ID','limits','product_ID','jwt','private_key');

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
        //--- validate
        $isAuth = $Object->auth($jwt,$private_key);
        $isAuth['AUTH'] =true;
        if($isAuth['AUTH']){
            //$acl = $isAuth['acl_list'];
            //--- validate
            $errObj = $Object->validate_clLimits_fields($limits);

            if(!$errObj['error']){
                if(empty($ID)){
                    $result = $Object->AddClaimLimits($product_ID,$limits);

                    if(is_numeric($result) &&  $result){
                        $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result);
                    } else {
                        $ret = array('SAVE'=>'FAIL','ERROR'=>$result);
                    }

                }else{
                    $result = $Object->updateClaimLimits($ID,$limits);
                    //die($result);
                    if(is_numeric($result)) {
                        $ret = array('SAVE'=>'SUCCESS','ERROR'=>'');
                    }else{
                        $ret = array('SAVE'=>'FAIL','ERROR'=>$result);
                    }
                }

            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>true);
            }

        }else{
            $ret = array('SAVE'=>'FAIL','AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }
    }

    $Object->close_conn();
    echo json_encode($ret);





