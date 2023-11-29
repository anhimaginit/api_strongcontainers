<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.task.php';
    $Object = new Task();

    $EXPECTED = array('token','id','jwt','private_key');
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
            $list =array();
            if(isset($_POST["data_post"])){
                $data = $_POST["data_post"];
                if(is_array($data)){
                    foreach($data as $item){
                        $order_id = $Object->protect($item["order_id"]);
                        if($order_id !='' && is_numeric($order_id)){
                            $id = $Object->protect($item["id"]);
                            $status_exsiting = $Object->return_id("select status from assign_task where id = '{$id}'",'status');
                            //echo "<pre>";print_r($order_id);echo "</pre>";
                            if($status_exsiting !='CLOSED' && $status_exsiting !='CANCELLED'){
                                if($status_exsiting !='DELIVERED'){
                                   // echo "<pre>";print_r("st = ".$status_exsiting);echo "</pre>"; die();
                                    $status = $Object->protect($item["status"]);
                                    if($id !='' && $id !=null){
                                        $p_key = array("id"=>$id);
                                        $k_value= array("status"=>$status);
                                        $list[] = $Object->update_table("assign_task",$p_key,$k_value);
                                    }

                                    $p_key = array("order_id "=>$order_id);
                                    $k_value= array("order_status"=>$status);
                                    $Object->update_table("quote",$p_key,$k_value);
                                }else{
                                    if($status=='CLOSED'){
                                        $status = $Object->protect($item["status"]);
                                        if($id !='' && $id !=null){
                                            $p_key = array("id"=>$id);
                                            $k_value= array("status"=>$status);
                                            $list[] = $Object->update_table("assign_task",$p_key,$k_value);
                                        }
                                        $p_key = array("order_id "=>$order_id);
                                        $k_value= array("order_status"=>$status);
                                        $Object->update_table("quote",$p_key,$k_value);
                                    }
                                }

                            }
                        }
                    }
                }
            }
            $ret = array('AUTH'=>true,'ERROR'=>'','list'=>$list);
        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }
    }

    $Object->close_conn();
    echo json_encode($ret);




