<?php
require_once 'class.common.php';

class ReportOrder extends Common{
    public function order_report($from_date,$to_date,$status,$text_search,$cursor,$limit){
        $query ="SELECT * FROM quote_short_report";
        $where =" WHERE ";

        if($from_date !=''){
            $from_date = $this->is_Date($from_date);
            if($from_date !=''){
                $from_date = date('Y-m-d', strtotime($from_date .' -1 day'));
                $from_date =$from_date." "."23:59:00";
                $query = $query.$where."createTime > '{$from_date}'";
                $where =" AND ";
            }
        }

        if($text_search !=''){
                $query = $query.$where."(order_title LIKE '{$text_search}%' OR
                order_sku LIKE '{$text_search}%' OR
                b_name LIKE '{$text_search}%' OR
                quote_temp_depot_name LIKE '{$text_search}%' OR
                quote_temp_container_type_name LIKE '{$text_search}%' OR
                quote_temp_prod_name LIKE '{$text_search}%' )";
                $where =" AND ";

        }
        if($to_date !=''){
            $to_date = $this->is_Date($to_date);
            if($to_date !=''){
                $to_date = date('Y-m-d', strtotime($to_date .' +1 day'));
                $query = $query.$where."createTime < '{$to_date}'";
                $where =" AND ";
            }
        }
        //die($query);
        if($status !=''){
            $query = $query.$where."assign_task_status = '{$status}'";
        }else{
            $query = $query.$where."assign_task_status IS NULL";
        }

        //die($query);
        $query_count = $query;
        $query .= " order by order_id DESC";
        if($limit !=''){
            $query.= " LIMIT {$limit} ";
        }
        if($cursor !=''){
            $query.= " OFFSET {$cursor} ";
        }
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list =array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                if($row['assign_task_id'] !=null && $row['assign_task_id'] !=''){
                    $row['driver_total_payment'] =  $this->get_total_payment_task($row['assign_task_id']);
                    $row['pay_acct'] = $this->get_pay_acct($row['order_id']);
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

    //------------------------------------------------------
    public function get_pay_acct($order_id)
    {
        $sqlText = "Select pay_date,pay_type,pay_amount,pay_note From pay_acct
        WHERE order_id='{$order_id}'";

        $result = mysqli_query($this->con,$sqlText);

        $list =array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }
   /////////////
}