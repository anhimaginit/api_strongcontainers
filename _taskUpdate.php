<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.task.php';
    $Object = new Task();

    $EXPECTED = array('token','id','actionset','assign_id','content','customer_id',
    'doneDate','dueDate','status','taskName','alert','urgent','time','jwt','private_key',
        'assign_order','assign_driver_id','deliverydate','deliverytime','product_sku');
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
            $status_exsiting = $Object->return_id("select status from assign_task where id = '{$id}'",'status');
            if($status_exsiting !='CLOSED'){
                $errObj = $Object->validate_task_fields($taskName);
                if(!$errObj['error']){
                    if($deliverydate !=''){
                        if($status !="CLOSED" && $status != ! 'PICKED UP' && $status != ! 'DELIVERED') $status ="SCHEDULED FOR DELIVERY";
                    }
                    //process image
                    $api_domain = $Object->api_domain;
                    $upload_order_file_path = $Object->upload_order_file_path;
                    $unload_order_file_path = $Object->unload_order_file_path;
                    //upload image
                    $upload_existing_file ='';
                    $upload_existing_file_temp=array();
                    if(isset($_POST['upload_existing_file'])){
                        $upload_existing_file = $_POST['upload_existing_file'];
                        if($upload_existing_file !='')
                            $upload_existing_file_temp = explode(',',$upload_existing_file);
                    }

                    $file_name_upload_return = array();
                    $list_file_upload_name='';

                    $target_path_temp =$_SERVER["DOCUMENT_ROOT"].$upload_order_file_path;
                    $target_path_return = $api_domain.$upload_order_file_path;
                    //$target_path_temp ='C:/xampp/htdocs/CRMAPI/'.$upload_order_file_path;
                    //$target_path_return ='http://localhost/CRMAPI/'.$upload_order_file_path;

                    $files_save_err ='';
                    $fileError ='';
                    if(isset($_FILES['upload_file'])){
                        //print_r($_FILES['file']);
                        for($i=0; $i<count($_FILES['upload_file']['name']); $i++){
                            $f_name = basename( $_FILES['upload_file']['name'][$i]);
                            $fileError = $Object->upload_exception($_FILES['upload_file']['error'][$i]);
                            $target_path = $target_path_temp . $f_name;
                            // die($target_path);
                            $file_size = $_FILES['upload_file']['size'][$i];
                            $allowed_ext = array("jpg","jpeg","png","pdf");

                            $file_ext_temp = explode('.',$f_name);
                            $file_ext = strtolower(end($file_ext_temp));

                            if(in_array($file_ext, $allowed_ext)){
                                if($fileError == "" && $file_size < 10000000){
                                    if(move_uploaded_file($_FILES['upload_file']['tmp_name'][$i], $target_path)) {
                                        if(!is_numeric(array_search($f_name,$upload_existing_file_temp))){
                                            $file_name_temp = $target_path_return.$f_name;
                                            array_push($file_name_upload_return,$file_name_temp);
                                            $list_file_upload_name = ($list_file_upload_name=='')?$f_name:$list_file_upload_name.','.$f_name;
                                        }

                                    } else{
                                        $files_save_err = "There was an error uploading the file, please try again!";
                                    }
                                }
                            }else{
                                $fileError ="File is not in format";
                            }

                        }
                    }

                    if($list_file_upload_name !=''){
                        if($status !="CLOSED" && $status !="DELIVERED") $status ="PICKED UP";
                        $upload_existing_file= ($upload_existing_file=='')?$list_file_upload_name : $upload_existing_file.','.$list_file_upload_name ;

                    }
                    //use to update
                    $file_upload_name =$upload_existing_file;

                    $file_upload_database = $Object->return_id("select file_pickup_name from assign_task where id = '{$id}'",'file_pickup_name');

                    if($file_upload_database !='' && $file_upload_database !=null){
                        $file_upload_database = explode(',',$file_upload_database);
                        $upload_existing_file = explode(',',$upload_existing_file);

                        $list_file_delete = array_diff($file_upload_database,$upload_existing_file);
                        //delefile
                        if(count($list_file_delete) > 0){
                            foreach($list_file_delete as $item){
                                $filePathTemp = $target_path_temp.$item;
                                if(file_exists($filePathTemp)){
                                    $err = unlink($filePathTemp);
                                }
                            }
                        }
                    }
                    //unload image
                    $unload_existing_file ='';
                    $unload_existing_file_temp=array();
                    if(isset($_POST['unload_existing_file'])){
                        $unload_existing_file = $_POST['unload_existing_file'];
                        if($unload_existing_file !='')
                            $unload_existing_file_temp = explode(',',$unload_existing_file);
                    }

                    $file_name_unload_return = array();
                    $list_file_unload_name='';

                    $target_path_temp =$_SERVER["DOCUMENT_ROOT"].$unload_order_file_path;
                    $target_path_return = $api_domain.$unload_order_file_path;
                    //$target_path_temp ='C:/xampp/htdocs/CRMAPI/'.$unload_order_file_path;
                    //$target_path_return ='http://localhost/CRMAPI/'.$unload_order_file_path;

                    $files_save_unload_err ='';
                    $fileUnloadError ='';
                    if(isset($_FILES['unload_file'])){
                        //print_r($_FILES['file']);
                        for($i=0; $i<count($_FILES['unload_file']['name']); $i++){
                            $f_name = basename( $_FILES['unload_file']['name'][$i]);
                            $fileError = $Object->upload_exception($_FILES['unload_file']['error'][$i]);
                            $target_path = $target_path_temp . $f_name;

                            $file_size = $_FILES['unload_file']['size'][$i];
                            $allowed_ext = array("jpg","jpeg","png","pdf");

                            $file_ext_temp = explode('.',$f_name);
                            $file_ext = strtolower(end($file_ext_temp));

                            if(in_array($file_ext, $allowed_ext)){
                                if($fileError == "" && $file_size < 10000000){
                                    if(move_uploaded_file($_FILES['unload_file']['tmp_name'][$i], $target_path)) {
                                        if(!is_numeric(array_search($f_name,$unload_existing_file_temp))){
                                            $file_name_temp = $target_path_return.$f_name;
                                            array_push($file_name_unload_return,$file_name_temp);
                                            $list_file_unload_name = ($list_file_unload_name=='')?$f_name:$list_file_unload_name.','.$f_name;
                                        }
                                    } else{
                                        $files_save_unload_err = "There was an error uploading the file, please try again!";
                                    }
                                }
                            }else{
                                $fileUnloadError ="File is not in format";
                            }
                        }
                    }

                    if($list_file_unload_name !=''){
                        if($status !="CLOSED") $status ="DELIVERED";
                        $unload_existing_file= ($unload_existing_file=='')?$list_file_unload_name : $unload_existing_file.','.$list_file_unload_name ;
                    }
                    //use to database
                    $file_unload_name =$unload_existing_file;

                    $file_unload_database = $Object->return_id("select file_delivery_name from assign_task where id = '{$id}'",'file_delivery_name');

                    if($file_unload_database !='' && $file_unload_database !=null){
                        $file_unload_database = explode(',',$file_unload_database);
                        $unload_existing_file = explode(',',$unload_existing_file);

                        $list_file_delete = array_diff($file_unload_database,$unload_existing_file);
                        //delefile
                        if(count($list_file_delete) > 0){
                            foreach($list_file_delete as $item){
                                $filePathTemp = $target_path_temp.$item;
                                if(file_exists($filePathTemp)){
                                    $err = unlink($filePathTemp);
                                }
                            }
                        }
                    }
                    //////////////////////
                    if(isset($deliverydate) && $deliverydate !=''){
                        if(isset($deliverytime) && $deliverytime !=''){
                            $deliverydate = $deliverydate." ".$deliverytime;
                        }
                    }

                    if($status_exsiting =='DELIVERED') $status = $status_exsiting;

                    $sku_existing_task = $Object->return_id("SELECT product_sku FROM `assign_task` WHERE id  = '{$id}'","product_sku");
                    $order_existing_task = $Object->return_id("SELECT assign_order FROM `assign_task` WHERE id = '{$id}'","assign_order");

                    $result = $Object->updateTask($id,$actionset,$assign_id,$content,$customer_id,
                        $doneDate,$dueDate,$status,$taskName,$time,$alert,$urgent,
                        $assign_order,$assign_driver_id,$deliverydate,$product_sku,
                        $file_upload_name,$file_unload_name);

                    if(is_numeric($result) && $result!=""){
                        //update sku
                        if($assign_order !=''){
                            if(trim($sku_existing_task) !=trim($product_sku)){
                                //restore sku for old order
                                $order_sku_existing = $Object->return_id("SELECT order_sku FROM `quote` WHERE order_id = '{$order_existing_task}'","order_sku");
                                $order_sku_existing = json_decode($order_sku_existing,true);
                                $order_sku_processed = array();
                                foreach($order_sku_existing as $itm){
                                    $arr_temp =array();
                                    if($itm["sku"] == $sku_existing_task){
                                        $arr_temp["quantity"] = 0;
                                        $arr_temp["quantity"] = $itm["quantity"] +1;
                                        $arr_temp["sku"]  = $itm["sku"];
                                        array_push($order_sku_processed,$arr_temp);
                                    }else{
                                        array_push($order_sku_processed,$itm);
                                    }
                                }

                                $sku_list =''; $container_amount =0;
                                foreach($order_sku_processed as $itm){
                                    if($itm["quantity"] > 0){
                                        $sku_list = ($sku_list =="")? trim($itm["sku"]): $sku_list.",".trim($itm["sku"]);
                                        $container_amount +=$itm["quantity"] ;
                                    }
                                }

                                $order_sku_processed =json_encode($order_sku_processed) ;
                                $array_key_value = array("sku_list"=>$sku_list,
                                    "available_container_amount"=>$container_amount,
                                    "order_sku"=>$order_sku_processed);
                                $array_primary =array("order_id"=>$order_existing_task);

                                $Object->update_table("quote",$array_primary,$array_key_value);
                                //update sku
                                $order_sku = $Object->return_id("SELECT order_sku FROM `quote` WHERE order_id = '{$assign_order}'","order_sku");
                                $order_sku = json_decode($order_sku,true);
                                $order_sku_processed = array();
                                foreach($order_sku as $itm){
                                    $arr_temp =array();
                                    if($itm["sku"] == $product_sku){
                                        $arr_temp["quantity"] = 0;
                                        if($itm["quantity"] > 0 ); $arr_temp["quantity"] = $itm["quantity"] -1;
                                        $arr_temp["sku"]  = $itm["sku"];
                                        array_push($order_sku_processed,$arr_temp);
                                    }else{
                                        array_push($order_sku_processed,$itm);
                                    }
                                }

                                $sku_list =''; $container_amount =0;
                                foreach($order_sku_processed as $itm){
                                    if($itm["quantity"] > 0){
                                        $sku_list = ($sku_list =="")? trim($itm["sku"]): $sku_list.",".trim($itm["sku"]);
                                        $container_amount +=$itm["quantity"] ;
                                    }
                                }

                                $order_sku_processed =json_encode($order_sku_processed) ;
                                $array_key_value = array("sku_list"=>$sku_list,
                                    "available_container_amount"=>$container_amount,
                                    "order_sku"=>$order_sku_processed);
                                $array_primary =array("order_id"=>$order_existing_task);

                                $Object->update_table("quote",$array_primary,$array_key_value);
                                ///////
                            }
                        }
                        $is_send =0;
                        if($assign_order !='' && $assign_driver_id !=''){
                            $deliverydate = $Object->is_Date1($deliverydate);
                            if($deliverydate !=''){
                                $available_container = $Object->return_id("SELECT available_container_amount FROM `quote` WHERE order_id = '{$assign_order}'","available_container_amount");
                                if($available_container ==0 || $available_container ==""){
                                    $Object->Order_update_one_field($assign_order,"order_status" ,"Scheduled for delivery");
                                }
                            }
                            //send email to driver
                            $driver_info = $Object->getContactEmail_ID($assign_driver_id);
                            //$info = $Object->depot_and_customer_address($assign_order);
                            //$customer = $Object->customer_info_order($assign_order);
                            $depot_customer = $Object->depot_customer_by_sku($product_sku);

                            $order_total = $Object->return_id("SELECT `total` FROM `quote` WHERE order_id  = '{$assign_order}'","total");
                            $driver_total = $Object->return_id("SELECT `driver_total` FROM `assign_task` WHERE id  = '{$id}'","driver_total");

                            $depot_info ='
                        <tr><td><strong>DEPOT INFO</strong></td> </tr>
                        <tr><td>Name: '.$depot_customer["depot_name"].'</td></tr>
                        <tr><td>Phone number: '.$depot_customer["depot_phone"].'</td></tr>
                        <tr> <td>Address: '.$depot_customer["depot_address"].'</td></tr>
                        <tr> <td>Depot to Customer: '.$depot_customer["distance"].' mile</td></tr>
                        <tr> <td>Order total:$ '.number_format($order_total,2,".",",").'</td></tr>
                        <tr><td></td></tr>
                        <tr> <td></td></tr>
                        <tr> <td></td></tr>';

                            $driver_tr ='
                        <tr><td><strong>DRIVER INFO</strong></td> </tr>
                        <tr> <td>Driver total:$ '.number_format($driver_total,2,".",",").'</td></tr>
                        <tr><td></td></tr>
                        <tr> <td></td></tr>
                        <tr> <td></td></tr>';

                            $customer_info = '
                        <tr><td><strong>CUSTOMER INFO</strong></td></tr>
                        <tr> <td>Name: '.$depot_customer["shipping_customer_name"].'</td></tr>
                        <tr> <td>Phone number: '.$depot_customer["shipping_phone"].'</td></tr>
                        <tr><td>Email: '.$depot_customer["email_phone"].'</td></tr>
                        <tr><td>Address: '.$depot_customer["shipping_address"].'</td></tr>';

                            $body='<html lang="en">
                      <head>
                        <meta charset="UTF-8">
                        <title>Information for delivery goods</title>
                        <link rel="stylesheet" href="http://phptopdf.com/bootstrap.css">
                        <style>
                          @import url(http://fonts.googleapis.com/css?family=Bree+Serif);
                          body, h1, h2, h3, h4, h5, h6{
                          font-family: "Bree Serif", serif;
                          }
                        </style>
                      </head>

                      <body>
                            <table>
                                '.$depot_info.'
                                '.$driver_tr.'
                                '.$customer_info.'
                            </table>
                      </body>
                    </html>';

                            $subject ="Assign a Container";
                            $to_name =$driver_info["driver_name"];
                            $email =$driver_info["primary_email"];

                            $Ob_manager = new EmailAdress();
                            $domain_path = $Ob_manager->domain_path;
                            $from_name=$Ob_manager->admin_name ;
                            $from_email=$Ob_manager->admin_email;
                            $from_id=$Ob_manager->admin_id;

                            $is_send =  $Object->mail_to($from_name,$to_name,$email,$subject,$body);

                            /////////////////////////
                        }
                        $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','AUTH'=>true,'id'=>$id,
                            'files_save_unload_err'=>$files_save_unload_err,
                            'fileUnloadError'=>$fileUnloadError,
                            'file_delivery'=>$file_name_unload_return,
                            'files_save_err'=>$files_save_err,
                            'fileError'=>$fileError,
                            'file_pickup'=>$file_name_upload_return,
                            'sent_email'=>$is_send);
                    }else{
                        $ret = array('SAVE'=>'FAIL','ERROR'=>$result,'AUTH'=>true,'id'=>$id);
                    }
                }else{
                    $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>true);
                }

            }else{
                $ret = array('SAVE'=>'FAIL','ERROR'=>'Task closed','AUTH'=>true);
            }
        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }
    }

    $Object->close_conn();
    echo json_encode($ret);




