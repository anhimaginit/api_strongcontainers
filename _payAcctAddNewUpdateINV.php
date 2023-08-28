<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.payment.php';
include_once '_qbviacurl.php';
    $Object = new Payment();

    $EXPECTED = array('token','pay_amount','pay_type',
        'pay_note','submit_by','approved','invID','order_id','overage','customer','is_overage',
    'payment_date');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $return = array('ERROR'=>'Authentication is failed','SAVE'=>'','pay_id'=>'');
    }else{
        //-----------
        $errObj = $Object->validate_payacct_fields($pay_amount,$pay_type);

        if(!$errObj['error']){
            //Overage is yes or no
            //get payment invoice and order
            if(empty($pay_amount)) $pay_amount=0;

            $oderInfo = $Object->getPaymentBalance_orderID($order_id);

            $contract_overage = $oderInfo["contract_overage"];
            if(empty($contract_overage)) $contract_overage=0;

            $grand_total = $oderInfo["grand_total"];
            if(empty($grand_total)) $grand_total=0;

            $total =$oderInfo['total'];
            if(empty($total)) $total=0;

            $payment_temp =$oderInfo['payment'];
            if(empty($payment_temp)) $payment_temp=0;

            //$continue=1;
            if(empty($is_overage)) $is_overage=0;
            $sumPayment =$payment_temp + $pay_amount;
            $sumOverage =$total + $contract_overage;

            $equal_total=0; $equal_overage =0; $grand_grand_total=0;

            if($payment_temp < $total){
                if($sumPayment>$sumOverage){
                    $overage = ($payment_temp + $pay_amount) - $total;
                    if($is_overage==0){
                        $adjustAmount = $total-$payment_temp;
                        $return = array('IsOverage'=>1,'overage'=>$overage,'ERROR'=>'','SAVE'=>'','pay_id'=>"",
                            'ledger_id'=>"",'ledger'=>array(),
                             'adjustAmount'=>$adjustAmount);

                        $Object->close_conn();
                        echo json_encode($return);
                        return;
                    }
                }else{
                   if($sumPayment >=$total && $sumPayment<=$sumOverage){
                       $overage =($payment_temp + $pay_amount) - $total;
                       $equal_overage =$overage;
                       //update contract overage and grand total
                       $grand_grand_total = $sumPayment;
                       $equal_total=1;
                   }else{
                       $overage=0;
                   }
                }

            }else{
                $return = array('ERROR'=>'Payment is overage','SAVE'=>'Payment is overage, you can not save','pay_id'=>"",
                    'ledger_id'=>"",'ledger'=>array());
                $Object->close_conn();
                echo json_encode($return);
                return;
            }


            //check credit
               $ispayment_date = $Object->is_Date($payment_date);
               if(empty($ispayment_date)) $payment_date = date('Y-m-d H:i:s');

               $ledger_id=0;
               $ledger=array();
               $idreturn = $Object->AddPayAcct($pay_amount,$pay_type,
                $pay_note,$submit_by,$approved,$invID,$order_id,$overage,$customer,$payment_date);
            //check date to update for warranty
               if(is_numeric($idreturn) && !empty($order_id)){
                   //payid,invoice, Amount for _qbCreatePayment
                   $pay_id =$idreturn;
                   $invoice_id =$invID;
                   $Amount = $pay_amount;
                   //update contract_overage
                   if($equal_total==1)  $Object->updateContractOverageGrandTotal_Order_id($order_id,$equal_overage,$grand_grand_total);
                   //
                  $rsl = $Object->getWarrantyStartDate_orderID($order_id);
                   //Create ledger and update invoice
                   $ledger_date =date("Y-m-d H:i:s");
                   $ledger_note ="Pay for Invoice ".$invID;
                   $ledger= array('ledger_credit'=>$pay_amount,'ledger_invoice_id'=>$invID,
                   'ledger_order_id'=>$order_id,'ledger_payment_note'=>$ledger_note,
                   'ledger_type'=>$pay_type,'tran_id'=>$idreturn,'ledger_date'=>$ledger_date,
                   'payment_date'=>$payment_date);

                   //get payment invoice and order
                   $oder_info = $Object->getPaymentBalance_orderID($order_id);
                   $inv_info = $Object->getPaymentBalance_INVID($invID);

                   $order_payment =$oder_info['payment'] +$pay_amount;
                   $order_balance =$oder_info['balance'] - $pay_amount;
                   if($order_balance<=0){
                       $order_balance=0;
                       //check closingdate is existing
                      $closing_date= $Object->getOrderClosingDate_orderID($order_id);
                       if(empty($closing_date)){
                           //
                           $date_temp1=date_create($ispayment_date);
                           $date_temp1_f=  date_format($date_temp1,"Y-m-d");
                           $date1 =date($date_temp1_f);
                           $date2 =date('Y-m-d');
                           $closing_date =$date2;
                           if(strtotime($date1) > strtotime($date2)) $closing_date =$date1;
                           //
                           $Object->updateOrderClosingdate_order_id($order_id,$closing_date);
                       }
                   }

                   $inv_payment =$inv_info['payment'] +$pay_amount;
                   $inv_balance =$inv_info['balance'] - $pay_amount;
                   if($inv_balance<=0){
                       $inv_balance=0;
                       $paidInFull =date("Y-m-d");
                       //$Object->updateClosingdateForInvoice_oderID($order_id,$paidInFull);
                   }

                   $info = $Object->auotUpdateIVN_payacct($invID, $inv_balance,$inv_payment,$order_id,
                       $order_payment,$order_balance,$ledger);

                   $ledger_id =$info["ledger_id"];

                   if(is_numeric($ledger_id) && !empty($ledger_id)){
                       $ledger= array('ledger_credit'=>$pay_amount,'ledger_invoice_id'=>$invID,
                           'ledger_order_id'=>$order_id,'ledger_payment_note'=>$ledger_note,
                           'ledger_type'=>$pay_type,'tran_id'=>$idreturn,'ledger_date'=>$ledger_date,
                       'ID'=>$ledger_id);
                   }

                   //
                   //print_r($rsl); die("123");
                   if(is_numeric($rsl['warranty']) && !empty($rsl['warranty']) &&
                       empty($rsl['warranty_start_date']) ){
                      $Object->updateStartDateforWarranty($rsl['warranty']);
                   }
               }

            //create qb payment
            $curlObj= new QBviaCurl();
            $url = "_qbCreatePayment.php";

            $data = array(
                "Amount"=>$Amount,
                "invoice_id"=>$invoice_id,
                "pay_id"=>$pay_id);

            $qb_rsl=$curlObj->httpost_curl($url,$data);

            $qb_rsl_decode = json_decode($qb_rsl,true);

            //
            $return = array('ERROR'=>'','SAVE'=>'SUCCESS','pay_id'=>$idreturn,
            'ledger_id'=>$ledger_id,'ledger'=>$ledger,'qb_payment_id' =>$qb_rsl_decode['CreatedId']);
        }else{
            $return = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'pay_id'=>'');
        }

    }

$Object->close_conn();
echo json_encode($return);



