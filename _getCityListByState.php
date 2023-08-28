<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    include_once './lib/class.state.php';

    $Object = new State();
    $EXPECTED = array('token', 'state');


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
        $ret = array('ERROR'=>'Authentication is failed');
        $Object->close_conn();
        echo json_encode($ret);
    }else{
        if(empty($state) || $state==""){
            $ret = array('city'=>array(), 'zip'=>array());
            $Object->close_conn();
            echo json_encode($ret);
        }else{
            $cites = $Object->getCityByState($state);

            $zipcode = array();
            if(count($cites) >0){
                $zipcode = $Object->getZipcodeByCity($cites[0]["city"]);
            }

            $ret = array('city'=>$cites, 'zip'=>$zipcode);
            $Object->close_conn();
            echo json_encode($ret);
        }
    }


