<?php
require_once 'class.common.php';
class Employee extends Common{

    //------------------------------------------------------------------
   /* public function  check_exist_employee_type($id) {
        $total_query = "SELECT count(*) from employee_type
          WHERE UID ='{$id}'";
        $total = $this->totalRecords($total_query ,0);
        return $total;
    }*/

    public function updateEmployee()
    {
        /*
        if($this->check_exist_employee_type($id) >=1) {

            $updateCommand = "UPDATE `employee_type`
                SET active = '{$active_employee}',
                e_type = '{$E_type}'
                WHERE UID = '{$id}'";

            $update = mysqli_query($this->con,$updateCommand);
            $update = 5;
        } else {
            $query = "insert into employee_type(UID,e_type,active) values('{$id}','{$E_type}','{$active_employee}')";
            $update = mysqli_query($this->con,$query);
            $update = 6;

        }*/

        return 5;

    }

    /////////////////////////////////////////////////////////
}