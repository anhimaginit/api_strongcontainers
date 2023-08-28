<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.warranty.php';
$Object = new Warranty();

$EXPECTED = array('token','pageno','pagelength' ,'search_all','limitDay');


foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key]))
        ${$key} = $Object->protect($_POST[$key]);
    else
        ${$key} = NULL;
}

$list = array();
$isAuth =$Object->basicAuth($token);

if(!$isAuth){
    $ret = array("list"=>[],"total"=>0,'ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    $columns =['salesman','buyer','warranty_order_id',
        'warranty_start_date','warranty_end_date','warranty_address1'];

    $limit = empty($pagelength) ? 0 : $pagelength;

    $offset = empty($pageno) ? 0 : ($pageno-1)*$pagelength;

    $list = $Object->getDashboardWarrantyList($columns,$search_all,$limit,$offset,$limitDay);
    $total = $Object->warrantyTotal($columns,$search_all);
    $Object->close_conn();

    $ret = array("list"=>$list,"total"=>$total);
    echo json_encode($ret);
}