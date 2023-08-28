<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.cus_warranty.php';
    $Object = new CustomerWarranty();

    $EXPECTED = array('token','ID','cell_phone_number','comments_regarding_personal_property','company_name','email','first_name',
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
        $ret = array('SAVE'=>false,'ERROR'=>'Authentication is failed');
    }else if(!empty($ID)){
        $errObj['errorMsg']="Authentication is failed";
        $isAuth = $Object->auth($jwt,$private_key);
        $isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $errObj = $Object->validate_cw_fields($email,$first_name,$order_placed_by);
            $isAuth['AUTH']=true;
            if(!$errObj['error']){
                $result = $Object->updateCusWarrStep2($ID,$cell_phone_number,$comments_regarding_personal_property,$company_name,
                    $email,$first_name,$last_name,$office_phone_number,$order_placed_by,
                    $type_of_property,$prop_or_investment,$warranty_overage_for);

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





