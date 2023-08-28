<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claimtask.php';
    $Object = new ClaimTask();

    $EXPECTED = array('token','jwt','private_key','claimID','taskList');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    // $isAuth =$Object->basicAuth($token);
    // if(!$isAuth){
    //     $ret = array('ERROR'=>'Authentication is failed');
    // }else{
    //     //check $prod_price =0 or =""
    //     $isAuth = $Object->auth($jwt,$private_key);
    //     $isAuth['AUTH']=true;
    //     if($isAuth['AUTH']){
    //         $tasks = $_POST["task"];

    //         $result = $Object->AddClaimTaskList($tasks);

            var_dump($_POST["taskList"]);

    //         if(is_array($tasks) && count($tasks) >0){
    //             $ret = array('AUTH'=>true,'ERROR'=>'');

    //             if(isset($result["taskIDs"])){
    //                 if(count($result["taskIDs"])>0){
    //                    $taskIDs = $result["taskIDs"];

    //                     $current_asg_task = $Object->getClaimAssignTaskByClaimID($claimID);

    //                     if(isset($current_asg_task[0])){
    //                         $asg_task = json_decode($current_asg_task[0],true);

    //                         if(count($asg_task)>0){
    //                             $taskIDs = array_merge($taskIDs,$asg_task);
    //                         }
    //                     }

    //                     $taskIDs = json_encode($taskIDs);

    //                     $isUpdate =  $Object->updateClaimAssignTask($taskIDs,$claimID);

    //                     if(is_numeric($isUpdate) && !empty($isUpdate)){
    //                         $ret = array('AUTH'=>true,'ERROR'=>'', 'task'=>$result,"AssignTask"=>"SUCCESS");
    //                     }else{
    //                         $ret = array('AUTH'=>true,'ERROR'=>'', 'task'=>$result,"AssignTask"=>"Failed");
    //                     }
    //                 }
    //             }
    //             //save bug
    //             if(count($result["err"] >0)){
    //                 $info ="";
    //                 foreach($result["err"] as $item){
    //                     $info .= empty($info) ? "" : "; ";
    //                     $info .=" Claim Transaction: , taskName: ".$item["taskName"].
    //                         ", assign_id: ".$item["assign_id"];
    //                 }

    //                $Object->err_log("ClaimTask",$info,0);
    //             }

    //         }else{
    //             $ret = array('ERROR'=>"Task work is wrong format",'AUTH'=>true);
    //         }

    //     }else{
    //         $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
    //     }

    // }

    // $Object->close_conn();
    // echo json_encode($ret);