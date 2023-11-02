<?php

require_once 'class.common.php';
class Calendar extends Common{
    public function  check_driver_in_calendar($assign_id,$delivery_date,$task_id=null){
        if($delivery_date ==''){
            return array("driver"=>"","calendar_id" =>"","ignore"=>1);
        }
        //check task is exiting in calendar
        if($delivery_date !='' && $task_id !=''){
            $temp = $this->date_hms($delivery_date);
            $query ="SELECT calendar_id FROM `calendar` WHERE task_id = '{$task_id}' AND
            driver_id = '{$task_id}' AND
             task_in_day ='{$temp}' limit 1";
            //die($query);
            $calendar_id  = $this->return_id($query,"calendar_id");
            if($calendar_id !=''){
                return array("driver"=>"","calendar_id" =>$calendar_id,"ignore"=>1);
            }
        }

        $next_date = date('Y-m-d', strtotime($delivery_date .' +1 day'));
        $prev_date = date('Y-m-d', strtotime($delivery_date .' -1 day'));
        $prev_date = $prev_date." "."23:59";

        $query ="SELECT delivery_date,calendar_id,task_id,task_in_day FROM calendar_short
        WHERE driver_id ='{$assign_id}' AND
        task_in_day > '{$prev_date}' AND task_in_day < '{$next_date}'";
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list =array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }
       // echo "<pre>";print_r($list);echo "</pre>"; die();
        if(count($list) > 1){
            if(count($list) ==2 && $task_id !=''){
                $date = strtotime($delivery_date);
                $hours = date('H', $date);
                $hours_to_set = intval($hours);
                //check task is exiting in calendar
                if($task_id !=''){
                    $calendar_row =  $this->find_task_in_list($task_id,$list);
                    if(count($calendar_row) >0){
                        $date = strtotime($calendar_row["task_in_day"]);
                        $hours = date('H', $date);
                        $hours_existing = intval($hours);
                        if(($hours_to_set <13 && $hours_existing <13) || ($hours_to_set >12 && $hours_existing >12)){
                            return array("driver"=>"","calendar_id" =>$calendar_row["calendar_id"],"ignore"=>'');
                        }else{
                            return array("driver"=>"Driver is not available","calendar_id" =>$calendar_row["calendar_id"],"ignore"=>1);
                        }
                    }else{
                        return array("driver"=>"Driver is not available","calendar_id" =>"","ignore"=>1);
                    }

                }
            }else{
                if($task_id !=''){
                    $query = "SELECT calendar_id FROM `calendar_short`
                WHERE task_id = '{$task_id}' AND
                assign_id ='{$assign_id}' AND
                task_in_day = '{$delivery_date}'
                limit 1";
                    //die($query);
                    $calendar_id  = $this->return_id($query,"calendar_id");
                    if($calendar_id !=''){
                        return array("driver"=>"","calendar_id" =>$calendar_id,"ignore"=>1);
                    }else{
                        return array("driver"=>"Driver is not available","calendar_id" =>"","ignore"=>1);
                    }
                }else{
                    return array("driver"=>"Driver is not available","calendar_id" =>"","ignore"=>1);
                }
            }

        }else if( count($list) ==1){
            $date = strtotime($delivery_date);
            $hours = date('H', $date);
            $hours_to_set = intval($hours);
            //check task is exiting in calendar
            if($task_id !=''){
              $calendar_row =  $this->find_task_in_list($task_id,$list);
                if(count($calendar_row) >0){
                    return array("driver"=>"","calendar_id" =>$calendar_row["calendar_id"],"ignore"=>'');
                }
               // echo "<pre>";print_r($calendar_row);echo "</pre>"; die();
            }
            //no existing
            $date = strtotime($list[0]["task_in_day"]);
            $hours = date('H', $date);
            $hours_existing = intval($hours);
            if($hours_existing <13 && $hours_to_set >12){
                $calendar_id  = $this->return_id("SELECT calendar_id FROM `calendar` WHERE task_id = '{$task_id}' limit 1","calendar_id");
                if($calendar_id !=""){
                    return array("driver"=>"","calendar_id" =>$calendar_id,"ignore"=>'');
                }else{
                    return array("driver"=>"","calendar_id" =>"","ignore"=>'');
                }
            }elseif($hours_existing >12 && $hours_to_set < 13){
                $calendar_id = $this->return_id("SELECT calendar_id FROM `calendar` WHERE calendar_id = '{$task_id}'","calendar_id");
                if($calendar_id !=""){
                    return array("driver"=>"","calendar_id" =>$calendar_id,"ignore"=>'');
                }else{
                    return array("driver"=>"","calendar_id" =>"","ignore"=>'');
                }
            }else{
                if($task_id !=""){
                    return array("driver"=>"Driver is not available","calendar_id" =>"","ignore"=>1);
                }else{
                    return array("driver"=>"Driver is not available","calendar_id" =>"","ignore"=>1);
                }
            }
        }

        $calendar_id  = $this->return_id("SELECT calendar_id FROM `calendar` WHERE task_id = '{$task_id}' limit 1","calendar_id");
        if($calendar_id !=""){
            return array("driver"=>"","calendar_id" =>$calendar_id,"ignore"=>'');
        }else{
            return array("driver"=>"","calendar_id" =>"","ignore"=>'');
        }
    }

    //--------------------------------------------
    public function  find_task_id($task_id){
        $query ="SELECT * FROM calendar_short
        WHERE task_id ='{$task_id}'";

        $rsl = mysqli_query($this->con,$query);
        $list =array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //--------------------------------------------
    public function update_calendar($calendar_id,$task_in_day,$task_id,$driver_id){
        $updateComm = "UPDATE `calendar`
                SET task_in_day = '{$task_in_day}',
                task_id = '{$task_id}',
                driver_id ='{$driver_id}'
                WHERE calendar_id = '{$calendar_id}'";
        $update = mysqli_query($this->con,$updateComm);
        if($update){
            return 1;
        }else{
            return mysqli_error($this->con);
        }
    }
    //--------------------------------------------
    public function new_calendar($task_in_day,$task_id,$driver_id){
        $fields = "task_in_day,task_id,driver_id";
        $values ="'{$task_in_day}','{$task_id}','{$driver_id}'";
        $query = "INSERT INTO calendar({$fields}) VALUES({$values})";
        //die($query);
        mysqli_query($this->con,$query);
        $idreturn = mysqli_insert_id($this->con);
        if($idreturn){
            return $idreturn;
        }else{
            $err =mysqli_error($this->con);
            return $err;
        }
    }
    //--------------------------------------------
    public function create_calendar($task_id,$driver_id,$delivery_date){
        $calendar_id ='';
        $is_driver = "Driver is not available";
        $available =array();
        if($delivery_date !='' && $driver_id !=''){
            $available = $this->check_driver_in_calendar($driver_id,$delivery_date,$task_id);
            $is_driver =$available["driver"];
            //echo "<pre>";print_r($available);echo "</pre>"; die();
            if($available["driver"] !=''){
                return array('driver'=>$available["driver"],'calendar_id'=>'');
            }
        }

        if(count($available) >0){
            if($available["driver"] =='' && $available["ignore"] ==''){
                if($available["calendar_id"] != ""){
                   $is_update = $this->update_calendar($available["calendar_id"],$delivery_date,$task_id,$driver_id);
                    if($is_update == 1) {
                        $array_key_value = array("delivery_date"=>$delivery_date,"assign_id"=>$driver_id);
                        $array_primary =array("id"=>$task_id);

                        $this->update_table("assign_task",$array_primary,$array_key_value);
                        $calendar_id = $available["calendar_id"];
                    }else{
                        $calendar_id = $is_update;
                    }
                }else{
                    $calendar_id = $this->new_calendar($delivery_date,$task_id,$driver_id);
                    $array_key_value = array("delivery_date"=>$delivery_date,"assign_id"=>$driver_id);
                    $array_primary =array("id"=>$task_id);

                    $this->update_table("assign_task",$array_primary,$array_key_value);
                }
            }
        }

        return array('driver'=>$is_driver,'calendar_id'=>$calendar_id);
    }
    //--------------------------------------------
    public function  calendars_from_to_date($from_date,$to_date,$contact_id,$role){
        $query ="SELECT * FROM calendar_short";
        $where =" WHERE ";
        if($from_date !=''){
            $from_date = $this->is_Date($from_date);
        }

        if($to_date !=''){
            $to_date = $this->is_Date($to_date);
        }

        if($from_date !=''){
            $from_date = date('Y-m-d', strtotime($from_date .' -1 day'));
            $query = $query.$where."task_in_day > '{$from_date}'";
            $where =" AND ";
        }

        if($to_date !=''){
            $to_date = date('Y-m-d', strtotime($to_date .' +1 day'));
            $query = $query.$where."task_in_day < '{$to_date}'";
            $where =" AND ";
        }

        if($role !='Admin' && $contact_id !=''){
            $query = $query.$where."assign_id = '{$contact_id}'";
            $where =" AND ";
        }
        //die($query);

        $rsl = mysqli_query($this->con,$query);
        $list =array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                if($row["task_in_day"] !=''){
                    $temp = explode(" ",$row["task_in_day"]);
                    $row["date"] =explode("-",$temp[0])[2];
                }
                /*if($row["product_sku"] !=''){
                    $row["product_sku"] = $this->depot_info_by_sku($row["product_sku"]);
                }*/
                $list[] = $row;
            }
        }
        return $list;
    }

    //--------------------------------------------
    public function  calendars_date($date,$contact_id,$role,$text_search,$limit,$cursor){
        $query ="SELECT * FROM calendar_short";
        $where =" WHERE ";

        if($date !=''){
            $date = $this->is_Date($date);
        }
        if($date !=''){
            $from_date = date('Y-m-d', strtotime($date .' -1 day'));
            $from_date =$from_date." "."23:59:00";
            $to_date = date('Y-m-d', strtotime($date .' +1 day'));
            $query = $query.$where."task_in_day > '{$from_date}'";
            $where =" AND ";
            $query = $query.$where."task_in_day < '{$to_date}'";
            $where =" AND ";
        }
        if($role !='Admin'){
            if($contact_id !=''){
                $query = $query.$where."assign_id = '{$contact_id}'";
                $where =" AND ";
            }
        }
        if($text_search !=''){
            $query = $query.$where."(taskName LIKE '%{$text_search}%' OR
        product_sku LIKE '%{$text_search}%' OR
        order_title LIKE '%{$text_search}%' OR
        driver_name LIKE '%{$text_search}%' OR
        customer_name LIKE '%{$text_search}%')";
            $where =" AND ";
        }
        //die($query);
        $query_count = $query;
        $query .= " order by calendar_id DESC";
        if($limit !=''){
            $query.= " LIMIT {$limit} ";
        }
        if($cursor !=''){
            $query.= " OFFSET {$cursor} ";
        }
        //die($query);
        $api_domain = $this->api_domain;
        $pickup_path = $this->pickup_path;
        $delivery_path = $this->delivery_path;
        //pickup image
        $pickup_target_path = $api_domain.$pickup_path;
        $delivery_target_path = $api_domain.$delivery_path;
        //$pickup_target_path ='http://localhost/CRMAPI/'.$upload_order_file_path;
        //$delivery_target_path ='http://localhost/CRMAPI/'.$unload_order_file_path;

        $rsl = mysqli_query($this->con,$query);
        $list =array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                if($row["task_in_day"] !=''){
                    $temp = explode(" ",$row["task_in_day"]);
                    $row["date"] =explode("-",$temp[0])[2];
                }
                if($row["product_sku"] !=''){
                    $row["product_sku"] = $this->depot_info_by_sku($row["product_sku"],$row["quote_temp_id"]);
                }
                if($row["file_pickup_name"] !=''){
                    $file_temp = explode(',',$row["file_pickup_name"]);
                    $file_name_array = array();
                    if(is_array($file_temp)){
                        foreach($file_temp as $file_name){
                            $file_name_array[] = $pickup_target_path.$file_name;
                        }
                    }
                    $row["file_pickup_name"] = $file_name_array;
                }
                if($row["file_delivery_name"] !=''){
                    $file_temp = explode(',',$row["file_delivery_name"]);
                    $file_name_array = array();
                    if(is_array($file_temp)){
                        foreach($file_temp as $file_name){
                            $file_name_array[] = $delivery_target_path.$file_name;
                        }
                    }
                    $row["file_delivery_name"] = $file_name_array;
                }
                $list[] = $row;
            }
        }

        if($cursor ==0){
            $result = mysqli_query($this->con,$query_count);
            //die($query);
            $row_cnt = mysqli_num_rows($result);
        }else{
            $row_cnt =0;
        }

        return array("list"=>$list,"row_cnt"=>$row_cnt);
    }

    //--------------------------------------------
    public function find_task_in_list($task,$list){
        $existing_row =array();
        foreach($list as $item){
            if($task == $item["task_id"]){
                $existing_row = $item;
                break;
            }
        }

        return $existing_row;
    }
}
?>