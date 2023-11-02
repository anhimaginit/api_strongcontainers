<?php
require_once 'class.common.php';
require_once 'class.calendar.php';
require_once 'class.depot.php';
class Task extends Common{
    //------------------------------------------------------------
    public function validate_task_fields($taskName)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($taskName)){
            $error = true;
            $errorMsg = "Name is required.";
        }
        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------------
    public function AddNewTask($actionset,$assign_id,$content,$customer_id,
                               $doneDate,$dueDate,$status,$taskName,$time,$alert=null,$urgent=null,
                               $assign_order=null,$assign_driver_id=null,$deliverydate=null,$product_sku=null,
                               $file_pickup_name=null,$file_delivery_name=null)
    {
        $doneDate = $this->is_Date($doneDate);
        $dueDate = $this->is_Date($dueDate);
        $deliverydate = $this->is_Date1($deliverydate);

        $obj_calendar = new Calendar();
        //$createDate =date("Y-m-d");
        $available =array();
        if($deliverydate !='' && $assign_driver_id !=''){
            $available = $obj_calendar->check_driver_in_calendar($assign_driver_id,$deliverydate,'');
            if($available["driver"] !=''){
                return $available["driver"];
            }
        }

        if(empty($assign_id)) $assign_id=0;
        if(empty($customer_id)) $customer_id=0;
        $fields = "actionset,content,
        status,taskName";

        $values = "'{$actionset}','{$content}',
        '{$status}','{$taskName}'";

        if(!empty($time)){
            $fields .= ",time";
            $values .=",'{$time}'";
        }
        if(!empty($alert)){
            $fields .= ",alert";
            $values .=",'{$alert}'";
        }

        if(!empty($urgent)){
            $fields .= ",urgent";
            $values .=",'{$alert}'";
        }

        if(!empty($doneDate)){
            $fields .= ",doneDate";
            $values .=",'{$doneDate}'";
        }
        if(!empty($dueDate)){
            $fields .= ",dueDate";
            $values .=",'{$dueDate}'";
        }

        if(!empty($deliverydate)){
            $fields .= ",delivery_date";
            $values .=",'{$deliverydate}'";
        }

        if($assign_order !=''){
            $fields .= ",assign_order";
            $values .=",'{$assign_order}'";
            if($assign_driver_id !=''){
                $fields .= ",assign_id";
                $values .=",'{$assign_driver_id}'";
            }
        }else{
            if(isset($assign_id) && $assign_id !=''){
                $fields .= ",assign_id";
                $values .=",'{$assign_id}'";
            }
        }

        if(isset($customer_id) && $customer_id !=''){
            $fields .= ",customer_id";
            $values .=",'{$customer_id}'";
        }

        if(isset($product_sku) && $product_sku !=''){
            $fields .= ",product_sku";
            $values .=",'{$product_sku}'";
        }

        if(isset($file_pickup_name) && $file_pickup_name !=''){
            $fields .= ",file_pickup_name";
            $values .=",'{$file_pickup_name}'";
        }

        if(isset($file_delivery_name) && $file_delivery_name !=''){
            $fields .= ",file_delivery_name";
            $values .=",'{$file_delivery_name}'";
        }

        $insertComm = "INSERT INTO assign_task({$fields}) VALUES({$values})";
       // print_r($insertComm); die();
        mysqli_query($this->con,$insertComm);
        $idreturn = mysqli_insert_id($this->con);

        if($idreturn){
            if(count($available) >0){
                if($available["driver"] =='' && $available["ignore"] ==''){
                   $calendar_id = $obj_calendar->new_calendar($deliverydate,$idreturn,$assign_driver_id);
                }
            }
            //update total
            if(isset($assign_order) && $assign_order !='' &&
                isset($product_sku) && $product_sku !='' &&
                isset($assign_driver_id) && $assign_driver_id !=''){
                $obj_depot = new Depot();
                $total = $obj_depot->calulate_driver_rate($assign_order,$product_sku,$assign_driver_id);
                if($total > 0){
                    $array_primary =array('id'=>$idreturn);
                    $arr_key_value =array('driver_total'=>$total);
                    $obj_depot->update_table('assign_task',$array_primary,$arr_key_value);
                }
            }

            return $idreturn;
        }else{
            $err =mysqli_error($this->con);
            return $err;
        }

    }

    //------------------------------------------------------
    public function updateTask($id,$actionset,$assign_id,$content,$customer_id,
                               $doneDate,$dueDate,$status,$taskName,$time,$alert=null,$urgent=null,
                               $assign_order=null,$assign_driver_id=null,$deliverydate=null,$product_sku=null,
                               $file_pickup_name=null, $file_delivery_name=null)
    {
        $doneDate = $this->is_Date($doneDate);
        $dueDate = $this->is_Date($dueDate);
        $deliverydate = $this->is_Date1($deliverydate);
        //echo "<pre>";print_r($deliverydate);echo "</pre>"; die();
        $obj_calendar = new Calendar();
        $available =array();
        if($deliverydate !='' && $assign_driver_id !=''){
            $available = $obj_calendar->check_driver_in_calendar($assign_driver_id,$deliverydate,$id);
            //echo "<pre>";print_r($available);echo "</pre>"; die();
            if($available["driver"] !=''){
                return $available["driver"];
            }
        }
        if(empty($assign_id)) $assign_id=0;
        if(empty($customer_id)) $customer_id=0;

        $updateComm = "UPDATE `assign_task`
                SET actionset = '{$actionset}',
                content = '{$content}',
                status = '{$status}',
                taskName = '{$taskName}'";
        if(!empty($alert)){
            $updateComm .=",alert = '{$alert}'";
        }
        if(!empty($urgent)){
            $updateComm .=",urgent = '{$urgent}'";
        }
        if(!empty($time)){
            $updateComm .=",time = '{$time}'";
        }

        if(!empty($dueDate)){
            $updateComm .=",dueDate = '{$dueDate}'";
        }

        if(!empty($doneDate)){
            $updateComm .=",doneDate = '{$doneDate}'";
        }

        if($assign_order !=''){
            $updateComm .=",assign_order = '{$assign_order}'";
            if($assign_driver_id !=''){
                $updateComm .=",assign_id = '{$assign_driver_id}'";
                $assign_id_tbl = $this->return_id("SELECT assign_id FROM assign_task WHERE id='{$id}'","assign_id");
                $date_accept = $this->return_id("SELECT date_accept FROM assign_task WHERE id='{$id}'","date_accept");
                //reset date $assign_id_tbl !=$assign_driver_id
                if($date_accept !=null && $date_accept !='1970-01-01 00:00:00'){
                    if($assign_id_tbl !=$assign_driver_id){
                        $updateComm .=",date_accept = '1970-01-01 00:00:00'";
                    }
                }
            }
            if(!empty($deliverydate)){
                $updateComm .=",delivery_date = '{$deliverydate}'";
            }
        }else{
            if(isset($assign_id) && $assign_id !='') $updateComm .=",assign_id = '{$assign_id}'";
        }

        if(isset($customer_id) && $customer_id !='') $updateComm .=",customer_id = '{$customer_id}'";
        if(isset($product_sku) && $product_sku !='') $updateComm .=",product_sku = '{$product_sku}'";
        if(!empty($file_pickup_name)) $updateComm .=",file_pickup_name = '{$file_pickup_name}'";
        if(!empty($file_delivery_name))$updateComm .=",file_delivery_name = '{$file_delivery_name}'";

        $updateComm .="WHERE id='{$id}'";
        //die($updateComm);
        $update = mysqli_query($this->con,$updateComm);
        if($update){
            if(count($available) >0){
                if($available["driver"] =='' && $available["ignore"] ==''){
                    if($available["calendar_id"] != ""){
                        $obj_calendar->update_calendar($available["calendar_id"],$deliverydate,$id,$assign_driver_id);
                    }else{
                        $obj_calendar->new_calendar($deliverydate,$id,$assign_driver_id);
                    }
                }
            }

            if(isset($assign_order) && $assign_order !='' &&
                isset($product_sku) && $product_sku !='' &&
                isset($assign_driver_id) && $assign_driver_id !=''){
                $obj_depot = new Depot();
                $total = $obj_depot->calulate_driver_rate($assign_order,$product_sku,$assign_driver_id);
                if($total > 0){
                    $array_primary =array('id'=>$id);
                    $arr_key_value =array('driver_total'=>$total);
                    $obj_depot->update_table('assign_task',$array_primary,$arr_key_value);
                }
            }

            return 1;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------------
    public function getTasks($taskName=null)
    {

        $sqlText = "Select * From assign_task_short";
        if(!empty($taskName)){
            $sqlText .= " WHERE (actionset like '%{$taskName}%' OR
            content like '%{$taskName}%' OR
            taskName like '%{$taskName}%' OR
            assign_name like '%{$taskName}%' OR
            cus_name like '%{$taskName}%' OR
            status like '%{$taskName}%')";
        }

        $sqlText .= " ORDER BY id DESC
        LIMIT 1000";
        $result = mysqli_query($this->con,$sqlText);
        //die($sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------
    public function getTaskByID($taskID)
    {
        $sqlText = "Select * From assign_task_short
        WHERE ID='{$taskID}' limit 1";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if($row['actionset'] =='claim'){
                    $row['claimID'] = $this->taskBelongtoClaim($row['id']);
                }
                $list = $row;
            }
        }

       return $list;

    }

    //------------------------------------------------------
    public function taskBelongtoClaim($taskID)
    {
        $sqlText = "Select ID From claims
        where JSON_CONTAINS(assign_task->'$[*]', JSON_ARRAY('{$taskID}'))";

        $result = mysqli_query($this->con,$sqlText);

        $claimID = "";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $claimID = $row['ID'];
            }
        }
        return $claimID;
    }

    //------------------------------------------------------
    public function previous_nextBtn($ID,$greater,$table,$role,$login_id)
    {
        $id_select ='ID';
        $v = $this->protect($role[0]["department"]);

        switch ($table){
            case "assign_task":
                if($greater ==1){
                    $sqlText = "Select ID From assign_task
                    where ID > '{$ID}' ORDER BY ID LIMIT 1";
                }else{
                    $sqlText = "Select ID From assign_task
                    where ID < '{$ID}' ORDER BY ID DESC LIMIT 1";
                }
                break;
            case "claims":
                if($greater ==1){
                    $ID1= "ID > '{$ID}'";
                    $des = " ID ";
                }else{
                    $ID1= "ID < '{$ID}'";
                    $des = " ID DESC ";
                }
                $sqlText = "Select ID From claims
                    where ".$ID1." AND ID IN (
                    Select DISTINCT ID
                    From claim_short
                    where  (customer='{$login_id}' OR create_by='{$login_id}' )
                    )

                    AND (inactive =0 OR inactive IS NULL) ORDER BY ".$des." LIMIT 1";

                if($v =='Employee' || $v=="SystemAdmin"){
                    $sqlText = "Select ID From claims
                    where ".$ID1." AND (inactive =0 OR inactive IS NULL) ORDER BY ".$des." LIMIT 1";
                }elseif($v=="Vendor"){
                    $claimIDs = $this->getClaim_login($login_id);
                    if(count($claimIDs)>0){
                        $claimID = implode(",",$claimIDs);
                    }else{
                        $claimID = 0;
                    }

                    $sqlText = "Select ID From claims
                    where ".$ID1." AND ID IN (Select ID From claim_short
                           where ((ID IN ({$claimID})) OR create_by='{$login_id}')
                    )

                    AND (inactive =0 OR inactive IS NULL) ORDER BY ".$des." LIMIT 1";
                }

                break;
            case "contact":
                if($greater ==1){
                    $ID1= "ID > '{$ID}'";
                    $des = " ID ";
                }else{
                    $ID1= "ID < '{$ID}'";
                    $des = " ID DESC ";
                }

                $sqlText = "Select ID From contact
                    where ".$ID1." AND ID IN (
                        Select DISTINCT c.ID
                        From quote_short as o
                        Left Join contact_short
                         as c ON o.s_ID = c.ID
                        where o.b_ID = '{$login_id}'
                        UNION
                        Select DISTINCT c.ID
                        From contact_short as c
                        where (c.ID ='{$login_id}' OR c.create_by='{$login_id}')
                    )

                    AND (contact_inactive =0 OR contact_inactive IS NULL) ORDER BY ".$des." LIMIT 1";

                $level = $this->protect($role[0]['level']);
                if(($level=='Admin' && $v =='Sales') || $v =="Employee" || $v=="SystemAdmin"){
                    $sqlText = "Select ID From contact
                    where ".$ID1." AND (contact_inactive =0 OR contact_inactive IS NULL) ORDER BY ".$des." LIMIT 1";
                }

                break;
            case "company":
                if($greater ==1){
                    $sqlText = "Select ID From company
                    where ID > '{$ID}' ORDER BY ID LIMIT 1";
                }else{
                    $sqlText = "Select ID From company
                    where ID < '{$ID}' ORDER BY ID DESC LIMIT 1";
                }
                break;
            case "invoice":
                if($greater ==1){
                    $ID1= "ID > '{$ID}'";
                    $des = " ID ";
                }else{
                    $ID1= "ID < '{$ID}'";
                    $des = " ID DESC ";
                }

                $sqlText = "Select ID From invoice
                    where ".$ID1." AND ID IN (
                    Select ID
                     From invoice_short
                     where (customer = '{$login_id}' OR invoice_create_by ='{$login_id}')
                    )
                    ORDER BY ".$des." LIMIT 1";

                if($v=="Employee"|| $v=="SystemAdmin"){
                    $sqlText = "Select ID From invoice
                    where ".$ID1." ORDER BY ".$des." LIMIT 1";
                }elseif($v=="Sales"){
                    $sqlText = "Select ID From invoice
                    where ".$ID1." AND ID IN (
                    Select ID
                     From invoice_short
                     where (s_contactID = '{$login_id}' OR invoice_create_by ='{$login_id}')
                    )
                    ORDER BY ".$des." LIMIT 1";
                }

                break;
            case "warranty":
                if($greater ==1){
                    $ID1= "ID > '{$ID}'";
                    $des = " ID ";
                }else{
                    $ID1= "ID < '{$ID}'";
                    $des = " ID DESC ";
                }

                $sqlText = "Select ID From warranty
                    where ".$ID1." AND ID IN (
                     Select DISTINCT w.ID From warranty_short AS w
                    where (w.buyer_id = '{$login_id}' OR w.warranty_create_by = '{$login_id}')
                    )

                    ORDER BY ".$des." LIMIT 1";

                if($v=="Employee"|| $v=="SystemAdmin"){
                    $sqlText = "Select ID From warranty
                    where ".$ID1." ORDER BY ".$des." LIMIT 1";
                }elseif($v=="Affiliate"){
                    $sqlText = "Select ID From warranty
                    where ".$ID1." AND ID IN (
                     Select DISTINCT w.ID From warranty_short AS w
                    where (
                        af_s_contactID= '{$login_id}' OR af_b_contactID= '{$login_id}' OR
                        af_m_contactID= '{$login_id}' OR af_t_contactID= '{$login_id}')
                    )

                    ORDER BY ".$des." LIMIT 1";
                }elseif($v=="Sales"){
                    $sqlText = "Select ID From warranty
                    where ".$ID1." AND ID IN (
                     Select DISTINCT w.ID From warranty_short AS w
                    where (UID = '{$login_id}' OR warranty_create_by = '{$login_id}')
                    )

                    ORDER BY ".$des." LIMIT 1";
                }

                break;
            case "orders":
                $id_select ='order_id';
                if($greater ==1){
                   $order_id= "order_id > '{$ID}'";
                    $des = " order_id ";
                }else{
                    $order_id= "order_id < '{$ID}'";
                    $des = " order_id DESC ";
                }

                $sqlText = "Select order_id From quote
                    where ".$order_id." and order_id IN(
                         Select DISTINCT o.order_id
                         From quote_short as o
                         Where (o.b_ID = '{$login_id}' OR o.order_create_by = '{$login_id}')
                    )
                    ORDER BY ".$des." LIMIT 1";

                if($v=="Employee" || $v=="SystemAdmin"){
                    $sqlText = "Select order_id From quote
                     where ".$order_id." ORDER BY ".$des." LIMIT 1";
                }elseif($v=="Sales"){
                    $sqlText = "Select order_id From quote
                    where ".$order_id." and order_id IN(
                         Select DISTINCT o.order_id
                         From quote_short as o
                         Where (o.s_ID = '{$login_id}' OR o.order_create_by = '{$login_id}')
                    )
                    ORDER BY ".$des." LIMIT 1";
                }
                break;

            case "products":
                if($greater ==1){
                    $sqlText = "Select ID From products
                    where ID > '{$ID}' ORDER BY ID LIMIT 1";
                }else{
                    $sqlText = "Select ID From products
                    where ID < '{$ID}' ORDER BY ID DESC LIMIT 1";
                }
                break;

            case "helpdesk":
                $id_select ='id';
                if($greater ==1){
                    $ID1= "id > '{$ID}'";
                    $des = " id ";
                }else{
                    $ID1= "id < '{$ID}'";
                    $des = " id DESC ";
                }

                $sqlText = "Select id From helpdesk
                    where ".$ID1." ORDER BY ".$des." LIMIT 1";
                break;

        }
        $result = mysqli_query($this->con,$sqlText);

        $rID = "";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $rID = $row[$id_select];
            }
        }
        return $rID;
    }

    //------------------------------------------------------------------
    public function getClaim_login($login_id)
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
    //------------------------------------------------------------------
    public function get_task_open_status($contactID){
        if(is_numeric($contactID) && !empty($contactID)){
            $query = "SELECT count(*) FROM `assign_task` WHERE customer_id ='{$contactID}' AND
             status ='NEEDS TO BE SCHEDULED'";
           return $this->totalRecords($query,0);
        }else{
            return 0;
        }
    }

    //------------------------------------------------------------
    public function depot_and_customer_address($order_id)
    {
        $query = "SELECT *
        FROM quote_short
        WHERE order_id ='{$order_id}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if($row["products_ordered"] !='' && $row["products_ordered"] !=null){
                    $row["products_ordered"] = json_decode($row["products_ordered"],true);
                }
                $list = $row;
            }
        }

        if(count($list) >0){
            $temp =$list["products_ordered"];
            if(count($temp) >0){
                $str ='';
                foreach($temp as $item){
                    $str = ($str =='')?$item["id"]: $str.",".$item["id"];
                }
                if($str !=''){
                    $list["depots"] =$this-> find_depot_address($str);
                }
            }

            unset($list["products_ordered"]);
        }
        return $list;
    }

    //------------------------------------------------------------
    public function find_depot_address($product_ids)
    {
        //$setmode ="SET @@sql_mode = SYS.LIST_DROP(@@sql_mode, 'ONLY_FULL_GROUP_BY')";
        //mysqli_query($this->con,$setmode);

        $list = array();
        if(empty($product_ids)) return $list;
        /*
        $query = "SELECT depot_name,depot_address,depot_phone
        FROM depot_product_short
        WHERE product_id IN ($product_ids) GROUP BY depot_name";*/
        $query = "SELECT DISTINCT depot_name,depot_address,depot_phone,depot_city,
              depot_state,depot_zip,depot_hour_operation
        FROM depot_product_short
        WHERE product_id IN ($product_ids)";

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function getContactEmail_ID($contactID)
    {
        $command = "Select primary_email, concat(first_name,' ',middle_name,' ',last_name) as driver_name from `contact`
        where ID='{$contactID}'";
        //print_r($insertCommand);

        $rsl = mysqli_query($this->con,$command);
        $list =array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list = $row;
            }
        }

        return $list;

    }

    //------------------------------------------------------------
    public function depot_customer_by_sku($sku)
    {
        $query = "SELECT * FROM quote_temp_short
        WHERE prod_SKU ='{$sku}' LIMIT 1";
        //die($query);
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function customer_info_order($order_id)
    {
        $query = "SELECT
         shipping_customer_name,shipping_address,shipping_phone,email_phone,
         shipping_city,shipping_zip,shipping_state
        FROM quote_short
        WHERE order_id ='{$order_id}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }
   //------------------------------------------------------------
    public function Order_update_one_field($order_id,$fieldname,$value){
        $query = "UPDATE `quote`
                SET $fieldname = '{$value}'
                WHERE order_id = '{$order_id}'";

        $update = mysqli_query($this->con,$query);
    }

    //------------------------------------------------------------
    public function searchTask($text_search,$level=null,$login_id=null){
        $list = array();
        if($level =='') return $list;

        $sql = "Select *
        From `assign_task_short`
        where status <> 'close' AND
        (taskName LIKE '%{$text_search}%' OR
        product_sku LIKE '%{$text_search}%' OR
        order_title LIKE '%{$text_search}%' OR
        assign_name LIKE '%{$text_search}%' OR
        cus_name LIKE '%{$text_search}%')";

        $continue = " AND ";
        if($level=='Admin'){
            $sql .=$continue."((assign_id IS NULL OR assign_id ='' OR assign_id =0) OR delivery_date IS NULL) ";
        }else{
           if($login_id !=''){
               $sql .=$continue."((assign_id ='{$login_id}') OR (assign_id IS NULL OR assign_id ='' OR assign_id =0))" ;
           }
        }
        //die($sql);
        $result = mysqli_query($this->con,$sql);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //------------------------------------------------------
    public function get_driver_to_payment($ID)
    {
        $sqlText = "Select * From driver_task_short
        WHERE ID='{$ID}' limit 1";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if($row['driver_total'] >0){
                    $row['total_payment'] =  $this->get_total_payment_for_driver($ID);
                }
                $list = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------
    public function get_total_payment_for_driver($task_id,$driver_id=null)
    {
        $sqlText = "Select SUM(pay_amount) as total_payment From pay_for_driver
        WHERE pay_task='{$task_id}'";

        $result = mysqli_query($this->con,$sqlText);

        $total_payment = 0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $total_payment = $row['total_payment'];
            }
        }

        return $total_payment;
    }

    /////////////////////////////////////////////////////////
}