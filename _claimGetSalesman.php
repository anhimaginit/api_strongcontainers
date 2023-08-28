<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
$Object = new Claim();

    $EXPECTED = array('token','salesman');

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
        $isAuth = $Object->validToken_logging($salesman);
        //print_r($isAuth['email']);die();
        if($isAuth['AUTH']){
            $primary_email=trim($isAuth['email']);
            include_once './lib/class.login.php';
            $Object_login = new Login();
            $rsl = $Object_login->loginEmail(5,$primary_email,"","","","","");

            unset($Object_login);
            if(count($rsl)>0){
                //get claim
                $ret_temp = $Object->getClaim_ID($isAuth["ID"]);
                if(count($ret_temp)>0){
                    $temp = json_decode($ret_temp[0]['warranty_claim_limit'],true);
                    $ret_temp[0]['warranty_claim_limit'] = $temp;
                    $ret_temp[0]['notes'] = $Object->getNotesByClaimsID($isAuth["ID"]);
                    $ret = array('ERROR'=>'','Claim'=>$ret_temp[0],'contact'=>$rsl[0]);
                }else{
                    $ret = array('ERROR'=>'','Claim'=>'','contact'=>'');
                }

                //log
                //$Object->log("login",$user_agent,$type_login,$ip);
                //$had_login =$Object->getLogLogin($user_agent,$type_login);
            }else{
                $ret = array('AUTH'=>false,'ERROR'=>"User isn't already");
            }
            //print_r($isAuth); die();

        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }

    $Object->close_conn();
    echo json_encode($ret);

