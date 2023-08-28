<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.common.php';
    $Object = new Common();

    $EXPECTED = array('token','id','email','title','content','jwt','private_key');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed');
    }else{
        $isAuth = $Object->auth($jwt,$private_key);
        //$isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $ret = $Object->getEmail_ID($id);
            $create_by = $ret['contact_id'];
            //get contact name;
            $user_name =$Object->getContactNameByID($create_by);

            $p= stripos($email,";");
            $emails = array();
            if(is_numeric($p)){
               $emails = explode(";",$email);
            }else{
                $emails[]=$email;
            }

            foreach($emails as $to){
                $status = '';
                //check email
                if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                    $status = 'Bounce';
                }

                $domain = substr($to, strpos($to, '@') + 1);
                if  (!checkdnsrr($domain) !== FALSE) {
                    $status = 'Bounce';
                }

                $id_tracking = $Object->insertTrackingEmail($to,$title,$content,$create_by,$status);

                if(empty($status)){
                    $is_send =  $Object->mail_to($user_name,"",$to,$title,$content,$id_tracking);
                    if($is_send==1){
                        $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                    }
                }

            }
            //$subject = $ret['title'];
            //$content = $ret['content'];
            //$reciver=$ret['to'];

            $ret = array('AUTH'=>true,'ERROR'=>'','Send'=>'Success');

        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }
    }

    $Object->close_conn();
    echo json_encode($ret);




