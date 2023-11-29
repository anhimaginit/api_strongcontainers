<?php

require_once 'class.common.php';
class Discount extends Common{
    //----------------------------------------------------------
    public function discountList(){
        //DATEDIFF(cd.exp_date,NOW()) =29
        $query ="SELECT * FROM discount ";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['apply_to']= json_decode($row['apply_to'],true);
                $list[] = $row;
            }

        }
        return $list;
    }
    //
}
