<?php
require 'vendor_sandbox/autoload.php';
require_once 'constants/SampleCodeConstants.php';
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

//define("AUTHORIZENET_LOG_FILE", "phplog");

require_once 'class.common.php';
class Sandbox extends Common{
    public function chargeCreditCard($amount,$cardNumber,$expirationDate,$CardCode,
                                     $invoiceNumber,$description,
                                    $buyer_id,$cus_identify,
                                    $sale_id)
    {
        /* Create a merchantAuthenticationType object with authentication details
           retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(\SampleCodeConstants::MERCHANT_LOGIN_ID);
        $merchantAuthentication->setTransactionKey(\SampleCodeConstants::MERCHANT_TRANSACTION_KEY);

        // Set the transaction's refId
        $refId = 'ref' . time();

        // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($cardNumber);
        $creditCard->setExpirationDate($expirationDate);
        $creditCard->setCardCode($CardCode);

        // Add the payment data to a paymentType object
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);

        // Create order information
        $order = new AnetAPI\OrderType();
        //$order->setInvoiceNumber($invoiceNumber);
        $order->setDescription($description);

        // Set the customer's Bill To address
        $cusInfo = $this->getBuyer_ID($buyer_id);

        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName($cusInfo['first_name']);
        $customerAddress->setLastName($cusInfo['last_name']);
        $customerAddress->setCompany($cusInfo['name']);
        $customerAddress->setAddress($cusInfo['primary_street_address1']);
        $customerAddress->setCity($cusInfo['primary_city']);
        $customerAddress->setState($cusInfo['primary_state']);
        $customerAddress->setZip($cusInfo['primary_postal_code']);
        $customerAddress->setCountry("USA");

        // Set the customer's identifying information
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        //$customerData->setId($cus_identify);
        $customerData->setEmail($cusInfo['primary_email']);

        // Add values for transaction settings
        $duplicateWindowSetting = new AnetAPI\SettingType();
        $duplicateWindowSetting->setSettingName("duplicateWindow");
        $duplicateWindowSetting->setSettingValue("60");

        // Add some merchant defined fields. These fields won't be stored with the transaction,
        // but will be echoed back in the response.
        $saleInfo = $this->getBuyer_ID($sale_id);
        $sale_name =$saleInfo['first_name']." ".$saleInfo['last_name'];

        $merchantDefinedField1 = new AnetAPI\UserFieldType();

        $merchantDefinedField1->setName($sale_name);
        $merchantDefinedField1->setValue($saleInfo['primary_email']);

//        $merchantDefinedField2 = new AnetAPI\UserFieldType();
//        $merchantDefinedField2->setName("favoriteColor");
//        $merchantDefinedField2->setValue("blue");

        // Create a TransactionRequestType object and add the previous objects to it
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($paymentOne);
        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);
        $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
        $transactionRequestType->addToUserFields($merchantDefinedField1);
        //$transactionRequestType->addToUserFields($merchantDefinedField2);

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);

        // Create the controller and get the response
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);


        if ($response != null) {
            // Check to see if the API request was successfully received and acted upon
            if ($response->getMessages()->getResultCode() == "Ok") {
                // Since the API request was successful, look for a transaction response
                // and parse it to display the results of authorizing the card
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    $ret = array("Successfully created transaction with Transaction ID"=>$tresponse->getTransId(),
                        "Transaction Response Code"=>$tresponse->getResponseCode(),
                        " Message Code"=>$tresponse->getMessages()[0]->getCode(),
                        "Auth Code"=>$tresponse->getAuthCode(),
                        "Description"=>$tresponse->getMessages()[0]->getDescription(),
                        "Error Code"=>"",
                        "Error Message"=>"",
                        "Success"=>1,
                    );

                } else {
                    $ret =array(
                        "Successfully created transaction with Transaction ID"=>"",
                        "Transaction Response Code"=>"",
                        " Message Code"=>"",
                        "Auth Code"=>"",
                        "Description"=>"Transaction Failed",
                        "Error Code"=>$tresponse->getErrors()[0]->getErrorCode(),
                        "Error Message"=>$tresponse->getErrors()[0]->getErrorText(),
                        "Success"=>""
                    );
                }
                // Or, print errors if the API request wasn't successful
            } else {
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getErrors() != null) {
                    $ErrorCode = $tresponse->getErrors()[0]->getErrorCode();
                    $ErrorMessage = $tresponse->getErrors()[0]->getErrorText();
                } else {
                    $ErrorCode = $response->getMessages()->getMessage()[0]->getCode();
                    $ErrorMessage = $response->getMessages()->getMessage()[0]->getText();
                }

                $ret =array(
                    "Successfully created transaction with Transaction ID"=>"",
                    "Transaction Response Code"=>"",
                    " Message Code"=>"",
                    "Auth Code"=>"",
                    "Description"=>"Transaction Failed",
                    "Error Code"=>$ErrorCode,
                    "Error Message"=>$ErrorMessage,
                    "Success"=>""
                );
            }
        } else {
            $ret =array(
                "Successfully created transaction with Transaction ID"=>"",
                "Transaction Response Code"=>"",
                " Message Code"=>"",
                "Auth Code"=>"",
                "Description"=>"No response returned",
                "Error Code"=>"",
                "Error Message"=>"",
                "Success"=>""
            );
        }


        return $ret;
    }

    //------------------------------------------------
    public function getBuyer_ID($ID) {
        $query = "SELECT c.first_name,c.last_name, cp.name,
        c.primary_street_address1,c.primary_city,c.primary_state,
        c.primary_postal_code,c.primary_email FROM  contact as c
        left join company as cp on cp.ID = c.company_name
        where c.ID = '{$ID}'";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }
    /////////////////////////////////////////////////////////
}