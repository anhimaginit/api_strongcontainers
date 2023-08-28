<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    require __DIR__.'/lib/vendor/autoload.php';
    include_once './lib/class.receiveSMS.php';


    $Object = new ReceiveSMS();

    $EXPECTED = array('jwt','private_key');
    
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
    $Object->close_conn();
    echo json_encode($ret);
}else{

    // Configure HTTP basic authorization: BasicAuth
    $config = ClickSend\Configuration::getDefaultConfiguration()
        ->setUsername('Trinh')
        ->setPassword('E0A992D3-4F57-7662-B451-AE624FEA81C6');

    $apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(),$config);
    $q = "q_example"; // string | Your keyword or query.
    $page = 1; // int | Page number
    $limit = 100; // int | Number of records per page

    try {
        $result = $apiInstance->smsInboundGet($q, $page, $limit);
        //print_r($result);
        //die();
        $result1 =json_decode($result,true);
        $http_code = $result1['http_code'];
        $response_code =$result1['response_code'];
        $data1 = $result1['data'];
        //print_r($data1); die();
        if($http_code==200){
           $rsl= $Object->receive_smsInbound($data1,'inboundget');
            $ret = array('ERROR'=>'','response_code'=>$response_code,'info'=>$data1,
            'ids'=>$rsl);
        }


    } catch (Exception $e) {
        $ret = array('ERROR'=>$e->getMessage(),'response_code'=>'','info'=>'',
            'ids'=>array());
       // echo 'Exception when calling SMSApi->smsInboundGet: ', $e->getMessage(), PHP_EOL;
    }
    //$ret = array('ERROR'=>'','info'=>$rsl);

    $Object->close_conn();
    echo json_encode($ret);
    http_response_code($code);
}

