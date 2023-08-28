<?php

require_once 'class.common.php';
class Invoice extends Common{

    //--------------------------------------------------------------
    public function validate_invoice_fields($token,$invoiceid,$customer)
    {
        $error = false;
        $errorMsg = "";
        /*
        if(!$error && empty($invoiceid)){
            $error = true;
            $errorMsg = "Invoiceid is required.";
        }*/

        if(!$error && empty($customer)){
            $error = true;
            $errorMsg = "Customer is required.";
        }

        //
        /*if(!$error && empty($warranty)){
            $error = true;
            $errorMsg = "Warranty is required.";
        }

        if(!$error && empty($salesperson)){
            $error = true;
            $errorMsg = "Salesperson is required.";
        }*/

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------------
    public function addInvoice($balance,$customer,$invoiceid,$order_id,$payment,
                             $salesperson,$total,$ledger,$notes,$invoice_payment=null,$billingDate=null,$claimID=null)

    {
        $fields = "balance,customer,invoiceid,order_id,payment,salesperson,total,createTime";

        $createTime = date("Y-m-d");

        $values = "'{$balance}','{$customer}','{$invoiceid}','{$order_id}',
        '{$invoice_payment}','{$salesperson}','{$total}','{$createTime}'";
        if(!empty($claimID) && is_numeric($claimID)){
            $fields .=",claim_id";
            $values .= ",'{$claimID}'";
        }
        $insertCommand = "INSERT INTO invoice ({$fields}) VALUES({$values})";

        /*print_r($insertCommand);
        echo "---------";
        print_r($ledger);
        die();*/
        $flag = false;
        if(($this->checkInvoiceNum($invoiceid))){
            $flag = true;
        }

        if($flag) return "The Invoice Number doesn't already.";

        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        if($idreturn){
            //for ledger table
            $ledger_fields="";//"ledger_credit,ledger_debit,ledger_order_id,ledger_payment_note,ledger_type,ledger_create_date,ledger_update_date,ledger_invoice_id";
            $createDateTime = date("Y-m-d H:i:s");
            $ledger_value="";
            $list_payments=array();
            $i=0;
            if(is_array($ledger) && count($ledger)>0){
                foreach($ledger as $v){
                    $val =""; $i++;
                    $temp1 = array();
                    foreach($v as $key=>$item){
                        //create new array
                        if($key!='invoiceDate'){
                            if($key!='payment_schedule_id'){
                                $temp1[$key] = $item;
                            }else{
                                if(!empty($item)){
                                    $temp1[$key] = $item;
                                    $list_payments[]=$item; //get id of payment_schedule
                                }
                            }
                        }
                    }

                    $temp1["ledger_invoice_id"] = $idreturn;
                    $temp1["ledger_create_date"] = $createDateTime;

                    //create value and key
                    //remove payment_schedule_id not using now
                    foreach($temp1 as $kk=>$vv){
                        if($kk=='ledger_create_date'||$kk=='ledger_credit'||
                            $kk=='ledger_invoice_id'||$kk=='ledger_order_id'||
                            $kk=='ledger_payment_note'||$kk=='ledger_type'||
                            $kk=='tran_id' || $kk=='payment_date'){
                            $vv = $this->protect($vv);
                            $val .= empty($val) ? "" : ",";
                            $val .= "'{$vv}'";
                            //create key
                            if($i==1){
                                $ledger_fields .= empty($ledger_fields) ? "" : ",";
                                $ledger_fields .= "{$kk}";
                            }
                        }

                    }

                    $ledger_value .= empty($ledger_value) ? "" : ",";
                    $ledger_value .= "({$val})";
                }

            }
            if(!empty($ledger_value)){
                $insertCommandLedger = "INSERT INTO ledger ({$ledger_fields}) VALUES{$ledger_value}";

                mysqli_query($this->con,$insertCommandLedger);
                $error_temp = mysqli_error($this->con);
                if($error_temp){
                    mysqli_query($this->con,"DELETE FROM invoice WHERE ID = '{$idreturn}'");
                    return $error_temp;
                }else{
                    //update order
                    $updateOrder = $this->updateOrder_id($order_id,$balance,$payment);

                    if(!$updateOrder) return mysqli_error($this->con);
                    //update payment schedule
                    //if(count($list_payments)>0){
                        /*foreach($list_payments as $pays){
                           $this->updateInvoice_paymentSchedule($idreturn,$pays);
                        }*/
                    $this->updatePaymentSchedule_INV($idreturn,$order_id);

                       //insert payment schedule
                        $_orderBalance =$this->getBalance_order_id($order_id);
                        if(is_numeric($_orderBalance) && $_orderBalance>0 && $balance>0){
                            $this->generateNewPayment($order_id,$balance,$billingDate);
                        }

                   // }
                }
            }

            $err = $this->add_notes($notes,$customer,$idreturn);

            if(is_numeric($err) && $err){
                return $idreturn;
            }else{
                return $err;
            }

        }else{
            //die($insertCommand);
            return mysqli_error($this->con);
        }

    }

     //------------------------------------------------------------------    
     /**
     * add new in renews process
     * select orderID 
     * $id = select id form payment_schedule where orderID = $orderID and billToID = $billToID and invoiceID = $invoiceID order by id DESC LIMIT 1
     * update payment_schedule SET .... where id = $id
     */
    public function renewsPaymentSchedule($orderID,$billToID,$invoiceID){
        //get id
        $query = "SELECT * FROM payment_schedule WHERE orderID = '{$orderID}' and billToID = '{$billToID}' order by id DESC ";
        $result = mysqli_query($this->con,$query);
        //print_r($query); die();

        $paymentScheduleList = array();
        if($result){
            while ($rows = mysqli_fetch_assoc($result)) {
                $paymentScheduleList[] = $rows;
            }
        }
        $id = $paymentScheduleList[0]['id'];

        //get 
        // SELECT subscription FROM `orders` WHERE orderID = $orderID
        //get id
        $query = "SELECT subscription FROM orders WHERE orderID = '{$orderID}' ";
        $result = mysqli_query($this->con,$query);
        //print_r($query); die();
        $ordersList = array();
        if($result){
            while ($rows = mysqli_fetch_assoc($result)) {
                $ordersList[] = $rows;
            }
        }
        // $ordersList[0][subscription]

        // update all payment_schedule
        $renewsDate = date("Y-m-d h:i:s");             
        $updateCommand = "UPDATE `payment_schedule` SET renews = 0 , renewsDate = '$renewsDate',invoiceID='$invoiceID' WHERE id = '{$id}'";

        mysqli_query($this->con,$updateCommand);

        // //create new payment_schedule
        // $renewsDate =  date_create(date("Y-m-d h:i:s"));
        // date_add($renewsDate, date_interval_create_from_date_string($paymentPeriod.' month'));
        // $renewsDate = date_format($renewsDate, 'Y-m-d h:i:s');
        // $amount=0;
        // if (count($paymentScheduleList) > 1)
        // {           
        //     $data = json_decode(stripslashes($ordersList[0]['subscription']));
        //     $paymentPeriod = floatval($data->paymentPeriod);
        //     $processingFee = floatval($data->processingFee);
        //     $amount = floatval($paymentPeriod * $processingFee);              
        // }else
        // {
        //     $data = json_decode(stripslashes($ordersList[0]['subscription']));
        //     $paymentPeriod = floatval($data->paymentPeriod);
        //     $processingFee = floatval($data->processingFee);
        //     $initiedFee = floatval($data->initiedFee);
        //     $amount = floatval($initiedFee + ($paymentPeriod*$processingFee));           
        // }
        
        // $check = true;
        // $dtA = new DateTime($renewsDate);
        // $dtB = new DateTime($data->endDate);

        // if ( $dtA > $dtB ) {
        //     $check = false;
        // }

        // $idreturn = null;
        // if($check == true)
        // {
        //     $fields = "id,orderID,billToID,invoiceDate,renews,renewsDate,invoiceID,inactive,amount,fee";        
        //     $values = "NULL,'{$orderID}','{$billToID}',NULL,0,{'$renewsDate'},'{$invoiceID}',1,'{$amount}',0";
        //     $insertCommand = "INSERT INTO payment_schedule({$fields}) VALUES({$values})";
        //     mysqli_query($this->con,$insertCommand);
        //     $idreturn = mysqli_insert_id($this->con);           
        // }      
          
        //return $updateCommand;
        return true;
    }

     //------------------------------------------------------------------    
     /**
     * add new in update invoice process
     * select orderID 
     * $id = select id form payment_schedule where orderID = $orderID and billToID = $billToID and invoiceID = $invoiceID order by id DESC LIMIT 1
     * update payment_schedule SET .... where id = $id
     */
    public function updatePaymentSchedule($orderID,$billToID,$invoiceID){
        //get id
        $query = "SELECT * FROM payment_schedule WHERE orderID = '{$orderID}' and billToID = '{$billToID}' and invoiceID = '{$invoiceID}' order by id DESC ";
        $result = mysqli_query($this->con,$query);
        //print_r($query); die();

        $paymentScheduleList = array();
        if($result){
            while ($rows = mysqli_fetch_assoc($result)) {
                $paymentScheduleList[] = $rows;
            }
        }
        $id = $paymentScheduleList[0]['id'];

        //get 
        // SELECT subscription FROM `orders` WHERE orderID = $orderID
        //get id
        $query = "SELECT subscription FROM orders WHERE orderID = '{$orderID}' ";
        $result = mysqli_query($this->con,$query);
        //print_r($query); die();
        $ordersList = array();
        if($result){
            while ($rows = mysqli_fetch_assoc($result)) {
                $ordersList[] = $rows;
            }
        }
        // $ordersList[0][subscription]

        $data = json_decode($ordersList[0]['subscription']);
        $paymentPeriod = floatval($data->paymentPeriod);
        $processingFee = floatval($data->processingFee);
        $amount = floatval($paymentPeriod * $processingFee);

        // update all payment_schedule                
        $updateCommand = "UPDATE `payment_schedule`
        SET renews = 1 ,
        amount = '$amount'            
        WHERE id = '{$id}'";

        mysqli_query($this->con,$updateCommand);
        
        return $id;
    }


    //-------------------------------------------------
    public function updateInvoice($ID, $balance,$customer,$invoiceid,$order_id,
    $payment,$salesperson,$total,$ledger,$notes,$invoice_payment=null,$billingDate=null)
    {
        $updateTime = date("Y-m-d");

        $updateCommand = "UPDATE `invoice`
                SET balance = '{$balance}',
                customer = '{$customer}',
                order_id = '{$order_id}',
                payment = '{$invoice_payment}',
                salesperson = '{$salesperson}',
                total = '{$total}',
                updateTime ='$updateTime'";

        if(!empty($invoiceid) && is_numeric($invoiceid)){
            $updateCommand .= ",invoiceid = '{$invoiceid}'";
        }
        $updateCommand .= " WHERE ID = '{$ID}'";

        $selectCommand ="SELECT COUNT(*) AS NUM FROM invoice WHERE `ID` = '{$ID}'";
        if (!$this->checkExists($selectCommand)) return "The Invoice doesn't already";

        $flag = false;
        if(($this->checkInvoiceNum($invoiceid,$ID))){
            $flag = true;
        }

        if($flag) return "The Invoice Number doesn't already.";

        //update invoice
        $update = mysqli_query($this->con,$updateCommand);
        //die($updateCommand);
        if($update){
            //update order
            $update_order =$this->updateOrder_id($order_id,$balance,$payment);
            if(!$update_order) return mysqli_error($this->con);
            //for ledger table
            $ledger_fields="";
            $createDateTime=date("Y-m-d H:i:s");
            $i=0;
            //check compare $oderid_old with $oderid
                //update insert delete
                $list_payments=array();
                if(is_array($ledger)){
                    foreach($ledger as $v){
                        if(empty($v["ID"])){
                            $ledger_value="";
                            //add new
                            $val =""; $i++;
                            $temp1 = array();
                            foreach($v as $key=>$item){
                                //create new array
                                if($key!="ID" && $key!='invoiceDate'){
                                    if($key!='payment_schedule_id'){
                                        $temp1[$key] = $item;
                                    }else{
                                        if(!empty($item)){
                                            $temp1[$key] = $item;
                                            $list_payments[]=$item;
                                        }
                                    }
                                }

                            }

                            $temp1["ledger_invoice_id"] = $ID;
                            $temp1["ledger_create_date"] = $createDateTime;

                            //create value and key for adding new
                            foreach($temp1 as $kk=>$vv){
                                if($kk=='ledger_create_date'||$kk=='ledger_credit'||
                                    $kk=='ledger_invoice_id'||$kk=='ledger_order_id'||
                                    $kk=='ledger_payment_note'||$kk=='ledger_type'||
                                    $kk=='tran_id' || $kk=='payment_date'){
                                    $vv = $this->protect($vv);
                                    $val .= empty($val) ? "" : ",";
                                    $val .= "'{$vv}'";
                                    //create key
                                    if($i==1){
                                        $ledger_fields .= empty($ledger_fields) ? "" : ",";
                                        $ledger_fields .= "{$kk}";
                                    }
                                }
                            }

                            $ledger_value .= empty($ledger_value) ? "" : ",";
                            $ledger_value .= "({$val})";


                            if(!empty($ledger_value)){
                                $insertCommandLedger = "INSERT INTO ledger ({$ledger_fields}) VALUES{$ledger_value}";
                                mysqli_query($this->con,$insertCommandLedger);
                                $err_temp =mysqli_error($this->con);
                                if($err_temp){
                                    //mysqli_query($this->con,"DELETE FROM ledger WHERE ledger_invoice_id = '{$ID}'");

                                    //mysqli_query($this->con,"DELETE FROM invoice WHERE ID = '{$ID}'");

                                    return $err_temp;
                                }

                            }
                        }
                    }
                }

            //update and insert payment schedule
            $this->updatePaymentSchedule_INV($ID,$order_id);
            $order_balance =$this->getBalance_order_id($order_id);
            if(is_numeric($order_balance) && $order_balance>0 && $balance>0){
                $this->generateNewPayment($order_id,$balance,$billingDate);
            }
            //update notes
            $err = $this->update_notes($notes,$customer,$ID);

            if(is_numeric($err) && $err){
                return 1;

            }else{
                return $err;
            }


        }else{
            return mysqli_error($this->con);
        }

    }

    //------------------------------------------------------------------
    public function getInvoiceID($ID) {
        $query = "SELECT i.ID,i.balance,i.customer,i.invoiceid,i.order_id,i.payment,
                  i.salesperson,i.total,i.createTime,i.updateTime,
         i.b_company_name,i.b_first_name, i.b_last_name,i.b_ID, i.b_primary_city,
         i.b_primary_email,
         i.b_primary_phone,i.b_primary_state,
         i.s_company_name,
         i.s_first_name,i.s_last_name,i.s_ID, i.s_primary_city,i.s_primary_email,
         i.s_primary_phone,i.s_primary_state,o.balance as order_balance,
         o.contract_overage,o.grand_total
         FROM  invoice_short as i
         left join orders as o on o.order_id = i.order_id
         WHERE ID ='{$ID}' LIMIT 1";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(!is_numeric($row['contract_overage'])) $row['contract_overage'] = 0;
                if(!is_numeric($row['total'])) $row['total'] = 0;
                $row['grand_total'] = $row['total'] + $row['contract_overage'];

                $row['total_overage'] =$this->getOverage_contactID($row['customer']);
                $list[] = $row;
            }
        }
        if(count($list)>0){
            $list[0]["ledger"]=$this->ledgerList_invID($list[0]['ID']);
            $list[0]["payaccts"]=$this->payAcctsList_invID($ID);

        }

        return $list;
    }

    //------------------------------------------------
    public function checkInvoiceNum($invoiceid, $id=null) {
        if(empty($id)){
            $query = "SELECT count(*) FROM  invoice WHERE invoiceid = '{$invoiceid}' LIMIT 1";
        }else{
            $query = "SELECT count(*) FROM  invoice WHERE invoiceid = '{$invoiceid}' AND ID <>'{$id}' LIMIT 1";
        }
        //die($query);
        $check = mysqli_query($this->con,$query);
        $row = mysqli_fetch_row($check);
        //$num = $this->totalRow($query,0);
        if ($row[0] > 0)
            return true;
        else
            return false;
    }

    //------------------------------------------------------------
    public function InvoiceTotalRecords($columns=null,$search_all=null,$role=null,$id_login=null)
    {
        $criteria = "";

        if(!empty($search_all)){
            $temp = $this->columnsFilterOr($columns,$search_all);
            $criteria .="(".$temp.")";
        }
        //Vendor Affilate Customer
        $sqlText = "Select count(*)
                     From invoice_short
                     where (customer = '{$id_login}' OR invoice_create_by ='{$id_login}') ";

        if(!empty($criteria)){
            $sqlText .= " AND ".$criteria;
        }
        //Employee  SystemAdmin Sales
        $v = $this->protect($role[0]["department"]);
        $level = $this->protect($role[0]['level']);
        if($v=="Employee" || $v=="SystemAdmin" || $v=="Sales" ){
            $sqlText = "Select count(*)
                     From invoice_short";

            if(!empty($criteria)){
                $sqlText .= " WHERE ".$criteria;
            }
        }

        //die($sqlText);
        $num = $this->totalRecords($sqlText,0);

        return $num;
    }

    //------------------------------------------------------------
    public function searchInvoiceList($columns=null,$search_all=null,$limit,$offset,$role=null,$id_login=null)
    {
        $criteria = "";

        if(!empty($search_all)){
            $temp = $this->columnsFilterOr($columns,$search_all);
            $criteria .="(".$temp.")";
        }
        //Vendor Affilate Customer
        $sqlText = "Select ID,balance,customer,invoiceid,order_id,payment,
                    salesperson,total,createTime,updateTime,customer_name,sale_name
                     From invoice_short
                     where (customer = '{$id_login}' OR invoice_create_by ='{$id_login}') ";

        if(!empty($criteria)){
            $sqlText .= " AND ".$criteria;
        }
        //Employee  SystemAdmin Sales
        $v = $this->protect($role[0]["department"]);
        $level = $this->protect($role[0]['level']);
        if($v=="Employee" || $v=="SystemAdmin" || $v=="Sales" ){
            $sqlText = "Select ID,balance,customer,invoiceid,order_id,payment,
                    salesperson,total,createTime,updateTime,customer_name,sale_name
                     From invoice_short";

            if(!empty($criteria)){
                $sqlText .= " WHERE ".$criteria;
            }
        }
        /*
        elseif($v=="Sales" ){
            $sqlText = "Select ID,balance,customer,invoiceid,order_id,payment,
                    salesperson,total,createTime,updateTime,customer_name,sale_name
                     From invoice_short
                     where (s_contactID = '{$id_login}' OR invoice_create_by ='{$id_login}') ";

            if(!empty($criteria)){
                $sqlText .= " AND ".$criteria;
            }
        } */


        /*not delete
         * $criteria1 =empty($criteria)?"":" AND ".$criteria;

        $sqlText = "Select DISTINCT ID,balance,customer,invoiceid,
        order_id,payment,salesperson,total,createTime,updateTime,
        customer_name,sale_name
        From invoice_short
                    where  (customer = '{$id_login}' || invoice_create_by = '{$id_login}')".$criteria1;

        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if(($level=='Admin' && $v =='Sales')|| $v=="SystemAdmin"){
                $sqlText = "Select ID,balance,customer,invoiceid,order_id,payment,
                    salesperson,total,createTime,updateTime,customer_name,sale_name
                     From invoice_short";

                if(!empty($criteria)){
                    $sqlText .= " WHERE ".$criteria;
                }
            }
        }*/

        $sqlText .= " ORDER BY createTime DESC, ID DESC";

        if(!empty($limit)){
            $sqlText .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $sqlText .= " OFFSET {$offset} ";
        }

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }
    //------------------------------------------------------------
    public function deleteInvoice($id)
    {
        $deleteSQL = "DELETE FROM invoice WHERE ID = '{$id}' AND payment=0";
        mysqli_query($this->con,$deleteSQL);
        $deleteT = mysqli_affected_rows($this->con);
        if($deleteT){
            return 1;
        }else{
            return 0;
        }

    }
    //------------------------------------------------------------
    public function ledgerList_orderID($order_id)
    {
        $sqlText = "Select * From ledger where ledger_order_id ='{$order_id}'";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['invoiceDate'] = $this->getVoiceDate($row['payment_schedule_id']);
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function ledgerList_invID($invID)
    {
        $sqlText = "Select * From ledger where ledger_invoice_id ='{$invID}'";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['invoiceDate'] = $this->getVoiceDate($row['payment_schedule_id']);
                $list[] = $row;
            }
        }
        return $list;
    }

    //-------------------------------------------------
    public function updateOrder_id($order_id,$balance,$payment)
    {
        $updateCommand = "UPDATE `orders`
                SET balance = '{$balance}',
                payment = '{$payment}'
                WHERE order_id = '{$order_id}'";

        $update = mysqli_query($this->con,$updateCommand);

        return $update;

    }

    //------------------------------------------------
    public function getNotesByInvoiceID($id){
        $query = "SELECT * FROM  notes
                where typeID = '{$id}' AND LOWER(`type`) ='invoice'
                ORDER BY noteID DESC";

        $rsl = mysqli_query($this->con,$query);

        $notesList = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $notesList[] = $row;
            }
        }
        return $notesList;
    }

    //------------------------------------------------------------
    public function dashboardInvoiceList($limitDay,$login_id,$role=null,$start_date=null,$end_date=null)
    {
        $sql = "";
        $interval="(`i`.`createTime` > (NOW() - INTERVAL '{$limitDay}' DAY))";

        if(empty($limitDay)){
            if(!empty($start_date) && !empty($end_date)){
                $interval = "`i`.`createTime` >= '{$start_date}'";
                $interval .= "AND `i`.`createTime` <= '{$end_date}'";
            }elseif(!empty($start_date) && empty($end_date)){
                $interval = "`i`.`createTime` >= '{$start_date}'";
            }elseif(empty($start_date) && !empty($end_date)){
                $interval = "`i`.`createTime` <= '{$end_date}'";
            }
        }

        $sql = "Select DISTINCT i.ID,i.balance,i.customer,i.invoiceid,
        i.order_id,i.payment,i.salesperson,i.total,i.createTime,
        i.updateTime,i.customer_name,i.sale_name From invoice_short as i
        where ".$interval." AND (i.customer = '{$login_id}' OR invoice_create_by='{$login_id}')
        ORDER BY i.createTime DESC,i.ID DESC";

        $v = $this->protect($role[0]["department"]);
        $level = $this->protect($role[0]['level']);

        if($v =='Sales'){
            $sql = "Select DISTINCT i.ID,i.balance,i.customer,i.invoiceid,
        i.order_id,i.payment,i.salesperson,i.total,i.createTime,
        i.updateTime,i.customer_name,i.sale_name From invoice_short as i
        where ".$interval." AND (i.s_contactID = '{$login_id}')
        ORDER BY i.createTime DESC, i.ID DESC ";
        }
        /*
        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);

            if($level=='Admin' && $v =='Sales' || $v=="SystemAdmin"){
                $sql = "Select i.ID,i.balance,i.customer,i.invoiceid,
        i.order_id,i.payment,i.salesperson,i.total,i.createTime,
        i.updateTime,i.customer_name,i.sale_name From invoice_short as i
        where ".$interval." ORDER BY i.ID";
            }
        } */
        $sql .= " LIMIT 1000 ";
        //die($sql);

        $result = mysqli_query($this->con,$sql);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function getVoiceDate($payment_schedule_id)
    {
        $sqlText = "Select invoiceDate From payment_schedule where id ='{$payment_schedule_id}'";
        $result = mysqli_query($this->con,$sqlText);

        $list = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row['invoiceDate'];
            }
        }
        return $list;
    }
    //------------------------------------------------------------
    public function updateInvoice_paymentSchedule($invoiceID,$payment_scheduleID=null){
        // update all payment_schedule
        $updateCommand = "UPDATE `payment_schedule`
        SET renews = 0 ,
        invoiceID='$invoiceID'
        WHERE id = '{$payment_scheduleID}' AND (inactive=0 || inactive IS NULL)";
        mysqli_query($this->con,$updateCommand);

    }

    //------------------------------------------------------------
    public function updatePaymentSchedule_INV($invoiceID,$orderID){
        // update all payment_schedule
        $updateCommand = "UPDATE `payment_schedule`
        SET renews = 0 ,
        invoiceID='$invoiceID'
        WHERE orderID = '{$orderID}' AND
        (invoiceID ='' OR invoiceID IS NULL)
         AND (inactive=0 || inactive IS NULL) ";
        //die($updateCommand);
        mysqli_query($this->con,$updateCommand);

    }
   //------------------------------------------------------------
    public function generateNewPayment($orderID,$balance=null,$billingDate=null){
       $sub = $this->getSubs_orderID($orderID);
        if(count($sub)==0) return;
       $billingDate = $sub['billingDate'];
       $amount = $sub['paymentAmount'];
       $end_Date =date_create($sub['endDate']);
        $paymentPeriod =$sub['paymentPeriod'];

       $inv_date = $this->invoiceDateOfLastRow_orderID($orderID);
        if(!empty($inv_date)){
            $temp = explode('-',$inv_date);
            if(!empty($billingDate)){
                if($sub['billingCircleEvery']=='day'){
                    $inv_date = $this->nextDate($inv_date,$sub['betweenToPay'],'days');
                }elseif($sub['billingCircleEvery']=='month'){
                    $inv_date = $this->nextDate($inv_date,1,'months');
                    if($sub['billingDate']=='1st of month'){
                        $temp = explode('-',$inv_date);
                        $inv_date = $temp[0].'-'.$temp[1].'-01';
                    }

                }elseif($sub['billingCircleEvery']=='quarter'){
                    $inv_date = $this->nextDate($inv_date,3,'months');
                    if($sub['billingDate']=='1st of month'){
                        $temp = explode('-',$inv_date);
                        $inv_date = $temp[0].'-'.$temp[1].'-01';
                    }
                }elseif($sub['billingCircleEvery']=='year'){
                    $inv_date = $this->nextDate($inv_date,12,'months');
                    if($sub['billingDate']=='1st of month'){
                        $temp = explode('-',$inv_date);
                        $inv_date = $temp[0].'-'.$temp[1].'-01';
                    }
                }


            }

            $datePayment = date_create($inv_date);
            if($end_Date <$datePayment){
                $inv_date =$sub['endDate'];
            }

            //Insert new a payment schedule
            $amount_temp = $amount;
            if($amount_temp > $balance) $amount = $balance;
            $fields = "orderID,invoiceDate,renews,invoiceID,inactive,amount";
            $values = "'{$orderID}','{$inv_date}',0,NULL,0,'{$amount}'";
            $insertCommand = "INSERT INTO payment_schedule({$fields}) VALUES({$values})";
            mysqli_query($this->con,$insertCommand);
            mysqli_insert_id($this->con);
        }

    }

    public function invoiceDateOfLastRow_orderID($orderID){
        /*$query = "SELECT MAX(id) as id1 FROM payment_schedule
        WHERE orderID = '{$orderID}' and invoiceID IS NOT NULL";*/
        $query = "SELECT MAX(id) as id1 FROM payment_schedule
        WHERE orderID = '{$orderID}'";
        $result = mysqli_query($this->con,$query);

        $idMax='';
        if($result){
            while ($rows = mysqli_fetch_assoc($result)) {
                $idMax = $rows['id1'];
            }
        }

        $dateReturn ='';
        if(!empty($idMax)){
            $query = "SELECT invoiceDate FROM payment_schedule
        WHERE id = '{$idMax}'";
            $result = mysqli_query($this->con,$query);
            if($result){
                while ($rows = mysqli_fetch_assoc($result)) {
                    $dateReturn = $rows['invoiceDate'];
                }
            }
        }
        //does payment not pay?
        $query = "SELECT MAX(id) as id1 FROM payment_schedule
        WHERE orderID = '{$orderID}' AND (inactive IS NULL OR inactive=0)";
        /*
        $query = "SELECT MAX(id) as id1 FROM payment_schedule
        WHERE orderID = '{$orderID}' and invoiceID IS NULL AND (inactive IS NULL OR inactive=0)";
        */
        $result = mysqli_query($this->con,$query);

        $idMax1= '';
        if($result){
            while ($rows = mysqli_fetch_assoc($result)) {
                $idMax1 = $rows['id1'];
            }
        }

        if(!empty($idMax1) && is_numeric($idMax1)){
            $dateReturn ='';
        }

        return $dateReturn;
    }

    //------------------------------------------------------------------
    public function getBalance_order_id($order_id) {
        $query = "SELECT balance
        FROM  orders
        WHERE order_id ='{$order_id}'";

        $result = mysqli_query($this->con,$query);
        //print_r($query);
        $list = 0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row['balance'];
            }
        }

        return $list;

    }

    //-------------------------------------------------
    public function updatePayAcct_pay_id($pay_id,$invoice_id,$order_id,$approved=null)
    {
        if(empty($invoice_id)) return 'invoice_id is required';
        if(empty($order_id)) return 'order_id is required';
        if(empty($approved)) $approved=0;


        $updateCommand = "UPDATE `pay_acct`
                SET invoice_id = '{$invoice_id}',
                order_id = '{$order_id}',
                approved= '{$approved}'
                WHERE pay_id = '{$pay_id}'";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            return 1;
        }else{
            return mysqli_error($this->con);
        }

    }

    //------------------------------------------------------------
    public function payAcctsList_invID($invID)
    {
        $sqlText = "Select * From pay_acct where invoice_id ='{$invID}'";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
   //------------------------------------------------------------
    public function generateINVNumber($invNum){
        $selectCommand ="SELECT COUNT(*) AS NUM FROM invoice WHERE `invoiceid` = '{$invNum}'";

        if ($this->checkExists($selectCommand)){
            $invoiceid = date('Y').strtotime("now");
            return $this->generateINVNumber($invoiceid);
        }else{
            return $invNum;
        }
    }

    //------------------------------------------------------------
    public function updateCustomerForPayment(){
        $sqlText ="SELECT ID,customer FROM invoice";
        $result = mysqli_query($this->con,$sqlText);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(!empty($row['customer'])){
                    $c_id=$row["customer"];
                    $invoice_id =$row["ID"];
                    $updateCommand = "UPDATE `pay_acct`
                      SET customer = '{$c_id}'
                      WHERE invoice_id = '{$invoice_id}'";

                    $update = mysqli_query($this->con,$updateCommand);
                }
            }
        }

    }

    //-------------------------------------------------
    public function autoUpdateInvoice($ID, $inv_balance,$invoice_payment,$order_id,
                                  $payment,$balance,$ledger)
    {
        $updateTime = date("Y-m-d");
        $billingDate = date("Y-m-d");

        $updateCommand = "UPDATE `invoice`
                SET balance = '{$inv_balance}',
                payment = '{$invoice_payment}',
                updateTime ='$updateTime'";

        $updateCommand .= " WHERE ID = '{$ID}'";
        //update invoice
        $update = mysqli_query($this->con,$updateCommand);
        //die($updateCommand);
        if($update){
            //update order
            $update_order =$this->updateOrder_id($order_id,$balance,$payment);
            if(!$update_order){
                return array("updateInv"=>1,"updateOrder"=>mysqli_error($this->con),
                    "ledger_id"=>"","ledger_err"=>"") ;
            }
            //create ledger
            $field_lg="ledger_credit,ledger_invoice_id,ledger_order_id,
            ledger_payment_note,ledger_type,tran_id,ledger_create_date,payment_date";

            $ledger_credit =$ledger["ledger_credit"];
            $ledger_invoice_id =$ledger["ledger_invoice_id"];
            $ledger_order_id =$ledger["ledger_order_id"];
            $ledger_payment_note =$ledger["ledger_payment_note"];
            $ledger_type =$ledger["ledger_type"];
            $tran_id =$ledger["tran_id"];
            $ledger_date =$ledger['ledger_date'];
            $payment_date =$ledger['payment_date'];

            $value_lg="'{$ledger_credit}','{$ledger_invoice_id}','{$ledger_order_id}',
            '{$ledger_payment_note}','{$ledger_type}','$tran_id','{$ledger_date}',
            '{$payment_date}'";

            $insertCommandLedger = "INSERT INTO ledger ({$field_lg}) VALUES({$value_lg})";
            mysqli_query($this->con,$insertCommandLedger);
            $lg_id = mysqli_insert_id($this->con);
            if(!is_numeric($lg_id)){
                return array("updateInv"=>1,"updateOrder"=>1,
                    "ledger_id"=>"","ledger_err"=>mysqli_error($this->con)) ;
            }

            //update and insert payment schedule
            $this->updatePaymentSchedule_INV($ID,$order_id);
            $order_balance =$this->getBalance_order_id($order_id);
            if(is_numeric($order_balance) && $order_balance>0 && $balance>0){
                $this->generateNewPayment($order_id,$balance,$billingDate);
            }

            return array("updateInv"=>1,"updateOrder"=>1,
                "ledger_id"=>$lg_id,"ledger_err"=>"") ;


        }else{
            return array("updateInv"=>mysqli_error($this->con),"updateOrder"=>"",
                "ledger_id"=>"","ledger_err"=>"") ;
        }

    }

    /////////////////////////////////////////////////////////
}