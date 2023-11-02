<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.task.php';
    $Object = new Task();

    $EXPECTED = array('token','id');
    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth = true;//$Object->basicAuth($token);
    $is_send =0;
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed');
    }else{
        //send email to driver
        $task = $Object->getTaskByID($id);
        $driver_total = $task['driver_total'];
        $assign_order = $task['assign_order'];
        $assign_sku = $task['product_sku'];
        $driver_info = $Object->getContactEmail_ID($task['assign_id']);

        $order_total = $Object->return_id("SELECT `total` FROM `quote` WHERE order_id  = '{$assign_order}'","total");
        $shipping_contact_id = $Object->return_id("SELECT `bill_to` FROM `quote` WHERE order_id  = '{$assign_order}'","bill_to");

        $info = $Object->depot_and_customer_address($assign_order);
        $customer = $Object->customer_info_order($assign_order);
        $depot = $Object->depot_customer_by_sku($assign_sku);
        $container_type_name = $depot['container_type_name'];

       $table_box = '<table border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100.0%">
            <tbody>
                <tr>
                    <td style="background:#00c0c8;padding:15.0pt 15.0pt 15.0pt 15.0pt">
                        <p align="center" style="text-align:center"><span style="color:white">Please click button below to View and Accept the Rate Con<u></u><u></u></span></p>
                    </td>
                </tr>
            </tbody>
        </table>';
        $order_info ='
                        <tr><td><strong>Shipping Container Suppliers</strong></td> </tr>
                        <tr><td>Order Item: #'.$info["order_title"].'</td></tr>
                        <tr><td></td></tr>
                        <tr> <td></td></tr>
                        <tr> <td></td></tr>';

        $depot_info ='
                        <tr><td><strong>Container INFO</strong></td> </tr>
                        <tr><td>Name:'.$container_type_name.'('.$assign_sku.')</td></tr>
                        <tr><td></td></tr>
                        <tr> <td></td></tr>
                        <tr> <td></td></tr>';

        $driver_tr ='
                        <tr><td><strong>DRIVER INFO</strong></td> </tr>
                        <tr> <td>Driver total:$ '.number_format($driver_total,2,".",",").'</td></tr>
                        <tr><td></td></tr>
                        <tr> <td></td></tr>
                        <tr> <td></td></tr>';

        $Ob_manager = new EmailAdress();
        $domain_path = $Ob_manager->domain_path;
        $from_name=$Ob_manager->admin_name ;
        $from_email=$Ob_manager->admin_email;
        $from_id=$Ob_manager->admin_id;

        $code =base64_encode($id);
        $code2 = base64_encode($task['assign_id']);
        $code =$code.'@2&45$'.$code2;

        $hrf =$domain_path."/purchase_competitor.php?id=".$code;

        $link = '<p align="center" style="text-align:center">
                <a href='.$hrf.' >
                    <span style="font-size:11.5pt;color:white;background:#fb9678;text-decoration:none">View Rate Con</span>
                </a>
              </p>';
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
                            '.$table_box.'
                            <table>
                                '.$order_info.'
                                '.$depot_info.'
                                '.$driver_tr.'
                                <tr><td>Click the link to view and Accept the Rate Confirmation<td></tr>
                            </table>
                            '.$link.'
                      </body>
                    </html>';

        $subject ="Strong containers - to drivers";
        $to_name =$driver_info["driver_name"];
        $email =$driver_info["primary_email"];
        //print_r($body);
        //die($hrf);
        $is_send =  $Object->mail_to($from_name,$to_name,$email,$subject,$body);
        $ret['sent']=$is_send;
    }

    $Object->close_conn();
    echo json_encode($ret);




