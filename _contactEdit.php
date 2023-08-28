<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.contact.php';
include_once '_qbviacurl.php';
    $Object = new Contact();

    $EXPECTED = array('token','ID','company_name','contact_inactive','contact_notes','contact_tags',
        'contact_type',
        'first_name','last_name','middle_name','primary_city','primary_email',
        'primary_phone','primary_phone_ext','primary_phone_type','primary_postal_code',
        'primary_state', 'primary_street_address1','primary_street_address2','primary_website','aff_type','E_type',
        'user_name','password','userID','create_by','submit_by','gps','V_type','jwt','private_key','archive_id','area',
        'second_phone','license_exp','w9_exp','insurrance_exp','save_anyway','contact_salesman_id',
        'sms_api_username','sms_api_key','saveanyway','TaxIdentifier');

    $code=200;
    //check fields don't permit update
    $up_inactive =false;
    $up_email =false;
    $up_phone =false;
    $up_user_name =false;
    $up_password =false;
    if(isset($_POST["contact_inactive"])){
        $up_inactive =true;
    }

    if(isset($_POST["primary_email"])){
        $up_email =true;
    }

    if(isset($_POST["primary_phone"])){
        $up_phone =true;
    }

    if(isset($_POST["user_name"])){
        $up_user_name =true;
    }

    if(isset($_POST["password"])){
        $up_password =true;
    }

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    //--- validate
    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('SAVE'=>false,'ERROR'=>'Authentication is failed');
    }else if(!empty($ID)){
        $errObj['errorMsg']="Authentication is failed";
        $isAuth = $Object->auth($jwt,$private_key);

        if($isAuth['AUTH']){
            //$errObj = $Object->validate_contact_fields($first_name,$last_name,$primary_email,$primary_street_address1);
            $errObj = $Object->validateContactFieldEmailOrPhone($primary_email,$primary_phone);
            if(!$errObj['error']){
                if($contact_inactive=="" || empty($contact_inactive)) {
                    $contact_inactive =0;
                }

                $notes =array();
                if(isset($_POST['notes'])){
                    $notes=$_POST['notes'];
                }

                $contact_doc =array();
                if(isset($_POST['contact_doc'])){
                    $contact_doc=$_POST['contact_doc'];
                }

                $err_upload_file =array();
                //print_r($contact_doc); echo ";;;";
                if(count($contact_doc) >0){
                    for($i=0;$i<count($contact_doc);$i++){
                        $item = $contact_doc[$i];
                        $image_name ="";
                        $imageData = "";
                        if(isset($item["image_name"])) $image_name=$item["image_name"];
                        if(isset($item["image"])) $imageData = $item["image"];
                        if(!empty($image_name) && ($imageData!=$image_name) && !empty($imageData)){

                            $ext = explode('.', $image_name);
                            if(count($ext)>0){
                                $index = count($ext)-1;
                                if($ext[$index]=="pdf" || $ext[$index]=="PDF"){
                                    $repl = 'data:application/pdf;base64,';
                                }elseif($ext[$index]=="jpg" || $ext[$index]=="JPG"){
                                    $repl = 'data:image/jpeg;base64,';
                                }elseif($ext[$index]=="png" || $ext[$index]=="PNG"){
                                    $repl = 'data:image/png;base64,';
                                }

                                $data = str_replace(
                                    $repl,
                                    '',
                                    $imageData
                                );
                                //verify file is true
                                if(base64_encode(base64_decode($data, true)) === $data){
                                    list($type, $imageData) = explode(';', $imageData);
                                    list(,$extension) = explode('/',$type);
                                    list(,$imageData)      = explode(',', $imageData);

                                    $flag = in_array($extension,[ 'jpg', 'jpeg', 'gif', 'png', 'pdf','JPG', 'JPEG','PNG']);

                                    if($flag){
                                        $name ="/photo/docs/contacts/".$first_name."_".$last_name."_".$item["image_name"];
                                        $photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$name; //'.'.$extension;
                                        $imageData = base64_decode($imageData);
                                        $upload = file_put_contents($photoPathTemp, $imageData);

                                        if(is_numeric($upload)){
                                            $photoPath = $name;
                                            $contact_doc[$i]["image"]=$name;
                                            unset($contact_doc[$i]["image_name"]);
                                        }else{
                                            $err_temp=array();
                                            $err_temp["filename"]=$item["image_name"];
                                            $err_upload_file[]=$err_temp;

                                            if(isset($contact_doc[$i]["ID"])){
                                                if(!empty($contact_doc[$i]["ID"])){
                                                    $contact_doc[$i]["image"]="";
                                                    unset($contact_doc[$i]["image_name"]);
                                                }
                                            }else{
                                                unset($contact_doc[$i]);
                                            }

                                        }
                                    }
                                    //
                                }else{
                                    $err_temp=array();
                                    $err_temp["filename"]=$item["image_name"];
                                    $err_upload_file[]=$err_temp;
                                    if(isset($contact_doc[$i]["ID"])){
                                        if(!empty($contact_doc[$i]["ID"])){
                                            $contact_doc[$i]["image"]="";
                                            unset($contact_doc[$i]["image_name"]);
                                        }
                                    }else{
                                        unset($contact_doc[$i]);
                                    }

                                }

                            }

                        }else{
                            if(empty($image_name)){
                                if(isset($contact_doc[$i]["image"])){
                                    $contact_doc[$i]["image"]=$Object->protect($contact_doc[$i]["image"]);
                                }else{
                                    $contact_doc[$i]["image"]="";
                                }

                            }
                            unset($contact_doc[$i]["image_name"]);

                        }


                    }

                }
                //$primary_phone = $Object->format_phone_new($primary_phone);
                //$primary_phone = $Object->format_phone($primary_phone);
               //print_r($contact_doc);
               // die();
                if(empty($company_name) || !is_numeric($company_name)) $company_name=0;
                if(empty($archive_id)) $archive_id =0;

                $result = $Object->updateContact($ID,$company_name,$contact_inactive,$contact_notes,$contact_tags,$contact_type,
                    $first_name,$last_name,$middle_name,$primary_city,$primary_email,
                    $primary_phone,$primary_phone_ext,$primary_phone_type,$primary_postal_code,
                    $primary_state, $primary_street_address1,$primary_street_address2,$primary_website,$aff_type,$E_type,
                    $user_name,$password,$notes,$userID,$up_email,$up_phone,$up_user_name,$up_password,$up_inactive,$create_by,$submit_by,
                    $gps,$contact_doc,$archive_id,$area,
                    $license_exp,$w9_exp,$insurrance_exp,$save_anyway,$sms_api_username,$sms_api_key,$contact_salesman_id,$TaxIdentifier);

                //die($result);
                if(is_numeric($result["updated"])) {
                    //Update vendor type
                    $p = stripos($contact_type,"Vendor");
                    if(is_numeric($p)){
                        $vendor = true;
                    }else{
                        $vendor = 0;
                    }

                    $Object->updateVendorType($ID,$vendor,$V_type);
                    //Add tags
                    $Object->addTag_contact("Contact",$contact_tags);
                    //Add second phone
                    $secondPhone= $Object->addSecondPhone($second_phone,$ID);
                    $rsl_emp='';
                    //Add quickbook
                    $info = $Object->getQBEmployeeVendorID_conID($ID);
                    //employee

                    $p= stripos($contact_type,"Employee");
                    if(!is_numeric($info["qb_employee_id"]) && is_numeric($p)){
                        $SSN="";
                        $data = array('Line1'=>$primary_street_address1,
                            'SSN'=>$SSN,
                            'City'=>$primary_city,
                            'CountrySubDivisionCode'=>$primary_state,
                            'PostalCode'=>$primary_postal_code,
                            'GivenName'=>$first_name,
                            'MiddleName'=>$middle_name,
                            'FamilyName'=>$last_name,
                            'PrimaryPhone'=>$primary_phone);

                        $curlObj= new QBviaCurl();
                        $url = "_qbCreateEmployee.php";
                        $rsl=$curlObj->httpost_curl($url,$data);
                        $rsl = json_decode($rsl,true);
                        unset($curlObj);
                        if(isset($rsl["CreatedId"])){
                            if(is_numeric($rsl["CreatedId"])){
                                $rsl_emp= $Object->updateQBEmployeeID_contactID($ID,$rsl["CreatedId"]);
                            }

                        }
                    }elseif(is_numeric($info["qb_employee_id"]) && is_numeric($p)){
                        //update employee in qb
                        $SSN="";
                        $data = array('Line1'=>$primary_street_address1,
                            'SSN'=>$SSN,
                            'City'=>$primary_city,
                            'CountrySubDivisionCode'=>$primary_state,
                            'PostalCode'=>$primary_postal_code,
                            'GivenName'=>$first_name,
                            'MiddleName'=>$middle_name,
                            'FamilyName'=>$last_name,
                            'PrimaryPhone'=>$primary_phone,
                            'Id'=>$info["qb_employee_id"]);

                        $curlObj= new QBviaCurl();
                        $url = "_qbUpdateEmployee.php";
                        $rsl=$curlObj->httpost_curl($url,$data);
                    }

                    //vendor
                    $rsl_vendor='';
                    $p = stripos($contact_type,"Vendor");

                    if(is_numeric($p)&& is_numeric($info["qb_vendor_id"])){
                        //update vendor on quickbook
                        $data = array('Line1'=>$primary_street_address1,
                            'City'=>$primary_city,
                            'CountrySubDivisionCode'=>$primary_state,
                            'PostalCode'=>$primary_postal_code,
                            'GivenName'=>$first_name,
                            'MiddleName'=>$middle_name,
                            'FamilyName'=>$last_name,
                            'PrimaryPhone'=>$primary_phone,
                            'PrimaryEmailAddr'=>$primary_email,
                            'CompanyName'=>'',
                            'TaxIdentifier'=>$TaxIdentifier,
                            'Id'=>$info["qb_vendor_id"]);

                        $curlObj= new QBviaCurl();
                        $url = "_qbUpdateVendor.php";
                        $rsl=$curlObj->httpost_curl($url,$data);
                        unset($curlObj);

                    }elseif(is_numeric($p)&& !is_numeric($info["qb_vendor_id"])){
                        $data = array('Line1'=>$primary_street_address1,
                            'City'=>$primary_city,
                            'CountrySubDivisionCode'=>$primary_state,
                            'PostalCode'=>$primary_postal_code,
                            'GivenName'=>$first_name,
                            'MiddleName'=>$middle_name,
                            'FamilyName'=>$last_name,
                            'PrimaryPhone'=>$primary_phone,
                            'PrimaryEmailAddr'=>$primary_email,
                            'CompanyName'=>'',
                            'TaxIdentifier'=>$TaxIdentifier);

                        $curlObj= new QBviaCurl();
                        $url = "_qbCreateVendor.php";
                        $rsl=$curlObj->httpost_curl($url,$data);
                        unset($curlObj);
                        $rsl = json_decode($rsl,true);
                        if(isset($rsl["CreatedId"])){
                            if(is_numeric($rsl["CreatedId"])){
                                $rsl_vendor= $Object->updateQBVendorID_contactID($ID,$rsl["CreatedId"]);
                            }
                        }
                    }

                    ////end  elseif(is_numeric($p)&& !is_numeric($info["qb_vendor_id"])
                    $ret = array('SAVE'=>'SUCCESS','ERROR'=>'',"notes"=>$result['notes'],"doc"=>$result['doc'],
                    'user'=>$result['user'],"err_upload_file"=>$err_upload_file,
                    'secondPhone'=>$secondPhone,
                        'contact_duplicated'=>array(),'qbemployee_id'=>$rsl_emp,'qbvendor_id'=>$rsl_vendor);
                    // print_r("test=");print_r($result); die();
                }else{

                    //log errors
                    $info ="Contact -- Address1:".$primary_street_address1. ", Email: ".$primary_email.
                        ", phone: ".$primary_phone_type.", postal code ".$primary_postal_code.", err: ".$result["updated"];

                    $Object->err_log("Contact",$info,$ID);
                    if($result["updated"]=='The phone and email are used'){
                        $code=403;
                    }else{
                        $result['contact_duplicated']=array();
                    }
                    $ret = array('SAVE'=>'FAIL','ERROR'=>$result["updated"],"err_upload_file"=>$err_upload_file,
                    'contact_duplicated'=>$result['contact_duplicated']);
                }

            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
                //log errors
                $info ="Contact -- Address1:".$primary_street_address1. ", Email: ".$primary_email.
                    ", phone: ".$primary_phone_type.", postal code ".$primary_postal_code.", err: ".$errObj['errorMsg'];
            }
        }else{
            $ret = array('SAVE'=>false,'AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }else{
    $ret = array('SAVE'=>'FAIL','ERROR'=>'Contact is not already');

  }

    $Object->close_conn();
    echo json_encode($ret);
    http_response_code($code);





