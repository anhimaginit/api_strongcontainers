<?php
require __DIR__.'/vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

require_once 'class.common.php';
require_once 'class.salesman.php';
require_once 'class.affiliate.php';
require_once 'class.employee.php';

class Contact extends Common{

    //------------------------------------------------------------
    public function contactTotal($filterAll=null,$role=null,$id_login=null)
    {
        $criteria = "";

        if(!empty($filterAll)){
            $criteria .= " ((c.first_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.last_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_email LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_phone LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_city LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_state LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_postal_code LIKE '%{$filterAll}%'))";
        }

        $criteria .= !empty($criteria) ? " AND " : "";
        $criteria .= " (c.contact_inactive = 0) ";

        $sqlText = "Select Count(*) From contact_short as c";

        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }
        /*
        $criteria1 =!empty($criteria) ? " AND ".$criteria : "";

        $sqlText = "Select count(*)
                        from (
                            select DISTINCT c.ID From orders_short o
                            Left Join contact_short as c ON o.s_ID = c.ID
                            where o.b_ID = '{$id_login}' ".$criteria1. "

                            UNION
                            Select  DISTINCT c.ID
                            From contact_short as c
                            where c.ID ='{$id_login}'" .$criteria1.") tb";


        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if(($level=='Admin' && $v =='Sales') || $v =="Employee" || $v=="SystemAdmin"){
                $sqlText = "Select Count(*) From contact_short as c";

                if(!empty($criteria)){
                    $sqlText .= " WHERE ".$criteria;
                }
            }
        }*/

        $num = $this->totalRecords($sqlText,0);

        return $num;
    }


    //------------------------------------------------------------
    public function searchContactList($filterAll=null,$limit,$offset,$role=null,$id_login=null)
    {
        $criteria = "";

        if(!empty($filterAll)){
            $criteria .= " ((c.first_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.last_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.contact_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.f_m_lname LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_email LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_phone LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_city LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_state LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_street_address1 LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_postal_code LIKE '%{$filterAll}%'))";
        }

        $criteria .= !empty($criteria) ? " AND " : "";
        $criteria .= " (c.contact_inactive = 0) ";

        //admin, employee
        $v = $this->protect($role[0]["department"]);
        $level = $this->protect($role[0]['level']);
        //if(($level=='Admin' && $v =='Sales') || $v =="Employee" || $v=="SystemAdmin"){
        if(($v =='Sales') || $v =="Employee" || $v=="SystemAdmin"){
            $sqlText = "Select DISTINCT * From contact_short as c";

            if(!empty($criteria)){
                $sqlText .= " WHERE ".$criteria;
            }
        }else{
            $criteria1 =!empty($criteria) ? " AND ".$criteria : "";
            $sqlText = "Select DISTINCT c.ID,c.first_name,c.last_name,
                                c.primary_email,c.primary_phone,c.primary_city,
                                c.primary_state,c.primary_postal_code,c.contact_inactive,
                                c.create_date,c.contact_type,c.company_name,
                                c.c_name,c.c_name_company,c.contact_name,c.f_m_lname
                            From orders_short as o
                            Left Join contact_short
                             as c ON o.s_ID = c.ID
                            where o.b_ID = '{$id_login}' ".$criteria1. "

                            UNION
                            Select DISTINCT c.ID,c.first_name,c.last_name,
                                c.primary_email,c.primary_phone,c.primary_city,
                                c.primary_state,c.primary_postal_code,c.contact_inactive,
                                c.create_date,c.contact_type,c.company_name,
                                c.c_name,c.c_name_company,c.contact_name,c.f_m_lname
                            From contact_short as c
                            where (c.ID ='{$id_login}' OR c.create_by='{$id_login}')" .$criteria1;
        }

        /* not delete
        $criteria1 =!empty($criteria) ? " AND ".$criteria : "";

        $sqlText = "Select DISTINCT c.ID,c.first_name,c.last_name,
                                c.primary_email,c.primary_phone,c.primary_city,
                                c.primary_state,c.primary_postal_code,c.contact_inactive,
                                c.create_date,c.contact_type,c.company_name,
                                c.c_name,c.c_name_company,c.contact_name,c.f_m_lname
                            From orders_short as o
                            Left Join contact_short
                             as c ON o.s_ID = c.ID
                            where o.b_ID = '{$id_login}' ".$criteria1. "

                            UNION
                            Select  c.ID,c.first_name,c.last_name,
                                c.primary_email,c.primary_phone,c.primary_city,
                                c.primary_state,c.primary_postal_code,c.contact_inactive,
                                c.create_date,c.contact_type
                            From contact_short as c
                            where c.ID ='{$id_login}'" .$criteria1;

        if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if(($level=='Admin' && $v =='Sales') || $v =="Employee" || $v=="SystemAdmin"){
                $sqlText = "Select * From contact_short as c";

                if(!empty($criteria)){
                    $sqlText .= " WHERE ".$criteria;
                }
            }
        }*/

        $sqlText .= " ORDER BY ID ASC";

        if(!empty($limit)){
            $sqlText .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $sqlText .= " OFFSET {$offset} ";
        }

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function searchSalesman_idlogin($filterAll=null,$limit,$offset,$idlogin=null)
    {
        //c.contact_type,
        //get record of idlogin
        $sqlText = "Select c.ID,c.first_name,c.last_name,c.primary_email,c.primary_phone,c.primary_city,c.primary_state,c.primary_postal_code,c.contact_inactive From contact_short as c";
        $criteria = "";

        if(!empty($filterAll)){
            $criteria .= " ((c.first_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.last_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_email LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_phone LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_city LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_state LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_postal_code LIKE '%{$filterAll}%'))";
        }


        $criteria .= !empty($criteria) ? " AND " : "";
        $criteria .= " (c.contact_inactive = 0) ";

        $search_conditions =$criteria;

        if(!empty($idlogin)){
            $criteria .= !empty($criteria) ? " AND " : "";
            $criteria .= " (c.ID = '{$idlogin}') ";
        }

        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }
        //c.contact_type,
        //get salesman belong to $idlogin
        $salesman_query = "Select DISTINCT c.ID,c.first_name,c.last_name,c.primary_email,c.primary_phone,c.primary_city,c.primary_state,c.primary_postal_code,c.contact_inactive
         From orders_short o
         Inner Join contact_short as c ON o.s_ID = c.ID";

        if(!empty($idlogin)){
            $search_conditions .= !empty($search_conditions) ? " AND " : "";
            $search_conditions .= " (o.b_ID = '{$idlogin}') ";
        }

        if(!empty($search_conditions)){
            $salesman_query .= " WHERE ".$search_conditions;
        }

        //UNION
       // $query = "(".$sqlText.")". " UNION ". "(".$salesman_query.")";

        //
        $query = $salesman_query;
        $query .= " ORDER BY ID ASC";

        if(!empty($limit)){
            $query .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $query .= " OFFSET {$offset} ";
        }

        //die($query);

        /*if(!empty($orderClause)){
            //$sqlText .= " ORDER BY {$orderClause}";
        } else {
            //$sqlText .= " ORDER BY first_name";
        }*/

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

    //------------------------------------------------------------
    public function totalSalesman_idlogin($filterAll=null,$idlogin=null)
    {
        //get record of idlogin
        $sqlText = "Select count(*) From contact_short as c";
        $criteria = "";

        if(!empty($filterAll)){
            $criteria .= " ((c.first_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.last_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_email LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_phone LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_city LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_state LIKE '%{$filterAll}%') OR ";
            $criteria .= " (c.primary_postal_code LIKE '%{$filterAll}%'))";
        }


        $criteria .= !empty($criteria) ? " AND " : "";
        $criteria .= " (c.contact_inactive = 0) ";

        $search_conditions =$criteria;

        if(!empty($idlogin)){
            $criteria .= !empty($criteria) ? " AND " : "";
            $criteria .= " (c.ID = '{$idlogin}') ";
        }

        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }

        //get salesman belong to $idlogin
        $salesman_query = "Select count(distinct c.ID)
         From orders_short o
         Inner Join contact_short as c ON o.s_ID = c.ID";

        if(!empty($idlogin)){
            $search_conditions .= !empty($search_conditions) ? " AND " : "";
            $search_conditions .= " (o.b_ID = '{$idlogin}') ";
        }

        if(!empty($search_conditions)){
            $salesman_query .= " WHERE ".$search_conditions;
        }

        $salesman_query .= " GROUP BY ID";

        //UNION
//        $query ="SELECT COUNT(*)
//                    FROM
//                    (".$sqlText. " UNION ". $salesman_query.") uniontable";
        $query = $salesman_query;
        $num = $this->totalRecords($query,0);

        return $num;
    }

    //--------------------------------------------------------------
    public function validateContactFieldEmailOrPhone($primary_email,$phone)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($primary_email)&& empty($phone)){
            $error = true;
            $errorMsg = "Email or Phone Name is required.";
        }else{
           if(!empty($phone)){
               $phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$phone);
               if(preg_match("/^[0-9]{10,10}$/", $phone)){
                     //print_r("USA Phone 10");
               }else{
                   $error = true;
                   $errorMsg = "US Phone format is required.";
               }
           }

        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //--------------------------------------------------------------
    public function validate_contact_fields($first_name,$last_name,$primary_email,$primary_street_address1)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($first_name)){
            $error = true;
            $errorMsg = "First Name is required.";
        }

        if(!$error && empty($primary_street_address1)){
            //$error = true;
           //$errorMsg = "Address is required.";
        }

        if(!$error && empty($last_name)){
            $error = true;
            $errorMsg = "Last is required.";
        }

        //$email
        if(!$error && empty($primary_email)){
            $error = true;
            $errorMsg = "Email is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }
    //------------------------------------------------------------------
    public function addContact($company_name,$contact_inactive,$contact_notes,$contact_tags,$contact_type,
                            $first_name,$last_name,$middle_name,$primary_city,$primary_email,
                            $primary_phone,$primary_phone_ext,$primary_phone_type,$primary_postal_code,
                            $primary_state, $primary_street_address1,$primary_street_address2,$primary_website,$aff_type,
                            $user_name,$password,$notes,$create_by,$submit_by,$gps,$doc=null,$archive_id=null,$area=null,
                            $license_exp=null,$w9_exp=null,$insurrance_exp=null,$contact_salesman_id=null,$TaxIdentifier=null)
    {

        //verify phone and email are duplicate
        $duplicate=0;
        $primary_phone1 = $this->format_phone($primary_phone);
        if(!empty($primary_phone) && !empty($primary_email)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                        ((`primary_phone` ='{$primary_phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (`primary_email` ='{$primary_email}' AND
                          primary_email IS NOT NULL AND primary_email <>'')";
        }elseif(!empty($primary_email) && empty($primary_phone)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                          (`primary_email` ='{$primary_email}' AND
                          primary_email IS NOT NULL AND primary_email <>'') AND
                          (primary_phone IS NULL OR primary_phone ='')";
        }elseif(empty($primary_email) && !empty($primary_phone)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                          ((`primary_phone` ='{$primary_phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (primary_email IS NULL OR primary_email ='')";
        }
        if($duplicate==1){
            if ($this->checkExists($selectCommand)){
                $list_duplicate = $this->getDuplicateContact($primary_email,$primary_phone);
                return array("ID"=>"The phone and email are used",'contact_duplicated'=>$list_duplicate);
            }
        }

        /*if(!empty($primary_phone)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE  `primary_phone` ='{$primary_phone}' AND
            primary_phone IS NOT NUL AND primary_phone <>''";
            //die($selectCommand);
            if ($this->checkExists($selectCommand)) return array("ID"=>"The phone is used");
        }

        if(!empty($primary_email)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE  `primary_email` ='{$primary_email}'";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The email is used");

            //$user_temp = strtolower($user_name);
            $selectCommand ="SELECT COUNT(*) AS NUM FROM users WHERE  `user_name` ='{$primary_email}'";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The email is used");
            $user_name = $primary_email;
        }*/

        $first_name = trim($first_name);
        $last_name = trim($last_name);
        $middle_name = trim($middle_name);
        $date = date("Y-m-d");
        if(!empty($user_name)) $user_name = trim($user_name);
        if(empty($contact_salesman_id)) $contact_salesman_id=0;

        //if(!empty($primary_phone) && strlen($primary_phone)==10) $primary_phone="1".$primary_phone;
        $fields = "company_name,contact_inactive,contact_notes,contact_tags,contact_type,
                            first_name,last_name,middle_name,primary_city,primary_email,
                            primary_phone,primary_phone_ext,primary_phone_type,primary_postal_code,
                            primary_state, primary_street_address1,primary_street_address2,primary_website,
                            create_by,submit_by,gps,create_date,archive_id,contact_salesman_id,
                            TaxIdentifier";

        $values = "'{$company_name}','{$contact_inactive}','{$contact_notes}','{$contact_tags}','{$contact_type}',
                '{$first_name}','{$last_name}','{$middle_name}','{$primary_city}','{$primary_email}',
                '{$primary_phone}','{$primary_phone_ext}','{$primary_phone_type}','{$primary_postal_code}',
                '{$primary_state}','{$primary_street_address1}','{$primary_street_address2}','{$primary_website}'
                ,'{$create_by}','{$submit_by}','{$gps}','{$date}','{$archive_id}','{$contact_salesman_id}',
                '{$TaxIdentifier}'";

        $license_exp=$this->dateYmd($license_exp);
        if(!empty($license_exp)){
            $fields .=",license_exp";
            $values .=",'{$license_exp}'";
        }
        $w9_exp=$this->dateYmd($w9_exp);
        if(!empty($w9_exp)){
            $fields .=",w9_exp";
            $values .=",'{$w9_exp}'";
        }
        $insurrance_exp=$this->dateYmd($insurrance_exp);
        if(!empty($insurrance_exp)){
            $fields .=",insurrance_exp";
            $values .=",'{$insurrance_exp}'";
        }

        $insertCommand = "INSERT INTO contact({$fields}) VALUES({$values})";
        //insert record contact table
         mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        //$this->addNewContactToFireBase($idreturn,$first_name,$last_name);

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
            $is_user= $this->addNewUser($idreturn,$contact_inactive,$user_name,$contact_type);
            if(!is_numeric($is_user)) return array("ID"=>$is_user);

            //insert record  note table
            $note_err=array();
            if(is_array($notes) && count($notes)>0){
                $note_err= $this->add_notes_new($notes,$idreturn);
            }
            //insert doc
            $doc_err=array();
            if(is_array($doc) && count($doc)>0){               
                $doc_err = $this->addContactDoc($doc,$idreturn);
            }
            //insert record  salesman and Affiliate table
            if($active_salesman==1){
                $obSalesman = new Salesman();
                $obSalesman->addSalesman($idreturn,$active_salesman,$area);
                $obSalesman->close_conn();
                unset($obSalesman);
            }
            $affilateID="";
            if($active_affiliate==1){
                $obAffType = new Affiliate();
                $affilateID=$obAffType->addAffliate($idreturn,$active_affiliate,$aff_type);

                $obAffType->close_conn();
                unset($obAffType);
            }

            return array("ID"=>$idreturn,"notes"=>$note_err,"doc"=>$doc_err,"affilateID"=>$affilateID);
        }else{
            return array("ID"=>mysqli_error($this->con),"notes"=>"","doc"=>"","affilateID"=>"");
        }

    }


    //------------------------------------------------------------------
    public function addContactNotLogin($company_name,$contact_inactive,$contact_notes,$contact_tags,$contact_type,
                               $first_name,$last_name,$middle_name,$primary_city,$primary_email,
                               $primary_phone,$primary_phone_ext,$primary_phone_type,$primary_postal_code,
                               $primary_state, $primary_street_address1,$primary_street_address2,$primary_website,$aff_type,
                               $user_name,$notes,$gps,$doc=null,$archive_id=null,$area=null,
                               $license_exp=null,$w9_exp=null,$insurrance_exp=null,
                               $contact_salesman_id=null)
    {
        //verify phone and email are duplicate
        $duplicate=0;
        $primary_phone1 = $this->format_phone($primary_phone);
        if(!empty($primary_phone) && !empty($primary_email)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                        ((`primary_phone` ='{$primary_phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (`primary_email` ='{$primary_email}' AND
                          primary_email IS NOT NULL AND primary_email <>'')";
        }elseif(!empty($primary_email) && empty($primary_phone)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                          (`primary_email` ='{$primary_email}' AND
                          primary_email IS NOT NULL AND primary_email <>'') AND
                          (primary_phone IS NULL OR primary_phone ='')";
        }elseif(empty($primary_email) && !empty($primary_phone)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                          ((`primary_phone` ='{$primary_phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (primary_email IS NULL OR primary_email ='')";
        }
        if($duplicate==1){
            if ($this->checkExists($selectCommand)){
                $list_duplicate = $this->getDuplicateContact($primary_email,$primary_phone);
                return array("ID"=>"The phone and email are used",'contact_duplicated'=>$list_duplicate);
            }
        }


        $first_name = trim($first_name);
        $last_name = trim($last_name);
        $middle_name = trim($middle_name);
        $date = date("Y-m-d");
        if(empty($contact_salesman_id)) $contact_salesman_id=0;
        if(!empty($user_name)) $user_name = trim($user_name);
        $fields = "company_name,contact_inactive,contact_notes,contact_tags,contact_type,
                            first_name,last_name,middle_name,primary_city,primary_email,
                            primary_phone,primary_phone_ext,primary_phone_type,primary_postal_code,
                            primary_state, primary_street_address1,primary_street_address2,primary_website,
                            gps,create_date,archive_id,contact_salesman_id";

        $values = "'{$company_name}','{$contact_inactive}','{$contact_notes}','{$contact_tags}','{$contact_type}',
                '{$first_name}','{$last_name}','{$middle_name}','{$primary_city}','{$primary_email}',
                '{$primary_phone}','{$primary_phone_ext}','{$primary_phone_type}','{$primary_postal_code}',
                '{$primary_state}','{$primary_street_address1}','{$primary_street_address2}','{$primary_website}',
                '{$gps}','{$date}','{$archive_id}','{$contact_salesman_id}'";

        $license_exp=$this->dateYmd($license_exp);
        if(!empty($license_exp)){
            $fields .=",license_exp";
            $values .=",'{$license_exp}'";
        }

        $w9_exp=$this->dateYmd($w9_exp);
        if(!empty($w9_exp)){
            $fields .=",w9_exp";
            $values .=",'{$w9_exp}'";
        }

        $insurrance_exp=$this->dateYmd($insurrance_exp);
        if(!empty($insurrance_exp)){
            $fields .=",insurrance_exp";
            $values .=",'{$insurrance_exp}'";
        }

        $insertCommand = "INSERT INTO contact({$fields}) VALUES({$values})";
        /*
        if(!empty($primary_phone)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE  `primary_phone` ='{$primary_phone}' AND
            primary_phone IS NOT NUL AND primary_phone <>''";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The phone is used");

        }

        if(!empty($primary_email)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE  `primary_email` ='{$primary_email}'";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The email is used");

            $selectCommand ="SELECT COUNT(*) AS NUM FROM users WHERE  `user_name` ='{$primary_email}'";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The email is used");
            $user_name =$primary_email;
        }*/

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
            $is_user= $this->addNewUser($idreturn,$contact_inactive,$user_name,$contact_type);
            if(!is_numeric($is_user)) return array("ID"=>$is_user);

            //insert record  note table
            $note_err=array();
            if(is_array($notes) && count($notes)>0){
                $note_err= $this->add_notes_new($notes,$idreturn);
            }

            //insert doc
            $doc_err=array();
            if(is_array($doc) && count($doc)>0){
                $doc_err = $this->addContactDoc($doc,$idreturn);
            }

            //insert record  salesman and Affiliate table
            if($active_salesman==1){
                $obSalesman = new Salesman();
                $obSalesman->addSalesman($idreturn,$active_salesman,$area);
                $obSalesman->close_conn();
                unset($obSalesman);
            }

            $affilateID="";

            if($active_affiliate==1){
                $obAffType = new Affiliate();
                $affilateID = $obAffType->addAffliate($idreturn,$active_affiliate,$aff_type);

                $obAffType->close_conn();
                unset($obAffType);
            }

            return array("ID"=>$idreturn,"notes"=>$note_err,"doc"=>$doc_err,"affilateID"=>$affilateID);
        }else{
            return array("ID"=>mysqli_error($this->con),"notes"=>"","doc"=>"","affilateID"=>"");
        }

    }

    //-------------------------------------------------
    public function addNewUser($idreturn,$contact_inactive,$user_name,$contact_type=null,$password=null){
        $temp="";
        if(!empty($password)) $temp =md5($password);

        $fields = "userActive,userContactID,user_name";
        if(empty($contact_inactive) || $contact_inactive==0){
            $contact_inactive=1;
        }else{
            $contact_inactive=0;
        }

        //data for user
        $values = "'{$contact_inactive}','{$idreturn}','{$user_name}'";

        mysqli_query($this->con,"INSERT INTO users ({$fields}) VALUES({$values})");

        //die("INSERT INTO users ({$fields}) VALUES({$values})");

        $user_id = mysqli_insert_id($this->con);
        $err = mysqli_error($this->con);
        if($err){
            return $err;
        }else{
            return $user_id;
        }
    }
    //-------------------------------------------------$sms_api_username,$sms_api_key
    public function updateContact($id,$company_name,$contact_inactive,$contact_notes,$contact_tags,$contact_type,
                                  $first_name,$last_name,$middle_name,$primary_city,$primary_email,
                                  $primary_phone,$primary_phone_ext,$primary_phone_type,$primary_postal_code,
                                  $primary_state, $primary_street_address1,$primary_street_address2,$primary_website,$aff_type,$E_type,
                                  $user_name,$password,$notes,$userID,$up_email,$up_phone,$up_user_name,$up_password,$up_inactive,$create_by,
                                  $submit_by,$gps=null,$doc=null,$archive_id=null,$area=null,
                                  $license_exp=null,$w9_exp=null,$insurrance_exp=null,
                                  $save_anyway=null,$sms_api_username=null,$sms_api_key=null,$contact_salesman_id=null,
                                  $TaxIdentifier=null)
    {
        //print_r($save_anyway);
        if(empty($save_anyway)) $save_anyway=0;
        $first_name = trim($first_name);
        $last_name = trim($last_name);
        $middle_name = trim($middle_name);
        $primary_phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$primary_phone);

        $primary_phone1='';
        $orgin=0;
        $primary_phone =trim($primary_phone);

        //verify phone and email are duplicate
        $duplicate=0;
        $primary_phone1 = $this->format_phone($primary_phone);
        if(!empty($primary_phone) && !empty($primary_email)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                        ((`primary_phone` ='{$primary_phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (`primary_email` ='{$primary_email}' AND
                          primary_email IS NOT NULL AND primary_email <>'') AND
                          ID <> '{$id}' AND (contact_inactive=0 OR contact_inactive IS NULL)";
        }elseif(!empty($primary_email) && empty($primary_phone)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                          (`primary_email` ='{$primary_email}' AND
                          primary_email IS NOT NULL AND primary_email <>'') AND
                          (primary_phone IS NULL OR primary_phone ='') AND
                          ID <> '{$id}' AND (contact_inactive=0 OR contact_inactive IS NULL)";
        }elseif(empty($primary_email) && !empty($primary_phone)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                          ((`primary_phone` ='{$primary_phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (primary_email IS NULL OR primary_email ='') AND
                          ID <> '{$id}' AND (contact_inactive=0 OR contact_inactive IS NULL)";
        }
        if($duplicate==1 && $save_anyway==0){
            //print_r("save_anyway= ".$save_anyway." --".$duplicate);
            //print_r($selectCommand);die();
            if ($this->checkExists($selectCommand)){
                $list_duplicate = $this->getDuplicateContact($primary_email,$primary_phone,$id);
                return array("updated"=>"The phone and email are used",'contact_duplicated'=>$list_duplicate);
            }
        }
        if(empty($contact_salesman_id)) $contact_salesman_id=0;

        $license_exp=$this->dateYmd($license_exp);
        $w9_exp=$this->dateYmd($w9_exp);
        $insurrance_exp=$this->dateYmd($insurrance_exp);

        $updateCommand = "UPDATE `contact`
                SET company_name = '{$company_name}',
                contact_notes = '{$contact_notes}',
                contact_tags = '{$contact_tags}',
                contact_type = '{$contact_type}',
                first_name = '{$first_name}',
                last_name = '{$last_name}',
                middle_name = '{$middle_name}',
                primary_city = '{$primary_city}',
                primary_phone_ext = '{$primary_phone_ext}',
                primary_phone_type = '{$primary_phone_type}',
                primary_postal_code = '{$primary_postal_code}',
                primary_state = '{$primary_state}',
                primary_street_address1 = '{$primary_street_address1}',
                primary_street_address2 = '{$primary_street_address2}',
                contact_inactive = '{$contact_inactive}',
                archive_id ='{$archive_id}',
                submit_by = '{$submit_by}',
                primary_website = '{$primary_website}',
                primary_email = '{$primary_email}',
                primary_phone = '{$primary_phone}',
                sms_api_username = '{$sms_api_username}',
                sms_api_key = '{$sms_api_key}',
                contact_salesman_id ='{$contact_salesman_id}',
                TaxIdentifier='{$TaxIdentifier}'
                ";

        if(!empty($gps)) $updateCommand .=",gps = '{$gps}'";
        //if($up_email) $updateCommand .=",primary_email = '{$primary_email}'";
        //if($orgin==1) $updateCommand .=",primary_phone = '{$primary_phone1}'";

        if(!empty($license_exp)){
            $updateCommand .=",license_exp = '{$license_exp}'";
        } else{
            $updateCommand .=",license_exp = null";
        }
        if(!empty($w9_exp)){
            $updateCommand .=",w9_exp = '{$w9_exp}'";
        } else{
            $updateCommand .=",w9_exp = null";
        }
        if(!empty($insurrance_exp)){
            $updateCommand .=",insurrance_exp = '{$insurrance_exp}'";
        } else{
            $updateCommand .=",insurrance_exp = null";
        }
        //if($up_inactive) $updateCommand .=",contact_inactive = '{$contact_inactive}'";

        $updateCommand .=" WHERE ID = '{$id}'";

        //update contact table
        $update = mysqli_query($this->con,$updateCommand);

        //check salesman Active or inavtive
        $active_salesman = 0;
        $p= stripos($contact_type,"Sales");
        if(is_numeric($p) && $contact_inactive==0){
            $active_salesman = 1;
        }else{
            $active_salesman=0;
        }

        //check Affiliate Active or inavtive
        $p= stripos($contact_type,"Affiliate");
        if(is_numeric($p) && $contact_inactive==0){
            $active_affiliate = 1;
        }else{
            $active_affiliate = 0;
        }
		
		//check Employee Active or inavtive
        $p= stripos($contact_type,"Employee");
        if(is_numeric($p) && $contact_inactive==0){
            $active_employee = 1;
        }else{
            $active_employee = 0;
        }

        //"active,contactID,password,user_name";
        if($update){
            //update users table
            if(empty($contact_inactive)){
                $contact_inactive=1;
            }else{
                $contact_inactive=0;
            }

            $user_err = $this->updateUser($id,$contact_inactive,$contact_type);
            //update notes table
            $notes_err=array();
            if(is_array($notes)){
                $notes_err= $this->update_notes_new($notes,$id);
            }

            //update doc
            $doc_err=array();
            if(is_array($doc)){
                $doc_err = $this->updateDoc($doc,"contactID",$id);
            }



            //update salesman and affiliate table
			//update employee type table
            $this->updateSalesman($id,$active_salesman,$area);
            $this->updateAff($id,$aff_type,$active_affiliate);
			$this->updateEmployee($id,$E_type,$active_employee);

            return array("updated"=>1,"notes"=>$notes_err,'user'=>$user_err,'doc'=>$doc_err);
        }else{
            return array("updated"=>mysqli_error($this->con),"notes"=>"",'user'=>"",'doc'=>'');
        }

    }
    //----------------------------------------------------
    public function updateEmployee($id,$E_type,$active_employee)
    {
        $total_query = "SELECT count(*) from employee_type
          WHERE UID ='{$id}'";
        $total = $this->totalRecords($total_query ,0);

        if($total >=1) {

            $updateCommand = "UPDATE `employee_type`
                SET active = '{$active_employee}',
                e_type = '{$E_type}'
                WHERE UID = '{$id}'";

            $update = mysqli_query($this->con,$updateCommand);
        //    $update = 5;
        } else {
            $query = "insert into employee_type(UID,e_type,active) values('{$id}','{$E_type}','{$active_employee}')";
            $update = mysqli_query($this->con,$query);
        //    $update = 6;

        }

        return $update;

    }

    //------------------------------------------------
    public function updateUser($id,$userActive=null,$contact_type=null,$user_name=null,$password=null){
        $temp ="";
        if(!empty($password)) $temp =md5($password);

        $user_err="";
        if(!empty($user_name)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM users WHERE `userContactID` <> '{$id}' AND `user_name` ='{$user_name}'";
            if ($this->checkExists($selectCommand)) return "The user name is used";

            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact
                WHERE `primary_email` ='{$user_name}' AND ID <>'{$id}'";
            if ($this->checkExists($selectCommand)) return "The user name is used";
        }

        if($id ){ //|| $up_inactive
            $updateCommand = "UPDATE `users` SET ";
            $update= "";

            if(is_numeric($userActive)){
                $update .= empty($update) ? "" : ",";
                $update .="userActive = '{$userActive}'";
            }

            /*if(isset($contact_type)){
                $update .= empty($update) ? "" : ",";
                $update .="contact_type = '{$contact_type}'";
            }*/

            if(isset($user_name)){
                $update .= empty($update) ? "" : ",";
                $update .="user_name = '{$user_name}'";
            }

            /*if(isset($password)){
                $update .= empty($update) ? "" : ",";
                $update .="password = '{$temp}'";
            }*/

            $updateCommand .=$update;

            $updateCommand .= " WHERE userContactID = '{$id}'";


            $isupdate =  mysqli_query($this->con,$updateCommand);
            if($isupdate){
                return 1;
            }else{
                return mysqli_error($this->con);
            }

        }else{
            $isNew = $this->addNewUser($id,$userActive,$user_name,$contact_type,$password);

            if(is_numeric($isNew)){
                return $isNew;
            }else{
                return mysqli_error($this->con);
            }
        }

    }

    //------------------------------------------------
    public function userExisting($user_name,$contactID){
        $selectCommand ="SELECT COUNT(*) AS NUM FROM users
        WHERE `user_name` ='{$user_name}' AND userContactID <>'{$contactID}'";
        if ($this->checkExists($selectCommand)){
            return true;
        }else{
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact
        WHERE `primary_email` ='{$user_name}' AND ID <>'{$contactID}'";

            if ($this->checkExists($selectCommand)){
                return true;
            }else{
                return "";
            }
        }


    }


    //------------------------------------------------
    public function getContactByID($ID) {
        $query = "SELECT * FROM  contact
        Inner Join affiliate ON affiliate.UID = contact.ID
        left Join users ON users.userContactID = contact.ID
        where contact.ID = '{$ID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            if(count($list)>0){
                $query = "SELECT * FROM  notes
                where contactID = '{$ID}'";

                $rsl = mysqli_query($this->con,$query);
                $notesList = array();
                if($rsl){
                    while ($row = mysqli_fetch_assoc($rsl)) {
                        $notesList[] = $row;
                    }
                }

                $list[0]["notes"]=$notesList;
            }
        }
        return $list;
    }

    //------------------------------------------------
    //c.contact_type
    public function getContact_ID($ID) {
        /*$query = "SELECT c.company_name, c.contact_inactive,c.contact_notes,c.contact_tags,
        c.first_name,c.ID,c.last_name,c.middle_name,c.primary_city,
        c.primary_email,c.primary_phone,c.primary_phone_ext,c.primary_phone_type,
        c.primary_postal_code,c.primary_state,c.primary_street_address1,c.primary_street_address2,
        c.primary_website,c.gps,
        sb.submit_by as submit_by_id,
        cr.create_by as create_by_id,
        concat(sb.first_name,' ',sb.last_name) as submit_by,
        concat(cr.first_name,' ',cr.last_name) as create_by,
        u.userActive,u.userContactID,u.userID,u.user_name,
        u.contact_type,
        aff.active,aff.aff_type,aff.AID,aff.UID,
        com.ID as com_ID, com.name

        FROM  contact as c
        Left Join contact as sb ON c.submit_by = sb.ID
        Left Join contact as cr ON c.create_by = cr.ID
        Left Join affiliate as aff ON aff.UID = c.ID
        left Join users as u ON u.userContactID = c.ID
        left Join company as com ON com.ID = c.company_name
        where c.ID = '{$ID}'";*/

        $query = "SELECT company_name, contact_inactive,contact_notes,contact_tags,
        first_name,ID,last_name,middle_name,primary_city,
        primary_email,primary_phone,primary_phone_ext,primary_phone_type,
        primary_postal_code,primary_state,primary_street_address1,primary_street_address2,
        primary_website,gps,
        contact_salesman_id,
        submit_by_id,
        create_by_id,
        submit_by,
        create_by,
        userActive,userContactID,userID,user_name,
        contact_type,
        active,aff_type,e_type,AID,UID,
        com_ID, name,V_type,V_active,
        area,
        sms_api_username,
        sms_api_key,
        license_exp,w9_exp,insurrance_exp
        from contact_detail
        where ID = '{$ID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['area'] =json_decode($row['area'],true);
                $row['second_phone'] =$this->secondPhone_CID($ID);
                $row['total_overage'] =$this->getOverage_contactID($ID);
                $row['primary_phone'] = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$row['primary_phone']);
                if(strlen($row['primary_phone'])>11){
                    $row['primary_phone'] = substr($row['primary_phone'],2,10);
                }elseif(strlen($row['primary_phone'])==11){
                    $row['primary_phone'] = substr($row['primary_phone'],1,10);
                }

                $list[] = $row;
            }
            if(count($list)>0){
                $list[0]["notes"]=$this->getNotes("contact",$ID);
            }
        }
        return $list;
    }

    /*
    public function getContact_ID($ID) {
        $query = "SELECT *

        FROM  contact_detail
        where ID = '{$ID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            if(count($list)>0){
                $list[0]["notes"]=$this->getNote("contact",$ID);
            }
        }
        return $list;
    }
    */

//------------------------------------------------
    public function deleteContact($ID)
    {

        $deleteSQL = "DELETE FROM contact WHERE ID = '{$ID}' ";
        mysqli_query($this->con,$deleteSQL);
        $delete = mysqli_affected_rows($this->con);
        if($delete){
            return true;
        } else {
            return false;
        }
    }

    //------------------------------------------------
    public function existEmail($email,$id=null) {
        if(empty($id)){
            $query = "SELECT count(*) FROM  contact WHERE primary_email = '{$email}' and
            (primary_email <>'' AND primary_email IS NOT NULL ) LIMIT 1";
        }else{
            $query = "SELECT count(*) FROM  contact WHERE primary_email = '{$email}'
             AND (primary_email <>'' AND primary_email IS NOT NULL )
             AND ID <>'{$id}' LIMIT 1";
        }

        //die($query);

        $check = mysqli_query($this->con,$query);
        $row = mysqli_fetch_row($check);
        //$num = $this->totalRow($query,0);
        if ($row[0] > 0)
            return true;
        else
            return false;
    }

    //------------------------------------------------------------
    public function OrderTotalRecords($filters=null,$filterAll=null)
    {
        $sqlText = "Select * From orders";
        $criteria = "";

        if(!empty($filterAll)){
            $criteria .= " ((warranty LIKE '%{$filterAll}%') OR ";
            $criteria .= " (bill_to LIKE '%{$filterAll}%') OR ";
            $criteria .= " (payment LIKE '%{$filterAll}%') OR ";
            $criteria .= " (total LIKE '%{$filterAll}%') OR ";
            $criteria .= " (balance LIKE '%{$filterAll}%') OR ";
            $criteria .= " (salerperson LIKE '%{$filterAll}%'))";
        }

        if(count($filters)>0){
            foreach($filters as $key=>$value){
                if($key == 'warranty'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (warranty LIKE '%{$value}%') ";
                }  else if($key == 'bill_to'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (bill_to  LIKE '%{$value}%') ";
                }  else if($key == 'payment'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (payment  LIKE '%{$value}%') ";
                }  else if($key == 'total'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (total LIKE '%{$value}%') ";
                }else if($key == 'balance'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (balance LIKE '%{$value}%') ";
                }else if($key == 'salerperson'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (salerperson LIKE '%{$value}%') ";
                }
            }
        }

        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }

        $num = $this->totalRecords($sqlText,0);

        return $num;
    }

    //------------------------------------------------------------
    public function searchOderList($filters=null,$filterAll=null,$orderClause=null, $limit,$offset)
    {
        $sqlText = "Select * From orders";
        $criteria = "";

        if(!empty($filterAll)){
            $criteria .= " ((warranty LIKE '%{$filterAll}%') OR ";
            $criteria .= " (bill_to LIKE '%{$filterAll}%') OR ";
            $criteria .= " (payment LIKE '%{$filterAll}%') OR ";
            $criteria .= " (total LIKE '%{$filterAll}%') OR ";
            $criteria .= " (balance LIKE '%{$filterAll}%') OR ";
            $criteria .= " (salerperson LIKE '%{$filterAll}%'))";
        }

        if(count($filters)>0){
            foreach($filters as $key=>$value){
                if($key == 'warranty'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (warranty LIKE '%{$value}%') ";
                }  else if($key == 'bill_to'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (bill_to  LIKE '%{$value}%') ";
                }  else if($key == 'payment'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (payment  LIKE '%{$value}%') ";
                }  else if($key == 'total'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (total LIKE '%{$value}%') ";
                }else if($key == 'balance'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (balance LIKE '%{$value}%') ";
                }else if($key == 'salerperson'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (salerperson LIKE '%{$value}%') ";
                }
            }
        }


        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }

        if(!empty($limit)){
            $sqlText .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $sqlText .= " OFFSET {$offset} ";
        }

        if(!empty($orderClause)){
            //$sqlText .= " ORDER BY {$orderClause}";
        } else {
            //$sqlText .= " ORDER BY first_name";
        }

        //die($sqlText);

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //------------------------------------------------------------
    public function employeeList(){
        $sql = "Select * From employee_short
        where contact_inactive =0";

        $result = mysqli_query($this->con,$sql);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    /*
    public function dashboardContactList($limitDay,$idlogin)
    {
        //c.contact_type,
        //get salesman belong to $idlogin
        $salesman_query = "Select DISTINCT c.ID,c.first_name,c.last_name,c.primary_email,c.primary_phone,c.primary_city,c.primary_state,c.primary_postal_code,c.contact_inactive
         From orders_short o
         Inner Join contact_short as c ON o.s_ID = c.ID";

        $criteria = "";

        $criteria .= !empty($criteria) ? " AND " : "";
        $criteria .= " (c.contact_inactive = 0) ";

        if(!empty($idlogin)){
            $criteria .= !empty($criteria) ? " AND " : "";
            $criteria .= " (o.b_ID = '{$idlogin}') ";
        }

        if(!empty($criteria)){
            $salesman_query .= " WHERE ".$criteria;
        }

        $query = $salesman_query;
        $query .= " ORDER BY ID ASC";

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
    */
    //------------------------------------------------------------
    public function dashboardContactList($limitDay,$idlogin,$role=null,$start_date=null,$end_date=null)
    {

        $interval="(`c`.`create_date` > (NOW() - INTERVAL '{$limitDay}' DAY))";

        if(empty($limitDay)){
            if(!empty($start_date) && !empty($end_date)){
                $interval = "`c`.`create_date` >= '{$start_date}'";
                $interval .= "AND `c`.`create_date` <= '{$end_date}'";
            }elseif(!empty($start_date) && empty($end_date)){
                $interval = "`c`.`create_date` >= '{$start_date}'";
            }elseif(empty($start_date) && !empty($end_date)){
                $interval = "`c`.`create_date` <= '{$end_date}'";
            }
        }
        ///
        $sql = "Select DISTINCT c.ID,c.first_name,c.last_name,c.primary_email,c.primary_phone,c.primary_city,c.primary_state,
                          c.primary_postal_code,c.contact_inactive
                         From orders_short o
                         Inner Join contact_short as c ON o.s_ID = c.ID
                         where o.b_ID = '{$idlogin}' AND c.contact_inactive = 0
                         AND ".$interval;

       /* if(is_array($role)){
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if(($level=='Admin' && $v =='Sales') ||  $v=="SystemAdmin"){
                $sql = "select DISTINCT c.ID,c.first_name,c.last_name,c.primary_email,c.primary_phone,
                c.primary_city,
                    c.primary_postal_code,c.contact_inactive,c.primary_state
                    from contact_short as c
                where  c.contact_inactive = 0 AND ".$interval;
            }
        } */


        $sql .= " LIMIT 1000 ";
        $result = mysqli_query($this->con,$sql);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
     //------------------------------------------------------------
     public function addContactDoc($doc,$contactID){
        //insert record  note table
        //active date_entered document_type exp_date need_update  start_date contactID      
        
        $err = array();
        if(is_array($doc) && count($doc)>0){
            foreach($doc as $v){
                $doc_value="";
                $docFields="";
                $val ="";
                $temp1 = array();

                $note="";
                foreach($v as $key=>$item){
                    if($key=="date_entered" || $key=="exp_date" || $key=="start_date"){
                        if(!empty($item) && $key!="date_entered"){
                            $val .= empty($val) ? "" : ",";
                            $item = $this->protect($item);
                            $val .= "'{$item}'";

                            $docFields .= empty($docFields) ? "" : ",";
                            $docFields .= "{$key}";
                        }

                    }else{
                        if($key!="ID" && $key!="contactID"){
                            if(($key=="active") && empty($item)) $item=0;
                            if(($key=="need_update") && empty($item)) $item=0;

                            $val .= empty($val) ? "" : ",";
                            $item = $this->protect($item);
                            $val .= "'{$item}'";

                            $docFields .= empty($docFields) ? "" : ",";
                            $docFields .= "{$key}";
                        }

                        if($key =="document_type") $note = $item;
                    }
                    //create new array
                }

                $val .= empty($val) ? "" : ",";
                $val .= "'{$contactID}'";

                $docFields .= empty($docFields) ? "" : ",";
                $docFields .= "contactID";

                $doc_value .= empty($doc_value) ? "" : ",";
                $doc_value .= "({$val})";

                if(!empty($doc_value)){
                    $query = "INSERT INTO contact_doc ({$docFields}) VALUES{$doc_value}";

                    mysqli_query($this->con,$query);

                    if(mysqli_error($this->con)){
                        $err[] = array("doc"=>$note);
                    }
                }
               //
           }

       }

        return $err;

    }

    //------------------------------------------------------------
    public function getContactDoc_ID($contactID)
    {
        $command = "Select * from contact_doc
        where contactID='{$contactID}'";
        //print_r($insertCommand);

        $rsl = mysqli_query($this->con,$command);
        $doc = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $doc[] = $row;
            }
        }

        return $doc;

    }

    //------------------------------------------------------------
    public function getContact_state($state)
    {
        $command = "Select * from contact
        where primary_state='{$state}'";

        $rsl = mysqli_query($this->con,$command);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        return $list;

    }

    //------------------------------------------------------------
    public function checkOldPass($oldpass,$id)
    {
        $temp="";
        if(!empty($oldpass)) $temp =md5($oldpass);

        $command = "Select password from users
        where userContactID ='{$id}'";

        $rsl = mysqli_query($this->con,$command);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        if(count($list)>0) {
            return 1;
        }else{
            return 0;
        }

    }

    //------------------------------------------------------------
    public function checkUserPass($user_name,$password)
    {
        $temp="";
        if(!empty($password)) $temp =md5($password);

        $command = "Select * from users
        where user_name ='{$user_name}'";

        $rsl = mysqli_query($this->con,$command);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        if(count($list)>0) {
            return 1;
        }else{
            return 0;
        }

    }

    //------------------------------------------------
    public function existingEmail_User($email,$id=null) {

        if(empty($id)){
            $query = "SELECT count(*) AS NUM FROM  contact WHERE primary_email = '{$email}'
            AND (primary_email <>'' AND primary_email IS NOT NULL )
            LIMIT 1";
            $isUser = $this->checkExists($query);

            $query ="SELECT COUNT(*) AS NUM FROM users WHERE  `user_name` ='{$email}' AND (`user_name`<>'' AND `user_name` IS NOT NULL) ";
            $isEmail = $this->checkExists($query);


        }else{
            $query = "SELECT count(*) AS NUM FROM  contact WHERE primary_email = '{$email}'
            AND (primary_email <>'' AND primary_email IS NOT NULL )
            AND ID <>'{$id}' LIMIT 1";
            $isEmail = $this->checkExists($query);

            $query ="SELECT COUNT(*) AS NUM FROM users WHERE `userContactID` <> '{$id}' AND `user_name` ='{$email}' AND (`user_name`<>'' AND `user_name` IS NOT NULL)";
            $isUser = $this->checkExists($query);

        }

        if (!$isEmail && !$isUser )
            return array("existing"=>false, "ID"=>"");
        else
            $ID = $this->getContactID_email($email);
            return array("existing"=>true, "ID"=>$ID);
    }


    //------------------------------------------------------------
    public function getContactID_email($email)
    {
        $command = "Select ID from contact
        where primary_email ='{$email}'";

        $rsl = mysqli_query($this->con,$command);
        $ID = '';
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $ID = $row['ID'];
            }
        }

       return $ID;
    }

    //------------------------------------------------
    public function alreadyEmailUser($email) {

        $query = "SELECT `ID` FROM `contact` WHERE `primary_email`= '{$email}'
        AND (primary_email <>'' AND primary_email IS NOT NULL ) LIMIT 1";

        $rsl = mysqli_query($this->con,$query);
        $ID='';
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $ID = $row['ID'];
            }
        }

        if(empty($ID)){
            if(!empty($email)){
                $query = "SELECT  userContactID as ID FROM users WHERE user_name= '{$email}' LIMIT 1";

                $rsl = mysqli_query($this->con,$query);
                if($rsl){
                    while ($row = mysqli_fetch_assoc($rsl)) {
                        $ID = $row['ID'];
                    }
                }
            }


        }

        return $ID;

    }

    //------------------------------------------------
    public function alreadyEmailFreedom($email,$phone=null) {
        $primary_phone = preg_replace('/\s+|-+|\(+|\)+/', '',$phone);
        $phone = $this->format_phone($primary_phone);

        $query = "SELECT  `ID` FROM `contact` WHERE ((
            `primary_email`= '{$email}' AND
             (`primary_email` <>'' AND `primary_email` IS NOT NULL)
            ) OR ((`primary_phone`= '{$phone}' OR `primary_phone`= '{$primary_phone}') and
              `primary_phone`<>'' AND `primary_phone` IS NOT NULL)) and contact_inactive <> 1
           LIMIT 1";

        $rsl = mysqli_query($this->con,$query);
        $ID=0;
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $ID = $row['ID'];
            }
        }

        if(empty($ID)){
            if(!empty($email)){
                $query ="SELECT userContactID AS ID FROM users
                WHERE  `user_name` ='{$email}' AND (`user_name`<>'' AND `user_name` IS NOT NULL) ";
                $rsl = mysqli_query($this->con,$query);
                if($rsl){
                    while ($row = mysqli_fetch_assoc($rsl)) {
                        $ID = $row['ID'];
                    }
                }
            }
            return $ID;
        }else{
            return $ID;
        }
    }

    //------------------------------------------------
    public function getSale_EmailFreedom($email) {

        $query = "SELECT `SID` FROM `salesman` as s
        Left JOIN contact as c ON c.ID = s.UID
        WHERE `primary_email`= '{$email}' AND
         (`primary_email` <>'' AND `primary_email` IS NOT NULL )
         LIMIT 1";

        $rsl = mysqli_query($this->con,$query);
        $SID='';
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $SID = $row['SID'];
            }
        }

        if(empty($SID)){
            return 0;
        }else{
            return $SID;
        }
    }
    //------------------------------------------------
    /**
     * noteID ~ auto increment  
     * contactID ~ int
     * typeID ~
     * create_date ~ get date now
     * type ~ text
     * note ~ text
     * 
     * $fields
     * $values
     * 
     */
    public function installNotes($fields,$values)
    {
        $insertCommand = "INSERT INTO notes ({$fields}) VALUES({$values})";
        //die($insertCommand);
        mysqli_query($this->con,$insertCommand);       
        $idreturn = mysqli_insert_id($this->con);
        if(is_numeric($idreturn) && !empty($idreturn)){
             return $idreturn;
        }else{
            return mysqli_error($this->con);
        }

    }

    //------------------------------------------------------------
    public function getContactList($contact_name=null)
    {
        if(!empty($contact_name)){
            $sqlText = "Select distinct ID,first_name,last_name,primary_city,primary_state From contact_short
        where contact_inactive = 0 AND first_name LIKE '{$contact_name}%'";
        }else{
            $sqlText = "Select distinct ID,first_name,last_name,primary_city,primary_state From contact_short
        where contact_inactive = 0";
        }


        $sqlText .= " ORDER BY ID ASC";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------

   /* public function contactList_Search($contact_name=null)
    {
        if(!empty($contact_name)) $contact_name =trim($contact_name);
        $sqlText = "Select distinct ID as id,
        (
        CASE
            WHEN (primary_city <>'' OR primary_city <> null) AND (primary_state <> '' OR primary_state <> null) THEN concat(IFNULL(first_name,''),' ',IFNULL(last_name,''),'-',primary_city,'-',primary_state)
            WHEN (primary_city <>'' OR primary_city <> null) AND (primary_state = '' OR primary_state = null) THEN concat(IFNULL(first_name,''),' ',IFNULL(last_name,''),'-',primary_city)
            WHEN (primary_city ='' OR primary_city = null) AND (primary_state <> '' OR primary_state <> null) THEN concat(IFNULL(first_name,''),' ',IFNULL(last_name,''),'-',primary_state)
            ELSE concat(IFNULL(first_name,''),' ',IFNULL(last_name,''))
        END)  as text

         From contact_short
        where contact_inactive = 0 AND contact_name LIKE '{$contact_name}%'";

        $sqlText .= " ORDER BY ID ASC";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    } */

    //------------------------------------------------------------

    public function contactList_Search($contact_name=null)
    {
        if(!empty($contact_name)) $contact_name =trim($contact_name);
        $sqlText = "Select distinct ID as id,c_name_company  as text

         From contact_short
        where contact_inactive = 0 AND contact_name LIKE '{$contact_name}%'";

        $sqlText .= " ORDER BY ID ASC";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //------------------------------------------------------------

    public function contactListForGroup($contact_name=null,$unit=null)
    {
        if(!empty($contact_name)) $contact_name =trim($contact_name);
        $sqlText = "Select distinct ID as id,c_name  as text

         From contact_short";

        if($unit=='SystemAdmin'){
            $sqlText .= " where contact_inactive = 0  AND contact_name LIKE '{$contact_name}%'";
        }else{
            $sqlText .= " where contact_inactive = 0 AND (contact_type like '%{$unit}%' || contact_type like '%SystemAdmin%') AND contact_name LIKE '{$contact_name}%'";
        }

        $sqlText .= " ORDER BY ID ASC";

        //die($sqlText);
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function salesmanList_Search($contact_name=null)
    {
        if(!empty($contact_name)) $contact_name =trim($contact_name);
        /*
        $sqlText = "Select distinct s.SID as id,
        (
        CASE
            WHEN (c.primary_city <>'' OR c.primary_city <>null) AND (c.primary_state <> '' OR c.primary_state <> null) THEN concat(c.first_name,' ',c.last_name,'-',c.primary_city,'-',c.primary_state)
            WHEN (c.primary_city <>'' OR c.primary_city <>null) AND (c.primary_state = '' OR c.primary_state = null) THEN concat(c.first_name,' ',c.last_name,'-',c.primary_city)
            WHEN (c.primary_city ='' OR c.primary_city =null) AND (c.primary_state <> '' OR c.primary_state <> null) THEN concat(c.first_name,' ',c.last_name,'-',c.primary_state)
            ELSE concat(c.first_name,' ',c.last_name)
        END)  as text

         From salesman as s
         Inner JOIN contact_short as c on s.UID = c.ID
        where c.contact_inactive = 0 AND c.contact_name LIKE '{$contact_name}%'
        AND s.active =1"; */
        $sqlText = "Select distinct id,text
        FROM salesman_short
        WHERE text LIKE '{$contact_name}%'";

        $sqlText .= " ORDER BY ID ASC";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function searchContacts($c_name=null)
    {
        if(!empty($c_name)){
            $sqlText = "Select ID,c_name,first_name,last_name,primary_city,primary_state From contact_short
        where c_name like '%{$c_name}%' AND contact_inactive = 0 limit 100";
        }else{
            $sqlText = "Select ID,c_name,first_name,last_name,primary_city,primary_state From contact_short
        where contact_inactive = 0 limit 100";
        }

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
    public function addVendorType($UID,$active,$v_type)
    {

        $fields = "UID,active,V_type";

        $values = "'{$UID}','{$active}','{$v_type}'";

        $insertCommand = "INSERT INTO vendor_type({$fields}) VALUES({$values})";

        //print_r($insertCommand);
         mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        return $idreturn;

    }

    //------------------------------------------------------------------
    public function updateVendorType($UID,$active,$v_type)
    {
        $selectCommand ="SELECT COUNT(*) AS NUM FROM vendor_type WHERE  `UID` ='{$UID}'";

        if ($this->checkExists($selectCommand)){
            $updateaffiliate = "UPDATE `vendor_type`
                SET active = '{$active}',
                    V_type = '{$v_type}'
                WHERE UID = '{$UID}'";
             mysqli_query($this->con,$updateaffiliate);
        }else{
            $this->addVendorType($UID,$active,$v_type);
        }

    }

    //------------------------------------------------------------------
    public function addNewContact($company_name,$contact_inactive,$contact_type,
                               $first_name,$last_name,$middle_name,$primary_city,$primary_email,
                               $primary_phone,$primary_phone_ext,$primary_phone_type,$primary_postal_code,
                               $primary_state, $primary_street_address1,$primary_street_address2,$aff_type
                               )
    {
        $date = date("Y-m-d");
        $archive_id =0;
        $fields = "company_name,contact_inactive,contact_type,
                            first_name,last_name,middle_name,primary_city,primary_email,
                            primary_phone,primary_phone_ext,primary_phone_type,primary_postal_code,
                            primary_state, primary_street_address1,primary_street_address2,
                            create_date,archive_id";

        $values = "'{$company_name}','{$contact_inactive}','{$contact_type}',
                '{$first_name}','{$last_name}','{$middle_name}','{$primary_city}','{$primary_email}',
                '{$primary_phone}','{$primary_phone_ext}','{$primary_phone_type}','{$primary_postal_code}',
                '{$primary_state}','{$primary_street_address1}','{$primary_street_address2}',
                '{$date}','{$archive_id}'";

        $insertCommand = "INSERT INTO contact({$fields}) VALUES({$values})";

        $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE  `primary_email` ='{$primary_email}'
        AND (`primary_email` <>'' AND `primary_email` IS NOT NULL )";
        if ($this->checkExists($selectCommand)) return array("ID"=>"The email is used");

        if(!empty($primary_phone)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE  `primary_phone` ='{$primary_phone}' AND `primary_postal_code` ='{$primary_postal_code}'";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The phone and postal-code is used");
        }

        if(!empty($primary_email)){
            //$user_temp = strtolower($user_name);
            $selectCommand ="SELECT COUNT(*) AS NUM FROM users WHERE  `user_name` ='{$primary_email}'";
            if ($this->checkExists($selectCommand)) return array("ID"=>"The email is used");
        }

        //insert record contact table
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        //check salesman active or inactive
        $p= stripos($contact_type,"Sales");
        if(is_numeric($p)){
            $active_salesman = 1;
        }else{
            $active_salesman = 0;
        }

        //check Affiliate Active or inavtive
        $p= stripos($contact_type,"Affiliate");
        if(is_numeric($p)){
            $active_affiliate = 1;
        }else{
            $active_affiliate = 0;
        }

        if(is_numeric($idreturn) && $idreturn){
            //insert record  users table
            $is_user= $this->addNewUser($idreturn,$contact_inactive,$primary_email,$contact_type);
            if(!is_numeric($is_user)) return array("ID"=>$is_user);

            //insert record  salesman and Affiliate table
            if($active_salesman==1){
                $obSalesman = new Salesman();
                $obSalesman->addSalesman($idreturn,$active_salesman);
                $obSalesman->close_conn();
                unset($obSalesman);
            }

            if($active_affiliate==1){
                $obAffType = new Affiliate();
                $obAffType->addAffliate($idreturn,$active_affiliate,$aff_type);

                $obAffType->close_conn();
                unset($obAffType);
            }

            return array("ID"=>$idreturn);
        }else{
            return array("ID"=>mysqli_error($this->con));
        }

    }

    //------------------------------------------------------------------
    public function existingAccount($email){
        $query ="Select * from contact
                Left Join users ON users.userContactID = contact.ID
                Where contact.primary_email ='{$email}'
                AND (contact.primary_email <>'' AND contact.primary_email IS NOT NULL )
                LIMIT 1";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        if(count($list)>0){
            return 1;
        }else {
            return '';
        }
    }

    //------------------------------------------------------------------
    public function getContactAddr_ID($contactID){
        $query ="Select primary_street_address1,primary_street_address2,
                  primary_city,primary_postal_code,primary_state,contact_salesman_id,
                  first_name,middle_name,last_name
                from contact
                Where ID ='{$contactID}' LIMIT 1";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //-------------------------------------------------
    public function updateCreatebyForContact($create_by)
    {
        $updateCommand = "UPDATE `contact`
                SET create_by = '{$create_by}'
                WHERE ID = '{$create_by}'";

        //update contact table
        $update = mysqli_query($this->con,$updateCommand);
        if($update){
            return 1;
        }else{
            return 0;
        }

    }
    //------------------------------------------------
    public function getPayeeInfo($ID) {
        $query = "SELECT first_name,last_name,
        primary_street_address1,primary_city,primary_state,
        primary_postal_code,primary_email
        FROM  contact_short
        where ID = '{$ID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }
        return $list;

    }

    //------------------------------------------------------------
    public function emailSearchInternalNotInternal($email,$internal=null)
    {
        $sqlText = "Select distinct primary_email from contact_short ";
        if(empty($internal)) $internal=0;
        if($internal==0){
            $sqlText .="where contact_inactive = 0 AND (
            contact_name LIKE '{$email}%' OR primary_email LIKE '{$email}%'
            ) AND contact_type like '%Employee%'";
        }else{
            $sqlText .="where contact_inactive = 0 AND (
            contact_name LIKE '{$email}%' OR primary_email LIKE '{$email}%'
            )";
        }

        $sqlText .= " ORDER BY primary_email ASC";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function addSecondPhone($second_phone,$contact_id){
        $second_phone = explode(",",$second_phone);
        $value ="";
        foreach($second_phone as $item){
            if(!empty($item)){
                $item = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$item);
                $item =trim($item);
                if(!empty($item)){
                    //$item="1".$item;
                    $value .= (empty($value))?"":",";
                    $value .="('{$item}','{$contact_id}')";
                }
            }
        }
          //print_r($value);die();
        if(!empty($value)){
            //Delete before add
            $deleteSQL = "DELETE FROM contact_second_phone WHERE contact_id = '{$contact_id}' ";
            mysqli_query($this->con,$deleteSQL);
             mysqli_affected_rows($this->con);


            $fields ="second_phone,contact_id";
            $insertCommand = "INSERT INTO contact_second_phone({$fields}) VALUES {$value}";
            mysqli_query($this->con,$insertCommand);

            $err = mysqli_error($this->con);
            if($err) {
                return $err;
            }else{
                return 1;
            }

        }
    }
    //------------------------------------------------------------
    public function checkPhoneExist($contactID,$primary_phone){
        if(!empty($primary_phone)){
            $primary_phone1= $primary_phone;
            $primary_phone = $this->format_phone($primary_phone);
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE `ID` = '{$contactID}' AND
            (`primary_phone` ='{$primary_phone}' OR `primary_phone` ='{$primary_phone1}')";
            if (!$this->checkExists($selectCommand)){
                $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE `ID` <> '{$contactID}' AND `primary_phone` ='{$primary_phone}'";
                if ($this->checkExists($selectCommand)){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }

    //------------------------------------------------------------
    public function  secondPhone_CID($ID){
        $query ="Select * from contact_second_phone
        where contact_id='{$ID}'";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['second_phone'] = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$row['second_phone']);
                if(strlen($row['second_phone'])>11){
                    $row['second_phone'] = substr($row['second_phone'],2,10);
                }elseif(strlen($row['second_phone'])==11){
                    $row['second_phone'] = substr($row['second_phone'],1,10);
                }
                $list[] = $row;
            }
        }
        return $list;
    }


    public function  dateYmd($date){
        $format='Y-m-d';
        $d = DateTime::createFromFormat($format, $date);
        if($d && $d->format($format) === $date){
            return $date;
        }else{
            return '';
        }
    }
    //------------------------------------------------------------------
    public function getW9($contactID){
        $query ="Select if(w9_exp <= NOW(),'Yes','No') as expired,w9_exp
        from contact
        WHERE ID= '{$contactID}' AND w9_exp IS NOT NULL AND w9_exp <>''";

        $result = mysqli_query($this->con,$query);

        $renew='';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $renew = $row['expired'];
            }
        }
        return $renew;
    }

    //------------------------------------------------------------------
    public function updateW9Contact_ID($contactID,$w9_exp){
        $w9_exp=$this->dateYmd($w9_exp);
        if(!empty($w9_exp)){
            $updateCommand = "UPDATE `contact`
                SET w9_exp = '{$w9_exp}'
                WHERE ID ='{$contactID}'";

            $update = mysqli_query($this->con,$updateCommand);

            if($update){
                return '';
            }else{
                return mysqli_error($this->con);
            }
        } else{
            return "W9 is required";
        }


    }

    //-------------------------------------------------
    public function deleteEmailOrPhone($contactID,$primary_email, $primary_phone)
    {
        $update ="";
        if($primary_email==1 && $primary_phone==1){
            $update = "UPDATE `contact` SET primary_email = '',
                       primary_phone = ''
            WHERE ID = '{$contactID}'";
        }elseif($primary_email==1){
            $update = "UPDATE `contact` SET primary_email = ''
            WHERE ID = '{$contactID}'";
        }elseif($primary_phone==1){
            $update = "UPDATE `contact` SET primary_phone = ''
            WHERE ID = '{$contactID}'";
        }

        if(!empty($update)){
            $updateScc = mysqli_query($this->con,$update);
            if($updateScc){
                return 1;
            }else{
                return mysqli_error($this->con);
            }
        }else{
            return "Delete failed";
        }

    }

    /////////////////////////////////////////////////////////

    function addNewContactToFireBase($id,$myLastName,$myFirstName)
    {

        // This assumes that you have placed the Firebase credentials in the same directory
        // as this PHP file.
        $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/vendor/service-chat-call-firebase-adminsdk-mta72-0781609a81.json');

        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            // The following line is optional if the project id in your credentials file
            // is identical to the subdomain of your Firebase project. If you need it,
            // make sure to replace the URL with the URL of your project.
            ->withDatabaseUri('https://service-chat-call.firebaseio.com')
            ->create();

        $database = $firebase->getDatabase();       

        // $newPost->getKey(); // => -KVr5eu8gcTv7_AHb-3-
        // $newPost->getUri(); // => https://my-project.firebaseio.com/blog/posts/-KVr5eu8gcTv7_AHb-3-

        // $newPost->getChild('title')->set('Changed post title');
        // $newPost->getValue(); // Fetches the data from the realtime database
        // $newPost->remove();
        
        // create new users
        $newPost = $database
        ->getReference('users')
        ->push([
            'chatkey' => '123456',
            'name' => $myLastName.' '.$myFirstName,
            'id' => $id                
        ]);
            
        $sql = " SELECT * FROM `contact` WHERE ID !='{$id}' ";
        $result = mysqli_query($this->con,$sql);
            
        $list = array();
        if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                $uniqid = uniqid();
                //push my list
                $newPost = $database
                ->getReference('roomlist/'.$id)
                ->push([
                    'nameRoom' => $row['last_name'].' '.$row['first_name'],
                    'ID' => $row['ID'],
                    'img' => 'link',
                    'name' => $row['last_name'].' '.$row['first_name'],
                    'nickname' => 'null',
                    'roomID' => $uniqid         
                ]);

                //push friend list
                $newPost = $database
                ->getReference('roomlist/'.$row['ID'])                    
                ->push([
                    'nameRoom' => $myLastName.' '.$myFirstName,
                    'ID' => $id,
                    'img' => 'link',
                    'name' => $myLastName.' '.$myFirstName,
                    'nickname' => 'null',
                    'roomID' => $uniqid         
                ]);
                                
                }
        }
       
       
    }


    //------------------------------------------------
    public function getContactByEmail($email) {

        $query = "SELECT company_name, contact_inactive,contact_notes,contact_tags,
        first_name,ID,last_name,middle_name,primary_city,
        primary_email,primary_phone,primary_phone_ext,primary_phone_type,
        primary_postal_code,primary_state,primary_street_address1,primary_street_address2,
        primary_website,gps,
        submit_by_id,
        create_by_id,
        submit_by,
        create_by,
        userActive,userContactID,userID,user_name,
        contact_type,
        active,aff_type,AID,UID,
        com_ID, name,V_type,V_active,
        area,
        license_exp,w9_exp,insurrance_exp
        from contact_detail
        where primary_email = '{$email}' and primary_email <>'' and
         primary_email IS NOT NULL limit 1";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['area'] =json_decode($row['area'],true);
                $row['second_phone'] =$this->secondPhone_CID($row['ID']);
                $row['total_overage'] =$this->getOverage_contactID($row['ID']);
                $row['primary_phone'] = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$row['primary_phone']);
                if(strlen($row['primary_phone'])>11){
                    $row['primary_phone'] = substr($row['primary_phone'],2,10);
                }elseif(strlen($row['primary_phone'])==11){
                    $row['primary_phone'] = substr($row['primary_phone'],1,10);
                }

                $list[] = $row;
            }

        }
        return $list;
    }

    //------------------------------------------------
    public function getContactByPhone($phone) {
        $primary_phone = preg_replace('/\s+|-+|\(+|\)+/', '',$phone);
        $primary_phone_format= $this->format_phone($primary_phone);
        $primary_phone11='';
        $primary_phone12='';
        if(!empty($primary_phone) && strlen($primary_phone)==10){
            $primary_phone11="1".$primary_phone;
        }

        if(!empty($primary_phone) && strlen($primary_phone)==11){
            $primary_phone12="+".$primary_phone;
        }

        $query = "SELECT company_name, contact_inactive,contact_notes,contact_tags,
        first_name,ID,last_name,middle_name,primary_city,
        primary_email,primary_phone,primary_phone_ext,primary_phone_type,
        primary_postal_code,primary_state,primary_street_address1,primary_street_address2,
        primary_website,gps,
        submit_by_id,
        create_by_id,
        submit_by,
        create_by,
        userActive,userContactID,userID,user_name,
        contact_type,
        active,aff_type,AID,UID,
        com_ID, name,V_type,V_active,
        area,
        license_exp,w9_exp,insurrance_exp
        from contact_detail
        where (primary_phone = '{$primary_phone}' OR
        primary_phone ='{$primary_phone_format}' OR
        primary_phone ='{$primary_phone11}' OR
        primary_phone = '{$primary_phone12}'
        )  AND
        primary_phone<>'' AND primary_phone is not null limit 1";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['area'] =json_decode($row['area'],true);
                $row['second_phone'] =$this->secondPhone_CID($row['ID']);
                $row['total_overage'] =$this->getOverage_contactID($row['ID']);
                $row['primary_phone'] = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$row['primary_phone']);
                if(strlen($row['primary_phone'])>11){
                    $row['primary_phone'] = substr($row['primary_phone'],2,10);
                }elseif(strlen($row['primary_phone'])==11){
                    $row['primary_phone'] = substr($row['primary_phone'],1,10);
                }

                $list[] = $row;
            }

        }
        return $list;
    }

    //------------------------------------------------------------------
    public function getDuplicateContact($email,$phone,$contactID= null){
        $primary_phone1 = $this->format_phone($phone);
        if(!empty($phone) && !empty($email)){
            $query ="SELECT ID,first_name,last_name FROM contact WHERE
                        ((`primary_phone` ='{$phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (`primary_email` ='{$email}' AND
                          primary_email IS NOT NULL AND primary_email <>'')";

            if(!empty($contactID) && is_numeric($contactID)){
                $query .=" AND ID<>$contactID";
            }

        }elseif(!empty($email) && empty($phone)){
            $query ="SELECT ID,first_name,last_name FROM contact WHERE
                          (`primary_email` ='{$email}' AND
                          primary_email IS NOT NULL AND primary_email <>'') AND
                          (primary_phone IS NULL OR primary_phone='')";

            if(!empty($contactID) && is_numeric($contactID)){
                $query .=" AND ID<>$contactID";
            }

        }elseif(empty($email) && !empty($phone)){
            $query ="SELECT ID,first_name,last_name FROM contact WHERE
                        ((`primary_phone` ='{$phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (primary_email IS NULL OR primary_email='')";

            if(!empty($contactID) && is_numeric($contactID)){
                $query .=" AND ID<>$contactID";
            }

        }

        $result = mysqli_query($this->con,$query);

        $list=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[]= $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    public function updateQBEmployeeID_contactID($contactID,$qbEmployeeID){
        $updateCommand = "UPDATE `contact`
                SET qb_employee_id = '{$qbEmployeeID}'
				 WHERE ID = '{$contactID}'";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            return $qbEmployeeID;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------------------------
    public function updateQBVendorID_contactID($contactID,$qbVendorID){
        $updateCommand = "UPDATE `contact`
                SET qb_vendor_id = '{$qbVendorID}'
				 WHERE ID = '{$contactID}'";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            return $qbVendorID;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------
    public function getQBEmployeeID_ContactID($contactID) {
        $query = "SELECT qb_employee_id
        FROM  contact
        where ID = '{$contactID}'";

        $result = mysqli_query($this->con,$query);
        $list = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row["qb_employee_id"];
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function getQBVendorID_ContactID($contactID) {
        $query = "SELECT qb_vendor_id
        FROM  contact
        where ID = '{$contactID}'";

        $result = mysqli_query($this->con,$query);
        $list = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row["qb_vendor_id"];
            }
        }
        return $list;
    }

    //------------------------------------------------------------------
    public function getQBEmployeeVendorID_conID($contactID){
        $query= "Select qb_employee_id,qb_vendor_id,qb_customer_id
            from contact where ID ='{$contactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }

        }
        return $list;
    }

    //------------------------------------------------------------------
    public function getCompanyName_comID($companyID){
        $query= "Select name
            from company where ID ='{$companyID}' LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $list = "";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row["name"];
            }

        }
        return $list;
    }

    //------------------------------------------------------------------
    /*
     This function using for freedomhw.com
     */
    public function findContactByEmail($email){
       // array('Id','FirstName','LastName', 'Company','StreetAddress1', 'StreetAddress2',
       //     'City', 'State', 'PostalCode', 'BillingInformation', 'Phone1', 'Fax1');
        $query= "Select ID as Id,first_name as FirstName,last_name as LastName,
                company_name as Company,
                primary_street_address1 as StreetAddress1,
                primary_street_address2 as StreetAddress2,
                primary_city as City, primary_state as State,
                primary_postal_code as PostalCode,
                primary_phone as Phone1,
                fax as Fax1

            from contact_short where primary_email ='{$email}' LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $list = "";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

        }
        return $list;
    }

    //-----------------------------------------------------
    /*
     *update contact by Quickbooks ID
     */
    public function qbUpdateContactByQBID($id,$contact_type,
                                  $first_name,$last_name,$middle_name,$primary_city,$primary_email,
                                  $primary_phone,$primary_postal_code,
                                  $primary_state, $primary_street_address1,$primary_street_address2,$primary_website,
                                  $save_anyway=null)
    {
        if(empty($save_anyway)) $save_anyway=0;
        $first_name = trim($first_name);
        $last_name = trim($last_name);
        $middle_name = trim($middle_name);
        $primary_phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$primary_phone);

        $primary_phone =trim($primary_phone);

        //verify phone and email are duplicate
        $duplicate=0;
        $primary_phone1 = $this->format_phone($primary_phone);
        if(!empty($primary_phone) && !empty($primary_email)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                        ((`primary_phone` ='{$primary_phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (`primary_email` ='{$primary_email}' AND
                          primary_email IS NOT NULL AND primary_email <>'') AND
                          ID <> '{$id}' AND (contact_inactive=0 OR contact_inactive IS NULL)";
        }elseif(!empty($primary_email) && empty($primary_phone)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                          (`primary_email` ='{$primary_email}' AND
                          primary_email IS NOT NULL AND primary_email <>'') AND
                          (primary_phone IS NULL OR primary_phone ='') AND
                          ID <> '{$id}' AND (contact_inactive=0 OR contact_inactive IS NULL)";
        }elseif(empty($primary_email) && !empty($primary_phone)){
            $duplicate=1;
            $selectCommand ="SELECT COUNT(*) AS NUM FROM contact WHERE
                          ((`primary_phone` ='{$primary_phone}' || `primary_phone` ='{$primary_phone1}') AND
                          primary_phone IS NOT NULL AND primary_phone <>'') AND
                          (primary_email IS NULL OR primary_email ='') AND
                          ID <> '{$id}' AND (contact_inactive=0 OR contact_inactive IS NULL)";
        }
        if($duplicate==1 && $save_anyway==0){
            if ($this->checkExists($selectCommand)){
                $list_duplicate = $this->getDuplicateContact($primary_email,$primary_phone,$id);
                return array("updated"=>"The phone and email are used",'contact_duplicated'=>$list_duplicate);
            }
        }

        $updateCommand = "UPDATE `contact`
                SET primary_city = '{$primary_city}',
                primary_postal_code = '{$primary_postal_code}',
                primary_state = '{$primary_state}',
                primary_street_address1 = '{$primary_street_address1}',
                primary_street_address2 = '{$primary_street_address2}',
                primary_email = '{$primary_email}',
                primary_phone = '{$primary_phone}'
                ";

        if($contact_type=="Employee" || $contact_type=="Customer"){
            $updateCommand .=",first_name = '{$first_name}'";
            $updateCommand .=",last_name = '{$last_name}'";
            $updateCommand .=",middle_name = '{$middle_name}'";
        }

        if($contact_type=="Vendor" || $contact_type=="Customer"){
            $updateCommand .=",primary_website = '{$primary_website}'";
        }

        //if($up_inactive) $updateCommand .=",contact_inactive = '{$contact_inactive}'";
        $updateCommand .=" WHERE ID = '{$id}'";

        //update contact table
        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            return array("updated"=>1);
        }else{
            return array("updated"=>mysqli_error($this->con),"notes"=>"",'user'=>"",'doc'=>'');
        }

    }

    //------------------------------------------------------------------
    public function getContactIDByQBIDandType($quickbooksID,$type){
        $query= "Select ID
            from contact ";
        if($type=="Employee"){
            $where ="where qb_employee_id ='{$quickbooksID}'";
        }

        if($type=="Customer"){
            $where ="where qb_customer_id ='{$quickbooksID}'";
        }

        if($type=="Vendor"){
            $where ="where qb_vendor_id ='{$quickbooksID}'";
        }

        $query .=$where." LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $ID = "";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $ID = $row["ID"];
            }

        }
        return $ID;
    }
    //////////////////////////////
}