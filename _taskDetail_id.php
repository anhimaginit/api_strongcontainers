<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.task.php';
    $Object = new Task();

    $EXPECTED = array('token','taskID','jwt','private_key');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed','list'=>array());
    }else{
        $result = $Object->getTaskByID($taskID);

        $api_domain = $Object->api_domain;
        $upload_order_file_path = $Object->upload_order_file_path;
        $unload_order_file_path = $Object->unload_order_file_path;
        //upload
        $target_path = $api_domain.$upload_order_file_path;
        //$target_path ='http://localhost/CRMAPI/'.$upload_order_file_path;
        $file_name_upload = $result['file_pickup_name'];
        if($file_name_upload !='' && $file_name_upload !=null){
            $file_name_upload = explode(',',$file_name_upload);
        }

        $file_name_array = array();
        if(is_array($file_name_upload)){
            foreach($file_name_upload as $file_name){
                $file_name_array[] = $target_path.$file_name;
            }
        }

        $result['file_pickup_name'] = $file_name_array;
        ////unload
        $target_path = $api_domain.$unload_order_file_path;
        //$target_path ='http://localhost/CRMAPI/'.$unload_order_file_path;

        $file_name_unload = $result['file_delivery_name'];
        if($file_name_unload !='' && $file_name_unload !=null){
            $file_name_unload = explode(',',$file_name_unload);
        }

        $file_name_array = array();
        if(is_array($file_name_unload)){
            foreach($file_name_unload as $file_name){
                $file_name_array[] = $target_path.$file_name;
            }
        }

        $result['file_delivery_name'] = $file_name_array;
        //print_r($result); die();
        /////////////////////////////
        $ret = array('ERROR'=>'','task'=>$result);
    }

    $Object->close_conn();
    echo json_encode($ret);




