<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.calendar.php';
    $Object = new Calendar();

    $EXPECTED = array('token','task_id','driver_id','delivery_date');

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
        $ret = $Object->create_calendar($task_id,$driver_id,$delivery_date);
        if($driver_id!=''){
            $deliverydate = $Object->is_Date1($delivery_date);
            include_once './lib/class.task.php';
            $Obj_task = new Task();
            //send email to driver
            $driver_info = $Obj_task->getContactEmail_ID($driver_id);
            $order_id = $Object->return_id("SELECT assign_order FROM assign_task WHERE id = '{$task_id}'","assign_order");
            $product_sku = $Object->return_id("SELECT product_sku FROM assign_task WHERE id = '{$task_id}'","product_sku");
            //update task
            $driver_total =0;
            if($order_id !='' && $product_sku !='' && $driver_id !=''){
                $obj_depot = new Depot();
                $driver_total = $obj_depot->calulate_driver_rate($order_id,$product_sku,$driver_id);
                if($driver_total > 0){
                    $array_primary =array('id'=>$task_id);
                    $arr_key_value =array('driver_total'=>$driver_total);
                    $obj_depot->update_table('assign_task',$array_primary,$arr_key_value);
                }
            }

            $ret['driver_total'] = $driver_total;

            $order_total = $Object->return_id("SELECT `total` FROM `quote` WHERE order_id  = '{$order_id}'","total");
            $depot_customer = $Obj_task->depot_customer_by_sku($product_sku);
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
            //print_r($body); die();
            $subject ="Assign a Container";
            $to_name =$driver_info["driver_name"];
            $email =$driver_info["primary_email"];

            $Ob_manager = new EmailAdress();
            $domain_path = $Ob_manager->domain_path;
            $from_name=$Ob_manager->admin_name ;
            $from_email=$Ob_manager->admin_email;
            $from_id=$Ob_manager->admin_id;
            //$is_send =0;
            $is_send =  $Object->mail_to($from_name,$to_name,$email,$subject,$body);
            $ret["email_sent"] =$is_send;
            /////////////////////////
        }
        //send email
        $ret['ERROR'] =$ret['driver'];
    }
    $Object->close_conn();
    echo json_encode($ret);




