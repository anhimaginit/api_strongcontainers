<?php
include_once './lib/emailaddress.php';
require_once 'db.php';
include_once 'config.php';

require_once 'PHPMailer-5.2.27/PHPMailerAutoload.php';

include_once 'php-jwt/BeforeValidException.php';
include_once 'php-jwt/ExpiredException.php';
include_once 'php-jwt/SignatureInvalidException.php';

include_once 'php-jwt/JWT.php';
use \Firebase\JWT\JWT ;
//use \Firebase\JWT ;
JWT::$leeway = 30;

class Common extends dbConnect
{
    const API_FOLDER_NAME = "api";
    protected $users_child = array();
    protected $parent_repeat = array();
    //----------------------------------------------------------
    public function protect($dirty_string)
    {
        $clean_string =  mysqli_real_escape_string($this->con,$dirty_string);
        return $clean_string;
    }
    //----------------------------------------------------------
    public function checkExists($sqlText)
    {
       $check = mysqli_query($this->con,$sqlText);
        $row = mysqli_fetch_row($check);

        if ($row[0] > 0)
            return true;
        else
            return false; 
    }

    //----------------------------------------------------------
    public function checkExisting($sqlText)
    {
        $check = mysqli_query($this->con,$sqlText);
        $row = mysqli_fetch_row($check);

        if ($row[0] > 0)
            return 1;
        else
            return "";
    }
    //----------------------------------------------------------
    public function existRow($sqlText){
        $result = mysqli_query($this->con,$sqlText);
        $exists = false;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $exists = true;
                break;
            }            
        }
        return $exists;
    }
    //----------------------------------------------------------
    public function getRow($sqlText){ 
        //mysql_query("SET NAMES 'utf8'");  
        $result = mysqli_query($this->con,$sqlText);
        $info = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $info = $row;
                break;
            }            
        }
        return $info;
    }

    //----------------------------------------------------------
    public function totalRow($sqlText,$defaulValue)
    {
        $result = mysqli_query($this->con,$sqlText);
        $value = $defaulValue;
        if($result){
            while ( mysqli_fetch_assoc($result)) {
                $value ++;
            }
        }

        if(empty($value)){
            $value = $defaulValue;
        }
        return $value;
    }

    //----------------------------------------------------------
    public function totalRecords($sqlText,$defaulValue)
    {
        $result = mysqli_query($this->con,$sqlText);
        $row = mysqli_fetch_row($result);

        if ($row[0] > 0)
            $value =$row[0];
        else
            $value = $defaulValue;

        if(empty($value)){
            $value = $defaulValue;
        }
        return $value;
    }
    //----------------------------------------------------------
    public function getValue($sqlText,$defaulValue)
    {
        $result = mysqli_query($this->con,$sqlText);
        $value = $defaulValue;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                foreach($row as $key=>$v){
                    $value = $v;
                    break;                    
                }
                break;
            }            
        }
        if(empty($value)){
            $value = $defaulValue;
        }
        return $value;        
    }

    //----------------------------------------------------------
    public function getList($sqlText){
        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }            
        }
        return $list;
    }


    //----------------------------------------------------------
    public function columnsFilterOr($colums,$value)
    {
        $criteria = "";
        $i=0;
        foreach($colums as $item){
            if($i==0){
                $criteria .= " {$item} LIKE '%{$value}%' ";
            }else{
                $criteria .= " OR {$item} LIKE '%{$value}%' ";
            }
            $i++;
        }
        return $criteria;
    }

    //----------------------------------------------------------
    public function columnFilterAnd($criteriaData)
    {
         $criteria = "";
         foreach($criteriaData as $key=>$value){
             $criteria .= empty($criteria) ? "" : " AND ";
             $criteria .= " ({$key} LIKE '%{$value}%') ";
         }

         return $criteria;
    }

   //----------------------------------------------------------
    public function basicAuth($vallue){
       if($vallue=="MjE0YTIwMzYxOTllNDdlZGU0OGI3ZTQ2OGM3OTZkYjUtdXMxOQ=="){
            return true;
        }else{
            return false;
        }

        $key = base64_decode($vallue);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://us19.api.mailchimp.com/3.0/?apikey='.$key);

        curl_setopt($curl, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

       // Send the request
        $result = curl_exec($curl);

      // Get some cURL session information back
        $info = curl_getinfo($curl);
        curl_close($curl);
        
	if($info['http_code']==200) {
            return true;
        }else{
            return false;
        }
    }

    //----------------------------------------------------------
    public function auth($jwt,$publicKey){
        $config = new Config();
        $secretKey = $config->jwt_key;
        $secretKey = base64_decode($secretKey).$publicKey;

        unset($config);
        //die($secretKey);
        if ($jwt) {
            try {
                JWT::$leeway = 30;
                $token = JWT::decode($jwt, $secretKey, array('HS512'));
                return array("AUTH"=>true,"ERROR"=>"",'acl_list'=> $token->data->list_acl);
            }catch(Exception  $e) {
                return array("AUTH"=>false,"ERROR"=>$e->getMessage());
            }
        }

    }

    //----------------------------------------------------------
    public function validToken_logging($jwt){
        $config = new Config();
        $secretKey = $config->jwt_key;
        $secretKey = base64_decode($secretKey);

        unset($config);
        //die($secretKey);
        if ($jwt) {
            try {
                $token = JWT::decode($jwt, $secretKey, array('HS512'));
                return array("AUTH"=>true,"ERROR"=>"",'email'=> $token->data->email,'ID'=> $token->data->ID,'type'=> $token->data->type);
            }catch(Exception  $e) {
                return array("AUTH"=>false,"ERROR"=>$e->getMessage());
            }
        }

    }

    //----------------------------------------------------------
    public function resetToken($jwt_refresh,$publicKey){
        $config = new Config();
        $secretKey = $config->jwt_key;
        $secretKey = base64_decode($secretKey).$publicKey;

        unset($config);
        //die($secretKey);
        if ($jwt_refresh) {
            try {
                $token = JWT::decode($jwt_refresh, $secretKey, array('HS512'));

                $UID = $token->data->id;
                $first_name ='';// $token->data->firstname;
                $last_name = '';// $token->data->lastname;
                $primary_email =""; //$token->data->email;
                $list_acl = $token->data->list_acl;

                return $this->generateToken($UID,$first_name,$last_name,$primary_email,$list_acl);

            }catch(Exception  $e) {
                return array("AUTH"=>false,"ERROR"=>$e->getMessage());
            }
        }

    }

    //----------------------------------------------------------
    public function generateToken($UID,$first_name,$last_name,$primary_email,$list_acl){
        $config = new Config();
        $jwt_key = $config->jwt_key;
        $jwt_iss = $config->jwt_iss;
        $jwt_aud = $config->jwt_aud;
        $jwt_issuedAt = $config->jwt_issuedAt;
        $jwt_notBefore = $config->jwt_notBefore;
        $jwt_expire = $config->jwt_expire;

        $list = array();
            $key = base64_decode($jwt_key).$UID;

            $token = array(
                "iss" => $jwt_iss,
                "aud" => $jwt_aud,
                "iat" => $jwt_issuedAt,
                "nbf" => $jwt_notBefore,
                "exp" => $jwt_expire,
                "data" => array(
                    "id" => $UID,
                    //"firstname" => $first_name,
                    //"lastname" => $last_name,
                    //"email" => $primary_email,
                    "list_acl"=>$list_acl
                )
            );
            // generate jwt
        //JWT::$leeway = 2;
        $ret = JWT::encode($token, $key,'HS512');

            $list[0]["jwt"] = $ret;
            $list[0]["acl_list"] = $list_acl;

        unset($config);
        return $list;
    }


    //----------------------------------------------------------
    public function resetFreshToken($jwt_refresh,$publicKey){
        $config = new Config();
        $secretKey = $config->jwt_key;
        $secretKey = base64_decode($secretKey).$publicKey;

        unset($config);
        //die($secretKey);
        if ($jwt_refresh) {
            try {
                $token = JWT::decode($jwt_refresh, $secretKey, array('HS512'));

                $UID = $token->data->id;
                $first_name = '';//$token->data->firstname;
                $last_name =''; //$token->data->lastname;
                $primary_email =""; //$token->data->email;
                $list_acl = $token->data->list_acl;
                //$list_acl_a = (array)$list_acl;
                //print_r((array)$list_acl ); die();
                return $this->generateRefreshToken($UID,$first_name,$last_name,$primary_email,$list_acl);

            }catch(Exception  $e) {
                return array("AUTH"=>false,"ERROR"=>$e->getMessage());
            }
        }

    }

    //----------------------------------------------------------
    public function generateRefreshToken($UID,$first_name,$last_name,$primary_email,$list_acl){
        $config = new Config();
        $jwt_key = $config->jwt_key;
        $jwt_iss = $config->jwt_iss;
        $jwt_aud = $config->jwt_aud;
        $jwt_issuedAt = $config->jwt_issuedAt;
        $jwt_notBefore = $config->jwt_notBefore;
        $jwt_expire = $config->jwt_expire;

        $key = base64_decode($jwt_key).$UID;

        $refresh_token = array(
            "iss" => $jwt_iss,
            "aud" => $jwt_aud,
            "iat" => $jwt_issuedAt,
            "nbf" => $jwt_notBefore,
            "exp" => $jwt_expire +10*60,
            "data" => array(
                "id" => $UID,
                //"firstname" => $first_name,
                //"lastname" => $last_name,
               // "email" => $primary_email,
                'list_acl'=>$list_acl
            )
        );
        // generate jwt
        //JWT::$leeway = 2;
        $refresh = JWT::encode($refresh_token, $key,'HS512');

        unset($config);
        return $refresh;
    }

    //----------------------------------------------------------
    public function add_notes($notes,$contactID,$typeID=null){
        //insert record  note table
        $i=0;
        $notes_fields="";

        $notes_value="";
        if(is_array($notes) && count($notes)>0){
            foreach($notes as $v){
                $val =""; $i++;
                $temp1 = array();
                foreach($v as $key=>$item){
                    //create new array
                    if($key!="noteID") $temp1[$key] = $item;
                }

                $temp1["contactID"] = $contactID;
                $temp1["typeID"] = $typeID;
                if(empty($contactID)) unset($temp1["contactID"]);
                if(empty($typeID)) unset($temp1["typeID"]);
                //create value and key
                foreach($temp1 as $kk=>$vv){
                    $val .= empty($val) ? "" : ",";
                    $vv = $this->protect($vv);
                    $val .= "'{$vv}'";
                    //create key
                    if($i==1){
                        $notes_fields .= empty($notes_fields) ? "" : ",";
                        $notes_fields .= "{$kk}";
                    }
                }

                $notes_value .= empty($notes_value) ? "" : ",";
                $notes_value .= "({$val})";
            }

            if(!empty($notes_value)){
                $insertNotes = "INSERT INTO notes ({$notes_fields}) VALUES{$notes_value}";

                mysqli_query($this->con,$insertNotes);

                $err = mysqli_error($this->con);
                if($err) {
                    return $err;
                }else{
                    return 1;
                }
            }

        }else{
            return 1;
        }
    }
    //----------------------------------------------------------
    public function update_notes($notes,$contactID,$typeID=null){
        //update notes table
        $i=0;
        $notes_fields="";

        if(is_array($notes) && count($notes)>0){
            foreach($notes as $v){
                if(empty($v["noteID"])){
                    //add new
                    $notes_value="";
                    $val =""; $i++;
                    $temp1 = array();
                    foreach($v as $key=>$item){
                        //create new array
                        if($key!="noteID") $temp1[$key] = $item;
                    }

                    $temp1["contactID"] = $contactID;
                    $temp1["typeID"] = $typeID;
                    if(empty($contactID)) unset($temp1["contactID"]);
                    if(empty($typeID)) unset($temp1["typeID"]);
                    //create value and key for adding new
                    foreach($temp1 as $kk=>$vv){
                        $vv = $this->protect($vv);
                        $val .= empty($val) ? "" : ",";
                        $val .= "'{$vv}'";
                        //create key
                        if($i==1){
                            $notes_fields .= empty($notes_fields) ? "" : ",";
                            $notes_fields .= "{$kk}";
                        }
                    }

                    $notes_value .= empty($notes_value) ? "" : ",";
                    $notes_value .= "({$val})";


                    if(!empty($notes_value)){
                        $insertNotes = "INSERT INTO notes ({$notes_fields}) VALUES{$notes_value}";
                        mysqli_query($this->con,$insertNotes);
                        if(mysqli_error($this->con)){
                            return mysqli_error($this->con);
                        }

                    }

                }elseif(!empty($v["noteID"])){
                    $noteID ='';
                    $updateCommand = "UPDATE `notes` ";
                    $updateCommand1 ='';
                    $updateCommand2 ='';
                    foreach($v as $key=>$item){
                        if($key=='internal_flag'){
                            $v = $this->protect($item);
                            $updateCommand1="SET internal_flag = '{$v}'";
                        }

                        if($key=='note'){
                            $v = $this->protect($item);
                            $updateCommand2 .=", note = '{$v}'";
                        }

                        if($key=='description'){
                            $v = $this->protect($item);
                            $updateCommand2 .=", description = '{$v}'";
                        }

                        if($key=='noteID'){
                            $noteID = $item;
                        }
                    }

                    if(is_numeric($noteID)){
                        $updateCommand .=$updateCommand1;
                        $updateCommand .=$updateCommand2;
                        $updateCommand .=" WHERE noteID = '{$noteID}'";
                    }
                }
            }
            return 1;
        }else{
            return 1;
        }
    }

    //----------------------------------------------------------
    public function update_notes_claim($notes,$contactID,$typeID=null){
        //update notes table
        $i=0;
        $notes_fields="";
        $err = array();
        foreach($notes as $v){
            if(empty($v["noteID"])){
                //add new
                $note="";
                $notes_value="";
                $val =""; $i++;
                $temp1 = array();
                foreach($v as $key=>$item){
                    //create new array
                    if($key!="noteID") $temp1[$key] = $item;
                    if($key =="note") $note = $item;

                }

                $temp1["contactID"] = $contactID;
                $temp1["typeID"] = $typeID;
                if(empty($contactID)) unset($temp1["contactID"]);
                if(empty($typeID)) unset($temp1["typeID"]);
                //create value and key for adding new
                foreach($temp1 as $kk=>$vv){
                    $vv = $this->protect($vv);
                    $val .= empty($val) ? "" : ",";
                    $val .= "'{$vv}'";
                    //create key
                    if($i==1){
                        $notes_fields .= empty($notes_fields) ? "" : ",";
                        $notes_fields .= "{$kk}";
                    }
                }

                $notes_value .= empty($notes_value) ? "" : ",";
                $notes_value .= "({$val})";


                if(!empty($notes_value)){
                    $insertNotes = "INSERT INTO notes ({$notes_fields}) VALUES{$notes_value}";
                    //print_r($insertNotes); echo ";;;";
                    mysqli_query($this->con,$insertNotes);
                    if(mysqli_error($this->con)){
                        $err[] = array("note"=>$note);
                    }
                    // print_r($insertNotes); print_r("; ");
                }

            }elseif(!empty($v["noteID"])){
                $noteID ='';
                $updateCommand = "UPDATE `notes` ";
                $updateCommand1 ='';
                $updateCommand2 ='';
                foreach($v as $key=>$item){
                    if($key=='internal_flag'){
                        $v = $this->protect($item);
                        $updateCommand1="SET internal_flag = '{$v}'";
                    }

                    if($key=='note'){
                        $v = $this->protect($item);
                        $updateCommand2 .=", note = '{$v}'";
                    }

                    if($key=='description'){
                        $v = $this->protect($item);
                        $updateCommand2 .=", description = '{$v}'";
                    }

                    if($key=='noteID'){
                        $noteID = $item;
                    }
                }

                if(is_numeric($noteID)){
                    $updateCommand .=$updateCommand1;
                    $updateCommand .=$updateCommand2;
                    $updateCommand .=" WHERE noteID = '{$noteID}'";
                }
            }
        }
        //die();
        return $err;
    }

    //----------------------------------------------------------
    public function add_notes_new($notes,$contactID,$typeID=null){
        //insert record  note table
        $err = array();
        if(is_array($notes) && count($notes)>0){
            foreach($notes as $v){
                $val ="";
                $temp1 = array();
                $notes_value="";
                $notes_fields="";
                $note="";
                foreach($v as $key=>$item){
                    //create new array
                    if($key!="noteID") $temp1[$key] = $item;
                    if($key =="note") $note = $item;
                }

                $temp1["contactID"] = $contactID;
                $temp1["typeID"] = $typeID;

                if(empty($contactID)) unset($temp1["contactID"]);
                if(empty($typeID)) unset($temp1["typeID"]);

                //create value and key
                foreach($temp1 as $kk=>$vv){
                    $val .= empty($val) ? "" : ",";
                    $vv = $this->protect($vv);
                    $val .= "'{$vv}'";
                    //create key
                    $notes_fields .= empty($notes_fields) ? "" : ",";
                    $notes_fields .= "{$kk}";
                }

                //$notes_value .= empty($notes_value) ? "" : ",";
                $notes_value .= "({$val})";

                if(!empty($notes_value)){
                    $insertNotes = "INSERT INTO notes ({$notes_fields}) VALUES{$notes_value}";
                    //die($insertNotes);
                    mysqli_query($this->con,$insertNotes);

                    if(mysqli_error($this->con)){
                        $err[] = array("note"=>$note);
                    }
                }
            }
        }

        return $err;

    }

    //----------------------------------------------------------
    public function add_note_new($notes,$contactID,$typeID=null){
        //insert record  note table
        $err="";
        if(is_array($notes) && count($notes)>0){
            foreach($notes as $v){
                $val ="";
                $temp1 = array();
                $notes_value="";
                $notes_fields="";
                $note="";
                foreach($v as $key=>$item){
                    //create new array
                    if($key!="noteID") $temp1[$key] = $item;
                    if($key =="note") $note = $item;
                }

                $temp1["contactID"] = $contactID;
                $temp1["typeID"] = $typeID;

                if(empty($contactID)) unset($temp1["contactID"]);
                if(empty($typeID)) unset($temp1["typeID"]);

                //create value and key
                foreach($temp1 as $kk=>$vv){
                    $val .= empty($val) ? "" : ",";
                    $vv = $this->protect($vv);
                    $val .= "'{$vv}'";
                    //create key
                    $notes_fields .= empty($notes_fields) ? "" : ",";
                    $notes_fields .= "{$kk}";
                }

                //$notes_value .= empty($notes_value) ? "" : ",";
                $notes_value .= "({$val})";

                if(!empty($notes_value)){
                    $insertNotes = "INSERT INTO notes ({$notes_fields}) VALUES{$notes_value}";

                    mysqli_query($this->con,$insertNotes);

                    if(mysqli_error($this->con)){
                        $err = mysqli_error($this->con);
                    }else{
                        $err = mysqli_insert_id($this->con);
                    }
                }
            }
        }

        return $err;
    }

    //----------------------------------------------------------
    public function update_notes_new($notes,$contactID,$typeID=null){
        //update notes table
        $err = array();
        foreach($notes as $v){
            if(empty($v["noteID"])){
                //add new
                $note="";
                $notes_value="";
                $notes_fields="";
                $val ="";
                $temp1 = array();
                foreach($v as $key=>$item){
                    //create new array
                    if($key!="noteID") $temp1[$key] = $item;
                    if($key =="note") $note = $item;
                }

                if(empty($contactID)) {
                    unset($temp1["contactID"]);
                }else{
                    $temp1["contactID"] = $contactID;
                }

                if(empty($typeID)) {
                    unset($temp1["typeID"]);
                }else{
                    $temp1["typeID"] = $typeID;
                }

                //create value and key for adding new
                foreach($temp1 as $kk=>$vv){
                    $vv = $this->protect($vv);
                    $val .= empty($val) ? "" : ",";
                    $val .= "'{$vv}'";
                    //create key
                    $notes_fields .= empty($notes_fields) ? "" : ",";
                    $notes_fields .= "{$kk}";
                }

                $notes_value .= empty($notes_value) ? "" : ",";
                $notes_value .= "({$val})";


                if(!empty($notes_value)){
                    $insertNotes = "INSERT INTO notes ({$notes_fields}) VALUES{$notes_value}";
                    //print_r($insertNotes); echo ";;;";
                    mysqli_query($this->con,$insertNotes);
                    if(mysqli_error($this->con)){
                        $err[] = array("note"=>$note);
                    }
                    // print_r($insertNotes); print_r("; ");
                }

            }elseif(!empty($v["noteID"])){
                $noteID ='';
                $updateCommand = "UPDATE `notes` ";
                $updateCommand1 ='';
                $updateCommand2 ='';
                foreach($v as $key=>$item){
                    if($key=='internal_flag'){
                        $v = $this->protect($item);
                        $updateCommand1 ="SET internal_flag = '{$v}'";
                    }

                    if($key=='note'){
                        $v = $this->protect($item);
                        $updateCommand2 .=", note = '{$v}'";
                    }

                    if($key=='description'){
                        $v = $this->protect($item);
                        $updateCommand2 .=", description = '{$v}'";
                    }

                    if($key=='noteID'){
                        $noteID = $item;
                    }
                }

                if(is_numeric($noteID)){
                    $updateCommand .= $updateCommand1;
                    $updateCommand .= $updateCommand2;
                    $updateCommand .=" WHERE noteID = '{$noteID}'";
                }
            }
        }
        //die();
        return $err;
    }

    //----------------------------------------------------------
    public function err_log($type,$info,$typeID=null){
        $info1 = $this->protect($info);
        $tempDate = date("Y-m-d H:i:s");
        if(empty($typeID)) {
            $fields = "create_date,info,type";
            $values = "'{$tempDate}','{$info1}','{$type}'";
        }else{
            $fields = "create_date,info,type,typeID";
            $values = "'{$tempDate}','{$info1}','{$type}','{$typeID}'";
        }

        $query = "INSERT INTO bug({$fields}) VALUES({$values})";

        mysqli_query($this->con,$query);

    }

    //----------------------------------------------------------
    public function log($type,$info,$type_login,$ip,$typeID=null){
        $tempDate = date("Y-m-d H:i:s");

        if(empty($typeID)) {
            $fields = "create_date,info,type,type_login,ip_address";
            $values = "'{$tempDate}','{$info}','{$type}','{$type_login}','{$ip}'";
        }else{
            $fields = "create_date,info,type,typeID,type_login,ip_address";
            $values = "'{$tempDate}','{$info}','{$type}','{$typeID}','{$type_login}','{$ip}'";
        }

        $query = "INSERT INTO log({$fields}) VALUES({$values})";

        mysqli_query($this->con,$query);

    }

    //----------------------------------------------------------
    public function getLogLogin($user_agent,$type_login){
        $query ="Select ID from log
                Where info ='{$user_agent}' AND type_login='{$type_login}'";

        $result = mysqli_query($this->con,$query);

        $i=0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $i++;
            }
        }

        if($i>0) return 1;
        else return "";

    }

    //----------------------------------------------------------
    public function acl($acl_list, $value){
       $userType ="User";
       $listType="";
       switch ($value){
           case "OrderForm":

               break;
           case "ClaimForm":

               break;
           case "ContactForm":
               if(count($acl_list)>0){
                   foreach($acl_list[0] as $val){
                       if(isset($val->ContactForm)){
                           $listType .= !empty($listType) ? "," : "";
                           $listType .= $val->ContactForm;
                       }
                   }
               }

               if(!empty($listType)){
                   $p= stripos($listType,"SuperAdmin");
                   if(is_numeric($p)){
                       $userType = "SuperAdmin";
                       break;
                   }

                   $p= stripos($listType,"Admin");
                   if(is_numeric($p)){
                       $userType = "Admin";
                   }
               }

               break;
           case "InvoiceForm":

               break;
           case "ProductForm":

               break;
           case "WarrantyForm":

               break;
           default:
               $userType ="User";
       }

        return $userType;
    }

    //----------------------------------------------------------
    public function contacts($idlogin=null)
    {
        $criteria = "";
        $criteria .= !empty($criteria) ? " AND " : "";
        $criteria .= " (c.contact_inactive = 0) ";

        $search_conditions =$criteria;
        //get salesman belong to $idlogin
        $salesman_query = "Select DISTINCT c.ID
         From orders_short o
         Inner Join contact_short as c ON o.s_ID = c.ID";

        if(!empty($idlogin)){
            $search_conditions .= !empty($search_conditions) ? " AND " : "";
            $search_conditions .= " (o.b_ID = '{$idlogin}') ";
        }

        if(!empty($search_conditions)){
            $salesman_query .= " WHERE ".$search_conditions;
        }

        $query =$salesman_query;
        //

        $query .= " ORDER BY ID ASC";

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
                $list[] = $row['ID'];
            }
        }

        $i = count($list);
        //$list[$i] = Array ( "ID" => $idlogin ) ;
        $list[$i] = $idlogin ;
        return $list;
    }

    //1----------------------------------------------------------
    public function contacts_right_admin($idlogin=null,$type=null)
    {
        $sqlText = "Select c.ID From contact_short as c";
        $criteria = "";

        $criteria .= !empty($criteria) ? " AND " : "";
        $criteria .= " (c.contact_inactive = 0) ";

        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }

        $query =$sqlText;
        //

        $query .= " ORDER BY ID ASC";

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

    //----------------------------------------------------------
    public function contactsAdminLogin($idlogin=null,$type=null)
    {
        $sqlText = "Select c.ID From contact_short as c";
        $criteria = "";

        $criteria .= !empty($criteria) ? " AND " : "";
        $criteria .= " (c.contact_inactive = 0) ";

        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }

        $query =$sqlText;
        //

        $query .= " ORDER BY ID ASC";

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
                $list[] = $row['ID'];
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function products()
    {
        $sqlText = "Select ID From products_short where prod_inactive =0";
        $sqlText .= " ORDER BY ID ASC";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function orders($b_ID=null)
    {
        if(!empty($b_ID)){
            $sqlText = "Select distinct order_id as ID From orders_short
            Where b_ID = '{$b_ID}' || order_create_by ='{$b_ID}' ORDER BY order_id ASC";
        }else{
            $sqlText = "Select order_id as ID From orders_short ORDER BY order_id ASC";
        }

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function ordersForVendor($warrIDs=null,$contactID=null)
    {
        $warrID =0;
        if(count($warrIDs)>0){
            $warrID = implode(",",$warrIDs);
        }else{
            $warrID =0;
        }

        $sqlText = "Select order_id From orders_short
            Where warranty IN ({$warrID}) ORDER BY order_id ASC";
        $result = mysqli_query($this->con,$sqlText);

        $orders =$this->orders($contactID);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(!in_array($row['order_id'],$orders))
                    $orders[] = $row['order_id'];
            }
        }

        return $orders;
    }

    //----------------------------------------------------------
    public function invsForVendor($orderIDs=null,$contactID=null)
    {
        $orderID =0;
        if(count($orderIDs)>0){
            $orderID = implode(",",$orderIDs);
        }else{
            $orderID =0;
        }

        $sqlText = "Select ID From invoice_short
            Where order_id IN ({$orderID}) ORDER BY ID ASC";
        $result = mysqli_query($this->con,$sqlText);

        $invs =$this->invoices($contactID);

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(!in_array($row['ID'],$invs))
                    $invs[] = $row['ID'];
            }
        }

        return $invs;
    }

    //----------------------------------------------------------
    public function invoices($b_ID=null)
    {
        if(!empty($b_ID)){
            $sqlText = "Select DISTINCT i.ID From invoice_short as i
                    where i.customer = '{$b_ID}' || invoice_create_by '{$b_ID}' ORDER BY i.ID";
        }else{
            $sqlText = "Select ID From invoice_short ORDER BY ID ASC";
        }

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function warranties($b_ID=null,$type=null)
    {
        if(!empty($b_ID)){
            $sqlText ="Select DISTINCT w.ID
            From warranty_short as w
            where w.buyer_id = '{$b_ID}' || warranty_create_by = '{$b_ID}' ORDER BY w.ID";
        }else{
            $sqlText = "Select ID From warranty_short ORDER BY ID ASC";
        }

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function getRelativeDataforVendor($b_ID=null,$type=null)
    {
        $sqlText ="Select warranty_ID, UID,ID,customer,claim_assign
                From claims
                where UID like '%{$b_ID}%'";

        $result = mysqli_query($this->con,$sqlText);
        $IDs = array();
        $contactIDs= array();
        $warr_IDs = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $in = explode(",",$row['UID']);
                if(!in_array($b_ID,$in)) {
                    if(!in_array($row['warranty_ID'],$warr_IDs)){
                        $warr_IDs[] = $row['warranty_ID'];
                    }

                    if(!in_array($row['ID'],$IDs)){
                        $IDs[] = $row['ID'];
                    }

                    if(!in_array($row['customer'],$contactIDs)){
                        $contactIDs[] = $row['customer'];
                    }
                }

            }
        }

        return array("IDs"=>$IDs,"contactIDs"=>$contactIDs,"warr_IDs"=>$warr_IDs);
    }

    //----------------------------------------------------------
    public function warrantiesforVendor($b_ID=null,$type=null)
    {
        $sqlText ="Select warranty_ID, UID,
                From claims
                where UID like '%{$b_ID}%'";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $in = explode(",",$row['UID']);
                if(in_array($b_ID,$in)) {
                    if(!in_array($row['warranty_ID'],$list)){
                        $list[] = $row['warranty_ID'];
                    }
                }

            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function claimsCreatedBy($create_by=null)
    {
        $sqlText = "Select ID From claims";
        if(!empty($create_by) && is_numeric($create_by)){
            $sqlText .=" where create_by='{$create_by}'";
        }

        $sqlText .= " ORDER BY ID ASC";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function claimsForSales($create_by=null)
    {
        $sqlText = "Select ID From claims
        where customer='{$create_by}'";


        $sqlText .= " ORDER BY ID ASC";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function claimsForEmployee($create_by=null)
    {
        $sqlText = "Select ID From claims
        where create_by='{$create_by}' || customer='{$create_by}' || claim_assign='{$create_by}'";


        $sqlText .= " ORDER BY ID ASC";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function claimsforVendor($contactID=null,$claimIDs=null)
    {
        $sqlText = "SELECT DISTINCT ID,UID,create_by FROM claims";

        if(!empty($contactID) && is_numeric($contactID)){
            $sqlText .=" WHERE create_by='{$contactID}' || UID LIKE '%{$contactID}%'";
        }

        $sqlText .= " ORDER BY ID ASC";

        $result = mysqli_query($this->con,$sqlText);
        $IDs = array();
        //if(count($claimIDs)>0 ) $IDs = $claimIDs;

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $inArrs = explode(",",$row['UID']);
                if(in_array($contactID,$inArrs) || $contactID==$row['create_by']){
                    $IDs[] = $row['ID'];
                }
            }
        }
        return $IDs;

    }

    //----------------------------------------------------------
    public function claimsforAffiliate($warrID=null,$contactID=null)
    {
        $sqlText = "Select DISTINCT ID From claims
        where customer = '{$contactID}'";

        $sqlText .= " ORDER BY ID ASC";

        $result = mysqli_query($this->con,$sqlText);
        $list =array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function warrsContsForAffiliate($b_ID=null,$type=null)
    {
        $sqlText ="Select ID,warranty_buyer_id,warranty_salesman_id
            From warranty
            where (warranty_buyer_id ='{$b_ID}' || warranty_create_by = '{$b_ID}' ||
            warranty_escrow_id= '{$b_ID}' || warranty_mortgage_id= '{$b_ID}' ||
            warranty_seller_agent_id= '{$b_ID}' || warranty_buyer_agent_id= '{$b_ID}')
             ORDER BY ID";

        $result = mysqli_query($this->con,$sqlText);

        $warrtyIDs = array();
        $contactIDs = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(!in_array($row['ID'],$warrtyIDs)){
                    $warrtyIDs[] = $row['ID'];
                }

                if(!in_array($row['warranty_buyer_id'],$contactIDs)){
                    $contactIDs[] = $row['warranty_buyer_id'];
                }

                if(!in_array($row['warranty_salesman_id'],$contactIDs)){
                    $contactIDs[] = $row['warranty_salesman_id'];
                }
            }
        }
        return array("warrIDs"=>$warrtyIDs,"contactIDs"=>$contactIDs);
    }

    //----------------------------------------------------------
    public function contactsForAffiliate($idlogin,$contactIDs=null,$type=null)
    {
        $sqlText = "Select DISTINCT salesperson From orders
        where contact_inactive = 0 AND bill_to = '{$idlogin}'";
        $result = mysqli_query($this->con,$sqlText);

        $list=array();
        //if(count($contactIDs)> 0)  $list = $contactIDs;

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                //if(!in_array($row['salesperson'],$list))
                $list[] = $row['salesperson'];
            }
        }


        return $list;
    }

    //----------------------------------------------------------
    public function orderforAffiliate($warrIDs=null,$contactID=null)
    {
        $ordersID =$this->orders($contactID);
        return $ordersID;
    }
    //----------------------------------------------------------
    public function invsforAffiliate($orderIDs=null){
        $in = explode(",",$orderIDs);
        $sqlText = "Select distinct ID From invoice
        where order_id IN ({$in})";
        $sqlText .= " ORDER BY ID ASC";

        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];

            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function transactions_person($person=null)
    {
        $sqlText = "Select ID From claim_transaction";
        if(!empty($person) && is_numeric($person)){
            $sqlText .=" where person='{$person}'";
        }

        $sqlText .= " ORDER BY ID ASC";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['ID'];
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function sendEmail($user_agent,$type_login,$ip,$from_email=null){
        date_default_timezone_set('Etc/UTC');
        //require 'PHPMailer-5.2.27/PHPMailerAutoload.php';
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.mandrillapp.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';////Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPAuth = true;
        $mail->Username = "marketing@freedomhw.com";
        $mail->Password = "aL9zuKiIRK44voh1Jx0hsA";
        $mail->setFrom("marketing@freedomhw.com", 'Marketing Freedom');
        $mail->addAddress($from_email, 'Anh');

        $mail->Subject = 'Login from: '.$type_login;

        $tempDate = date("Y-m-d H:i:s");
        //$mail->IsHTML(true); // send as HTML
        $mail->Body    = "The ".$type_login. " is logging at: ".$tempDate.", with ip is ".$ip;

        if (!$mail->send()) {
            return $mail->ErrorInfo;
        } else {
            return 1;
        }

    }

    //----------------------------------------------------------
    public function generateEmailToAdminClaimSub($email,$link=null,$custom_email=null,$name=null,$id_tracking=null){
        date_default_timezone_set('Etc/UTC');
        //require 'PHPMailer-5.2.27/PHPMailerAutoload.php';
        $Ob_manager = new EmailAdress();
        $api_path = $Ob_manager->api_path;

        $body = "Dear Admin \n";
        $body .= $link;

        $body .= "<img src='".$api_path."/trackonline.php?id=".$id_tracking."' border='0' width='1' height='1' alt=''>";

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.mandrillapp.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';////Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPAuth = true;
        $mail->Username = "marketing@freedomhw.com";
        $mail->Password = "aL9zuKiIRK44voh1Jx0hsA";
        $mail->setFrom("marketing@freedomhw.com", 'Marketing Freedom');
        $mail->addAddress($email, 'Admin');

        $mail->Subject = 'Claim was submited by: '.$name;

        $tempDate = date("Y-m-d H:i:s");
        //$mail->Body    = $link;
        $mail->IsHTML(true); // send as HTML
        $mail->MsgHTML($body);

        if (!$mail->send()) {
            return $mail->ErrorInfo;
        } else {
            return 1;
        }

    }

    //----------------------------------------------------------
    public function check_login_type($type,$UID){

        $query ="Select ID from contact
        Where contact_type like '{$type}' AND ID ='{$UID}'";

        $rlt_acl = mysqli_query($this->con,$query);

        $list_acl = array();
        if($rlt_acl){
            while ($row = mysqli_fetch_assoc($rlt_acl)) {
                $list_acl[] = $row;
            }
        }

        if(count($list_acl) >0)return 1;
        else return "";

    }

    //------------------------------------------------------
    public function getClLimit_proIDs($IDs)
    {
        $cl_limit = "Select limits from `claim_limits`";

        $cl_limit .=" WHERE product_ID in ({$IDs})";

        $result = mysqli_query($this->con,$cl_limit);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = json_decode($row['limits'],true);
            }


        }

        //die($list);
        return json_encode($list);
    }

    //------------------------------------------------------
    public function getClLimit($ID)
    {
        $cl_limit = "Select DISTINCT limits from `claim_limits`
        left join products on products.ID = claim_limits.product_ID";

        $cl_limit .=" WHERE product_ID = '{$ID}' AND (products.prod_class='Warranty' OR products.prod_class ='A La Carte')";

        $result = mysqli_query($this->con,$cl_limit);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = json_decode($row['limits'],true);
            }
        }

        if(count($list)>0) return $list[0];
        //print_r($list); die();
        return $list;
    }

    //----------------------------------------------------------
    public function generateTokenByCus($email_admin,$ID,$type=null){
        $config = new Config();
        $jwt_key = $config->jwt_key;
        $jwt_iss = $config->jwt_iss;
        $jwt_aud = $config->jwt_aud;
        $jwt_issuedAt = $config->jwt_issuedAt;
        $jwt_notBefore = $config->jwt_notBefore;
        $jwt_expire = $config->jwt_expire;

        //get External ACL
        $list = array();
        $int_acl = array();
        $key = base64_decode($jwt_key);

        $token = array(
            "iss" => $jwt_iss,
            "aud" => $jwt_aud,
            "iat" => $jwt_issuedAt,
            "nbf" => $jwt_notBefore,
            "exp" => $jwt_expire +24*60*60,
            "data" => array(
                "email" => $email_admin,
                "ID" => $ID,
                'type'=>$type
            )
        );
        // generate jwt
        JWT::$leeway = 2;
        $ret = JWT::encode($token, $key,'HS512');

        unset($config);
        return $ret;
    }

    //----------------------------------------------------------
    public function getAssignTaskTaskIDs($taskIDs){
        $query = "select * from assign_task
        where id in ({$taskIDs})";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function close_conn(){
        mysqli_close($this->con);
    }


    //----------------------------------------------------------
    public function sendEmailToAssiged($email,$name,$task_name,$link, $subject=null,$id_tracking=null){
        date_default_timezone_set('Etc/UTC');
        //require 'PHPMailer-5.2.27/PHPMailerAutoload.php';
        $Ob_manager = new EmailAdress();
        $api_path = $Ob_manager->api_path;

        $body = "Dear ".$name. "\n";
        $body .= $link;
        $body .= "<img src='".$api_path."/trackonline.php?id=".$id_tracking."' border='0' width='1' height='1' alt=''>";

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.mandrillapp.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';////Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPAuth = true;
        $mail->Username = "marketing@freedomhw.com";
        $mail->Password = "aL9zuKiIRK44voh1Jx0hsA";
        $mail->setFrom("marketing@freedomhw.com", 'Marketing Freedom');
        $mail->addAddress($email, $name);

        $mail->Subject =  'Task '. $task_name. " Assign to ".$name;

        $tempDate = date("Y-m-d H:i:s");
        $mail->IsHTML(true); // send as HTML
        $mail->MsgHTML($body);


        if (!$mail->send()) {
            return $mail->ErrorInfo;
        } else {
            return 1;
        }

    }
    //----------------------------------------------------------
    public function sendEmailTo()
    {
        $query = "Select e.task_id,e.claim_id,e.id, e.assign_by,
            em.primary_email,
           concat(IFNULL(em.first_name,''),' ',IFNULL(em.last_name,'')) as assign_name,
           concat(IFNULL(em1.first_name,''),' ',IFNULL(em1.last_name,'')) as assign_by_name,
           at.taskName
           from `email_to_assign` as e
           left join assign_task as at on at.id = e.task_id
           left join employee_short as em on em.ID = at.assign_id
           left join employee_short as em1 on em1.ID = e.assign_by
           WHERE `create_date` < (NOW() - INTERVAL 180 MINUTE) AND `create_date` IS NOT NULL";

         $result = mysqli_query($this->con,$query);

        $Ob_manager = new EmailAdress();
        $domain_path = $Ob_manager->domain_path;

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list)>0){
                foreach ($list as $item){
                    if(!empty($item["primary_email"]) && !empty($item["assign_name"])){
                        //check email
                        $status ='';
                        if (!filter_var($item["primary_email"], FILTER_VALIDATE_EMAIL)) {
                            $status = 'Bounce';
                        }

                        $domain = substr($item["primary_email"], strpos($item["primary_email"], '@') + 1);
                        if  (!checkdnsrr($domain) !== FALSE) {
                            $status = 'Bounce';
                        }

                        //generate $id_tracking
                        $id = base64_encode($item["claim_id"]);
                        $link =$domain_path."/#ajax/claim-form.php?id=".$id;

                        $subject = 'Task '. $item["taskName"]. ' Assign to  '.$item["assign_name"];
                        $content = '<p>Dear '.$item["assign_name"].'</p>';
                        $content .='<a href="'.$link.'">Click here to access claim</a>';

                        $id_tracking = $this->insertTrackingEmail($item["primary_email"],$subject,$content,$item["assign_by"],$status);
                        if(empty($status)) {
                           $is_send = $this->mail_to($item["assign_by_name"],$item["assign_name"],$item["primary_email"],$subject,$content,$id_tracking);
                           //$is_send = $this->sendEmailToAssiged($item["primary_email"],$item["assign_name"],$item["taskName"],$link,"",$id_tracking);
                           if($is_send==1){
                               $this->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                           }
                        }

                        $delete_task = "DELETE FROM email_to_assign  WHERE id ='{$item["id"]}'";
                        mysqli_query($this->con,$delete_task);
                        //$err_temp =mysqli_error($this->con);
                        //print_r($err_temp);
                        sleep(1);
                    }

                }
            }

        }
        return $list;
    }


    //----------------------------------------------------------
    public function notifyDocExpired($email,$name,$doc_type,$past, $ex_date,$ccAdd=null){
        date_default_timezone_set('Etc/UTC');
        //require 'PHPMailer-5.2.27/PHPMailerAutoload.php';
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.mandrillapp.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';////Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPAuth = true;
        $mail->Username = "marketing@freedomhw.com";
        $mail->Password = "aL9zuKiIRK44voh1Jx0hsA";
        $mail->setFrom("marketing@freedomhw.com", 'Marketing Freedom');
        $mail->addAddress($email, $name);
        $mail->addCC($ccAdd, "Admin");

        $is = "will be";
        if($past <0) $is = "was";
        $mail->Subject =  'Document '.$is.' expired on '.$ex_date;

        $tempDate = date("Y-m-d H:i:s");
        $body    = "<p>Dear ".$name."</p>";
        $body    .='<p>The  '.$doc_type." ".$is.' expired on '.$ex_date."</p>";

        $mail->IsHTML(true); // send as HTML
        $mail->MsgHTML($body);
        if (!$mail->send()) {
            return $mail->ErrorInfo;
        } else {
            return 1;
        }

    }

    //----------------------------------------------------------
    public function requiredUpdatedCertification($ccAdd=null)
    {
        $query = "Select cd.document_type,cd.exp_date,
           concat(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) as contact_name,
           c.primary_email,DATEDIFF(cd.exp_date,NOW()) as past
           from `contact_doc` as cd
           left join contact as c on c.ID = cd.contactID
           WHERE ((DATEDIFF(cd.exp_date,NOW()) =29) OR (DATEDIFF(cd.exp_date,NOW()) =15) OR (DATEDIFF(cd.exp_date,NOW()) =0)) AND cd.exp_date IS NOT NULL
           AND cd.need_update=1 AND cd.active=1 AND c.primary_email IS NOT NULL";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list)>0){
                foreach ($list as $item){
                    if(!empty($item["primary_email"])){
                        $this->notifyDocExpired($item["primary_email"],$item["contact_name"],$item["document_type"],$item["past"], $item["exp_date"],$ccAdd);

                        sleep(1);
                    }
                }
            }

        }

    }

    //----------------------------------------------------------
    public function requiredUpdateDoc($ccAdd=null)
    {
        $query = "Select vd.document_type,vd.exp_date,DATEDIFF(vd.exp_date,NOW()) as past,
        c.name,c.email
        from `vendor_doc` as vd
        inner join vendor as v on v.ID = vd.vendorID
        left join company as c on c.ID = v.comID
        WHERE ((DATEDIFF(vd.exp_date,NOW()) =29) OR (DATEDIFF(vd.exp_date,NOW()) =15 OR (DATEDIFF(vd.exp_date,NOW()) =0))) AND vd.exp_date IS NOT NULL
        AND vd.need_update=1 AND vd.active=1 AND c.email IS NOT NULL";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

            if(count($list)>0){
                foreach ($list as $item){
                    if(!empty($item["primary_email"])){
                        $this->notifyDocExpired($item["email"],$item["name"],$item["document_type"],$item["past"], $item["exp_date"],$ccAdd);

                        sleep(1);
                    }
                }
            }

        }

    }

    //----------------------------------------------------------
    public function updateDoc($doc,$typeID=null,$Relative_ID=null){
        //get doc for db
        $doc_ids = $this->getDocIDs_TypeID($typeID,$Relative_ID);
        //update notes table
        $i=0;
        $err = array();
        $update_docs = array();
        if(is_array($doc) && count($doc)>0){
            foreach($doc as $v){
                if(!isset($v["ID"])){
                    $doc_value="";
                    $docFields="";
                    $val =""; $i++;
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
                            if($key!="ID" && $key!=$typeID){
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
                    $val .= "'{$Relative_ID}'";

                    $docFields .= empty($docFields) ? "" : ",";
                    $docFields .= "{$typeID}";

                    $doc_value .= empty($doc_value) ? "" : ",";
                    $doc_value .= "({$val})";

                    if(!empty($doc_value)){
                        if($typeID=="vendorID"){
                            $query = "INSERT INTO vendor_doc ({$docFields}) VALUES{$doc_value}";
                        }elseif($typeID=="contactID"){
                            $query = "INSERT INTO contact_doc ({$docFields}) VALUES{$doc_value}";
                        }

                        //print_r($query);
                        mysqli_query($this->con,$query);

                        if(mysqli_error($this->con)){
                            $err[] = array("doc"=>$note);
                        }
                    }
                }else{
                    if(empty($v["ID"])){
                        $doc_value="";
                        $docFields="";
                        $val =""; $i++;
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
                                if($key!="ID" && $key!=$typeID){
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
                        $val .= "'{$Relative_ID}'";

                        $docFields .= empty($docFields) ? "" : ",";
                        $docFields .= "{$typeID}";

                        $doc_value .= empty($doc_value) ? "" : ",";
                        $doc_value .= "({$val})";

                        if(!empty($doc_value)){
                            if($typeID=="vendorID"){
                                $query = "INSERT INTO vendor_doc ({$docFields}) VALUES{$doc_value}";
                            }elseif($typeID=="contactID"){
                                $query = "INSERT INTO contact_doc ({$docFields}) VALUES{$doc_value}";
                            }

                            mysqli_query($this->con,$query);

                            if(mysqli_error($this->con)){
                                $err[] = array("doc"=>$note);
                            }
                        }

                    }elseif(!empty($v["ID"])){
                        $update_docs[]=$v["ID"];
                        //update
                        if($typeID=="vendorID"){
                            $query = "UPDATE `vendor_doc` SET ";
                        }elseif($typeID=="contactID"){
                            $query = "UPDATE `contact_doc` SET ";
                        }

                        $queryTemp ="";
                        if(isset($v["start_date"])){
                            if(!empty($v["start_date"])){
                                $queryTemp .= empty($queryTemp)? "":",";
                                $queryTemp .="start_date='{$this->protect($v["start_date"])}'";
                            }
                        }

                        if(isset($v["exp_date"])){
                            if(!empty($v["exp_date"])){
                                $queryTemp .= empty($queryTemp)? "":",";
                                $queryTemp .="exp_date='{$this->protect($v["exp_date"])}'";
                            }
                        }

                        /*if(isset($v["date_entered"])){
                            if(!empty($v["date_entered"])){
                                $queryTemp .= empty($queryTemp)? "":",";
                                $queryTemp .="date_entered='{$this->protect($v["date_entered"])}'";
                            }
                        }*/


                        if(isset($v["document_type"])){
                            $queryTemp .= empty($queryTemp)? "":",";
                            $queryTemp .="document_type='{$this->protect($v["document_type"])}'";
                        }

                        if(isset($v["active"])){
                            if(empty($v["active"])) $v["active"]=0;
                            $queryTemp .= empty($queryTemp)? "":",";
                            $queryTemp .="active='{$this->protect($v["active"])}'";
                        }

                        if(isset($v["need_update"])){
                            if(empty($v["need_update"])) $v["need_update"]=0;
                            $queryTemp .= empty($queryTemp)? "":",";
                            $queryTemp .="need_update='{$this->protect($v["need_update"])}'";
                        }

                        if(isset($v["image"])){
                            $queryTemp .= empty($queryTemp)? "":",";
                            $queryTemp .="image='{$this->protect($v["image"])}'";
                        }

                        $query .=$queryTemp." WHERE ID = '{$v["ID"]}'";
                        //print_r(";;;".$query);
                         mysqli_query($this->con,$query);

                        if(mysqli_error($this->con)){
                            $err[] = array("doc"=>$v["document_type"]);
                        }
                    }
                }

            }

        }

        //delete
        $delete_docs = array_diff($doc_ids,$update_docs);

        if(count($delete_docs)>0) {
            $ids = "";
            foreach($delete_docs as $v){
                $ids .= empty($ids)? "":",";
                $ids .=$v;
            }
            $this->deleteDocIDs_TypeID($typeID,$ids);
        }

        return $err;
    }
    //------------------------------------------------
        public function getNotesByTypeID($id,$type){
            $query = "SELECT n.contactID,n.create_date,n.enter_by,
                        n.note, n.noteID,n.type,n.typeID,
                        concat(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) as enter_by_name
                        FROM  notes as n
                        left join contact as c on c.ID = n.enter_by
                    where n.typeID = '{$id}' AND n.type ='{$type}'
                    order by noteID DESC";

            $rsl = mysqli_query($this->con,$query);
    
            $notesList = array();
            if($rsl){
                while ($row = mysqli_fetch_assoc($rsl)) {
                    $notesList[] = $row;
                }
            }
            return $notesList;
        }

    //------------------------------------------------
    public function getDocIDs_TypeID($Type,$TypeID)
    {
        if($Type=="vendorID"){
            $command = "Select ID from vendor_doc
        where vendorID='{$TypeID}'";

        }elseif($Type=="contactID"){
            $command = "Select ID from contact_doc
        where contactID='{$TypeID}'";
        }
        //print_r($insertCommand);

        $rsl = mysqli_query($this->con,$command);
        $doc = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $doc[] = $row["ID"];
            }
        }

        return $doc;

    }

    ///------------------------------------------------
    public function deleteDocIDs_TypeID($Type,$IDs)
    {
        if($Type=="vendorID"){
            $command = "DELETE FROM vendor_doc WHERE ID IN ({$IDs})";

        }elseif($Type=="contactID"){
            $command = "DELETE FROM contact_doc WHERE ID IN ({$IDs})";

        }
        //print_r($command); die();

        $rsl = mysqli_query($this->con,$command);

        return 1;

    }

    //------------------------------------------------
    public function getNote($type,$ID=null){
        if(strtolower($type)=="contact"){
            $query = "SELECT n.contactID,n.create_date,n.enter_by,n.description,
                      n.note, n.noteID,n.type,n.typeID,n.internal_flag,
                      concat(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) as enter_by_name
                      FROM  notes as n
                      left join contact as c on c.ID = n.enter_by
                    where n.contactID = '{$ID}' AND n.type='{$type}'

                    order by noteID DESC";
        }else{
            $query = "SELECT n.contactID,n.create_date,n.enter_by,n.description,
                      n.note, n.noteID,n.type,n.typeID,n.internal_flag,
                      concat(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) as enter_by_name
                      FROM  notes as n
                      left join contact as c on c.ID = n.enter_by
                    where n.typeID = '{$ID}' AND n.type='{$type}' AND typeID IS NOT NULL
                    order by noteID DESC";
        }


        $rsl = mysqli_query($this->con,$query);

        $notesList = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $notesList[] = $row;
            }
        }
        return $notesList;
    }

    //------------------------------------------------
    public function getNotes($type,$ID=null){
        $query = "SELECT n.contactID,n.create_date,n.enter_by,n.description,
                      n.note, n.noteID,n.type,n.typeID,n.internal_flag,
                      concat(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) as enter_by_name
                      FROM  notes as n
                      left join contact as c on c.ID = n.enter_by
                    where contactID = '{$ID}'

                    order by noteID DESC";

        $rsl = mysqli_query($this->con,$query);

        $notesList = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $notesList[] = $row;
            }
        }
        return $notesList;
    }


    //------------------------------------------------
    public function uploadFiles($doc){
        //insert record  note table
        //active date_entered document_type exp_date need_update  start_date contactID

        $err = array();
        if(is_array($doc) && count($doc)>0){
            foreach($doc as $v){

            }

        }

        return $err;

    }
    //------------------------------------------------
    function validBase64($string)
    {
        $decoded = base64_decode($string, true);

        // Check if there is no invalid character in string
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) return false;

        // Decode the string in strict mode and send the response
        if (!base64_decode($string, true)) return false;

        // Encode and compare it to original one
        if (base64_encode($decoded) != $string) return false;

        return true;
    }

    //------------------------------------------------
    public function insertTrackingEmail($to,$title,$content,$contactID=null,$status=null,$opened=null,$claim_id=null,$attachment=null)
    {
        if(empty($status)) $status='';
        if(empty($opened)) $opened='';
        if(empty($contactID)) $contactID=0;

        $createTime = date("Y-m-d");
        $fields = "to_email,title,sent_by_id,status,opened,date_sent,content,attachment";
        $values = "'{$to}','{$title}','{$contactID}','{$status}','{$opened}','{$createTime}','{$content}','{$attachment}'";

        if(!empty($claim_id)){
            $fields = "to_email,title,sent_by_id,status,opened,date_sent,content,claim_id,attachment";
            $values = "'{$to}','{$title}','{$contactID}','{$status}','{$opened}','{$createTime}','{$content}','{$claim_id}','{$attachment}'";
        }
        $insertCommand = "INSERT INTO `track_email` ({$fields}) VALUES({$values})";
        //die($insertCommand);
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        if(is_numeric($idreturn) && $idreturn){
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }

    }

    //------------------------------------------------
    public function updateTrackEmail($id,$status=null,$opened=null)
    {
        $updateCommand = "UPDATE `track_email`
                SET status = '{$status}',
                opened = '{$opened}'
                where id ='{$id}' And opened <> 'Opened'";
                //die($updateCommand);
        $update = mysqli_query($this->con,$updateCommand);

        return $update;
    }

    //------------------------------------------------
    public function getTrackEmail_contactID($id)
    {
        $query = "select id,to_email as email,title,status,sent_by_id as contact_id,
opened,date_sent,content from `track_email` where sent_by_id = '{$id}'";
       // die($query);
        $list = array();
        $rsl = mysqli_query($this->con,$query);
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getAllByEmail($email)
    {
        $query = "select ID from `contact` where primary_email = '{$email}'";
        // die($query);
        $list = array();
        $rsl = mysqli_query($this->con,$query);
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[0]['claim'] = $this->getClaim_contactID($row['ID']);
                $list[0]['order'] = $this->getOrder_contactID($row['ID']);
                $list[0]['warranty'] = $this->getOrder_contactID($row['ID']);
                $list[0]['invoice'] = $this->getinvoice_contactID($row['ID']);
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getClaim_contactID($create_by)
    {
        $query = "select * from `claim_short` where create_by = '{$create_by}'";
        // die($query);
        $list = array();
        $rsl = mysqli_query($this->con,$query);
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getOrder_contactID($bill_to)
    {
        $query = "select order_id,balance,createTime,payment,
        total,warranty,b_name,b_ID,b_primary_city,b_primary_email,b_primary_phone,
        b_primary_state,s_name,s_ID,s_primary_city,s_primary_email,s_primary_phone,s_primary_state
        from `orders_short` where b_ID = '{$bill_to}'";
        // die($query);
        $list = array();
        $rsl = mysqli_query($this->con,$query);
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getWarranty_contactID($bill_to)
    {
        $query = "select w.warranty_order_id,w.warranty_start_date,
        w.warranty_end_date,w.warranty_address1,w.warranty_creation_date,
        w.salesman,W.ID
        from `warranty_short` as w
        left join orders_short as o on o.order_id = w.warranty_order_id
        where o.bill_to = '{$bill_to}'";
        // die($query);
        $list = array();
        $rsl = mysqli_query($this->con,$query);
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getinvoice_contactID($bill_to)
    {

        $query = "select i.order_id,i.balance,i.customer,i.invoiceid,
i.payment,i.salesperson,i.total,i.createTime,i.customer_name,i.sale_name,
i.ID
        from `invoice_short` as i
        left join orders_short as o on o.order_id = i.order_id
        where o.bill_to = '{$bill_to}'";
        // die($query);
        $list = array();
        $rsl = mysqli_query($this->con,$query);
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function mail_to($from,$receiver_name,$email,$subject,$content, $id_tracking=null,$attachment=null, $file_name=null){
        date_default_timezone_set('Etc/UTC');

        $Ob_manager = new EmailAdress();
        $api_path = $Ob_manager->api_path;
        //require 'PHPMailer-5.2.27/PHPMailerAutoload.php';
        $body = '';
        if(!empty($id_tracking)){
            $body .= "<img src='".$api_path."/trackonline.php?id=".$id_tracking."' border='0' width='1' height='1' alt=''>";
        }
        $body.=$content;
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.mandrillapp.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';////Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPAuth = true;
        $mail->Username = "marketing@freedomhw.com";
        $mail->Password = "aL9zuKiIRK44voh1Jx0hsA";
        $mail->setFrom("marketing@freedomhw.com", $from);
        $mail->addAddress($email, $receiver_name);

        $mail->Subject = $subject;

        if(!empty($attachment)){
            $mail->addAttachment($attachment, $file_name);
        }

        $tempDate = date("Y-m-d H:i:s");

        //$mail->Body    = $link;
        $mail->IsHTML(true); // send as HTML
        $mail->MsgHTML($body);

        if (!$mail->send()) {
            unset($Ob_manager);
            return $mail->ErrorInfo;
        } else {
            unset($Ob_manager);
            return 1;
        }

    }

    //------------------------------------------------
    public function getEmail_ID($id)
    {
        $query = "select id,to_email as email,title,status,sent_by_id as contact_id,
opened,date_sent,content from `track_email` where id = '{$id}'";
        // die($query);
        $list = array();
        $rsl = mysqli_query($this->con,$query);
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        if(count($list)>0){
            return $list[0];
        }else{
            return "";
        }

    }
    //------------------------------------------------
    public function addTag_importContact($tag_type,$tags){
        $err=array();
        if(!empty($tags)){
            $isFind = $this->findTag($tag_type,$tags);
            if($isFind==0){
                $isInsert = $this->insertTag($tag_type,$tags);
                if(!is_numeric($isInsert)){
                    $err[] = $isInsert;
                }
            }

        }

        return $err;
    }

    //------------------------------------------------
    public function addTag_contact($tag_type,$tags){
        $err=array();
        if(!empty($tags)){
            $find = stripos($tags,";");
            if(is_numeric($find)){
                $tagTemp = explode(";",$tags);
                foreach($tagTemp as $item){
                    $isFind = $this->findTag($tag_type,$item);
                    if($isFind==0){
                        $isInsert = $this->insertTag($tag_type,$item);
                        if(!is_numeric($isInsert)){
                            $err[] = $isInsert;
                        }
                    }
                }
            }else{
                $isFind = $this->findTag($tag_type,$tags);
                if($isFind==0){
                    $isInsert = $this->insertTag($tag_type,$tags);
                    if(!is_numeric($isInsert)){
                        $err[] = $isInsert;
                    }
                }
            }
        }

        return $err;
    }
    //------------------------------------------------
    public function addTag($tag_type,$tags){
        $err=array();
        if(!empty($tags)){
            $find = stripos($tags,",");
            if(is_numeric($find)){
                $tagTemp = explode(",",$tags);
                foreach($tagTemp as $item){
                    $isFind = $this->findTag($tag_type,$item);
                    if($isFind==0){
                        $isInsert = $this->insertTag($tag_type,$item);
                        if(!is_numeric($isInsert)){
                            $err[] = $isInsert;
                        }
                    }
                }
            }else{
                $isFind = $this->findTag($tag_type,$tags);
                if($isFind==0){
                    $isInsert = $this->insertTag($tag_type,$tags);
                    if(!is_numeric($isInsert)){
                        $err[] = $isInsert;
                    }
                }
            }
        }

        return $err;
    }
    //------------------------------------------------
    public function findTag($tag_type,$tag){
        $tagTemp = strtolower($tag);
        $tag_type = strtolower($tag_type);
        $query = "SELECT count(*) from tags
        where tag_name = '{$tagTemp}' AND tag_type = '{$tag_type}'";

        $result = mysqli_query($this->con,$query);
        $row = mysqli_fetch_row($result);

        if ($row[0] > 0) {
            return $row[0];
        }else{
            return 0;
        }
    }

    //------------------------------------------------
    public function insertTag($tag_type,$tag){

        $fields = "tag_type,tag_name";
        $values = "'{$tag_type}','{$tag}'";

        $insert = "INSERT INTO tags ({$fields}) VALUES({$values})";

        mysqli_query($this->con,$insert);
        $idret = mysqli_insert_id($this->con);

        if(is_numeric($idret) && !empty($idret)){
            return $idret;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------
    public function getTag($tag_type,$tag_name){
        $tagTemp = strtolower($tag_name);
        $tag_type = strtolower($tag_type);
        $query = "SELECT tag_name from tags
        where tag_name like '{$tagTemp}%' AND tag_type = '{$tag_type}'";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)){
                $list[] = $row['tag_name'];
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function getTag_tagType($tag_type){
        $tag_type = strtolower($tag_type);
        $query = "SELECT tag_name from tags
        where tag_type = '{$tag_type}'";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)){
                $list[] = $row['tag_name'];
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function getContactNameByID($ID) {
        $query = "SELECT  concat(IFNULL(first_name,''),' ',IFNULL(last_name,'')) as c_name
        FROM  contact
        where ID = '{$ID}'";

        $result = mysqli_query($this->con,$query);
        $list = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row['c_name'];
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function process_limit_new($a) {
        if(count($a)>0){
            for($i=0;$i<count($a);$i++){
                if(isset($a[$i])){
                    foreach($a[$i] as $key=>$value){
                        for($j=0;$j<count($a);$j++){
                            foreach($a[$j] as $key_b=>$value_b){
                                if($i<>$j && strtolower($key)==strtolower($key_b)){
                                    $a[$i][$key]=$value + $value_b;
                                    unset($a[$j][$key_b]);
                                }
                            }
                        }
                    }
                }
            }
        }

        /*for($i=0;$i<count($a);$i++){
            if(isset($a[$i])){
                if(count($a[$i])==0) unset($a[$i]);
            }

        }*/

        $list = array();

        foreach($a as $it_new){
            if(count($it_new)>0){
                $list[]=$it_new;
            }

        }

        return $list;
    }


    //------------------------------------------------------------------
    public function process_transaction($a) {
        if(count($a)>0){
            for($i=0;$i<count($a);$i++){
                $j=$i+1;
                for($j;$j<count($a);$j++){
                    if(isset($a[$i])&& isset($a[$j])){
                        if(count($a[$i])>0 && count($a[$j])>0){
                            if(isset($a[$i]['name']) && isset($a[$j]['name'])){
                                if($a[$i]['name'] ==$a[$j]['name']){
                                    $a[$i]['claim']=$a[$i]['claim'] + $a[$j]['claim'];
                                    unset($a[$j]['name']);
                                    unset($a[$j]['claim']);
                                    unset($a[$j]['current']);
                                    unset($a[$j]['person']);
                                    unset($a[$j]['datetime']);
                                    unset($a[$j]['original']);
                                    unset($a[$j]['available']);
                                    unset($a[$j]['transaction']);
                                }
                            }
                        }
                    }

                }
            }
        }

        //for($i=0;$i<count($a);$i++){
            //if(count($a[$i])==0) unset($a[$i]);
        //}

        return $a;
    }

    //------------------------------------------------------------------
   public function internalACLTemp($type,$group_name,$role){
       $roleConvert = strtolower($role);

       $query ="Select i.ID, i.unit, i.level, i.acl_rules from acl_rules as i
        Where i.unit = '{$type}' AND i.level = '{$roleConvert}' LIMIT 1";

       $rlt_int_acl = mysqli_query($this->con,$query);

       $int_acl = array();
       if($rlt_int_acl){
           while ($row = mysqli_fetch_assoc($rlt_int_acl)) {
               //$int_acl["ID"] = $row["ID"];
               $int_acl["unit"] = $row["unit"];
               $int_acl["level"] = $row["level"];
               $int_acl["acl_rules"] = json_decode($row["acl_rules"],true);
               $int_acl["group_name"] = $group_name;
           }
       }

       return $int_acl;
   }

    //------------------------------------------------------------------
    public function internalACL($type,$group_name,$role,$acl){
        $int_acl = array();
        $int_acl["unit"] = $type;
        $int_acl["level"] = $role;
        $int_acl["acl_rules"] = json_decode($acl,true);
        $int_acl["group_name"] = $group_name;

        return $int_acl;
    }

    //------------------------------------------------------------------
    public function internalACLSupperAdmin($type,$group_name,$role){
        $roleConvert = strtolower($role);

        $query ="SELECT ID, unit, level, acl_rules FROM acl_rules
        WHERE unit = '{$type}' AND level = '{$roleConvert}' LIMIT 1";

        $rlt_int_acl = mysqli_query($this->con,$query);

        $int_acl = array();
        if($rlt_int_acl){
            while ($row = mysqli_fetch_assoc($rlt_int_acl)) {
                //$int_acl["ID"] = $row["ID"];
                $int_acl["unit"] = $row["unit"];
                $int_acl["level"] = $row["level"];
                $int_acl["acl_rules"] = json_decode($row["acl_rules"],true);
                $int_acl["group_name"] = $group_name;
            }
        }

        return $int_acl;
    }

    //----------------------------------------------------------
    public function emailToBuyer()
    {
        $query = "Select *
           from `payment_schedule_short`
           WHERE (`invoiceDate` < NOW() + INTERVAL 6 DAY) AND (`invoiceDate` > NOW()-INTERVAL 1 DAY) AND (`invoiceDate` IS NOT NULL) AND inactive=0 AND invoiceID IS NULL";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }


            if(count($list)>0){
                foreach ($list as $item){
                    //
                    $now = date_create(date("Y-m-d"));
                    $invoice_date=date_create($item['invoiceDate']);
                    $diff=date_diff($now,$invoice_date);
                    $during = $diff->format("%a");
                    //send mail if $during =5 or =1
                    if((4< $during && $during <6) || (0< $during && $during <2)){

                        if(!empty($item["primary_email"]) && !empty($item["buyer"])){
                            //check email
                            $status ='';
                            if (!filter_var($item["primary_email"], FILTER_VALIDATE_EMAIL)) {
                                $status = 'Bounce';
                            }

                            $domain = substr($item["primary_email"], strpos($item["primary_email"], '@') + 1);
                            if  (!checkdnsrr($domain) !== FALSE) {
                                $status = 'Bounce';
                            }

                            //generate $id_tracking
                            $subject = 'The term of payment on '.$item['invoiceDate'];
                            $content = '<p>Dear '.$item["buyer"].'</p>';
                            //admin id
                            $admin_id = 202;
                            $id_tracking = $this->insertTrackingEmail($item["primary_email"],$subject,$content,$admin_id,$status);

                            if(empty($status)) {
                                //send from system
                                $adminName = "System admin";
                                $is_send = $this->mail_to($adminName,$item["buyer"],$item["primary_email"],$subject,$content,$id_tracking);
                                //$is_send = $this->sendEmailToAssiged($item["primary_email"],$item["assign_name"],$item["taskName"],$link,"",$id_tracking);
                                if($is_send==1){
                                    $this->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                                }
                            }

                            //$err_temp =mysqli_error($this->con);
                            //print_r($err_temp);
                            sleep(1);
                        }
                    }
                    //
                }
            }

        }
        return $list;
    }

    //----------------------------------------------------------
    public function notSales($ID){
        $query ="Select contact_type from contact
                Where ID ='{$ID}' AND contact_type NOT LIKE '%Sales%'  limit 1";

        $result = mysqli_query($this->con,$query);

        $flag_notSales =false;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $flag_notSales=true;
            }
        }

       return $flag_notSales;
    }

    //----------------------------------------------------------
    public function updateStatusPayment($orderID){
        $query = "UPDATE `payment_schedule`
                SET inactive = 1
                where orderID ='{$orderID}' AND (invoiceID IS NULL || invoiceID='')";

        mysqli_query($this->con,$query);
    }

    //----------------------------------------------------------
    public function orders_contact($b_ID=null)
    {
        $sqlText = "Select DISTINCT order_id, balance, b_name, payment, total, order_title From orders_short
            Where bill_to = '{$b_ID}' || order_create_by = '{$b_ID}' ORDER BY order_id ASC";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function warranties_contact($b_ID=null)
    {
        $sqlText = "Select DISTINCT ID,warranty_order_id,buyer,salesman,
        warranty_start_date,warranty_end_date,warranty_address1,warranty_type
            From warranty_short
        where buyer_id = '{$b_ID}'";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['warranty_type'] = $this->getProductNameprodIDs($row['warranty_type']);
                $list[] = $row;
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function warrantiesForClaim($b_ID=null)
    {
        $sqlText = "Select DISTINCT ID,warranty_order_id,buyer,salesman,
        warranty_start_date,warranty_end_date,warranty_address1
            From warranty_short
        where buyer_id = '{$b_ID}' || warranty_create_by ='{$b_ID}'";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $isWarranty = $this->warrantyHasClassWarranty($row['warranty_order_id']);
                if($isWarranty>0){
                    $list[] = $row;
                }
            }
        }

        return $list;
    }
    //----------------------------------------------------------
    public function warrantyHasClassWarranty($order_id=null)
    {
       // $orders = explode(",",$order_id);

        $sqlText = "Select products_ordered
            From orders
        where order_id IN ({$order_id}) AND
        JSON_SEARCH(products_ordered, 'all', 'Warranty') IS NOT NULL";
        //die($sqlText);
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }

        if(count($list)>0){
            return 1;
        }else{
            return 0;
        }

    }

    //----------------------------------------------------------
    public function claims_contact($b_ID=null)
    {
        $sqlText = "Select DISTINCT customer_name as contact_name,create_by , ID,create_by_name,
            transactionID,paid,start_date,status,warranty_serial_number,warranty_ID,
            create_by,customer,UID,claim_assign,note
            From claim_short
        where create_by = '{$b_ID}' || customer='{$b_ID}' ||
        claim_assign='{$b_ID}'";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                //$arr_in = explode(",",$row['UID']);
                /*if($row['create_by']==$b_ID || $row['customer']==$b_ID ||
                    $row['claim_assign']==$b_ID){

                    $list[] = $row;
                }*/
                $list[] = $row;

            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function getContact_ID($ID)
    {
        $sqlText = "Select concat(IFNULL(first_name,''),' ',IFNULL(last_name,'')) as customer_name,
                    primary_email
            From contact
        where ID = '{$ID}' AND ID <>'' AND ID IS NOT NULL";
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

    //----------------------------------------------------------
    public function getVendor_ID($IDs)
    {
        $query = "SELECT concat(IFNULL(first_name,''),' ',IFNULL(last_name,'')) as c_name,primary_email,
        primary_street_address1,primary_city,primary_state,primary_postal_code
        FROM contact
        where ID IN ({$IDs})";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }


    //------------------------------------------------
    public function getInvoice_OrderID($orderID,$billToID)
    {
        $query = "select invoiceID from `payment_schedule_short` where orderID = '{$orderID}'
        AND billToID = '{$billToID}' limit 1";
        // die($query);
        $list = '';
        $rsl = mysqli_query($this->con,$query);
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list = $row['invoiceID'];
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function getSubs_orderID($order_id)
    {
        $query = "Select subscription from orders
        where order_id='{$order_id}'";
        $result = mysqli_query($this->con,$query);

        $res = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $res = json_decode($row['subscription'],true);
            }
        }
        return $res;
    }

    //------------------------------------------------------------
    public function  isDate($date){
        $format='Y-m-d';
        $d = DateTime::createFromFormat($format, $date);
        if($d && $d->format($format) === $date){
            return $date;
        }else{
            return date("Y-m-d");
        }
    }

    //------------------------------------------------------------
    public function  is_Date($date){
        $m_temp = explode(" ",$date);
        if(isset($m_temp[1])){
            $format = 'Y-m-d H:i:s';
        }else{
            $format = 'Y-m-d';
        }

        $d = DateTime::createFromFormat($format, $date);
        if($d && $d->format($format) === $date){
            return $date;
        }else{
            return "";
        }
    }

    //------------------------------------------------------------
    public function processACL($rsl){
        if(count($rsl)>1){
            //acl
            $temp1 = $rsl[0]['acl_rules'][0];
            $ClaimForm_0 = array();
            if(isset($temp1['ClaimForm'])){
                $ClaimForm_0 = $temp1['ClaimForm'] ;
            }
            $OrderForm_0 =array();
            if(isset($temp1['OrderForm'])){
                $OrderForm_0 =$temp1['OrderForm'] ;
            }
            $ContactForm_0 =array();
            if(isset($temp1['ContactForm'] )){
                $ContactForm_0 =$temp1['ContactForm'] ;
            }
            $InvoiceForm_0 =array();
            if(isset($temp1['InvoiceForm'])){
                $InvoiceForm_0 =$temp1['InvoiceForm'] ;
            }
            $ProductForm_0 =array();
            if(isset($temp1['ProductForm'])){
                $ProductForm_0 =$temp1['ProductForm'] ;
            }
            $WarrantyForm_0 =array();
            if(isset($temp1['WarrantyForm'])){
                $WarrantyForm_0 =$temp1['WarrantyForm'];
            }

            $CompanyForm_0 =array();
            if(isset($temp1['CompanyForm'])){
                $CompanyForm_0 =$temp1['CompanyForm'];
            }

            $Dashboard_0 =array();
            if(isset($temp1['Dashboard'])){
                $Dashboard_0 =$temp1['Dashboard'];
            }

            $GroupForm_0 =array();
            if(isset($temp1['GroupForm'])){
                $GroupForm_0 =$temp1['GroupForm'];
            }

            $Navigation_0 =array();
            if(isset($temp1['Navigation'])){
                $Navigation_0 =$temp1['Navigation'];
            }
            //--
            $BillingTemplateForm_0 =array();
            if(isset($temp1['BillingTemplateForm'])){
                $BillingTemplateForm_0 =$temp1['BillingTemplateForm'];
            }

            $DiscountForm_0 =array();
            if(isset($temp1['DiscountForm'])){
                $DiscountForm_0 =$temp1['DiscountForm'];
            }

            $SettingForm_0 =array();
            if(isset($temp1['SettingForm'])){
                $SettingForm_0 =$temp1['SettingForm'];
            }

            $PermissionForm_0 =array();
            if(isset($temp1['PermissionForm'])){
                $PermissionForm_0 =$temp1['PermissionForm'];
            }

            $TaskForm_0 =array();
            if(isset($temp1['TaskForm'])){
                $TaskForm_0 =$temp1['TaskForm'];
            }

            $ControlListForm_0 =array();
            if(isset($temp1['ControlListForm'])){
                $ControlListForm_0 =$temp1['ControlListForm'];
            }
            //level
            $level_0= $rsl[0]['level'] ;
            $unit_0= $rsl[0]['unit'] ;
            for($i=1;$i<count($rsl);$i++){
                $temp2 = $rsl[$i]['acl_rules'][0];
                $level_i= $rsl[$i]['level'] ;

                if($level_0!="Admin"){
                    if($level_0=='Manager'){
                        if($level_i =='Admin') $level_0=$level_i;
                    }else if($level_0=='Leader'){
                        if($level_i =='Admin' || $level_i =='Manager') $level_0=$level_i;
                    }else if($level_0=='User'){
                        $level_0=$level_i;
                    }
                }
                //process claim acl
                $ClaimForm_i =array();
                if(isset($temp2['ClaimForm'])){
                    $ClaimForm_i =$temp2['ClaimForm'] ;
                }

                if(count($ClaimForm_0)>0){
                    $diff = array_diff_key($ClaimForm_i,$ClaimForm_0);
                    if(count($diff) >0) {
                        $ClaimForm_0 = array_merge($ClaimForm_0,$diff);
                    }

                    foreach($ClaimForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ClaimForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $ClaimForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $ClaimForm_0[$k0] = $v0;
                    }
                }else{
                    $ClaimForm_0 = $ClaimForm_i;
                }

                //process OrderForm acl
                $OrderForm_i =array();
                if(isset($temp2['OrderForm'])){
                    $OrderForm_i =$temp2['OrderForm'];
                }

                if(count($OrderForm_0)>0){
                    $diff = array_diff_key($OrderForm_i,$OrderForm_0);
                    if(count($diff) >0) {
                        $OrderForm_0 = array_merge($OrderForm_0,$diff);
                    }

                    foreach($OrderForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($OrderForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $OrderForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $OrderForm_0[$k0] = $v0;
                    }
                }else{
                    $OrderForm_0 =$OrderForm_i;
                }

                //process ContactForm acl
                $ContactForm_i =array();
                if(isset($temp2['ContactForm'])){
                    $ContactForm_i =$temp2['ContactForm'] ;
                }

                if($ContactForm_0>0){
                    $diff = array_diff_key($ContactForm_i,$ContactForm_0);
                    if(count($diff) >0) {
                        $ContactForm_0 = array_merge($ContactForm_0,$diff);
                    }

                    foreach($ContactForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ContactForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $ContactForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $ContactForm_0[$k0] = $v0;
                    }
                }else{
                    $ContactForm_0 =  $ContactForm_i;
                }



                //process InvoiceForm acl
                $InvoiceForm_i =array();
                if(isset($temp2['InvoiceForm'])){
                    $InvoiceForm_i =$temp2['InvoiceForm'] ;
                }
                if(count($InvoiceForm_0)>0){
                    $diff = array_diff_key($InvoiceForm_i,$InvoiceForm_0);
                    if(count($diff) >0) {
                        $InvoiceForm_0 = array_merge($InvoiceForm_0,$diff);
                    }

                    foreach($InvoiceForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($InvoiceForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $InvoiceForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $InvoiceForm_0[$k0] = $v0;
                    }
                }else{
                    $InvoiceForm_0 = $InvoiceForm_i;
                }

                //process ProductForm acl
                $ProductForm_i =array();
                if(isset($temp2['ProductForm'])){
                    $ProductForm_i =$temp2['ProductForm'] ;
                }

                if(count($ProductForm_0)>0){
                    $diff = array_diff_key($ProductForm_i,$ProductForm_0);
                    if(count($diff) >0) {
                        $ProductForm_0 = array_merge($ProductForm_0,$diff);
                    }

                    foreach($ProductForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ProductForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $ProductForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $ProductForm_0[$k0] = $v0;
                    }
                }else{
                    $ProductForm_0 = $ProductForm_i;
                }
                //process WarrantyForm acl
                $WarrantyForm_i =array();
                if(isset($temp2['WarrantyForm'])){
                    $WarrantyForm_i =$temp2['WarrantyForm'] ;
                }

                if(count($WarrantyForm_0)>0){
                    $diff = array_diff_key($WarrantyForm_i,$WarrantyForm_0);
                    if(count($diff) >0) {
                        $WarrantyForm_0 = array_merge($WarrantyForm_0,$diff);
                    }

                    foreach($WarrantyForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($WarrantyForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $WarrantyForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $WarrantyForm_0[$k0] = $v0;
                    }
                }else{
                    $WarrantyForm_0 = $WarrantyForm_i;
                }


                //process CompanyForm acl
                $CompanyForm_i =array();
                if(isset($temp2['CompanyForm'])){
                    $CompanyForm_i =$temp2['CompanyForm'] ;
                }

                if(count($CompanyForm_0)>0){
                    $diff = array_diff_key($CompanyForm_i,$CompanyForm_0);
                    if(count($diff) >0) {
                        $CompanyForm_0 = array_merge($CompanyForm_0,$diff);
                    }

                    foreach($CompanyForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($CompanyForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $CompanyForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $CompanyForm_0[$k0] = $v0;
                    }
                }else{
                    $CompanyForm_0 = $CompanyForm_i;
                }
                //process Dashboard acl
                $Dashboard_i =array();
                if(isset($temp2['Dashboard'])){
                    $Dashboard_i =$temp2['Dashboard'] ;
                }

                if(count($Dashboard_0)>0){
                    $diff = array_diff_key($Dashboard_i,$Dashboard_0);
                    if(count($diff) >0) {
                        $Dashboard_0 = array_merge($Dashboard_0,$diff);
                    }

                    foreach($Dashboard_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($Dashboard_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $Dashboard_i[$k0][$v0_k];
                                }
                            }
                        }
                        $Dashboard_0[$k0] = $v0;
                    }
                }else{
                    $Dashboard_0 = $Dashboard_i;
                }

                //process GroupForm acl
                $GroupForm_i =array();
                if(isset($temp2['GroupForm'])){
                    $GroupForm_i =$temp2['GroupForm'] ;
                }

                if(count($GroupForm_0)>0){
                    $diff = array_diff_key($GroupForm_i,$GroupForm_0);
                    if(count($diff) >0) {
                        $GroupForm_0 = array_merge($GroupForm_0,$diff);
                    }

                    foreach($GroupForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($GroupForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $GroupForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $GroupForm_0[$k0] = $v0;
                    }
                }else{
                    $GroupForm_0 = $GroupForm_i;
                }

                //process Navigation acl
                $Navigation_i =array();
                if(isset($temp2['Navigation'])){
                    $Navigation_i =$temp2['Navigation'] ;
                }

                if(count($Navigation_0)>0){
                    $diff = array_diff_key($Navigation_i,$Navigation_0);
                    if(count($diff) >0) {
                        $Navigation_0 = array_merge($Navigation_0,$diff);
                    }

                    foreach($Navigation_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($Navigation_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $Navigation_i[$k0][$v0_k];
                                }
                            }
                        }
                        $Navigation_0[$k0] = $v0;
                    }
                }else{
                    $Navigation_0 = $Navigation_i;
                }
                //--
                //process BillingTemplateForm acl
                $BillingTemplateForm_i =array();
                if(isset($temp2['BillingTemplateForm'])){
                    $BillingTemplateForm_i =$temp2['BillingTemplateForm'] ;
                }

                if(count($BillingTemplateForm_0)>0){
                    $diff = array_diff_key($BillingTemplateForm_i,$BillingTemplateForm_0);
                    if(count($diff) >0) {
                        $BillingTemplateForm_0 = array_merge($BillingTemplateForm_0,$diff);
                    }

                    foreach($BillingTemplateForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($BillingTemplateForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $BillingTemplateForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $BillingTemplateForm_0[$k0] = $v0;
                    }
                }else{
                    $BillingTemplateForm_0 = $BillingTemplateForm_i;
                }

                //process DiscountForm acl
                $DiscountForm_i =array();
                if(isset($temp2['DiscountForm'])){
                    $DiscountForm_i =$temp2['DiscountForm'] ;
                }

                if(count($DiscountForm_0)>0){
                    $diff = array_diff_key($DiscountForm_i,$DiscountForm_0);
                    if(count($diff) >0) {
                        $DiscountForm_0 = array_merge($DiscountForm_0,$diff);
                    }

                    foreach($DiscountForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($DiscountForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $DiscountForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $DiscountForm_0[$k0] = $v0;
                    }
                }else{
                    $DiscountForm_0 = $DiscountForm_i;
                }

                //process SettingForm acl
                $SettingForm_i =array();
                if(isset($temp2['SettingForm'])){
                    $SettingForm_i =$temp2['SettingForm'] ;
                }

                if(count($SettingForm_0)>0){
                    $diff = array_diff_key($SettingForm_i,$SettingForm_0);
                    if(count($diff) >0) {
                        $SettingForm_0 = array_merge($SettingForm_0,$diff);
                    }

                    foreach($SettingForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($SettingForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $SettingForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $SettingForm_0[$k0] = $v0;
                    }
                }else{
                    $SettingForm_0 = $SettingForm_i;
                }

                //process PermissionForm acl
                $PermissionForm_i =array();
                if(isset($temp2['PermissionForm'])){
                    $PermissionForm_i =$temp2['PermissionForm'] ;
                }

                if(count($PermissionForm_0)>0){
                    $diff = array_diff_key($PermissionForm_i,$PermissionForm_0);
                    if(count($diff) >0) {
                        $PermissionForm_0 = array_merge($PermissionForm_0,$diff);
                    }

                    foreach($PermissionForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($PermissionForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $PermissionForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $PermissionForm_0[$k0] = $v0;
                    }
                }else{
                    $PermissionForm_0 = $PermissionForm_i;
                }

                //process TaskForm acl
                $TaskForm_i =array();
                if(isset($temp2['TaskForm'])){
                    $TaskForm_i =$temp2['TaskForm'] ;
                }

                if(count($TaskForm_0)>0){
                    $diff = array_diff_key($TaskForm_i,$TaskForm_0);
                    if(count($diff) >0) {
                        $TaskForm_0 = array_merge($TaskForm_0,$diff);
                    }

                    foreach($TaskForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($TaskForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $TaskForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $TaskForm_0[$k0] = $v0;
                    }
                }else{
                    $TaskForm_0 = $TaskForm_i;
                }

                //process ControlListForm acl
                $ControlListForm_i =array();
                if(isset($temp2['ControlListForm'])){
                    $ControlListForm_i =$temp2['ControlListForm'] ;
                }

                if(count($ControlListForm_0)>0){
                    $diff = array_diff_key($ControlListForm_i,$ControlListForm_0);
                    if(count($diff) >0) {
                        $ControlListForm_0 = array_merge($ControlListForm_0,$diff);
                    }

                    foreach($ControlListForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ControlListForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $v0_v || $ControlListForm_i[$k0][$v0_k];
                                }
                            }
                        }
                        $ControlListForm_0[$k0] = $v0;
                    }
                }else{
                    $ControlListForm_0 = $ControlListForm_i;
                }
                //--

            }
            //print_r($ClaimForm_0); die();
            $rtn=  Array
                (
                    Array
                    (
                        'unit' => $unit_0,
                        'level' => $level_0,
                        'acl_rules' => Array
                        (
                            Array
                            (
                                'ClaimForm' => $ClaimForm_0,
                                'OrderForm' => $OrderForm_0,
                                'ContactForm' => $ContactForm_0,
                                'InvoiceForm' => $InvoiceForm_0,
                                'ProductForm' => $ProductForm_0,
                                'WarrantyForm' => $WarrantyForm_0,
                                'Dashboard' => $Dashboard_0,
                                'GroupForm' => $GroupForm_0,
                                'Navigation' => $Navigation_0,
                                'CompanyForm'=>$CompanyForm_0,
                                'BillingTemplateForm'=>$BillingTemplateForm_0,
                                'DiscountForm'=>$DiscountForm_0,
                                'SettingForm'=>$SettingForm_0,
                                'PermissionForm'=>$PermissionForm_0,
                                'TaskForm'=>$TaskForm_0,
                                'ControlListForm'=>$ControlListForm_0
                            )
                        ),
                        'group_name' => ''
                    )
                );

            if(count($ClaimForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ClaimForm']);
            }

            if(count($OrderForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['OrderForm']);
            }

            if(count($ContactForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ContactForm']);
            }

            if(count($InvoiceForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['InvoiceForm']);
            }

            if(count($ProductForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ProductForm']);
            }

            if(count($WarrantyForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['WarrantyForm']);
            }

            if(count($CompanyForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['CompanyForm']);
            }

            if(count($Dashboard_0)<1) {
                unset($rtn[0]['acl_rules'][0]['Dashboard']);
            }

            if(count($GroupForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['GroupForm']);
            }

            if(count($Navigation_0)<1) {
                unset($rtn[0]['acl_rules'][0]['Navigation']);
            }
            //--
            if(count($BillingTemplateForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['BillingTemplateForm']);
            }
            if(count($DiscountForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['DiscountForm']);
            }
            if(count($SettingForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['SettingForm']);
            }
            if(count($PermissionForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['PermissionForm']);
            }

            if(count($TaskForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['TaskForm']);
            }

            if(count($ControlListForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ControlListForm']);
            }

            return $rtn;

        }else{
            return array();
        }
    }

    //----------------------------------------------------------
    public function processACL_again($rsl){
        if(count($rsl)>1){
            //acl
            $temp1 = $rsl[0]['acl_rules'][0];
            $ClaimForm_0 = array();
            if(isset($temp1['ClaimForm'])){
                $ClaimForm_0 = $temp1['ClaimForm'] ;
            }
            $OrderForm_0 =array();
            if(isset($temp1['OrderForm'])){
                $OrderForm_0 =$temp1['OrderForm'] ;
            }
            $ContactForm_0 =array();
            if(isset($temp1['ContactForm'] )){
                $ContactForm_0 =$temp1['ContactForm'] ;
            }
            $InvoiceForm_0 =array();
            if(isset($temp1['InvoiceForm'])){
                $InvoiceForm_0 =$temp1['InvoiceForm'] ;
            }
            $ProductForm_0 =array();
            if(isset($temp1['ProductForm'])){
                $ProductForm_0 =$temp1['ProductForm'] ;
            }
            $WarrantyForm_0 =array();
            if(isset($temp1['WarrantyForm'])){
                $WarrantyForm_0 =$temp1['WarrantyForm'];
            }

            $CompanyForm_0 =array();
            if(isset($temp1['CompanyForm'])){
                $CompanyForm_0 =$temp1['CompanyForm'];
            }

            $Dashboard_0 =array();
            if(isset($temp1['Dashboard'])){
                $Dashboard_0 =$temp1['Dashboard'];
            }

            $GroupForm_0 =array();
            if(isset($temp1['GroupForm'])){
                $GroupForm_0 =$temp1['GroupForm'];
            }

            $Navigation_0 =array();
            if(isset($temp1['Navigation'])){
                $Navigation_0 =$temp1['Navigation'];
            }
            //--
            $BillingTemplateForm_0 =array();
            if(isset($temp1['BillingTemplateForm'])){
                $BillingTemplateForm_0 =$temp1['BillingTemplateForm'];
            }

            $DiscountForm_0 =array();
            if(isset($temp1['DiscountForm'])){
                $DiscountForm_0 =$temp1['DiscountForm'];
            }

            $SettingForm_0 =array();
            if(isset($temp1['SettingForm'])){
                $SettingForm_0 =$temp1['SettingForm'];
            }

            $PermissionForm_0 =array();
            if(isset($temp1['PermissionForm'])){
                $PermissionForm_0 =$temp1['PermissionForm'];
            }

            $TaskForm_0 =array();
            if(isset($temp1['TaskForm'])){
                $TaskForm_0 =$temp1['TaskForm'];
            }

            $ControlListForm_0 =array();
            if(isset($temp1['ControlListForm'])){
                $ControlListForm_0 =$temp1['ControlListForm'];
            }
            //level
            $level_0= $rsl[0]['level'] ;
            $unit_0= $rsl[0]['unit'] ;
            for($i=1;$i<count($rsl);$i++){
                $temp2 = $rsl[$i]['acl_rules'][0];
                $level_0= $rsl[$i]['level'] ;
                //process claim acl
                $ClaimForm_i =array();
                if(isset($temp2['ClaimForm'])){
                    $ClaimForm_i =$temp2['ClaimForm'] ;
                }

                if(count($ClaimForm_0)>0 && count($ClaimForm_i)>0){
                    $diff = array_diff_key($ClaimForm_i,$ClaimForm_0);
                    if(count($diff) >0) {
                        $ClaimForm_0 = array_merge($ClaimForm_0,$diff);
                    }

                    foreach($ClaimForm_0 as $k0=>$v0){
                        //print_r($k0);echo "=";
                        //print_r($ClaimForm_i[$k0]);echo "-----";
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ClaimForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $ClaimForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $ClaimForm_0[$k0] = $v0;
                    }
                }else{
                    $ClaimForm_0 = $ClaimForm_i;
                }

                //process OrderForm acl
                $OrderForm_i =array();
                if(isset($temp2['OrderForm'])){
                    $OrderForm_i =$temp2['OrderForm'];
                }

                if(count($OrderForm_0)>0 && count($OrderForm_i) >0){
                    $diff = array_diff_key($OrderForm_i,$OrderForm_0);
                    if(count($diff) >0) {
                        $OrderForm_0 = array_merge($OrderForm_0,$diff);
                    }

                    foreach($OrderForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($OrderForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $OrderForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $OrderForm_0[$k0] = $v0;
                    }
                }else{
                    $OrderForm_0 =$OrderForm_i;
                }

                //process ContactForm acl
                $ContactForm_i =array();
                if(isset($temp2['ContactForm'])){
                    $ContactForm_i =$temp2['ContactForm'] ;
                }

                if($ContactForm_0>0 && count($ContactForm_i) >0){
                    $diff = array_diff_key($ContactForm_i,$ContactForm_0);
                    if(count($diff) >0) {
                        $ContactForm_0 = array_merge($ContactForm_0,$diff);
                    }

                    foreach($ContactForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ContactForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $ContactForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $ContactForm_0[$k0] = $v0;
                    }
                }else{
                    $ContactForm_0 =  $ContactForm_i;
                }

                //process InvoiceForm acl
                $InvoiceForm_i =array();
                if(isset($temp2['InvoiceForm'])){
                    $InvoiceForm_i =$temp2['InvoiceForm'] ;
                }
                if(count($InvoiceForm_0)>0 && count($InvoiceForm_i) >0){
                    $diff = array_diff_key($InvoiceForm_i,$InvoiceForm_0);
                    if(count($diff) >0) {
                        $InvoiceForm_0 = array_merge($InvoiceForm_0,$diff);
                    }

                    foreach($InvoiceForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($InvoiceForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $InvoiceForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $InvoiceForm_0[$k0] = $v0;
                    }
                }else{
                    $InvoiceForm_0 = $InvoiceForm_i;
                }

                //process ProductForm acl
                $ProductForm_i =array();
                if(isset($temp2['ProductForm'])){
                    $ProductForm_i =$temp2['ProductForm'] ;
                }

                if(count($ProductForm_0)>0 &&count($ProductForm_i) >0){
                    $diff = array_diff_key($ProductForm_i,$ProductForm_0);
                    if(count($diff) >0) {
                        $ProductForm_0 = array_merge($ProductForm_0,$diff);
                    }

                    foreach($ProductForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ProductForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $ProductForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $ProductForm_0[$k0] = $v0;
                    }
                }else{
                    $ProductForm_0 = $ProductForm_i;
                }
                //process WarrantyForm acl
                $WarrantyForm_i =array();
                if(isset($temp2['WarrantyForm'])){
                    $WarrantyForm_i =$temp2['WarrantyForm'] ;
                }

                if(count($WarrantyForm_0)>0 &&count($WarrantyForm_i) >0){
                    $diff = array_diff_key($WarrantyForm_i,$WarrantyForm_0);
                    if(count($diff) >0) {
                        $WarrantyForm_0 = array_merge($WarrantyForm_0,$diff);
                    }

                    foreach($WarrantyForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($WarrantyForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $WarrantyForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $WarrantyForm_0[$k0] = $v0;
                    }
                }else{
                    $WarrantyForm_0 = $WarrantyForm_i;
                }

                //process CompanyForm acl
                $CompanyForm_i =array();
                if(isset($temp2['CompanyForm'])){
                    $CompanyForm_i =$temp2['CompanyForm'] ;
                }

                if(count($CompanyForm_0)>0 && count($CompanyForm_i) >0){
                    $diff = array_diff_key($CompanyForm_i,$CompanyForm_0);
                    if(count($diff) >0) {
                        $CompanyForm_0 = array_merge($CompanyForm_0,$diff);
                    }

                    foreach($CompanyForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($CompanyForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $CompanyForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $CompanyForm_0[$k0] = $v0;
                    }
                }else{
                    $CompanyForm_0 = $CompanyForm_i;
                }

                //process Dashboard acl
                $Dashboard_i =array();
                if(isset($temp2['Dashboard'])){
                    $Dashboard_i =$temp2['Dashboard'] ;
                }

                if(count($Dashboard_0)>0 && count($Dashboard_i) >0){
                    $diff = array_diff_key($Dashboard_i,$Dashboard_0);
                    if(count($diff) >0) {
                        $Dashboard_0 = array_merge($Dashboard_0,$diff);
                    }

                    foreach($Dashboard_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($Dashboard_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $Dashboard_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $Dashboard_0[$k0] = $v0;
                    }
                }else{
                    $Dashboard_0 = $Dashboard_i;
                }

                //process GROUPorm acl
                $GroupForm_i =array();
                if(isset($temp2['GroupForm'])){
                    $GroupForm_i =$temp2['GroupForm'] ;
                }

                if(count($GroupForm_0)>0 && count($GroupForm_i) >0){
                    $diff = array_diff_key($GroupForm_i,$GroupForm_0);
                    if(count($diff) >0) {
                        $GroupForm_0 = array_merge($GroupForm_0,$diff);
                    }

                    foreach($GroupForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($GroupForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $GroupForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $GroupForm_0[$k0] = $v0;
                    }
                }else{
                    $GroupForm_0 = $GroupForm_i;
                }

                //process Navigation acl
                $Navigation_i =array();
                if(isset($temp2['Navigation'])){
                    $Navigation_i =$temp2['Navigation'] ;
                }

                if(count($Navigation_0)>0 && count($Navigation_i) >0){
                    $diff = array_diff_key($Navigation_i,$Navigation_0);
                    if(count($diff) >0) {
                        $Navigation_0 = array_merge($Navigation_0,$diff);
                    }

                    foreach($Navigation_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($Navigation_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $Navigation_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $Navigation_0[$k0] = $v0;
                    }
                }else{
                    $Navigation_0 = $Navigation_i;
                }
                //--
                //process BillingTemplateForm acl
                $BillingTemplateForm_i =array();
                if(isset($temp2['BillingTemplateForm'])){
                    $BillingTemplateForm_i =$temp2['BillingTemplateForm'] ;
                }

                if(count($BillingTemplateForm_0)>0 && count($BillingTemplateForm_i) >0){
                    $diff = array_diff_key($BillingTemplateForm_i,$BillingTemplateForm_0);
                    if(count($diff) >0) {
                        $BillingTemplateForm_0 = array_merge($BillingTemplateForm_0,$diff);
                    }

                    foreach($BillingTemplateForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($BillingTemplateForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $BillingTemplateForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $BillingTemplateForm_0[$k0] = $v0;
                    }
                }else{
                    $BillingTemplateForm_0 = $BillingTemplateForm_i;
                }

                //process DiscountForm acl
                $DiscountForm_i =array();
                if(isset($temp2['DiscountForm'])){
                    $DiscountForm_i =$temp2['DiscountForm'] ;
                }

                if(count($DiscountForm_0)>0 && count($DiscountForm_i) >0){
                    $diff = array_diff_key($DiscountForm_i,$DiscountForm_0);
                    if(count($diff) >0) {
                        $DiscountForm_0 = array_merge($DiscountForm_0,$diff);
                    }

                    foreach($DiscountForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($DiscountForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $DiscountForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $DiscountForm_0[$k0] = $v0;
                    }
                }else{
                    $DiscountForm_0 = $DiscountForm_i;
                }

                //process SettingForm acl
                $SettingForm_i =array();
                if(isset($temp2['SettingForm'])){
                    $SettingForm_i =$temp2['SettingForm'] ;
                }

                if(count($SettingForm_0)>0 && count($SettingForm_i) >0){
                    $diff = array_diff_key($SettingForm_i,$SettingForm_0);
                    if(count($diff) >0) {
                        $SettingForm_0 = array_merge($SettingForm_0,$diff);
                    }

                    foreach($SettingForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($SettingForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $SettingForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $SettingForm_0[$k0] = $v0;
                    }
                }else{
                    $SettingForm_0 = $SettingForm_i;
                }

                //process PermissionForm acl
                $PermissionForm_i =array();
                if(isset($temp2['PermissionForm'])){
                    $PermissionForm_i =$temp2['PermissionForm'] ;
                }

                if(count($PermissionForm_0)>0 && count($PermissionForm_i) >0){
                    $diff = array_diff_key($PermissionForm_i,$PermissionForm_0);
                    if(count($diff) >0) {
                        $PermissionForm_0 = array_merge($PermissionForm_0,$diff);
                    }

                    foreach($PermissionForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($PermissionForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $PermissionForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $PermissionForm_0[$k0] = $v0;
                    }
                }else{
                    $PermissionForm_0 = $PermissionForm_i;
                }

                //process TaskForm acl
                $TaskForm_i =array();
                if(isset($temp2['TaskForm'])){
                    $TaskForm_i =$temp2['TaskForm'] ;
                }

                if(count($TaskForm_0)>0 && count($TaskForm_i) >0){
                    $diff = array_diff_key($TaskForm_i,$TaskForm_0);
                    if(count($diff) >0) {
                        $TaskForm_0 = array_merge($TaskForm_0,$diff);
                    }

                    foreach($TaskForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($TaskForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $TaskForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $TaskForm_0[$k0] = $v0;
                    }
                }else{
                    $TaskForm_0 = $TaskForm_i;
                }

                //process ControlListForm acl
                $ControlListForm_i =array();
                if(isset($temp2['ControlListForm'])){
                    $ControlListForm_i =$temp2['ControlListForm'] ;
                }

                if(count($ControlListForm_0)>0 && count($ControlListForm_i) >0){
                    $diff = array_diff_key($ControlListForm_i,$ControlListForm_0);
                    if(count($diff) >0) {
                        $ControlListForm_0 = array_merge($ControlListForm_0,$diff);
                    }

                    foreach($ControlListForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ControlListForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $ControlListForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $ControlListForm_0[$k0] = $v0;
                    }
                }else{
                    $ControlListForm_0 = $ControlListForm_i;
                }

                //--
            }

            $rtn=  Array
            (
                Array
                (
                    'unit' => $unit_0,
                    'level' => $level_0,
                    'acl_rules' => Array
                    (
                        Array
                        (
                            'ClaimForm' => $ClaimForm_0,
                            'OrderForm' => $OrderForm_0,
                            'ContactForm' => $ContactForm_0,
                            'InvoiceForm' => $InvoiceForm_0,
                            'ProductForm' => $ProductForm_0,
                            'WarrantyForm' => $WarrantyForm_0,
                            'Dashboard' => $Dashboard_0,
                            'GroupForm' => $GroupForm_0,
                            'Navigation'=>$Navigation_0,
                            'CompanyForm'=>$CompanyForm_0,
                            'BillingTemplateForm'=>$BillingTemplateForm_0,
                            'DiscountForm'=>$DiscountForm_0,
                            'SettingForm'=>$SettingForm_0,
                            'PermissionForm'=>$PermissionForm_0,
                            'TaskForm'=>$TaskForm_0,
                            'ControlListForm'=>$ControlListForm_0
                        )
                    ),
                    'group_name' => ''
                )
            );

            if(count($ClaimForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ClaimForm']);
            }

            if(count($OrderForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['OrderForm']);
            }

            if(count($ContactForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ContactForm']);
            }

            if(count($InvoiceForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['InvoiceForm']);
            }

            if(count($ProductForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ProductForm']);
            }

            if(count($WarrantyForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['WarrantyForm']);
            }

            if(count($CompanyForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['CompanyForm']);
            }

            if(count($Dashboard_0)<1) {
                unset($rtn[0]['acl_rules'][0]['Dashboard']);
            }

            if(count($GroupForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['GroupForm']);
            }

            if(count($Navigation_0)<1) {
                unset($rtn[0]['acl_rules'][0]['Navigation']);
            }

            if(count($BillingTemplateForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['BillingTemplateForm']);
            }
            if(count($DiscountForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['DiscountForm']);
            }
            if(count($SettingForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['SettingForm']);
            }
            if(count($PermissionForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['PermissionForm']);
            }

            if(count($TaskForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['TaskForm']);
            }
            if(count($ControlListForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ControlListForm']);
            }
            return $rtn;

        }else{
            return array();
        }
    }

    //----------------------------------------------------------
    public function processACL_supperadmin($rsl){
        $acl = array();
        if(count($rsl)<1) return $acl;

        foreach($rsl[0] as $k=>$v){
            $value = array();
            foreach($v as $k0=>$v0){
                foreach($v0 as $v0_k=>$v0_v){
                    $v0[$v0_k] = true;
                }
                $value[$k0] = $v0;
            }

            $acl[$k] =$value;
        }

        return $acl;
    }

    //----------------------------------------------------------
    public function email_register($email,$from_email,$from_name,$from_id,$domain_path=null)
    {
        if(!empty($email)){
            //check email
            $status ='';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $status = 'Bounce';
            }

            $domain = substr($email, strpos($email, '@') + 1);
            if  (!checkdnsrr($domain) !== FALSE) {
                $status = 'Bounce';
            }

            //generate $id_tracking
            $id = base64_encode($email);
            $subject = 'Welcome';
            $html =$domain_path."/register-form.php?YW5oQGF0MXRzLmNvbQ=".$id;
            $content = '<p>Dear Customer</p>
                        <p>Thanks for registering with us. As a member of CRM.</p>
                        <p>Click the link to SUBSCRIBE </p>'.$html;
            //$content .='<a href="'.$html.'"></a>';

            //admin id
            $id_tracking = $this->insertTrackingEmail($email,$subject,$content,$from_id,$status);

            if(empty($status)) {
                //send from system
                $is_send = $this->mail_to($from_name,"Customer",$email,$subject,$content,$id_tracking);

                if($is_send==1){
                    $this->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                }
            }

        }
    }

    //----------------------------------------------------------
    public function validateDiscountFields($discount_name,$start_date,$stop_date=null,$nerver_expired=null)
    {
        $error = false;
        $errorMsg = "";

        if($nerver_expired==0){
            if(!$error && empty($stop_date)){
                $error = true;
                $errorMsg = "Stop date is required.";
            }
        }

        if(!$error && empty($discount_name)){
            $error = true;
            $errorMsg = "Name is required.";
        }

        if(!$error && empty($start_date)){
            $error = true;
            $errorMsg = "Start date is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //----------------------------------------------------------
    public function loginGenerateToken1($UID,$first_name,$last_name,$primary_email,$type=null){
        $config = new Config();
        $jwt_key = $config->jwt_key;
        $jwt_iss = $config->jwt_iss;
        $jwt_aud = $config->jwt_aud;
        $jwt_issuedAt = $config->jwt_issuedAt;
        $jwt_notBefore = $config->jwt_notBefore;
        $jwt_expire = $config->jwt_expire;

        //get External ACL
        $list = array();
        $list[0]["admin"] = '';

        $acl_list=array();
        $intACL =array();
        //get user's role
        $roles_Q ="Select department, group_name, role,acl from groups
        Where department ='{$type}' AND JSON_SEARCH(`users`, 'all', '{$UID}') IS NOT NULL";

        $rlt_role = mysqli_query($this->con,$roles_Q);

        if($rlt_role){
            while ($row = mysqli_fetch_assoc($rlt_role)) {
                if(empty($row['acl']) || is_null($row['acl']) || $row['acl']=='null'){
                    $intACL[]= $this->internalACLTemp($type,$row['group_name'],$row['role']);
                }else{
                    $intACL[]= $this->internalACL($type,$row['group_name'],$row['role'],$row['acl']);
                }

                if($row['role']=='Admin' || $type=='SystemAdmin'){
                    $list[0]["admin"] = 1;
                }
            }
        }

        //user isn't belong yo any groups
        if(count($intACL)==0){
            if($type!="SystemAdmin"){
                $intACL[]= $this->internalACLTemp($type,'','User');

            }else{
                $intACL[]= $this->internalACLSupperAdmin('SystemAdmin','','Admin');
                $list[0]["admin"] = 1;
            }

        }else if(count($intACL)>0){
            if(count($intACL)>1){
                $intACL =$this->processACL($intACL);

            }

            if($type!="SystemAdmin"){
                $acl_process[0]= $this->internalACLTemp($type,'','User');
            }else{
                $acl_process[0]= $this->internalACLTemp($type,'','Admin');
            }

            $acl_process[1] = $intACL[0];
            $intACL =  $this->processACL_again($acl_process);

        }
        //get internal ACL
        $acl_list['int_acl']= $intACL;
        //print_r($intACL); die();
        $key = base64_decode($jwt_key).$UID;

        $token = array(
            "iss" => $jwt_iss,
            "aud" => $jwt_aud,
            "iat" => $jwt_issuedAt,
            "nbf" => $jwt_notBefore,
            "exp" => $jwt_expire,
            "data" => array(
                "id" => $UID,
                //"firstname" => $first_name,
                //"lastname" => $last_name,
                //"email" => $primary_email,
                "list_acl"=> array() //$acl_list[$type]
            )
        );
        // generate jwt
        //JWT::$leeway = 2;
        $ret = JWT::encode($token, $key,'HS512');

        $list[0]["jwt"] = $ret;
        $list[0]["acl_list"] = $acl_list;

        //refresh token
        $refresh_token = array(
            "iss" => $jwt_iss,
            "aud" => $jwt_aud,
            "iat" => $jwt_issuedAt,
            "nbf" => $jwt_notBefore,
            "exp" => $jwt_expire +10*60,
            "data" => array(
                "id" => $UID,
                //"firstname" => $first_name,
                //"lastname" => $last_name,
                // "email" => $primary_email,
                "list_acl"=>array() //$acl_list[$type]
            )
        );

        $refresh = JWT::encode($refresh_token, $key,'HS512');
        $list[0]["jwt_refresh"] = $refresh;
        unset($config);
        return $list;
    }


    //----------------------------------------------------------
    public function addNewDiscount($discount_name,$apply_to,
                                   $start_date,$stop_date,$excludesive_offer,$active,$nerver_expired){

        $discount_name = strtoupper($discount_name);
        $d = date('Y-m-d H:i:s');
        $temp = explode(" ",$d);
        $temp1 = explode("-",$temp[0]);
        $temp2 = explode(":",$temp[1]);
        $discount_code = $temp1[0].$temp1[1].$temp1[2].$temp2[0].$temp2[1].$temp2[2];
            $date = date("Y-m-d");
            if($nerver_expired==1){
                $fields = "discount_name,apply_to,start_date,
             excludesive_offer,active,nerver_expired,discount_code";
                $values = "'{$discount_name}','{$apply_to}','{$start_date}',
               '{$excludesive_offer}','{$active}','{$nerver_expired}','{$discount_code}'";
            }else{
                $fields = "discount_name,apply_to,start_date,
            stop_date,excludesive_offer,active,nerver_expired,discount_code";
                $values = "'{$discount_name}','{$apply_to}','{$start_date}',
            '{$stop_date}','{$excludesive_offer}','{$active}','{$nerver_expired}','{$discount_code}'";
            }

            $query= "INSERT INTO discount({$fields}) VALUES({$values})";
            //verify name
            $selectCommand ="SELECT COUNT(*) AS NUM FROM discount WHERE  `discount_name` ='{$discount_name}' AND
            active = 1";
            if ($this->checkExists($selectCommand)) return "Name is using";

            //insert record contact table
            mysqli_query($this->con,$query);
            $idreturn = mysqli_insert_id($this->con);

            if(is_numeric($idreturn) && $idreturn){
                return $idreturn;
            }else{
                return mysqli_error($this->con);
            }

    }


    //----------------------------------------------------------
    public function updateDiscount($id,$discount_name,$apply_to,
                                   $start_date,$stop_date,$excludesive_offer,$active,$nerver_expired){

        $discount_name = strtoupper($discount_name);
        //verify name
        $selectCommand ="SELECT COUNT(*) AS NUM FROM discount WHERE  `discount_name` ='{$discount_name}' AND
        `id` <> $id AND active = 1";
        if ($this->checkExists($selectCommand)) return "Name is using";

        $d = date('Y-m-d H:i:s');
        $temp = explode(" ",$d);
        $temp1 = explode("-",$temp[0]);
        $temp2 = explode(":",$temp[1]);
        $discount_code = $temp1[0].$temp1[1].$temp1[2].$temp2[0].$temp2[1].$temp2[2];

        $date = date("Y-m-d");

        $query="UPDATE `discount`
                SET discount_name = '{$discount_name}',
                apply_to = '{$apply_to}',
                start_date = '{$start_date}',
                excludesive_offer = '{$excludesive_offer}',
                active = '{$active}',
                discount_code ='{$discount_code}',
                nerver_expired = '{$nerver_expired}'";

        $where =" WHERE id = '{$id}'";

        if($nerver_expired==0){
            $stopdate =",stop_date = '{$stop_date}'";
            $query .=$stopdate.$where;
        }else{
            $query .=$where;
        }

        //update record contact table
        $update = mysqli_query($this->con,$query);

        if($update){
            return 1;
        }else{
            return mysqli_error($this->con);
        }

    }

    //----------------------------------------------------------
    public function getDiscount_id($id){
        $query ="SELECT * FROM discount WHERE id ='{$id}' LIMIT 1";
        $result = mysqli_query($this->con,$query);
        $list = "";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['apply_to']= json_decode($row['apply_to'],true);

                $list = $row;
            }

        }
        return $list;
    }

    //----------------------------------------------------------
    public function getDiscount_discount($discount_code){
        $query ="SELECT * FROM discount WHERE discount_code ='{$discount_code}' LIMIT 1";
        $result = mysqli_query($this->con,$query);
        $list = "";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['apply_to']= json_decode($row['apply_to'],true);

                $list = $row;
            }

        }
        return $list;
    }

    //----------------------------------------------------------
    public function getDiscount_name($discount_name){
        $discount_name = strtoupper($discount_name);
        if(!empty($discount_name)) $discount_name= trim($discount_name);
        $query ="SELECT * FROM discount WHERE discount_name ='{$discount_name}' AND
        active = 1 LIMIT 1";
        $result = mysqli_query($this->con,$query);
        $list = "";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['apply_to']= json_decode($row['apply_to'],true);

                $list = $row;
            }

        }
        return $list;
    }
    //----------------------------------------------------------
    public function getDiscounts(){
        //DATEDIFF(cd.exp_date,NOW()) =29
        $query ="SELECT * FROM discount WHERE
         IF(`nerver_expired`=1,(DATEDIFF(`start_date`,NOW())<1) && (`start_date` IS NOT NULL),
         (DATEDIFF(`start_date`,NOW())<1) && (`start_date` IS NOT NULL) && (DATEDIFF(`stop_date`,NOW())>-1) && (`stop_date` IS NOT NULL))
         AND active =1";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['apply_to']= json_decode($row['apply_to'],true);

                $list[] = $row;
            }

        }
        return $list;
    }

    //------------------------------------------------------------------
    public function nextDate($date_st,$period,$unit){
        $date_st = date_create($date_st);
        $next_date_temp = $date_st->format('Y-m-d');
        $next_date =  date_create($next_date_temp);
        $paymentPeriod=$period;
        $date_interval = $paymentPeriod." ".$unit;
        date_add($next_date, date_interval_create_from_date_string($date_interval));

        return date_format($next_date, 'Y-m-d');

    }

    //----------------------------------------------------------
    public function sendEmailToAccountant($from_name=null,$create_by=null,$subject=null,$content=null)
    {
        $query = "Select primary_email,
           concat(IFNULL(first_name,''),' ',IFNULL(last_name,'')) as accountant_name
           from contact where contact_type like '%accountant%'";

        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $to = $row['primary_email'];
                $receiver = $row['accountant_name'];
                //check email
                $status ='';
                if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                    $status = 'Bounce';
                }

                $domain = substr($to, strpos($to, '@') + 1);
                if  (!checkdnsrr($domain) !== FALSE) {
                    $status = 'Bounce';
                }

                $id_tracking = $this->insertTrackingEmail($to,$subject,$content,$create_by,$status);

                if(empty($status)){
                    $is_send =  $this->mail_to($from_name,$receiver,$to,$subject,$content,$id_tracking);
                    if($is_send==1){
                        $this->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                    }
                }
            }
        }
        return true;
    }

    //------------------------------------------------------------
    public function test1($type,$UID){
        //get user's role
        $roles_Q ="Select department, group_name, role,acl from groups
        Where department ='{$type}' AND JSON_SEARCH(`users`, 'all', '{$UID}') IS NOT NULL";

        $rlt_role = mysqli_query($this->con,$roles_Q);
        $intACL = array();
        if($rlt_role){
            while ($row = mysqli_fetch_assoc($rlt_role)) {
                if(empty($row['acl']) || is_null($row['acl']) || $row['acl']=='null'){
                    $intACL[]= $this->internalACLTemp($type,$row['group_name'],$row['role']);
                }else{
                    $intACL[]= $this->internalACL($type,$row['group_name'],$row['role'],$row['acl']);
                }

                if($row['role']=='Admin'){
                    $list[0]["admin"] = 1;
                }
            }
        }

        if(count($intACL)==0){
            $intACL[]= $this->internalACLTemp($type,'','User');
        }

        return $intACL;

    }

    //------------------------------------------------------------
    public function getProds_orderID($orderID){
        $query ="SELECT o.products_ordered,o.total,o.order_title,x.invoiceDate
               FROM  orders_short as o
               left join(
            Select p.orderID ,MAX(p.invoiceDate) as invoiceDate
            from payment_schedule_short as p
            Group by p.orderID
        )x on o.order_id = x.orderID
               WHERE order_id ='{$orderID}'";

        $sqlExecuting = mysqli_query($this->con,$query);

        $prods = array();
        if($sqlExecuting){
            while ($row = mysqli_fetch_assoc($sqlExecuting)) {
                $row['products_ordered'] =json_decode($row['products_ordered'],true);
                $prods[]= $row;
            }
        }

        return $prods;
    }

    //------------------------------------------------------------
    public function getCharityNameByID($charityID){
        $query ="SELECT name
               FROM  charity_of_choice
               WHERE ID ='{$charityID}' limit 1";

        $sqlExecuting = mysqli_query($this->con,$query);

        $name='';
        if($sqlExecuting){
            while ($row = mysqli_fetch_assoc($sqlExecuting)) {
                $name= $row['name'];
            }
        }

        return $name;
    }

    //----------------------------------------------------------
    public function getContactID_affil_titleID($AID)
    {
        $sqlText = "Select UID
            From affil_title
            where AID = '{$AID}'";
        $result = mysqli_query($this->con,$sqlText);

        $UID="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $UID = $row["UID"];
            }
        }

        return $UID;
    }

    //----------------------------------------------------------
    public function getContactID_affil_agentID($AID)
    {
        $sqlText = "Select UID
            From affil_agent
            where AID = '{$AID}'";
        $result = mysqli_query($this->con,$sqlText);

        $UID="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $UID = $row["UID"];
            }
        }

        return $UID;
    }

    //----------------------------------------------------------
    public function getContactID_mortgageID($AID)
    {
        $sqlText = "Select UID
            From affil_mortgage
            where AID = '{$AID}'";
        $result = mysqli_query($this->con,$sqlText);

        $UID="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $UID = $row["UID"];
            }
        }

        return $UID;
    }

    //----------------------------------------------------------
    public function getContactID_salemanID($SID)
    {
        $sqlText = "Select UID
            From salesman
            where SID = '{$SID}'";
        $result = mysqli_query($this->con,$sqlText);

        $UID="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $UID = $row["UID"];
            }
        }

        return $UID;
    }

    //----------------------------------------------------------
    public function existingSalesman($ContactID){
        $query = "SELECT count(*) from salesman
          WHERE UID ='{$ContactID}'";
        return $this->checkExisting($query);
    }

    //----------------------------------------------------------
    public function existingAff($ContactID){
        $query = "SELECT count(*) from affiliate
          WHERE UID ='{$ContactID}'";
        return $this->checkExisting($query);

    }

    //----------------------------------------------------------
    public function updateAff($ContactID,$aff_type,$active)
    {
        $info = $this->existingAff($ContactID);
        if(is_numeric($info)){
            $updateaffiliate = "UPDATE `affiliate`
                SET active = '{$active}',
                    aff_type = '{$aff_type}'
                WHERE UID = '{$ContactID}'";
            mysqli_query($this->con,$updateaffiliate);
        }else{
            if($active==1){
                $fields = "aff_type,UID,active";
                $values = "'{$aff_type}','{$ContactID}',1";
                $insert = "INSERT INTO affiliate({$fields}) VALUES({$values})";
                mysqli_query($this->con,$insert);
            }

        }

    }
    //----------------------------------------------------------
    public function updateSalesman($ContactID,$active,$area=null)
    {
        if(empty($area)) $area='[]';
        $info = $this->existingSalesman($ContactID);
        if(is_numeric($info)){
            $updateaffiliate = "UPDATE `salesman`
                SET active = '{$active}',
                     SID ='{$ContactID}',
                     area ='{$area}'
                WHERE UID = '{$ContactID}'";
            mysqli_query($this->con,$updateaffiliate);
        }else{
            if($active==1){
                $fields = "SID,UID,active,area";
                $values = "'{$ContactID}','{$ContactID}','{$active}','{$area}'";
                $insert = "INSERT INTO salesman({$fields}) VALUES({$values})";
                mysqli_query($this->con,$insert);
            }
        }

    }

    //------------------------------------------------------------
    public function getProductNameprodIDs($prodIDs)
    {
        $sqlText = "Select ID,prod_name From products
        where ID  IN ({$prodIDs})";
        $result = mysqli_query($this->con,$sqlText);

        $list=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    function US_format_phone($phone){
        $numbers = explode("\n",$phone);
       return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number). "\n";

    }

    //------------------------------------------------------------
    function format_phone($s){
        if(preg_match('/[a-zA-Z]{1}/', $s)){
            return '';
        }

        $result="";
        $rx = "/
        (1)?\D*     # optional country code
        (\d{3})?\D* # optional area code
        (\d{3})\D*  # first three
        (\d{4})     # last four
        (?:\D+|$)   # extension delimiter or EOL
        (\d*)       # optional extension
    /x";
        preg_match($rx, $s, $matches);
        if(!isset($matches[0])) return '';

        $country = $matches[1];
        $area = $matches[2];
        $three = $matches[3];
        $four = $matches[4];
        $ext = $matches[5];

        $out = "$three-$four";
        if(!empty($area)) $out = "($area) $out";
        if(!empty($country)) $out = "+$country-$out";
        if(!empty($ext)) $out .= "x$ext";

        return $out;
    }

    //------------------------------------------------------------
    function format_phone_new($n){
        $n= preg_replace('/[^0-9]+/', '', $n);
        $m =$n;
        $country=0;
        $length =0;
        if(strlen($n)>9) {
            $m = substr($n, -10);
            $length = strlen($n)-10;
            $country = substr($n,0,$length);
        }

        $phone = $this->format_phone($m);
        //if(!empty($country) && $country!=1) $phone ='+'.$country.'-'.$phone;
        return $phone;
    }



    //----------------------------------------------------------
    public function ConvertCtactTypeLeadPolicy_ID($ContactID)
    {
        $sqlText = "Select contact_type
            From contact
        where ID = '{$ContactID}'";
        //die($sqlText);
        $result = mysqli_query($this->con,$sqlText);

        $contact_type = '';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $contact_type = $row['contact_type'];
            }
        }
        $contacType_temp='';
        $p= stripos($contact_type,"Lead");
        if(is_numeric($p)){
            $contact_type=str_replace("Lead","",$contact_type);
            $contact_type=str_replace("Policy Holder","",$contact_type);
            $temp = explode(",",$contact_type);
            foreach ($temp as $item){
                if(!empty($item)){
                    $contacType_temp .=empty($contacType_temp)?'':',';
                    $contacType_temp .=$item;
                }

            }
            $contacType_temp .=empty($contacType_temp)?'':',';
            $contacType_temp .="Policy Holder";

            //update Contact Type
            $updateCommand = "UPDATE `contact`
                SET contact_type = '{$contacType_temp}'
                WHERE ID = '{$ContactID}'";
            $update = mysqli_query($this->con,$updateCommand);
            if($update){
                return 1;
            }else{
                return mysqli_error($this->con);
            }
        }else{
            return 'Contact Type is not Lead';
        }


        return $contactType;
    }

    //------------------------------------------------
    public function get_ProductByID($ID) {
        $query = "SELECT * FROM  products WHERE ID = '{$ID}'";
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
    public function getProdsByOrderID($orderID){
        $query ="SELECT products_ordered
               FROM  orders
               WHERE order_id ='{$orderID}'";

        $sqlExecuting = mysqli_query($this->con,$query);

        $prods = array();
        if($sqlExecuting){
            while ($row = mysqli_fetch_assoc($sqlExecuting)) {
                $row['products_ordered'] =json_decode($row['products_ordered'],true);
                $prods[]= $row;
            }
        }

        return $prods;
    }

    //------------------------------------------------------------
    public function getInv_orderID($orderID){
        $query ="SELECT createTime,invoiceid
                FROM invoice
               WHERE order_id ='{$orderID}'";

        $sqlExecuting = mysqli_query($this->con,$query);

        $invoices = array();
        if($sqlExecuting){
            while ($row = mysqli_fetch_assoc($sqlExecuting)) {
                $invoices[]= $row;
            }
        }

        return $invoices;
    }

    //------------------------------------------------
    public function getContactType_name($tag_name,$tag_type) {
        $query = "SELECT * FROM  tags WHERE tag_name like '%{$tag_name}%' AND tag_type='{$tag_type}'";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function getTags($tag_type) {
        $query = "SELECT * FROM  tags WHERE tag_type='{$tag_type}'";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function getpostalCodeFromContact() {
        $query = "SELECT DISTINCT primary_postal_code FROM  contact";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['primary_postal_code'];
            }
        }
        return $list;
    }
    //filter user
    //------------------------------------------------
    public function userChild($parent_id){
        $query = "SELECT DISTINCT parent_id,users
                  FROM groups_short
                   where  parent_id like '%{$parent_id}%'";

        $this->users_child= array();
        $this->parent_repeat= array();
        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $expl = explode(",",$row['parent_id']);
                if(in_array($parent_id, $expl)){
                    $row['users'] =json_decode($row['users'],true);
                    $arr_p =array();
                    $arr_p[] =$parent_id;
                    $this->parent_repeat=array_merge($this->parent_repeat,$arr_p);

                    $this->users_child=array_merge($this->users_child,$row['users']);
                    $this->userChild_child($row['users']);
                }
                //----------------------------
            }
        }

        return $this->users_child;
    }

    //------------------------------------------------
    public function userChild_child($parentID) {
        foreach($parentID as $item){
            if(!in_array($item, $this->parent_repeat)){
                $query = "SELECT DISTINCT parent_id,users
                  FROM groups_short
                   where  parent_id like '%{$item}%'";
                //print_r($query); echo "----";

                $result = mysqli_query($this->con,$query);
                if($result){
                    while ($row = mysqli_fetch_assoc($result)) {
                        $expl = explode(",",$row['parent_id']);
                        if(in_array($item, $expl)){
                            $row['users'] =json_decode($row['users'],true);
                            $arr_p =array();
                            $arr_p[] =$item;
                            $this->parent_repeat=array_merge($this->parent_repeat,$arr_p);
                            $this->users_child=array_merge($this->users_child,$row['users']);
                            $this->userChild_child($row['users']);
                        }

                    }
                }
            }
            //

        }

    }

    //------------------------------------------------
    public function parentManageUsers($parent_id){
        $query = "SELECT DISTINCT parent_id,users
                  FROM groups_short
                   where  parent_id like '%{$parent_id}%'";

        $list =array();
        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $expl = explode(",",$row['parent_id']);
                if(in_array($parent_id, $expl)){
                    $row['users'] =json_decode($row['users'],true);
                    $list[]=$parent_id;
                    $list=array_merge($list,$row['users']);
                }
                //----------------------------
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function orderRelative($parent_id)
    {
        $sqlText = "Select DISTINCT order_id
        From orders_short
            Where b_ID = '{$parent_id}'";

        //die($sqlText);
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['order_id'];
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function warrantyRelative($parent_id)
    {
        $sqlText = $sqlText ="Select warranty_order_id
            From warranty_short
            where buyer_id = '{$parent_id}'";

        //die($sqlText);
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['warranty_order_id'];
            }
        }
        return $list;
    }
    //------------------------------------------------------------
    public function getOverage_contactID($contactID)
    {
        $sqlText ="select sum(overage)-
                   IFNULL((select sum(pay_amount) from pay_acc_short
                     where customer='{$contactID}' AND pay_type='OnAcct'),0) AS total_overage
                  from pay_acc_short
                  where customer='{$contactID}'";
        $result = mysqli_query($this->con,$sqlText);

        $total_overage=0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $total_overage = $row['total_overage'];
            }
        }
        //----------sum contract-overage-------------
        $query ="select sum(contract_overage)
                  from orders
                  where bill_to='{$contactID}'";
        $result = mysqli_query($this->con,$query);
        $total_overage1=0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(!is_numeric($row['contract_overage'])) $row['contract_overage']=0;
                $total_overage1 = $row['contract_overage'];
            }
        }
        return $total_overage +$total_overage1;
    }


    //------------------------------------------------------------------
    public function updateStartDateforWarranty($warrantyID){
        $createTime = date("Y-m-d");
        $updateCommand = "UPDATE `warranty`
                SET warranty_start_date = '{$createTime}'
                where ID='{$warrantyID}'";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            return 1;
        }else{
            return mysqli_error($this->con);
        }

    }
    //----------------------------------------------------------
    public function getQBToken(){
        $query = "SELECT accessTokenKey,refreshTokenKey from quickbook_token LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $list =array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function filterDatePayment($startDate,$endDate){
        $query = "select * from payment_ledger_short";

        if(!empty($startDate) && !empty($endDate)){
            $query = "select * from payment_ledger_short
            where  p_pay_date >=$startDate and p_pay_date <=$endDate";
        }else if(!empty($startDate) && empty($endDate)){
            $query = "select * from payment_ledger_short
            where  p_pay_date >=$startDate";
        }else if(empty($startDate) && !empty($endDate)){
            $query = "select * from payment_ledger_short
            where  p_pay_date <=$endDate";
        }

        $result = mysqli_query($this->con,$query);
        $list =array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;

    }

    //------------------------------------------------------------------
    public function getCustomerID_conID($contactID){
        $query= "Select qb_customer_id,
          primary_street_address1,primary_city,primary_state,
          primary_postal_code,first_name,last_name,middle_name,
          primary_phone,primary_email,company_name
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
    public function returnCustomerInfo_contactID($customer){
        $info= $this->getCustomerID_conID($customer);
        if(empty($info["qb_customer_id"])){
            $data =array("Line1"=>$info["primary_street_address1"],
                "City"=>$info["primary_city"],
                "CountrySubDivisionCode"=>$info["primary_state"],
                "PostalCode"=>$info["primary_postal_code"],
                "GivenName"=>$info["first_name"],
                "FamilyName"=>$info["last_name"],
                "PrimaryPhone"=>$info["primary_phone"],
                "PrimaryEmailAddr"=>$info["primary_email"],
                "CompanyName"=>$info["company_name"]);

            return $data;
        }else{
            return array();
        }
    }

    //------------------------------------------------------------------
    public function updateQBVendor_contactID($contactID,$qb_customer_id){
        $updateCommand = "UPDATE `contact`
                SET qb_customer_id = '{$qb_customer_id }'
				 WHERE ID = '{$contactID}'";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            return $qb_customer_id;
        }else{
            return mysqli_error($this->con);
        }
    }
   //////////
}  
