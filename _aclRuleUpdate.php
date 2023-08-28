<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.group.php';
    $Object = new Group();

    $EXPECTED = array('token','acl_rules','unit','level','jwt','ID','private_key');

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
        //$acl_rules = $_POST['acl_rules'];
        if(!empty($acl_rules)){
            $result = $Object->ACL_update($acl_rules,$level,$ID);

            if(is_numeric($result) &&  $result){
                $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','AUTH'=>true);
            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$result,'AUTH'=>true);
            }

        } else {
            $ret = array('SAVE'=>'FAIL','ERROR'=>"acl is required",'AUTH'=>true);
        }
    }else{
        $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>false);
    }
}
    $Object->close_conn();
    echo json_encode($ret);




