<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.depot.php';
    $Object = new Depot();

    $EXPECTED = array('token','email','phone');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    //--- validate
$isAuth = true;//$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('error'=>'Authentication is failed');
}else{
    $data =array() ;
    if(isset($_POST["data_post"])) $data= $_POST["data_post"];
    //print_r($data); die();
    $ret = $Object->add_quote_temp($email,$phone,$data);
    if(is_numeric($ret["quote_temp_id"]) && !empty($ret["quote_temp_id"])){
        $status ='';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $status = 'Bounce';
        }
        //check email
        $domain = substr($email, strpos($email, '@') + 1);
        if  (!checkdnsrr($domain) !== FALSE) {
            $status = 'Bounce';
        }

        if(empty($status)){
            //get admin info
            $Ob_manager = new EmailAdress();
            $domain_path = $Ob_manager->domain_path;
            $from_name=$Ob_manager->admin_name ;
            $from_email=$Ob_manager->admin_email;
            $from_id=$Ob_manager->admin_id;

            $hrf =$domain_path."/quote.php?id=".$ret["code"];
            $body ='<p>Hi</p>';
            $body .='<p><a href='.$hrf.' > Click here to create your order</a></p>';
            $body .='<p></p>';
            $body .='<p>Thank</p>';

            $subject ="Quote";
            $to_name = "Customer";
            $is_send =0;
            $is_send =  $Object->mail_to($from_name,$to_name,$email,$subject,$body);
            $ret["email_sent"] =$is_send;
        }
    }
}
    $Object->close_conn();
    echo json_encode($ret);




