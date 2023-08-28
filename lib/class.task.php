<?php


require_once 'class.common.php';
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
                               $doneDate,$dueDate,$status,$taskName,$time,$alert=null,$urgent=null)
    {
        $doneDate = $this->is_Date($doneDate);
        $dueDate = $this->is_Date($dueDate);
        $createDate =date("Y-m-d");

        if(empty($assign_id)) $assign_id=0;
        if(empty($customer_id)) $customer_id=0;
        $fields = "createDate,actionset,assign_id,content,customer_id,
        status,taskName,time,alert,urgent";

        $values = "'{$createDate}','{$actionset}','{$assign_id}',
        '{$content}','{$customer_id}','{$status}','{$taskName}','{$time}',
        '{$alert}','{$urgent}'";

        if(!empty($doneDate)){
            $fields .= ",doneDate";
            $values .=",'{$doneDate}'";
        }
        if(!empty($dueDate)){
            $fields .= ",dueDate";
            $values .=",'{$dueDate}'";
        }

        $insertComm = "INSERT INTO assign_task({$fields}) VALUES({$values})";
        //print_r($insertComm); die();
        mysqli_query($this->con,$insertComm);
        $idreturn = mysqli_insert_id($this->con);

        if($idreturn){
            return $idreturn;
        }else{
            $err =mysqli_error($this->con);
            return $err;
        }

    }

    //------------------------------------------------------
    public function updateTask($id,$actionset,$assign_id,$content,$customer_id,
                               $doneDate,$dueDate,$status,$taskName,$time,$alert=null,$urgent=null)
    {
        $doneDate = $this->is_Date($doneDate);
        $dueDate = $this->is_Date($dueDate);

        if(empty($assign_id)) $assign_id=0;
        if(empty($customer_id)) $customer_id=0;

        $updateComm = "UPDATE `assign_task`
                SET actionset = '{$actionset}',
                assign_id = '{$assign_id}',
                content = '{$content}',
                customer_id = '{$customer_id}',
                status = '{$status}',
                alert = '{$alert}',
                urgent = '{$urgent}',
                taskName = '{$taskName}',
                time = '{$time}'";

        if(!empty($doneDate)){
            $updateComm .=",doneDate = '{$doneDate}'";
        }

        if(!empty($dueDate)){
            $updateComm .=",dueDate = '{$dueDate}'";
        }

        $updateComm .="WHERE id='{$id}'";
        //die($updateComm);
        $update = mysqli_query($this->con,$updateComm);
        if($update){
            return 1;
        }else{
            $err = mysqli_error($this->con);
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
                        From orders_short as o
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

                $sqlText = "Select order_id From orders
                    where ".$order_id." and order_id IN(
                         Select DISTINCT o.order_id
                         From orders_short as o
                         Where (o.b_ID = '{$login_id}' OR o.order_create_by = '{$login_id}')
                    )
                    ORDER BY ".$des." LIMIT 1";

                if($v=="Employee" || $v=="SystemAdmin"){
                    $sqlText = "Select order_id From orders
                     where ".$order_id." ORDER BY ".$des." LIMIT 1";
                }elseif($v=="Sales"){
                    $sqlText = "Select order_id From orders
                    where ".$order_id." and order_id IN(
                         Select DISTINCT o.order_id
                         From orders_short as o
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
    /////////////////////////////////////////////////////////
}