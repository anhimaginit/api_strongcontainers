<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.contact.php';
    $Object = new Contact();

    $EXPECTED = array('token','company_name','contact_type',
        'first_name','last_name','middle_name','primary_city','primary_email',
        'primary_phone','primary_phone_ext','primary_phone_type','primary_postal_code',
        'primary_state', 'primary_street_address1','primary_street_address2',
        'aff_type','V_type');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $primary_email = base64_decode($primary_email);

    if (filter_var($primary_email, FILTER_VALIDATE_EMAIL)) {
        $isvalid = 1;
    } else {
        $isvalid = '';
    }

    //--- validate
$isAuth =$Object->basicAuth($token);
if(!$isAuth && empty($isvalid)){
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    $errObj = $Object->validate_contact_fields($first_name,$last_name,$primary_email,$primary_street_address1);

    if(!$errObj['error']){
        //upload file
        $err_upload_file =array();

        if(empty($company_name) || !is_numeric($company_name)) $company_name =0;
        $contact_inactive=0;

        $primary_phone = preg_replace('/\s+|-+|\(+|\)+/', '',$primary_phone);
        $primary_phone =trim($primary_phone);
        if(!empty($primary_phone) && strlen($primary_phone)==10) $primary_phone="1".$primary_phone;

        $result = $Object->addNewContact($company_name,$contact_inactive,$contact_type,
            $first_name,$last_name,$middle_name,$primary_city,$primary_email,
            $primary_phone,$primary_phone_ext,$primary_phone_type,$primary_postal_code,
            $primary_state, $primary_street_address1,$primary_street_address2,$aff_type);

        if(is_numeric($result["ID"]) && !empty($result["ID"])){
            $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result["ID"]);
            //Add vendor type
            $contact_type = stripos($contact_type,"Vendor");
            if(is_numeric($contact_type)){
                $vendor = true;
            }else{
                $vendor = 0;
            }

            $Object->addVendorType($result["ID"],$vendor,$V_type);

        } else {
            //log errors
            $errUpdate = $result['ID'];
            $info ="Contact -- Address1:".$primary_street_address1. ", Email: ".$primary_email.
                ", phone: ".$primary_phone_type.", postal code ".$primary_postal_code.", err: ".$errUpdate;

            $Object->err_log("Contact",$info,0);
            if($result){
                $ret = array('SAVE'=>'FAIL','ERROR'=>$result["ID"]);
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
}
    $Object->close_conn();
    echo json_encode($ret);




