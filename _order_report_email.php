<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.report.order.php';
    $Object = new ReportOrder();
    $EXPECTED = array('token','from_date','to_date','status','text_search','cursor','limit');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed','sent'=>'');
    }else{
        $ret = $Object->order_report($from_date,$to_date,$status,$text_search,$cursor,$limit);
        if(count($ret['list']) >0){
            $tb1 = '<table>
                    <tr><td><b>Product Sku</b></td><td><b>Quantity</b></td></tr>
                </table>';
            $task =''; $customer =''; $order =''; $depot ='';
            foreach($ret['list'] as $item){
                $products ='';
                if($item['order_sku'] !=null && $item['order_sku'] !=''){
                    $item['order_sku'] = json_decode($item['order_sku'], true);
                    if(count($item['order_sku']) >0){
                        foreach($item['order_sku'] as $item1){
                            $products .='<tr>
                            <td>'.$item1["sku"].'</td><td>'.$item1["quantity"].'</td>
                        </tr>';
                        }
                    }
                }

                $task ='
                        <tr><td><strong>Task INFO</strong></td> </tr>
                        <tr><td>Task name: '.$item["assign_task_taskName"].'</td></tr>
                        <tr><td>Status: '.$item["assign_task_status"].'</td></tr>
                        <tr> <td>Task id: '.$item["assign_task_id"].'</td></tr>
                        <tr> <td>SKU: '.$item["assign_task_product_sku"].' miles</td></tr>
                        <tr> <td>Driver name: '.$item["assign_task_driver_name"].'</td></tr>
                        <tr> <td>Driver id: '.$item["assign_task_driver_id"].'</td></tr>
                        <tr> <td>Delivery date: '.$item["assign_task_delivery_date"].'</td></tr>
                        <tr> <td>Driver total: $ '.number_format($item["assign_task_driver_total"],2,".",",").'</td></tr>
                        <tr> <td>Paid: $ '.number_format($item["driver_total_payment"],2,".",",").'</td></tr>
                        <tr><td></td></tr>
                        <tr> <td></td></tr>
                        <tr> <td></td></tr>';

                $customer ='
                        <tr><td><strong>CUSTOMER INFO</strong></td> </tr>
                        <tr><td>Customer name: '.$item["shipping_customer_name"].'</td></tr>
                        <tr><td>Customer id: '.$item["b_ID"].'</td></tr>
                        <tr> <td>Address: '.$item["shipping_address"].'</td></tr>
                        <tr> <td>City: '.$item["shipping_city"].'</td></tr>
                        <tr> <td>State: '.$item["shipping_state"].'</td></tr>
                        <tr> <td>Email: '.$item["shipping_email_phone"].' miles</td></tr>
                        <tr> <td>Phone: '.$item["shipping_phone"].'</td></tr>
                        <tr><td></td></tr>
                        <tr> <td></td></tr>
                        <tr> <td></td></tr>';

                $order ='
                        <tr><td><strong>ORDER</strong></td> </tr>
                        <tr><td>Title: '.$item["order_title"].'</td></tr>
                        <tr><td>Discount code: '.$item["discount_code"].'</td></tr>
                        <tr> <td>Products: '.$products.'</td></tr>
                         <tr> <td>Total: $ '.number_format($item["total"],2,".",",").'</td></tr>
                        <tr> <td>Paid: $ '.number_format($item["payment"],2,".",",").'</td></tr>
                        <tr> <td>Create date: '.$item["createTime"].'</td></tr>
                        <tr><td></td></tr>
                        <tr> <td></td></tr>
                        <tr> <td></td></tr>';

                $depot ='
                        <tr><td><strong>DEPOT</strong></td> </tr>
                        <tr><td>Name: '.$item["quote_temp_depot_name"].'</td></tr>
                        <tr> <td>Depote id: '.$item["quote_temp_depot_id"].'</td></tr>
                        <tr><td>Container type name: '.$item["quote_temp_container_type_name"].'</td></tr>
                        <tr> <td>Cost: '.$item["container_cost"].'</td></tr>
                        <tr> <td>Container price: '.$item["quote_temp_container_rate"].'</td></tr>
                        <tr> <td>Price: '.$item["quote_temp_best_price"].'</td></tr>
                        <tr> <td>Rate/mile: '.$item["quote_temp_rate_mile"].'</td></tr>
                        <tr> <td>Distance: '.$item["quote_temp_distance"].'</td></tr>
                        <tr><td></td></tr>
                        <tr> <td></td></tr>
                        <tr> <td></td></tr>';

            }

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
                                '.$task.'
                                '.$customer.'
                                '.$order.'
                                '.$depot.'
                            </table>
                      </body>
                    </html>';

            $subject ="Report Info";
            $to_name ='Sara';
            $email ='sara@strongcontainers.com';

            $Ob_manager = new EmailAdress();
            $domain_path = $Ob_manager->domain_path;
            $from_name=$Ob_manager->admin_name ;
            $from_email=$Ob_manager->admin_email;
            $from_id=$Ob_manager->admin_id;
           // echo "<pre>"; print_r($body); echo "</pre>";
           // print_r($body); //die();
            $is_send =  $Object->mail_to($from_name,$to_name,$email,$subject,$body);
            $ret = array('ERROR'=>'','sent'=>$is_send);
        }
    }
    $Object->close_conn();
    echo json_encode($ret);




