<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
    $config = include('qbconfig.php');
    include_once './lib/class.qbVendorAndCustomer.php';
    include_once './lib/class.quickbookconnect.php';
    //include_once   'qbClassVendorAndCustomer.php';
    $qbComIDconfig = include('_qbcompanyID.php');
    $Object = new Quickbookconnect();

    $EXPECTED = array('token',
        'jwt','private_key','GivenName','FamilyName','MiddleName');
    
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
    $ret = array('ERROR'=>'Authentication is failed','CreatedId'=>'');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    //print_r($accessTokenKey); die();
    $ret = array('ERROR'=>'','customerID'=>'');
    $ID='';
    $token_info= $Object->getQBToken();

    if(count($token_info)>0){
        $dataService=array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'accessTokenKey' =>$token_info['accessTokenKey'],
            'refreshTokenKey' => $token_info['refreshTokenKey'],
            'QBORealmID' =>$qbComIDconfig['QBORealmID'],
            'baseUrl' => $qbComIDconfig['baseUrl']
        );

        $CompanyName="IT com";

        $ObjectVC = new QBVendorAndCustomer();
        $rsl = $ObjectVC->qbCustomerSearch($dataService,$GivenName,$MiddleName,$FamilyName);

        if(is_numeric($rsl["customerID"]) && !empty($rsl["customerID"])){
            $ret = array('ERROR'=>'','customerID'=>$rsl["customerID"]);
        }else{
            $ret = array('ERROR'=>$rsl["TheStatusCodeIs"],'TheHelperMessageIs'=>$rsl["TheHelperMessageIs"],
                'TheResponseMessageIs'=>$rsl["TheResponseMessageIs"],'customerID'=>'');
        }
    }

    $Object->close_conn();
    echo json_encode($ret);
}

