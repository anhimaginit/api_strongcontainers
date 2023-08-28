<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.invoice.php';
    $Object = new Invoice();

    $EXPECTED = array('token','balance','customer','invoiceid','order_id','payment','invoice_payment',
        'salesperson','total','jwt','private_key','billingDate','approved');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }
    //die();
   //check $invoiceid =0 or =""
    if(is_numeric($invoiceid)){
        $is_invoiceid=true;
    }else{
        $is_invoiceid=false;
    }

   if(empty($invoiceid)){
       if(isset($_POST['invoiceid'])) {
           if(strlen($_POST['invoiceid']) >0) {
               $is_invoiceid=true;
           }else{
               $is_invoiceid=false;
           }

       }else{
           $is_invoiceid=false;
       }
   }

    //Customer =0 or =""
    if(is_numeric($customer)){
        $is_customer=true;
    }else{
        $is_customer=false;
    }

    if(empty($customer)){
        if(isset($_POST['customer'])) {
            if(strlen($_POST['customer']) >0) {
                $is_customer=true;
            }else{
                $is_customer=false;
            }

        }else{
            $is_customer=false;
        }
    }
    //--- validate
    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed');
    }else{
        $isAuth = $Object->auth($jwt,$private_key);
        //$isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $errObj = $Object->validate_invoice_fields($token,$is_invoiceid,$customer);

            if(!$errObj['error']){
                if(empty($balance)) {
                    $balance =0;
                }

                if(empty($customer)) {
                    $customer =0;
                }

                if(empty($invoiceid)) {
                    $invoiceid =0;
                }

                if(empty($order_id)) {
                    $order_id =0;
                }

                if(empty($payment)) {
                    $payment =0;
                }

                if(empty($invoice_payment))  $invoice_payment =0;

                if(empty($salesperson)) {
                    $salesperson =0;
                }

                if(empty($total)) {
                    $total =0;
                }


                $ledger = array();
                if(isset($_POST['ledger'])){
                    $ledger=$_POST['ledger'];
                }

                $_payaccList = array();
                if(isset($_POST['_payaccList'])){
                    $_payaccList=$_POST['_payaccList'];
                }

                //print_r($ledger); die();
                $notes =array();
                if(isset($_POST['notes'])){
                    $notes = $_POST["notes"];
                }


                $result = $Object->addInvoice($balance,$customer,$invoiceid,$order_id,$payment,
                    $salesperson,$total,$ledger,$notes,$invoice_payment,$billingDate);

                if(is_numeric($result) && $result){
                    $pay_acct =array();
                    foreach($_payaccList as $item){
                        $pay_acct[]= $Object->updatePayAcct_pay_id($item,$result,$order_id,$approved);
                    }

                    $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result,'pay_acct'=>$pay_acct);

                } else {
                    //log errors
                    $info ="invoiceid: ".$invoiceid.
                        ", order_id: ".$order_id.", salesperson ".$salesperson."payment: ".$payment. ", err: ".$result;

                    $Object->err_log("Invoice",$info,0);

                    if($result){
                        $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$result);
                    }else{
                        $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>'System can not add the Invoice.');
                    }

                }

            } else {
                $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);

            }
        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }
    }


    $Object->close_conn();
    echo json_encode($ret);




