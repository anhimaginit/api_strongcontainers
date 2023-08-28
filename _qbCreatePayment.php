<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    $config = include('qbconfig.php');
    include_once '_qbviacurl.php';
    include_once './lib/class.qbPayment.php';
    include_once './lib/class.quickbookconnect.php';
    $qbComIDconfig = include('_qbcompanyID.php');

       $Object = new Quickbookconnect();

    $EXPECTED = array('token',
        'jwt','private_key','CustomerRef','TotalAmt','Amount','invoice_id','pay_id');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        }else if (!empty($_GET[$key])) {
            ${$key} = $Object->protect($_GET[$key]);
        } else {
            ${$key} = NULL;
        }
    }
//$isAuth =$Object->basicAuth($token);
$isAuth=true;
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed','CreatedId'=>'');
    echo json_encode($ret);
}else{
    //print_r($accessTokenKey); die();
    $ret = array('ERROR'=>'','CreatedId'=>'');
    $ID='';
    $token_info= $Object->getQBToken();
    if(count($token_info)>0){
        $dataService=array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'accessTokenKey' =>$token_info['accessTokenKey'],
            'refreshTokenKey' => $token_info['refreshTokenKey'],
            'QBORealmID' =>$qbComIDconfig['QBORealmID'],
            'baseUrl' => $qbComIDconfig['baseUrl']
        );

        $Country="USA";
        //Get invoice, order_id, customer_id
        $qb_inv=$Object->qbGetQBInvoiceID_invoiceID($invoice_id);
        if(empty($qb_inv["TxnId"])){

            $curlObj= new QBviaCurl();
            $url = "_qbCreateInvoice.php";

            $data = array(
                "contactID"=>$qb_inv["customer"],
                "invoiceID"=>$invoice_id,
                "orderID"=>$qb_inv["order_id"]);

            $qbInfo=$curlObj->httpost_curl($url,$data);
            $qbInfo_decode = json_decode($qbInfo,true);
            $TxnId=$qbInfo_decode['CreatedId'];
        }else{
            $TxnId=$qb_inv["TxnId"];
        }

        //end create qb invoice
        //get qb_customer_id
        $c_info= $Object->qbGetContact_ID($qb_inv["customer"]);
        //print_r($TxnId."---");
        //print_r($c_info); die();
        $CustomerRef = $c_info["qb_customer_id"];
        //
        if(is_numeric($CustomerRef)&& is_numeric($TxnId)){
            $ObPayment = new QBPayment();

            $ID = $ObPayment->qbCreatePayment($dataService,
                $CustomerRef,$TxnId,$Amount);

            if(is_numeric($ID["CreatedId"])){
                //update payacct table with qbpaymentID
                $Object->qbUpdateQBPaymentIntoPayaccTable($pay_id,$ID["CreatedId"]);

                $ret = array('ERROR'=>'','CreatedId'=>$ID["CreatedId"]);
            }else{
                $ret = array('ERROR'=>$ID["TheStatusCodeIs"],'TheHelperMessageIs'=>$ID["TheHelperMessageIs"],
                    'TheResponseMessageIs'=>$ID["TheResponseMessageIs"],'CreatedId'=>'');
            }
        }else{
            $ret = array('ERROR'=>'CustomerRef is required','CreatedId'=>'');
            if(is_numeric($CustomerRef)){
                $ret = array('ERROR'=>'TaxId is required','CreatedId'=>'');
            }

        }

    }

    echo json_encode($ret);
}

