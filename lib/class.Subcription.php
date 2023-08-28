<?php
require_once 'class.common.php';


class Subcription extends Common{
    //------------------------------------------------------------
    //------------------------------------------------
    public function validate_sub_fields($name,$json)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($name)){
            $error = true;
            $errorMsg = "Name is required.";
        }

        if(!$error && empty($json)){
            $error = true;
            $errorMsg = "Template is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }


    public function getSubTempl_id($id){
        $sqlText = "Select * From subscription_template
        where id='{$id}'";
        $result = mysqli_query($this->con,$sqlText);

        $list = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['json'] = json_decode($row['json'],true);
                $list = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function subName(){
        $sqlText = "Select `id`, `name` From subscription_template
        Where status =1";
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
    public function historicalPayment($orderID,$invoiceID=null){
        $sqlText = "Select ps.amount,ps.billToID,ps.fee,
            ps.id,ps.inactive,ps.invoiceDate,ps.invoiceID,
            ps.orderID,ps.renews,ps.renewsDate,
            i.invoiceid as invoiceNum
          From payment_schedule as ps
          Left join invoice as i on i.ID = ps.invoiceID
        where ps.orderID='{$orderID}' and (ps.inactive =0 || ps.inactive IS NULL) and ps.invoiceID IS NOT NULL";

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
    public function schedulePayment($orderID,$invoiceID=null){
        $sqlText = "Select * From payment_schedule
        where orderID='{$orderID}' and (inactive =0 || inactive IS NULL) and invoiceID IS NULL";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function addSubmit($name,$json,$status)
    {
        $dateTemp = date("Y-m-d");
        $fields = "name,json,status";
        $values = "'{$name}','{$json}','{$status}'";

        $insert = "INSERT INTO subscription_template ({$fields}) VALUES({$values})";

        mysqli_query($this->con,$insert);
        $idRet = mysqli_insert_id($this->con);

        if(is_numeric($idRet) && !empty($idRet)){
            return $idRet;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------
    public function updateSubmit($id,$name,$json,$status)
    {
        $update ="UPDATE `subscription_template`
                SET name = '{$name}',
                status = '{$status}',
                json = '{$json}'
                where id = '{$id}'";

        $isSucc = mysqli_query($this->con,$update);

        if($isSucc){
            return 1;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------------------
    public function subList(){
        $sqlText = "Select * From subscription_template";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['json'] = json_decode($row['json'],true);
                $list[] = $row;
            }
        }
        return $list;
    }
    /////////////////////////////////////////////////////////
}