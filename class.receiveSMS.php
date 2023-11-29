<?php

require_once 'class.common.php';
class ReceiveSMS extends Common{


    public function forward_sms_to($senderID, $senderName, $receiverID, $receiverName, $forwarded_content,$timestamp) {
        $query = "insert into sms_center(senderID,senderName,receiverID,receiverName, body,timestamp,is_forwarded) values('{$senderID}','{$senderName}','{$receiverID}','{$receiverName}','{$forwarded_content}','{$timestamp}',1)";

        $insert = mysqli_query($this->con,$query);
      
        return $insert;
    }

    public function get_Area_by_Phone($phone) {

        $total_query = "SELECT count(*) from sms_area
          WHERE phone ='{$phone}'";

        $query = "SELECT area from sms_area
          WHERE phone ='{$phone}'";

        $list = array();
        $total = $this->totalRecords($total_query ,0);
        $list['total'] = $total;
        $result = mysqli_query($this->con,$query);


        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list['area'] =$row['area'];
            }
        }
        return $list;
    }

    public function insert_phone_area($phone) {
        $query = "insert into sms_area(phone) values('{$phone}')";
        $insert = mysqli_query($this->con,$query);
        return $insert;
    }
   
    public function  get_eID_cID_by_msgid($msgid) {
        $query = "SELECT senderID, receiverID from sms_center
          WHERE message_id ='{$msgid}'";

        $result = mysqli_query($this->con,$query);
        $list_eID_cID = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list_eID_cID =$row;
            }
        }
        return $list_eID_cID;
    }

    public function normalize_phone_number($phone_number) {
        $phone1 =preg_replace('/\++|\s+|-+|\(+|\)+/', '',$phone_number);
        if (substr($phone1, 0, 1) === '1') {
            $normal_phone = substr($phone1, 1);
            // echo $normal_phone;
        } else {
            $normal_phone =  $phone1;
        }
        return $normal_phone;
    }

    public function get_contactList_returned_normalize_phone($start,$perpage) {
        //$phone_input = $this->normalize_phone_number($phone_number);
        $query = "SELECT ID,primary_phone from contact limit {$start}, {$perpage}";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['primary_phone'] = $this->normalize_phone_number($row['primary_phone']);
                $list[] =$row;
            //    print_r($list[0]);
            //    die();
            }
        }
        return $list;
    }

    //public function

    public function getContact_by_phone($phone_number){
        $selectCommand ="SELECT COUNT(*) AS NUM FROM contact";
        $total = $this->totalRecords($selectCommand,0);
        //return $total;

        print_r($total);
        print_r("<br>");
        //die();
        $perpage = 1000;
        $totalPages = ceil($total / $perpage);

        print_r("total page is".$totalPages);
        print_r("<br>");

        //echo "total page is".$totalPages;
        //echo "<br>";
        //$returned_contactID="undefined";
        $returned_contactID = 0;
        $stop = 0;
        for($i=1; $i <= $totalPages; $i++)
        {
            //echo "start".$i;
            $calc = $perpage * $i;
            $start = $calc - $perpage;
            //$result = $this->get_contactList_returned_normalize_phone($start,$perpage);
            $contact_list_search = $this->get_contactList_returned_normalize_phone($start,$perpage);
           //die();
            //print_r("mang la");
            print_r("<br>");
            $phone_input = $this->normalize_phone_number($phone_number);

            foreach($contact_list_search as $search_item) {
                if($search_item['primary_phone'] === $phone_input) {
                    $returned_contactID = $search_item['ID'];
                    //print_r();
                    print_r("We stop here and die");
                    print_r($returned_contactID);
                //    die();
                    $stop = 1;
                    break;
                }
            }

            if($stop=1) break;

        }


        return $returned_contactID;
        //die();

    }

    public function getRepresentative_contactID($contactID) {
        $query ="SELECT contact_salesman_id FROM contact where ID='{$contactID}'";
        $result = mysqli_query($this->con,$query);
        $salesman_id = 0;

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                //$row['primary_phone'] = $this->normalize_phone_number($row['primary_phone']);
               // $list[] =$row;
                $salesman_id = $row['contact_salesman_id'];
            }
        }
        return $salesman_id;

    }

    public function getSaleManagerID() {
        $query = "SELECT users FROM `groups` where ID=91";
        //$result = $conn->query($sql);
        $result = mysqli_query($this->con,$query);

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                //var_dump($row['users']);

                //echo $row['users'];
                $user_array = json_decode($row['users']);
            //    echo $user_array[0];
                $user_ID = $user_array[0];

            }
        }

        return $user_ID;
    }


    //$body,$message_id,$orginal_body,$original_msg_id,$to,$from,$timestamp,'inboundget');
        public function inbound_bak_get($api_response,$message_id,$body,$from,$to,$type,$orginal_body,$timestamp,$original_msg_id) {

            $eID_cID = $this->get_eID_cID_by_msgid($original_msg_id);
            if(isset($eID_cID) && !empty($eID_cID)) {

                $senderID = $eID_cID['receiverID'];
                $receiverID = $eID_cID['senderID'];

                $sender_name_arr = $this->getNamebyID($senderID);
                $sender_name = $sender_name_arr['contact_name'];
                $receiver_name_arr = $this->getNamebyID($receiverID);
                $receiver_name = $receiver_name_arr['contact_name'];
                //    print_r($eID_cID);
                //    print_r("<br>");

            } else { // if cant get senderID and receiverID
                /*  $senderID = null;
                  $receiverID = null;
                  $sender_name = null;
                  $receiver_name = null;*/

                $senderID = $this->getContact_by_phone($from);
                //  print_r("nguoi gui khong co id luc dau la");
                print_r($senderID);
                print_r("<br>");

                if($senderID === 0) {
                    $sender_name = "is setting";
                } else {
                    $sender_name_arr = $this->getNamebyID($senderID);
                    $sender_name = $sender_name_arr['contact_name'];
                }

                $receiverID = $this->getContact_by_phone($to);

                if($receiverID === 0){
                    // set receiverID as sales manager
                    $sales_manager_id = $this->getSaleManagerID();
                    $receiverID = $sales_manager_id;
                    $receiver_name_arr = $this->getNamebyID($receiverID);
                    $receiver_name = $receiver_name_arr['contact_name'];

                } else {
                    //
                    $representative = $this->getRepresentative_contactID($receiverID);


                    if($representative === 0){
                        // set receiverID as sales manager
                        $sales_manager_id = $this->getSaleManagerID();
                        $receiverID = $sales_manager_id;
                        $receiver_name_arr = $this->getNamebyID($receiverID);
                        $receiver_name = $receiver_name_arr['contact_name'];
                    } else {
                        $receiverID = $representative;
                        $receiver_name_arr = $this->getNamebyID($receiverID);
                        $receiver_name = $receiver_name_arr['contact_name'];
                    }
                }

            }

        $api_response = $this->protect($api_response);
        $insert = "INSERT INTO sms_center(senderID,	senderName,	receiverID,receiverName,api_response,message_id,body,msg_from,msg_to,type,original_body,timestamp,original_msg_id) VALUES ('{$senderID}','{$sender_name}','{$receiverID}','{$receiver_name}','{$api_response}','{$message_id}','{$body}','{$from}','{$to}','{$type}','{$orginal_body}','{$timestamp}','{$original_msg_id}')";
        //$insert = "INSERT INTO sms_center(message_id,body,msg_from,msg_to,type,original_body,timestamp,original_msg_id) VALUES ('{$body}','{$message_id}','{$orginal_body}','{$original_msg_id}','{$to}','{$from}','{$timestamp}','{$inboundget}')";
        mysqli_query($this->con,$insert);
        $idreturn = mysqli_insert_id($this->con);
        if(is_numeric($idreturn) && !empty($idreturn)){
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }


    }

    public function receive_smsInbound_insert($data1,$inboundget){
        $list=array();
        if(count($data1)>0){
            //print_r(count($data1));
            //print_r("total count sofar");
            //$data = $data1['data'];

            $rep1 = json_encode($data1);

            $data = $data1;
            foreach($data as $item){

                $message_id=trim($item['message_id']);
                $status="";
                $body=$item['body'];
                $body =$this->protect($body);
                $inbound_to =preg_replace('/\++|\s+|-+|\(+|\)+/', '',$item['to']);
                $inbound_from =preg_replace('/\++|\s+|-+|\(+|\)+/', '',$item['from']);
                $api_response1 = json_encode($item);
            //    $api_response = $this->protect($api_response1);
                $api_response = $rep1;

                $original_body=$item['original_body'];
                $original_body =$this->protect($original_body);
                $original_message_id = $item['original_message_id'];
                $original_message_id = $this->protect($original_message_id);
                $eID_cID = $this->get_eID_cID_by_msgid($original_message_id);

                if(isset($eID_cID) && !empty($eID_cID)) {

                    $senderID = $eID_cID['receiverID'];
                    $receiverID = $eID_cID['senderID'];

                    $sender_name_arr = $this->getNamebyID($senderID);
                    $sender_name = $sender_name_arr['contact_name'];
                    $receiver_name_arr = $this->getNamebyID($receiverID);
                    $receiver_name = $receiver_name_arr['contact_name'];
                    //    print_r($eID_cID);
                    //    print_r("<br>");

                } else { // if cant get senderID and receiverID
                    /*  $senderID = null;
                      $receiverID = null;
                      $sender_name = null;
                      $receiver_name = null;*/

                    $senderID = $this->getContact_by_phone($inbound_from);
                    //  print_r("nguoi gui khong co id luc dau la");
                    print_r($senderID);
                    print_r("<br>");

                    if($senderID === 0) {
                        $sender_name = "is setting";
                    } else {
                        $sender_name_arr = $this->getNamebyID($senderID);
                        $sender_name = $sender_name_arr['contact_name'];
                    }

                    $receiverID = $this->getContact_by_phone($inbound_to);

                    if($receiverID === 0){
                        // set receiverID as sales manager
                        $sales_manager_id = $this->getSaleManagerID();
                        $receiverID = $sales_manager_id;
                        $receiver_name_arr = $this->getNamebyID($receiverID);
                        $receiver_name = $receiver_name_arr['contact_name'];

                    } else {
                        //
                        $representative = $this->getRepresentative_contactID($receiverID);


                        if($representative === 0){
                            // set receiverID as sales manager
                            $sales_manager_id = $this->getSaleManagerID();
                            $receiverID = $sales_manager_id;
                            $receiver_name_arr = $this->getNamebyID($receiverID);
                            $receiver_name = $receiver_name_arr['contact_name'];
                        } else {
                            $receiverID = $representative;
                            $receiver_name_arr = $this->getNamebyID($receiverID);
                            $receiver_name = $receiver_name_arr['contact_name'];
                        }
                    }

                }

                $timestamp =$this->protect($item['timestamp']);

                $selectCommand ="SELECT COUNT(*) AS NUM FROM sms_center WHERE message_id ='{$message_id}'";
                if (!$this->checkExists($selectCommand)){
                    $list[] = $this->insertInboundSMS($senderID, $sender_name,$receiverID,$receiver_name,$status,$api_response,$message_id,
                        $inbound_to,$inbound_from,$inboundget,$body,$original_body,$timestamp,$original_message_id);

                }else{


                }
            }
        }
        //die();
        return $list;
    }

    public function receive_smsInbound($data1,$inboundget){
        $list=array();
        if(count($data1)>0){
            //print_r(count($data1));
            //print_r("total count sofar");
            //$data = $data1['data'];
            $data = $data1;
            foreach($data as $item){

                $message_id=trim($item['message_id']);
                $status="";
                $body=$item['body'];
                $body =$this->protect($body);
                $inbound_to =preg_replace('/\++|\s+|-+|\(+|\)+/', '',$item['to']);
                $inbound_from =preg_replace('/\++|\s+|-+|\(+|\)+/', '',$item['from']);
                $api_response1 = json_encode($item);
                $api_response = $this->protect($api_response1);

                $original_body=$item['original_body'];
                $original_body =$this->protect($original_body);
                $original_message_id = $item['original_message_id'];
                $original_message_id = $this->protect($original_message_id);
                $eID_cID = $this->get_eID_cID_by_msgid($original_message_id);
                if(isset($eID_cID) && !empty($eID_cID)) {

                    $senderID = $eID_cID['receiverID'];
                    $receiverID = $eID_cID['senderID'];

                    $sender_name_arr = $this->getNamebyID($senderID);
                    $sender_name = $sender_name_arr['contact_name'];
                    $receiver_name_arr = $this->getNamebyID($receiverID);
                    $receiver_name = $receiver_name_arr['contact_name'];
                //    print_r($eID_cID);
                //    print_r("<br>");

                } else { // if cant get senderID and receiverID
                  /*  $senderID = null;
                    $receiverID = null;
                    $sender_name = null;
                    $receiver_name = null;*/

                    $senderID = $this->getContact_by_phone($inbound_from);
                  //  print_r("nguoi gui khong co id luc dau la");
                    print_r($senderID);
                    print_r("<br>");

                    if($senderID === 0) {
                        $sender_name = "is setting";
                    } else {
                        $sender_name_arr = $this->getNamebyID($senderID);
                        $sender_name = $sender_name_arr['contact_name'];
                    }

                    $receiverID = $this->getContact_by_phone($inbound_to);

                    if($receiverID === 0){
                        // set receiverID as sales manager
                        $sales_manager_id = $this->getSaleManagerID();
                        $receiverID = $sales_manager_id;
                        $receiver_name_arr = $this->getNamebyID($receiverID);
                        $receiver_name = $receiver_name_arr['contact_name'];

                    } else {
                        //
                        $representative = $this->getRepresentative_contactID($receiverID);


                        if($representative === 0){
                            // set receiverID as sales manager
                            $sales_manager_id = $this->getSaleManagerID();
                            $receiverID = $sales_manager_id;
                            $receiver_name_arr = $this->getNamebyID($receiverID);
                            $receiver_name = $receiver_name_arr['contact_name'];
                        } else {
                            $receiverID = $representative;
                            $receiver_name_arr = $this->getNamebyID($receiverID);
                            $receiver_name = $receiver_name_arr['contact_name'];
                        }
                    }

                }

                $timestamp =$this->protect($item['timestamp']);

                $selectCommand ="SELECT COUNT(*) AS NUM FROM sms_center WHERE message_id ='{$message_id}'";
                if (!$this->checkExists($selectCommand)){
                    $list[] = $this->insertInboundSMS($senderID, $sender_name,$receiverID,$receiver_name,$status,$api_response,$message_id,
                        $inbound_to,$inbound_from,$inboundget,$body,$original_body,$timestamp,$original_message_id);

                }else{


                }
            }
        }
        //die();
        return $list;
    }

    //------------------------------------------------------------
    public function insertInboundSMS($senderID,$sender_name, $receiverID,$receiver_name, $status,$api_response,$message_id,
                                     $inbound_to,$inbound_from,$inboundget,$body
                                     ,$original_body,$timestamp,$original_message_id){

        $fields .="senderID, senderName, receiverID, receiverName, status,api_response,message_id,msg_to,msg_from,type,body,original_body,timestamp,original_msg_id";
        $values .="'{$senderID}', '{$sender_name}','{$receiverID}','{$receiver_name}','{$status}','{$api_response}','{$message_id}','{$inbound_to}',
        '{$inbound_from}','{$inboundget}','{$body}','{$original_body}','{$timestamp}','{$original_message_id}'";

        $insert = "INSERT INTO sms_center ({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insert);
        $idreturn = mysqli_insert_id($this->con);
        if(is_numeric($idreturn) && !empty($idreturn)){
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }
    }


    public function getSMStasklist($list_data) {
        $list=array();
        if(count($list_data)>0){
           // print_r(count($data1));
           // print_r("total count sofar");
            $data = $list_data['data'];
            foreach($data as $item){

                $message_id=trim($item['message_id']);
                $status="";
                $body=$item['body'];
                $body =$this->protect($body);
                $inbound_to =preg_replace('/\++|\s+|-+|\(+|\)+/', '',$item['to']);
                $inbound_from =preg_replace('/\++|\s+|-+|\(+|\)+/', '',$item['from']);
                $api_response1 = json_encode($item);
                $api_response = $this->protect($api_response1);

                $original_body=$item['original_body'];
                $original_body =$this->protect($original_body);
                $original_message_id = $item['original_message_id'];
                $original_message_id = $this->protect($original_message_id);
                $eID_cID = $this->get_eID_cID_by_msgid($original_message_id);
                if(isset($eID_cID) && !empty($eID_cID)) {

                    $senderID = $eID_cID['receiverID'];
                    $receiverID = $eID_cID['senderID'];
                    //    print_r($eID_cID);
                    //    print_r("<br>");

                } else {
                    $senderID = 12;
                    $receiverID = 13;
                }
                //$eID = 12;
                //$cID = 11;

                //print_r($original_message_id);
                //print_r('<br>');

                $timestamp =$this->protect($item['timestamp']);

                $selectCommand ="SELECT COUNT(*) AS NUM FROM sms_center WHERE original_msg_id ='{$original_message_id}'";

                $total = $this->totalRecords($selectCommand,0);
                print_r("total of".$original_message_id."is".$total);
                print_r('<br>');
                /*if (!$this->checkExists($selectCommand)){
                    $list[] = $this->insertInboundSMS($senderID, $receiverID,$status,$api_response,$message_id,
                        $inbound_to,$inbound_from,$inboundget,$body,$original_body,$timestamp,$original_message_id);

                }else{

                }*/
            }
        }
        die();
        return 1;

    }

    public function getTotalInbox($phone,$contact_ID) {

        //$phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$phone_number);
        $phone11 =0;

        if(strlen($phone)==10){
            $phone11 ="1".$phone;
        }

        $selectCommand ="SELECT COUNT(*) AS NUM FROM sms_center WHERE
                                                                msg_to ='{$phone11}' and
                                                                receiverID = '{$contact_ID}'";
        //print_r($selectCommand);
        //die();
        $total = $this->totalRecords($selectCommand,0);
        //print_r($total);
        //die();
        return $total;
    }

    public function getTotalnewMsg($contactID) {

        $selectCommand ="SELECT COUNT(*) AS NUM FROM sms_center WHERE
                                                                receiverID = '{$contactID}' and is_read IS NULL";
        //print_r($selectCommand);
        //die();
        $total = $this->totalRecords($selectCommand,0);
        //print_r($total);
        //die();
        return $total;
    }
    //------------------------------------------------------------
    public function updateInboundSMS($status,$api_response,$message_id,
                                     $inbound_to,$inbound_from){
        $updateCommand = "UPDATE `sms_center`
                SET status = '{$status}',
                   api_response='{$api_response}',
                    inbound_to='{$inbound_to}',
                    inbound_from='{$inbound_from}'
                    WHERE message_id ='{$message_id}'";

        $update = mysqli_query($this->con,$updateCommand);

    }

    //------------------------------------------------------------
    public function updateReadSMS($message_id)
    {
        $updateCommand = "UPDATE `sms_center`
                SET is_read = 1
                WHERE message_id ='{$message_id}'";

        $isUpdate = mysqli_query($this->con,$updateCommand);

        if($isUpdate){
            return 1;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------------------
    public function save_smsOutbound($data1,$send, $senderID, $receiverID,$original_body=null,$original_msg_id=null){
        $list=array();
        if(count($data1)>0){
            foreach($data1 as $item){
                $message_id=trim($item['message_id']);
                $status="";
                $outbound_to =preg_replace('/\++|\s+|-+|\(+|\)+/', '',$item['to']);
                $outbound_from =preg_replace('/\++|\s+|-+|\(+|\)+/', '',$item['from']);
                $api_response1 = $item['body'];
                $date = $item['date'];
                $api_response = $this->protect($api_response1);
                $status = $item['status'];

                $sender_name_arr = $this->getNamebyID($senderID);
                $sender_name = $sender_name_arr['contact_name'];
                $receiver_name_arr = $this->getNamebyID($receiverID);
                $receiver_name = $receiver_name_arr['contact_name'];
                //print_r($sender_name);
                //die();

                $selectCommand ="SELECT COUNT(*) AS NUM FROM sms_center WHERE message_id ='{$message_id}'";
                if (!$this->checkExists($selectCommand)){
                    $list[] = $this->insertOutboundSMS($senderID,$sender_name,$receiverID,$receiver_name,$status,$api_response,$message_id,
                        $outbound_to,$outbound_from,$send,$date,$original_body,$original_msg_id);
                }
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function insertOutboundSMS($senderID,$sender_name, $receiverID,$receiver_name,$status,$body,$message_id,
                                      $outbound_to,$outbound_from,$send,$date,$original_body=null,$original_msg_id=null ){

        $fields .="senderID,senderName,receiverID,receiverName,status,body,message_id,msg_to,msg_from,type,timestamp";
        $values .="'{$senderID}','$sender_name','{$receiverID}','{$receiver_name}','{$status}','{$body}','{$message_id}','{$outbound_to}','{$outbound_from}','{$send}','{$date}'";

        if(!empty($original_msg_id)){
            $fields .=empty($fields)?"":",";
            $fields .="original_body,original_msg_id";

            $values .=empty($values)?"":",";
            $values .="'{$original_body}','{$original_msg_id}'";

        }

        $insert = "INSERT INTO sms_center ({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insert);
        $idreturn = mysqli_insert_id($this->con);
        if(is_numeric($idreturn) && !empty($idreturn)){
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }
    }

    //-------------------------------------------------------------
    public function getPhonebyID($receiverID)
    {
        $query = "SELECT primary_phone from contact
          WHERE ID ='{$receiverID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }

    //-------------------------------------------------------------
    public function getAPIkey($contactID)
    {
        $query = "SELECT sms_api_username,sms_api_key from contact
          WHERE ID ='{$contactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }

    //-------------------------------------------------------------
    public function getNamebyID($contactID)
    {
        $query = "SELECT CONCAT(first_name,' ', middle_name,' ', last_name) as contact_name from contact WHERE ID ='{$contactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }



    //----------------------------------------------------------
    public function getInboundSMS_MsgID($ID){

        $query = "SELECT * from sms_center
          WHERE ID ='{$ID}' LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function getInbox_phone($contactID){
       /* $phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$phone_number);
        $phone11 =0;

        if(strlen($phone)==10){
            $phone11 ="1".$phone;
        }*/

        $query = "SELECT * from sms_center
          WHERE receiverID ='{$contactID}' order by timestamp desc";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function getNewInbox_phone($contactID){
        /*$phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$phone_number);
        $phone11 =0;

        if(strlen($phone)==10){
            $phone11 ="1".$phone;
        }*/

        $query = "SELECT * from sms_center
          WHERE receiverID='{$contactID}' and is_read IS NULL order by timestamp desc";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function getSentSMS($contactID){
       /* $phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$phone_number);
        $phone11 =0;

        if(strlen($phone)==10){
            $phone11 ="1".$phone;
        }*/

        $query = "SELECT * from sms_center
          WHERE senderID ='{$contactID}' order by ID desc";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function getAllSMS_contactID_related($contactID){

        $query = "SELECT * from sms_center
          WHERE receiverID ='{$contactID}' or senderID ='{$contactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }
        return $list;
    }

    /////////////////////////////////////////////////////////
}