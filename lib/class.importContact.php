<?php
require_once 'class.common.php';
require_once 'class.salesman.php';
require_once 'class.affiliate.php';
class ImportContact extends Common{
    //--------------------------------------------------------------
    public function validateContactFieldEmail_Phone($primary_email,$phone)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($primary_email)&& empty($phone)){
            $error = true;
            $errorMsg = "Email or Phone Name is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }
    //------------------------------------------------
    public function contact_existing($primary_email,$primary_phone) {
        $query = "SELECT ID FROM contact WHERE  (primary_email ='{$primary_email}' && (primary_email <> null OR primary_email <> ''))
           OR
        (primary_phone ='{$primary_phone}') && (primary_phone <> null OR primary_phone <>'')";

        $rsl = mysqli_query($this->con,$query);
        $id=array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $id[] = $row;
            }
        }


        if(count($id)>1){
            return "can't update the record";
        }elseif(count($id)==1){
            return $id[0]['ID'];
        }else{
            return '';
        }

    }


    //------------------------------------------------------------------
    public function import_Contact($first_name,$middle_name,$last_name,$primary_street_address1,$primary_street_address2,
                               $primary_city,$primary_state,$primary_postal_code,$primary_phone,$primary_phone_ext,
                               $primary_phone_type,$primary_email,$primary_website,$contact_type,
                               $contact_inactive, $contact_notes,$contact_tags,$create_by,$submit_by,
                               $gps,$create_date,$company_name,$archive_id,$dateofbirth,$aff_type=null)
    {
        $fields = "first_name,middle_name,last_name,primary_street_address1,primary_street_address2,
                            primary_city,primary_state,primary_postal_code,primary_phone,primary_phone_ext,
                            primary_phone_type,primary_email,primary_website,contact_type,
                            contact_inactive, contact_notes,contact_tags,create_by,submit_by,
                            gps,create_date,company_name,archive_id";

        $values = "'{$first_name}','{$middle_name}','{$last_name}','{$primary_street_address1}','{$primary_street_address2}',
                '{$primary_city}','{$primary_state}','{$primary_postal_code}','{$primary_phone}','{$primary_phone_ext}',
                '{$primary_phone_type}','{$primary_email}','{$primary_website}','{$contact_type}',
                '{$contact_inactive}','{$contact_notes}','{$contact_tags}','{$create_by}','{$submit_by}'
                ,'{$gps}','{$create_date}','{$company_name}','{$archive_id}'";
        if(!empty($dateofbirth)){
            $fields .=",dateofbirth";
            $values .=",'{$dateofbirth}'";
        }
        $insertCommand = "INSERT INTO contact({$fields}) VALUES({$values})";
        //print_r($insertCommand);
        if(!empty($primary_phone)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE  `primary_phone` ='{$primary_phone}'";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The phone is used");
        }
        $user_name='';
        if(!empty($primary_email)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE  `primary_email` ='{$primary_email}'";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The email is used");

            $selectCommand ="SELECT COUNT(*) AS NUM FROM users WHERE  `user_name` ='{$primary_email}'";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The user is used");
            $user_name =$primary_email;
        }
        //insert record contact table
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        //check salesman active or inactive
        $p= stripos($contact_type,"Sales");
        if(is_numeric($p) && $contact_inactive==0){
            $active_salesman = 1;
        }else{
            $active_salesman = 0;
        }

        //check Affiliate Active or inavtive
        $p= stripos($contact_type,"Affiliate");
        if(is_numeric($p) && $contact_inactive==0){
            $active_affiliate = 1;
        }else{
            $active_affiliate = 0;
        }

        if(is_numeric($idreturn) && $idreturn){
            //insert record  users table
            $user_name='';
            $is_user= $this->addNew_User($idreturn,$contact_inactive,$user_name);
            if(!is_numeric($is_user)) return array("ID"=>$is_user);

            //insert record  salesman and Affiliate table
            $area = '['."'{$primary_state}'".']';
            if($active_salesman==1){
                $obSalesman = new Salesman();
                $obSalesman->addSalesman($idreturn,$active_salesman,$area);
                $obSalesman->close_conn();
                unset($obSalesman);
            }
            $affilateID="";
            if($active_affiliate==1){
                $aff_type='Real Estate Agent';
                $obAffType = new Affiliate();
                $affilateID=$obAffType->addAffliate($idreturn,$active_affiliate,$aff_type);

                $obAffType->close_conn();
                unset($obAffType);
            }


            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }

    }

    //------------------------------------------------------------------
    public function impUpdateContact($ID,$first_name,$middle_name,$last_name,$primary_street_address1,$primary_street_address2,
                                  $primary_city,$primary_state,$primary_postal_code,$primary_phone,$primary_phone_ext,
                                  $primary_phone_type,$primary_email,$primary_website,$contact_type,
                                  $contact_inactive, $contact_notes,$contact_tags,$create_by,$submit_by,
                                  $gps,$create_date,$company_name,$archive_id,$dateofbirth,$aff_type=null
                                  )
    {
        //get contact type
        $sqlText = "Select contact_type
            From contact
        where ID = '{$ID}'";
        //die($sqlText);
        $result = mysqli_query($this->con,$sqlText);

        $contact_type1 = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $contact_type1 = $row['contact_type'];
            }
        }

        $p= stripos($contact_type1,"Lead");
        if(is_numeric($p)){

        }else{
            $contact_type1 .=empty($contact_type1)?'':',';
            $contact_type1 .="Lead";
        }

        //update
        $updateCommand = "UPDATE `contact`  SET ";

        $updateCommand1 ="";
        if(!empty($first_name)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="first_name = '{$first_name}'";
        }

        if(!empty($middle_name)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="middle_name = '{$middle_name}'";
        }

        if(!empty($last_name)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="last_name = '{$last_name}'";
        }

        if(!empty($primary_street_address1)) {
            $updateCommand1 .=empty($primary_street_address1)?'':',';
            $updateCommand1 .="primary_street_address1 = '{$primary_street_address1}'";
        }

        if(!empty($primary_street_address2)) {
            $updateCommand1 .=empty($primary_street_address2)?'':',';
            $updateCommand1 .="primary_street_address2 = '{$primary_street_address2}'";
        }

        if(!empty($primary_city)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="primary_city = '{$primary_city}'";
        }

        if(!empty($primary_state)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="primary_state = '{$primary_state}'";
        }

        if(!empty($primary_postal_code)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="primary_postal_code = '{$primary_postal_code}'";
        }

        if(!empty($primary_phone)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="primary_phone = '{$primary_phone}'";
        }

        if(!empty($primary_phone_ext)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="primary_phone_ext = '{$primary_phone_ext}'";
        }

        if(!empty($primary_phone_type)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="primary_phone_type = '{$primary_phone_type}'";
        }

        if(!empty($primary_email)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="primary_email = '{$primary_email}'";
        }

        if(!empty($primary_website)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="primary_website = '{$primary_website}'";
        }

        if(!empty($contact_type1)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="contact_type = '{$contact_type1}'";
        }

        if(!empty($contact_inactive)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="contact_inactive = '{$contact_inactive}'";
        }

        if(!empty($contact_notes)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="contact_notes = '{$contact_notes}'";
        }

        if(!empty($contact_tags)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="contact_tags = '{$contact_tags}'";
        }

        if(!empty($create_by)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="create_by = '{$create_by}'";
        }
        if(!empty($submit_by)) {
            $updateCommand1 .=empty($submit_by)?'':',';
            $updateCommand1 .="submit_by = '{$submit_by}'";
        }
        if(!empty($gps)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="gps = '{$gps}'";
        }
        if(!empty($company_name)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="company_name = '{$company_name}'";
        }
        if(!empty($archive_id)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="contact_tags = '{$archive_id}'";
        }
        if(!empty($dateofbirth)) {
            $updateCommand1 .=empty($updateCommand1)?'':',';
            $updateCommand1 .="dateofbirth = '{$dateofbirth}'";
        }

        $updateCommand .=$updateCommand1." where ID ='{$ID}'";
           // print_r($updateCommand);
        if(!empty($primary_email)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE `ID` <> '{$ID}' AND `primary_email` ='{$primary_email}'";
            if ($this->checkExists($selectCommand)) return array("updated"=>"The email is used");

            $selectCommand ="SELECT COUNT(*) AS NUM FROM users WHERE `userContactID` <> '{$ID}' AND `user_name` ='{$primary_email}'";
            if ($this->checkExists($selectCommand)) return array("updated"=>"The user name is used");

        }

        if(!empty($primary_phone)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE `ID` <> '{$ID}' AND `primary_phone` ='{$primary_phone}'";
            if ($this->checkExists($selectCommand)) return array("updated"=>"The phone is used");

        }
        //update contact table
        $update = mysqli_query($this->con,$updateCommand);

        //check salesman Active or inavtive
        $area = '['."'{$primary_state}'".']';
        $active_salesman = 0;
        $p= stripos($contact_type,"Sales");
        if(is_numeric($p) && $contact_inactive==0){
            $active_salesman = 1;
        }else{
            $active_salesman=0;
        }

        //check Affiliate Active or inavtive
        $p= stripos($contact_type,"Affiliate");
        //$aff_type='Real Estate Agent';
        if(is_numeric($p) && $contact_inactive==0){
            $active_affiliate = 1;
        }else{
            $active_affiliate = 0;
        }

        if($update){
            //update users table
            if(empty($contact_inactive)){
                $contact_inactive=1;
            }else{
                $contact_inactive=0;
            }

           $user_err = $this->update_User($ID,$contact_inactive,$contact_type);

            //update salesman and affiliate table
            $this->updateSalesman($ID,$active_salesman,$area);
            $this->updateAff($ID,$aff_type,$active_affiliate);

            return 1;
        }else{
            return mysqli_error($this->con);
        }

    }

    //-------------------------------------------------
    public function addNew_User($idreturn,$contact_inactive,$user_name){
        $fields = "userActive,userContactID,user_name";
        if(empty($contact_inactive) || $contact_inactive==0){
            $contact_inactive=1;
        }else{
            $contact_inactive=0;
        }

        //data for user
        $values = "'{$contact_inactive}','{$idreturn}','{$user_name}'";

        mysqli_query($this->con,"INSERT INTO users ({$fields}) VALUES({$values})");

        $user_id = mysqli_insert_id($this->con);
        $err = mysqli_error($this->con);
        if($err){
            return $err;
        }else{
            return $user_id;
        }
    }

    //------------------------------------------------
    public function update_User($id,$userActive=null,$contact_type=null){
        if($id ){
            $updateCommand = "UPDATE `users` SET ";
            $update= "";

            if(is_numeric($userActive)){
                $update .= empty($update) ? "" : ",";
                $update .="userActive = '{$userActive}'";
            }

            $updateCommand .=$update;

            $updateCommand .= " WHERE userContactID = '{$id}'";


            $isupdate =  mysqli_query($this->con,$updateCommand);
            if($isupdate){
                return 1;
            }else{
                return mysqli_error($this->con);
            }

        }

    }

    /////////////////////////////////////////////////////////
}