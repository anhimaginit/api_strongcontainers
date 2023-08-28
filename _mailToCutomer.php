<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
    $Object = new Claim();

    $EXPECTED = array('token','claimID','name','email','subject','private_key','type','ID');

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
        $errObj = $Object->validate_send_email($ID);
        $content =$_POST['content'];
        $errObj['error']=false;
        if(!$errObj['error']){
            $ret = array('sent'=>'false','ERROR'=>'');
            $content =$_POST['content'];

            $sendBy =$Object->getContact_ID($private_key);
            $sendByName =$sendBy[0]['customer_name'];
            //content
            $Ob_manager = new EmailAdress();
            $domain_path = $Ob_manager->domain_path;
            //'type','ID'  , , order, invoice,
           // $html =$domain_path."/#ajax/claim-form.php?id=".$ID;
            if($type =='warranty'){
                $html =$domain_path."/#ajax/warranty-form.php?id=".$ID;
            }elseif($type =='claim'){
                $html =$domain_path."/#ajax/claim-form.php?id=".$ID;
            }elseif($type =='order'){
                $html =$domain_path."/#ajax/order-form.php?id=".$ID;
            }elseif($type =='invoice'){
                $html =$domain_path."/#ajax/invoice-form.php?id=".$ID;
            }elseif($type =='contact'){
                $html =$domain_path."/#ajax/contact-form.php?id=".$ID;
            }elseif($type =='help desk'){
                $html ="";
            }else{
                $html ="";
            }

            $content_b = '<p>'.$content. '</p>';

            $content_b .= '<p>'.$html. '</p>';
            $receiver = $name;
            //send mail
            $email = explode(";",$email);
            foreach($email as $to){
                $status="";
                //check email
                if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                    $status = 'Bounce';
                }

                $domain = substr($to, strpos($to, '@') + 1);
                if  (!checkdnsrr($domain) !== FALSE) {
                    $status = 'Bounce';
                }

                $id_tracking = $Object->insertTrackingEmail($to,$subject,$content_b,$private_key,$status,'');

                if(empty($status)){
                    $is_send =  $Object->mail_to($sendByName,$receiver,$to,$subject,$content_b,$id_tracking);
                    if($is_send==1){
                        $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                        $ret = array('sent'=>'SUCCESS','ERROR'=>'');
                    }
                }

            }

        }else{
            $ret = array('sent'=>false,'ERROR'=>$errObj['errorMsg']);
        }

    }


    $Object->close_conn();
    echo json_encode($ret);





