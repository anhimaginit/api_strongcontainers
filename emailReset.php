<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/emailaddress.php';
include_once './lib/class.contact.php';
    $Object = new Contact();

    $EXPECTED = array('email');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    if(!empty($email)){
        $exsiting = $Object->existingAccount($email);
        $ret = array('Auth'=>true,'Email'=>true);
        if(empty($exsiting)){
            $Ob_manager = new EmailAdress();
            $from_email =$Ob_manager->admin_email;
            $from_name = $Ob_manager->admin_name;
            $from_id = $Ob_manager->admin_id;
            $domain_path = $Ob_manager->domain_path;

            $Object->email_register($email,$from_email,$from_name,$from_id,$domain_path);
            $ret = array('Auth'=>true,'Email'=>false);
        }
    }else{
        $ret = array('Auth'=>false);
    }

    echo json_encode($ret);




