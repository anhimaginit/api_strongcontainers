<?php
require_once 'class.common.php';
class ClaimTask extends Common{
    //------------------------------------------------------
    public function AddClaimTask($tasks)
    {
        $taskIDs = array();
        $err = array();
        $fields = "actionset,assign_id,createDate,customer_id,doneDate,dueDate,status,taskName,time";

        if(is_array($tasks) && count($tasks)>0){
            foreach($tasks as $item){
                $actionset="";
                if(isset($item["actionset"])) $actionset = $this->protect($item["actionset"]) ;

                $assign_id="";
                if(isset($item["assign_id"])) $assign_id = $this->protect($item["assign_id"]);

                $createDate="";
                if(isset($item["createDate"])) $createDate =$this->protect($item["createDate"]);

                $customer_id="";
                if(isset($item["customer_id"])) $customer_id =$this->protect($item["customer_id"]);

                $doneDate="";
                if(isset($item["doneDate"])) $doneDate =$this->protect($item["doneDate"]);

                $dueDate="";
                if(isset($item["dueDate"])) $dueDate =$this->protect($item["dueDate"]);

                $status="";
                if(isset($item["status"])) $status =$this->protect($item["status"]);

                $taskName="";
                if(isset($item["taskName"])) $taskName =$this->protect($item["taskName"]);
                $time="";
                if(isset($item["time"])) $time =$this->protect($item["time"]);

                $values = "'{$actionset}','{$assign_id}','{$createDate}',
                    '{$customer_id}','{$doneDate}','{$dueDate}',
                    '{$status}','{$taskName}','{$time}'";

                $insertCommand = "INSERT INTO `assign_task` ({$fields}) VALUES({$values})";
                //die($insertCommand);
                mysqli_query($this->con,$insertCommand);
                $err_temp =mysqli_error($this->con);

                if($err_temp){
                    $err[] = array("taskName"=>$taskName,"assign_id"=>$assign_id);
                }else{
                    $taskIDs[]= mysqli_insert_id($this->con);
                }
            }
        }

        return array("taskIDs"=>$taskIDs,"err"=>$err);
    }

    //------------------------------------------------------
    public function UpdateClaimTask($id,$actionset,$assign_id,$createDate,
                                    $customer_id,$doneDate,$dueDate,$status,$taskName)
    {
        $updateComd ="UPDATE `assign_task`
                SET actionset = '{$actionset}',
                assign_id = '{$assign_id}',
                createDate = '{$createDate}',
                customer_id = '{$customer_id}',
                doneDate = '{$doneDate}',
                dueDate = '{$dueDate}',
                status = '{$status}',
                taskName = '{$taskName}'
                Where id ='{$id}'";

        //print_r($updateComd); die();
        $issucc = mysqli_query($this->con,$updateComd);
        if($issucc) return 1;
        else return "";
    }

    //------------------------------------------------------------------
    public function updateClaimAssignTask($assign_task,$ID)
    {
        $updateComd ="UPDATE `claims`
                SET assign_task = '{$assign_task}'
                Where ID ='{$ID}'";


        $issucc = mysqli_query($this->con,$updateComd);
        if($issucc) return 1;
        else return "";
    }

    //------------------------------------------------------------------
    public function taskByCus_id($customer_id)
    {
        $tempDate = date("Y-m-d H:i:s");
        $command ="select actionset,assign_id, TIMESTAMPDIFF(MINUTE,'{$tempDate}',dueDate) as duration,
createDate,dueDate,customer_id,doneDate,id,status,taskName,time,assign_name,cus_name,alert,urgent
from assign_task_short
Where customer_id in ({$customer_id}) ORDER BY dueDate DESC";

        $result = mysqli_query($this->con,$command);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //------------------------------------------------------------------
    public function taskByAssign_id($assign_id)
    {
        $tempDate = date("Y-m-d H:i:s");
        $command ="select actionset,assign_id, TIMESTAMPDIFF(MINUTE,'{$tempDate}',dueDate) as duration,
createDate,dueDate,customer_id,doneDate,id,status,taskName,time,assign_name,cus_name,alert,urgent
from assign_task_short
                Where assign_id IN ({$assign_id}) ORDER BY dueDate DESC";
        //die($command);
        $result = mysqli_query($this->con,$command);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    /*
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
    */
    /////////////////////////////////////////////////////////
}