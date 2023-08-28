<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

           
include_once './lib/class.common.php';
    $Object = new Common();

    $EXPECTED = array('token','contactID','typeID');

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
        $notes =array();
        if(isset($_POST['note'])){
            $notes=$_POST['note'];
        }
        
        $id = $Object->add_notes($notes,$contactID,$typeID);
        if(is_numeric($id) && !empty($id)){
            $ret = array('add'=>true,'ERROR'=>'');
        }else{
            $ret = array('add'=>false,'ERROR'=>$id);
        }

    }

    $Object->close_conn();
    echo json_encode($ret);




