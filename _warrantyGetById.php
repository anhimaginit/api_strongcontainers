<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.warranty.php';
$Object = new Warranty();

    $EXPECTED = array('token','ID');

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
        $ret = $Object->getWarrantyByID($ID);
        if(count($ret)>0){
            //$notes = $Object->getNotesByWarrantyID($ID);
            $notes = $Object->getNote("warranty",$ID);
            $ret[0]['notes']=$notes;
        }
    }

    $Object->close_conn();
    echo json_encode($ret);


