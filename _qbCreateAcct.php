<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
$config = include('qbconfig.php');
include_once './lib/class.quickbookconnect.php';
$qbComIDconfig = include('_qbcompanyID.php');
    $Object = new Quickbookconnect();

    $EXPECTED = array('token','accounType','accountName');
    
    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        }else if (!empty($_GET[$key])) {
            ${$key} = $Object->protect($_GET[$key]);
        } else {
            ${$key} = NULL;
        }
    }

//$isAuth =$Object->basicAuth($token);
$isAuth=true;
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed','CreatedId'=>"");
    $Object->close_conn();
    echo json_encode($ret);
}else{
    //print_r($accessTokenKey); die();
    $ID='';
    $token_info= $Object->getQBToken();

    if(count($token_info)>0){
        $info=array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'accessTokenKey' =>$token_info['accessTokenKey'],
            'refreshTokenKey' => $token_info['refreshTokenKey'],
            'QBORealmID' =>$qbComIDconfig['QBORealmID'],
            'baseUrl' => $qbComIDconfig['baseUrl']
        );
        $ID = $Object->qbNewAcct($info,$accounType,$accountName);
        if(is_numeric($ID["CreatedId"])){
            $Object->qbInsertAccount($accountName,$accounType,$ID["CreatedId"]);
            $ret = array('ERROR'=>'','CreatedId'=>$ID["CreatedId"]);
        }else{
            $ret = array('ERROR'=>$ID["TheStatusCodeIs"],'TheHelperMessageIs'=>$ID["TheHelperMessageIs"],
                'TheResponseMessageIs'=>$ID["TheResponseMessageIs"],'CreatedId'=>'');
        }
    }

    $Object->close_conn();
    echo json_encode($ret);
}

