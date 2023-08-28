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
        'jwt','private_key','Line1','City','CountrySubDivisionCode','PostalCode','GivenName',
        'FamilyName','PrimaryPhone','PrimaryEmailAddr','MiddleName','CompanyName','TaxIdentifier','Id');
    
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
    $ret = array('ERROR'=>'','CreatedId'=>'');
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
        $Country="U.S.A";

        $ObjectVC = new QBVendorAndCustomer();
        $ID = $ObjectVC->qbUpdateVendor($dataService,$Id,
        $Line1,$City,$Country,$CountrySubDivisionCode,$PostalCode,
                             $GivenName,$FamilyName,$PrimaryPhone,$PrimaryEmailAddr,
                             $MiddleName,$CompanyName,$TaxIdentifier);

        if(is_numeric($ID["CreatedId"])){
            $ret = array('ERROR'=>'','CreatedId'=>$ID["CreatedId"]);
        }else{
            $ret = array('ERROR'=>$ID["TheStatusCodeIs"],'TheHelperMessageIs'=>$ID["TheHelperMessageIs"],
                'TheResponseMessageIs'=>$ID["TheResponseMessageIs"],'CreatedId'=>'');
        }
    }

    $Object->close_conn();
    echo json_encode($ret);
}

