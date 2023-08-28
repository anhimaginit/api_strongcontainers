<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.group.php';
    $Object = new Group();

    $EXPECTED = array('token','ID','role','department');

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
        $ret = array('group'=>'','ERROR'=>'Authentication is failed');
    }else{
        $result = $Object->aclRule_grpID($ID);
        $role1 =$result['role'];

        if($result['acl'] ==0 || count($result)==0){
            $result = $Object->getACLUnitLevel($department,$role);
        }else{
            $process[0] = $Object->getACLUnitLevel($department,$role);
            $process[1] =$result;
            $result = $Object->update_processACL($process);
            $result['role']=$role1;
            print_r($result); die();
        }
        $ret = array('group'=>$result,'ERROR'=>'');
    }

    $Object->close_conn();
    echo json_encode($ret);




