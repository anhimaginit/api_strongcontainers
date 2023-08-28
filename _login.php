<?php
 $origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/emailaddress.php';
    include_once './lib/class.login.php';

    $Object = new Login();
    $user_agent="";
    if(isset($_SERVER['HTTP_USER_AGENT'])){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    $EXPECTED = array('token','primary_email','primary_phone','primary_postal_code','login_type','ip','user_name','pass','type');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('AUTH'=>false,'ERROR'=>'Authentication is failed','Token'=>false);
    }else{
        //if($type =="PolicyHolder")  $type ="Policy Holder";
        $rsl = $Object->loginEmailPass($login_type,$primary_email,$primary_phone,$primary_postal_code,$user_name,$pass,$type);

        $type_login="";
        if(!empty($primary_email)){
            $type_login=$primary_email;
        }elseif(!empty($primary_phone)){
            $type_login=$primary_phone;
        }elseif(!empty($user_name)){
            $type_login=$user_name;
        }

        //print_r($rsl);die();

        if(count($rsl)>0){
            $Object->log("login",$user_agent,$type_login,$ip);

            $had_login =$Object->getLogLogin($user_agent,$type_login);
            if(empty($had_login)){
                $Ob_manager = new EmailAdress();
                $from_email =$Ob_manager->admin_email;
                $Object->sendEmail($user_agent,$type_login, $ip,$from_email);
            }

            $ret = array('AUTH'=>true,'contact'=>$rsl[0]);
        }else{
            $Ob_manager = new EmailAdress();
            $from_email =$Ob_manager->admin_email;
            $exsiting=$Object->exsitingEmail($primary_email);
            if($login_type==5 && empty($exsiting)){
                // System send email
                $from_name = $Ob_manager->admin_name;
                $from_id = $Ob_manager->admin_id;
                $domain_path = $Ob_manager->domain_path;

                $Object->email_register($primary_email,$from_email,$from_name,$from_id,$domain_path);

                $ret = array("AUTH"=>false,"ERROR"=>"The email isn't ready. We have just sent you a registration email" ,"exsiting"=>$exsiting);
            }else{
                $ret = array('AUTH'=>false,'ERROR'=>'Authentication is failed','exsiting'=>$exsiting);
            }

        }

    }

    $Object->close_conn();
    echo json_encode($ret);



