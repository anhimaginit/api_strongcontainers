<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
    $Object = new Claim();

    $EXPECTED = array('token','actionset','json_template');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array("list"=>'','ERROR'=>'Authentication is failed');
    }else{
        $task_temp =false;
        if(!empty($actionset)){
            $task_temp = $Object->addClaimTemplate($json_template,$actionset);
        }
        $ret =array("SUCCESS"=>$task_temp,'ERROR'=>'');
    }

    $Object->close_conn();
    echo json_encode($ret);




