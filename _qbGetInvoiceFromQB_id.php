<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
    $config = include('qbconfig.php');

    $qbComIDconfig = include('_qbcompanyID.php');
    include_once './lib/class.qbInvoice.php';
    include_once './lib/class.quickbookconnect.php';

    //include_once   'qbClassVendorAndCustomer.php';
    $Object = new Quickbookconnect();

    $EXPECTED = array('id');
    
    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        }else if (!empty($_GET[$key])) {
            ${$key} = $Object->protect($_GET[$key]);
        } else {
            ${$key} = NULL;
        }
    }

$ret = array();
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

    //$CompanyName="IT com";

    $ObjectVC = new QBInvoice();
    $ret = $ObjectVC->qbGetInvoiceFromQBByInvoiceID($dataService,$id);
    //Get product by ItemRef and create order_product
    $products_ordered = array();
    if($ret['product']>0){
        foreach($ret['product'] as $item){
          $prod= $Object->qbGetProductByItemRef($item['ItemRef']);
            if(count($prod)>0){
                $products_ordered[] = array('id'=>$prod['ID'],
                'sku'=>$prod['SKU'],
                    'prod_name'=>$prod['prod_name'],
                    'prod_class'=>$prod['prod_class'],
                    'quantity'=>$item['Qty'],
                    'price'=>$item['UnitPrice'],
                    'line_total'=>$item['Amount']
                );
            }
        }
    } //end if $item_rsl['product']>0
    //get and set bill_to
    $qb_customer_id_info = $Object->qbGetContactID_qb_customer_id($ret['CustomerRef']);
    $bill_to=0;
    if(isset($qb_customer_id_info["ID"])) $bill_to = $qb_customer_id_info["ID"];

    //get order
    $order_info = $Object->qbGetOrdertByTaxId($ret['Id']);
    //set order infomation
    $order_info['bill_to'] =$bill_to;
    $order_info['total'] =$ret['TotalAmt'];
    $order_info['order_total'] =$ret['TotalAmt'];
    //$order_info['balance'] =$ret['Balance'];
    $order_info['products_ordered'] = $products_ordered;

    //
}

$Object->close_conn();
echo json_encode($order_info);

