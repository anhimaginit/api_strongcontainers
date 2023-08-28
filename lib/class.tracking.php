<?php


require_once 'class.common.php';
class Tracking extends Common{
    //------------------------------------------------------
    public function update_trackEmail($id)
    {
        $updateCommand = "UPDATE `track_email`
                SET status = 'opened',
                title='test'
                where id ='{$id}' And status <> 'opened'";

        $update = mysqli_query($this->con,$updateCommand);

        return $update;
    }

    /////////////////////////////////////////////////////////
}