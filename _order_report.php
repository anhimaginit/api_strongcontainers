<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.report.order.php';
    $Object = new ReportOrder();
    $EXPECTED = array('token','from_date','to_date','status','text_search','cursor','limit');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed','list'=>array());
    }else{
        $ret = $Object->order_report($from_date,$to_date,$status,$text_search,$cursor,$limit);
    }
    $Object->close_conn();
    echo json_encode($ret);




