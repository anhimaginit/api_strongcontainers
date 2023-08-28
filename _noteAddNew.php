<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
include_once './lib/class.contact.php';

$Object = new Contact();

$isAuth =$Object->basicAuth($_POST['token']);

if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    $data = $_POST['data'];

    $contacID = $Object->protect($data['contactID']);
    $type = $Object->protect($data['type']);
    $note = $Object->protect($data['note']);
    $create_date = $Object->protect($data['create_date']);
    $description = $Object->protect($data['description']);
    $internal_flag =0;
    if(isset($data['internal_flag'])){
        $internal_flag = $Object->protect($data['internal_flag']);
        if(empty($internal_flag)) $internal_flag=0;
    }

    $fields = "contactID,create_date,type,note,description,internal_flag";
    $values = "'{$contacID}','{$create_date}','{$type}','{$note}','{$description}','{$internal_flag}'";
    $ret1 = $Object->installNotes($fields,$values);
    
    if(is_numeric($ret1) && !empty($ret1)){
        $ret['msg'] ="SUCCESS";
        $ret['id'] =$ret1;
    }else{        
        $ret['msg'] =$ret1;
    }
    
}

echo json_encode($ret);




