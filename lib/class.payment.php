<?php

//define("AUTHORIZENET_LOG_FILE", "phplog");

require_once 'class.common.php';
require_once 'class.invoice.php';
class Payment extends Common{
    //------------------------------------------------
    public function validate_payacct_fields($pay_amount,$pay_type)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($pay_amount)){
            $error = true;
            $errorMsg = "Amount is required.";
        }

        if(!$error && empty($pay_type)){
            $error = true;
            $errorMsg = "Pay type is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------
    public function AddPayAcct($pay_amount,$pay_type,
                               $pay_note,$submit_by,$approved,$invID,$order_id,$overage,$bill_to,
                               $payment_date=null,$tran_id=null)
    {
        if(empty($submit_by)) $submit_by=0;
        if(empty($approved)) $approved=0;
        if(empty($invID)) $invID=0;
        if(empty($order_id)) $order_id=0;
        if(empty($bill_to)) $bill_to=0;
        if(empty($overage)) $overage=0;
        //if(empty($pay_tran_id)) $pay_tran_id=0;
        $create_date = date('Y-m-d H:i:s');

        $fields = "pay_amount,pay_type,pay_note,
        submit_by,approved,invoice_id,order_id,overage,customer,create_date";

        $values = "'{$pay_amount}','{$pay_type}','{$pay_note}',
        '{$submit_by}','{$approved}','{$invID}','{$order_id}',
        '{$overage}','{$bill_to}','{$create_date}'";
        if($tran_id !=''){
            $fields .=",pay_tran_id";
            $values .=",'{$tran_id}'";
        }

        if($payment_date !=''){
            $fields .=",pay_date";
            $values .=",'{$payment_date}'";
        }
        $insertCommand = "INSERT INTO pay_acct({$fields}) VALUES({$values})";
        //die($insertCommand);
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);
        return $idreturn;
    }

    //------------------------------------------------
    public function getPayAcc_payID($payID){
        $select ="SELECT p.pay_amount,p.pay_type,p.pay_note,p.pay_tran_id,
        p.submit_by,p.approved,p.pay_date,p.pay_id,p.customer,
        concat(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) as submit_by_name,
        concat(IFNULL(cc.first_name,''),' ',IFNULL(cc.last_name,'')) as approved_name,
        concat(IFNULL(ccc.first_name,''),' ',IFNULL(ccc.last_name,'')) as customer_name
        FROM pay_acct AS p
        left join contact as c on c.ID = p.submit_by
        left join contact as cc on cc.ID = p.approved
        left join contact as ccc on ccc.ID = p.customer
        where pay_id='{$payID}'";

        $rsl = mysqli_query($this->con,$select);

        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list = $row;
            }
        }
        return $list;
    }
    //------------------------------------------------
    public function getWarrantyStartDate_orderID($orderID){
        $query ="SELECT o.warranty,w.warranty_start_date
		from  quote as o
        left Join warranty as w ON w.ID = o.warranty
        Where order_id ='{$orderID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getPaymentBalance_orderID($orderID){
        $query ="SELECT balance,payment,grand_total,contract_overage,total,paid_in_full
		from  quote
        Where order_id ='{$orderID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getPaymentBalance_INVID($invoiceID){
        $query ="SELECT balance,payment
		from  invoice
        Where ID ='{$invoiceID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function auotUpdateIVN_payacct($ID, $inv_balance,$invoice_payment,$order_id,
                                          $payment,$balance,$ledger,$overage=null,$grand_total=null){
        $invObj = new Invoice();
        $info = $invObj->autoUpdateInvoice($ID, $inv_balance,$invoice_payment,$order_id,
            $payment,$balance,$ledger,$overage,$grand_total);

        unset($invObj);
        return $info;
    }

    //-------------------------------------------------
    public function updateContractOverageGrandTotal_Order_id($order_id,$contractOverage,$grandTotal)
    {
        if(!is_numeric($contractOverage)) $contractOverage=0;
        if(!is_numeric($grandTotal)) $grandTotal=0;

        $updateCommand = "UPDATE `quote`
                SET contract_overage = '{$contractOverage}',
                grand_total = '{$grandTotal}'
                WHERE order_id = '{$order_id}'";

        $update = mysqli_query($this->con,$updateCommand);

        return $update;

    }

    //-------------------------------------------------
    public function getOrderClosingDate_orderID($orderid){
        $query ="SELECT paid_in_full
		from  quote
        Where order_id ='{$orderid}'";

        $result = mysqli_query($this->con,$query);
        $closing_date = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $closing_date = $row['paid_in_full'];
            }
        }

        return $closing_date;
    }
//-------------------------------------------------
    public function updateOrderClosingdate_order_id($order_id,$closing_date)
    {
        $updateCommand = "UPDATE `quote`
                SET paid_in_full = '{$closing_date}'
                WHERE order_id = '{$order_id}'";

        $update = mysqli_query($this->con,$updateCommand);

        return $update;

    }

    public function updateClosingdateForInvoice_oderID($oderID,$closing_date)
    {
        $updateCommand = "UPDATE `invoice`
                SET paid_in_full = '{$closing_date}'
                WHERE order_id = '{$oderID}'";

        $update = mysqli_query($this->con,$updateCommand);

        return $update;

    }

    //------------------------------------------------
    public function new_pay_driver($pay_driver,$pay_amount,$pay_task,$pay_date,
                                   $submit_by,$pay_type,$pay_note)
    {
        $create_date = date('Y-m-d H:i:s');

        $fields = ""; $values='';
        if($pay_driver !=''){
            $fields .="pay_driver";
            $values .="'{$pay_driver}'";
        }

        if($pay_amount !=''){
        $fields .=",pay_amount";
        $values .=",'{$pay_amount}'";
        }
        if($pay_task !=''){
            $fields .=",pay_task";
            $values .=",'{$pay_task}'";
        }
        if($pay_date !=''){
            $fields .=",pay_date";
            $values .=",'{$pay_date}'";
        }
        if($submit_by !=''){
            $fields .=",submit_by";
            $values .=",'{$submit_by}'";
        }
        if($pay_type !=''){
            $fields .=",pay_type";
            $values .=",'{$pay_type}'";
        }
        if($pay_note !=''){
            $fields .=",pay_note";
            $values .=",'{$pay_note}'";
        }

        $insertCommand = "INSERT INTO pay_for_driver({$fields}) VALUES({$values})";
       // die($insertCommand);
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);
        if(is_numeric($idreturn) && $idreturn !=''){
            return array('ERROR'=>'','SAVE'=>'SUCCESS','pay_id'=>$idreturn);
        }
        else {
            array('ERROR'=>mysqli_error($this->con),'SAVE'=>'','pay_id'=>'');
        }
    }

    //------------------------------------------------------
    public function get_payment_task($pay_task)
    {
        $sqlText = "Select * From pay_for_driver
        WHERE pay_task='{$pay_task}' order by pay_id DESC";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        $total_payment =  $this->get_total_payment_task($pay_task);
        $driver_total =  $this->return_id("SELECT driver_total FROM assign_task WHERE ID='{$pay_task}'","driver_total");
        return array("list"=>$list,'total_payment'=>$total_payment,'driver_total'=>$driver_total);
    }

    /////////////////////////////////////////////////////////
}