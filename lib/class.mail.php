<?php

require_once 'class.common.php';
class LocalEmail extends Common{
    //------------------------------------------------------------
    public function validate_mail_fields($senderID,$reciverID)
    {
        $error = false;
        $errorMsg = "";

         if(!$error && empty($senderID)){
             $error = true;
             $errorMsg = "Sender is required.";
         }

        if(!$error && empty($reciverID)){
            $error = true;
            $errorMsg = "Receiver is required.";
        }


        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }


    public function updateMailDepartment($e_department,$email_id,$email)
    {
        $total_query = "SELECT count(*) from email_represent
          WHERE e_department ='{$e_department}'";
        $total = $this->totalRecords($total_query ,0);

        if($total >=1) {

            $updateCommand = "UPDATE `email_represent`
                SET email_id = '{$email_id}',
                	email = '{$email}'
                WHERE e_department = '{$e_department}'";

            $update = mysqli_query($this->con,$updateCommand);
            //    $update = 5;
        } else {
            $query = "insert into email_represent(e_department,email_id,email) values('{$e_department}','{$email_id}','{$email}')";
            $update = mysqli_query($this->con,$query);
            //    $update = 6;

        }

        return $update;

    }

    //----------------------------------------------------------
    public function getEmailResList(){
        /* $phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$phone_number);
         $phone11 =0;

         if(strlen($phone)==10){
             $phone11 ="1".$phone;
         }*/

        $query = "SELECT * from email_represent";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function ComposerMail($description,$draft,$receiverID,$senderID,$subject,$id,$checked){

        $star=0;

        if(empty($id)){
            $idreturn = $this->ComposerNewMail($description,$draft,$receiverID,$senderID,$subject,$checked);
        }else{
            $idreturn = $this->sentDraftMail($description,$draft,$receiverID,$subject,$id,$checked);
        }

       return $idreturn;
    }

    //------------------------------------------------------------
    public function ComposerNewMail($description,$draft,$receiverID,$senderID,$subject,$checked){

        $star=0;
        if(empty($draft)) $draft =0;
        $fields = "checked,description,draft,receiverID,senderID,subject";

        $values = "'{$checked}','{$description}','{$draft}',
        '{$receiverID}','{$senderID}','{$subject}'";

        $insertComm = "INSERT INTO mail({$fields}) VALUES({$values})";
        //print_r($insertComm); die();
        mysqli_query($this->con,$insertComm);
        $idreturn = mysqli_insert_id($this->con);

        if($idreturn){
            return $idreturn;

        }else{
            $err =mysqli_error($this->con);
            return $err;
        }
    }

    //------------------------------------------------------------
    public function sentDraftMail($description,$draft,$receiverID,$subject,$id,$checked){
        $draft=1;
        $update ="UPDATE `mail`
                SET description = '{$description}',
                draft = '{$draft}',
                receiverID = '{$receiverID}',
                subject = '{$subject}',
                checked ='{$checked}'
                Where id ='{$id}'";

        $issucc = mysqli_query($this->con,$update);
        if($issucc){
            return 1;
        } else{
            $err =mysqli_error($this->con);
            return $err;
        }
    }

    //------------------------------------------------------------
    public function mail_inbox($contactID,$limit=null,$offset=null)
    {
        $query ="Select * from mail_short
        where JSON_CONTAINS(receiverID->'$[*]', JSON_ARRAY('{$contactID}')) AND
        draft=1
        ORDER BY id ASC";

        if(!empty($limit)){
            $query .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $query .= " OFFSET {$offset} ";
        }

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['checked'] = $this->mail_opened($row['checked'],$contactID);
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function mail_inbox_total($contactID)
    {
        $query ="Select Count(*) from mail_short
        where JSON_CONTAINS(receiverID->'$[*]', JSON_ARRAY('{$contactID}')) AND
        draft=1";
        $num = $this->totalRecords($query,0);

        return $num;
    }

    //------------------------------------------------------------
    public function mail_inbox_total_new($contactID)
    {
        $query ="SELECT Count(*) FROM mail_short WHERE json_contains(checked->'$[*].receiverID', json_array('{$contactID}'))
                 AND json_contains(checked->'$[*].checked', json_array(0))
                 AND JSON_CONTAINS(receiverID->'$[*]', JSON_ARRAY('{$contactID}'))
                 AND draft=1";
        $num = $this->totalRecords($query,0);

        return $num;
    }

    //------------------------------------------------------------
    public function mail_sent($contactID,$limit=null,$offset=null)
    {
        $query ="Select * from mail_short
        where senderID='{$contactID}' AND
        draft=1
        ORDER BY id ASC";

        if(!empty($limit)){
            $query .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $query .= " OFFSET {$offset} ";
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
    //------------------------------------------------------------
    public function mail_sent_total($contactID)
    {
        $query ="Select Count(*) from mail_short
        where senderID='{$contactID}' AND
        draft=1";
        $num = $this->totalRecords($query,0);

        return $num;
    }
    //------------------------------------------------------------
    public function mail_draft($contactID,$limit=null,$offset=null)
    {
        $query ="Select * from mail_short
        where senderID='{$contactID}' AND
        (draft=0 || draft=null)
        ORDER BY id ASC";

        if(!empty($limit)){
            $query .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $query .= " OFFSET {$offset} ";
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

    //------------------------------------------------------------
    public function mail_draft_total($contactID)
    {
        $query ="Select Count(*) from mail_short
        where senderID='{$contactID}' AND
        (draft=0 || draft=null)";
        $num = $this->totalRecords($query,0);

        return $num;
    }


    //------------------------------------------------------------
    public function mail_open($mailID,$receiverID=null,$inbox=null)
    {
        $query ="Select * from mail_short
        where id = '{$mailID}' limit 1";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['receiver_detail'] = $this->get_receiver($row['receiverID']);
                $row['checked'] =$this->update_mail_checked($mailID,$row['checked'],$receiverID,$inbox);
                $list = $row;
            }
        }

        return $list;
    }
    //------------------------------------------------------------
    public function get_receiver($receiverIDs_json)
    {
        $receiverIDs_arr = json_decode($receiverIDs_json,true);

        $receiverIDs="";
        foreach($receiverIDs_arr as $item){
            $receiverIDs .=(empty($receiverIDs))? '':',';
            $receiverIDs .=$item;
        }

        $list = array();
        if(empty($receiverIDs)) return $list;

        $query ="Select ID,primary_email,  concat(first_name,' ',last_name) as receiver_name
        from contact
        where id IN ($receiverIDs)";

        $result = mysqli_query($this->con,$query);

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function update_mail_checked($mailID,$checked_json,$receiverID=null,$inbox=null)
    {
        $isupdate =0;
        $checked_arr = json_decode($checked_json,true);
        for($i=0;$i<count($checked_arr);$i++){
            $item= $checked_arr[$i];
            if($item['receiverID']==$receiverID){
                if($checked_arr[$i]['checked']!=1){
                    $checked_arr[$i]['checked']=1;
                    $isupdate=1;
                }

                break;
            }
        }

        if($isupdate==1 && $inbox==1){
            $checked_json = json_encode($checked_arr);
            $update ="UPDATE `mail`
                SET checked ='{$checked_json}'
                Where id ='{$mailID}'";

            $issucc = mysqli_query($this->con,$update);
            if($issucc){
                return 1;
            } else{
                $err =mysqli_error($this->con);
                return $err;
            }

        }else{
            return 2;
        }
    }

    //------------------------------------------------------------
    public function mail_opened($checked_json,$receiverID=null)
    {
        $checked =0;
        $checked_arr = json_decode($checked_json,true);
        for($i=0;$i<count($checked_arr);$i++){
            $item= $checked_arr[$i];
            if($item['receiverID']==$receiverID && $item['checked']==1){
                $checked =1;
                break;
            }
        }

       return $checked;
    }

    //------------------------------------------------------------
    public function mail_delete($mailID)
    {
        $deleteSQL = "DELETE FROM mail WHERE id IN ({$mailID}) ";
        mysqli_query($this->con,$deleteSQL);
        $delete = mysqli_affected_rows($this->con);
        if($delete){
            return 1;
        } else {
            return mysqli_error($this->con);
        }
    }

}
?>