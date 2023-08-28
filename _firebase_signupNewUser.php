<?php 
header('Content-Type: application/json');
$API_KEY = 'AIzaSyAqXCluATPscHI8jmK_zaREVboAnnwaiig';
$url = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/signupNewUser?key='.$API_KEY;

$email = $_GET["email"];
$password = $_GET["password"];

if(strlen($password)>=6)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fields = array (
            'email' => $email,
            'password' => $_GET["password"],
            'returnSecureToken'=>true
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
}else{
    $myObj = (object) [
        'error'=>[
            'message' => "Password is considered invalid.",
            'code' => 400
        ]
    ];
   
    $myJSON = json_encode($myObj);   
    echo $myJSON;
}

/* Sample response //_firebase_signupNewUser.php?email=ganuonglu2@yahoo.com.vn&password=123456
    ERORR
 */

?>
