<?php
require_once 'class.common.php';
class Tasktemplate extends Common{
    //------------------------------------------------------------------
    public function actionsetList(){
        $query = "SELECT actionset from task_template";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['actionset'];
            }
        }
        return $list;
    }
    /////////////////////////////////////////////////////////
}
