<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/emailaddress.php';
include_once './lib/class.claim.php';
    $Object = new Claim();
    $EXPECTED = array('token','create_by','claim_assign','warranty_ID','claim_ID',
        'subject','body','UID','invoice_amount','invoice_date',
        'warranty_total','vendor_invoice_number','login_id');

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
        //get $data_quote=
        $data_quote=$_POST['data_quote'];
        //email content
        $my_html = $Object->claimEmailFormat($data_quote,$warranty_ID);
        $html =$domain_path."/#ajax/claim-form.php?id=".$claim_ID;
        $content = $body;
        $content .=' '.$my_html;

        $Ob_manager = new EmailAdress();
        $domain_path = $Ob_manager->domain_path;
        //accountant info
        $from_info=$Object->getContact_ID($login_id);
        $from_name = $from_info[0]['customer_name'];

        //send to claim assigned
        $status='';
        $create_by_info =$Object->getContact_ID($claim_assign);
        $receiver =$create_by_info[0]['customer_name'];
        $to =$create_by_info[0]['primary_email'];
        //check email
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $status = 'Bounce';
        }

        $domain = substr($to, strpos($to, '@') + 1);
        if  (!checkdnsrr($domain) !== FALSE) {
            $status = 'Bounce';
        }
        $content1 =$content;
        $content1 = $Object->protect($content1);
        $id_tracking = $Object->insertTrackingEmail($to,$subject,$content1,$login_id,$status);

        if(empty($status)){
            $is_send =  $Object->mail_to($from_name,$receiver,$to,$subject,$content,$id_tracking);
            if($is_send==1){
                $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
            }
        }

    }
$ret =array("send"=>true);
    $Object->close_conn();
    echo json_encode($ret);




