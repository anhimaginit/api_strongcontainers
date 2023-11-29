<?php

require_once 'class.common.php';
class Helpdesk extends Common{
//--------------------------------------------------------------
    public function validate_helpdesk_fields($subject,$problem)
    {
        $error = false;
        $errorMsg = "";
        //--- $token
        if(!$error && empty($subject)){
            $error = true;
            $errorMsg = "Subject is required.";
        }

        if(!$error && empty($problem)){
            $error = true;
            $errorMsg = "Problem is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //--------------------------------------------------------------
    public function addNewHelpDesk($photoPath,$subject,$problem,$form,$status,$assign_to=null,$created_by=null){
      if(empty($assign_to)) $assign_to=0;

        $dateTemp = date("Y-m-d");
        $fields = "screenshot,subject,problem,form,status,
                   assign_to,create_date,created_by";

        $values = "'{$photoPath}','{$subject}','{$problem}','{$form}','{$status}',
                '{$assign_to}','{$dateTemp}','$created_by'";

        $insertCommand = "INSERT INTO helpdesk({$fields}) VALUES({$values})";

        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);
        if($idreturn){
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }

    }
    //--------------------------------------------------------------
   public function getHelpDesk_ID($helpdeskID){
      $select = "SELECT * from  helpdesk_short
      WHERE id ='{$helpdeskID}'";

     $result = mysqli_query($this->con,$select);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['screenshot']= json_decode($row['screenshot'],true);
                $list=$row;
            }
        }

       return $list;
   }

    //------------------------------------------------------------
    public function searchHelpdeskList($columns=null,$search_all=null,$limit,$offset)
    {
        $criteria = "";

        if(!empty($search_all)){
            $temp = $this->columnsFilterOr($columns,$search_all);
            $criteria.= empty($criteria)?"":" AND ";
            $criteria .="(".$temp.")";
        }

        $sqlText = "Select * from helpdesk_short";
        if(!empty($criteria)) $sqlText.="where ".$criteria;
        $query_total = $sqlText;
        //
        $sqlText .= " ORDER BY id";

        if(!empty($limit)){
            $sqlText .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $sqlText .= " OFFSET {$offset} ";
        }
        //die($sqlText);
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        //totol row
        $rsl = mysqli_query($this->con,$query_total);
        $row = mysqli_fetch_row($rsl);
        if ($row[0] > 0)
            $value =$row[0];
        else $value=0;

        return array("totalRow"=>$value,'list'=>$list);

    }
    //------------------------------------------------------------
    public function updateHelpDesk($id,$photoPath,$subject,$problem,$form,$status,$assign_to=null,$last_update=null){
        if(empty($assign_to)) $assign_to=0;
        $dateTemp = date("Y-m-d");
        $query = "UPDATE `helpdesk`
                SET screenshot = '{$photoPath}',
                    subject = '{$subject}',
                    problem ='{$problem}',
                    form ='{$form}',
                    status = '{$status}',
                    assign_to ='{$assign_to}',
                    last_update ='{$last_update}',
                    last_update_time ='{$dateTemp}'
                WHERE id = '{$id}'";
        $update = mysqli_query($this->con,$query);
        if($update){
            return 1;
        }else{
            return mysqli_error($this->con);
        }
    }

    /////////////////////
}