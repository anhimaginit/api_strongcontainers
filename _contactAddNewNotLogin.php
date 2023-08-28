<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.contact.php';
include_once '_qbviacurl.php';
$Object = new Contact();
$EXPECTED = array('token','company_name','contact_inactive','contact_notes','contact_tags',
    'contact_type',
    'first_name','last_name','middle_name','primary_city','primary_email',
    'primary_phone','primary_phone_ext','primary_phone_type','primary_postal_code',
    'primary_state', 'primary_street_address1','primary_street_address2','primary_website','aff_type','user_name','password',
    'create_by','submit_by' ,'gps','V_type','jwt','private_key','archive_id','area','second_phone',
    'license_exp','w9_exp','insurrance_exp','contact_salesman_id','TaxIdentifier');

foreach ($EXPECTED AS $key) {
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    } else {
        ${$key} = NULL;
    }
}
//die();
//--- validate
$isAuth =$Object->basicAuth($token);
$code=200;
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    $create_by = $private_key;
    $isAuth['AUTH']=true;
    $errObj['errorMsg']="Authentication is failed";
    if($isAuth['AUTH']){
        //print_r($isAuth); die("1234");
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

            //upload file
            $err_upload_file =array();
            if(count($contact_doc) >0){
                for($i=0;$i<count($contact_doc);$i++){
                    $item = $contact_doc[$i];
                    $image_name ="";
                    $imageData = "";
                    if(isset($item["image_name"])) $image_name=$item["image_name"];
                    if(isset($item["image"])) $imageData = $item["image"];
                    if(!empty($image_name)){
                        $imageData = $item["image"];
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
                                        unset($contact_doc[$i]);
                                    }
                                }
                            }else{
                                unset($contact_doc[$i]);
                            }

                        }
                    }else{
                        $contact_doc[$i]["image"]="";
                        if(isset($contact_doc[$i]["image_name"])) unset($contact_doc[$i]["image_name"]);

                    }

                }
            }

            if(empty($company_name) || !is_numeric($company_name)) $company_name =0;
            if(empty($archive_id)) $archive_id =0;
            //$primary_phone = $Object->format_phone($primary_phone);
            $primary_phone = preg_replace('/\++|\s+|-+|\(+|\)+/', '',$primary_phone);
            $primary_phone =trim($primary_phone);
            //if(!empty($primary_phone) && strlen($primary_phone)==10) $primary_phone="1".$primary_phone;

            $result = $Object->addContact($company_name,$contact_inactive,$contact_notes,$contact_tags,$contact_type,
                $first_name,$last_name,$middle_name,$primary_city,$primary_email,
                $primary_phone,$primary_phone_ext,$primary_phone_type,$primary_postal_code,
                $primary_state, $primary_street_address1,$primary_street_address2,$primary_website,$aff_type,
                $user_name,$password,$notes,$private_key,$submit_by,$gps,$contact_doc,$archive_id,$area,
                $license_exp,$w9_exp,$insurrance_exp,$contact_salesman_id,$TaxIdentifier);

            if(is_numeric($result["ID"]) && !empty($result["ID"])){
                //Add second phone
                $secondPhone= $Object->addSecondPhone($second_phone,$result["ID"]);
                //Add tags
                $Object->addTag_contact("Contact",$contact_tags);
                //Add vendor type
                $p = stripos($contact_type,"Vendor");
                if(is_numeric($p)){
                    $vendor = true;
                }else{
                    $vendor = 0;
                }

                $Object->addVendorType($result["ID"],$vendor,$V_type);
                //Quickbook
                //Employee
                $rsl_emp='';
                $p= stripos($contact_type,"Employee");
                if(is_numeric($p)){
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
                    unset($curlObj);
                    $rsl = json_decode($rsl,true);
                    if(isset($rsl["CreatedId"])){
                        if(is_numeric($rsl["CreatedId"])){
                            $rsl_emp= $Object->updateQBEmployeeID_contactID($result["ID"],$rsl["CreatedId"]);
                        }

                    }
                    //print_r($rsl); die();
                }
                //vendor
                $rsl_vendor='';
                $p= stripos($contact_type,"Vendor");
                if(is_numeric($p) && !empty($company_name)){
                    $company_real_name=$Object->getCompanyName_comID($company_name);

                    //check vendor form quickbook
                    $url = "_qbVendorSearch_companyname.php";
                    $data = array('CompanyName'=>$company_real_name);
                    $curlObj= new QBviaCurl();
                    $rsl=$curlObj->httpost_curl($url,$data);
                    unset($curlObj);
                    $rsl = json_decode($rsl,true);
                    if(isset($rsl["vendorID"])){
                        if(is_numeric($rsl["vendorID"])){
                            $rsl_vendor= $Object->updateQBVendorID_contactID($result["ID"],$rsl["vendorID"]);

                        }else{
                            $data = array('Line1'=>$primary_street_address1,
                                'City'=>$primary_city,
                                'CountrySubDivisionCode'=>$primary_state,
                                'PostalCode'=>$primary_postal_code,
                                'GivenName'=>$first_name,
                                'MiddleName'=>$middle_name,
                                'FamilyName'=>$last_name,
                                'PrimaryPhone'=>$primary_phone,
                                'PrimaryEmailAddr'=>$primary_email,
                                'CompanyName'=>$company_real_name,
                                'TaxIdentifier'=>$TaxIdentifier);

                            $curlObj= new QBviaCurl();
                            $url = "_qbCreateVendor.php";
                            $rsl=$curlObj->httpost_curl($url,$data);
                            unset($curlObj);
                            $rsl = json_decode($rsl,true);
                            if(isset($rsl["CreatedId"])){
                                if(is_numeric($rsl["CreatedId"])){
                                    $rsl_vendor= $Object->updateQBVendorID_contactID($result["ID"],$rsl["CreatedId"]);
                                }
                            }
                        }
                    }
                    //print_r($rsl); die();
                }
                //
                $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result["ID"],'affilateID'=>$result["affilateID"],
                    'notes'=>$result['notes'],'doc'=>$result['doc'],'err_upload_file'=>$err_upload_file,
                    'secondPhone'=>$secondPhone,
                    'contact_duplicated'=>array(),
                    'qbemployee_id'=>$rsl_emp,'qbvendor_id'=>$rsl_vendor);

            } else {
                //log errors
                $errUpdate = $result['ID'].' Note: '.$result['notes'].' Document: '.$result['doc'];
                $info ="Contact -- Address1:".$primary_street_address1. ", Email: ".$primary_email.
                    ", phone: ".$primary_phone_type.", postal code ".$primary_postal_code.", err: ".$errUpdate;

                $Object->err_log("Contact",$info,0);
                if($result){
                    if($result["ID"]=='The phone and email are used'){
                        $code=403;
                    }else{
                        $result['contact_duplicated']=array();
                    }
                    $ret = array('SAVE'=>'FAIL','ERROR'=>$result["ID"],"err_upload_file"=>$err_upload_file,
                        'contact_duplicated'=>$result['contact_duplicated']);
                }else{
                    $ret = array('SAVE'=>'FAIL','ERROR'=>"Can't add the contact");
                }
            }

        } else {
            $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
            //log errors
            $info ="Contact -- Address1:".$primary_street_address1. ", Email: ".$primary_email.
                ", phone: ".$primary_phone_type.", postal code ".$primary_postal_code.", err: ".$errObj['errorMsg'];

            $Object->err_log("Contact",$info,0);

        }
    }else{
        $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
    }
}
$Object->close_conn();
echo json_encode($ret);
http_response_code($code);




