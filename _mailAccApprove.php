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
        'warranty_total','vendor_invoice_number');

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
        $Ob_manager = new EmailAdress();
        $from_name = $Ob_manager->accountant_name;

        //get warranty info
        $waInf = $Object->getWarrantyInfo_ID($warranty_ID);
        $w_info = "\n More Information \n";
        $w_info = "Wanrranty Infomation \n";
        $w_info.= "Serial Number: ".$waInf['warranty_serial_number']."\n";
        $w_info.= "Policy Holder: ".$waInf['buyer']."\n";
        $w_info.= "Start date: ".$waInf['warranty_start_date']."\n";
        $w_info.= "End date: ".$waInf['warranty_end_date']."\n";
        $w_info.= "Closing date: ".$waInf['warranty_closing_date']."\n";
        $w_info.= "Address: ".$waInf['warranty_address1']."\n";

        //get vendor info
        $v_info.="\n Vendor Infomation \n";
        $vInf = $Object->getVendor_ID($UID);
        if(count($vInf)>0){
            foreach($vInf as $item){
                $w_info.="Name: ".$item['c_name']."\n";
            }
        }

        $w_info = "\n Invoice Infomation \n";
        $w_info.="Invoice number: ".$vendor_invoice_number."\n";
        $w_info.="Invoice date: ".$invoice_date."\n";
        $w_info.="Invoice amount: ".$invoice_amount."\n";
        $w_info.="Approve payment: ".$warranty_total."\n";
        $w_info.="Who is approved it?: ".$from_name."\n";

        $html ="http://warrantyproject.com/#ajax/claim-form.php?id=".$claim_ID;
        $subject ="Approve payment";
        $content = "Dear Sir/Madam";
        $content .=$html;
        $content .=$w_info;
        //send to claim assigned
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

        $id_tracking = $Object->insertTrackingEmail($to,$subject,$content,$claim_assign,$status);

        if(empty($status)){
            $is_send =  $Object->mail_to($from_name,$receiver,$to,$subject,$content,$id_tracking);
            if($is_send==1){
                $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
            }
        }

    }

    $Object->close_conn();
    echo json_encode($ret);




