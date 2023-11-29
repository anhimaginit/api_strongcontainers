<?php

require_once 'class.common.php';
class CustomerWarranty extends Common{

    //------------------------------------------------------------
    public function getContact_Email($email)
    {
        $query = "SELECT c.first_name,c.last_name,
        c.primary_email as email,
        com.name as company_name ,com.phone as office_phone_number

        FROM  contact as c
        left Join company as com ON com.ID = c.company_name
        where c.primary_email = '{$email}'";

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
    public function searchEmail($email)
    {
        $query = "SELECT *

        FROM  customer_warranty
        where email = '{$email}'";

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
    public function addCusWarranty($cell_phone_number,$comments_regarding_personal_property,$company_name,
                                   $email,$first_name,$last_name,$office_phone_number,$order_placed_by,
                                   $type_of_property,$prop_or_investment,$warranty_overage_for)
    {
        if(!empty($prop_or_investment)) $prop_or_investment=0;

        $fields = "cell_phone_number,comments_regarding_personal_property,company_name,email,
                            first_name,last_name,office_phone_number,order_placed_by,
                            type_of_property, prop_or_investment,warranty_overage_for";

        $values = "'{$cell_phone_number}','{$comments_regarding_personal_property}','{$company_name}','{$email}',
                '{$first_name}','{$last_name}','{$office_phone_number}','{$order_placed_by}',
                '{$type_of_property}','{$prop_or_investment}','{$warranty_overage_for}'";

        $inserts= "INSERT INTO customer_warranty({$fields}) VALUES({$values})";

        //insert record contact table
        mysqli_query($this->con,$inserts);
        $idreturn = mysqli_insert_id($this->con);

        if(is_numeric($idreturn) && $idreturn){
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }

    }

    //------------------------------------------------------------
    public function updateCusWarrStep2($ID,$cell_phone_number,$comments_regarding_personal_property,$company_name,
                                   $email,$first_name,$last_name,$office_phone_number,$order_placed_by,
                                   $type_of_property,$prop_or_investment,$warranty_overage_for)
    {
        if(!empty($prop_or_investment)) $prop_or_investment=0;

        $update = "UPDATE `customer_warranty`
                SET cell_phone_number = '{$cell_phone_number}',
                comments_regarding_personal_property = '{$comments_regarding_personal_property}',
                company_name = '{$company_name}',
                email = '{$email}',
                first_name = '{$first_name}',
                last_name = '{$last_name}',
                office_phone_number = '{$office_phone_number}',
                order_placed_by = '{$order_placed_by}',
                type_of_property = '{$type_of_property}',
                prop_or_investment = '{$prop_or_investment}',
                warranty_overage_for = '{$warranty_overage_for}'";

        $update .=" WHERE ID = '{$ID}'";

        $isUpdate = mysqli_query($this->con,$update);

        if($isUpdate){
            return 1;
        }else{
            return 0;
        }
    }

    //------------------------------------------------------------
    public function updateCusWarrStep2_5($ID,$escrow_officer_firstnane,$escrow_officer_lastnane,
                                         $title_company_name,
                                         $escrow_officer_email,$title_office_phone)
    {

        $update = "UPDATE `customer_warranty`
                SET escrow_officer_firstnane = '{$escrow_officer_firstnane}',
                escrow_officer_lastnane = '{$escrow_officer_lastnane}',
                title_company_name = '{$title_company_name}',
                escrow_officer_email = '{$escrow_officer_email}',
                title_office_phone = '{$title_office_phone}'";

        $update .=" WHERE ID = '{$ID}'";

        $isUpdate = mysqli_query($this->con,$update);

        if($isUpdate){
            return 1;
        }else{
            return 0;
        }
    }


    //------------------------------------------------------------
    public function updateCusWarrStep3($ID,$is_prop_new_existing,$property_type,
                                       $location_prop_warr_street,$location_prop_warr_address2,
                                       $location_prop_warr_city,$location_prop_warr_state,
                                       $location_prop_warr_zipcode,

                                       $sales_rep,$estimated_closing_date,$home_warranty_amount_purchase_contract,
                                       $warr_buyer_firstname,$warr_buyer_lastname,
                                       $different_warr_prop_address,$billing_address,$billing_street1,
                                        $billing_address2,$billing_city,$billing_state,
                                        $billing_zipcode,$billing_phone,$home_owner_email_checked,
                                        $home_owner_email,$additional_comments_or_concerns)
    {
        if(!empty($different_warr_prop_address)) $different_warr_prop_address=0;

        $update = "UPDATE `customer_warranty`
                SET is_prop_new_existing = '{$is_prop_new_existing}',
                property_type = '{$property_type}',
                location_prop_warr_street = '{$location_prop_warr_street}',
                location_prop_warr_address2 = '{$location_prop_warr_address2}',
                location_prop_warr_city = '{$location_prop_warr_city}',
                location_prop_warr_state = '{$location_prop_warr_state}',
                location_prop_warr_zipcode = '{$location_prop_warr_zipcode}',
                sales_rep = '{$sales_rep}',
                estimated_closing_date = '{$estimated_closing_date}',
                home_warranty_amount_purchase_contract = '{$home_warranty_amount_purchase_contract}',
                warr_buyer_firstname = '{$warr_buyer_firstname}',
                warr_buyer_lastname = '{$warr_buyer_lastname}',
                different_warr_prop_address = '{$different_warr_prop_address}',
                billing_address = '{$billing_address}',
                billing_street1 = '{$billing_street1}',
                billing_address2 = '{$billing_address2}',
                billing_city = '{$billing_city}',
                billing_state = '{$billing_state}',
                billing_zipcode ='{$billing_zipcode}',
                billing_phone ='{$billing_phone}',
                home_owner_email_checked ='{$home_owner_email_checked}',
                home_owner_email ='{$home_owner_email}',
                additional_comments_or_concerns ='{$additional_comments_or_concerns}'}'";

        $update .=" WHERE ID = '{$ID}'";

        $isUpdate = mysqli_query($this->con,$update);

        if($isUpdate){
            return 1;
        }else{
            return 0;
        }
    }


    //------------------------------------------------------------
    public function updateCusWarrStep4($ID,$additional_person_infor_firstname,$additional_person_infor_lastname,
                                       $additional_person_infor_email)
    {
        $update = "UPDATE `customer_warranty`
                SET additional_person_infor_firstname = '{$additional_person_infor_firstname}',
                additional_person_infor_lastname = '{$additional_person_infor_lastname}',
                additional_person_infor_email = '{$additional_person_infor_email}'";

        $update .=" WHERE ID = '{$ID}'";

        $isUpdate = mysqli_query($this->con,$update);

        if($isUpdate){
            return 1;
        }else{
            return 0;
        }
    }

    //------------------------------------------------------------
    public function updateCusWarrStep5($ID,$protection,$eagle_protection)
    {
        if(empty($eagle_protection)) $eagle_protection =0;
        $update = "UPDATE `customer_warranty`
                SET protection = '{$protection}',
                eagle_protection = '{$eagle_protection}'";

        $update .=" WHERE ID = '{$ID}'";

        $isUpdate = mysqli_query($this->con,$update);

        if($isUpdate){
            return 1;
        }else{
            return 0;
        }
    }


    //------------------------------------------------------------
    public function updateCusWarrStep6($ID,$charity,$discount_code)
    {
        if(empty($eagle_protection)) $eagle_protection =0;
        $update = "UPDATE `customer_warranty`
                SET charity = '{$charity}',
                discount_code = '{$discount_code}'";

        $update .=" WHERE ID = '{$ID}'";

        $isUpdate = mysqli_query($this->con,$update);

        if($isUpdate){
            return 1;
        }else{
            return 0;
        }
    }
    //------------------------------------------------------------
    public function validate_cw_fields($email,$first_name,$order_placed_by)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($first_name)){
            $error = true;
            $errorMsg = "First Name is required.";
        }

        if(!$error && empty($email)){
            $error = true;
            $errorMsg = "Email is required.";
        }

        if(!$error && empty($order_placed_by)){
            $error = true;
            $errorMsg = "Order placed by is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }
//------------------------------------------------------------
    public function validate_cw_fields_step3($property_type,$location_prop_warr_street,$location_prop_warr_city,$location_prop_warr_state,
$location_prop_warr_zipcode){
        $error = false;
        $errorMsg = "";

        if(!$error && empty($property_type)){
            $error = true;
            $errorMsg = "Property_type is required.";
        }

        if(!$error && empty($location_prop_warr_street)){
            $error = true;
            $errorMsg = "Street is required.";
        }

        if(!$error && empty($location_prop_warr_city)){
            $error = true;
            $errorMsg = "City is required.";
        }

        if(!$error && empty($location_prop_warr_state)){
            $error = true;
            $errorMsg = "State is required.";
        }

        if(!$error && empty($location_prop_warr_zipcode)){
            $error = true;
            $errorMsg = "Zipcode is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------
    public function validate_cw_fields2_5($escrow_officer_firstnane,$escrow_officer_lastnane,
                                          $escrow_officer_email,$title_office_phone){
        $error = false;
        $errorMsg = "";

        if(!$error && empty($escrow_officer_firstnane)){
            $error = true;
            $errorMsg = "First Name is required.";
        }

        if(!$error && empty($escrow_officer_lastnane)){
            $error = true;
            $errorMsg = "Last Name is required.";
        }

        if(!$error && empty($escrow_officer_email)){
            $error = true;
            $errorMsg = "Email is required.";
        }

        if(!$error && empty($title_office_phone)){
            $error = true;
            $errorMsg = "Phone is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------------------
    public function validate_cw_fields5($protection,$eagle_protection){
        $error = false;
        $errorMsg = "";

        if(!$error && empty($protection)){
            $error = true;
            $errorMsg = "Warranty - Single Family Dwelling is required.";
        }

        if(!$error && empty($eagle_protection)){
            $error = true;
            $errorMsg = "Eagle Protection.";
        }

        if(!$error && empty($escrow_officer_email)){
            $error = true;
            $errorMsg = "Email is required.";
        }

        if(!$error && empty($title_office_phone)){
            $error = true;
            $errorMsg = "Phone is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }
    //------------------------------------------------------------
    public function getCusWarrantyByID($ID) {
        $query = "SELECT * FROM  customer_warranty
        where ID = '{$ID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        if(count($list)>0){
            return $list[0];
        }else{
            return "";
        }

    }
        /////////////////////////////////////////////////////////
}