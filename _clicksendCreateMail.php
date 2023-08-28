<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

require __DIR__.'/lib//vendor/autoload.php';
include_once './lib/class.receiveSMS.php';
$Object = new ReceiveSMS();

$EXPECTED = array('jwt','private_key','email');
$Object = new ReceiveSMS();

foreach ($EXPECTED AS $key) {
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    }else if (!empty($_GET[$key])) {
        ${$key} = $Object->protect($_GET[$key]);
    } else {
        ${$key} = NULL;
    }
}

$http_code=200;
//$isAuth =$Object->basicAuth($token);
$isAuth=true;
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
    //$Object->close_conn();
    echo json_encode($ret);
}else{

    //$data = json_decode($email);

    /*$config = ClickSend\Configuration::getDefaultConfiguration()
        ->setUsername('bmhillman')
        ->setPassword('5162FF52-2790-E3B0-1756-AD937E76BFC5');



    $apiInstance = new ClickSend\Api\EmailMarketingApi(new GuzzleHttp\Client(),$config);
    $email_address = new \ClickSend\Model\EmailAddress();
    $email_address->setEmailAddress($email);

    try {
        $result = $apiInstance->allowedEmailAddressPost($email_address);
        $result = json_decode($result,true);
        $http_code = $result['http_code'];
        if($http_code==200){

            $ret = array('ERROR'=>'','result'=>'success');
        }

    } catch (Exception $e) {
        $ret = array('ERROR'=>$e->getMessage(),'result'=>'failed');
    }

    $Object->close_conn();*/

    //echo $email;
    $mailArray = json_decode($_POST['email'], true);
    //print_r($someArray);
    foreach($mailArray as $mail) {
        echo $mail['email'];
    }

    http_response_code($code);
}

