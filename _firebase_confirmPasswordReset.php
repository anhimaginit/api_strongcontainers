<?php 
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$API_KEY = 'AIzaSyAqXCluATPscHI8jmK_zaREVboAnnwaiig';
$url = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/resetPassword?key='.$API_KEY;

$oobCode = $_POST['oobCode'];
$newPassword = $_POST['newPassword'];

if(strlen($oobCode)>=4)
{
    if(strlen($newPassword)>=4)
    {
        
        $fields = array (
            'oobCode' => $oobCode,
            'newPassword' => $newPassword
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
                'message' => "New password is considered invalid.",
                'code' => 400
            ]
        ];
       
        $myJSON = json_encode($myObj);   
        echo $myJSON;
    }

}else{
    $myObj = (object) [
        'error'=>[
            'message' => "oobCode is considered invalid.",
            'code' => 400
        ]
    ];
   
    $myJSON = json_encode($myObj);
    echo $myJSON;
}

/*
Sample response
{
  "kind": "identitytoolkit#ResetPasswordResponse",
  "email": "[user@example.com]",
  "requestType": "PASSWORD_RESET"
}
*/

?>




