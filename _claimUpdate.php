<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/emailaddress.php';
include_once './lib/class.claim.php';
    $Object = new Claim();
    $EXPECTED = array('token','ID','UID','warranty_ID','create_by','login_by',
    'paid','status','jwt','private_key',
        'customer','invoice_amount','invoice_date','invoice_flag','please_pay_flag',
        'warranty_total','quote_flag','quote_amount','quote_date',
    'quote_number','vendor_invoice_number','claim_assign','claim_limit','inactive',
    'note');

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
        if(!empty($ID)){
            $isAuth = $Object->auth($jwt,$private_key);
            $isAuth['AUTH']=true;
            if($isAuth['AUTH']){
                //$acl = $isAuth['acl_list'];

                $errObj = $Object->validate_claim_fields_update($warranty_ID,$UID);
                if(!$errObj['error']){
                    $notes =array();
                    if(isset($_POST['notes'])){
                        $notes=$_POST['notes'];
                    }


                    if(empty($_POST['claim_limit'])){
                        $claim_limit='[]';
                    } /*else{
                        $claim_limit = stripslashes($_POST['claim_limit']);
                    }*/

                    if(empty($warranty_total) || !is_numeric($warranty_total)) $warranty_total=0;
                    if(empty($invoice_flag) || !is_numeric($invoice_flag)) $invoice_flag=0;
                    if(empty($invoice_amount)) $invoice_amount =0;
                    if(empty($quote_flag) || !is_numeric($quote_flag)) $quote_flag=0;
                    if(empty($quote_number)) $quote_number =0;
                    if(empty($quote_amount)) $quote_amount =0;

                    if(empty($please_pay_flag) || !is_numeric($please_pay_flag)) $please_pay_flag=0;
                    if(empty($vendor_invoice_number)) $vendor_invoice_number =0;


                    //
                    $current_asg_task = $Object->getClaimAssignTaskByClaimID($ID);
                    $asg_task = array();

                    if(isset($current_asg_task[0])){
                        $asg_task = json_decode($current_asg_task[0],true);
                    }

                    //$assign_task = $_POST["assign_task"];
                    //$assign_task = json_encode($assign_task);
                    $taskIDs = array();
                    $tasks = array();
                    if(isset($_POST["assign_task"])) $tasks = $_POST["assign_task"];

                    $rlt = $Object->save_update_task($tasks,$asg_task,$ID,$login_by);
                    //print_r($rlt); die();
                    if(count($rlt["taskIDs"])>0){
                        $taskIDs = $rlt["taskIDs"];
                    }

                    $taskIDs = json_encode($taskIDs);

                    $notes =array();
                    if(isset($_POST['notes'])){
                        $notes=$_POST['notes'];
                    }

                    $data_quote =array();
                    if(isset($_POST['data_quote'])){
                        $data_quote=$_POST['data_quote'];
                    }

                    //check employee
                    $ischanged ="SELECT COUNT(*) AS NUM FROM claims WHERE `ID` = '{$ID}' AND `claim_assign` ='{$claim_assign}'";

                    $result = $Object->upClaim($ID,$UID,$taskIDs,$warranty_ID,$paid,$status,$notes,$login_by,
                        $customer,$invoice_amount,$invoice_date,$invoice_flag,
                        $please_pay_flag,$warranty_total,$quote_flag,$quote_amount,$quote_date,$quote_number,
                        $vendor_invoice_number,$claim_assign,$claim_limit,$data_quote,$inactive,$note);

                    if(is_numeric($result["update"]) && $result["update"]){
                        $status = '';
                        //send mail to accountant
                        $Ob_manager = new EmailAdress();
                        $domain_path = $Ob_manager->domain_path;

                        if ($Object->checkExists($ischanged)){
                            //set employee's(assign) info and status email
                            $emp_info =$Object->getContact_ID($claim_assign);
                            $to_name =$emp_info[0]['customer_name'];
                            $to = $emp_info[0]['primary_email'];
                            //check email
                            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                                $status = 'Bounce';
                            }

                            $domain = substr($to, strpos($to, '@') + 1);
                            if  (!checkdnsrr($domain) !== FALSE) {
                                $status = 'Bounce';
                            }

                            //login info
                            $login_info =$Object->getContact_ID($login_by);
                            $from_name = $login_info[0]['customer_name'];

                            $html =$domain_path."/#ajax/claim-form.php?id=".$ID;
                            $subject ='Claim assinged to: '.$to_name;
                            $content = '<p>Dear Sir/Madam</p>';
                            $content .='<p>Please see the claim</p>';
                            $content .='<a href="'.$html.'">Click here to access claim</a>';
                            $id_tracking = $Object->insertTrackingEmail($to,$subject,$content,$login_by,$status);

                            if(empty($status)){
                                $is_send =  $Object->mail_to($from_name,$to_name,$to,$subject,$content,$id_tracking);
                                if($is_send==1){
                                    $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                                }
                            }
                        }

                        $ret = array('SAVE'=>'SUCCESS',"claim_updated_err"=>"",'err_task'=>$rlt["err"],'err_notes'=>$result["err_note"],'AUTH'=>true);
                        //save assign task is error
                        if(count($rlt["err"])>0){
                            foreach($rlt["err"] as $err_task){
                                $info ="ClaimID: ".$ID." , taskName: ".$err_task["taskName"].
                                    ", taskID: ".$err_task["taskID"];
                                 $Object->err_log("Assign Task",$info,$err_task["taskID"]);
                            }
                        }

                    } else {
                        $info ="ClaimID -- ".$ID." , UID: ".$UID.
                            ", warranty_ID: ".$warranty_ID. ",create_by: ".$create_by.
                            ", paid: ".$paid. ", status: ".$status.
                            ",err: ".$result["err_note"];
                        //print_r($info); die();

                        $Object->err_log("Claim",$info,$ID);

                        $ret = array('SAVE'=>'FAIL',"claim_updated_err"=>$rlt["claim_updated_err"],'ERROR'=>'System can not update the claim.','err_task'=>$rlt["err"],'err_notes'=>$result["err_note"],'AUTH'=>true);

                    }
                }else{
                    $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>true);

                }

            }else{
                $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
            }
        }else{
            $ret = array('SAVE'=>'FAIL','ERROR'=> "The Claim ID isn't empty");
        }

    }

    $Object->close_conn();
    echo json_encode($ret);




