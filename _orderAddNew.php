<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
include_once '_qbviacurl.php';
    $Object = new Orders();

    $EXPECTED = array('token','balance','bill_to','note','payment','salesperson','total','warranty','order_title','jwt',
        'private_key','order_total','discount_code','order_create_by','contract_overage','grand_total',
        'order_doors','order_releases','order_zipcode');

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
        //check $prod_price =0 or =""
        if(is_numeric($total)){
            $verifytotal=true;
        }else{
            $verifytotal=false;
        }

        if(empty($total)){
            if(isset($_POST['total'])) {
                if(strlen($_POST['total']) >0) {
                    $verifytotal=true;
                }else{
                    $verifytotal=false;
                }

            }else{
                $verifytotal=false;
            }
        }

        $isAuth = $Object->auth($jwt,$private_key);
        //$isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $errObj = $Object->validate_order_fields($token,$bill_to,$salesperson);
            if(!$errObj['error']){

                $value_Array = $_POST['products_ordered'];
                //echo "<pre>";print_r($value_Array);echo "</pre>"; die();
                $container = array();
                if(isset($_POST["data_post"])) $container =$_POST["data_post"];
               // echo "<pre>";print_r($container);echo "</pre>";
                //die();
                if(empty($order_total)) $order_total=0;

                if(empty($salesperson)) $salesperson=0;

                if(empty($total)) {
                    $total =0;
                }

                if(empty($payment)) {
                    $payment =0;
                }

                if(empty($balance)) {
                    $balance =0;
                }

                if(empty($warranty)) {
                    $warranty =0;
                }

                $notes =array();
                if(isset($_POST['notes'])){
                    $notes=$_POST['notes'];
                }

                $subscription = $_POST['subscription'];
                if(empty($subscription)){
                    $subscription='{}';
                    $invDate =date('Y-m-d');
                    $initAmount = $total;

                } else{
                    $subscription = json_decode($subscription,true);
                    $numberOfPay = (isset($subscription['numberOfPay']))?$subscription['numberOfPay']:0;
                    $processingFee= (isset($subscription['processingFee']))?$subscription['processingFee']:0;
                    $initiedFee= (isset($subscription['initiedFee']))?$subscription['initiedFee']:0;
                    //$total = $order_total - $numberOfPay*$processingFee -$initiedFee;
                    $sub =  $Object->initialAmountInvoice_date($subscription, $total);

                    $invDate =$sub['invDate'];
                    $initAmount = $sub['init_amount'];

                    $subscription['numberOfPay'] = $sub['numberOfPay'];
                    $subscription['paymentAmount'] = $sub['paymentAmount'];
                    $subscription['endDate'] = $sub['endDate'];

                    //$balance  = $order_total =$total + $sub['numberOfPay']* $subscription['processingFee'] + $subscription['initiedFee'];
                    $subscription = json_encode($subscription);

                }

                //if($balance==0 || $order_total==0){
                    $result ="Total or Balance must be greater than 0";
                //}else{
                    if(empty($order_create_by)) $order_create_by=$private_key;
                //create quote temp
                $result ='';
                $contract_overage =0;
                if(count($container) >0){
                    $rsl = $Object->quote_new_direct($bill_to,$container);
                    if($rsl['quote_temp_id'] !='' && is_numeric($rsl['quote_temp_id'])){
                        $result = $Object->addOrder($value_Array,
                            $balance,$bill_to,$note,$payment,
                            $salesperson,$total,$warranty,$notes,$order_title,$subscription,
                            $discount_code,$order_create_by,$contract_overage,$grand_total,
                            $rsl['quote_temp_id'],$container,
                            $order_doors,$order_releases,$order_zipcode);
                    }else{
                        $result ="Error";
                    }
                }else{
                    $result = $Object->addOrder($value_Array,
                        $balance,$bill_to,$note,$payment,
                        $salesperson,$total,$warranty,$notes,$order_title,$subscription,
                        $discount_code,$order_create_by,$contract_overage,$grand_total);
                }
               // }

                if(is_numeric($result) && $result){
                    $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result);

                    $orderID = $result;
                    $billToID = $bill_to;

                    $payment_schedule_id=$Object->addNewPaymentSchedule($orderID,$invDate,$initAmount);
                    //Create Invoice
                    if(!is_numeric($payment_schedule_id)) $payment_schedule_id=0;
                    $ledger_payment_note = "Pay for schedule for ".$payment_schedule_id;

                    $invoiceDate =date("Y-m-d");

                    $customer= $bill_to;
                    $invoiceid = date('Y').strtotime("now");
                    $order_id=$result;
                    $payment=0;
                    $invoice_payment=0;
                    $salesperson = $salesperson;
                    $billingDate = date("Y-m-d");

                    $payment=0; $invoice_payment=0; $ledger =array();
                    $invID = $Object->autoAddInvoice($balance,$customer,$invoiceid,$order_id,$payment,
                        $salesperson,$total,$ledger,$notes,$invoice_payment,$billingDate);
                    //quicbook
                    $rsl_customer='';
                    /*$customer_data = $Object->returnCustomerInfo_contactID($bill_to);
                    if(count($customer_data)>0){
                        $curlObj= new QBviaCurl();
                        $url = "_qbCreateCustmer.php";
                        $Line1 =empty($customer_data["Line1"])?"":$customer_data["Line1"];
                        $City =empty($customer_data["City"])?"":$customer_data["City"];
                        $CountrySubDivisionCode =empty($customer_data["CountrySubDivisionCode"])?"":$customer_data["CountrySubDivisionCode"];
                        $PostalCode =empty($customer_data["PostalCode"])?"":$customer_data["PostalCode"];
                        $GivenName =empty($customer_data["GivenName"])?"":$customer_data["GivenName"];
                        $FamilyName =empty($customer_data["FamilyName"])?"":$customer_data["FamilyName"];
                        $PrimaryPhone =empty($customer_data["PrimaryPhone"])?"":$customer_data["PrimaryPhone"];
                        $PrimaryEmailAddr =empty($customer_data["PrimaryEmailAddr"])?"":$customer_data["PrimaryEmailAddr"];
                        $data = array(
                            "Line1"=>$Line1,
                            "City"=>$City,
                            "Country"=>"USA",
                            "CountrySubDivisionCode"=>$CountrySubDivisionCode,
                            "PostalCode"=>$PostalCode,
                            "GivenName"=>$GivenName,
                            "MiddleName"=>"",
                            "FamilyName"=>$FamilyName,
                            "CompanyName"=>"",
                            "PrimaryPhone"=>$PrimaryPhone,
                            "PrimaryEmailAddr"=>"");
                        $rsl=$curlObj->httpost_curl($url,$data);
                        unset($curlObj);
                        $rsl = json_decode($rsl,true);
                        if(isset($rsl["CreatedId"])){
                            $rsl_customer= $Object->updateQBVendor_contactID($bill_to,$rsl["CreatedId"]);
                        }
                    }

                    //create quickbook invoice
                    $curlObj= new QBviaCurl();
                    $url = "_qbCreateInvoice.php";

                    $data = array(
                        "contactID"=>$customer,
                        "invoiceID"=>$invID,
                        "orderID"=>$orderID);

                    $qbInfo=$curlObj->httpost_curl($url,$data);
                    unset($curlObj);
                    $qbInfo_decode = json_decode($qbInfo,true);
                    */
                   // $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result,'invID'=>$invID,'rsl_emp'=>$rsl_customer,'qbInvoiceID'=>$qbInfo_decode['CreatedId']);
                    //

                    //send email
                    $code = $Object->return_id("SELECT code FROM quote_short where order_id ='{$result}'","code");
                    $temp =$Object->get_quote_temp($code);
                    $quote=$temp['quotes'];
                    $more_info = $temp["more_info"];
                    $status ='';
                    $email = $more_info['email_phone'];
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

                        $tr='';
                        foreach($quote as $item){
                            $tr .='<tr>
                        <td>'.$item["prod_name"].'</td>
                        <td>'.$item["qty"].'</td>
                        <td style="text-align: right">$'.number_format($total,2,".",",").'</td>
                        </tr>';
                        }

                        $div ='';
                        $div .='<div style="text-align: center; width: 100%"><strong>Customer info</strong></div>';
                        $div .='<div style="width: 100%">Customer name: '.$more_info["shipping_customer_name"].'</div>';
                        $div .='<div style="width: 100%">Customer address: '.$more_info["shipping_address"].'</div>';
                        $div .='<div style="width: 100%">Customer Phone: '.$more_info["shipping_phone"].'</div>';

                        $div .='<div style="width: 100%"><strong>Order</strong></div>';
                        $div .='<div style="width: 100%">Order title: '.$more_info["order_title"].'</div>';
                        $div .='<div style="width: 100%">Order status: '.$more_info["order_status"].'</div>';

                        $table =' <table style="width:100%;">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Container name</th>
                                <th style="width: 20%;">Quality</th>
                                <th style="width: 30%;">Total line</th>
                            </tr>
                        </thead>
                        <tbody>'.$tr.'
                        <tr style="text-align: right">
                        <td colspan="2" class="p-r20" style="text-align: right"><strong>Total</strong></td>
                        <td style="text-align: right">$'.number_format($total,2,".",",").'</td>
                        </tr>
                        </tbody>
                    </table>';

                        $html='<html lang="en">
                      <head>
                        <meta charset="UTF-8">
                        <link rel="stylesheet" href="http://phptopdf.com/bootstrap.css">

                      </head>
                      <body>
                        '.$div.'
                        <div class="m_t10" style="width: 100%">'.$table.'</div>
                      </body>
                    </html>';

                        $hrf =$domain_path."/confirm_quote.php?id=".$code;
                        $url='<a href='.$hrf.' > Click here to confirm your quote</a>';

                        $subject ="Your Quote";
                        $to_name = $more_info["shipping_customer_name"];
                        $body ='<p>Hi</p>
                        <p>'.$url.'</p>';
                        $is_send =0;
                        //print_r($html); die();
                        //$is_send =  $Object->mail_to($from_name,$to_name,$email,$subject,$body,'',$file_temp,$file_name);
                        $ret["email_sent"] =$is_send;
                    }

                    $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result,'invID'=>$invID);

                } else {
                    //log errors
                    $info ="Order -- products_ordered: , bill_to: ".$bill_to.
                        ",  salesperson ".$salesperson.", err: ".$result;

                    $Object->err_log("Orders",$info,0);
                    $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$result);

                   /* if($result){
                        $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$result);
                    }else{
                        $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>'System can not add the order.');
                    }*/

                }
            }else{
                $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
            }
        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }

    $Object->close_conn();
    echo json_encode($ret);




