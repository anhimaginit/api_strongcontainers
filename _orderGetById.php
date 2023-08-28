<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
$Object = new Orders();

    $EXPECTED = array('token','order_id');

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
        $ret_temp = $Object->getOrderID($order_id);
        if(count($ret_temp)>0){
            //$ret_temp[0]['notes'] = $Object->getNotesByOrderID($order_id);
            $ret_temp[0]['notes'] =$Object->getNote("order",$order_id);

            $t =  json_decode($ret_temp[0]['products_ordered'],true);

            $subs =  json_decode($ret_temp[0]['subscription'],true);

            unset($ret_temp[0]['products_ordered']);
            $ret_temp[0]['subscription']=$subs;

            //$invID = $Object->getInvoice_OrderID($order_id,$ret_temp[0]['bill_to']);

            $ret =array("order"=>$ret_temp,"products"=>$t) ;
        }else{
            $ret= $ret_temp;
        }

    }

    $Object->close_conn();
    echo json_encode($ret);

