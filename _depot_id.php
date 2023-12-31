<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.depot.php';
    $Object = new Depot();

    $EXPECTED = array('token','depot_id');

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
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    $ret = $Object->get_depot_id($depot_id);

    $api_domain = $Object->api_domain;
    $product_img = $Object->product_img;
    $target_path_image = $api_domain.$product_img;
    $ret['ERROR'] = "";
    $ret['path_image'] =$target_path_image;
}
    $Object->close_conn();
    echo json_encode($ret);




