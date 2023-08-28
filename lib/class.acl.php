<?php
require_once 'class.common.php';
class ACL extends Common{


    //------------------------------------------------------
    public function get_ACL($unit,$level)
    {
        $query = "Select acl_rules from acl_rules
                where level='{$level}' AND unit='{$unit}' limit 1";
        //die($update);
        $rsl = mysqli_query($this->con,$query);

        $list_acl = "";
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list_acl = json_decode($row["acl_rules"],true);
            }

        }

        return $list_acl;
    }

    //------------------------------------------------------
    public function get_globalACL_ID($UID)
    {
        $query = "Select * from global_acl

                where g_UID='{$UID}' limit 1";
        //die($update);
        $rsl = mysqli_query($this->con,$query);

        $list_acl = "";
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list_acl[] = $row;
            }

            if(count($list_acl)>0){
                $list_acl[0]["g_right"] =  json_decode($list_acl[0]["g_right"],true);
            }

        }

        return $list_acl;
    }

    //------------------------------------------------------
    public function get_globalACLs($limit,$offset)
    {
        $query = "Select g.g_id,g.g_UID,
        concat(c.first_name,' ',c.last_name) as contact_name
         from global_acl as g
         Left Join contact as c ON c.ID = g.g_UID";

        $query .= " ORDER BY g_id ASC";

        if(!empty($limit)){
            $query .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $query .= " OFFSET {$offset} ";
        }
        //die($update);
        $rsl = mysqli_query($this->con,$query);

        $list_acl = "";
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list_acl[] = $row;
            }

        }

        return $list_acl;
    }

    //------------------------------------------------------------
    public function globalACL_Records()
    {
        $sqlText = "Select Count(*) From global_acl";
        //die($sqlText);
        $num = $this->totalRecords($sqlText,0);

        return $num;
    }

    /////////////////////////////////////////////////////////
}