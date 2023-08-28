<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.sandbox.php';
    $Object = new Sandbox();

    $EXPECTED = array('token','amount','cardNumber','expirationDate','CardCode',
        'invoiceNumber','description',
        'buyer_id','cus_identify','sale_id');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed');
    }else{
        $ret =$Object->chargeCreditCard($amount,$cardNumber,$expirationDate,$CardCode,
            $invoiceNumber,$description,
            $buyer_id,$cus_identify,
            $sale_id);


    }

$Object->close_conn();
echo json_encode($ret);



