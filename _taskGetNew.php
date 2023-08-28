<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.task.php';
    $Object = new Task();

    $EXPECTED = array('token','taskName','assign_id','jwt','private_key');

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
	
	$isAuth = true;
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed','list'=>array());
    }else{
        $result = $Object->getnewTasks($assign_id,$taskName);
        $ret = array('ERROR'=>'','list'=>$result);
    }

    $Object->close_conn();
    echo json_encode($ret);




