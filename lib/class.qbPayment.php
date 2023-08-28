<?php
require '/mnt/web/api/freedomcrm-api/lib/vendor/autoload.php';
//require '/mnt/web/api/freedomcrm-api/quickbook/vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Payment;

class QBPayment{
    public function qbCreatePayment($dataService1,$CustomerRef,$TxnId,$Amount){
       // print_r($dataService1); die();
        $dataService = DataService::Configure($dataService1);

        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);
        //Add a new Invoice
        $theResourceObj = Payment::create([
            "CustomerRef" =>
            [
                "value" => $CustomerRef
            ],
            "TotalAmt" => $Amount,
            "Line" => [
                [
                    "Amount" => $Amount,
                    "LinkedTxn" => [
                        [
                            "TxnId" => $TxnId,
                            "TxnType" => "Invoice"
                        ]]
                ]]
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

///////////////////////////////////////
}


?>