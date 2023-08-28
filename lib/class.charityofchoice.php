<?php
require_once 'class.common.php';
class CharityofChoice extends Common{
    //------------------------------------------------------
    public function charityofChoiceList()
    {
        $sqlText = "Select * From charity_of_choice";
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
}