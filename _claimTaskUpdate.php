<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claimtask.php';
    $Object = new ClaimTask();
    $EXPECTED = array('token','id','actionset','assign_id','createDate',
        'customer_id','doneDate','dueDate','status','taskName','jwt','private_key');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    if(!empty($id)){
        $isAuth =$Object->basicAuth($token);
        if(!$isAuth){
            $ret = array('ERROR'=>'Authentication is failed');
        }else{
            //--- validate
            $isAuth = $Object->auth($jwt,$private_key);
            $isAuth['AUTH'] =true;
            if($isAuth['AUTH']){
                //$acl = $isAuth['acl_list']; ''
                $result = $Object->UpdateClaimTask($id,$actionset,$assign_id,$createDate,
                    $customer_id,$doneDate,$dueDate,$status,$taskName);

                if(is_numeric($result) &&  $result){
                    $ret = array('SAVE'=>'SUCCESS','ERROR'=>'');
                } else {
                    $info ="Claim Task: , id: ".$id.
                        "actionset: ".$actionset.
                        ", assign_id: ".$assign_id.", createDate: ".$createDate.
                        ", doneDate: ".$doneDate.
                        ", status: ".$status.
                        ", taskName: ".$taskName.
                        ",customer_id: ".$customer_id. "; ".$result;

                    $Object->err_log("ClaimTask",$info,$id);

                    $ret = array('SAVE'=>'FAIL','ERROR'=>$result);
                }

            }else{
                $ret = array('SAVE'=>'FAIL','AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
            }
        }

    }else{
        $ret = array('SAVE'=>'FAIL','AUTH'=>false,'ERROR'=>"ID isn't already");
    }

    $Object->close_conn();
    echo json_encode($ret);





