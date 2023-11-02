<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.depot.php';
require_once __DIR__ . '/lib/vendor_Mpdf/autoload.php';
    $Object = new Depot();

    $EXPECTED = array('token','cus_name','email_phone','cus_address','cus_city',
    'cus_state','cus_zipcode','cus_phone',
    'tran_id','payment_type','amount','discount_code');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    //--- validate
$isAuth = true;//$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('error'=>'Authentication is failed');
}else{
    $ret = array('error'=>'In valid data');
    $data = $_POST["data_post"];
    if(count($data) > 0) {
        $retun1 = $Object->add_quote_temp($cus_name,$email_phone, $cus_address, $cus_city,
            $cus_state,$cus_zipcode,$cus_phone,
            $data);
        $code = $retun1['code'];

        $ret = $Object->saveCustomerInfo($cus_name,$cus_address,
            $cus_city,$cus_state,$cus_zipcode,$cus_phone,$code,
            $tran_id,$payment_type,$amount,$discount_code);
        $ret["quote_temp_id"] = $retun1["quote_temp_id"];
        $ret["code"] = $code;
        //echo "<pre>";print_r($ret);echo "</pre>";
        if(isset($ret["order_id"])){
            if(is_numeric($ret["order_id"]) && $ret["order_id"] !=''){
                //send email
                $status ='';
                $email = $email_phone;
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $status = 'Bounce';
                }
                //check email
                $domain = substr($email, strpos($email, '@') + 1);
                if  (!checkdnsrr($domain) !== FALSE) {
                    $status = 'Bounce';
                }
                $status ='';
                if(empty($status)){
                    //get admin info
                    $Ob_manager = new EmailAdress();
                    $domain_path = $Ob_manager->domain_path;
                    $from_name=$Ob_manager->admin_name ;
                    $from_email=$Ob_manager->admin_email;
                    $from_id=$Ob_manager->admin_id;

                    $quote = $Object->getQuoteTemp($code);
                    $quote["more_info"]= $Object->quoteTemp($code);

                    $total =0;
                    $tr='';
                    foreach($quote["quotes"] as $item){
                       $total_line = $item["best_price"] * $item["qty"];
                        $total = $total + $total_line;
                        $tr .='<tr>
                        <td>'.$item["prod_name"].'</td>
                        <td>'.$item["qty"].'</td>
                        <td style="text-align: right">$'.number_format($total_line,2,".",",").'</td>
                        </tr>';
                    }

                    $div ='';
                    $div .='<div style="text-align: center; width: 100%"><strong>INVOICE</strong></div>';
                    $div .='<div style="width: 100%">Customer name: '.$quote["more_info"]["shipping_customer_name"].'</div>';
                    $div .='<div style="width: 100%">Customer address: '.$quote["more_info"]["shipping_address"].'</div>';
                    $div .='<div style="width: 100%">Customer Phone: '.$quote["more_info"]["shipping_phone"].'</div>';

                    $div .='<div style="width: 100%"><strong>Order</strong></div>';
                    $div .='<div style="width: 100%">Order title: '.$quote["more_info"]["order_title"].'</div>';
                    $div .='<div style="width: 100%">Order status: '.$quote["more_info"]["order_status"].'</div>';

                    $table =' <table style="width:100%;">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Container name</th>
                                <th style="width: 20%;">Quality</th>
                                <th style="width: 30%;">Total line</th>
                            </tr>
                        </thead>
                        <tbody>'.$tr.'
                        <tr style="text-align: right">
                        <td colspan="2" class="p-r20" style="text-align: right"><strong>Total</strong></td>
                        <td style="text-align: right">$'.number_format($total,2,".",",").'</td>
                        </tr>
                        </tbody>
                    </table>';

                    $html='<html lang="en">
                      <head>
                        <meta charset="UTF-8">
                        <link rel="stylesheet" href="http://phptopdf.com/bootstrap.css">

                      </head>
                      <body>
                        '.$div.'
                        <div class="m_t10" style="width: 100%">'.$table.'</div>
                      </body>
                    </html>';
                    //print_r($file_temp);
                    //die($html);
                    $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/lib/vendor_Mpdf/mpdf/mpdf/tmp']);
                    //$mpdf->WriteHTML($html);
                    $stylesheet = file_get_contents('./css_api/api_css.css');
                    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
                    $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);

                    $file_name = "invoice"."_".$ret["inv_id"];
                    $pathname ="photo/email_attachment/";
                    //$file_temp = 'C:/xampp/htdocs/CRMAPI/'.$pathname.basename($file_name);
                    $file_temp = $_SERVER["DOCUMENT_ROOT"].$pathname.$file_name.".pdf";
                   // print_r($file_temp);
                    $mpdf->Output($file_temp,'F');

                    $hrf =$domain_path."/quote.php?id=".$code;
                    $url='<a href='.$hrf.' > Click here to view your order status</a>';

                    $subject ="Your Order";
                    $to_name = $cus_name;
                    $body ='<p>Hi</p>
                    <p>'.$url.'</p>';
                    $is_send =0;
                    //print_r($html); die();
                    $is_send =  $Object->mail_to($from_name,$to_name,$email,$subject,$body,'',$file_temp,$file_name);
                    $ret["email_sent"] =$is_send;

                    if(is_file($file_temp)){
                    unlink($file_temp);
                    }
                }
              //////////////////
            }
        }
    }
}
    $Object->close_conn();
    echo json_encode($ret);




