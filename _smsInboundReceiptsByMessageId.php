<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    require __DIR__.'/vendor/autoload.php';
    include_once './lib/class.receiveSMS.php';


    $Object = new ReceiveSMS();

    $EXPECTED = array('token','message_id','jwt','private_key');
    
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
$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{

    // Configure HTTP basic authorization: BasicAuth
    $config = ClickSend\Configuration::getDefaultConfiguration()
        ->setUsername('bmhillman')
        ->setPassword('5162FF52-2790-E3B0-1756-AD937E76BFC5');

    $apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(),$config);
    //$message_id = "messageId_example"; // string | Message ID

    try {
        $result = $apiInstance->smsReceiptsByMessageIdGet($message_id);
        $result1 =json_decode($result,true);
        $http_code = $result1['http_code'];
        $response_code =$result1['response_code'];
        $data1 = $result1['data'];
    } catch (Exception $e) {
        echo 'Exception when calling SMSApi->smsReceiptsByMessageIdGet: ', $e->getMessage(), PHP_EOL;
    }
    //$ret = array('ERROR'=>'','info'=>$rsl);

    $Object->close_conn();
    echo json_encode($ret);
    http_response_code($code);
}

