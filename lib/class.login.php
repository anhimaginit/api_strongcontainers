<?php
require_once 'class.common.php';
require_once 'class.salesman.php';
require_once 'class.affiliate.php';

include_once 'config.php';
include_once 'php-jwt/BeforeValidException.php';
include_once 'php-jwt/ExpiredException.php';
include_once 'php-jwt/SignatureInvalidException.php';
include_once 'php-jwt/JWT.php';
use \Firebase\JWT\JWT;


class Login extends Common{
    //------------------------------------------------------------
    public function loginEmailPass($login_type,$email,$phone,$zipcode,$user_name=null,$pass=null,$type=null){
        $query="";
        $type1=$type;
        if($type =="PolicyHolder")  $type1 ="Policy Holder";
        $list = array();
        if($login_type==1){
            $query ="Select * from contact
                Where contact.primary_email ='{$email}' AND contact.contact_inactive=0 AND contact.primary_phone = '{$phone}'
                 AND contact.contact_type like '%{$type1}%' LIMIT 1";

        }elseif($login_type==2){
            $query ="Select * from contact
                Where contact.primary_email ='{$email}' AND contact.contact_inactive=0 AND contact.primary_postal_code = '{$zipcode}'
                  AND contact.contact_type like '%{$type1}%' LIMIT 1";
        }elseif($login_type==3){
            $query ="Select * from contact
                Where contact.primary_postal_code ='{$zipcode}' AND contact.contact_inactive=0 AND contact.primary_phone = '{$phone}'
                 AND contact.contact_type like '%{$type1}%' LIMIT 1";
        }
        elseif($login_type==5){
            if($type1!='SystemAdmin'){
                $query ="Select DISTINCT c.archive_id,c.company_name,c.contact_inactive,
                  c.contact_notes,c.contact_tags,c.contact_type,c.create_by,
                  c.create_date,c.first_name,c.gps,c.ID,
                  c.last_name,c.middle_name,c.primary_city,c.primary_email,
                  c.primary_phone,c.primary_phone_ext,c.primary_phone_type,
                  c.primary_postal_code,c.primary_state,c.primary_street_address1,
                  c.primary_street_address2,c.primary_website,c.sms_api_key,c.sms_api_username,c.submit_by
                from contact as c
                left join users as u on u.userContactID = c.ID
                Where c.primary_email ='{$email}' AND c.contact_inactive=0
                AND c.contact_type like '%{$type1}%' LIMIT 1";
            }else{
                $query = $this->checkContactIsSystemAdmin($email);

                if(empty($query)){
                    return $list;
                }
            }

        }else{
            return $list;
        }

        $result = mysqli_query($this->con,$query);

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list)>0){
                unset($list[0]['password']);

                $rsl_token = $this->loginGenerateToken1($list[0]['ID'],$list[0]['first_name'],$list[0]['last_name'],$list[0]['primary_email'],$type);

                if(count($rsl_token)>0){
                    $list[0]["jwt"] = $rsl_token[0]['jwt'];
                    $list[0]["jwt_refresh"] = $rsl_token[0]['jwt_refresh'];
                    $list[0]["acl_list"] = $rsl_token[0]['acl_list'];
                }else{
                    $list[0]["jwt"] = array();
                    $list[0]["jwt_refresh"] = "";
                    $list[0]["acl_list"] = array();
                }

                //
                $contactIDs = array();
                $ordersIDs = array();
                $invoicesIDs =array();
                $warrantiesIDs =array();
                $claimIDs=array();
                $transactionIDs=array();
                //only for test about three months
                //$contactIDs = $this->contactsAdminLogin();
                //$ordersIDs = $this->orders();
                //$invoicesIDs = $this->invoices();
                //$warrantiesIDs = $this->warranties();
                //$claimIDs = $this->claimsCreatedBy();
                /*
                if($type=="Sales" && is_numeric($rsl_token[0]['admin']) || $type=='SystemAdmin'){
                    $contactIDs = $this->contactsAdminLogin();
                    $ordersIDs = $this->orders();
                    $invoicesIDs = $this->invoices();
                    $warrantiesIDs = $this->warranties();
                    $claimIDs = $this->claimsCreatedBy();
                }else if($type=="Employee"){
                    $contactIDs = $this->contactsAdminLogin();
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $warrantiesIDs = $this->warranties($list[0]['ID']);
                    $claimIDs = $this->claimsForEmployee($list[0]['ID']);
                }
                else if($type=="Sales" && $rsl_token[0]['admin']==''){
                    $contactIDs = $this->contacts($list[0]['ID']);
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $warrantiesIDs = $this->warranties($list[0]['ID']);
                    $claimIDs = $this->claimsForSales($list[0]['ID']);
                }elseif($type=="Affiliate"){
                    $warrIDs = $this->warrsContsForAffiliate($list[0]['ID'],$type);
                    $warrantiesIDs = $warrIDs['warrIDs'];
                    $contactIDs = $this->contacts($list[0]['ID']);
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $claimIDs = $this->claimsforAffiliate("",$list[0]['ID']);

                }elseif( $type=="Vendor"){
                    $contactIDs = $this->contacts($list[0]['ID']);
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $warrantiesIDs = $this->warranties($list[0]['ID']);
                    $claimIDs = $this->claimsforVendor($list[0]['ID']);

                }elseif($type=="Policy Holder" || $type=="PolicyHolder"){
                    $contactIDs = $this->contacts($list[0]['ID']);
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $warrantiesIDs = $this->warranties($list[0]['ID']);
                    $claimIDs = $this->claimsforAffiliate("",$list[0]['ID']);
                } */

                $productsIDs = array();//$this->products();

                $list[0]["IDs"]=array("contactIDs"=>$contactIDs,"productsIDs"=>$productsIDs,
                    "ordersIDs"=>$ordersIDs,"invoicesIDs"=>$invoicesIDs,"warrantiesIDs"=>$warrantiesIDs,
                    "claimIDs"=>$claimIDs,"transactionIDs"=>$transactionIDs);
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function checkContactIsSystemAdmin($email){
        $query ="Select DISTINCT COUNT(*)
                from contact as c
                left join users as u on u.userContactID = c.ID
                Where c.primary_email ='{$email}'  AND c.contact_inactive=0
                AND c.contact_type like '%SystemAdmin%' LIMIT 1";

        $num = $this->totalRecords($query,0);

        $return_query="";
        if($num<1){
            //does ContactID  belong to Systemadmin unit?
            $query ="Select DISTINCT c.ID
                from contact as c
                left join users as u on u.userContactID = c.ID
                Where c.primary_email ='{$email}' AND c.contact_inactive=0
                LIMIT 1";

                $result = mysqli_query($this->con,$query);

                $ID_Contact='';
                if($result){
                    while ($row = mysqli_fetch_assoc($result)) {
                        $ID_Contact = $row['ID'];
                    }
                }

                if(!empty($ID_Contact)){
                    $roles_Q ="Select count(*) from groups
            Where department ='SystemAdmin' AND JSON_SEARCH(`users`, 'all', '{$ID_Contact}') IS NOT NULL";

                    $isSystemAdmin = $this->totalRecords($roles_Q,0);

                    if($isSystemAdmin>0){
                        $return_query ="Select DISTINCT c.archive_id,c.company_name,c.contact_inactive,
                  c.contact_notes,c.contact_tags,c.contact_type,c.create_by,
                  c.create_date,c.first_name,c.gps,c.ID,
                  c.last_name,c.middle_name,c.primary_city,c.primary_email,
                  c.primary_phone,c.primary_phone_ext,c.primary_phone_type,
                  c.primary_postal_code,c.primary_state,c.primary_street_address1,
                  c.primary_street_address2,c.primary_website,c.submit_by
                from contact as c
                left join users as u on u.userContactID = c.ID
                Where c.ID ='{$ID_Contact}'  AND c.contact_inactive=0
                 LIMIT 1";
                    }
                }

        }else{
            $return_query ="Select DISTINCT c.archive_id,c.company_name,c.contact_inactive,
                  c.contact_notes,c.contact_tags,c.contact_type,c.create_by,
                  c.create_date,c.first_name,c.gps,c.ID,
                  c.last_name,c.middle_name,c.primary_city,c.primary_email,
                  c.primary_phone,c.primary_phone_ext,c.primary_phone_type,
                  c.primary_postal_code,c.primary_state,c.primary_street_address1,
                  c.primary_street_address2,c.primary_website,c.submit_by
                from contact as c
                left join users as u on u.userContactID = c.ID
                Where c.primary_email ='{$email}' AND c.contact_inactive=0
                AND c.contact_type like '%SystemAdmin%' LIMIT 1";
        }

        return $return_query;
    }
    //------------------------------------------------------------
    public function loginEmailPass1($login_type,$email,$phone,$zipcode,$user_name=null,$pass=null,$type=null){
        $query="";

        $list = array();
        if($login_type==1){
            $query ="Select * from contact
                Where contact.primary_email ='{$email}' AND contact.contact_inactive=0 AND contact.primary_phone = '{$phone}'
                 AND contact.contact_type like '%{$type}%' LIMIT 1";

        }elseif($login_type==2){
            $query ="Select * from contact
                Where contact.primary_email ='{$email}' AND contact.contact_inactive=0 AND contact.primary_postal_code = '{$zipcode}'
                  AND contact.contact_type like '%{$type}%' LIMIT 1";
        }elseif($login_type==3){
            $query ="Select * from contact
                Where contact.primary_postal_code ='{$zipcode}' AND contact.contact_inactive=0 AND contact.primary_phone = '{$phone}'
                 AND contact.contact_type like '%{$type}%' LIMIT 1";
        }
        elseif($login_type==5){
            $query ="Select * from contact
                Where contact.primary_email ='{$email}' AND contact.contact_inactive=0
                AND contact.contact_type like '%{$type}%' LIMIT 1";
        }else{
            return $list;
        }

        $result = mysqli_query($this->con,$query);

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list)>0){
                unset($list[0]['password']);

                $rsl_token = $this->loginGenerateToken1($list[0]['ID'],$list[0]['first_name'],$list[0]['last_name'],$list[0]['primary_email'],$type);

                if(count($rsl_token)>0){
                    $list[0]["jwt"] = $rsl_token[0]['jwt'];
                    $list[0]["jwt_refresh"] = $rsl_token[0]['jwt_refresh'];
                    $list[0]["acl_list"] = $rsl_token[0]['acl_list'];
                }else{
                    $list[0]["jwt"] = array();
                    $list[0]["jwt_refresh"] = "";
                    $list[0]["acl_list"] = array();
                }

                //
                $contactIDs = array();
                $ordersIDs = array();
                $invoicesIDs =array();
                $warrantiesIDs =array();
                $claimIDs=array();
                $transactionIDs=array();
                if($type=="Sales" && is_numeric($rsl_token[0]['admin'])){
                    $contactIDs = $this->contactsAdminLogin();
                    $ordersIDs = $this->orders();
                    $invoicesIDs = $this->invoices();
                    $warrantiesIDs = $this->warranties();
                    $claimIDs = $this->claimsCreatedBy();
                    //$transactionIDs = $this->transactions_person();
                }else if($type=="Employee"){
                    $contactIDs = $this->contactsAdminLogin();
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $warrantiesIDs = $this->warranties($list[0]['ID']);
                    $claimIDs = $this->claimsForEmployee($list[0]['ID']);
                    //$transactionIDs = $this->transactions_person($list[0]['ID']);
                }
                else if($type=="Sales" && $rsl_token[0]['admin']==''){
                    $contactIDs = $this->contacts($list[0]['ID']);
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $warrantiesIDs = $this->warranties($list[0]['ID']);
                    $claimIDs = $this->claimsForSales($list[0]['ID']);
                    //$transactionIDs = $this->transactions_person($list[0]['ID']);
                }elseif($type=="Affiliate"){
                    $warrIDs = $this->warrsContsForAffiliate($list[0]['ID'],$type);
                    $warrantiesIDs = $warrIDs['warrIDs'];
                    $contactIDs = $this->contacts($list[0]['ID']);
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $claimIDs = $this->claimsforAffiliate("",$list[0]['ID']);
                    //$contactIDs = $this->contactsForAffiliate($list[0]['ID'],$warrIDs['contactIDs']);
                    //$ordersIDs = $this->orderforAffiliate($warrantiesIDs,$list[0]['ID']);
                    //$invoicesIDs =$this->invsforAffiliate($ordersIDs);

                }elseif( $type=="Vendor"){
                    //$v_inf = $this->getRelativeDataforVendor($list[0]['ID'],$type);

                    $contactIDs = $this->contacts($list[0]['ID']);
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $warrantiesIDs = $this->warranties($list[0]['ID']);
                    $claimIDs = $this->claimsforVendor($list[0]['ID']);

                    //$contactIDs = $v_inf['contactIDs'];

                    //$ordersIDs = $this->ordersForVendor($warrantiesIDs,$list[0]['ID']);
                    //$invoicesIDs = $this->invsForVendor($ordersIDs,$list[0]['ID']);
                }elseif($type=="Policy Holder" || $type=="PolicyHolder"){
                    $contactIDs = $this->contacts($list[0]['ID']);
                    $ordersIDs = $this->orders($list[0]['ID']);
                    $invoicesIDs = $this->invoices($list[0]['ID']);
                    $warrantiesIDs = $this->warranties($list[0]['ID']);
                    $claimIDs = $this->claimsforAffiliate("",$list[0]['ID']);
                }

                $productsIDs = $this->products();

                $list[0]["IDs"]=array("contactIDs"=>$contactIDs,"productsIDs"=>$productsIDs,
                    "ordersIDs"=>$ordersIDs,"invoicesIDs"=>$invoicesIDs,"warrantiesIDs"=>$warrantiesIDs,
                    "claimIDs"=>$claimIDs,"transactionIDs"=>$transactionIDs);
            }
        }

        return $list;
    }

    //------------------------------------------------------------

   public function exsitingEmail($primary_email){
       $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE  `primary_email` ='{$primary_email}'";
       if ($this->checkExists($selectCommand)){
           return 1;
       }else{
           return '';
       }
   }


    /////////////////////////////////////////////////////////
}