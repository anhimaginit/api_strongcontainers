<?php
require_once 'class.common.php';

class Company extends Common{
    //------------------------------------------------------------------
    public function validate_comp_fields($address1,$city,$email,$name,$phone){
        $error = false;
        $errorMsg = "";
      
        /*if(!$error && empty($address1)){
            $error = true;
            $errorMsg = "Address1 is required.";
        }*/



        if(!$error && empty($email) && empty($phone)){
            $error = true;
            $errorMsg = "Email is required or Phone is required.";
        }

        /*if(!$error && empty($name)){
            $error = true;
            $errorMsg = "name is required.";
        }

        if(!$error && empty($phone)){
            $error = true;
            $errorMsg = "phone is required.";
        }*/

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }
    //------------------------------------------------------------------
    public function getCompany_name($name){
        $query = "Select `ID`, `name`,`address1`,`city`,`state` from company
                    where name like '%{$name}%' OR
                    (email like '%{$name}%' and email<>'' and email IS NOT NULL ) OR
                    (address1 like '%{$name}%' and address1<>'' and address1 IS NOT NULL ) OR
                    (address2 like '%{$name}%' and address2<>'' and address2 IS NOT NULL ) ";

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        return $list;
    }
    //------------------------------------------------------------------
    public function getCompany_ID($ID){
        $query = "Select * from company
                    where ID ='{$ID}'";

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $row['phone'] = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$row['phone']);
                if(strlen($row['phone'])>11){
                    $row['phone'] = substr($row['phone'],2,10);
                }elseif(strlen($row['phone'])==11){
                    $row['phone'] = substr($row['phone'],1,10);
                }
                $list[] = $row;
            }
        }

        return $list;
    }
    //------------------------------------------------------------------
    public function addCompany($address1,$address2,$city,$email,
                               $fax,$name,$phone,
                               $state,$type,$www,$tag,$postal_code,$vendor=null,$vendor_type=null,$comp_note=null,$vendor_note=null,$vendor_doc=null,
                               $license_exp=null,$w9_exp=null,$insurrance_exp=null,$gps=null,$company_salesman_id=null)
    {
        $phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$phone);
        $phone =trim($phone);
        //if(!empty($phone) && strlen($phone)==10) $phone="1".$phone;
        if(empty($company_salesman_id)) $company_salesman_id=0;
        $fields = "address1,address2,city,email,fax,name,phone,state,type,www,tag,postal_code,company_salesman_id";

        $values = "'{$address1}','{$address2}','{$city}','{$email}',
                '{$fax}','{$name}','{$phone}','{$state}',
                '{$type}','{$www}','{$tag}','{$postal_code}','{$company_salesman_id}'";

        $license_exp=$this->dateYmd1($license_exp);
        if(!empty($license_exp)){
            $fields .=",license_exp";
            $values .=",'{$license_exp}'";
        }
        $w9_exp=$this->dateYmd1($w9_exp);
        if(!empty($w9_exp)){
            $fields .=",w9_exp";
            $values .=",'{$w9_exp}'";
        }
        $insurrance_exp=$this->dateYmd1($insurrance_exp);
        if(!empty($insurrance_exp)){
            $fields .=",insurrance_exp";
            $values .=",'{$insurrance_exp}'";
        }

        if(!empty($gps)){
            $fields .=",gps";
            $values .=",'{$gps}'";
        }

        $command = "INSERT INTO company ({$fields}) VALUES({$values})";
      //die($command);
        $selectCommand ="SELECT COUNT(*) AS NUM FROM company WHERE  LOWER(`name`) ='{strtolower($name)}'";
       // if ($this->checkExists($selectCommand)) return array('id'=>'','com'=>'The name is used');

        $selectCommand ="SELECT COUNT(*) AS NUM FROM company WHERE  `phone` ='{$phone}'";
        if ($this->checkExists($selectCommand)) return array('id'=>'','com'=>'The phone is used');

        $selectCommand ="SELECT COUNT(*) AS NUM FROM company WHERE  LOWER(`email`) ='{strtolower($email)}'";
        if ($this->checkExists($selectCommand)) return array('id'=>'','com'=>'The email is used');
        //insert record contact table
        //die($command);
        mysqli_query($this->con,$command);
        $comID = mysqli_insert_id($this->con);

        $notes_err= array();
        $vendor_err="";
        if(is_numeric($comID) && !empty($comID)){
            if(!empty($vendor) && $vendor==1){
                $vendor_err = $this->addVendor($vendor,$comID,$vendor_type,$vendor_note,$vendor_doc);
            }

            $comp_note = json_decode($comp_note,true);

           if(is_array($comp_note) && count($comp_note) >0){
               $notes_err= $this->add_notes_new($comp_note,"",$comID);
           }

            return array("id"=>$comID,"com"=>"","vendor"=>$vendor_err,"notes"=> $notes_err);
        }else{
            $com_err = mysqli_error($this->con);
            return array("id"=>"","com"=>$com_err,"vendor"=>$vendor_err,"notes"=>"");
        }

    }

    //------------------------------------------------------------------
    public function updateCompany($ID,$address1,$address2,$city,$email,
                                  $fax,$name,$phone,
                                  $state,$type,$www,$tag,$postal_code,$vendor=null,$vendor_type=null,$comp_note=null,$vendor_note=null,$vendor_doc=null,
                                  $license_exp=null,$w9_exp=null,$insurrance_exp=null,$gps=null,$company_salesman_id=null)
    {
        $phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$phone);
        $phone =trim($phone);
        //if(!empty($phone) && strlen($phone)==10) $phone="1".$phone;
        if(empty($company_salesman_id)) $company_salesman_id=0;
        $update = "UPDATE `company`
                SET address1 = '{$address1}',
                  address2 = '{$address2}',
                  city = '{$city}',
                  email = '{$email}',
                  fax = '{$fax}',
                  name = '{$name}',
                  phone = '{$phone}',
                  state = '{$state}',
                  type = '{$type}',
                  www = '{$www}',
                  tag = '{$tag}',
                  postal_code = '{$postal_code}',
                  company_salesman_id ='{$company_salesman_id}'
                 ";

        if(!empty($gps)) $update .=",gps = '{$gps}'";

        $license_exp=$this->dateYmd1($license_exp);
        $w9_exp=$this->dateYmd1($w9_exp);
        $insurrance_exp=$this->dateYmd1($insurrance_exp);

        if(!empty($license_exp)){
            $update .=",license_exp = '{$license_exp}'";
        } else{
            $update .=",license_exp = null";
        }
        if(!empty($w9_exp)){
            $update .=",w9_exp = '{$w9_exp}'";
        } else{
            $update .=",w9_exp = null";
        }
        if(!empty($insurrance_exp)){
            $update .=",insurrance_exp = '{$insurrance_exp}'";
        } else{
            $update .=",insurrance_exp = null";
        }

        $update .=" Where ID='{$ID}'";
        //die($update);
        $selectCommand ="SELECT COUNT(*) AS NUM FROM company
        WHERE  LOWER(`name`) ='{strtolower($name)}'
        AND ID <> '{$ID}'";
        //if ($this->checkExists($selectCommand))  return array('id'=>'','com'=>'The name is used');
        if(!empty($phone)){
            $selectCommand ="SELECT COUNT(*) AS NUM FROM company WHERE `ID` = '{$ID}' AND
            (`phone` ='{phone}')";
            if (!$this->checkExists($selectCommand)){
                $selectCommand ="SELECT COUNT(*) AS NUM FROM company WHERE `ID` <> '{$ID}' AND
            (`phone` ='{phone}')";
                if ($this->checkExists($selectCommand)){
                    return array("updated"=>"The phone is used");

                }
            }
        }
        //
        /*$selectCommand ="SELECT COUNT(*) AS NUM FROM company WHERE  `phone` ='{$phone}' AND ID <> '{$ID}'";
        if ($this->checkExists($selectCommand)) return array('id'=>'','com'=>'The phone is used');
        */
        $selectCommand ="SELECT COUNT(*) AS NUM FROM company WHERE  LOWER(`email`) ='{strtolower($email)}' AND ID <> '{$ID}'";
        if ($this->checkExists($selectCommand)) return array('id'=>'','com'=>'The email is used');

        $isupdated = mysqli_query($this->con,$update);

        $notes_err=array();
        $vendor_err="";

        if($isupdated){
            $comp_note = json_decode($comp_note,true);
            if(is_array($comp_note) && count($comp_note)>0) {
                $notes_err= $this->update_notes_new($comp_note,"",$ID);
            }

            $VendorID = $this->existingVendor_comID($ID);

            if(is_numeric($VendorID) && !empty($VendorID)){
                if($vendor!=1) $vendor =0;
                $vendor_err =$this->updateVendor($VendorID,$vendor,$ID,$vendor_type,$vendor_note,$vendor_doc);

            }else{
                if($vendor==1){
                    $vendor_err = $this->addVendor($vendor,$ID,$vendor_type,$vendor_note,$vendor_doc);
                }
            }

            return array("edit"=>"1","com"=>"","vendor"=>$vendor_err,"notes"=>$notes_err);

        }else{
            $err =mysqli_error($this->con);
            return array("id"=>"","com"=>$err,"vendor"=>"","notes"=>"");
        }

    }

    //------------------------------------------------------------------
    public function existingVendor_comID($comID)
    {
        $command = "Select ID from vendor
        where comID='{$comID}' limit 1";

        $result = mysqli_query($this->con,$command);

        $num="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $num = $row["ID"];
            }
        }

        return $num;

    }

    //------------------------------------------------------------------
    public function getVendor_comID($comID)
    {
        $command = "Select * from vendor
        where comID='{$comID}' limit 1";
        //print_r($insertCommand);

        $rsl = mysqli_query($this->con,$command);
        $vendor = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $vendor[] = $row;
            }
        }

        return $vendor;

    }

    //------------------------------------------------------------------
    public function addVendor($active,$comID,$vendor_type,$notes,$doc=null)
    {
        $fields = "active,comID,vendor_type,notes";
        if(empty($vendor_type)){
            $vendor_type = array();
            $vendor_type = json_encode($vendor_type);
        }
        $values = "'{$active}','{$comID}','{$vendor_type}','{$notes}'";

        $command = "INSERT INTO vendor ({$fields}) VALUES({$values})";

        mysqli_query($this->con,$command);
        $idreturn = mysqli_insert_id($this->con);

        if(is_numeric($idreturn) && !empty($idreturn)){
            $error = array();
            if(is_array($doc) && count($doc)>0){
                $error = $this->addVendorDoc($doc,$idreturn);
            }
            return array("id"=>$idreturn,"vendor_doc"=>$error);
        }else{
            $err = mysqli_error($this->con);
            return array("id"=>$err);
        }

    }

    //------------------------------------------------------------------
    public function updateVendor($VendorID,$active,$comID,$vendor_type=null,$vendor_note=null,$doc=null)
    {

        $update = "UPDATE `vendor`
                SET active = '{$active}',
                notes = '{$vendor_note}'";
                if($active==1){
                    $update.= ",vendor_type = '{$vendor_type}'";
                }
        $update.= "Where comID='{$comID}'";

        $isupdate = mysqli_query($this->con,$update);
        if($isupdate){
            $error = array();
            if(is_array($doc) && count($doc)>0){
                $error = $this->updateDoc($doc,"vendorID",$VendorID);
            }
            return array("comID"=>$comID,"vendor_doc"=>$error);
        }else{
            $err = mysqli_error($this->con);
            return array("comID"=>$err,"vendor_doc"=>"");
        }

    }

    //------------------------------------------------
    public function totalCompRecords($search_all)
    {
        $criteria = "";

        if(!empty($search_all)){
            $criteria .= " ((name LIKE '%{$search_all}%') OR ";
            $criteria .= " (address1 LIKE '%{$search_all}%') OR ";
            $criteria .= " (city LIKE '%{$search_all}%') OR ";
            $criteria .= " (email LIKE '%{$search_all}%') OR ";
            $criteria .= " (tag LIKE '%{$search_all}%') OR ";
            $criteria .= " (phone LIKE '%{$search_all}%'))";
        }

        $sqlText = "Select count(*) From company_short";

        if(!empty($criteria)){
            $sqlText .=" where ".$criteria;
        }
        $num = $this->totalRecords($sqlText,0);

        return $num;
    }

    //------------------------------------------------
    public function CompList($limit,$offset,$search_all)
    {
        $criteria = "";

        if(!empty($search_all)){
            $criteria .= " ((name LIKE '%{$search_all}%') OR ";
            $criteria .= " (address1 LIKE '%{$search_all}%') OR ";
            $criteria .= " (city LIKE '%{$search_all}%') OR ";
            $criteria .= " (email LIKE '%{$search_all}%') OR ";
            $criteria .= " (tag LIKE '%{$search_all}%') OR ";
            $criteria .= " (phone LIKE '%{$search_all}%'))";
        }

        $sqlText = "Select * From company_short";

        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }
        $sqlText .= " ORDER BY ID";

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

    //------------------------------------------------------------------
    public function addVendorDoc($doc,$vendorID){
        //insert record  note table
        //active date_entered document_type exp_date need_update  start_date vendorID
        $err = array();
        if(is_array($doc) && count($doc)>0){
            foreach($doc as $v){
                $doc_value="";
                $docFields="";
                $val =""; 
                $temp1 = array();

                $note="";
                foreach($v as $key=>$item){
                    //create new array
                    if($key!="ID"){ 
                        if(($key=="active") && empty($item)) $item=0;
                        if(($key=="need_update") && empty($item)) $item=0;

                        $temp1[$key] = $item;
                    }
                    
                    if($key =="document_type") $note = $item;
                }

                $temp1["vendorID"] = $vendorID;
                //create value and key
                foreach($temp1 as $kk=>$vv){
                    if($kk=="date_entered" || $kk=="exp_date" || $kk=="start_date"){
                        if(!empty($vv) && $kk!="date_entered"){
                            $val .= empty($val) ? "" : ",";
                            $vv = $this->protect($vv);
                            $val .= "'{$vv}'";

                            $docFields .= empty($docFields) ? "" : ",";
                            $docFields .= "{$kk}";
                            //print($docFields.";");
                        }

                    }else{
                        $val .= empty($val) ? "" : ",";
                        $vv = $this->protect($vv);
                        $val .= "'{$vv}'";

                        $docFields .= empty($docFields) ? "" : ",";
                        $docFields .= "{$kk}";
                    }


                }

                $doc_value .= empty($doc_value) ? "" : ",";
                $doc_value .= "({$val})";

                if(!empty($doc_value)){
                    $query = "INSERT INTO vendor_doc ({$docFields}) VALUES{$doc_value}";
                    mysqli_query($this->con,$query);

                    if(mysqli_error($this->con)){
                        $err[] = array("doc"=>$note);
                    }
                }
            }
        }

        return $err;

    }

    //------------------------------------------------------------------
    public function getVendorDoc_vendorID($vendorID)
    {
        $command = "Select * from vendor_doc
        where vendorID='{$vendorID}'";
        //print_r($insertCommand);

        $rsl = mysqli_query($this->con,$command);
        $vendor = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $vendor[] = $row;
            }
        }

        return $vendor;

    }

    //------------------------------------------------------------------
    public function deactivateContact_ID($contactID){
        $updateCommand = "UPDATE `contact`
                SET contact_inactive = '1'
                WHERE ID ='{$contactID}'";

        $update = mysqli_query($this->con,$updateCommand);
        if($update){
            return 1;
        }else{
            return mysqli_error($this->con);
        }
    }
    //------------------------------------------------------------------
    public function  dateYmd1($date){
        $format='Y-m-d';
        $d = DateTime::createFromFormat($format, $date);
        if($d && $d->format($format) === $date){
            return $date;
        }else{
            return '';
        }
    }

    //------------------------------------------------------------------
    public function updateW9Company_ID($companyID,$w9_exp){
        $w9_exp=$this->dateYmd1($w9_exp);
        if(!empty($w9_exp)){
            $updateCommand = "UPDATE `company`
                SET w9_exp = '{$w9_exp}'
                WHERE ID ='{$companyID}'";

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

    //------------------------------------------------
    /*
     * Search both company and contact
     *
     */
    public function comp_contact_search($search_all,$role=null,$id_login=null)
    {
        //company
        $criteria1 = "";
        if(!empty($search_all)){
            $criteria1 .= " ((name LIKE '%{$search_all}%') OR ";
            $criteria1 .= " (address1 LIKE '%{$search_all}%') OR ";
            $criteria1 .= " (city LIKE '%{$search_all}%') OR ";
            $criteria1 .= " (email LIKE '%{$search_all}%') OR ";
            $criteria1 .= " (tag LIKE '%{$search_all}%') OR ";
            $criteria1 .= " (phone LIKE '%{$search_all}%'))";
        }

        $sqlText = "Select ID, name,address1,phone,city,email,type From company_short";

        if(!empty($criteria1)){
            $sqlText .= " WHERE ".$criteria1;
        }
        $sqlText .= " ORDER BY ID";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row["type"] = json_decode($row["type"],true);
                $row["company"] = 1;
                $list[] = $row;
            }
        }
        //Contact
        $criteria = "";

        if(!empty($search_all)){
            $criteria .= " ((first_name LIKE '%{$search_all}%') OR ";
            $criteria .= " (last_name LIKE '%{$search_all}%') OR ";
            $criteria .= " (contact_name LIKE '%{$search_all}%') OR ";
            $criteria .= " (f_m_lname LIKE '%{$search_all}%') OR ";
            $criteria .= " (primary_email LIKE '%{$search_all}%') OR ";
            $criteria .= " (primary_phone LIKE '%{$search_all}%') OR ";
            $criteria .= " (primary_city LIKE '%{$search_all}%') OR ";
            $criteria .= " (primary_state LIKE '%{$search_all}%') OR ";
            $criteria .= " (contact_tags LIKE '%{$search_all}%') OR ";
            $criteria .= " (primary_street_address1 LIKE '%{$search_all}%') OR ";
            $criteria .= " (primary_postal_code LIKE '%{$search_all}%'))";
        }
        $criteria .= !empty($criteria) ? " AND " : "";
        $criteria .= " (c.contact_inactive = 0) ";


        //admin, employee  name,address1,phone,city,email,type
        $v = $role[0]["department"];
        $level = $role[0]['level'];
        if(($level=='Admin' && $v =='Sales') || $v =="Employee" || $v=="SystemAdmin"){
            $sqlText = "Select DISTINCT c.ID, c.f_m_lname as name,c.primary_street_address1 as address1,
             c.primary_phone as phone,
             c.primary_city as city,c.primary_email as email,c.contact_type as type
             From contact_short as c";

            if(!empty($criteria)){
                $sqlText .= " WHERE ".$criteria;
            }
        }else{
            $criteria2 =!empty($criteria) ? " AND ".$criteria : "";

            $sqlText = "Select DISTINCT c.ID,c.f_m_lname as name,c.primary_street_address1 as address1,
             c.primary_phone as phone,
             c.primary_city as city,c.primary_email as email,c.contact_type as type
                            From orders_short as o
                            Left Join contact_short
                             as c ON o.s_ID = c.ID
                            where o.b_ID = '{$id_login}' ".$criteria2. "

                            UNION
                            Select DISTINCT c.ID,c.f_m_lname as name,c.primary_street_address1 as address1,
             c.primary_phone as phone,
             c.primary_city as city,c.primary_email as email,c.contact_type as type
                            From contact_short as c
                            where (c.ID ='{$id_login}' OR c.create_by='{$id_login}')" .$criteria2;
        }
        $sqlText .= " LIMIT 1000 ";
        //die($sqlText);
        $result = mysqli_query($this->con,$sqlText);

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row["company"] = 0;
                $list[] = $row;
            }
        }

        //
        return $list;
    }
    /////////////////////////////////////////////////////////
}