<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    require __DIR__.'/lib//vendor/autoload.php';
    include_once './lib/class.receiveSMS.php';
    $Object = new ReceiveSMS();

    $EXPECTED = array('senderID','receiverID','setBody','setTo','setSource','setFrom',
        'original_body','original_msg_id','send_out',
        'jwt','private_key','sms_api_username','sms_api_key');
    
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
        ->setUsername($sms_api_username)
        ->setPassword($sms_api_key);

    $apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(),$config);

    $msg = new \ClickSend\Model\SmsMessage();
    $msg->setBody($setBody);
    $msg->setFrom($setFrom);
    $msg->setTo($setTo);
    $msg->setSource($setSource);

  // \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
    $sms_messages = new \ClickSend\Model\SmsMessageCollection();
    $sms_messages->setMessages([$msg]);

    try {
        $result = $apiInstance->smsSendPost($sms_messages);
        $result = json_decode($result,true);
        $http_code = $result['http_code'];
        if($http_code==200){
            $msg_info = $result['data']['messages'];
            $type = "Out";
            //print_r($msg_info);
            //if($send_out==0) $type = "inboundget";
            if($send_out==1)
            $rsl= $Object->save_smsOutbound($msg_info,$type,$senderID, $receiverID,$original_body,$original_msg_id);
            $ret = array('ERROR'=>'','ids'=>$rsl);
        }

    } catch (Exception $e) {
        $ret = array('ERROR'=>$e->getMessage(),'ids'=>array());
        //echo 'Exception when calling SMSApi->smsSendPost: ', $e->getMessage(), PHP_EOL;
    }
    //$ret = array('ERROR'=>'','info'=>$rsl);
     //die();
    $Object->close_conn();
    echo json_encode($ret);
    http_response_code($code);
}

