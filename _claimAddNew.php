<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/emailaddress.php';
include_once './lib/class.claim.php';
    $Object = new Claim();

    $EXPECTED = array('token','UID','warranty_ID','create_by','user_name',
    'customer','invoice_amount','invoice_date','invoice_flag','please_pay_flag',
        'total','quote_flag','quote_amount','quote_date','quote_number','vendor_invoice_number',
        'warranty_start_date','note','jwt','private_key');

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
            $errObj = $Object->validate_claim_fields_add($warranty_ID,$customer);
            if(!$errObj['error']){
                $notes =array();
                if(isset($_POST['notes'])){
                    $notes=$_POST['notes'];
                }

                if(empty($total) || !is_numeric($total)) $total=0;
                if(empty($invoice_flag) || !is_numeric($invoice_flag)) $invoice_flag=0;
                if(empty($quote_flag) || !is_numeric($quote_flag)) $quote_flag=0;
                if(empty($please_pay_flag) || !is_numeric($please_pay_flag)) $please_pay_flag=0;
                if(empty($invoice_amount)) $invoice_amount=0;
                if(empty($quote_amount)) $quote_amount=0;
                if(empty($quote_number)) $quote_number =0;
                if(empty($vendor_invoice_number)) $vendor_invoice_number =0;
                //get task template
                $tasks = $Object->addTaskTemp_taskTable($create_by);

                $taskIDs = json_encode($tasks["taskIDs"]);

                $result = $Object->addClaim($UID,$warranty_ID,$create_by,$taskIDs,$notes,$customer,$note);

                //$admin = base64_encode("Admin");
                $id = base64_encode($result);

                $status = '';
                if(is_numeric($result) && $result){
                    //check Warranty for updating
                    if(empty($warranty_start_date)){
                        $Object->updateStartDateforWarranty($warranty_ID);
                    }
                    // for admin
                    $Ob_manager = new EmailAdress();
                    $to =$Ob_manager->admin_email;
                    $domain_path = $Ob_manager->domain_path;
                    //check email
                    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                        $status = 'Bounce';
                    }

                    $domain = substr($to, strpos($to, '@') + 1);
                    if  (!checkdnsrr($domain) !== FALSE) {
                        $status = 'Bounce';
                    }

                    $html =$domain_path."/#ajax/claim-form.php?id=".$id;
                    $subject ='Claim was submited by: '.$user_name;
                    $content = '<p>Dear '.$Ob_manager->admin_name. '</p>';
                    $content .='<a href="'.$html.'">Click here to access claim</a>';
                    $receiver = $Ob_manager->admin_name;
                    $id_tracking = $Object->insertTrackingEmail($to,$subject,$content,$create_by,$status);

                    if(empty($status)){
                        $is_send =  $Object->mail_to($user_name,$receiver,$to,$subject,$content,$id_tracking);
                        if($is_send==1){
                            $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                        }
                    }

                    //send email to customer when a claim is filed
                    $customer_info =$Object->getContact_ID($customer);
                    $to =$customer_info[0]['primary_email'];
                    //check email
                    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                        $status = 'Bounce';
                    }

                    $domain = substr($to, strpos($to, '@') + 1);
                    if  (!checkdnsrr($domain) !== FALSE) {
                        $status = 'Bounce';
                    }

                    $html =$domain_path."/#ajax/claim-form.php?id=".$id;
                    $subject ='Claim was submited by: '.$user_name;
                    $content = '<p>Dear Sir/Madam</p>';
                    $content .='<a href="'.$html.'">Click here to access claim</a>';
                    $content .='<p>You have opened a claim</p>';


                    $receiver = $customer_info[0]['customer_name'];
                    $id_tracking = $Object->insertTrackingEmail($to,$subject,$content,$create_by,$status);

                    if(empty($status)){
                        $is_send =  $Object->mail_to($user_name,$receiver,$to,$subject,$content,$id_tracking);
                        if($is_send==1){
                            $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                        }
                    }

                    //create invoice and order
                    $total=65;$order_create_by=$private_key;
                    $order_title ="Claim-#".$result;
                    $rsl=$Object->createOrderID_cl($customer,$total,$order_title,$order_create_by,$result);
                    $total_overage =$Object->getOverage_contactID($create_by);
                    $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result,'AUTH'=>true,
                    'orderID'=>$rsl['orderID'],'invID'=>$rsl['invID'],'total_overage'=>$total_overage);
                } else {
                    $info ="Claim -- UID: ".$UID.
                        ", warranty_ID: ".$warranty_ID. ",create_by: ".$create_by.
                        ",err: ".$result;

                    $Object->err_log("Claim",$info,0);

                    $ret = array('SAVE'=>'FAIL','ERROR'=>'System can not add the Claim.','AUTH'=>true);

                }
            }else{
                $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>true);

            }

        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }

    $Object->close_conn();
    echo json_encode($ret);




