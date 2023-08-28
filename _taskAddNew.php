<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.task.php';
    $Object = new Task();

    $EXPECTED = array('token','actionset','assign_id','content','customer_id',
    'doneDate','dueDate','status','taskName','time','alert','urgent','jwt','private_key');

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
        $isAuth = $Object->auth($jwt,$private_key);
        //$isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $errObj = $Object->validate_task_fields($taskName);
            if(!$errObj['error']){
                $result = $Object->AddNewTask($actionset,$assign_id,$content,$customer_id,
                    $doneDate,$dueDate,$status,$taskName,$time,$alert,$urgent);
                if(is_numeric($result) && $result!=""){
                    $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','AUTH'=>true,'id'=>$result);
                }else{
                    $ret = array('SAVE'=>'FAIL','ERROR'=>$result,'AUTH'=>true,'id'=>'');
                }

            }else{
                $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>true);

            }

        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }

    $Object->close_conn();
    echo json_encode($ret);




