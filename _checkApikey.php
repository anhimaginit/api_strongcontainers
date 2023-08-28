<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers:Access-Control-Allow-Origin,X-AUTH-TOKEN, Authorization, X-Requested-With,Access-Control-Allow-Headers,Content-Type');
include_once './lib/class.common.php';
$Object = new Common();

$EXPECTED = array('token');

foreach ($EXPECTED AS $key) {
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    } else {
        ${$key} = NULL;
    }
}

$headers = apache_request_headers();

$keytemp = $headers['X-AUTH-TOKEN'];
$key = base64_decode($keytemp);


//print_r($key);
if($key=='214a2036199e47ede48b7e468c796db5-us19'){
    print_r("good");
    echo json_encode(array("SUCCESS"=>$key));
}else{
    echo json_encode(array("SUCCESS"=>$headers["X-AUTH-TOKEN"]));
}

//die();

$auth = base64_encode( 'user:anhho' );
$data = array(
    'apikey' => '214a2036199e47ede48b7e468c796db5-us19'

);

$key ='214a2036199e47ede48b7e468c796db5-us19';

$json_data = json_encode($data);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://us19.api.mailchimp.com/3.0/?apikey='.$key);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
    'Authorization: Basic '.$auth));
curl_setopt($curl, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
//curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);

// You can also bunch the above commands into an array if you choose using: curl_setopt_array

// Send the request
$result = curl_exec($curl);

// Get some cURL session information back
$info = curl_getinfo($curl);
//echo 'content type: ' . $info['content_type'] . '<br />';
echo 'http code: ' . $info['http_code'] . '<br />';

curl_close($curl);

//echo $result;





