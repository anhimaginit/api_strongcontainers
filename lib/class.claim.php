<?php
require_once 'class.common.php';
require_once './lib/class.orders.php';
class Claim extends Common{
    //------------------------------------------------------
    public function AddClaimLimits($product_ID,$limits)
    {
        $fields = "product_ID,limits";
        $values = "'{$product_ID}','{$limits}'";

        $insertCommand = "INSERT INTO `claim_limits` ({$fields}) VALUES({$values})";
        //die($insertCommand);
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        if(is_numeric($idreturn) && $idreturn){
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------------
    public function updateClaimLimits($ID,$limits)
    {
        $cl_limit_update = "UPDATE `claim_limits`
               set limits = '{$limits}'";

        $cl_limit_update .=" WHERE ID = '{$ID}'";
        //die($cl_limit_update);
        $isUpdate = mysqli_query($this->con,$cl_limit_update);

        if($isUpdate){
            return 1;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------------
    public function getClaimLimit_prodID($ID)
    {
        $cl_limit = "Select c.ID, c.limits,c.product_ID, p.SKU from `claim_limits` as c
        left join products as p on p.ID=c.product_ID";

        $cl_limit .=" WHERE product_ID = '{$ID}'";

        $result = mysqli_query($this->con,$cl_limit);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list) >0){
                $t =  json_decode($list[0]['limits'],true);
                $list[0]['limits'] =$t;
            }

        }
        return $list;
    }

    //------------------------------------------------------------
    public function claimlimits_productList()
    {
        $sqlText = "Select p.ID, p.prod_name, p.prod_class,p.SKU From products as p
        Where (p.prod_class = 'Warranty' OR p.prod_class ='A La Carte') AND p.prod_inactive =0";

        //
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        //print_r($list);
        return $list;
    }

    //------------------------------------------------------------
    public function validate_clLimits_fields($limit)
    {
        $error = false;
        $errorMsg = "";
        /*
         if(!$error && empty($limit)){
             $error = true;
             $errorMsg = "Limit is required.";
         }
          */

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------
    public function validate_claim_fields_add($warranty_ID,$customer=null)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($customer)){
            $error = true;
            $errorMsg = "Customer is required.";
        }

        if(!$error && empty($warranty_ID)){
            $error = true;
            $errorMsg = "Warranty_ID is required.";
        }


        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------
    public function validate_claim_fields_update($warranty_ID,$UID=null)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($warranty_ID)){
            $error = true;
            $errorMsg = "Warranty_ID is required.";
        }


        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //--------------------------------------------------------------
    public function validate_claims_fields($UID,$warranty_ID)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($UID)){
            $error = true;
            $errorMsg = "Contact is required.";
        }

        if(!$error && empty($warranty_ID)){
            $error = true;
            $errorMsg = "Warranty is required.";
        }


        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------------
    public function addClaim($UID,$warranty_ID,$create_by,$taskIDs,$notes=null,$customer=null,$description=null)
    {
        $fields = "warranty_ID,create_by,assign_task,status,start_date,
        customer,note";

        $createTime = date("Y-m-d");
        $status ="Open";
        $values = "'{$warranty_ID}','{$create_by}',
        '{$taskIDs}','{$status}','{$createTime}',
        '{$customer}','{$description}'";

        $insertComm = "INSERT INTO claims({$fields}) VALUES({$values})";
        //print_r($insertComm); die();
        mysqli_query($this->con,$insertComm);
        $idreturn = mysqli_insert_id($this->con);

        //add notes
        if($idreturn){
            if(is_array($notes) && count($notes)>0){
                $err = $this->add_notes($notes,$create_by,$idreturn);
            }
            //get template and insert task table
            return $idreturn;

        }else{
            $err =mysqli_error($this->con);
            return $err;
        }

    }

    //------------------------------------------------------------------
    public function upClaim($ID,$UID,$assign_task,$warranty_ID,$paid,$status,$notes,$login_by,
                            $customer=null,$invoice_amount=null,$invoice_date=null,
                            $invoice_flag=null,
                            $please_pay_flag=null,$warranty_total=null,$quote_flag=null,
                            $quote_amount=null,$quote_date=null,
                            $quote_number=null,$vendor_invoice_number=null,
                            $claim_assign=null,$claim_limit=null,$data_quote,$inactive=null,$description=null)
    {
        if(empty($inactive)) $inactive=0;
        $quote_date =$this->isDate($quote_date);
        $invoice_date =$this->isDate($invoice_date);
        $data_quote = json_decode($data_quote,true);
        $UIDs =$this->saveClaimQuote($data_quote,$ID);

        if(!is_numeric($paid) || empty($paid)) {
            $paid =0;
        }

        $updateComm = "UPDATE `claims`
                SET UID = '{$UIDs}',
                assign_task = '{$assign_task}',
                warranty_ID = '{$warranty_ID}',
                status = '{$status}',
                customer ='{$customer}',
                invoice_amount='{$invoice_amount}',

                invoice_flag ='{$invoice_flag}',
                please_pay_flag='{$please_pay_flag}',
                warranty_total='{$warranty_total}',
                quote_flag='{$quote_flag}',
                quote_amount='{$quote_amount}',

                quote_number='{$quote_number}',
                vendor_invoice_number='{$vendor_invoice_number}',
                claim_limit ='{$claim_limit}',
                inactive ='{$inactive}',
                note = '$description'";

        if(!empty($claim_assign)){
            $updateComm .= ",claim_assign='{$claim_assign}'";
        }

        if(!empty($paid) && $paid==1){
            $updateComm .= ",paid='1'";
        }

        $updateComm .=" WHERE ID = '{$ID}'";
        //die($updateComm);
        $update = mysqli_query($this->con,$updateComm);

        if($update){
            $err=array();
            if(is_array($notes) && count($notes) >0){
                $err = $this->update_notes_claim($notes,$login_by,$ID);
            }
            return array("update"=>1,"err_note"=>$err,"claim_updated_err"=>"");
        }else{
            $err = mysqli_error($this->con);
            return array("update"=>"","claim_updated_err"=>$err,"err_note"=>"");
        }

    }

    //------------------------------------------------------------------
    public function getClaim_ID($ID) {
        $query = "SELECT cl.create_by, cl.end_date, cl.start_date, cl.ID,
         cl.paid, cl.status, cl.UID,
         cl.warranty_ID, cl.assign_task,
         cl.create_by_name,cl.customer_name,cl.claim_asg_name,
         cl.customer,cl.claim_assign,
         cl.invoice_amount,cl.vendor_invoice_number,
		 cl.invoice_flag,cl.invoice_date,
		 cl.quote_amount,cl.quote_number,cl.quote_date,cl.quote_flag,
		 cl.please_pay_flag,cl.warranty_total,
		 cl.claim_limit,
		 cl.inactive,
		 cl.balance,
		 cl.invoice_id,
		 cl.order_id,
		 cl.payment,
		 cl.total,
		 cl.note,
         w.warranty_claim_limit,w.warranty_order_id,w.buyer,w.salesman,
         w.warranty_start_date,w.warranty_end_date,w.warranty_address1,
		 ct.ID as transactionID
		 from  claim_short as cl
		left JOIN claim_transaction as ct ON ct.claim_ID =cl.ID
        LEFT Join warranty_short as w ON w.ID = cl.warranty_ID
        WHERE cl.ID ='{$ID}'";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['warranty_claim_limit'] = json_decode($row["warranty_claim_limit"],true);
                $row['assign_task'] = json_decode($row['assign_task'],true);
                $row['claim_limit'] = json_decode($row['claim_limit'],true);

                $rsl = $this->getVendorsByIDs($row['UID']);
                $row['UID'] =$rsl["UIDs"];
                $row['data_quote'] =$rsl["quotedata"];
                $row['total_overage'] =$this->getOverage_contactID($row['customer']);
                $row['payment_acct'] =$this->getNotepaymentByInvoice($row['invoice_id']);
                $list[] = $row;

            }

        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getNotesByClaimsID($id){
        $query = "SELECT n.contactID,n.create_date,n.note,n.noteID,n.internal_flag,
         n.type,n.typeID,  concat(c.first_name,' ',c.last_name) as who,
         concat(cc.first_name,' ',cc.last_name) as enter_by_name,
         n.enter_by,n.description
         FROM  notes as n
                  left join contact as c on c.ID = n.contactID
                  left join contact as cc on cc.ID = n.enter_by
                where typeID = '{$id}' AND LOWER(`type`) ='claim'
                order by noteID DESC";
        //die($query);
        $rsl = mysqli_query($this->con,$query);

        $notesList = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $notesList[] = $row;
            }
        }
        return $notesList;
    }

    //------------------------------------------------
    public function getWarrantyLimit_WarrantID($ID) {
        $query = "SELECT warranty_claim_limit FROM  warranty WHERE ID = '{$ID}'";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        if(count($list)>0){
            $temp = json_decode($list[0]["warranty_claim_limit"],true);
            $list[0]["warranty_claim_limit"] = $temp;

        }
        return $list;
    }

    //------------------------------------------------
    public function claimTotalRecords($columns=null,$filterAll=null,$role=null,$id_login=null)
    {
        $num =0;
        $criteria = "";

        if(!empty($filterAll)){
            $temp = $this->columnsFilterOr($columns,$filterAll);
            $criteria .="(".$temp.")";
        }

        $criteria1 =empty($criteria)?"":" AND ".$criteria;

        if(is_array($role)){
            $sqlText = "Select count(*)
                From claim_short";
            if(!empty($criteria)){
                $sqlText .= " WHERE ".$criteria;
            }
            /* not delete
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if($level=='Admin' && $v =='Sales' || $v=="SystemAdmin"){
                $sqlText = "Select count(*)
                From claim_short";
                if(!empty($criteria)){
                    $sqlText .= " WHERE ".$criteria;
                }
            }elseif($v=="Sales"){
                $sqlText = "Select count(*)
                From claim_short
                    where customer='{$id_login}'".$criteria1;
            }elseif($v=="PolicyHolder"){
                $sqlText = "Select count(*) From claim_short
                    where customer='{$id_login}'".$criteria1;
            }elseif($v=="Employee"){
                $sqlText = "Select count(*) From claim_short
                    where  create_by='{$id_login}' || customer='{$id_login}' || claim_assign='{$id_login}'".$criteria1;
            }elseif($v=="Affiliate"){
                $sqlText = "Select count(*) From claim_short
                    where  customer='{$id_login}'".$criteria1;
            }elseif($v=="Vendor"){
                $sqlText = "Select count(*) From claim_short
                    where  create_by='{$id_login}' || UID like '%{$id_login}%'".$criteria1;
            }*/

            $num = $this->totalRecords($sqlText,0);
        }




        return $num;
    }

    //------------------------------------------------------------
    public function searchClaimList($columns=null,$filterAll=null,$limit,$offset,$role=null,$id_login=null)
    {
        $list = array();
        $criteria = "";

        if(!empty($filterAll)){
            $temp = $this->columnsFilterOr($columns,$filterAll);
            $criteria .="(".$temp.")";
        }

        $criteria .=empty($criteria)?"":" AND ";
        $criteria .= "(inactive =0 or inactive is null)";

        $criteria1 =empty($criteria)?"":" AND ".$criteria;

        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if($v =='Employee' || $v=="SystemAdmin"){
                $sqlText = "Select DISTINCT customer_name as contact_name,create_by, ID,create_by_name,
                transactionID,paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id,note
                From claim_short";
                if(!empty($criteria)){
                    $sqlText .= " WHERE ".$criteria;
                }
            }elseif($v=="Sales" || $v=="PolicyHolder" ||
                $v=="Affiliate" || $v=="Customer"){
                $sqlText = "Select DISTINCT customer_name as contact_name,create_by, ID,create_by_name,transactionID,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id,note
                From claim_short
                    where  (customer='{$id_login}' OR create_by='{$id_login}' )".$criteria1;
            }elseif($v=="Vendor"){
                $orders= $this->getContactID_login($id_login);
                if(count($orders)>0){
                    $orders = implode(',',$orders);

                    $sqlText = "Select DISTINCT UID,customer_name as contact_name,create_by, ID,create_by_name,transactionID,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id,note
                From claim_short
                   where ((ID IN ({$orders})) OR create_by='{$id_login}') ".$criteria1;
                }else{
                    $sqlText = "Select DISTINCT UID,customer_name as contact_name,create_by, ID,create_by_name,transactionID,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id,note
                From claim_short
                   where (create_by='{$id_login}') ".$criteria1;
                }

            }

            $sqlText .= " ORDER BY ID DESC";

            if(!empty($limit)){
                $sqlText .= " LIMIT {$limit} ";
            }
            if(!empty($offset)) {
                $sqlText .= " OFFSET {$offset} ";
            }

            $result = mysqli_query($this->con,$sqlText);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['service_fee'] =$this->getServiceFree_order_id($row['order_id']);
                    $list[] = $row;
                }
            }

        }

        return $list;
    }

    //------------------------------------------------------------------
    public function multiplyQualityLimit($a,$quanlity) {
        if(count($a)>0){
            foreach($a as $key=>$value){
                $a[$key]=$value*$quanlity;
            }

        }

        return $a;
    }

    //------------------------------------------------------------------
    public function process_limit($a) {
        if(count($a)>0){
            for($i=0;$i<count($a);$i++){
                if(isset($a[$i])){
                    foreach($a[$i] as $key=>$value){
                        for($j=0;$j<count($a);$j++){
                            foreach($a[$j] as $key_b=>$value_b){
                                if($i<>$j && strtolower($key)==strtolower($key_b)){
                                    $a[$i][$key]=$value + $value_b;
                                    unset($a[$j][$key_b]);
                                }
                            }
                        }
                    }
                }

            }

        }

        for($i=0;$i<count($a);$i++){
            if(isset($a[$i])){
                if(count($a[$i])==0) unset($a[$i]);
            }

        }

        return $a;
    }

    //------------------------------------------------------------------
    public function process_limit_test($a) {
        if(count($a)>0){
            for($i=0;$i<count($a);$i++){
                if(isset($a[$i])){
                    foreach($a[$i] as $key=>$value){
                        for($j=0;$j<count($a);$j++){
                            foreach($a[$j] as $key_b=>$value_b){
                                if($i<>$j && strtolower($key)==strtolower($key_b)){
                                    $a[$i][$key]=$value + $value_b;
                                    unset($a[$j][$key_b]);
                                }
                            }
                        }
                    }
                }

            }

        }

        for($i=0;$i<count($a);$i++){
            if(isset($a[$i])){
                if(count($a[$i])==0) unset($a[$i]);
            }

        }

        return $a;
    }

    //------------------------------------------------------------
    public function getClaimList_create_by($create_by)
    {
        $sqlText = "Select ID, customer_name as contact_name From claim_short
        where create_by ='{$create_by}'";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    public function addClaimTransaction($claim_ID,$claim_amount,$person,$createTime,$warranty_id)
    {
        //if(is_array($claim_amount)) $claim_amount = json_encode($claim_amount);
        $fields = "claim_ID,transaction,person,date_time,warranty_id";

        //$createTime = date("Y-m-d");

        $values = "'{$claim_ID}','{$claim_amount}','{$person}','{$createTime}','{$warranty_id}'";

        $insertComm = "INSERT INTO claim_transaction ({$fields}) VALUES({$values})";
        //print_r($insertComm); die();
        mysqli_query($this->con,$insertComm);
        $idreturn = mysqli_insert_id($this->con);
        return $idreturn;

    }

    //------------------------------------------------------------------
    public function upClaimTransaction($ID,$claim_ID,$claim_amount,$person,$date_time,$warranty_id)
    {
        //if(is_array($claim_amount)) $claim_amount = json_encode($claim_amount);
        $updateComd ="UPDATE `claim_transaction`
                SET claim_ID = '{$claim_ID}',
                transaction = '{$claim_amount}',
                person = '{$person}',
                date_time = '{$date_time}',
                warranty_id = '{$warranty_id}'
                Where ID ='{$ID}'";

        //print_r($updateComd); die();
        $issucc = mysqli_query($this->con,$updateComd);
        if($issucc) return 1;
        else return "";
    }

    //------------------------------------------------------------------
    public function getClaimTransaction($ID)
    {
        $selectComd ="Select ct.transaction, ct.claim_ID, ct.ID,
         ct.person, ct.date_time, concat(c.first_name,'',c.last_name) as person_name,
         ct.warranty_id
         from claim_transaction as ct
        left join contact as c on c.ID = ct.person
        where ct.ID ='{$ID}'";

        //print_r($selectComd); die();
        $result = mysqli_query($this->con,$selectComd);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list)>0){
                $temp = json_decode($list[0]['transaction'],true);
                $list[0]['transaction'] =$temp;
                $list[0]["claim"]=$this->getClaim_ID($list[0]["claim_ID"]);

            }
        }
        return $list;

    }

    //------------------------------------------------------------
    public function validateClaimTransFields($claim_ID,$person,$claim_amount)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($claim_ID)){
            $error = true;
            $errorMsg = "Claim ID is required.";
        }

        if(!$error && empty($person)){
            $error = true;
            $errorMsg = "Person is required.";
        }

        if(!$error && empty($claim_amount)){
            $error = true;
            $errorMsg = "Amount is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------
    public function getAssignTasks($taskIDs){
        $query ="select * from assign_task
       where id IN ({$taskIDs})";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list)>0){
//                $temp = json_decode($list[0]['transaction'],true);
//                $list[0]['transaction'] =$temp;
//                $list[0]["claim"]=$this->getClaim_ID($list[0]["claim_ID"]);

            }
        }
        return $list;
    }

    //------------------------------------------------
    public function claimTransTotalRecords($columns=null,$filterAll=null,$role=null,$id_login=null)
    {
        //$flagNotSales=$this->notSales($id_login);
        $criteria = "";

        if(!empty($filterAll)){
            $temp = $this->columnsFilterOr($columns,$filterAll);
            $criteria .="(".$temp.")";
        }

        $criteria1 =empty($criteria)?"":" AND ".$criteria;

        $sqlText = "Select count(*) From claim_transaction_short
            Where person = '{$id_login}'".$criteria1;

        if(is_array($role)){
            foreach($role as $item){
                $v = $this->protect($item["department"]);
                if(($this->protect($item['level'])=='Admin' && $v =='Sales')  || $v=="SystemAdmin"){
                    $sqlText = "Select count(*) From claim_transaction_short";

                    if(!empty($criteria)){
                        $sqlText .= " WHERE ".$criteria;
                    }
                    break;
                }
            }
        }

        //die($criteria);
        $num = $this->totalRecords($sqlText,0);

        return $num;
    }

    //------------------------------------------------------------
    public function searchClaimTransList($columns=null,$filterAll=null,$limit,$offset,$role=null,$id_login=null)
    {
        //$flagNotSales=$this->notSales($id_login);
        $criteria = "";

        if(!empty($filterAll)){
            $temp = $this->columnsFilterOr($columns,$filterAll);
            $criteria .="(".$temp.")";
        }

        $criteria1 =empty($criteria)?"":" AND ".$criteria;

        $sqlText = "Select * From claim_transaction_short
            Where person = '{$id_login}'".$criteria1;

        if(is_array($role)){
            foreach($role as $item){
                $v = $this->protect($item["department"]);
                if(($this->protect($item['level'])=='Admin' && $v =='Sales') || $v=="SystemAdmin"){
                    $sqlText = "Select * From claim_transaction_short";

                    if(!empty($criteria)){
                        $sqlText .= " WHERE ".$criteria;
                    }
                    break;
                }
            }
        }

        $sqlText .= " ORDER BY ID";

        if(!empty($limit)){
            $sqlText .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $sqlText .= " OFFSET {$offset} ";
        }

        if(!empty($orderClause)){
            // $sqlText .= " ORDER BY {$orderClause}";
        } else {
            // $sqlText .= " ORDER BY prod_name";
        }
        //$sqlText .= " ORDER BY ID";
        //die($sqlText);

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            $i=0;
            while ($row = mysqli_fetch_assoc($result)) {
                $list[$i]["ID"] = $row["ID"];
                $list[$i]["date_time"] = $row["date_time"];
                $list[$i]["transaction"] = json_decode($row["transaction"],true);
                $list[$i]["person_name"] = $row["person_name"];
                $i++;
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    public function getClaimAssignTaskByClaimID($ID)
    {
        $command ="select assign_task from `claims`
                Where ID ='{$ID}'";

        $result = mysqli_query($this->con,$command);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row["assign_task"];
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    public function save_update_task($tasks,$old_tasks,$ID=null,$login_by=null){
        $i=0;
        $tasks_fields="";
        $taskIDs = array();
        $err = array();
        $task_new = array();
        $tempDate = date("Y-m-d H:i:s");
        $date_email="";
        //"actionset,assign_id,createDate,customer_id,doneDate,dueDate,status,taskName,time";
        if(is_array($tasks)){
            foreach($tasks as $v){
                //add
                if(empty($v["id"])){
                    $values ="";
                    $tasks_fields = "";
                    $actionset="";
                    if(isset($v["actionset"])){
                        $actionset = $this->protect($v["actionset"]) ;
                        $tasks_fields .= empty($tasks_fields) ? "" : ",";
                        $tasks_fields .= "actionset";

                        $values .= empty($values) ? "" : ",";
                        $values .= "'{$actionset}'";
                    }

                    $assign_id="";
                    if(isset($v["assign_id"])){
                        $assign_id = $this->protect($v["assign_id"]);

                        if(!empty($assign_id)){
                            $tasks_fields .= empty($tasks_fields) ? "" : ",";
                            $tasks_fields .= "assign_id";

                            $values .= empty($values) ? "" : ",";
                            $values .= "'{$assign_id}'";

                            $createDate =$tempDate;//$this->protect($v["createDate"]);
                            $tasks_fields .= empty($tasks_fields) ? "" : ",";
                            $tasks_fields .= "createDate";

                            $values .= empty($values) ? "" : ",";
                            $values .= "'{$createDate}'";

                            //customer_id is id of admin login
                            $tasks_fields .= empty($tasks_fields) ? "" : ",";
                            $tasks_fields .= "customer_id";

                            $values .= empty($values) ? "" : ",";
                            $values .= "'{$login_by}'";

                            if(isset($v["dueDate"])){
                                if(!empty($v["dueDate"])){
                                    $dueDate =$this->protect($v["dueDate"]);
                                    $tasks_fields .= empty($tasks_fields) ? "" : ",";
                                    $tasks_fields .= "dueDate";

                                    $values .= empty($values) ? "" : ",";
                                    $values .= "'{$dueDate}'";
                                }else{
                                    $dateplus = strtotime("+5 day");
                                    $dueDate = date("Y-m-d H:i:s",$dateplus);

                                    $tasks_fields .= empty($tasks_fields) ? "" : ",";
                                    $tasks_fields .= "dueDate";

                                    $values .= empty($values) ? "" : ",";
                                    $values .= "'{$dueDate}'";
                                }
                            }else{
                                $dateplus = strtotime("+5 day");
                                $dueDate = date("Y-m-d H:i:s",$dateplus);

                                $tasks_fields .= empty($tasks_fields) ? "" : ",";
                                $tasks_fields .= "dueDate";

                                $values .= empty($values) ? "" : ",";
                                $values .= "'{$dueDate}'";
                            }
                            //
                        }
                    }
                    $doneDate="";
                    if(isset($v["doneDate"])){
                        if(!empty($v["doneDate"])){
                            $doneDate =$this->protect($v["doneDate"]);
                            $tasks_fields .= empty($tasks_fields) ? "" : ",";
                            $tasks_fields .= "doneDate";

                            $values .= empty($values) ? "" : ",";
                            $values .= "'{$doneDate}'";
                        }

                    }



                    $status="";
                    if(isset($v["status"])) {
                        if(!empty($v["status"])){
                            $status =$this->protect($v["status"]);
                            $tasks_fields .= empty($tasks_fields) ? "" : ",";
                            $tasks_fields .= "status";

                            $values .= empty($values) ? "" : ",";
                            $values .= "'{$status}'";
                        }
                    }

                    $taskName="";
                    if(isset($v["taskName"])) {
                        $taskName =$this->protect($v["taskName"]);
                        $tasks_fields .= empty($tasks_fields) ? "" : ",";
                        $tasks_fields .= "taskName";

                        $values .= empty($values) ? "" : ",";
                        $values .= "'{$taskName}'";
                    }
                    $time="";
                    if(isset($v["time"])) {
                        $time =$this->protect($v["time"]);
                        $tasks_fields .= empty($tasks_fields) ? "" : ",";
                        $tasks_fields .= "time";

                        $values .= empty($values) ? "" : ",";
                        $values .= "'{$time}'";
                    }

                    if(!empty($values) && !empty($assign_id)){
                        $query = "INSERT INTO assign_task ({$tasks_fields}) VALUES ({$values})";

                        mysqli_query($this->con,$query);

                        $err_temp =mysqli_error($this->con);
                        if($err_temp){
                            $err[] = array("taskName"=>$taskName,"assign_id"=>$assign_id,'taskID'=>"");
                        }else{
                            $id=mysqli_insert_id($this->con);
                            $taskIDs[]= "{$id}";

                            $this->add_taskID_email_to_assign($id,$ID,$tempDate,$login_by);
                        }

                    }else{
                        // $err[] = array("taskName"=>$taskName,"assign_id"=>$assign_id,'taskID'=>"");
                    }

                }elseif(!empty($v["id"])){
                    //update
                    $date_email="";
                    $task_new[]="{$v["id"]}";

                    $update = "UPDATE `assign_task`
                            SET ";

                    $update1 ="";

                    if(isset($v["actionset"])){
                        $update1 .=empty($update1)? "actionset = '{$this->protect($v["actionset"])}'" : ",actionset = '{$this->protect($v["actionset"])}'";
                    }

                    $assign_id ="";
                    if(isset($v["assign_id"])){
                        $update1 .=empty($update1)? "assign_id = '{$this->protect($v["assign_id"])}'" : ",assign_id = '{$this->protect($v["assign_id"])}'";
                        if(!empty($v["assign_id"])){
                            $assign_id =$v["assign_id"];

                            if(empty($v["createDate"])){
                                $update1 .=empty($update1)? "createDate = '{$tempDate}'" : ",createDate = '{$tempDate}'";
                                $date_email=$tempDate;
                            }else{
                                $date_email=$v["createDate"];
                            }

                            //customer_id is id of admin login
                            if(isset($v["customer_id"])){
                                if(empty($v["customer_id"])){
                                    $update1 .=empty($update1)? "customer_id = '{$login_by}'" : ",customer_id = '{$login_by}'";
                                }else{
                                    $customer_id =$this->protect($v["customer_id"]);
                                    $update1 .=empty($update1)? "customer_id = '{$customer_id}'" : ",customer_id = '{$customer_id}'";
                                }
                            }

                            //due date
                            if(isset($v["dueDate"])){
                                if(!empty($v["dueDate"])){
                                    $update1 .=empty($update1)? "dueDate = '{$this->protect($v["dueDate"])}'" : ",dueDate = '{$this->protect($v["dueDate"])}'";
                                }else{
                                    $dateplus = strtotime("+5 day");
                                    $dueDate = date("Y-m-d H:i:s",$dateplus);

                                    $update1 .=empty($update1)? "dueDate = '{$dueDate}'" : ",dueDate = '{$dueDate}'";
                                }
                            }else{
                                $dateplus = strtotime("+5 day");
                                $dueDate = date("Y-m-d H:i:s",$dateplus);

                                $update1 .=empty($update1)? "dueDate = '{$dueDate}'" : ",dueDate = '{$dueDate}'";
                            }


                        }
                    }

                    if(isset($v["doneDate"])){
                        if(!empty($v["doneDate"])){
                            $update1 .=empty($update1)? "doneDate = '{$this->protect($v["doneDate"])}'" : ",doneDate = '{$this->protect($v["doneDate"])}'";
                        }

                    }

                    if(isset($v["status"])){
                        if(!empty($v["status"])){
                            $update1 .=empty($update1)? "status = '{$this->protect($v["status"])}'" : ",status = '{$this->protect($v["status"])}'";
                        }
                    }

                    if(isset($v["taskName"])){
                        $update1 .=empty($update1)? "taskName = '{$this->protect($v["taskName"])}'" : ",taskName = '{$this->protect($v["taskName"])}'";
                    }

                    if(isset($v["time"])){
                        $update1 .=empty($update1)? "time = '{$this->protect($v["time"])}'" : ",time = '{$this->protect($v["time"])}'";
                    }

                    //get old_assigned
                    $old_assigned = $this->getAssignTaskByID($v["id"]);

                    if(!empty($assign_id)){
                        $update .=$update1." WHERE id = '{$v["id"]}'";
                        mysqli_query($this->con,$update);
                        $err_temp =mysqli_error($this->con);
                        if($err_temp){
                            $err[] = array("taskName"=>$v["taskName"],"assign_id"=>$v["assign_id"],'taskID'=>$this->protect($v["id"]));
                        }else{
                            $this->update_taskID_email_to_assign($v["id"],$ID,$date_email,$assign_id,$old_assigned,$login_by);
                        }
                    }else{
                        if($old_assigned!="" || $old_assigned!=null){
                            $err[] = array("taskName"=>$v["taskName"],"assign_id"=>"assign_id is required",'taskID'=>$this->protect($v["id"]));
                        }

                    }
                }
            }

            //delete
            $delete=array_diff($old_tasks,$task_new);

            foreach($delete as $item){
                $delete_task = "DELETE FROM assign_task WHERE id = '{$item}' ";
                mysqli_query($this->con,$delete_task);

                $this->delete_taskID_email_to_assign($item);
            }

            $taskIDs = array_merge($task_new,$taskIDs);

            return array("taskIDs"=>$taskIDs,"err"=>$err);
        }
    }

    //------------------------------------------------------------------
    public function addTaskTemp_taskTable($customer_id){
        $task_temp = $this->getTaskTemplate("claim");

        $tempDate = date("Y-m-d H:i:s");
        //$dateTemp = $dateTemp->format("Y-m-d H:i:s");
        if(count($task_temp) >0){
            $err =array();
            $taskIDs=array();

            foreach($task_temp as $v){
                /*foreach($v as $key=>$value){
                    $insertCommand = "INSERT INTO `assign_task` (`taskName`,`time`) VALUES('{$key}','{$value}')";

                    mysqli_query($this->con,$insertCommand);
                    $err_temp =mysqli_error($this->con);

                    if($err_temp){
                        $err[] = array("taskName"=>$value);
                    }else{
                        $id= mysqli_insert_id($this->con);
                        $taskIDs[]="{$id}";
                    }
                } */
                //
                $fields ="taskName,time";
                $taskName = $this->protect($v['task']);
                $time = $v['time'];
                $val ="'{$taskName}','{$time}'";

                if(is_numeric($v['assignto']) && !empty($v['assignto'])){
                    $fields .=",assign_id";
                    $val .=",'{$v['assignto']}'";
                }

                if(isset($v['alert'])){
                    $fields .=",alert";
                    $val .=",'{$v['alert']}'";
                }

                if(isset($v['urgent'])){
                    $fields .=",urgent";
                    $val .=",'{$v['urgent']}'";
                }

                if(!empty($val)){
                    $fields .= ",actionset,status";
                    $val .=",'claim','open'";
                    $quote_value = "({$val})";
                    $insertCommand = "INSERT INTO `assign_task` ({$fields}) VALUES {$quote_value}";

                    mysqli_query($this->con,$insertCommand);
                    $err_temp =mysqli_error($this->con);

                    if($err_temp){
                        $err[] = array("taskName"=>$taskName);
                    }else{
                        $id= mysqli_insert_id($this->con);
                        $taskIDs[]="{$id}";
                    }
                }
                //
            }
        }

        return array("taskIDs"=>$taskIDs,"err"=>$err);

    }
    //------------------------------------------------------------------
    public function getTaskTemplate($actionset){
        $query = "SELECT json_template from task_template
        where actionset = '{$actionset}' LIMIT 1";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        if(count($list) >0){
            return json_decode($list[0]["json_template"],true);
        }else{
            return $list;
        }
    }

    //------------------------------------------------------------------
    public function add_taskID_email_to_assign($task_id,$claim=null,$create_date=null,$login_by=null){
        $fields= "";
        $values ="";

        if(!empty($task_id)) {
            $fields .= empty($fields) ? "" : ",";
            $fields .= "task_id";

            $values .= empty($values) ? "" : ",";
            $values .= "'{$task_id}'";

            if(!empty($claim)) {
                $fields .= empty($fields) ? "" : ",";
                $fields .= "claim_id";

                $values .= empty($values) ? "" : ",";
                $values .= "'{$claim}'";
            }

            if(!empty($create_date)) {
                $fields .= empty($fields) ? "" : ",";
                $fields .= "create_date";

                $values .= empty($values) ? "" : ",";
                $values .= "'{$create_date}'";
            }

            if(!empty($login_by)) {
                $fields .= empty($fields) ? "" : ",";
                $fields .= "assign_by";

                $values .= empty($values) ? "" : ",";
                $values .= "'{$login_by}'";
            }

            $query = "INSERT INTO email_to_assign ({$fields}) VALUES ({$values})";
            //die($query);
            mysqli_query($this->con,$query);
        }

    }

    //------------------------------------------------------------------
    public function update_taskID_email_to_assign($task_id,$claim_id=null,$create_date=null,$assign_id=null,$old_assigned=null,$login_by=null){
        if(!empty($old_assigned)) $old_assigned = trim($old_assigned);
        if(!empty($assign_id)) $assign_id = trim($assign_id);

        if($old_assigned!=$assign_id){
            $isTrue = $this->checkAssignedID_email($claim_id,$task_id);
            if(is_numeric($isTrue) && !empty($isTrue)){
                $update = "UPDATE `email_to_assign`
                            SET task_id = '{$task_id}',
                            claim_id = '{$claim_id}',
                            create_date = '{$create_date}',
                            assign_by = '{$login_by}'
                            WHERE task_id = '{$task_id}'";

                mysqli_query($this->con,$update);
            }else{
                $this->add_taskID_email_to_assign($task_id,$claim_id,$create_date,$login_by);
            }

        }else{
            $update = "UPDATE `email_to_assign`
                            SET task_id = '{$task_id}',
                            claim_id = '{$claim_id}',
                            create_date = '{$create_date}'
                            WHERE task_id = '{$task_id}'";

            mysqli_query($this->con,$update);
        }
    }

    //------------------------------------------------------------------
    public function delete_taskID_email_to_assign($task_id){

        $delete_task = "DELETE FROM email_to_assign  WHERE task_id = '{$task_id}' ";
        mysqli_query($this->con,$delete_task);
    }

    //------------------------------------------------------------------
    public function getAssignTaskByID($id)
    {
        $command ="select assign_id from `assign_task`
                Where id ='{$id}'";

        $result = mysqli_query($this->con,$command);

        $assign_id="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $assign_id = $row["assign_id"];
            }
        }
        return $assign_id;
    }

    //------------------------------------------------------------------
    public function checkAssignedID_email($claim_id,$task_id)
    {
        $command ="select count(*) from `email_to_assign`
                Where claim_id ='{$claim_id}' AND task_id ='{$task_id}'";

        $result = mysqli_query($this->con,$command);

        $row = mysqli_fetch_row($result);

        if ($row[0] > 0)
            return 1;
        else
            return "";
    }

    //------------------------------------------------------------------
    public function getOriginLimit($warrantyID)
    {
        $command ="select `warranty_order_id`, `warranty_claim_limit` from `warranty`
                Where ID ='{$warrantyID}'";

        $result = mysqli_query($this->con,$command);
        $list=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;

    }

    //------------------------------------------------------------------
    public function getProd_orderID($order_id)
    {
        /*$command ="select `products_ordered` from `orders`
                Where order_id ='{$order_id}' AND (JSON_SEARCH(products_ordered, 'all', 'Warranty') OR
                JSON_SEARCH(products_ordered, 'all', 'A La Carte')) IS NOT NULL";*/

        $command ="select `products_ordered` from `orders`
                Where order_id ='{$order_id}'AND (json_contains(products_ordered->'$[*].prod_class', json_array('A La Carte')) OR
 json_contains(products_ordered->'$[*].prod_class', json_array('Warranty')))";

        $result = mysqli_query($this->con,$command);
        $prods="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $prods = $row['products_ordered'];
            }
        }

        $products = json_decode($prods,true);
        //because every order has only product which has 1 warranty
        $id=array();
        if(count($products)>0){
            foreach($products as $item){
                //if(strtolower($item['prod_class'])=='warranty')
                if(strtolower($item['prod_class'])=='warranty' || strtolower($item['prod_class'])=='a la carte')
                {
                    if($item['quantity']==1){
                        $id[] = $item['id'];
                    } else{
                        for($i=0;$i<$item['quantity'];$i++){
                            $id[] =$item['id'];
                        }
                    }

                }
            }
        }

        return $id;

    }

    //------------------------------------------------------------------
    public function getProdByOrderID($order_id)
    {
        $command ="select `products_ordered` from `orders`
                Where order_id ='{$order_id}' AND JSON_SEARCH(products_ordered, 'all', 'Warranty') IS NOT NULL";

        $result = mysqli_query($this->con,$command);
        $prods="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $prods = $row['products_ordered'];
            }
        }

        $products = json_decode($prods,true);
        //because every order has only product which has 1 warranty
        $id=array();
        if(count($products)>0){
            foreach($products as $item){
                //if(strtolower($item['prod_class'])=='warranty' || strtolower($item['prod_class'])=='a la carte')
                if(strtolower($item['prod_class'])=='warranty'){
                    $tmp =array();
                    $tmp['quantity']=$item['quantity'];
                    $tmp['id']=$item['id'];
                    $id[] = $tmp;
                }
            }
        }

        return $id;

    }

    //------------------------------------------------------------------
    public function getTransaction_ID($ID)
    {
        $command ="select `transaction` from `claim_transaction`
                Where warranty_id ='{$ID}'";

        $result = mysqli_query($this->con,$command);
        $warr=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $warr[] = $row['transaction'];
            }
        }

        //$trans_limit = json_decode($warr,true);
        //print_r($warr); die();  $arrTemp[] =$item;
        $arrTemp =array();
        if(count($warr)>0){
            foreach($warr as $item){
                $t = json_decode($item,true);
                foreach($t as $it){
                    $arrTemp[] =$it;
                }
            }
        }

        $claim_trans=array();
        if(count($arrTemp)>0){
            $claim_trans=  $this->process_transaction($arrTemp);
        }

        return $claim_trans;

    }

    //------------------------------------------------------------------
    public function getTransaction_ClaimID($ID,$warranty_ID)
    {
        $command ="select `transaction`,`ID` from `claim_transaction`
                Where claim_ID ='{$ID}'";

        $result = mysqli_query($this->con,$command);
        $warr=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $warr[] = $row;
            }
        }

        $ID_trans ='';
        if(count($warr)>0){
            $ID_trans=$warr[0]['ID'];
            $limit = json_decode($warr[0]['transaction'],true);
        }else{
            $limit =$this->createLimit_claimID($ID,$warranty_ID);
        }

        return array("ID_trans"=>$ID_trans, 'limit'=>$limit);

    }

    //------------------------------------------------------------------
    public function getClaim_WarrantyID($warranty_ID)
    {
        $command ="select customer_name as contact_name,create_by ,ID,create_by_name,transactionID,paid,start_date,status from `claim_short`
                Where warranty_ID ='{$warranty_ID}'";

        $result = mysqli_query($this->con,$command);
        $list=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    public function getVendor($contact_name)
    {
        $cl_limit = "Select ID as id,
        (
        CASE
            WHEN (company_name <>'' OR company_name <>null) THEN concat(first_name,' ',last_name,'-',company_name)
            ELSE concat(first_name,' ',last_name)
        END)  as text

        from `contact_detail`
         WHERE V_active = 1 AND contact_name LIKE '{$contact_name}%'";


        $result = mysqli_query($this->con,$cl_limit);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;//json_encode($list);
    }

    //------------------------------------------------------------------
    public function getEmployee($contact_name)
    {
        $cl_limit = "Select ID as id, concat(first_name,' ',last_name) as text from `contact_detail`";

        $cl_limit .=" WHERE contact_type like '%Employee%' AND contact_inactive = '0' AND
        contact_name LIKE '{$contact_name}%'";

        $result = mysqli_query($this->con,$cl_limit);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;//json_encode($list);
    }

    //------------------------------------------------------------
    public function validate_send_email($claimID,$assignedID=null)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($claimID)){
            $error = true;
            $errorMsg = "claimID is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------------
    public function getCreateby_claimID($claimID)
    {
        $query = "SELECT create_by FROM claims
        Where ID ='{$claimID}'";

        $result = mysqli_query($this->con,$query);

        $list = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =  $row['create_by'];
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getEmailCutomer_ID($ID)
    {
        $query = "SELECT concat(first_name,' ',last_name) as c_name,primary_email FROM contact
        Where ID ='{$ID}'";

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
    public function getInvs_warrantyID($warranty_ID)
    {
        $query = "SELECT warranty_order_id
        FROM warranty
        Where ID ='{$warranty_ID}'";

        $result = mysqli_query($this->con,$query);

        $orders = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $orders = $row['warranty_order_id'];
            }
        }

        $query = "SELECT invoiceid,ID,total
        FROM invoice
        Where ID IN ({$orders})";

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
    public function getWarrantyInfo_ID($warrantyID)
    {
        $query = "SELECT warranty_serial_number,buyer,
        warranty_start_date,warranty_end_date,
        warranty_closing_date,warranty_address1
        FROM warranty_short
        Where ID ='{$warrantyID}'";

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
    public function getVendors_IDs($IDs)
    {
        $query = "SELECT concat(first_name,' ',last_name) as c_name,primary_email FROM contact
        Where ID IN ({$IDs})";
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
    public function getTrans_ClaimID($claim_ID)
    {
        $command ="select ID,claim_ID,date_time,person_name,transaction
                from `claim_transaction_short`
                Where claim_ID ='{$claim_ID}'";

        $result = mysqli_query($this->con,$command);
        $warr=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['transaction'] = json_decode($row['transaction'],true);
                $warr[] = $row;
            }
        }

        return $warr;

    }

    //------------------------------------------------------------------
    public function getTrans_ClaimID_test($claim_ID)
    {
        $command ="select ID,claim_ID,date_time,person_name,transaction
                from `claim_transaction_short`
                Where claim_ID ='{$claim_ID}'";

        $result = mysqli_query($this->con,$command);
        $warr=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['transaction'] = json_decode($row['transaction'],true);
                $warr[] = $row;
            }
        }

        return $warr;

    }

    //------------------------------------------------------------------
    public function createLimit_claimID($claimID,$warranty_ID)
    {
        $claim_trans=array();
        $temp_limit=array();
        $temp_origin =array();

        $currentTemplate=array();
        if(is_numeric($warranty_ID) && $warranty_ID>0){
            //query warranty
            $list1 = $this->getOriginLimit($warranty_ID);
            if(count($list1)>0){
                //get origin limit
                $temp_origin=json_decode($list1[0]['warranty_claim_limit'],true);

                //template limit
                $temp_prod=array();
                if(!empty($list1[0]['warranty_order_id'])){

                    $p= stripos($list1[0]['warranty_order_id'],",");
                    if(is_numeric($p)){
                        $temp = explode(",",$list1[0]['warranty_order_id']);
                        //get products with class warranty or A La Carte
                        foreach($temp as $key){
                            //if(!empty($key)) $temp_prod[]=$this->getProdByOrderID($key);
                            if(!empty($key)) $temp_prod[]=$this->getProd_OrderID($key);
                        }
                    }else{
                        //$temp_prod[]=$this->getProdByOrderID($list1[0]['warranty_order_id']);
                        $temp_prod[]=$this->getProd_OrderID($list1[0]['warranty_order_id']);
                    }
                }
                //get template limit
                // print_r($temp_prod); die();
                if(count($temp_prod)>0){
                    $temp_prod1 =array_count_values($temp_prod[0]);

                    foreach($temp_prod1 as $id_p =>$value_p){
                        $currentTemplate1=array();
                        $currentTemplate1= $this->getClLimit($id_p);
                        if($value_p!=1){
                            $arr_temp=array();
                            foreach($currentTemplate1 as $kkk=>$vvv){
                                $arr_temp[$kkk]= $vvv*$value_p;
                            }
                            $currentTemplate[]=$arr_temp;

                        }else{
                            $currentTemplate[]=$currentTemplate1;
                        }
                    }

                    //print_r($currentTemplate); die();
                    //
                    /*foreach($temp_prod1 as $item){
                        if(count($item)>0){
                            foreach($item as $it){
                                $currentTemplate[]= $this->getClLimit($it);
                                //multiply quality
                                //$clTemp= $this->getClLimit($it['id']);
                                // $currentTemplate[]=$this->multipleLimit($clTemp,$it['quantity']);
                            }
                        }
                    }*/

                    $temp_limit = $this->process_limit($currentTemplate);

                }
                //get claim transaction
                $claim_trans =$this->getTransaction_ID($warranty_ID);
            }
        }

        //create row
        $list =array(); $i=0;
        if(count($temp_origin)>0){
            foreach($temp_origin as $item){
                foreach($item as $k=>$v){
                    //create origin
                    $list[$i]['name']=$k;
                    $list[$i]['original']=$v;
                    //create current
                    $not_equal=0;
                    foreach($temp_limit as $item_l){
                        foreach($item_l as $k_l=>$v_l){
                            if($k_l==$k){
                                $not_equal=1;
                                $list[$i]['current']=$v_l;
                                //print_r('--'.$k.'='.$v_l);
                            }
                        }
                    }

                    if($not_equal==0) $list[$i]['current']=0;

                    /*if(count($temp_limit)>0){
                        if(isset($temp_limit[0][$k])) {
                            $list[$i]['current']=$temp_limit[0][$k];
                        }else{
                            $list[$i]['current']=0;
                        }

                    }else{
                        $list[$i]['current']=0;
                    }*/
                    //create transaction
                    if(count($claim_trans)>0){
                        foreach($claim_trans as $item_trans){
                            if(count($item_trans) >0){
                                if(isset($item_trans['name'])){
                                    if($item_trans['name']==$k){
                                        $list[$i]['transaction']=$item_trans['claim'];
                                        break;
                                    }
                                }
                            }

                        }
                    }else{
                        $list[$i]['transaction']=0;
                    }

                    $i++;
                }
            }
        }

        //print_r($claim_trans); echo "---";
        // print_r($temp_limit); echo "---";
        //print_r($list); echo "---";

        return $list;

    }

    //------------------------------------------------
    public function getVendor_CompanyByName($name) {
        //company
        $query = "SELECT ID as id,name as full_name,state,city,
      if(w9_exp <= NOW(),'Yes','No') as expired,w9_exp,
      if(license_exp <= NOW(),'Yes','No') as license_expired,license_exp,
      if(insurrance_exp <= NOW(),'Yes','No') as insurrance_expired,insurrance_exp
        FROM  company_short
        where full_name LIKE '%{$name}%'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row["type"]="company";
                $row["id"]="c".$row["id"];
                $list[] = $row;
            }

        }

        //Contact
        $query = "SELECT ID as id,contact_name as full_name,primary_state as state,
      primary_city as city,if(w9_exp <= NOW(),'Yes','No') as expired,w9_exp,
      if(license_exp <= NOW(),'Yes','No') as license_expired,license_exp,
      if(insurrance_exp <= NOW(),'Yes','No') as insurrance_expired,insurrance_exp
        FROM  contact_short
        where contact_type LIKE '%Vendor%' AND c_name LIKE '%{$name}%' AND
        contact_inactive =0";

        $result = mysqli_query($this->con,$query);

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row["type"]="vendor";
                $row["id"]="v".$row["id"];
                $list[] = $row;
            }

        }

        return $list;
    }
    //------------------------------------------------
    public function getIDsClaimQuoteOld_ClaimID($claimID) {

        //company
        $query = "SELECT id
        FROM  claim_quote
        where claimID = '{$claimID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['id'];
            }

        }
        return $list;
    }

    //------------------------------------------------
    public function saveClaimQuote($data,$claimID){
        $old = $this->getIDsClaimQuoteOld_ClaimID($claimID);
        $ret_id=array();
        //update insert delete
        $err_temp=array();
        if(is_array($data)){
            foreach($data as $v){
                if(empty($v["id"])){
                    $quote_value="";
                    //add new
                    $compare_type='';
                    $compare_typeID='';
                    //create field and value for insert or update
                    $fields = "";
                    $val ="";
                    foreach($v as $key=>$item){
                        //create new array
                        switch($key){
                            case "claimID":
                                $fields .= empty($fields) ? "" : ",";
                                $fields .= "{$key}";

                                $vv = $this->protect($item);
                                $val .= empty($val) ? "" : ",";
                                $val .= "'{$vv}'";
                                break;
                            case "type":
                                $fields .= empty($fields) ? "" : ",";
                                $fields .= "{$key}";

                                $vv = $this->protect($item);
                                $val .= empty($val) ? "" : ",";
                                $val .= "'{$vv}'";
                                //compare
                                $compare_typeID =$item;

                                break;
                            case "typeID":
                                $fields .= empty($fields) ? "" : ",";
                                $fields .= "{$key}";

                                $vv = $this->protect($item);
                                $val .= empty($val) ? "" : ",";
                                $val .= "'{$vv}'";

                                //compare
                                $compare_type =$item;
                                break;
                            case "quote":
                                $fields .= empty($fields) ? "" : ",";
                                $fields .= "{$key}";
                                $item = json_encode($item);
                                $vv = $this->protect($item);
                                $val .= empty($val) ? "" : ",";
                                $val .= "'{$vv}'";
                                break;
                        }
                    }

                    if(!empty($val)){
                        $quote_value .= "({$val})";
                        $query = "INSERT INTO claim_quote ({$fields}) VALUES {$quote_value}";
                        mysqli_query($this->con,$query);
                        $ret_id[] = mysqli_insert_id($this->con);
                        $err_temp =mysqli_error($this->con);
                        if($err_temp){
                            $err[]=array("error"=>$err_temp,"type"=>$compare_type,"typeID"=>$compare_typeID);

                        }
                    }

                }elseif(!empty($v["id"])){
                    $ret_id[]=$v["id"];
                    $v["claimID"] = $this->protect($v["claimID"]);
                    $v["type"] = $this->protect($v["type"]);
                    $v["typeID"] = $this->protect($v["typeID"]);
                    $v["quote"] = json_encode($v["quote"]);
                    $v["quote"] = $this->protect($v["quote"]);
                    //update
                    $update = "UPDATE `claim_quote`
                                SET claimID = '{$v["claimID"]}',
                                type = '{$v["type"]}',
                                typeID = '{$v["typeID"]}',
                                quote = '{$v["quote"]}'
                                WHERE ID = '{$v["id"]}'";

                    mysqli_query($this->con,$update);
                    $err_temp =mysqli_error($this->con);
                    if($err_temp){
                        $err[]=array("error"=>$err_temp,"type"=>$v['type'],"typeID"=>$v['typeID']);
                    }
                }
            }
            //delete
            $diff=array_diff($old,$ret_id);
            foreach($diff as $item){
                $delete = "DELETE FROM claim_quote WHERE id = '{$item}' ";
                mysqli_query($this->con,$delete);
            }
            // die();
        }
        return json_encode($ret_id);
    }
    //------------------------------------------------------------------
    public function getVendorsByIDs($IDs)
    {
        $IDs = json_decode($IDs,true);
        if(count($IDs)>0){
            $IDs = implode(",",$IDs);
        }else{
            $IDs = 0;
        }
        $query = "SELECT id,claimID,type,typeID,quote
        FROM claim_quote
        Where id IN ({$IDs})";
        $result = mysqli_query($this->con,$query);

        $uid_list = array();
        $quotedata_list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if($row['type']=='company'){
                    $uid_list[]=$this->getVendorUIDs_type($row['typeID'],'company');
                }elseif($row['type']=='vendor'){
                    $uid_list[]=$this->getVendorUIDs_type($row['typeID'],'vendor');
                }
                $row['quote']=json_decode($row['quote'],true);
                $quotedata_list[]=$row;
            }
        }

        return array("UIDs"=>$uid_list,"quotedata"=>$quotedata_list);
    }
    //------------------------------------------------
    public function getVendorUIDs_type($typeID,$type) {
        $list = array();
        //company
        if($type=="company"){
            $query = "SELECT ID as id,name as full_name,state,city,
            if(w9_exp <= NOW(),'Yes','No') as expired,w9_exp,
            if(license_exp <= NOW(),'Yes','No') as license_expired,license_exp,
            if(insurrance_exp <= NOW(),'Yes','No') as insurrance_expired,insurrance_exp
            FROM  company_short
            where ID = '{$typeID}'";

            $result = mysqli_query($this->con,$query);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row["type"]="company";
                    $row["id"]="c".$row["id"];
                    $list = $row;
                }

            }
        }elseif($type=="vendor"){
            //Contact
            $query = "SELECT ID as id,contact_name as full_name,primary_state as state,
      primary_city as city,if(w9_exp <= NOW(),'Yes','No') as expired,w9_exp,
      if(license_exp <= NOW(),'Yes','No') as license_expired,license_exp,
      if(insurrance_exp <= NOW(),'Yes','No') as insurrance_expired,insurrance_exp
        FROM  contact_short
        where ID = '{$typeID}'";

            $result = mysqli_query($this->con,$query);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row["type"]="vendor";
                    $row["id"]="v".$row["id"];
                    $list = $row;
                }
            }
        }

        return $list;
    }
    //------------------------------------------------
    public function addClaimTemplate($object,$type){
        $selectCommand ="SELECT COUNT(*) AS NUM FROM task_template WHERE  `actionset` ='{$type}'";
        if ($this->checkExists($selectCommand)){
            $updateComm = "UPDATE `task_template`
                SET json_template = '{$object}'
                WHERE actionset = '{$type}'";

            $update = mysqli_query($this->con,$updateComm);
            if($update){
                return 1;
            }else{
                return mysqli_error($this->con);
            }
        }else{
            $fields = "actionset,json_template";
            $values = "'{$type}','{$object}'";

            $insertCommand = "INSERT INTO task_template({$fields}) VALUES({$values})";
            mysqli_query($this->con,$insertCommand);
            $idreturn = mysqli_insert_id($this->con);
            if(is_numeric($idreturn) && $idreturn){
                return $idreturn;
            }else{
                return mysqli_error($this->con);
            }
        }

    }

    //------------------------------------------------
    public function claimEmailFormat($data_quote,$warranty_ID){
        $data = json_decode($data_quote,true);
        //print_r($data_quote); die();
        $inv_html=''; $total=0;
        if(is_array($data)){
            foreach($data as $v){
                $inv_html .='<tr>
            <td style="border: 1px solid black; text-align: center">' . $v["inv_date"] . '</td>
            <td style="border: 1px solid black; text-align: center">' . $v["inv_num"] . '</td>
        </tr>';

                $total =$total+$v["inv_amount"];
            }
        }else{
            $inv_html='<tr>
            <td style="border: 1px solid black; text-align: center"></td>
            <td style="border: 1px solid black; text-align: center"></td>
        </tr>';
        }

        //get warranty info
        $waInf = $this->getWarrantyInfo_ID($warranty_ID);
        $HTMLContent='
		<table width="700px" border="0">
		  <tbody >
			<tr style="color:black!important;" class="black">
			  <td width="20%"><img src="https://www.freedomhw.com//wp-content/uploads/2018/12/Freedom-Home-Warranty-Logo-RGB-01.png"  alt="Freedom HW Logo" style="max-width:150px"/></td>
			  <td width="50%" style="padding-left:10px"><h3 style="font-size:20px"><strong>Freedom Home Warranty</strong></h3>
					707 24th Street <br>
					Ogden, UT 84401 <br>
					Accounting@FreedomHW.com</td>
			  <td width="30%" class="wide30">
				  <div style="text-align: center;width:100%"><strong>INVOICE</strong></div>
				  <table style="float:right; border: 1px solid black;color:black;" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th style="border: 1px solid black;">Date</th>
											<th style="border: 1px solid black;">Invoice #</th>
										</tr>
									</thead>
									<tbody>'.$inv_html.'
									</tbody>

								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
						<br>
						<table width="350px" style="border: 1px solid black; padding:5px;color:black;" cellpadding="0" cellspacing="0">
						  <tbody>
							<tr >
							  <td  style="border-bottom: 1px solid black; text-align: center; font-size: 15px;" width="100%">WARRANTY INFO:</td>
							</tr>
							<tr>
							  <td style="padding:5px;" width="100%">' . $waInf['buyer']. '<br>
								' . $waInf['warranty_address1'] . ' <br>
								' . $waInf['warranty_address2'] . ' <br>
							  ' .  $waInf['warranty_city'] . ', ' . $waInf['warranty_state'] . ' ' . $waInf['warranty_postal_code']  . '</td>

							</tr>
						  </tbody>
						</table>
						<br>
						<table width="700px" style="border: 1px solid black;color:black;" cellpadding="0" cellspacing="0">
						  <tbody>
							<tr style="border: 1px solid black;" align="center">
							  <td width="20%" style="border: 1px solid black; text-align:center;">Item</td>
							  <td width="50%" style="border: 1px solid black;text-align:center;">Description</td>
							  <td width="30%" style="border: 1px solid black;text-align:center;">Amount</td>
							</tr>
							<tr align="center">
							  <td colspan="2" style="border: 1px solid black;" align="right"><strong></strong></td>
								<td width="30%" style="border: 1px solid black; padding:5px" align="right"><strong>Total Due:  $' . $total . '</strong></td>
							</tr>
						  </tbody>
						</table>';

        // PUT YOUR HTML IN A VARIABLE
        $my_html='<html lang="en">
              <head>
                <meta charset="UTF-8">
              </head>
              <body>
              ' . $HTMLContent .'
              </body>
            </html>';

        return $my_html;
    }

    //------------------------------------------------------------------
    public function createOrderID_cl($bill_to,$total,
                                     $order_title,$order_create_by,$claimID){
        $subscription='{}';
        $invDate =date('Y-m-d');
        if(empty($total)) $total=0;

        $obOrder = new Orders();
        $prod=$obOrder->getServceFeeProd();
        $prod_it=array(
            "id"=>$prod['ID'],
            "quantity"=>"1",
            "sku"=>$prod['SKU'],
            "prod_name"=>$prod['prod_name'],
            "prod_class"=>$prod['prod_class'],
            "price"=>$prod['prod_price'],
            "line_total"=>$prod['prod_price']
        );

        $order_prod[] =$prod_it;
        $order_prod = json_encode($order_prod);
        //create order
        $balance =$total;
        $payment=0;
        $note='';
        $salesperson=0;
        $warranty=0;
        $notes=array();
        $discount_code='';

        $invID='';

        $orderID = $obOrder->addOrder($order_prod,
            $balance,$bill_to,$note,$payment,
            $salesperson,$total,$warranty,$notes,$order_title,$subscription,
            $discount_code,$order_create_by);

        if(is_numeric($orderID) && $orderID){
            //add PaymentSchedule
            $obOrder->addNewPaymentSchedule($orderID,$invDate,$total);
            //add invoice
            $invoiceid = date('Y').strtotime("now");
            $ledger =array();
            $invID = $obOrder->autoAddInvoice($balance,$bill_to,$invoiceid,$orderID,$payment,
                $salesperson,$total,$ledger,$notes,$payment,$invDate,$claimID);
        }

        $obOrder->close_conn();
        unset($obOrder);

        return array("invID"=>$invID,"orderID"=>$orderID);

    }

    //------------------------------------------------------------------
    public function getServiceFree_order_id($order_id)
    {
        $query = "SELECT products_ordered
        FROM orders
        Where order_id = '{$order_id}' AND (
         json_contains(products_ordered->'$[*].prod_class', json_array('Service Fee'))AND
                            products_ordered->'$[*].id' <> json_array('Select product')
        )";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['products_ordered'] = json_decode($row['products_ordered'],true);
                $list=$row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getContactID_login($login_id)
    {
        $query = "SELECT claimID
        FROM claim_quote
        Where typeID = '{$login_id}'";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[]=$row["claimID"];
            }
        }

        return $list;
    }

//------------------------------------------------------------
    /*
    public function dashboardClaimList($limitDay,$id_login,$role=null,$start_date=null,$end_date=null)
    {
        $interval="(`start_date` > (NOW() - INTERVAL '{$limitDay}' DAY))";

        if(empty($limitDay)){
            if(!empty($start_date) && !empty($end_date)){
                $interval = "`start_date` >= '{$start_date}'";
                $interval .= "AND `start_date` <= '{$end_date}'";
            }elseif(!empty($start_date) && empty($end_date)){
                $interval = "`start_date` >= '{$start_date}'";
            }elseif(empty($start_date) && !empty($end_date)){
                $interval = "`start_date` <= '{$end_date}'";
            }
        }

        $interval =empty($interval)?"":" AND ".$interval;

        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if($v=="Sales" || $v=="PolicyHolder" || $v=="Employee" ||
                $v=="Affiliate" || $v=="Customer" || $v=="SystemAdmin"){
                $sqlText = "Select DISTINCT customer_name as contact_name,create_by, ID,create_by_name,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id
                From claim_short
                    where  (customer='{$id_login}' OR create_by='{$id_login}' OR
                     claim_assign ='{$id_login}') ".$interval;
            }elseif($v=="Vendor"){
                $orders= $this->getContactID_login($id_login);
                if(count($orders)>0){
                    $orders = implode(',',$orders);

                    $sqlText = "Select DISTINCT UID,customer_name as contact_name,create_by, ID,create_by_name,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id
                From claim_short
                   where ((ID IN ({$orders})) OR create_by='{$id_login}') ".$interval;
                }else{
                    $sqlText = "Select DISTINCT UID,customer_name as contact_name,create_by, ID,create_by_name,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id
                From claim_short
                   where (create_by='{$id_login}') ".$interval;
                }

            }

            $sqlText .= " ORDER BY ID";
            //die($sqlText);
            if(!empty($limit)){
                $sqlText .= " LIMIT {$limit} ";
            }
            if(!empty($offset)) {
                $sqlText .= " OFFSET {$offset} ";
            }

            $result = mysqli_query($this->con,$sqlText);
            $list=array();
            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['service_fee'] =$this->getServiceFree_order_id($row['order_id']);
                    $list[] = $row;
                }
            }

        }

        return $list;
    }
    */
    public function dashboardClaimList($limitDay,$id_login,$role=null,$start_date=null,$end_date=null)
    {
        $interval="(`start_date` > (NOW() - INTERVAL '{$limitDay}' DAY))";

        if(empty($limitDay)){
            if(!empty($start_date) && !empty($end_date)){
                $interval = "`start_date` >= '{$start_date}'";
                $interval .= "AND `start_date` <= '{$end_date}'";
            }elseif(!empty($start_date) && empty($end_date)){
                $interval = "`start_date` >= '{$start_date}'";
            }elseif(empty($start_date) && !empty($end_date)){
                $interval = "`start_date` <= '{$end_date}'";
            }
        }

        $interval1 =empty($interval)?"":" AND ".$interval;

        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if($v =='Employee' || $v=="SystemAdmin"){
                $sqlText = "Select DISTINCT customer_name as contact_name,create_by, ID,create_by_name,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id,note
                From claim_short";
                if(!empty($interval)){
                    $sqlText .= " WHERE ".$interval;
                }
            }elseif($v=="Sales" || $v=="PolicyHolder" ||
                $v=="Affiliate" || $v=="Customer"){
                $sqlText = "Select DISTINCT customer_name as contact_name,create_by, ID,create_by_name,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id,note
                From claim_short
                    where  (customer='{$id_login}' OR create_by='{$id_login}' )".$interval1;
            }elseif($v=="Vendor"){
                $orders= $this->getContactID_login($id_login);
                if(count($orders)>0){
                    $orders = implode(',',$orders);

                    $sqlText = "Select DISTINCT UID,customer_name as contact_name,create_by, ID,create_by_name,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id,note
                From claim_short
                   where ((ID IN ({$orders})) OR create_by='{$id_login}') ".$interval1;
                }else{
                    $sqlText = "Select DISTINCT UID,customer_name as contact_name,create_by, ID,create_by_name,
                paid,start_date,status,claim_assign as claim_assign_id,
                claim_asg_name,order_id,note
                From claim_short
                   where (create_by='{$id_login}') ".$interval1;
                }

            }

            /////////////////

            $sqlText .= " ORDER BY ID DESC";
            //die($sqlText);
            /*if(!empty($limit)){
                $sqlText .= " LIMIT {$limit} ";
            }
            if(!empty($offset)) {
                $sqlText .= " OFFSET {$offset} ";
            }*/

            $result = mysqli_query($this->con,$sqlText);
            $list=array();
            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['service_fee'] =$this->getServiceFree_order_id($row['order_id']);
                    $list[] = $row;
                }
            }

        }

        return $list;
    }
    //------------------------------------------------------------------
    public function getNotepaymentByInvoice($invoice_id)
    {
        $query = "SELECT *
        FROM pay_acct
        Where invoice_id = '{$invoice_id}'";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[]=$row;
            }
        }

        return $list;
    }


    /////////////////////////////////////////////////////////
}
