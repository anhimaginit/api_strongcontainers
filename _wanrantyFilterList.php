<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.warranty.php';
$Object = new Warranty();

$EXPECTED = array('token','pageno','pagelength' ,'search_all','jwt','private_key');


foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    }else if (!empty($_GET[$key])) {
        ${$key} = $Object->protect($_GET[$key]);
    }
    else
    {
        ${$key} = NULL;
    }
}

$list = array();
$isAuth =$Object->basicAuth($token);

if(!$isAuth){
    $ret = array("list"=>[],"total"=>0,'ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    $columns =['warranty_serial_number', 'salesman','buyer','warranty_order_id',
        'warranty_start_date','warranty_end_date','warranty_address1'];

    //$limit = empty($pagelength) ? 0 : $pagelength;
    $limit=1000;
    $offset = empty($pageno) ? 0 : ($pageno-1)*$pagelength;

    $isAuth = $Object->auth($jwt,$private_key);
    //$isAuth['AUTH']=true;
    if($isAuth['AUTH']){
        $role = $_POST['role'];

        $list = $Object->searchWarrantyList($columns,$search_all,$limit,$offset,$role,$private_key);
        //$total = $Object->warrantyTotal($columns,$search_all,$role,$private_key);
        $ret = array('AUTH'=>true,"list"=>$list,"total"=>0);
    }else{
        $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
    }


    $Object->close_conn();

    echo json_encode($ret);
}


