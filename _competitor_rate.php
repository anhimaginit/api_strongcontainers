<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.task.php';
    $Object = new Task();

    $EXPECTED = array('id');
    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth = true;//$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('err'=>'Authentication is failed');
    }else{
        $temp = explode('@2&45$',$id);

        $id= base64_decode(trim($temp[0]));
        $driver_id = base64_decode(trim($temp[1]));
        $driver_id_tbl = $Object->return_id("SELECT assign_id FROM assign_task WHERE id ='{$id}'","assign_id");
        if($driver_id !=$driver_id_tbl) {
            $ret=array('err'=>'No permission');
        }else{
            $task = $Object->getTaskByID($id);
            if($task['date_accept'] !=null && $task['date_accept'] !='1970-01-01 00:00:00'){
                if($task['status'] =='open'){
                    $primary_key = array("id"=>$id);
                    $key_value = array("status"=>'in progress');
                    $Object->update_table('assign_task',$primary_key,$key_value);
                }
            }else{
                $primary_key = array("id"=>$id);
                $date = date("Y-m-d H:i:s");
                $key_value = array("date_accept"=>$date);
                if($task['status'] =='open'){
                    $key_value['status'] ='in progress';
                }
                $Object->update_table('assign_task',$primary_key,$key_value);
            }
            $task = $Object->getTaskByID($id);
            $driver_total = $task['driver_total'];
            $assign_order = $task['assign_order'];
            $assign_sku = $task['product_sku'];
            $driver_info = $Object->getContactEmail_ID($task['assign_id']);
            $driver_info['driver_total'] = $driver_total;
            $driver_info['date_accept'] = $task['date_accept'];
            $driver_info['delivery_date'] = $task['delivery_date'];

            $order_total = $Object->return_id("SELECT `total` FROM `quote` WHERE order_id  = '{$assign_order}'","total");
            $shipping_contact_id = $Object->return_id("SELECT `bill_to` FROM `quote` WHERE order_id  = '{$assign_order}'","bill_to");

            $info = $Object->depot_and_customer_address($assign_order);
            $customer = $Object->customer_info_order($assign_order);
            $depot = $Object->depot_customer_by_sku($assign_sku);
            $container_type_name = $depot['container_type_name'];

            $ret=array("depot"=>$depot,"customer"=>$customer,'order'=>$info,
                'driver_info'=> $driver_info,'err'=>''
            );
        }
    }

    $Object->close_conn();
    echo json_encode($ret);




