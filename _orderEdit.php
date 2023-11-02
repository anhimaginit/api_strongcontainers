<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
include_once '_qbviacurl.php';
$Object = new Orders();
    $EXPECTED = array('token','order_id','balance','bill_to','note','payment',
        'ship_to','salesperson','total','order_total','warranty','order_title','discount_code',
        'jwt','private_key','contract_overage','grand_total','quickbooks_call');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $data_post=array();
    if(isset($_POST['order_title'])) $data_post['order_title'] = $Object->protect($_POST['order_title']);;
    if(isset($_POST['bill_to'])) $data_post['bill_to'] = $Object->protect($_POST['bill_to']);;
    if(isset($_POST['salesperson'])) $data_post['salesperson'] = $Object->protect($_POST['salesperson']);
    if(isset($_POST['order_doors'])) $data_post['order_doors'] = $Object->protect($_POST['order_doors']);
    if(isset($_POST['order_releases'])) $data_post['order_releases'] = $Object->protect($_POST['order_releases']);
    if(isset($_POST['note'])) $data_post['note'] = $Object->protect($_POST['note']);
    if(isset($_POST['discount_code'])) $data_post['discount_code'] = $Object->protect($_POST['discount_code']);
    //--- validate
    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('SAVE'=>false,'ERROR'=>'Authentication is failed');
    }else if(!empty($order_id)){
        $isAuth = $Object->auth($jwt,$private_key);

        if($isAuth['AUTH']){
            $errObj = $Object->validate_order_fields($token,$bill_to,$salesperson);

            if(!$errObj['error']){
                $value_Array = $_POST['products_ordered'];
                //print_r($value_Array);die();
                if(empty($order_total)) $order_total=0;
                if(empty($salesperson)) {
                    $salesperson =0;
                }

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
                }

                $subscription = json_decode($subscription,true);
                $prcessingFreeOld =0;

                $numberOfPay = (isset($subscription['numberOfPay']))?$subscription['numberOfPay']:0;
                $processingFee= (isset($subscription['processingFee']))?$subscription['processingFee']:0;
                $initiedFee= (isset($subscription['initiedFee']))?$subscription['initiedFee']:0;
                //$total = $order_total - $numberOfPay*$processingFee -$initiedFee;
                $payment_temp = $Object->payment_oder_id($order_id);
                if($payment_temp == null || $payment_temp =='') $payment_temp =0;
                if($payment_temp > 0){
                    $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>"Order is paying, it can't change");
                    echo json_encode($ret);
                    return;
                }
                //check assign task
                $assign_order = $Object->return_id("SELECT assign_order FROM assign_task WHERE assign_order='{$order_id}'","assign_order");
                if($assign_order !=''){
                    $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>"Oder can't change");
                    echo json_encode($ret);
                    return;
                }
                if($payment >0 && $balance!=0){
                    $old =$Object->getSub_OrderID($order_id);

                    $oldSub = $old['subscription'];

                    $sqlText ="select count(*) from payment_schedule
                    where orderID = '{$order_id}' AND invoiceID is NOT NULL AND
                    (inactive =0 || inactive IS NULL)";

                    $num = $Object->totalRecords($sqlText,0);

                    if(!isset($oldSub['processingFee'])) $oldSub['processingFee']=0;

                    $prcessingFreeOld = $num*$oldSub['processingFee'];

                    $total1 =$total -$payment + $prcessingFreeOld;

                    if(count($subscription)>0){
                        if($num==0){
                            $sub =  $Object->initialAmountInvoice_date($subscription, $total);
                            $amount = $sub['init_amount'] ;
                            $sub['notchange']=1;
                        }else{
                            $sub =  $Object->getNumberOfPayInvoice_date($order_id,$subscription, $oldSub,$total1);
                            $amount = $sub['paymentAmount'];
                        }
                    }

                }else{
                    if(count($subscription)>0){
                        $sub =  $Object->initialAmountInvoice_date($subscription, $total);
                        $amount = $sub['init_amount'] ;
                        $sub['notchange']=1;
                    }
                }

                if(count($subscription)>0){
                    $invDate =$sub['invDate'];

                    $subscription['numberOfPay'] = $sub['numberOfPay'];
                    $subscription['paymentAmount'] = $sub['paymentAmount'];
                    $subscription['endDate'] = $sub['endDate'];
                    //$order_total =$total + $sub['numberOfPay']* $subscription['processingFee'] + $subscription['initiedFee'] +$prcessingFreeOld;

                }else{
                    $amount=$total;   //$order_total-$payment;
                    $invDate =date('Y-m-d');
                    $sub['notchange']=1;
                }

                $subscription = json_encode($subscription);

                //if($order_total==0){
                    $result ="Total or Balance must be greater than 0";
                    $contract_overage =0;
                //}else{
                $container = array();
                if(isset($_POST["data_post"])) $container =$_POST["data_post"];
                if(count($container) >0){
                    $rsl = $Object->quote_new_direct($bill_to,$container,$order_id);
                    if($rsl['quote_temp_id'] !='' && is_numeric($rsl['quote_temp_id'])){
                        $result = $Object->update_order($order_id,$value_Array,$data_post,
                            $total,$balance,$notes,
                            $subscription,$contract_overage,$grand_total,$warranty,
                            $container,$rsl["quote_temp_id"]);
                    }
                }else{
                    $result = $Object->update_order($order_id,$value_Array,$data_post,
                        $total,$balance,$notes,
                        $subscription,$contract_overage,$grand_total,$warranty);
                }
                //}

                if(is_numeric($result) && $result) {
                    //update Invoice
                    $Object->updateInvoices_orderID($order_id,$total,$bill_to);

                    //updatePaymentSchedule
                    $orderID = $order_id;
                    $billToID = $bill_to;
                    if($sub['notchange']==1 ){
                        $sqlText ="select count(*) from payment_schedule
                                  where orderID = '{$order_id}' AND invoiceID is NULL AND
                                  (inactive =0 || inactive IS NULL)";

                        $num = $Object->totalRecords($sqlText,0);
                        if($num >0){
                            $Object->update_PaymentSchedule($order_id,$amount,$invDate);
                        }else{
                            $Object->addNewPaymentSchedule($orderID,$invDate,$amount);
                        }

                    }

                    //quicbook
                    $rsl_customer='';
                    /*$customer_data = $Object->returnCustomerInfo_contactID($bill_to);
                    if(count($customer_data)>0){
                        $curlObj= new QBviaCurl();
                        $url = "_qbCreateCustmer.php";
                        //test
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
                        //
                        $rsl=$curlObj->httpost_curl($url,$data);
                        unset($curlObj);
                        $rsl = json_decode($rsl,true);
                        if(isset($rsl["CreatedId"])){
                            $rsl_customer= $Object->updateQBVendor_contactID($bill_to,$rsl["CreatedId"]);
                        }
                    }*/
                    //update invoice on quicbooks online, get TxnId
                    $inv_info = $Object->getInvoices_orderID($order_id);

                    $TxnId=0;
                    $invoiceID=0;
                    if(count($inv_info)>0){
                        $TxnId =$inv_info[0]['TxnId'];
                        $invoiceID =$inv_info[0]['ID'];
                    }

                    //update invoice on quicbooks online
                   /* $curlObj= new QBviaCurl();
                    $url = "_qbCreateInvoice.php";

                    $data = array(
                        "contactID"=>$bill_to,
                        "invoiceID"=>$invoiceID,
                        "orderID"=>$orderID,
                        "TxnId"=>$TxnId);

                    $qbInvoiceID=0;
                    if($invoiceID>0){
                        $qbInfo=$curlObj->httpost_curl($url,$data);
                        $qbInfo_decode = json_decode($qbInfo,true);
                        $qbInvoiceID=$qbInfo_decode['CreatedId'];
                    }

                    unset($curlObj);*/
                    $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'','rsl_emp'=>$rsl_customer);
                    //$ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'','rsl_emp'=>$rsl_customer,'qbInvoiceID'=>$qbInvoiceID);
                }else{
                    //log errors
                    $info ="Order -- order_id: ".$order_id.", products_ordered: , bill_to: ".$bill_to.
                        ", salesperson ".$salesperson. ", err: ".$result;

                    $Object->err_log("Orders",$info,$order_id);

                    $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$result);
                }

            } else {
                $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
            }
        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }else{
    $ret = array('SAVE'=>'FAIL','ERROR'=>'The Order is not already');

  }

    $Object->close_conn();
    echo json_encode($ret);





