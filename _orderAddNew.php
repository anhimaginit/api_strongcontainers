<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
include_once '_qbviacurl.php';
    $Object = new Orders();

    $EXPECTED = array('token','balance','bill_to','note','payment','salesperson','total','warranty','order_title','jwt',
        'private_key','order_total','discount_code','order_create_by','contract_overage','grand_total');

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
                    $result = $Object->addOrder($value_Array,
                        $balance,$bill_to,$note,$payment,
                        $salesperson,$order_total,$warranty,$notes,$order_title,$subscription,
                        $discount_code,$order_create_by,$contract_overage,$grand_total);
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
                        $salesperson,$order_total,$ledger,$notes,$invoice_payment,$billingDate);
                    //quicbook
                    $rsl_customer='';
                    $customer_data = $Object->returnCustomerInfo_contactID($bill_to);
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
                    $ItemName = $prod_name;
                    $UnitPrice = $prod_price;

                    $data = array(
                        "contactID"=>$customer,
                        "invoiceID"=>$invID,
                        "orderID"=>$orderID);

                    $qbInfo=$curlObj->httpost_curl($url,$data);
                    unset($curlObj);
                    $qbInfo_decode = json_decode($qbInfo,true);
                    $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result,'invID'=>$invID,'rsl_emp'=>$rsl_customer,'qbInvoiceID'=>$qbInfo_decode['CreatedId']);
                    //
                  //  $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result,'invID'=>$invID,'rsl_emp'=>$rsl_customer);

                } else {
                    //log errors
                    $info ="Order -- products_ordered: , bill_to: ".$bill_to.
                        ",  salesperson ".$salesperson.", err: ".$result;

                    $Object->err_log("Orders",$info,0);

                    if($result){
                        $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$result);
                    }else{
                        $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>'System can not add the order.');
                    }

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




