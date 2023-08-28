<?php 
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');
$API_KEY ='AIzaSyA_8dzdQbvGq7bLBmg8qIBBGpnW284EwjU';
//$API_KEY = 'AIzaSyAqXCluATPscHI8jmK_zaREVboAnnwaiig';
$url = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/getOobConfirmationCode?key='.$API_KEY;

$email = $_POST["email"];

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $fields = array (
        "requestType" => "PASSWORD_RESET",  
        "email" => $email
    );

    $fields = json_encode ( $fields );

    $headers = array ('Content-Type: application/json');

    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_POST, true );
    curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );

    $result = curl_exec ( $ch );
    echo $result;
    curl_close ( $ch );

}else{
    $myObj = (object) [
        'error'=>[
            'message' => "Email address '".$email."' is considered invalid.",
            'code' => 400
        ]        
    ];
   
    $myJSON = json_encode($myObj);        
    echo $myJSON;
}


/*
Sample response
{
 "kind": "identitytoolkit#GetOobConfirmationCodeResponse",
 "email": "[user@example.com]"
}
*/

?>




