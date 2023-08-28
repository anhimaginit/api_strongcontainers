<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.contact.php';
$Object = new Contact();

    $EXPECTED = array('token','contactID','type','note','create_date','description','internal_flag',
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
        $errObj['errorMsg']="Authentication is failed";
        $isAuth = $Object->auth($jwt,$private_key);
        $isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $fields = "contactID,create_date,type,note,description,internal_flag";
            if(empty($internal_flag)) $internal_flag=0;
            $values = "'{$contactID}','{$create_date}','{$type}','{$note}','{$description}','{$internal_flag}'";
        //die($values);
        $ret1 = $Object->installNotes($fields,$values);
            $ret = array('AUTH'=>true,'ERROR'=>$rsl);
        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }

    $Object->close_conn();
    echo json_encode($ret);





