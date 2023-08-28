<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.receiveSMS.php';


$Object = new ReceiveSMS();

$body = $_POST['body'];
$message_id = $_POST['message_id'];
$orginal_body = $_POST['original_body'];
$original_msg_id = $_POST['original_message_id'];
$to = $_POST['to'];
$timestamp = $_POST['timestamp'];
$from = $_POST['from'];
$user_id = $_POST['user_id'];
$subaccount_id = $_POST['subaccount_id'];

/*$inbound_json_arr = array('body'=>$body,
                           'message_id '=>$message_id,
                           'orginal_body'=>$orginal_body,
                           'original_msg_id'=>$original_msg_id,
                           'to' => $to,
                           'timestamp' => $timestamp,
                           'from' => $from,
                           'user_id' => $user_id,
                           'subaccount_id' => $subaccount_id
                        );*/



$api_response = json_encode($_POST);
//$api_response = json_encode($inbound_json_arr);

$rsl= $Object->inbound_bak_get($api_response,$message_id,$body,$from,$to,'inboundget',$orginal_body,$timestamp,$original_msg_id);
//$rsl= $Object->inbound_bak_get($message_id,$body,$from,$to,'inboundget',$orginal_body,$timestamp,$original_msg_id);
$ret = array('ERROR'=>'','response_code'=>$response_code,'info'=>$data1,'ids'=>$rsl);
//$ret = array('ERROR'=>'','info'=>$rsl);
$code=200;
$Object->close_conn();
echo json_encode($ret);
http_response_code($code);


