<?php
require '/mnt/web/api/freedomcrm-api/lib/vendor/autoload.php';
//require '/mnt/web/api/freedomcrm-api/quickbook/vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Item;
class QBInvoice{
    public function qbCreateInvoice($dataService1,
                                $CustomerRef,$Amount,$email,$ItemRefs,$customField){
       // print_r($dataService1); die();
        $dataService = DataService::Configure($dataService1);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);

        if(empty($email)) $email="";
        //line
        $line =array();
        foreach($ItemRefs as $item){
            $line[]=
                [
                    "Amount" => $item["Amount"],
                    "DetailType" => "SalesItemLineDetail",
                    "SalesItemLineDetail" => [
                        "ItemRef" => [
                            "value" => $item["ItemRef"],
                            "name" => $item["Name"]
                        ],
                        "Qty"=> $item["Qty"],
                        "UnitPrice"=> $item["UnitPrice"]
                    ]
                ];
        }
        //custom fields
        $order_title= empty($customField["order_title"])?"":$customField["order_title"];
        $sale_name= empty($customField["sale_name"])?"":$customField["sale_name"];
        $invoice_number= empty($customField["invoice_number"])?"":$customField["invoice_number"];

        //Add a new Invoice
        $theResourceObj = Invoice::create([
            "TotalAmt"=>$Amount,
            "Line" => $line,
            "CustomerRef"=> [
                "value"=> $CustomerRef
            ],
            "BillEmail" => [
                "Address" => $email
            ],
            "CustomField"=> [
                [
                    "DefinitionId"=> "1",
                    "StringValue"=> $sale_name,
                    "Type"=> "STRING_TYPE",
                    "Name"=> "salesman_ivn"
                ],
                [
                    "DefinitionId"=> "2",
                    "StringValue"=> $order_title,
                    "Type"=> "STRING_TYPE",
                    "Name"=> "Order_title"
                ],
                [
                    "DefinitionId"=> "3",
                    "StringValue"=> $invoice_number,
                    "Type"=> "STRING_TYPE",
                    "Name"=> "Invoice_number"
                ]

            ]
        ]);

        //print_r($theResourceObj);die();
        $resultingObj = $dataService->Add($theResourceObj);
        $error = $dataService->getLastError();
        if ($error) {
            return array("CreatedId"=>"","Status_code"=>$error->getHttpStatusCode(),
                "Helper_message"=>$error->getOAuthHelperError(),
                "Response_message"=>$error->getResponseBody()
            );
        }
        else {
            return array("CreatedId"=>$resultingObj->Id);
        }
    }

   //-----------------------------------------------------
    public function qbCreateItem($dataService1,$ItemName,$UnitPrice,$income_id,$costOfGoodsSold_id,$accountsReceivable_id,
                                 $Sku,$Description,$PurchaseCost,$Taxable){
        $dataService = DataService::Configure($dataService1);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);

        $invStartDate = date("Y-m-d");
        if($Taxable==1){
            $Taxable =true;
        }else{
            $Taxable =false;
        }
        //IncomeAccountRef:Income,ExpenseAccountRef:Cost of Goods Sold
        //AssetAccountRef:Accounts Receivable
        $theResourceObj = Item::create([
            "Name" => $ItemName,
            "UnitPrice" => $UnitPrice,
            "Sku"=>$Sku,
            "Description"=>$Description,
            "PurchaseCost"=>$PurchaseCost,
            "Taxable"=>$Taxable,
            "IncomeAccountRef" => [
                "value" => $income_id,
                "name" => "Sales of Product Income"
            ],
            "ExpenseAccountRef" => [
                "value" => $costOfGoodsSold_id,
                "name" => "Cost of Goods Sold"
            ],
            "AssetAccountRef" => [
                "value" => $accountsReceivable_id,
                "name" => "Inventory Asset"
            ],
            "Type" => "Service",
            "InvStartDate" => $invStartDate
        ]);

        $resultingObj = $dataService->Add($theResourceObj);
        $error = $dataService->getLastError();
        if ($error) {
            return array("CreatedId"=>"","Status_code"=>$error->getHttpStatusCode(),
                "Helper_message"=>$error->getOAuthHelperError(),
                "Response_message"=>$error->getResponseBody()
            );
        }
        else {
            return array("CreatedId"=>$resultingObj->Id);
        }
    }

    //-----------------------------------------------------
    public function qbGetItemByItemID($dataService1,$item_id){
        $dataService = DataService::Configure($dataService1);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);

        $invStartDate = date("Y-m-d");
        $entities = $dataService->FindbyId('item', $item_id);
        //print_r($entities);
        $data = array();
        if(isset($entities->Id)){
            $data['ItemRef']=$entities->Id;
            $data['prod_name']=$entities->Name;
            $data['SKU']=$entities->Sku;
            $data['prod_price']=$entities->UnitPrice;
            $data['prod_cost']=$entities->PurchaseCost;
            $data['prod_desc']=$entities->Description;
            $Taxable=$entities->Taxable;
            if($Taxable){
                $data['product_taxable']=1;
            }else{
                $data['product_taxable']=0;
            }
        }

        //print_r($data); die();
        $error = $dataService->getLastError();
        if ($error) {
            /*return array("CreatedId"=>"","Status_code"=>$error->getHttpStatusCode(),
                "Helper_message"=>$error->getOAuthHelperError(),
                "Response_message"=>$error->getResponseBody()
            );*/
            $data['Status_code'] =$error->getHttpStatusCode();
            return $data;
        }
        else {
            return $data;
        }
    }

    /* update product and service on quickbooks by Id
     * 03/24/2020
     */
//-----------------------------------------------------
    public function qbUpdateProduct_Id($dataService1,$ItemName,$UnitPrice,
                                 $Sku,$Description,$PurchaseCost,$Taxable,$Id){
        $dataService = DataService::Configure($dataService1);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);

        $invStartDate = date("Y-m-d");
        if($Taxable==1){
            $Taxable =true;
        }else{
            $Taxable =false;
        }

        $item = $dataService->FindbyId('item', $Id);
        $theResourceObj = Item::update($item , [
            "Name" => $ItemName,
            "UnitPrice" => $UnitPrice,
            "Sku"=>$Sku,
            "Description"=>$Description,
            "PurchaseCost"=>$PurchaseCost,
            "Taxable"=>$Taxable
        ]);

        $resultingObj = $dataService->Add($theResourceObj);
        $error = $dataService->getLastError();
        if ($error) {
            return array("CreatedId"=>"","Status_code"=>$error->getHttpStatusCode(),
                "Helper_message"=>$error->getOAuthHelperError(),
                "Response_message"=>$error->getResponseBody()
            );
        }
        else {
            return array("CreatedId"=>$resultingObj->Id);
        }
    }
    //-----------------------------------------------------
    /*
     * Get Invoice from Quickbooks by invoice ID
     */
    public function qbGetInvoiceFromQBByInvoiceID($dataService1,$invoice_id){
        $dataService = DataService::Configure($dataService1);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);

        $invStartDate = date("Y-m-d");
        $entities = $dataService->FindbyId('invoice', $invoice_id);

        $data =array();
        $data["Id"] = $entities->Id;
        $data["CustomerRef"]=$entities->CustomerRef;
        $data["TotalAmt"] = $entities->TotalAmt;
        $data["Balance"] = $entities->Balance;
        $data["TxnDate"] = $entities->TxnDate;
        $data["DueDate"] = $entities->DueDate;

        $product = array();
        foreach($entities->Line as $item){
            if(!empty($item->LineNum)){
                $product[] = array('ItemRef'=>$item->SalesItemLineDetail->ItemRef,
                'Qty'=>$item->SalesItemLineDetail->Qty,
                'UnitPrice'=>$item->SalesItemLineDetail->UnitPrice,
                'Amount'=>$item->Amount);

                $data["product"] =$product;
            }

            if(empty($item->LineNum) && isset($item->DiscountLineDetail->PercentBased)){
                $data["Amount_Discount"] = $item->Amount;
                $data["DiscountPercent"] = $item->DiscountLineDetail->DiscountPercent;
                $data["PercentBased"] = $item->DiscountLineDetail->PercentBased;
            }

        }

        //print_r($data); die();
        $error = $dataService->getLastError();
        if ($error) {
            /*return array("CreatedId"=>"","Status_code"=>$error->getHttpStatusCode(),
                "Helper_message"=>$error->getOAuthHelperError(),
                "Response_message"=>$error->getResponseBody()
            );*/
            $data['Status_code'] =$error->getHttpStatusCode();
            return $data;
        }
        else {
            return $data;
        }
    }
    //-----------------------------------------------------
    /*
     * Update a invoice on quickbooks from CRM by quicbook invoice Id
     */
    public function qbUpdateInvoiceFromCRM($dataService1,
                                    $CustomerRef,$Amount,$email,$ItemRefs,$customField,$Id){
        // print_r($dataService1); die();
        $dataService = DataService::Configure($dataService1);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);

        if(empty($email)) $email="";
        //line
        $line =array();
        foreach($ItemRefs as $item){
            $line[]=
                [
                    "Amount" => $item["Amount"],
                    "DetailType" => "SalesItemLineDetail",
                    "SalesItemLineDetail" => [
                        "ItemRef" => [
                            "value" => $item["ItemRef"],
                            "name" => $item["Name"]
                        ],
                        "Qty"=> $item["Qty"],
                        "UnitPrice"=> $item["UnitPrice"]
                    ]
                ];
        }
        //custom fields
        $order_title= empty($customField["order_title"])?"":$customField["order_title"];
        $sale_name= empty($customField["sale_name"])?"":$customField["sale_name"];
        $invoice_number= empty($customField["invoice_number"])?"":$customField["invoice_number"];
        //read quickbooks invoice ID by Id_quickbooks_invoice
        $invoice = $dataService->FindbyId('invoice', $Id);
        //update
        $theResourceObj = Invoice::update($invoice,
            [
                "TotalAmt"=>$Amount,
                "Line" => $line,
                "CustomerRef"=> [
                    "value"=> $CustomerRef
                ],
                "BillEmail" => [
                    "Address" => $email
                ],
                "CustomField"=> [
                    [
                        "DefinitionId"=> "1",
                        "StringValue"=> $sale_name,
                        "Type"=> "STRING_TYPE",
                        "Name"=> "salesman_ivn"
                    ],
                    [
                        "DefinitionId"=> "2",
                        "StringValue"=> $order_title,
                        "Type"=> "STRING_TYPE",
                        "Name"=> "Order_title"
                    ]

                ]
            ]
        );

        $resultingObj = $dataService->Update($theResourceObj);
        //print_r($theResourceObj);die();
        $error = $dataService->getLastError();
        if ($error) {
            return array("CreatedId"=>"","Status_code"=>$error->getHttpStatusCode(),
                "Helper_message"=>$error->getOAuthHelperError(),
                "Response_message"=>$error->getResponseBody()
            );
        }
        else {
            return array("CreatedId"=>$resultingObj->Id);
        }
    }
////////////////////////////
}
?>