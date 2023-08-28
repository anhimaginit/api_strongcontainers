<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.task.php';
    $Object = new Task();

    $EXPECTED = array('token','ID','greater','table','login_id');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        }else if (!empty($_GET[$key])) {
            ${$key} = $Object->protect($_GET[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed','ID'=>'');
    }else{
        $role = $_POST['role'];
        $result = $Object->previous_nextBtn($ID,$greater,$table,$role,$login_id);
        $ret = array('ERROR'=>'','ID'=>$result);
    }

    $Object->close_conn();
    echo json_encode($ret);




