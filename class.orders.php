<?php

require_once 'class.common.php';
require_once 'class.invoice.php';
require_once 'class.payment.php';
class Orders extends Common{
    //--------------------------------------------------------------
    public function validate_order_fields($token,$bill_to,$salesperson)
    {
        $error = false;
        $errorMsg = "";
        //--- $token
        if(!$error && empty($token)){
            $error = true;
            $errorMsg = "Token is required.";
        }

        if(!$error && empty($bill_to)){
            $error = true;
            $errorMsg = "Bill_to is required.";
        }

        /*if(!$error && empty($ship_to)){
            $error = true;
            $errorMsg = "Ship_to is required.";
        } */

        //$email
        /*if(!$error && empty($salesperson)){
            $error = true;
            $errorMsg = "Salesperson is required.";
        }*/

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------------
    public function addOrder($products_ordered,$balance,$bill_to,$note,$payment,
                             $salesperson,$total,$warranty,$notes,$order_title=null,
                             $subscription=null,$discount_code=null,$order_create_by=null,
                             $contract_overage=null,$grand_total=null,
                             $quote_temp_id=null,$container=null,
                             $order_doors=null,$order_releases=null,$order_zipcode=null)
    {
        $products =$products_ordered;

        if(is_array($products_ordered)){
            $prod_encode =json_encode($products_ordered) ;
        }else{
            $products = json_decode($products,true);
            $prod_encode =$products_ordered;
        }

        $prod_encode = $this->protect($prod_encode);

        if(!is_numeric($contract_overage)) $contract_overage=0;
        if(!is_numeric($total)) $total=0;
        $grand_total= $contract_overage+$total ;

        $fields = "products_ordered,balance,bill_to,note,
        payment,salesperson,total,warranty,createTime,order_title,subscription,
        discount_code,order_create_by,contract_overage,grand_total,
        order_status";

        $createTime = date("Y-m-d");
        if($order_title==''){
            $order_title = date('Y').$this->get_max_id("SELECT MAX(order_id) as max_id  FROM `quote` LIMIT 1","max_id");
        }

        $values = "'{$prod_encode}','{$balance}','{$bill_to}','{$note}',
        '{$payment}','{$salesperson}','{$total}','{$warranty}','{$createTime}','{$order_title}',
        '{$subscription}','{$discount_code}','{$order_create_by}',
        '{$contract_overage}','{$grand_total}',
        'WAITING_CONFIRM'";
        $container_amount =0;
        $sku='';
        $order_sku =array();
        if(is_array($container)){
            foreach($container as $key=>$value){
                $container_amount +=$value["qty"] ;
                $sku = ($sku =="")? trim($value["prod_SKU"]): $sku.",".trim($value["prod_SKU"]);
                array_push($order_sku,array("sku"=>trim($value["prod_SKU"]),"quantity"=>$value["qty"]));
                $sku_check = $value["prod_SKU"];
                $available_product = $this->return_id("SELECT product_qty  FROM products WHERE SKU ='{$sku_check}'","product_qty");
                if($available_product > $value["qty"]){
                    return "Not enough container for the Quote";
                }
            }
        }
        //convert to string
        $productIDs =""; $arr_prods=array();
        foreach($products as $key=>$value){
            foreach($value as $k=>$v){
                if($k=="id"){
                    $arr_prods[] =$v;
                    if(!empty($productIDs)){
                        $productIDs = $productIDs.",".$v;
                    }else{
                        $productIDs = $v;
                    }
                }
            }
        }

        //check products are avialable
        $prod_id =""; $isExsiting =false;
        foreach($arr_prods as $item){
            $prod_id = $item;
            if(($this->checkProductsForOrder($item))){
                $isExsiting = true;
                break;
            }
        }

        if($isExsiting) return "The ".$prod_id. " doesn't already";
        $order_sku = json_encode($order_sku);
        $fields .=",available_container_amount,sku_list,order_sku";
        $values .=",'{$container_amount}','{$sku}','{$order_sku}'";

        if($quote_temp_id !=''){
            $fields .=",quote_temp_id";
            $values .=",'{$quote_temp_id}'";
        }

        if($order_doors !=''){
            $fields .=",order_doors";
            $values .=",'{$order_doors}'";
        }

        if($order_releases !=''){
            $fields .=",order_releases";
            $values .=",'{$order_releases}'";
        }
        $salesperson_amount = $this->salesperson_pay;
        if($total !=''){
            $fields .=",salesperson_amount";
            $values .=",'{$salesperson_amount}'";
        }
         if($order_zipcode !=''){
             $fields .=",order_zipcode";
             $values .=",'{$order_zipcode}'";
         }
        $insertCommand = "INSERT INTO quote({$fields}) VALUES({$values})";

        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        //update products if $idreturn
        if($idreturn){
           $err = $this->add_notes($notes,$bill_to,$idreturn);
            if(is_numeric($err) && $err){
                return $idreturn;
            }else{
               // mysqli_query($this->con,"DELETE FROM orders WHERE order_id = '{$idreturn}'");
                return $err;
            }
        }else{
            $err =mysqli_error($this->con);
            return $err;
        }
    }

//------------------------------------------------------------------
    public function newOrderStrongContainer($products_ordered,$balance,$bill_to,$note,$payment,
                                            $sale_id,
                            $total,$warranty,$notes,$order_title,
                            $contract_overage,$discount_code,
                            $quote_temp_id,$payment_type,$tran_id)
    {
        $container_amount =0;
        $sku ='';
        $order_sku =  array();
        if(is_array($products_ordered)){
            foreach($products_ordered as $itm){
               // echo "<pre>";print_r($itm);   echo "</pre>"; die();
                $container_amount +=$itm["quantity"] ;
                $sku = ($sku =="")? trim($itm["sku"]): $sku.",".trim($itm["sku"]);
                array_push($order_sku,array("sku"=>trim($itm["sku"]),"quantity"=>$itm["quantity"]));
            }
            $prod_encode =json_encode($products_ordered) ;
        }else{
            $temp = json_decode($products_ordered,true);
            foreach($temp as $itm){
                $container_amount +=$itm["quantity"] ;
                $sku = ($sku =="")? $itm["sku"]: $sku.",".$itm["sku"];
                array_push($order_sku,array("sku"=>trim($itm["sku"]),"quantity"=>$itm["quantity"]));
            }
            $prod_encode =$products_ordered;
        }

        $order_sku =json_encode($order_sku);

        $prod_encode = $this->protect($prod_encode);
        if(empty($balance) || !is_numeric($balance)) $balance =0;
        if(empty($payment) || !is_numeric($payment)) $payment =0;
        if(empty($contract_overage) || !is_numeric($contract_overage)) $contract_overage =0;
        $grand_total= $contract_overage+$total;

        $fields = "products_ordered,balance,bill_to,note,payment,
        total,order_title,
        contract_overage,grand_total,discount_code,
        quote_temp_id,order_status,
        available_container_amount,sku_list,order_sku";

        $values = "'{$prod_encode}','{$balance}','{$bill_to}','{$note}','{$payment}',
        '{$total}','{$order_title}',
        '{$contract_overage}','{$grand_total}','{$discount_code}',
        '{$quote_temp_id}','OPEN',
        '{$container_amount}','{$sku}','{$order_sku}'";

       // if(!empty($warranty)) {
           // $fields .= ",warranty";
           // $values .=",'{$warranty}'";
       // }
        if($balance ==0){
            $paid_in_full =date('Y-m-d');
            $fields .= ",paid_in_full";
            $values .=",'{$paid_in_full}'";
        }

        if($total > 0){
            $fields .= ",salesperson_amount";
            $values .=",'100'";
        }

         if(!empty($sale_id)) {
             $fields .= ",salesperson";
             $values .=",'{$sale_id}'";
         }

        $insertCommand = "INSERT INTO quote({$fields}) VALUES({$values})";
        //die($insertCommand);
        mysqli_query($this->con,$insertCommand);
        $order_id = mysqli_insert_id($this->con);
        //update products if $order_id
        if($order_id>0){
            //payment schedule
            $invDate =date('Y-m-d');
            //invoice
            $billingDate = date("Y-m-d");
            $ledger =array();
            $invID = $this->autoAddInvoice($balance,$bill_to,'',$order_id,$payment,
                '',$total,$ledger,$notes,$payment,$billingDate);

            $payment_schedule_id=$this->addNewPaymentSchedule($order_id,$invDate,$total,$invID);
            $pay_id='';
            $ledger_id ='';
            //pay_acc
            if($payment > 0){
                $objPayment = new Payment();
                $pay_note ='Pay for invoice '.$invID;
                $approved =1;
                $overage =0;
                $payment_date = date('Y-m-d H:i:s');
                $pay_id  = $objPayment->AddPayAcct($payment,$payment_type,
                    $pay_note,$bill_to,$approved,$invID,$order_id,$overage,$bill_to,
                    $payment_date,$tran_id);

                if(is_numeric($pay_id)){
                    //update order
                    /*if($payment >= $total){
                        $closing_date  =date('Y-m-d');
                        $objPayment->updateOrderClosingdate_order_id($order_id,$closing_date);
                    }*/
                    //ledger and invoice
                    $this->updateTime_ivn($invID);

                    $ledger_date =date("Y-m-d H:i:s");
                    $ledger_note ="Pay for Invoice ".$invID;
                    $ledger= array('ledger_credit'=>$payment,'ledger_invoice_id'=>$invID,
                        'ledger_order_id'=>$order_id,'ledger_payment_note'=>$ledger_note,
                        'ledger_type'=>$payment_type,'tran_id'=>$pay_id,'ledger_date'=>$ledger_date,
                        'payment_date'=>$payment_date);

                    $ledger_id = $this->create_ledger($ledger);

                }
            }
            //note
            $note_id ='';
            if(count($notes) >0){
                $err = $this->add_notes($notes,$bill_to,$order_id);
                if(! is_numeric($err) && $err){
                    $note_id = mysqli_error($this->con);
                }
            }

            return array("order_id"=>$order_id,"inv_id"=>$invID,
                "pay_id"=>$pay_id,'ledger_id'=>$ledger_id,'note_id'=>$note_id) ;
        ////////////////
        }else{
            $err =mysqli_error($this->con);
            return array("order_id"=>$err,"inv_id"=>'',"pay_id"=>'','ledger_id'=>'') ;
        }
    }

    //------------------------------------------------------------------
    //ccc.contact_type as s_contact_type,
    //c.contact_type as b_contact_type,
    // o.ship_to,

    public function getOrderID($order_id) {
        $query = "SELECT o.discount_code,o.order_id, o.products_ordered, o.balance, o.bill_to, o.createTime,
         o.note, o.payment, o.salesperson,o.warranty,o.order_title,
         o.total, o.b_company_name, o.b_first_name, o.b_last_name, o.b_ID,o.b_primary_city,
         o.subscription,
         o.b_primary_email,
         o.b_primary_phone,o.b_primary_state,
         o.b_primary_phone,
         o.order_releases,
         o.b_primary_phone,
         o.order_doors,
         o.order_status,
         b_address1,
         b_primary_city,
         b_primary_state,
         o.s_company_name,
         o.s_first_name, o.s_last_name, o.s_ID, o.s_primary_city,o.s_primary_email,
         o.s_primary_phone,o.s_primary_state,o.contract_overage,o.grand_total,
         o.quote_temp_id,
         o.salesperson_amount,
         o.order_zipcode,
         i.ID as ivn_id

         FROM  quote_short as o
         left join invoice as i on i.order_id = o.order_id
        WHERE o.order_id ='{$order_id}' limit 1";
        //print_r($query); die();
        $result = mysqli_query($this->con,$query);

        require_once 'class.depot.php';
        $obj = new Depot();
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['ivn_id1'] = $this->getInvoiceOrderID($order_id);
                $payment_temp = $this->payment_oder_id($order_id);
                if($payment_temp == null || $payment_temp =='') $payment_temp =0;
                $row["payment"] = $payment_temp;

                if(!is_numeric($row['contract_overage'])) $row['contract_overage'] = 0;
                if(!is_numeric($row['total'])) $row['total'] = 0;
                $row['grand_total'] = $row['total'] + $row['contract_overage'];

                if($row["quote_temp_id"] !='' && $row["quote_temp_id"] !=null){
                    $row['quote_temp']  = $obj->getQuoteTemp_qt_id($row["quote_temp_id"]);
                }else{
                    $row['quote_temp'] = array();
                }
                $row['discount_code_total'] =0;
                if($row['discount_code'] !='' && $row['discount_code'] != null){
                    $discount_code_total = $this->return_id("SELECT apply_to FROM discount WHERE discount_code ='{$row['discount_code']}' LIMIT 1","apply_to");
                    if($discount_code_total !=''){
                        $discount_code_total =json_decode($discount_code_total,true);
                        $value = $discount_code_total['value'];
                        $discount =$value['discount'];
                        $discount_type = $value['discount_type'];

                        if($discount_type !='$'){
                            $discount =  ($discount * $row['total'])/100;
                            $discount = number_format($discount,2);
                        }

                        $row['discount_code_total'] = $discount;
                    }

                }

                $row['sale_paid_list'] = $this->sales_paid_list($order_id);
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    public function getOrderID_invoice($order_id,$invoiceID=null) {
        $list = array();

        if(!empty($invoiceID)&&is_numeric($invoiceID)){
            $query = "SELECT o.order_id, o.products_ordered, o.balance, o.bill_to, o.createTime,
         o.note, o.payment,o.order_title,
         o.total, o.warranty,o.subscription,
         o.contract_overage,
         o.grand_total,
          inv.ID,inv.invoiceid,
          inv.salesperson
        FROM  quote as o
        LEFT Join invoice as inv ON inv.order_id = o.order_id
        WHERE o.order_id ='{$order_id}'";

            $result = mysqli_query($this->con,$query);
            //print_r($query);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['subscription']=json_decode($row['subscription'],true);
                    if(empty($row['grand_total'])) $row['grand_total'] = $row['total'];
                    if(empty($row['contract_overage'])) $row['contract_overage'] = 0;

                    if($row["order_id"] !='' && $row["order_id"] != null){
                        $row["payment"] = $this->payment_oder_id($row["order_id"]);
                        if($row["payment"] == null || $row["payment"] =='') $row["payment"] =0;
                    }

                    $row['total_overage'] =$this->getOverage_contactID($row['bill_to']);
                    $list[] = $row;
                }
            }
        }else{
            $query = "SELECT o.order_id, o.products_ordered, o.balance, o.bill_to, o.createTime,
         o.note, o.payment, o.salesperson,o.order_title,
         o.	total, o.warranty,o.subscription,
          o.contract_overage,
         o.grand_total,
          inv.ID,inv.invoiceid
        FROM  quote as o
        LEFT Join invoice as inv ON inv.order_id = o.order_id
        WHERE o.order_id ='{$order_id}' AND o.balance >0";

            $result = mysqli_query($this->con,$query);
            //print_r($query);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['subscription']=json_decode($row['subscription'],true);
                    if(empty($row['grand_total'])) $row['grand_total'] = $row['total'];
                    if(empty($row['contract_overage'])) $row['contract_overage'] = 0;

                    $row['total_overage'] =$this->getOverage_contactID($row['bill_to']);
                    $list[] = $row;
                }
            }
        }

        return $list;
    }

    //-------------------------------------------------
    public function update_order($order_id,$products_ordered,$data_post,
                                 $total,$balance,$notes,
                                 $subscription=null,$contract_overage=null,$grand_total=null,
                                 $warranty=null,
                                 $container=null,$quote_temp_id=null)
    {
        $products =$products_ordered;
        if(is_array($products_ordered)){
            $prod_encode =json_encode($products_ordered) ;
        }else{
            $products = json_decode($products);
            $prod_encode =$products_ordered;
        }

        $prod_encode = $this->protect($prod_encode);

        $updateTime = date("Y-m-d");

        if(!is_numeric($contract_overage)) $contract_overage=0;
        if(!is_numeric($total)) $total=0;
        if(!is_numeric($grand_total)) $grand_total=0;
        //$grand_total= $contract_overage+$total ;

        $updateCommand = "UPDATE `quote`
                SET products_ordered = '{$prod_encode}',
                total = '{$total}',
                balance ='{$balance}',
                warranty = '{$warranty}',
                updateTime ='{$updateTime}',
                subscription = '{$subscription}',
                contract_overage ='{$contract_overage}',
                grand_total = '{$grand_total}'";


        $container_amount =0;
        $sku='';
        $order_sku =array();
        if(is_array($container)){
            foreach($container as $key=>$value){
                $container_amount +=$value["qty"] ;
                $sku = ($sku =="")? trim($value["prod_SKU"]): $sku.",".trim($value["prod_SKU"]);
                array_push($order_sku,array("sku"=>trim($value["prod_SKU"]),"quantity"=>$value["qty"]));

            }
        }

        if($order_sku !=''){
            $order_sku = json_encode($order_sku);
            $updateCommand .=",order_sku='{$order_sku}'";
        }
        if($sku !=''){
            $updateCommand .=",sku_list='{$sku}'";
        }
        if($container_amount !=''){
            $updateCommand .=",available_container_amount='{$container_amount}'";
        }
        if($quote_temp_id !=''){
            $updateCommand .=",quote_temp_id='{$quote_temp_id}'";
        }

        $salesperson_amount = $this->salesperson_pay;
        if($total !=''){
            $updateCommand .=",salesperson_amount='{$salesperson_amount}'";
        }
        if(count($data_post) >0){
            foreach($data_post as $key=>$value){
                $updateCommand = ($updateCommand=="")?$key."='{$value}'":$updateCommand.", ".$key."='{$value}'";
            }
        }

        $updateCommand .= " WHERE order_id = '{$order_id}'";
        //die($updateCommand);
        $selectCommand ="SELECT COUNT(*) AS NUM FROM quote WHERE `order_id` = '{$order_id}'";
        if (!$this->checkExists($selectCommand)) return "The Order doesn't already";

        //get old product
        $query = "SELECT products_ordered FROM quote Where `order_id` = '{$order_id}'";
        $result= mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        //convert $products to string
        $productIDs =""; $arr_prods=array();
        foreach($products as $key=>$value){
            foreach($value as $k=>$v){
                if($k=="id"){
                    $arr_prods[] =$v;
                    if(!empty($productIDs)){
                        $productIDs = $productIDs.",".$v;
                    }else{
                        $productIDs = $v;
                    }
                }
            }
        }

        //convert old prods to string
        $prodIDs = ""; $old_oderid_temp=array();
        if(count($list)>0){
            $oderid_temp =  json_decode($list[0]['products_ordered'],true);
            foreach($oderid_temp as $key=>$value){
                foreach($value as $k=>$v){
                    if($k=="id"){
                        $old_oderid_temp[] = $v;
                        if(!empty($prodIDs)){
                            $prodIDs = $prodIDs.",".$v;
                        }else{
                            $prodIDs = $v;
                        }
                    }
                }
            }
        }
        //item in products has in old prods, if has removed it
        $temp =array_diff($arr_prods, $old_oderid_temp);
        //check products are avialable
        $prod_id =""; $isExsiting =false;
        foreach($temp as $item){
            $prod_id = $item;
            if(($this->checkProductsForOrder($item))){
                $isExsiting = true;
                break;
            }
        }

        if($isExsiting) return "The ".$prod_id. " doesn't already";
        //
        $selectCommand ="SELECT COUNT(*) AS NUM FROM quote WHERE `order_id` = '{$order_id}' AND
        (balance <> total or (payment <> 0 AND payment IS NOT NULL)) ";
        if ($this->checkExists($selectCommand)) return "Can not Update this Order";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            if($data_post['bill_to'] !='')
            $err = $this->update_notes($notes,$data_post['bill_to'],$order_id);
            if(is_numeric($err) && $err){
                return 1;
            }else{
                return $err;
            }

        }else{
            $err = mysqli_error($this->con);
            return $err;
        }

    }

    //-------------------------------------------------
    public function updateOrder($order_id,$products_ordered,$bill_to,$note,
                                $salesperson,$total,$warranty,$notes,$order_title=null,
                                $subscription=null,$discount_code=null,$contract_overage=null,$grand_total=null,
                                $container=null,$quote_temp_id=null)
    {
        $products =$products_ordered;
        if(is_array($products_ordered)){
            $prod_encode =json_encode($products_ordered) ;
        }else{
            $products = json_decode($products);
            $prod_encode =$products_ordered;
        }

        $prod_encode = $this->protect($prod_encode);

        $updateTime = date("Y-m-d");

        if(!is_numeric($contract_overage)) $contract_overage=0;
        if(!is_numeric($total)) $total=0;
        $grand_total= $contract_overage+$total ;

        $updateCommand = "UPDATE `quote`
                SET products_ordered = '{$prod_encode}',
                bill_to = '{$bill_to}',
                note = '{$note}',
                salesperson = '{$salesperson}',
                total = '{$total}',
                balance ='{$total}',
                warranty = '{$warranty}',
                updateTime ='{$updateTime}',
                order_title ='{$order_title}',
                subscription = '{$subscription}',
                discount_code ='{$discount_code}',
                contract_overage ='{$contract_overage}',
                grand_total = '{$grand_total}'";


        $container_amount =0;
        $sku='';
        $order_sku =array();
        if(is_array($container)){
            foreach($container as $key=>$value){
                $container_amount +=$value["qty"] ;
                $sku = ($sku =="")? trim($value["prod_SKU"]): $sku.",".trim($value["prod_SKU"]);
                array_push($order_sku,array("sku"=>trim($value["prod_SKU"]),"quantity"=>$value["qty"]));

            }
        }

        if($order_sku !=''){
            $order_sku = json_encode($order_sku);
            $updateCommand .=",order_sku='{$order_sku}'";
        }
        if($sku !=''){
            $updateCommand .=",sku_list='{$sku}'";
        }
        if($container_amount !=''){
            $updateCommand .=",available_container_amount='{$container_amount}'";
        }
        if($quote_temp_id !=''){
            $updateCommand .=",quote_temp_id='{$quote_temp_id}'";
        }

        $updateCommand .= "WHERE order_id = '{$order_id}'";

        $selectCommand ="SELECT COUNT(*) AS NUM FROM quote WHERE `order_id` = '{$order_id}'";
        if (!$this->checkExists($selectCommand)) return "The Order doesn't already";

        //get old product
        $query = "SELECT products_ordered FROM quote Where `order_id` = '{$order_id}'";
        $result= mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        //convert $products to string
        $productIDs =""; $arr_prods=array();
        foreach($products as $key=>$value){
            foreach($value as $k=>$v){
                if($k=="id"){
                    $arr_prods[] =$v;
                    if(!empty($productIDs)){
                        $productIDs = $productIDs.",".$v;
                    }else{
                        $productIDs = $v;
                    }
                }
            }
        }

        //convert old prods to string
        $prodIDs = ""; $old_oderid_temp=array();
        if(count($list)>0){
            $oderid_temp =  json_decode($list[0]['products_ordered'],true);
            foreach($oderid_temp as $key=>$value){
                foreach($value as $k=>$v){
                    if($k=="id"){
                        $old_oderid_temp[] = $v;
                        if(!empty($prodIDs)){
                            $prodIDs = $prodIDs.",".$v;
                        }else{
                            $prodIDs = $v;
                        }
                    }
                }
            }
        }
        //item in products has in old prods, if has removed it
        $temp =array_diff($arr_prods, $old_oderid_temp);
        //check products are avialable
        $prod_id =""; $isExsiting =false;
        foreach($temp as $item){
            $prod_id = $item;
            if(($this->checkProductsForOrder($item))){
                $isExsiting = true;
                break;
            }
        }

        if($isExsiting) return "The ".$prod_id. " doesn't already";
        //
        $selectCommand ="SELECT COUNT(*) AS NUM FROM quote WHERE `order_id` = '{$order_id}' AND
        (balance <> total or (payment <> 0 AND payment IS NOT NULL)) ";
        if ($this->checkExists($selectCommand)) return "Can not Update this Order";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            $err = $this->update_notes($notes,$bill_to,$order_id);
            if(is_numeric($err) && $err){
                return 1;
            }else{
                return $err;
            }

        }else{
            $err = mysqli_error($this->con);
            return $err;
        }

    }

    //------------------------------------------------------------
    public function OrderTotalRecords($columns=null,$search_all=null,$role=null,$id_login=null)
    {
        $criteria = "";

        if(!empty($search_all)){
            $temp = $this->columnsFilterOr($columns,$search_all);
            $criteria .="(".$temp.")";
        }

        $criteria1 =empty($criteria)?"":" AND ".$criteria;

        $sqlText = "Select DISTINCT count(*) From quote_short
            Where (b_ID = '{$id_login}' || order_create_by= '{$id_login}')".$criteria1;

        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if($level=='Admin' && $v =='Sales' || $v=="SystemAdmin"){
                $sqlText = "Select count(*) From quote_short";

                if(!empty($criteria)){
                    $sqlText .= " WHERE ".$criteria;
                }
            }

        }

        $num = $this->totalRecords($sqlText,0);

        return $num;
    }

    //------------------------------------------------------------
    public function searchOderList($columns=null,$search_all=null,$limit,$offset,$role=null,$id_login=null,$personal_filter=null)
    {
        $criteria = "";

        $personal_filter = trim($personal_filter);
        if($personal_filter == "login_only"){
            $p = $this->orderRelative($id_login);
            if(count($p)>0){
                $p = implode(",",$p);
                //$criteria = "(o.order_id IN ({$p}))";
            }
        }elseif($personal_filter == "group"){
            $p = $this->parentManageUsers($id_login);
            if(count($p)>0){
                $p = implode(",",$p);
                //$criteria = "(o.bill_to IN ({$p}))";
            }
        }elseif($personal_filter == "child_group"){
            $p = $this->userChild($id_login);
            if(count($p)>0){
                $p = implode(",",$p);
                //$criteria = "(o.bill_to IN ({$p}))";
            }
        }

        if(!empty($search_all)){
            $columns =['o.order_title', 'o.warranty','o.payment','o.total',
                'o.balance','o.s_name','o.b_name','o.createTime'];
            $temp = $this->columnsFilterOr($columns,$search_all);
            $criteria.= empty($criteria)?"":" AND ";
            $criteria .="(".$temp.")";
        }
        //print_r($criteria);
        $criteria1 =empty($criteria)?"":" AND ".$criteria;

        $sqlText = "Select DISTINCT o.order_id,o.balance,o.createTime,o.payment,
        o.total,o.warranty,o.b_name,o.b_ID,o.b_primary_city,o.b_primary_email,
		o.b_primary_phone,
		o.order_sku,
        o.b_primary_state,o.s_name,s_ID,o.s_primary_city,o.s_primary_email,
		o.s_primary_phone,o.s_primary_state,
		o.salesperson_amount,
        x.invoiceDate
        From quote_short as o
        left join(
            Select p.orderID ,MAX(p.invoiceDate) as invoiceDate
            from payment_schedule_short as p
            Group by p.orderID
        )x on o.order_id = x.orderID

            Where (o.b_ID = '{$id_login}' OR o.order_create_by = '{$id_login}')".$criteria1;

        //employee and admin
        $v = $this->protect($role[0]["department"]);
        $level = $this->protect($role[0]['level']);
        if($v=="Employee" || $v=="SystemAdmin" ||$v=="Sales"){
            $sqlText = "Select DISTINCT o.order_id,o.balance,o.createTime,o.payment,
        o.total,o.warranty,o.b_name,o.b_ID,o.b_primary_city,o.b_primary_email,
		o.b_primary_phone,o.order_title,
		o.order_sku,
		o.salesperson_amount,
        o.b_primary_state,o.s_name,s_ID,o.s_primary_city,o.s_primary_email,
		o.s_primary_phone,o.s_primary_state,o.salesperson as SID,
        x.invoiceDate
        From quote_short as o
        left join(
            Select p.orderID ,MAX(p.invoiceDate) as invoiceDate
            from payment_schedule_short as p
            Group by p.orderID
        )x on o.order_id = x.orderID";

            if(!empty($criteria)){
                $sqlText .= " WHERE ".$criteria;
            }
        }

        /*
        elseif($v=="Sales"){
            $sqlText = "Select DISTINCT o.order_id,o.balance,o.createTime,o.payment,
        o.total,o.warranty,o.b_name,o.b_ID,o.b_primary_city,o.b_primary_email,
		o.b_primary_phone,o.order_title,
        o.b_primary_state,o.s_name,s_ID,o.s_primary_city,o.s_primary_email,
		o.s_primary_phone,o.s_primary_state,o.salesperson as SID,
        x.invoiceDate
        From quote_short as o
        left join(
            Select p.orderID ,MAX(p.invoiceDate) as invoiceDate
            from payment_schedule_short as p
            Group by p.orderID
        )x on o.order_id = x.orderID

            Where (o.s_ID = '{$id_login}' OR o.order_create_by = '{$id_login}')".$criteria1;
        }*/
       /*
        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if($level=='Admin' && $v =='Sales' || $v=="SystemAdmin"){
                $sqlText = "Select o.order_id,o.balance,o.createTime,o.payment,
        o.total,o.warranty,o.b_name,o.b_ID,o.b_primary_city,o.b_primary_email,
		o.b_primary_phone,
        o.b_primary_state,o.s_name,s_ID,o.s_primary_city,o.s_primary_email,
		o.s_primary_phone,o.s_primary_state,
        x.invoiceDate
        From quote_short as o
        left join(
            Select p.orderID ,MAX(p.invoiceDate) as invoiceDate
            from payment_schedule_short as p
            Group by p.orderID
        )x on o.order_id = x.orderID";

                if(!empty($criteria)){
                    $sqlText .= " WHERE ".$criteria;
                }
            }
        }*/
        //

        $sqlText .= " ORDER BY createTime DESC, order_id desc";

        if(!empty($limit)){
            $sqlText .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $sqlText .= " OFFSET {$offset} ";
        }
        //die($sqlText);

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if($row['order_sku'] !=null && $row['order_sku'] !=''){
                    $or_sku= json_decode($row['order_sku'], true);
                    if(count($or_sku) >0){
                        $sku1='';
                        foreach($or_sku as $item1){
                            $item1["sku"] = '"'.$item1["sku"].'"';
                            $sku1 =($sku1=='')?$item1["sku"]:$sku1.','.$item1["sku"];
                        }

                        $row['sku_info'] = $this->get_container_rate_container_cost($sku1);
                    }
                }

                $row["driver_paid"]=$this->get_pay_driver_by_order($row["order_id"]);
                $row["salesperson_paid"]=$this->get_pay_salesperson_by_order($row["order_id"]);

                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function dashboardOderList($limitDay,$login_id,$role=null,$start_date=null,$end_date=null)
    {
        //$flagNotSales=$this->notSales($login_id);
        $sql ='';
        $interval="(`o`.`createTime` > (NOW() - INTERVAL '{$limitDay}' DAY))";

        if(empty($limitDay)){
            if(!empty($start_date) && !empty($end_date)){
                $interval = "`o`.`createTime` >= '{$start_date}'";
                $interval .= "AND `o`.`createTime` <= '{$end_date}'";
            }elseif(!empty($start_date) && empty($end_date)){
                $interval = "`o`.`createTime` >= '{$start_date}'";
            }elseif(empty($start_date) && !empty($end_date)){
                $interval = "`o`.`createTime` <= '{$end_date}'";
            }
        }

        $sql = "Select DISTINCT o.order_id,o.balance,o.createTime,o.payment,
        o.total,o.warranty,o.b_name,o.b_ID,o.b_primary_city,o.b_primary_email,
		o.b_primary_phone,
        o.b_primary_state,o.s_name,s_ID,o.s_primary_city,o.s_primary_email,
		o.s_primary_phone,o.s_primary_state,
        x.invoiceDate
        From quote_short as o
        left join(
            Select p.orderID ,MAX(p.invoiceDate) as invoiceDate
            from payment_schedule_short as p
            Group by p.orderID
        )x on o.order_id = x.orderID

            where ".$interval."  AND
            (o.b_ID = '{$login_id}' OR  o.order_create_by = '{$login_id}')
            ORDER BY o.createTime DESC,o.order_id DESC ";

        $v = $this->protect($role[0]["department"]);
        $level = $this->protect($role[0]['level']);
        if($v =='Sales'){
            $sql = "Select DISTINCT o.order_id,o.balance,o.createTime,o.payment,
        o.total,o.warranty,o.b_name,o.b_ID,o.b_primary_city,o.b_primary_email,
		o.b_primary_phone,
        o.b_primary_state,o.s_name,s_ID,o.s_primary_city,o.s_primary_email,
		o.s_primary_phone,o.s_primary_state,
        x.invoiceDate
        From quote_short as o
        left join(
            Select p.orderID ,MAX(p.invoiceDate) as invoiceDate
            from payment_schedule_short as p
            Group by p.orderID
        )x on o.order_id = x.orderID

            where ".$interval."  AND
            (o.s_ID = $login_id)
            ORDER BY o.createTime DESC,o.order_id DESC ";
        }
        /*
        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);

            if($level=='Admin' && $v =='Sales' || $v=="SystemAdmin"){
                $sql = "Select o.order_id,o.balance,o.createTime,o.payment,
        o.total,o.warranty,o.b_name,o.b_ID,o.b_primary_city,o.b_primary_email,
		o.b_primary_phone,
        o.b_primary_state,o.s_name,s_ID,o.s_primary_city,o.s_primary_email,
		o.s_primary_phone,o.s_primary_state,
        x.invoiceDate
        From quote_short as o
        left join(
            Select p.orderID ,MAX(p.invoiceDate) as invoiceDate
            from payment_schedule_short as p
            Group by p.orderID
        )x on o.order_id = x.orderID
                        where  ".$interval.
                    " ORDER BY o.order_id";
            }

        }*/

        $sql .= " LIMIT 1000 ";

        $result = mysqli_query($this->con,$sql);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function checkProductsForOrder($product_id) {
        if(empty($order_id)){
            $query = "SELECT count(*) FROM  products WHERE ID = '{$product_id}' AND prod_inactive =1 LIMIT 1";
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

    //------------------------------------------------
    public function deleteOder($order_id)
    {
        $deleteSQL = "DELETE FROM quote WHERE order_id = '{$order_id}' AND warranty=0";

        //get old product
        $query = "SELECT products_ordered FROM quote Where `order_id` = '{$order_id}'";
        $result= mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        //convert old prods to string
        $prodIDs = "";
        if(count($list)>0){
            $oderid_temp =  json_decode($list[0]['products_ordered'],true);
            foreach($oderid_temp as $key=>$value){
                foreach($value as $k=>$v){
                    if($k=="id"){
                        if(!empty($prodIDs)){
                            $prodIDs = $prodIDs.",".$v;
                        }else{
                            $prodIDs = $v;
                        }
                    }
                }
            }
        }

        //execute delete

         mysqli_query($this->con,$deleteSQL);
        $deleteT = mysqli_affected_rows($this->con);

        if($deleteT){
            $query = "UPDATE `products`
                SET prod_inactive = '0'
                WHERE ID IN ({$prodIDs})";
            mysqli_query($this->con,$query);
            return true;
        } else {
            return false;
        }
    }
   //------------------------------------------------
    public function getNotesByOrderID($id){
        $query = "SELECT * FROM  notes
                where typeID = '{$id}' AND LOWER(`type`) ='order'
                order by noteID DESC";

        $rsl = mysqli_query($this->con,$query);

        $notesList = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $notesList[] = $row;
            }
        }
        return $notesList;
    }

    //------------------------------------------------------------------
    public function getOrderID_byBillTo($bill_to,$order_id=null,$balance=null,$inv_id =null) {
        $query = "SELECT o.discount_code,o.order_title, o.order_id, o.balance, o.bill_to,o.payment,o.total,o.warranty
        FROM  quote as o
        WHERE o.bill_to ='{$bill_to}' AND o.balance >0 AND
        o.order_id NOT IN(SELECT order_id FROM invoice)";
       //print_r($query);die();
        if( is_numeric($inv_id) && !empty($inv_id) && is_numeric($order_id) && !empty($order_id)){
            $query = "SELECT o.discount_code,o.order_title, o.order_id, o.balance, o.bill_to,o.payment,o.total,o.warranty
        FROM  quote as o
        WHERE o.bill_to ='{$bill_to}' AND o.order_id ='{$order_id}' AND
          o.order_id = (SELECT inv.order_id  FROM invoice as inv WHERE inv.ID= '{$inv_id}')";
        }

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

       /* if($balance==0 && is_numeric($order_id) && !empty($order_id)){
            $query = "SELECT DISTINCT o.discount_code,o.order_title, o.order_id, o.balance, o.bill_to,o.payment,o.total,o.warranty
        FROM  quote as o
        WHERE o.order_id ='{$order_id}' AND o.balance =0";

            $result = mysqli_query($this->con,$query);
            //print_r($query);
            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $list[] = $row;
                }
            }
        }*/

        return $list;
    }

    //------------------------------------------------------------------
    public function getOrderID_shipTo($ship_to) {
        $query = "SELECT o.discount_code, o.order_id, o.balance, o.bill_to,o.payment,o.total,o.warranty,o.products_ordered, CONCAT(c.first_name,' ', c.last_name) as order_name
        FROM  quote as o
        left join contact as c on c.ID = o.bill_to
        WHERE o.ship_to ='{$ship_to}' AND o.balance >0";

        $result = mysqli_query($this->con,$query);
        //print_r($query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

//------------------------------------------------------------------
    public function getOrderID_shipTo_warranty($ship_to) {
        $query = "SELECT o.discount_code,o.order_id, o.balance, o.bill_to,o.payment,o.total,o.warranty,o.products_ordered
        FROM  quote as o
        WHERE o.ship_to ='{$ship_to}'";

        $result = mysqli_query($this->con,$query);
        //print_r($query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $isFlag_warranty = false;

                if(isset($row['products_ordered'])){
                    $t = json_decode($row['products_ordered'],true);
                    //$row['products_ordered']  = $t;
                    unset($row['products_ordered']);
                    for($i=0;$i<count($t);$i++){
                        foreach($t[$i] as $k=>$v){
                            if(trim($v)=="Warranty" && $k=="prod_class"){
                                $isFlag_warranty = true;

                            }
                        }
                    }

                    if($isFlag_warranty){
                        $list[] = $row;
                    }
                }

            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getOrderIDShipToWarranty($ship_to) {
        $query = "SELECT o.discount_code,o.order_id, o.balance, o.bill_to,o.payment,o.total,o.warranty, CONCAT(c.first_name,' ', c.last_name) as order_name
        FROM  quote as o
        left join contact as c on c.ID = o.bill_to
        WHERE o.ship_to ='{$ship_to}' AND JSON_SEARCH(products_ordered, 'all', 'Warranty') IS NOT NULL";

        $result = mysqli_query($this->con,$query);
        //print_r($query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getOrderIDShipToWarranty_Test($ship_to) {
        $query = "SELECT o.order_id, o.products_ordered, o.balance, o.bill_to,o.payment,o.total,o.warranty, CONCAT(c.first_name,' ', c.last_name) as order_name
        FROM  quote as o
        left join contact as c on c.ID = o.bill_to
        WHERE o.ship_to ='{$ship_to}' AND JSON_SEARCH(products_ordered, 'all', 'Warranty') IS NOT NULL";

        $result = mysqli_query($this->con,$query);
        //print_r($query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list)>0){
                $prods = json_decode($list[0]["products_ordered"],true);
                $limit_list=array();
                foreach($prods as $item){
                    if($item["prod_class"]=="Warranty"){
                        $limit_list[] = $this->getClLField_prodID($item["id"]);
                    }
                }

                if(count($limit_list)>0){
                    $list[0]["claim_limit"] =$limit_list[0];
                }else{
                    $list[0]["claim_limit"] =$limit_list;
                }

                //print_r($limit_list);
                //die();
                unset($list[0]["products_ordered"]);

            }
        }

        return $list;
    }

    //------------------------------------------------------
    public function getClLField_prodID($ID)
    {
        $cl_limit = "Select limits from `claim_limits`";

        $cl_limit .=" WHERE product_ID = '{$ID}'";
       // print_r($cl_limit);
        //die();
        $result = mysqli_query($this->con,$cl_limit);

        $list = array();
        $t = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list) >0){
                $t =  json_decode($list[0]['limits'],true);
            }

        }
        return $t;
    }



    //------------------------------------------------------
    public function getClLimit_proIDs($IDs)
    {
        $cl_limit = "Select limits from `claim_limits`";

        $cl_limit .=" WHERE product_ID in ({$IDs})";

        $result = mysqli_query($this->con,$cl_limit);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = json_decode($row['limits'],true);
            }


        }
        return json_encode($list);
    }


    //------------------------------------------------------------------
    public function order_open_total($interval,$login_id=null){
        //SELECT * FROM email_to_assign WHERE `create_date` < (NOW() - INTERVAL 10 MINUTE)
        $list_paid = array();
        switch ($interval){
            case 60:
                for($i=9;$i>=0;$i--){
                    $j = $i*6 +6;
                    $k = $i*6;
                    $criteria = "`createTime` >= (NOW() - INTERVAL '{$j}' DAY) AND `createTime` < (NOW() - INTERVAL '{$k
                }' DAY)";

                    $query = "SELECT SUM(payment) as order_close, SUM((if(total >0,total,0)) - if(payment>0,payment,0)) as order_open FROM quote";
                    if(!empty($login_id)){
                        $query .=" WHERE bill_to='{$login_id}' AND (".$criteria.")";
                    }else{
                        $query .=" WHERE " .$criteria;
                    }

                    $list_paid[] = $this->sum_order_paid($query);
                }
                break;
            case 90:
                for($i=9;$i>=0;$i--){
                    $j = $i*9 +9;
                    $k = $i*9;
                    $criteria = "`createTime` >= (NOW() - INTERVAL '{$j}' DAY) AND `createTime` < (NOW() - INTERVAL '{$k
                }' DAY)";

                    $query = "SELECT SUM(payment) as order_close, SUM((if(total >0,total,0)) - if(payment>0,payment,0)) as order_open FROM quote";
                    if(!empty($login_id)){
                        $query .=" WHERE bill_to='{$login_id}' AND (".$criteria.")";
                    }else{
                        $query .=" WHERE " .$criteria;
                    }

                    $list_paid[] = $this->sum_order_paid($query);
                }
                break;
            default:
                for($i=5;$i>=0;$i--){
                    $j = $i*5 +5;
                    $k = $i*5;
                    $criteria = "`createTime` >= (NOW() - INTERVAL '{$j}' DAY) AND `createTime` < (NOW() - INTERVAL '{$k
                }' DAY)";

                    $query = "SELECT SUM(payment) as order_close, SUM((if(total >0,total,0)) - if(payment>0,payment,0)) as order_open FROM quote";
                    if(!empty($login_id)){
                        $query .=" WHERE bill_to='{$login_id}' AND (".$criteria.")";
                    }else{
                        $query .=" WHERE " .$criteria;
                    }
                    //print_r($query);
                    $list_paid[] = $this->sum_order_paid($query);
                }

        }
        //die();
        return $list_paid;
    }

    public function  sum_order_paid($query){
        $result = mysqli_query($this->con,$query);

        $total = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $total = $row;
            }

        }

        return $total;
    }


    //------------------------------------------------------------
    public function getOders_ProductHasWarrantyClass($warrantyID=null)
    {
        if(empty($warrantyID)){
            $sqlText = "Select order_id, order_title From quote
        where (warranty=0 OR warranty IS NULL)
        AND JSON_SEARCH(products_ordered, 'all', 'Warranty') IS NOT NULL";
        }else{
            $sqlText = "Select order_id,order_title From quote
        where (warranty=0 OR warranty IS NULL OR warranty='{$warrantyID}')
        AND JSON_SEARCH(products_ordered, 'all', 'Warranty') IS NOT NULL";
        }
        //die($sqlText);

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
    public function getOders_ProductHasALacarteClass($warrantyID=null)
    {
        if(empty($warrantyID)){
            $sqlText = "Select order_id,order_title From quote
        where (warranty=0 OR warranty IS NULL)
        AND JSON_SEARCH(products_ordered, 'all', 'A La Carte') IS NOT NULL";
        }else{
            $sqlText = "Select order_id,order_title From quote
        where (warranty=0 OR warranty IS NULL OR warranty='{$warrantyID}')
        AND JSON_SEARCH(products_ordered, 'all', 'A La Carte') IS NOT NULL";
        }
        //die($sqlText);

        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    /////////////////////////////////////////////////////////

    public function countOrder ($number,$login_id=null,$start_date=null,$end_date=null,$role=null)
    {
        $list_paid = array();
        $query ='';
        $interval="`createTime` >= ( CURDATE() - INTERVAL ".$number." DAY )";
        //$query = "SELECT COUNT(*) as count_order FROM quote WHERE ";
        if(!empty($start_date) && !empty($end_date)){
            $interval= "`createTime` >= '{$start_date}'";
            $interval .= "AND `createTime` <= '{$end_date}'";
        }elseif(!empty($start_date) && empty($end_date)){
            $interval = "`createTime` >= '{$start_date}'";
        }elseif(empty($start_date) && !empty($end_date)){
            $interval = "`createTime` <= '{$end_date}'";
        }

        /*if(is_array($role)){
            foreach($role as $item){
                $v = $this->protect($item["department"]);
                if($this->protect($item['level'])=='Admin' && $v =='Sales' || $v=="SystemAdmin"){
                    $query = "SELECT COUNT(*) as count_order FROM quote WHERE ".$interval;
                    break;
                }
            }
        }*/

        if(empty($query)){
            $query = "SELECT COUNT(*) as count_order FROM quote WHERE ".$interval;
            if(!empty($login_id)){
                $query .="AND bill_to='{$login_id}'";
            }
        }
        //

        $queryOrderClose = $query." AND `balance` <= 0 ";
        $queryOrderOpen = $query." AND `balance` > 0 ";
        $queryOrderTotal = $query;
        //print_r($queryOrderClose." ------------- ");
        //print_r($queryOrderOpen."  ");
        //die();
        $list_paid['orderClose'] = $this->getValueCountOrder($queryOrderClose,'count_order');
        $list_paid['orderOpen'] = $this->getValueCountOrder($queryOrderOpen,'count_order');
        $list_paid['orderTotal'] = $this->getValueCountOrder($queryOrderTotal,'count_order');

        //die();
        return $list_paid;
    }
    //------------------------------------------------------------
    public function getValueCountOrder($query,$key)
    {
        $result = mysqli_query($this->con,$query);

        $res = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $res = $row[$key];
            }
        }
        return $res;
    }

    //------------------------------------------------------------------
    public function addNewPaymentSchedule($orderID,$invDate,$initAmount,$inv_id=null){
        $fields = "orderID,invoiceDate,inactive,amount";
        $values = "'{$orderID}','{$invDate}',0,'{$initAmount}'";
        if($inv_id !=''){
            $fields .= ",invoiceID";
            $values .=",'{$inv_id}'";
        }
        $insertCommand = "INSERT INTO payment_schedule({$fields}) VALUES({$values})";
        //die($insertCommand);
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        return $idreturn;

    }

    //------------------------------------------------------------------
    /**
     * update in update order process
     */
    public function update_PaymentSchedule($orderID,$amount,$invoiceDate){
        $updateCommand = "UPDATE `payment_schedule`
                SET
                amount = '{$amount}',
                invoiceDate ='{$invoiceDate}'
                WHERE orderID = '{$orderID}' AND invoiceID is NULL AND (inactive = 0 || inactive IS NULL)";

        mysqli_query($this->con,$updateCommand);
    }

    //------------------------------------------------------------------
    public function get_order_id($order_id) {
        $query = "SELECT balance,payment,total,subscription
        FROM  quote
        WHERE order_id ='{$order_id}'";

        $result = mysqli_query($this->con,$query);
        //print_r($query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['subscription']=json_decode($row['subscription'],true);
                $list[] = $row;
            }
        }
        if(count($list)>0) {
            return $list[0];
        }else{
            return $list;
        }

    }

    //------------------------------------------------------------------
    public function initialAmountInvoice_date($data, $total){
        $billingDate = $data['billingDate'];
        $offSecondPayFee =$data['offSecondPayFee'];
        $optionPayingLater =$data['optionPayingLater'];

        $subPlan = floatval($data['paymentPeriod']);
        $processingFee = floatval($data['processingFee']);
        $initiedFee = floatval($data['initiedFee']);

        $init_amount =$total + $initiedFee +$processingFee;
        $invDate=date('Y-m-d');
        $numberOfPay =0;
        $amountPayment =$total +$processingFee;

        $data['endDate'] = $this->nextDate($invDate,$subPlan,'months');

        if(is_numeric($subPlan)){
            if($data['billingCircleEvery']=='month') {
                //invoice date
                $numberOfPay = $subPlan;
                $amountPayment = round($total / $numberOfPay,2) +$processingFee;
                $init_amount = $amountPayment + $initiedFee;

                if($billingDate =='1st of month'){
                    if($optionPayingLater==true && $subPlan>1){
                        $str_date = date('Y').'-'.date('m').'-9';
                        //compare now with 10th of month
                        $date10th = date_create($str_date);
                        $currDate = date_create(date("Y-m-d"));
                        if($currDate>$date10th ){
                            $numberOfPay = $subPlan-1;
                            $amountPayment = round($total / $numberOfPay,2) +$processingFee;
                            $init_amount = $amountPayment+ $amountPayment + $initiedFee;

                            $str_date = date('Y').'-'.date('m').'-1';
                            $invDate = $this->nextDate($str_date,1,'months');
                        }
                    }

                }elseif(is_numeric($billingDate)){
                    if($optionPayingLater==true && $subPlan>1){
                        //compare now with 10th of month
                        $str_date = date('Y').'-'.date('m').'-'.$billingDate;
                        $invDate = $this->nextDate($str_date,1,'months');
                        $numberOfPay = $subPlan-1;
                        $amountPayment = round($total / $numberOfPay,2) +$processingFee;
                        $init_amount = $amountPayment+ $amountPayment + $initiedFee;
                    }

                }
            }elseif($data['billingCircleEvery']=='day'){
                $datestart = date('Y').'-'.date('m').'-'.$billingDate;
                $invDate = $datestart;
                if(is_numeric($billingDate)){
                    $date_st = date_create($datestart);
                    //a preiod of subcription
                    $dateStop = date_create($data['endDate']);
                    $diff=date_diff($dateStop,$date_st);
                    $dur_temp =  $diff->format("%a");
                    $payment_day = round($total/$dur_temp,2);

                    $betweenPayments = $data['betweenToPay'];

                    if(!empty($betweenPayments) && is_numeric($betweenPayments) &&is_numeric($dur_temp)){
                        $continue=true;
                        if($optionPayingLater ==true){
                            $invDate = $this->nextDate($datestart,1,'months');
                            $date_st = date_create($invDate);
                            if($dateStop < $date_st){
                                $invDate = $datestart;
                                $init_amount = floatval($initiedFee + $processingFee + $total);
                                $continue=false;
                            }
                        }

                        if($continue){
                            $isTrue = $this->isNextTimePayment($invDate,$dateStop,$betweenPayments);

                            if($isTrue==0){
                                $init_amount = floatval($initiedFee + $processingFee + $total);
                            }else{
                                //number of days for the first payment
                                $today = date('Y-m-d');
                                $today_temp = date_create($today);
                                $diff_init=date_diff($today_temp,$date_st);
                                $dur_init =  $diff_init->format("%a");
                                if($today_temp > $date_st) $dur_init =-$dur_init;

                                $diff=date_diff($date_st,$dateStop);
                                $duration =  $diff->format("%a");
                                $remain = $duration%$betweenPayments ;

                                if($remain >0) $remain=1;
                                //$numberOfPay $amountPayment
                                $numberOfPay = floor($duration/$betweenPayments) +$remain;

                                $amountPayment =$payment_day *$betweenPayments + $processingFee;
                                $init_amount = ($dur_init +$betweenPayments)*$payment_day + $processingFee + $initiedFee;

                            }
                        }

                    }else{
                        $init_amount = floatval($initiedFee + $processingFee + $total);
                    }

                }else{
                    $init_amount = floatval($initiedFee + $processingFee + $total);
                }
            }elseif($data['billingCircleEvery']=='quarter') {
                //invoice date
                $remain=0;
                if($subPlan%3>0) $remain=1;
                $restofmonth = $subPlan%3;
                $numberOfPay = floor($subPlan/3) + $remain;
                $payment_month = round($total / $subPlan,2);
                $amountPayment = $payment_month*3  +$processingFee;
                $init_amount = $amountPayment + $initiedFee;

                //if($numberOfPay<2 || ($restofmonth<2 && $numberOfPay>1)){
                if($numberOfPay<2){
                    $amountPayment = $total +$processingFee;
                    $init_amount = $total + $initiedFee + $processingFee;
                }

                if($billingDate =='1st of month'){
                    if($optionPayingLater==true && $subPlan>1){
                        $str_date = date('Y').'-'.date('m').'-9';
                        //compare now with 10th of month
                        $date10th = date_create($str_date);
                        $currDate = date_create(date("Y-m-d"));
                        if($currDate>$date10th){
                            if($restofmonth<2 && $numberOfPay>1){
                                $numberOfPay =$numberOfPay-1;
                            }
                            if($numberOfPay>1){
                                $init_amount = $init_amount+ $payment_month;
                            }else{
                                $amountPayment = $total  +$processingFee;
                                $init_amount = $total + $initiedFee +$processingFee;
                            }

                            $str_date = date('Y').'-'.date('m').'-1';
                            $invDate = $this->nextDate($str_date,1,'months');
                        }
                    }

                }elseif(is_numeric($billingDate)){
                    $str_date = date('Y').'-'.date('m').'-'.$billingDate;
                    $invDate = $str_date;
                    if($optionPayingLater==true && $subPlan>1){
                        $invDate = $this->nextDate($str_date,1,'months');
                        if($restofmonth<2 && $numberOfPay>1){
                            $numberOfPay =$numberOfPay-1;
                        }
                        if($numberOfPay>1){
                            $init_amount = $init_amount+ $payment_month;
                        }else{
                            $amountPayment = $total  +$processingFee;
                            $init_amount = $total + $initiedFee +$processingFee;
                        }
                    }

                }
            }elseif($data['billingCircleEvery']=='year') {
                //invoice date
                $remain=0;
                if($subPlan%3>0) $remain=1;
                $restofmonth = $subPlan%12;
                $numberOfPay = floor($subPlan/12) + $remain;
                $payment_month = round($total / $subPlan,2);
                $amountPayment = $payment_month*12  +$processingFee;
                $init_amount = $amountPayment + $initiedFee;

                if($numberOfPay<2){
                    $amountPayment = $total +$processingFee;
                    $init_amount = $total + $initiedFee + $processingFee;
                }

                if($billingDate =='1st of month'){
                    if($optionPayingLater==true && $subPlan>1){
                        $str_date = date('Y').'-'.date('m').'-9';
                        //compare now with 10th of month
                        $date10th = date_create($str_date);
                        $currDate = date_create(date("Y-m-d"));
                        if($currDate>$date10th){
                            if($restofmonth<2 && $numberOfPay>1){
                                $numberOfPay =$numberOfPay-1;
                            }
                            if($numberOfPay>1){
                                $init_amount = $init_amount+ $payment_month;
                            }else{
                                $amountPayment = $total  +$processingFee;
                                $init_amount = $total + $initiedFee +$processingFee;
                            }

                            $str_date = date('Y').'-'.date('m').'-1';
                            $invDate = $this->nextDate($str_date,1,'months');
                        }
                    }

                }elseif(is_numeric($billingDate)){
                    $str_date = date('Y').'-'.date('m').'-'.$billingDate;
                    $invDate = $str_date;
                    if($optionPayingLater==true && $subPlan>1){
                        $invDate = $this->nextDate($str_date,1,'months');
                        if($restofmonth<2 && $numberOfPay>1){
                            $numberOfPay =$numberOfPay-1;
                        }
                        if($numberOfPay>1){
                            $init_amount = $init_amount+ $payment_month;
                        }else{
                            $amountPayment = $total  +$processingFee;
                            $init_amount = $total + $initiedFee +$processingFee;
                        }
                    }

                }
            }
        }
        //

        return array("numberOfPay"=>$numberOfPay,"invDate"=>$invDate,"paymentAmount"=>$amountPayment,
            "init_amount"=>$init_amount,'endDate'=>$data['endDate']);
    }

    //------------------------------------------------------------------
    public function numberOfPaymentForAdd($data){
        $billingDate = $data['billingDate'];
        $offSecondPayFee =$data['offSecondPayFee'];
        $optionPayingLater =$data['optionPayingLater'];

        $subPlan = floatval($data['paymentPeriod']);
        $processingFee = floatval($data['processingFee']);
        $initiedFee = floatval($data['initiedFee']);

        $invDate=date('Y-m-d');
        $numberOfPay =0;

        if(is_numeric($subPlan)){
            if($data['billingCircleEvery']=='month') {
                //invoice date
                $numberOfPay = $subPlan;

                if($billingDate =='1st of month'){
                    if($optionPayingLater==true && $subPlan>1){
                        $str_date = date('Y').'-'.date('m').'-9';
                        //compare now with 10th of month
                        $date10th = date_create($str_date);
                        $currDate = date_create(date("Y-m-d"));
                        if($currDate>$date10th ){
                            $numberOfPay = $subPlan-1;
                        }
                    }

                }elseif(is_numeric($billingDate)){
                    if($optionPayingLater==true && $subPlan>1){
                        $numberOfPay = $subPlan-1;
                    }

                }
            }elseif($data['billingCircleEvery']=='day'){
                $datestart = date('Y').'-'.date('m').'-'.$billingDate;
                $invDate = $datestart;
                if(is_numeric($billingDate)){
                    $date_st = date_create($datestart);
                    //a preiod of subcription
                    $dateStop = date_create($data['endDate']);
                    $diff=date_diff($dateStop,$date_st);
                    $dur_temp =  $diff->format("%a");

                    $betweenPayments = $data['betweenToPay'];

                    if(!empty($betweenPayments) && is_numeric($betweenPayments) &&is_numeric($dur_temp)){
                        $continue=true;
                        if($optionPayingLater ==true){
                            $invDate = $this->nextDate($datestart,1,'months');
                            $date_st = date_create($invDate);
                            if($dateStop < $date_st){
                                $invDate = $datestart;
                                $continue=false;
                            }
                        }

                        if($continue){
                            $isTrue = $this->isNextTimePayment($invDate,$dateStop,$betweenPayments);

                            if($isTrue==0){

                            }else{
                                //number of days for the first payment
                                $today = date('Y-m-d');
                                $today_temp = date_create($today);
                                $diff_init=date_diff($today_temp,$date_st);
                                $dur_init =  $diff_init->format("%a");
                                if($today_temp > $date_st) $dur_init =-$dur_init;

                                $diff=date_diff($date_st,$dateStop);
                                $duration =  $diff->format("%a");
                                $remain = $duration%$betweenPayments ;

                                if($remain >0) $remain=1;
                                //$numberOfPay $amountPayment
                                $numberOfPay = floor($duration/$betweenPayments) +$remain;

                            }
                        }

                    }

                }
            }elseif($data['billingCircleEvery']=='quarter') {
                //invoice date
                $restofmonth = $subPlan%3;
                $remain=0;
                if($restofmonth>0) $remain=1;
                $numberOfPay = floor($subPlan/3) + $remain;
                if($billingDate =='1st of month'){
                    if($optionPayingLater==true && $subPlan>1){
                        $str_date = date('Y').'-'.date('m').'-9';
                        //compare now with 10th of month
                        $date10th = date_create($str_date);
                        $currDate = date_create(date("Y-m-d"));
                        if($currDate>$date10th){
                            if($restofmonth<2 && $numberOfPay>1){
                                $numberOfPay =$numberOfPay-1;
                            }
                        }
                    }

                }elseif(is_numeric($billingDate)){
                    $str_date = date('Y').'-'.date('m').'-'.$billingDate;
                    $invDate = $str_date;
                    if($optionPayingLater==true && $subPlan>1){
                        $invDate = $this->nextDate($str_date,1,'months');
                        if($restofmonth<2 && $numberOfPay>1){
                            $numberOfPay =$numberOfPay-1;
                        }
                    }

                }
            }elseif($data['billingCircleEvery']=='year') {
                //invoice date
                $restofmonth = $subPlan%12;
                $remain=0;
                if($restofmonth>0) $remain=1;
                $numberOfPay = floor($subPlan/12) + $remain;

                if($billingDate =='1st of month'){
                    if($optionPayingLater==true && $subPlan>1){
                        $str_date = date('Y').'-'.date('m').'-9';
                        //compare now with 10th of month
                        $date10th = date_create($str_date);
                        $currDate = date_create(date("Y-m-d"));
                        if($currDate>$date10th){
                            if($restofmonth<2 && $numberOfPay>1){
                                $numberOfPay =$numberOfPay-1;
                            }
                        }
                    }

                }elseif(is_numeric($billingDate)){
                    $str_date = date('Y').'-'.date('m').'-'.$billingDate;
                    $invDate = $str_date;
                    if($optionPayingLater==true && $subPlan>1){
                        $invDate = $this->nextDate($str_date,1,'months');
                        if($restofmonth<2 && $numberOfPay>1){
                            $numberOfPay =$numberOfPay-1;
                        }
                    }

                }
            }
        }
        //

        return $numberOfPay;
    }

    //------------------------------------------------------------------
    public function getNumberOfPayInvoice_date($orderID,$data,$oldSub, $total){

        $dateStop = '';
        $notchange ="";
        if(count($data)<1 && count($oldSub)>0){
            $invDate = $this->invDateLastRowNoIvn_orderID($orderID);
            $amountPayment =$total;
            return array("notchange"=>1,"numberOfPay"=>1,"invDate"=>$invDate,"paymentAmount"=>$amountPayment,"total"=>$total);
        }elseif(count($data)<1 && count($oldSub)<1){
            return array("notchange"=>"");
        }

        if(!isset($oldSub['numberOfPay'])) $oldSub['numberOfPay']=0;
        if(!isset($oldSub['billingDate'])) $oldSub['billingDate']=0;
        if(!isset($oldSub['billingCircleEvery'])) $oldSub['billingCircleEvery']="";
        if(!isset($oldSub['betweenToPay'])) $oldSub['betweenToPay']=0;
        if(!isset($oldSub['paymentPeriod'])) $oldSub['paymentPeriod']=0;
        if(!isset($oldSub['processingFee'])) $oldSub['processingFee']=0;

        //$old_fee = $oldSub['numberOfPay'] * $oldSub['processingFee'] + $oldSub['initiedFee'];
        //$oldTotal =$oldTotal -$old_fee;


        $billingDate = $data['billingDate'];
        $offSecondPayFee =$data['offSecondPayFee'];
        $optionPayingLater =$data['optionPayingLater'];
        $processingFee = floatval($data['processingFee']);
        $initiedFee = floatval($data['initiedFee']);

        $lastInvDate = $this->invDateLastRowNoIvn_orderID($orderID);
        $invDate = $lastInvDate;
        $numberOfPay =0;
        $amountPayment =0;


        if($oldSub['billingDate'] !=$data['billingDate'] ||
            $oldSub['paymentPeriod'] !=$data['paymentPeriod'] ||
            $oldSub['billingCircleEvery'] !=$data['billingCircleEvery'] ||
            $oldSub['betweenToPay'] !=$data['betweenToPay']
        ){
            if($oldSub['paymentPeriod'] !=$data['paymentPeriod']){
                $fistdate = $this->invDateFirstRow_orderID($orderID);

                $dateStop = $this->nextDate($fistdate,$data['paymentPeriod'],'months');
            }else{
                $dateStop = $oldSub['endDate'];
            }


            $ts1 = strtotime($lastInvDate);
            $ts2 = strtotime($dateStop);

            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);

            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);
            //the months remained
            $subPlan = (($year2 - $year1) * 12) + ($month2 - $month1);
            if(is_numeric($subPlan)){
                if($data['billingCircleEvery']=='month') {
                    //invoice date
                    $numberOfPay = $subPlan;
                    $total = $total + $numberOfPay*$processingFee + $initiedFee;
                    $amountPayment = round($total / $numberOfPay,2) +$processingFee;

                }elseif($data['billingCircleEvery']=='day'){
                    if(is_numeric($billingDate)){
                        $date_st = date_create($invDate);
                        $dateStop_temp = date_create($dateStop);
                        $diff=date_diff($dateStop_temp,$date_st);
                        $dur_temp =  $diff->format("%a");

                        $betweenPayments = $data['betweenToPay'];
                        if(!empty($betweenPayments) && is_numeric($betweenPayments) &&is_numeric($dur_temp)){
                            //check beetwen is over than stop-date
                            $dateTem = date_create($invDate);
                            $date_interval = $betweenPayments.' days';
                            date_add($dateTem, date_interval_create_from_date_string($date_interval));
                            $dateCopare = date_format($dateTem, 'Y-m-d');

                            $dateCopare = date_create($dateCopare);
                            if($dateStop_temp>$dateCopare){
                                $remain = $dur_temp%$betweenPayments ;
                                if($remain >0) $remain=1;
                                //$numberOfPay $amountPayment
                                $numberOfPay = floor($dur_temp/$betweenPayments) +$remain;

                                $total = $total + $numberOfPay*$processingFee + $initiedFee;

                                $payment_day = round($total/$dur_temp,2);
                                $amountPayment =$payment_day *$betweenPayments + $processingFee;
                            }else{
                                $numberOfPay=1;
                                $amountPayment = $total + $processingFee+$initiedFee;
                            }
                        }else{
                            $amountPayment = floatval( $processingFee + $total +$initiedFee);
                        }

                    }else{
                        $amountPayment = floatval($processingFee + $total +$initiedFee);
                    }
                }elseif($data['billingCircleEvery']=='quarter') {
                    //invoice date
                    $remain =0;
                    if($subPlan%3 >0) $remain=1;
                    $numberOfPay = floor($subPlan/3) + $remain;

                    $total = $total + $numberOfPay*$processingFee + $initiedFee;

                    $payment_month = round($total / $subPlan,2);
                    $amountPayment = $payment_month*3  +$processingFee;

                    if($numberOfPay<2){
                        $amountPayment = $total +$processingFee + $initiedFee;;
                    }
                }elseif($data['billingCircleEvery']=='year') {
                    //invoice date
                    $remain =0;
                    if($subPlan%12 >0) $remain=1;
                    $numberOfPay = floor($subPlan/12) + $remain;

                    $total = $total + $numberOfPay*$processingFee + $initiedFee;

                    $payment_month = round($total / $subPlan,2);
                    $amountPayment = $payment_month*12  +$processingFee;

                    if($numberOfPay<2){
                        $amountPayment = $total +$processingFee;
                    }
                }

                //
        }
            $notchange=1;
        }
        return array("notchange"=>$notchange,"numberOfPay"=>$numberOfPay,"invDate"=>$invDate,"paymentAmount"=>$amountPayment,"total"=>$total,'endDate'=>$dateStop);
    }

    //------------------------------------------------------------------
    public function getNumberOfPay($orderID,$data,$oldSub){
            $dateStop = '';
            $notchange ="";
            if(count($data)<1 && count($oldSub)>0){
                $invDate = $this->invDateLastRowNoIvn_orderID($orderID);
                return array("notchange"=>1,"numberOfPay"=>1);
            }elseif(count($data)<1 && count($oldSub)<1){
                return array("notchange"=>"","numberOfPay"=>0);
            }

            if(!isset($oldSub['numberOfPay'])) $oldSub['numberOfPay']=0;
            if(!isset($oldSub['billingDate'])) $oldSub['billingDate']=0;
            if(!isset($oldSub['billingCircleEvery'])) $oldSub['billingCircleEvery']="";
            if(!isset($oldSub['betweenToPay'])) $oldSub['betweenToPay']=0;
            if(!isset($oldSub['paymentPeriod'])) $oldSub['paymentPeriod']=0;

            $billingDate = $data['billingDate'];

            $lastInvDate = $this->invDateLastRowNoIvn_orderID($orderID);
            $invDate = $lastInvDate;
            $numberOfPay =1;


            if($oldSub['billingDate'] !=$data['billingDate'] ||
                $oldSub['paymentPeriod'] !=$data['paymentPeriod'] ||
                $oldSub['billingCircleEvery'] !=$data['billingCircleEvery'] ||
                $oldSub['betweenToPay'] !=$data['betweenToPay']
            ){
                if($oldSub['paymentPeriod'] !=$data['paymentPeriod']){
                    $fistdate = $this->invDateFirstRow_orderID($orderID);

                    $dateStop = $this->nextDate($fistdate,$data['paymentPeriod'],'months');
                }else{
                    $dateStop = $oldSub['endDate'];
                }


                $ts1 = strtotime($lastInvDate);
                $ts2 = strtotime($dateStop);

                $year1 = date('Y', $ts1);
                $year2 = date('Y', $ts2);

                $month1 = date('m', $ts1);
                $month2 = date('m', $ts2);
                //the months remained
                $subPlan = (($year2 - $year1) * 12) + ($month2 - $month1);
                if(is_numeric($subPlan)){
                    if($data['billingCircleEvery']=='month') {
                        //invoice date
                        $numberOfPay = $subPlan;

                    }elseif($data['billingCircleEvery']=='day'){
                        if(is_numeric($billingDate)){
                            $date_st = date_create($invDate);
                            $dateStop_temp = date_create($dateStop);
                            $diff=date_diff($dateStop_temp,$date_st);
                            $dur_temp =  $diff->format("%a");

                            $betweenPayments = $data['betweenToPay'];
                            if(!empty($betweenPayments) && is_numeric($betweenPayments) &&is_numeric($dur_temp)){
                                //check beetwen is over than stop-date
                                $dateTem = date_create($invDate);
                                $date_interval = $betweenPayments.' days';
                                date_add($dateTem, date_interval_create_from_date_string($date_interval));
                                $dateCopare = date_format($dateTem, 'Y-m-d');

                                $dateCopare = date_create($dateCopare);
                                if($dateStop_temp>$dateCopare){
                                    $remain = $dur_temp%$betweenPayments ;
                                    if($remain >0) $remain=1;
                                    //$numberOfPay $amountPayment
                                    $numberOfPay = floor($dur_temp/$betweenPayments) +$remain;
                                }else{
                                    $numberOfPay=1;
                                }
                            }

                        }
                    }elseif($data['billingCircleEvery']=='quarter') {
                        if($subPlan%3 >0) $remain=1;
                        $numberOfPay = floor($subPlan/3) + $remain;
                    }elseif($data['billingCircleEvery']=='year') {
                        if($subPlan%12 >0) $remain=1;
                        $numberOfPay = floor($subPlan/12) + $remain;
                    }
                }
                $notchange=1;
            }
            return array("notchange"=>$notchange,"numberOfPay"=>$numberOfPay);
        }

    //------------------------------------------------------------------
    public function getSub_OrderID($order_id) {

        $query = "SELECT subscription,total
        FROM  quote
        WHERE order_id ='{$order_id}'";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(!empty($row['subscription']) || $row['orders']!=null || $row['orders'] !='[]'){
                    $row['subscription'] = json_decode($row['subscription'],true);
                }
                $list = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    public function invDateLastRowNoIvn_orderID($order_id) {
        $query = " select invoiceDate
            from payment_schedule as p1,
                ( select MAX(id) as mid  from payment_schedule
                where orderID = '{$order_id}' AND invoiceID is NULL AND (inactive =0 || inactive IS NULL)) p2
            where p1.orderID = '{$order_id}' and p1.id = p2.mid AND invoiceID is NULL AND (p1.inactive =0 || p1.inactive IS NULL)";

        $result = mysqli_query($this->con,$query);
        $list = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row['invoiceDate'];
            }
        }

        return $list;

    }

    //------------------------------------------------------------------
    public function invDateFirstRow_orderID($order_id) {
        $query = " select invoiceDate
            from payment_schedule as p1,
                ( select MIN(id) as mid  from payment_schedule
                where orderID = '{$order_id}'  AND (inactive =0 || inactive IS NULL)) p2
            where p1.orderID = '{$order_id}' and p1.id = p2.mid AND (p1.inactive =0 || p1.inactive IS NULL)";

        $result = mysqli_query($this->con,$query);
        $list = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row['invoiceDate'];
            }
        }

        return $list;

    }

    //------------------------------------------------------------------
    public function autoAddInvoice($balance,$customer,$invoiceid,$order_id,$payment,
    $salesperson,$order_total,$ledger,$notes,$invoice_payment,$billingDate,$claimID=null){
        $obInv = new Invoice();
        $invoiceid= $obInv->generateINVNumber($invoiceid);
        if(is_numeric($claimID) && !empty($claimID)){
            $invID = $obInv->addInvoice($balance,$customer,$invoiceid,$order_id,$payment,
                $salesperson,$order_total,$ledger,$notes,$invoice_payment,$billingDate,$claimID);
        }else{
            $invID = $obInv->addInvoice($balance,$customer,$invoiceid,$order_id,$payment,
                $salesperson,$order_total,$ledger,$notes,$invoice_payment,$billingDate);
        }


        $obInv->close_conn();
        unset($obInv);
        return $invID;
    }
    //------------------------------------------------------------------
    /*
     $date_start = date_create($date_start)
     $dateStop = date_create($dateStop)
     */
    public function isNextTimePayment($date_start,$dateStop,$numberofdays){
        $temp = date_create($date_start);
        $date_interval = $numberofdays.' days';
        date_add($temp, date_interval_create_from_date_string($date_interval));
        $dateCopare = date_format($temp, 'Y-m-d');

        $dateCopare = date_create($dateCopare);
        if($dateStop<$dateCopare){
            return 0;
        }else{
            return 1;
        }

    }

    //------------------------------------------------------------------
    public function getInvoiceOrderID($order_id) {
        $query = "SELECT ID
         FROM  invoice_short
         WHERE order_id ='{$order_id}' LIMIT 1";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getServceFeeProd() {
        $query = "SELECT *
         FROM  products
         WHERE SKU ='AS001' LIMIT 1";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getInvoices_orderID($orderID){
        $query = "SELECT total,balance,payment,TxnId,ID
         FROM  invoice
         WHERE order_id ='{$orderID}' ORDER BY ID DESC LIMIT 1";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function updateInvoices_orderID($orderID,$total,$customer){
       $inv_info =$this->getInvoices_orderID($orderID);
        $update=1;
        foreach($inv_info as $item){
            $total1= $item['total'];
            $balance= $item['balance'];
            $payment= $item['payment'];
            if($total1!=$balance || $payment >0){
                $update=0;
                break;
            }
        }

        if($update==1){
            $updateCommand = "UPDATE `invoice`
                SET total = '{$total}',
                    balance ='{$total}',
                    customer ='{$customer}'
                    WHERE order_id ='{$orderID}'";

             mysqli_query($this->con,$updateCommand);
        }
    }

    //------------------------------------------------------------
    public function order_search($text_search)
    {
       $query = "SELECT order_id as id, order_title as text,
       products_ordered,sku_list,
       bill_to,payment,grand_total,order_status,order_status,
       createTime,
       shipping_customer_name,shipping_address,shipping_phone,email_phone
        FROM quote_short where (order_title like '%{$text_search}%' or
        shipping_customer_name like '%{$text_search}%' or
        email_phone like '%{$text_search}%' or
        shipping_phone like '%{$text_search}%' or
        shipping_address like '%{$text_search}%')
        AND order_id NOT IN (SELECT assign_order FROM assign_task)
        ORDER BY order_id DESC";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if($row["products_ordered"] !='' && $row["products_ordered"] !=null){
                    $row["products_ordered"] = json_decode($row["products_ordered"],true);
                }
               // if($row["sku_list"] !='' && $row["sku_list"] !=null){
                    //$row["sku_list"] = explode(",",$row["sku_list"]);
               // }
                $list[] = $row;
            }
        }
        return $list;
    }

    //---------------------------------
    public function updateTime_ivn($ivn_id){
        $billingDate = date("Y-m-d");

        $query = "UPDATE `invoice`
               SET updateTime ='$billingDate'";

        $query .= " WHERE ID = '{$ivn_id}'";
        mysqli_query($this->con,$query);
    }
    //---------------------------------
    public function create_ledger($ledger){
        //create ledger
        $ledger_credit =$ledger["ledger_credit"];
        $ledger_invoice_id =$ledger["ledger_invoice_id"];
        $ledger_order_id =$ledger["ledger_order_id"];
        $ledger_payment_note =$ledger["ledger_payment_note"];
        $ledger_type =$ledger["ledger_type"];
        $tran_id =$ledger["tran_id"];
        $ledger_date =$ledger['ledger_date'];
        $payment_date =$ledger['payment_date'];

        $field_lg="ledger_credit,ledger_invoice_id,ledger_order_id,
            ledger_payment_note,ledger_type,tran_id,ledger_create_date,payment_date";

        $value_lg="'{$ledger_credit}','{$ledger_invoice_id}','{$ledger_order_id}',
            '{$ledger_payment_note}','{$ledger_type}','$tran_id','{$ledger_date}',
            '{$payment_date}'";

        $query = "INSERT INTO ledger ({$field_lg}) VALUES({$value_lg})";
        mysqli_query($this->con,$query);
        $lg_id = mysqli_insert_id($this->con);

        return $lg_id;
    }

    //---------------------------------
    public function quote_new_direct($bill_to,$data,$order_id=null){
        $query ="SELECT * FROM `contact` WHERE ID ='{$bill_to}' limit 1";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }
        require_once 'class.depot.php';
        $obj = new Depot();
        $rsl = $obj->add_quote_temp_direct($list, $data,$order_id);
        return $rsl;
    }
    //---------------------------------
    public function get_container_rate_container_cost($ku){
        $query ="SELECT container_rate,container_cost,container_sku  FROM `rate_container` WHERE container_sku  IN ({$ku})";
        //die($query);
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //---------------------------------
    public function get_driver_task($ku){
        $query ="SELECT container_rate,container_cost,container_sku  FROM `rate_container` WHERE container_sku  IN ({$ku})";
        //die($query);
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------
    public function get_pay_driver_by_order($order_id){
        $sqlText = "select
        ANY_VALUE(task_id) task_id,
        ANY_VALUE(assign_order) assign_order,
        ANY_VALUE(driver_total) driver_total,
        ANY_VALUE(product_sku) product_sku,
        ANY_VALUE(taskName) taskName,
        ANY_VALUE(assign_driver) assign_driver,
        ANY_VALUE(driver_name) driver_name,
        pay_task,
         SUM(pay_amount) as total_paid

        from `paid_driver_task_short`
        WHERE assign_order='{$order_id}'
        GROUP BY pay_task";

        $result = mysqli_query($this->con,$sqlText);

        $list =array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------
    public function get_total_paid_driver_ordersku($order_id)
    {
        $sqlText = "SELECT
        ANY_VALUE(quote.order_sku) order_sku,
 		ANY_VALUE(x.task_id) task_id,
        ANY_VALUE(x.assign_order) assign_order,
        ANY_VALUE(x.driver_total) driver_total,
        ANY_VALUE(x.product_sku) product_sku,
        ANY_VALUE(x.taskName) taskName,
        ANY_VALUE(x.assign_driver) assign_driver,
        ANY_VALUE(x.driver_name) driver_name,
        x.pay_task,
        SUM(x.pay_amount) as total_paid

        FROM quote
        LEFT JOIN `paid_driver_task_short` as x on quote.order_id = x.assign_order
        WHERE  quote.order_id = '{$order_id}'
        GROUP BY x.pay_task";

        $result = mysqli_query($this->con,$sqlText);

        $list =array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
               /* if($row['order_sku'] !=null && $row['order_sku'] !=''){
                    $row['order_sku'] = json_decode($row['order_sku'],true);
                }*/
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function order_for_task($order_id)
    {
        $query = "SELECT order_id, order_title,sku_list
        FROM quote_short where order_id ='{$order_id}'
        AND available_container_amount >0
        ORDER BY order_id DESC";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------
    public function get_pay_salesperson_by_order($order_id){
        $sqlText = "select SUM(pay_amount) as total_paid
        from `pay_salesperson`
        WHERE pay_order='{$order_id}'";
        $result = mysqli_query($this->con,$sqlText);
        $total_paid  =0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $total_paid = $row["total_paid"];
            }
        }

        return $total_paid;
    }
    //------------------------------------------------------
    public function get_quote_temp($code){
        require_once 'class.depot.php';
        $obj = new Depot();
        $list = array();
        $quotes_temp = $obj->getQuoteTemp($code);
        $quotes = $quotes_temp['quotes'];
        $more_info = $obj->quoteTemp($code);
        return array("quotes"=>$quotes,"more_info"=>$more_info);
    }
    ///////////////////////////////////
}