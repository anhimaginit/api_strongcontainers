<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.cus_warranty.php';
    $Object = new CustomerWarranty();

    $EXPECTED = array('token','cell_phone_number','comments_regarding_personal_property','company_name','email','first_name',
        'last_name','office_phone_number','order_placed_by','type_of_property','prop_or_investment',
        'warranty_overage_for','jwt','private_key');

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
        $ret = array('SAVE'=>'FAIL','ERROR'=>'Authentication is failed');
    }else{
        $isAuth = $Object->auth($jwt,$private_key);
        $isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            //$acl = $isAuth['acl_list'];

            $errObj = $Object->validate_cw_fields($email,$first_name,$order_placed_by);

            if(!$errObj['error']){
                $result = $Object->addCusWarranty($cell_phone_number,$comments_regarding_personal_property,$company_name,
                    $email,$first_name,$last_name,$office_phone_number,$order_placed_by,
                    $type_of_property,$prop_or_investment,$warranty_overage_for);

               
                if(is_numeric($result) && !empty($result)){
                    $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result,'AUTH'=>true);
                } else {
                    //log errors
                    $ret = array('SAVE'=>'FAIL','ERROR'=>$result,'AUTH'=>true);

                }

            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>true);

            }

        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }


    $Object->close_conn();
    echo json_encode($ret);




