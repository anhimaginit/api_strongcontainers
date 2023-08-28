<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
$Object = new Claim();

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
        $ret_temp = $Object->getClaim_ID($ID);
        if(count($ret_temp)>0){
            $temp = $ret_temp[0]['assign_task'];
            $taskIDs="";
            if(count($temp)>0){
                foreach($temp as $item){
                    if($item >0){
                        $taskIDs .= empty($taskIDs)? "":",";
                        $taskIDs .= $item;
                    }
                }

                if(!empty($taskIDs)){
                    $ret_temp[0]['assign_task'] = $Object->getAssignTaskTaskIDs($taskIDs);
                }

            }

            $ret_temp[0]['notes'] = $Object->getNotesByClaimsID($ID);

            $ret = array('ERROR'=>'','Claim'=>$ret_temp[0]);
        }else{
            $ret = array('ERROR'=>'','Claim'=>'');
        }

    }

    $Object->close_conn();
    echo json_encode($ret);

