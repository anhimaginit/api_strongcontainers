<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

$tokenconfig = include('tokenconfig.php');
include_once '_qbviacurl.php';
//$payLoad = file_get_contents("php://input");
$name ="/photo/products/quickbookswebhook.txt";
$photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$name;

$name1 ="/photo/products/quickbookswebhook1.txt";
$photoPathTemp1 = $_SERVER["DOCUMENT_ROOT"].$name1;
//$upload = file_put_contents($photoPathTemp, $payLoad);
  //---------------------------------- c82b657a-113d-4cb2-b2c3-fce3945d4ce2
//$webhook_token =  "6eb03ff2-8b1a-408e-b392-41487201401e";//for warranty
//$webhook_token = "c82b657a-113d-4cb2-b2c3-fce3945d4ce2";//forproduct

$webhook_token = $tokenconfig["webhook_token"];
$is_verified = false;

if (isset($_SERVER['HTTP_INTUIT_SIGNATURE']) && !empty($_SERVER['HTTP_INTUIT_SIGNATURE'])) {
    $payLoad = file_get_contents("php://input");
    if (isValidJSON($payLoad)) {
        $payloadHash = hash_hmac('sha256', $payLoad, $webhook_token);
        $singatureHash = bin2hex(base64_decode($_SERVER['HTTP_INTUIT_SIGNATURE']));
        if($payloadHash == $singatureHash) {
            //login CRM
            $data = array(
                'token'=>$tokenconfig["token"],
                'primary_email'=>'crm@at1ts.com',
                'primary_postal_code'=>'84401',
                'login_type'=>2,
                'type'=>'Policy Holder'
            );

            $curlObj= new QBviaCurl();
            $url = "_login.php";
            $rsl=$curlObj->httpost_curl($url,$data);
            $login_rsl =json_decode($rsl);
            unset($curlObj);

            $jwt=$login_rsl->contact->jwt;
            $private_key = $login_rsl->contact->ID;
            $create_by=$private_key;
            $submit_by=$private_key;
            $gps='{}';
            //end login
            //for test
            file_put_contents($photoPathTemp, $payLoad);
            // verified....OK
            $is_verified = true;
            $payLoad_data = json_decode($payLoad, true);

            foreach ($payLoad_data['eventNotifications'] as $event_noti) {
                $realmId = $event_noti['realmId'];	//	this is your company-ID from Intuit
                // now do whatever you want to do with data received from Intuit...
                foreach($event_noti['dataChangeEvent']['entities'] as $entries) {
                    //
                     switch($entries['name']){
                         case 'Employee':
                             $id= $entries['id'];
                             $operation= $entries['operation'];
                             $contact_rsl =array();
                             if(is_numeric($id)){
                                 $data = array('id'=>$id,'type'=>'Employee');
                                 $curlObj= new QBviaCurl();
                                 $url = "_qbGetEmployeeVendorCustomer_id.php";
                                 $rsl=$curlObj->httpost_curl($url,$data);
                                 $contact_rsl =json_decode($rsl,true);
                                 unset($curlObj);
                                 //contact data
                                 $contact_rsl["token"] =$tokenconfig["token"];
                                 $contact_rsl["contact_type"] ="Employee";
                                 $contact_rsl["jwt"] =$jwt;
                                 $contact_rsl["private_key"] =$private_key;
                                 $contact_rsl["create_by"] =$create_by;
                                 $contact_rsl["submit_by"] =$submit_by;
                             }
                             //create a new contact on CRM when Webhook call
                             if($operation=='Create' && is_numeric($id)){
                                 $contact_rsl["gps"] =$gps;

                                 //add contact to CRM
                                 $curlObj= new QBviaCurl();
                                 $url = "_contactAddNew.php";
                                 $rsl=$curlObj->httpost_curl($url,$contact_rsl);
                                 //for test
                                 //file_put_contents($photoPathTemp1, $rsl);
                                 //$contact_rsl =json_decode($rsl,true);
                                 //$contact_rsl1 = json_encode($contact_rsl);
                                 //file_put_contents($photoPathTemp, $contact_rsl1);
                                 unset($curlObj);
                             }
                                //create the contact on CRM when Webhook call
                             if($operation=='Update' && is_numeric($id)){
                                 //update contact to CRM
                                 $curlObj= new QBviaCurl();
                                 $url = "_qbContactUpdate_qbid.php";
                                 $rsl=$curlObj->httpost_curl($url,$contact_rsl);
                                 unset($curlObj);
                                 //
                             }
                             break; //END Employee

                         case 'Customer':
                             $id= $entries['id'];
                             $operation= $entries['operation'];
                             $contact_rsl =array();
                             if(is_numeric($id)){
                                 $data = array('id'=>$id,'type'=>'Customer');
                                 $curlObj= new QBviaCurl();
                                 $url = "_qbGetEmployeeVendorCustomer_id.php";
                                 $rsl=$curlObj->httpost_curl($url,$data);
                                 $contact_rsl =json_decode($rsl,true);
                                 unset($curlObj);
                                 //create contact's data
                                 $contact_rsl["token"] =$tokenconfig["token"];
                                 $contact_rsl["jwt"] =$jwt;
                                 $contact_rsl["private_key"] =$private_key;
                                 $contact_rsl["create_by"] =$create_by;
                                 $contact_rsl["submit_by"] =$submit_by;

                             }
                             if($operation=='Create' && is_numeric($id)){
                                 //create contact's data
                                 $contact_rsl["contact_type"] ="Policy Holder";
                                 $contact_rsl["gps"] =$gps;

                                 //add contact to CRM
                                 $curlObj= new QBviaCurl();
                                 $url = "_contactAddNew.php";
                                 $rsl=$curlObj->httpost_curl($url,$contact_rsl);
                                 unset($curlObj);
                             }

                             if($operation=='Update' && is_numeric($id)){
                                 $contact_rsl["contact_type"] ="Customer";
                                 //update contact to CRM
                                 $curlObj= new QBviaCurl();
                                 $url = "_qbContactUpdate_qbid.php";
                                 $rsl=$curlObj->httpost_curl($url,$contact_rsl);
                                 unset($curlObj);
                             }
                             break; //END customer

                         case 'Vendor':
                             $id= $entries['id'];
                             $operation= $entries['operation'];
                             $contact_rsl =array();
                             if(is_numeric($id)){
                                 $data = array('id'=>$id,'type'=>'vendor');
                                 $curlObj= new QBviaCurl();
                                 $url = "_qbGetEmployeeVendorCustomer_id.php";
                                 $rsl=$curlObj->httpost_curl($url,$data);
                                 $contact_rsl =json_decode($rsl,true);
                                 unset($curlObj);
                                 //create contact's data
                                 $contact_rsl["token"] =$tokenconfig["token"];
                                 $contact_rsl["contact_type"] ="Vendor";
                                 $contact_rsl["jwt"] =$jwt;
                                 $contact_rsl["private_key"] =$private_key;
                                 $contact_rsl["create_by"] =$create_by;
                                 $contact_rsl["submit_by"] =$submit_by;
                             }
                             if($operation=='Create' && is_numeric($id)){
                                 $contact_rsl["gps"] =$gps;

                                 //add contact to CRM
                                 $curlObj= new QBviaCurl();
                                 $url = "_contactAddNew.php";
                                 $rsl=$curlObj->httpost_curl($url,$contact_rsl);
                                 unset($curlObj);
                             }

                             if($operation=='Update' && is_numeric($id)){
                                 //update contact to CRM
                                 $curlObj= new QBviaCurl();
                                 $url = "_qbContactUpdate_qbid.php";
                                 $rsl=$curlObj->httpost_curl($url,$contact_rsl);
                                 unset($curlObj);
                             }
                             break; //END vendor

                         case 'Item':
                             //get data product from quickbook
                             $id= $entries['id'];
                             $operation= $entries['operation'];
                             $data = array('id'=>$id);
                             $curlObj= new QBviaCurl();
                             $url = "_qbGetItem_id.php";
                             $rsl=$curlObj->httpost_curl($url,$data);
                             $item_rsl =json_decode($rsl,true);
                             unset($curlObj);
                             //get product data from CRM base on ItemRef
                             $data = array('ItemRef'=>$id,'token'=>$tokenconfig["token"]);
                             $curlObj= new QBviaCurl();
                             $url = "_qbGetProductItemRef.php";
                             $rsl=$curlObj->httpost_curl($url,$data);
                             //file_put_contents($photoPathTemp1, $rsl);
                             $pro_rsl =json_decode($rsl,true);
                             unset($curlObj);

                             $product_data = array('token'=>$tokenconfig["token"],'ID'=>$pro_rsl['ID'],
                                 'product_notes'=>$pro_rsl['product_notes'],
                                 'product_tags'=>$pro_rsl['product_tags'],
                                 'product_taxable'=>$pro_rsl['product_taxable'],
                                 'prod_name'=>$item_rsl['prod_name'],
                                 'SKU'=>$item_rsl['SKU'],
                                 'prod_price'=>$item_rsl['prod_price'],
                                 'prod_cost'=>$item_rsl['prod_cost'],
                                 'prod_desc'=>$item_rsl['prod_desc'],
                                 'product_updated_by'=>$pro_rsl['product_updated_by'],
                                 'prod_class'=>$pro_rsl['prod_class'],
                                 'prod_desc_short'=>$pro_rsl['prod_desc_short'],
                                 'prod_height'=>$pro_rsl['prod_height'],'prod_inactive'=>$pro_rsl['prod_inactive'],
                                 'prod_length'=>$pro_rsl['prod_length'],
                                 'prod_type'=>$pro_rsl['prod_type'],'prod_visible'=>$pro_rsl['prod_visible'],
                                 'prod_weight'=>$pro_rsl['prod_weight'],'prod_width'=>$pro_rsl['prod_width'],
                                 'product_updated'=>$pro_rsl['product_updated'],
                                 'prod_internal_visible'=>$pro_rsl['prod_internal_visible'],
                                 'jwt'=>$jwt,
                                 'private_key'=>$private_key);
                             //for test
                             //file_put_contents($photoPathTemp1, $rsl);
                             if($operation=='Update' && is_numeric($id)){
                                 $curlObj= new QBviaCurl();
                                 $url = "_productEdit.php";
                                 $rsl=$curlObj->httpost_curl($url,$product_data);
                                 //$pro_rsl =json_decode($rsl,true);
                                 unset($curlObj);
                             }
                             break;

                         case 'Invoice':
                             //get data Invoice from quickbook
                             $id= $entries['id'];
                             $operation= $entries['operation'];
                             $data = array('id'=>$id);
                             $curlObj= new QBviaCurl();
                             $url = "_qbGetInvoiceFromQB_id.php";
                             $rsl=$curlObj->httpost_curl($url,$data);
                             unset($curlObj);
                             $item_rsl =json_decode($rsl,true);
                             //a sign to recognize that _orderEdit.php is called from quickbook
                             $item_rsl['quickbooks_call'] =1;
                             $item_rsl['jwt'] =$jwt;
                             $item_rsl['private_key'] =$private_key;
                             $item_rsl['token']=$tokenconfig["token"];

                             if($operation=='Update' && is_numeric($id)){
                                 $curlObj= new QBviaCurl();
                                 $url = "_orderEdit.php";
                                 $rsl=$curlObj->httpost_curl($url,$item_rsl);
                                 $data_rsl =json_decode($rsl,true);
                                 file_put_contents($photoPathTemp1, $data_rsl);
                                 unset($curlObj);
                             }


                             break;

                     }  //end switch
                    //-------END foreacch $event_noti['dataChangeEvent']['entities']
                }
            }
        } else {
            // not verified
        }
    }
}


// check JSON
function isValidJSON($string) {
    if (!isset($string) || trim($string) === '') {
        return false;
    }

    @json_decode($string);
    if (json_last_error() != JSON_ERROR_NONE) {
        return false;
    }
    return true;
}



