<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.group.php';
    $Object = new Group();

    $EXPECTED = array('token','ID','department','group_name','role','users','parent_group','parent_id',
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

    //$isAuth['AUTH']=true;
    $errObj['errorMsg']="Authentication is failed";
    if($isAuth['AUTH']){
        $errObj = $Object->validate_group_fields($group_name,$department,$role,$parent_group,$parent_id);

        if(!$errObj['error']){

            $result = $Object->upGroup($ID,$department,$group_name,$role,$users,$parent_group,$parent_id);

            if(is_numeric($result) && !empty($result)){
                $ret = array('SAVE'=>'SUCCESS','ERROR'=>'');

            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$result);
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




