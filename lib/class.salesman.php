<?php
require_once 'class.common.php';
class Salesman extends Common{

    //------------------------------------------------------------------
    public function addSalesman($UID,$active,$area=null)
    {
        if(empty($area)) $area='[]';
        $fields = "SID,UID,active,area";

        $values = "'{$UID}','{$UID}','{$active}','{$area}'";

        $insertCommand = "INSERT INTO salesman({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        return $idreturn;

    }
    //------------------------------------------------------
    public function getSalesmanList($state=null,$corporate=null)
    {
        $query = "SELECT DISTINCT sl.active, sl.SID, sl.UID,c.first_name,sl.area, c.last_name, c.middle_name FROM
        salesman as sl
        Inner Join contact as c ON c.ID = sl.UID
        where sl.active=1";

        if(!empty($state) && empty($corporate)){
            $query = "SELECT DISTINCT sl.active, sl.SID, sl.UID,sl.area,c.first_name, c.last_name, c.middle_name
            FROM
        salesman as sl Inner Join contact as c ON c.ID = sl.UID
        where sl.active=1 AND (sl.area =JSON_SEARCH(area, 'all', '{$state}') IS NOT NULL OR
        sl.area =JSON_SEARCH(area, 'all', 'ALL') IS NOT NULL)";

        }elseif(!empty($corporate)){
            $query = "SELECT DISTINCT sl.active, sl.SID, sl.UID,sl.area,c.first_name, c.last_name, c.middle_name
            FROM
        salesman as sl Inner Join contact as c ON c.ID = sl.UID
        where sl.active=1 AND  (sl.area =JSON_SEARCH(area, 'all', 'Corporate') IS NOT NULL )";

        }
        //die($query);
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['area']= json_decode($row['area'],true);
                if (in_array("Corporate", $row['area'])){
                    $row['Corporate']=1;
                }else{
                    $row['Corporate']=0;
                }
                unset($row['area']);
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------
    public function getSales_state($state=null)
    {
        $query = "SELECT sl.active, sl.SID, sl.UID,c.first_name, c.last_name, c.middle_name FROM
        salesman as sl Inner Join contact as c ON c.ID = sl.UID where sl.active=1";

        if(!empty($state)){
            $query = "SELECT sl.active, sl.SID, sl.UID,c.first_name, c.last_name, c.middle_name FROM
        salesman as sl Inner Join contact as c ON c.ID = sl.UID
        where sl.active=1
        AND c.primary_state = '{$state}'";
        }

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //------------------------------------------------------
    public function getDetailSalebyID($id)
    {
        $query = "SELECT * FROM  salesman
        where salesman.SID = '{$id}'";
        //Inner Join contact ON contact.ID = salesman.UID where salesman.SID = '{$id}'";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------
    public function getSalesmanAndEmployeeList()
    {
        $query = "SELECT DISTINCT * FROM contact
        where contact_type like '%Employee%' OR contact_type like '%Sales%'";

        //die($query);
        $result = mysqli_query($this->con,$query);

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