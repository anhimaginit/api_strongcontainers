<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.payment.php';
    $Object = new Payment();

    $EXPECTED = array('token','pay_amount','pay_type',
        'pay_note','submit_by','approved','invID','order_id','overage','customer');

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
               $idreturn = $Object->AddPayAcct($pay_amount,$pay_type,
                $pay_note,$submit_by,$approved,$invID,$order_id,$overage,$customer);
            //check date to update for warranty
               if(is_numeric($idreturn) && !empty($order_id)){
                  $rsl = $Object->getWarrantyStartDate_orderID($order_id);
                   //print_r($rsl); die("123");
                   if(is_numeric($rsl['warranty']) && !empty($rsl['warranty']) &&
                       empty($rsl['warranty_start_date']) ){
                      $Object->updateStartDateforWarranty($rsl['warranty']);
                   }
               }
            $return = array('ERROR'=>'','SAVE'=>'SUCCESS','pay_id'=>$idreturn);
        }else{
            $return = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'pay_id'=>'');
        }

    }

$Object->close_conn();
echo json_encode($return);



