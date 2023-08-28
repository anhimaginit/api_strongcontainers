<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

           
include_once './lib/class.common.php';
    $Object = new Common();

    $EXPECTED = array('token','discount_name','apply_to','start_date',
        'stop_date','excludesive_offer','active','nerver_expired',
        'jwt','private_key');

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
    $isAuth = $Object->auth($jwt,$private_key);

    $isAuth['AUTH']=true;
    $errObj['errorMsg']="Authentication is failed";
    if($isAuth['AUTH']){

        if(empty($nerver_expired)) $nerver_expired=0;
        $errObj = $Object->validateDiscountFields($discount_name,$start_date,$stop_date,$nerver_expired);

        if(!$errObj['error']){
            if(empty($excludesive_offer)) $excludesive_offer=0;
            if(empty($active)) $active=0;


            $id = $Object->addNewDiscount($discount_name,$apply_to,
                $start_date,$stop_date,$excludesive_offer,$active,$nerver_expired);

            if(is_numeric($id) && !empty($id)){
                $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$id);
            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$id,'ID'=>"");
            }

        } else {
            $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);

        }
    }else{
        $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
    }
}
    $Object->close_conn();
    echo json_encode($ret);




