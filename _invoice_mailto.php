<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.invoice_sendmail.php';
require_once __DIR__ . '/lib/vendor_Mpdf/autoload.php';
    $Object = new InvoiceMail();

    $EXPECTED = array('token','invoice_num','emails','subtitle','content','jwt','private_key');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    //--- validate
$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('html'=>'','ERROR'=>'Authentication is failed');
}else{
    $isAuth = $Object->auth($jwt,$private_key);

    $isAuth['AUTH']=true;
    $errObj['errorMsg']="Authentication is failed";
    if($isAuth['AUTH']){
        $ret = array('send'=>false,'AUTH'=>true);
        //get admin info
        //get admin info
        $Ob_manager = new EmailAdress();
        $domain_path = $Ob_manager->domain_path;
        $from_name=$Ob_manager->admin_email;
        $from_email=$Ob_manager->admin_name;
        $from_id=$Ob_manager->admin_id;
        $api_path = $Ob_manager->api_path;

        $HTMLContent = $Object->getInvInfo_invID($invoice_num);

        // PUT YOUR HTML IN A VARIABLE
        $my_html='<html lang="en">
              <head>
                <meta charset="UTF-8">
              </head>
              <body>
              ' . $HTMLContent .'
              </body>
            </html>';
        //filename
        $d = date('Y-m-d H:i:s');
        $temp = explode(" ",$d);
        $temp2 = explode(":",$temp[1]);
        $fileName ='email_content'.'-'. $temp[0].'-'.$temp2[0].'-'.$temp2[1].'-'.$temp2[2].".pdf";

        $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/lib/vendor_Mpdf/mpdf/mpdf/tmp']);
        $mpdf->WriteHTML($my_html,2);
        $pathname ="/photo/email_attachment/";
        $photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$pathname.$fileName;
        $mpdf->Output($photoPathTemp,'F');
        $HTMLContentLinks = '<p><a href="'.$api_path.'/photo/email_attachment/'. $fileName .'">Click Here to Download Your Invoice</a></p><p>Attached is your buyer contract. <br />~HostedAttachment_132242~</p>';
        //send mail
        $info = explode(';',$emails);
        //print_r($info); die();
        if(count($info)>0){
            //content 'subtitle'
            $agentEmailContent = $content;
            $subject=$subtitle;
            $agentEmailContent1 = $Object->protect($agentEmailContent);
            //send to
            foreach($info as $email){
                $to_name = '';
                $to_email = $email;
                $status = '';
                //check email
                if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                    $status = 'Bounce';
                }
                //check email
                $domain = substr($to_email, strpos($to_email, '@') + 1);
                if  (!checkdnsrr($domain) !== FALSE) {
                    $status = 'Bounce';
                }

                $id_tracking = $Object->insertTrackingEmail($to_email,$subject,$agentEmailContent1,$from_id,$status);
                if(empty($status)){
                    $is_send =  $Object->mail_to($from_name,$to_name,$to_email,$subject,$agentEmailContent,$id_tracking,$photoPathTemp,$fileName);
                    if($is_send==1){
                        $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                        $ret = array('send'=>true,'AUTH'=>true);
                    }
                }
            }
            //

        }


    }else{
        $ret = array('send'=>'','AUTH'=>false);
    }
}
    $Object->close_conn();
    echo json_encode($ret);




