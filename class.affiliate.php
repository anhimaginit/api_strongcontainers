<?php
require_once 'class.common.php';
class Affiliate extends Common{

    //------------------------------------------------------------------
    public function addAffliate($UID,$active,$aff_type)
    {

        $fields = "UID,active,aff_type";

        $values = "'{$UID}','{$active}','{$aff_type}'";

        $insertCommand = "INSERT INTO affiliate({$fields}) VALUES({$values})";
        //print_r($insertCommand);
        $insert = mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        return $idreturn;

    }

    //------------------------------------------------------------------
    public function updateAffliate($UID,$active,$aff_type)
    {
        $updateCommand = "UPDATE `affiliate`
                SET active = '{$active}',
                aff_type = '{$aff_type}'
                WHERE UID = '{$UID}'";

        $update = mysqli_query($this->con,$updateCommand);

        return $update;

    }

    //------------------------------------------------------
    public function getAffilitateList()
    {
        $sqlText = "Select * From affiliate";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------
    public function getAffil_mortgageList($contact_name=null)
    {
        if(!empty($contact_name)) $contact_name =trim($contact_name);

        $sqlText = "Select AID as id,
      (
        CASE
            WHEN (primary_state <>'' AND primary_state IS NOT NULL) THEN concat(IFNULL(first_name,''),' ',IFNULL(last_name,''),'-',primary_state)
            ELSE concat(IFNULL(first_name,''),' ',IFNULL(last_name,''))
        END)  as text
        From affil_mortgage
        WHERE contact_name LIKE '{$contact_name}%'";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------
    public function getAffil_agentList($contact_name=null)
    {
        if(!empty($contact_name)) $contact_name =trim($contact_name);

        $sqlText = "Select DISTINCT AID as id,
         (
        CASE
            WHEN (primary_state <>'' AND primary_state IS NOT NULL) THEN concat(IFNULL(first_name,''),' ',IFNULL(last_name,''),'-',primary_state)
            ELSE concat(IFNULL(first_name,''),' ',IFNULL(last_name,''))
        END)  as text
        From affil_short
        WHERE contact_name LIKE '{$contact_name}%'";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------
    public function getAffil_titleList($contact_name=null)
    {
        if(!empty($contact_name)) $contact_name =trim($contact_name);

        $sqlText = "Select AID as id,(
        CASE
            WHEN (primary_state <>'' AND primary_state IS NOT NULL) THEN concat(IFNULL(first_name,''),' ',IFNULL(last_name,''),'-',primary_state)
            ELSE concat(IFNULL(first_name,''),' ',IFNULL(last_name,''))
        END)  as text
        From affil_title
        WHERE contact_name LIKE '{$contact_name}%'";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    public function getContactID_AffiliateID($AID)
    {
        $select ="SELECT UID FROM affiliate
        WHERE AID='{$AID}'";
        $result = mysqli_query($this->con,$select);

        $UID = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $UID = $row['UID'];
            }
        }
        return $UID;

    }


    //------------------------------------------------------
    public function searchAffilitate($aff_name)
    {
        $sqlText = "Select * From affil_short
        where contact_name like '%{$aff_name}%' AND active='1'";
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