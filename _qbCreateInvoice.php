<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

    $config = include('qbconfig.php');
    include_once './lib/class.qbInvoice.php';
    include_once './lib/class.quickbookconnect.php';
    include_once '_qbviacurl.php';
    $qbComIDconfig = include('_qbcompanyID.php');

       $Object = new Quickbookconnect();

    $EXPECTED = array('token',
        'jwt','private_key','contactID','invoiceID','orderID','TxnId');

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
        //Get $CustomerRef from contact table
        $c_info= $Object->qbGetContact_ID($contactID);
        if(empty($c_info["qb_customer_id"])){
            //create $CustomerRef on quick book
            if(count($c_info)>0){
                $curlObj= new QBviaCurl();
                $url = "_qbCreateCustmer.php";
                $Line1 =empty($c_info["primary_street_address1"])?"":$c_info["primary_street_address1"];
                $City =empty($c_info["primary_city"])?"":$c_info["primary_city"];
                $CountrySubDivisionCode =empty($c_info["primary_state"])?"":$c_info["primary_state"];
                $PostalCode =empty($c_info["primary_postal_code"])?"":$c_info["primary_postal_code"];
                $GivenName =empty($c_info["first_name"])?"":$c_info["first_name"];
                $FamilyName =empty($c_info["last_name"])?"":$c_info["last_name"];
                $MiddleName =empty($c_info["middle_name"])?"":$c_info["middle_name"];
                $PrimaryPhone =empty($c_info["primary_phone"])?"":$c_info["primary_phone"];
                $PrimaryEmailAddr =empty($c_info["primary_email"])?"":$c_info["primary_email"];
                $data = array(
                    "Line1"=>$Line1,
                    "City"=>$City,
                    "Country"=>"USA",
                    "CountrySubDivisionCode"=>$CountrySubDivisionCode,
                    "PostalCode"=>$PostalCode,
                    "GivenName"=>$GivenName,
                    "MiddleName"=>$MiddleName,
                    "FamilyName"=>$FamilyName,
                    "CompanyName"=>"",
                    "PrimaryPhone"=>$PrimaryPhone,
                    "PrimaryEmailAddr"=>$PrimaryEmailAddr);
                $rsl=$curlObj->httpost_curl($url,$data);
                unset($curlObj);
                $rsl = json_decode($rsl,true);
                if(isset($rsl["CreatedId"])){
                    $CustomerRef= $Object->updateQBVendor_contactID($contactID,$rsl["CreatedId"]);
                }
            }
            //end create customer on qb

        }else{
            $CustomerRef = $c_info["qb_customer_id"];
        }

        //create invoice on quick book
        $ItemRef_qb=array();
        if(is_numeric($CustomerRef)){
            //Get ItemRef if not Create ItemRef,Get product's ID
            $products =  $Object->qbGetProductordered_orderid($orderID);
            $ItemRefs=array();

            foreach($products as $prod){
                $products_temp = $Object->qbGetProduct_prodID($prod["id"]);

                $prod["quantity"] =empty($prod["quantity"])?0:$prod["quantity"];
                $prod["line_total"] =empty($prod["line_total"])?0:$prod["line_total"];
                if(empty($products_temp["ItemRef"])){
                    // Create ItemRef
                    $prod_price = empty($prod["price"])?0:$prod["price"];
                    $prod_cost = empty($products_temp["prod_cost"])?0:$products_temp["prod_cost"];
                    $product_taxable = empty($products_temp["product_taxable"])?0:$products_temp["product_taxable"];
                    $SKU = $products_temp["SKU"];
                    $prod_desc = $products_temp["prod_desc"];

                    $curlObj= new QBviaCurl();
                    $url = "_qbCreateItem.php";
                    $ItemName = $products_temp["prod_name"];
                    $UnitPrice = $prod_price;

                    $itemData = array(
                        "ItemName"=>$ItemName,
                        "UnitPrice"=>$prod_price,
                        "Sku"=>$SKU,
                        "Description"=>$prod_desc,
                        "PurchaseCost"=>$prod_cost,
                        "Taxable"=>$product_taxable
                    );
                    $rsl=$curlObj->httpost_curl($url,$itemData);
                    unset($curlObj);
                    $rsl = json_decode($rsl,true);
                    if(isset($rsl["CreatedId"])){
                        if(is_numeric($rsl["CreatedId"]) && !empty($rsl["CreatedId"]) ){
                            $ItemRef_qb[]= $Object->qbUpdateItemRefIntoProductTable($rsl["CreatedId"],$prod["id"]);
                            //create ItemRef for invoice
                            $ItemRefs[]=array(
                                "ItemRef"=>$rsl["CreatedId"],
                                "Amount"=>$prod["line_total"],
                                "UnitPrice"=>$prod["price"],
                                "Qty"=>$prod["quantity"],
                                "Name"=>$ItemName
                            );

                        }else{
                            $ItemRef_qb[]="Can't create ItemRef for ".$ItemName;
                        }
                    }else{
                        $ItemRef_qb[]="error 500 for".$ItemName;
                    }

                }else{
                    //get ItemRef from database
                    $ItemRefs[]=array(
                        "ItemRef"=>$products_temp["ItemRef"],
                        "Amount"=>$prod["line_total"],
                        "UnitPrice"=>$prod["price"],
                        "Qty"=>$prod["quantity"],
                        "Name"=>$products_temp["prod_name"]
                    );
                }

            }

            //End ItemRef
            if(count($ItemRefs)>0){
                //GET OderTitle, Salemans, IvnNumber
                $customField =$Object->qbGetOderTitleSalemansIvnNumber($invoiceID);
                //create invoice on quick book
                $Amount = $Object->qbGetInvoiceTotal($invoiceID);

                $ObPayment = new QBInvoice();
                if(!is_numeric($TxnId)){
                    //create a new invoice
                    $ID = $ObPayment->qbCreateInvoice($dataService,$CustomerRef,$Amount,$c_info["primary_email"],$ItemRefs,$customField);
                }else{
                    //update invoice
                    $ID = $ObPayment->qbUpdateInvoiceFromCRM($dataService,$CustomerRef,$Amount,$c_info["primary_email"],$ItemRefs,$customField,$TxnId);
                }


                if(is_numeric($ID["CreatedId"])){
                    //UPDATE INVOICE TABBLE
                    $TxnId =$ID["CreatedId"];
                    $invoiceID = $Object->qbUpdateTxnIdIntoInvoice($TxnId,$invoiceID);
                    $ret = array('ERROR'=>'','CreatedId'=>$ID["CreatedId"],'invoiceID'=>$invoiceID,"ItemRef_qb"=>$ItemRef_qb);
                }else{
                    $ret = array('ERROR'=>$ID["TheStatusCodeIs"],'TheHelperMessageIs'=>$ID["TheHelperMessageIs"],
                        'TheResponseMessageIs'=>$ID["TheResponseMessageIs"],'CreatedId'=>'',
                    "ItemRef_qb"=>$ItemRef_qb);
                }
            }else{
                $ret = array('ERROR'=>'ItemRef required','CreatedId'=>"","ItemRef_qb"=>$ItemRef_qb);
            }

        }else{
            $ret = array('ERROR'=>'Can not create Customer on Quick Book','CreatedId'=>"");
        }
    }

    echo json_encode($ret);
}

