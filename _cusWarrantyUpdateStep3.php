<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.cus_warranty.php';
    $Object = new CustomerWarranty();

    $EXPECTED = array('token','ID','is_prop_new_existing','property_type','location_prop_warr_street',
        'location_prop_warr_address2','location_prop_warr_city',
        'location_prop_warr_state','location_prop_warr_zipcode','sales_rep',
        'estimated_closing_date','home_warranty_amount_purchase_contract',
        'warr_buyer_firstname','warr_buyer_lastname','different_warr_prop_address',
        'billing_address','billing_street1','billing_address2',
        'billing_city','billing_state','billing_zipcode',
        'billing_phone','home_owner_email_checked','home_owner_email',
        'additional_comments_or_concerns','jwt','private_key');

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
        $isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $errObj = $Object->validate_cw_fields_step3($property_type,$location_prop_warr_street,$location_prop_warr_city,$location_prop_warr_state,
                $location_prop_warr_zipcode);

            if(!$errObj['error']){

                $result = $Object->updateCusWarrStep3($ID,$is_prop_new_existing,$property_type,
                    $location_prop_warr_street,$location_prop_warr_address2,
                    $location_prop_warr_city,$location_prop_warr_state,
                    $location_prop_warr_zipcode,
                    $sales_rep,$estimated_closing_date,$home_warranty_amount_purchase_contract,
                    $warr_buyer_firstname,$warr_buyer_lastname,
                    $different_warr_prop_address,$billing_address,$billing_street1,
                    $billing_address2,$billing_city,$billing_state,
                    $billing_zipcode,$billing_phone,$home_owner_email_checked,
                    $home_owner_email,$additional_comments_or_concerns);

                //die($result);
                if(is_numeric($result)) {
                    $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','AUTH'=>true);
                }else{
                    $ret = array('SAVE'=>'FAIL','AUTH'=>true,'ERROR'=>$result);
                }

            } else {
                $ret = array('SAVE'=>'FAIL','AUTH'=>true,'ERROR'=>$errObj['errorMsg']);
            }
        }else{
            $ret = array('SAVE'=>false,'AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }else{
    $ret = array('SAVE'=>'FAIL','ERROR'=>'The Warranty is not already');

  }

    $Object->close_conn();
    echo json_encode($ret);





