<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.payment.php';
    $Object = new Payment();

    $EXPECTED = array('token','pay_driver','pay_amount',
        'pay_task','pay_date','submit_by','pay_type',
    'pay_note');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $return = array('ERROR'=>'Authentication is failed','SAVE'=>'','pay_id'=>'');
    }else{
        //-----------
        $errObj = $Object->validate_payacct_fields($pay_amount,$pay_type);

        if(!$errObj['error']){
            //check credit
            $return = $Object->new_pay_driver($pay_driver,$pay_amount,$pay_task,$pay_date,
                   $submit_by,$pay_type,$pay_note);

        }else{
            $return = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'pay_id'=>'');
        }

    }

$Object->close_conn();
echo json_encode($return);



