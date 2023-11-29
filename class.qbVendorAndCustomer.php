<?php
require '/mnt/web/api/freedomcrm-api/lib/vendor/autoload.php';
//require '/mnt/web/api/freedomcrm-api/quickbook/vendor/autoload.php';
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Employee;
class QBVendorAndCustomer{
    public function qbNewVendor($dataService1,
                                $Line1,$City,$Country,$CountrySubDivisionCode,$PostalCode,
                                $GivenName,$FamilyName,$PrimaryPhone,$PrimaryEmailAddr,
                                $MiddleName,$CompanyName,$TaxIdentifier){

        $dataService = DataService::Configure($dataService1);

        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $theResourceObj = Vendor::create([
            "BillAddr" => [
                "Line1"=> $Line1,
                "Line2"=> "",
                "Line3"=> "12252 tang.",
                "City"=>$City,
                "Country"=> "U.S.A",
                "CountrySubDivisionCode"=> $CountrySubDivisionCode,
                "PostalCode"=> $PostalCode,
            ],
            "TaxIdentifier"=> $TaxIdentifier,
            "AcctNum"=> "",
            "Title"=> "",
            "GivenName"=> $GivenName,
            "FamilyName"=> $FamilyName,
            "MiddleName" => $MiddleName,
            "Suffix"=> "",
            "CompanyName"=> $CompanyName,
            "DisplayName"=> $CompanyName,
            "PrintOnCheckName"=> $CompanyName,
            "PrimaryPhone"=> [
                "FreeFormNumber"=> $PrimaryPhone
            ],
            "Mobile"=> [
                "FreeFormNumber"=> ""
            ],
            "PrimaryEmailAddr"=> [
                "Address"=> $PrimaryEmailAddr
            ],
            "WebAddr"=> [
                "URI"=> ""
            ],
            "Vendor1099" => true
        ]);

        $resultingObj = $dataService->Add($theResourceObj);
        $error = $dataService->getLastError();
        if ($error) {
            /*
           echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
           echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
           echo "The Response message is: " . $error->getResponseBody() . "\n";
              */
            return array("CreatedId"=>"","TheStatusCodeIs"=>$error->getHttpStatusCode(),
                "TheHelperMessageIs"=>$error->getOAuthHelperError(),
                "TheResponseMessageIs"=>$error->getResponseBody());
        }
        else {
            /* echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
            $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
            echo $xmlBody . "\n";*/
            return array("CreatedId"=>$resultingObj->Id);

        }

    }

    public function qbUpdateVendor($dataService1,$vendorID,
                                $Line1,$City,$Country,$CountrySubDivisionCode,$PostalCode,
                                $GivenName,$FamilyName,$PrimaryPhone,$PrimaryEmailAddr,
                                $MiddleName,$CompanyName,$TaxIdentifier){

        $dataService = DataService::Configure($dataService1);

        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $vendor = $dataService->FindbyId('vendor', $vendorID);
        $theResourceObj = Vendor::update($vendor,[
            "BillAddr" => [
                "Line1"=> $Line1,
                "City"=>$City,
                "Country"=> "U.S.A",
                "CountrySubDivisionCode"=> $CountrySubDivisionCode,
                "PostalCode"=> $PostalCode,
            ],
            "TaxIdentifier"=> "",
            "AcctNum"=> "",
            "Title"=> "",
            "GivenName"=> $GivenName,
            "FamilyName"=> $FamilyName,
            "MiddleName" => $MiddleName,
            "Suffix"=> "",
            "CompanyName"=> $CompanyName,
            "DisplayName"=> $CompanyName,
            "PrintOnCheckName"=> $CompanyName,
            "PrimaryPhone"=> [
                "FreeFormNumber"=> $PrimaryPhone
            ],
            "Mobile"=> [
                "FreeFormNumber"=> ""
            ],
            "PrimaryEmailAddr"=> [
                "Address"=> $PrimaryEmailAddr
            ],
            "WebAddr"=> [
                "URI"=> ""
            ],
            "Vendor1099" => true
        ]);

        $resultingObj = $dataService->Update($theResourceObj);
        $error = $dataService->getLastError();
        if ($error) {

            return array("CreatedId"=>"","TheStatusCodeIs"=>$error->getHttpStatusCode(),
                "TheHelperMessageIs"=>$error->getOAuthHelperError(),
                "TheResponseMessageIs"=>$error->getResponseBody());
        }
        else {
            /* echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
            $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
            echo $xmlBody . "\n";*/
            return array("CreatedId"=>$resultingObj->Id);

        }

    }
//-------------------------------------
    public function qbVendorSearchByCompName($dataService1,$CompanyName){

        $dataService = DataService::Configure($dataService1);

        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);
       // Run a query
        //$entities = $dataService->Query("Select * from vendor where CompanyName ='{$CompanyName}'");
        $entities = $dataService->Query("Select Id,GivenName,CompanyName from vendor where CompanyName = '{$CompanyName}'");
        $error = $dataService->getLastError();
        if ($error) {
            return array("vendorID"=>"","TheStatusCodeIs"=>$error->getHttpStatusCode(),
                "TheHelperMessageIs"=>$error->getOAuthHelperError(),
                "TheResponseMessageIs"=>$error->getResponseBody());
        }
        // Echo some formatted output
        //var_dump($entities);
        $vendorID='';
        if(count($entities)>0){
            foreach($entities as $row){
                $vendorID =$row->Id;
            }
        }

        return array("vendorID"=>$vendorID,"TheStatusCodeIs"=>"",
            "TheHelperMessageIs"=>"",
            "TheResponseMessageIs"=>"");

    }

    //------------------------------------------------------------
    /*
         * create a cutomer on QB
    */

    public function qbNewCustomer($dataService,
                                  $Line1,$City,$Country,$CountrySubDivisionCode,$PostalCode,
                                  $GivenName,$FamilyName,$MiddleName,$PrimaryPhone,$PrimaryEmailAddr,
                                  $CompanyName){
        // Prep Data Services
        $dataService = DataService::Configure($dataService);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);
        //Add a new Vendor
        $theResourceObj = Customer::create([
            "BillAddr" => [
                "Line1" => $Line1,
                "City" => $City,
                "Country" => $Country,
                "CountrySubDivisionCode" => $CountrySubDivisionCode,
                "PostalCode" => $PostalCode
            ],
            "Notes" => "",
            "Title" => "",
            "GivenName" => $GivenName,
            "MiddleName" => $MiddleName,
            "FamilyName" => $FamilyName,
            "Suffix" => "",
            "FullyQualifiedName" => "",
            "CompanyName" => $CompanyName,
            "DisplayName" => "",
            "PrimaryPhone" => [
                "FreeFormNumber" => $PrimaryPhone
            ],
            "PrimaryEmailAddr" => [
                "Address" => $PrimaryEmailAddr
            ]
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

    //------------------------------------------------------------
    /*
         * create a cutomer on QB
    */

    public function qbUpdateCustomer($dataService,$CustomerID,
                                  $Line1,$City,$Country,$CountrySubDivisionCode,$PostalCode,
                                  $GivenName,$FamilyName,$MiddleName,$PrimaryPhone,$PrimaryEmailAddr,
                                  $CompanyName){
        // Prep Data Services
        $dataService = DataService::Configure($dataService);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);
        //Update a new Vendor
        $customer = $dataService->FindbyId('customer', $CustomerID);
        $theResourceObj = Customer::update($customer,[
            "BillAddr" => [
                "Line1" => $Line1,
                "City" => $City,
                "Country" => $Country,
                "CountrySubDivisionCode" => $CountrySubDivisionCode,
                "PostalCode" => $PostalCode
            ],
            "Notes" => "",
            "Title" => "",
            "GivenName" => $GivenName,
            "MiddleName" => $MiddleName,
            "FamilyName" => $FamilyName,
            "Suffix" => "",
            "FullyQualifiedName" => "",
            "CompanyName" => $CompanyName,
            "DisplayName" => "",
            "PrimaryPhone" => [
                "FreeFormNumber" => $PrimaryPhone
            ],
            "PrimaryEmailAddr" => [
                "Address" => $PrimaryEmailAddr
            ]
        ]);

        $resultingObj = $dataService->Update($theResourceObj);

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

    //-------------------------------------
    public function qbCustomerSearch($dataService1,$GivenName,$MiddleName,$FamilyName){

        $dataService = DataService::Configure($dataService1);

        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);
        // Run a query
        $query ="Select Id,GivenName,FamilyName from Customer where GivenName = '{$GivenName}'";

        $criteria ="";
        if(!empty($GivenName)){
            $criteria ="GivenName='{$GivenName}'";
        }

        if(!empty($MiddleName)){
            if(empty($criteria)){
                $criteria =" MiddleName='{$MiddleName}' ";
            }else{
                $criteria .=" AND MiddleName='{$MiddleName}'";
            }
        }

        if(!empty($FamilyName)){
            if(empty($criteria)){
                $criteria =" FamilyName='{$FamilyName}' ";
            }else{
                $criteria .=" AND FamilyName='{$FamilyName}'";
            }
        }

        if(!empty($criteria)){
            $query ="Select Id,GivenName,FamilyName from Customer where " .$criteria;
        }

        $entities = $dataService->Query("{$query}");
        $error = $dataService->getLastError();
        if ($error) {
            return array("customerID"=>"","TheStatusCodeIs"=>$error->getHttpStatusCode(),
                "TheHelperMessageIs"=>$error->getOAuthHelperError(),
                "TheResponseMessageIs"=>$error->getResponseBody());
        }
        // Echo some formatted output
        //var_dump($entities);
        $customerID='';
        if(count($entities)>0){
            foreach($entities as $row){
                $customerID =$row->Id;
            }
        }

        return array("customerID"=>$customerID,"TheStatusCodeIs"=>"",
            "TheHelperMessageIs"=>"",
            "TheResponseMessageIs"=>"");

    }

    //------------------------------------------------------------
    /*
         * create a employee on QB
    */

    public function qbNewEmployee($dataService,$SSN,
                                  $Line1,$City,$CountrySubDivisionCode,$PostalCode,
                                  $GivenName,$FamilyName,$PrimaryPhone){
        // Prep Data Services
        $dataService = DataService::Configure($dataService);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);
        //Add a new Vendor
        $theResourceObj = Employee::create([
            "GivenName" => $GivenName,
            "SSN" => $SSN,
            "PrimaryAddr" => [
                "Line1" => $Line1,
                "City" => $City,
                "CountrySubDivisionCode" => $CountrySubDivisionCode,
                "PostalCode" => $PostalCode
            ],

            "FamilyName" => $FamilyName,
            "PrimaryPhone" => [
                "FreeFormNumber" => $PrimaryPhone
            ]
        ]);

        $resultingObj = $dataService->Add($theResourceObj);
        //print_r($resultingObj); die();
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

    //------------------------------------------------------------
    /*
         * update a employee on QB
    */

    public function qbUpdateEmployee($dataService,$employeeID,$SSN,
                                  $Line1,$City,$CountrySubDivisionCode,$PostalCode,
                                  $GivenName,$FamilyName,$PrimaryPhone){
        // Prep Data Services
        $dataService = DataService::Configure($dataService);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);
        //Update a new Vendor
        $customer = $dataService->FindbyId('employee', $employeeID);
        $theResourceObj = Employee::update($customer,[
            "GivenName" => $GivenName,
            "SSN" => $SSN,
            "PrimaryAddr" => [
                "Line1" => $Line1,
                "City" => $City,
                "CountrySubDivisionCode" => $CountrySubDivisionCode,
                "PostalCode" => $PostalCode
            ],

            "FamilyName" => $FamilyName,
            "PrimaryPhone" => [
                "FreeFormNumber" => $PrimaryPhone
            ]
        ]);

        $resultingObj = $dataService->Update($theResourceObj);
        //print_r($resultingObj); die();
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

    //-------------------------------------
    public function qbEmployeeSearch($dataService1,$GivenName,$FamilyName){

        $dataService = DataService::Configure($dataService1);

        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);
        // Run a query
        $query ="Select Id,GivenName,FamilyName from Employee where GivenName = '{$GivenName}'";

        $criteria ="";
        if(!empty($GivenName)){
            $criteria ="GivenName='{$GivenName}'";
        }

        if(!empty($FamilyName)){
            if(empty($criteria)){
                $criteria =" FamilyName='{$FamilyName}' ";
            }else{
                $criteria .=" AND FamilyName='{$FamilyName}'";
            }
        }

        if(!empty($criteria)){
            $query ="Select Id,GivenName,FamilyName from Employee where " .$criteria;
        }

        $entities = $dataService->Query("{$query}");
        $error = $dataService->getLastError();
        if ($error) {
            return array("employeeID"=>"","TheStatusCodeIs"=>$error->getHttpStatusCode(),
                "TheHelperMessageIs"=>$error->getOAuthHelperError(),
                "TheResponseMessageIs"=>$error->getResponseBody());
        }
        // Echo some formatted output
        //var_dump($entities);
        $employeeID='';
        if(count($entities)>0){
            foreach($entities as $row){
                $employeeID =$row->Id;
            }
        }

        return array("employeeID"=>$employeeID,"TheStatusCodeIs"=>"",
            "TheHelperMessageIs"=>"",
            "TheResponseMessageIs"=>"");

    }

    //------------------------------------------------------------
    /*
         * update a employee on QB
    */

    public function qbGetEmployeeVendorCustomer_id($dataService,$id,$type){
        // Prep Data Services
        $dataService = DataService::Configure($dataService);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);
        //Update a new Vendor
        //$customer = $dataService->FindbyId('employee', $id);
        if($type=="Employee"){
            $query ="Select * from Employee  where Id ='{$id}'";
        }elseif($type=="Customer"){
            $query ="Select * from Customer  where Id ='{$id}'";
        }elseif($type=="vendor"){
            $query ="Select * from vendor  where Id ='{$id}'";
        }

        $entities = $dataService->Query("{$query}");

        $qb_employee_id=0;
        $first_name ='';
        $last_name ='';
        $middle_name='';

        $primary_street_address1='';
        $primary_street_address2='';
        $primary_city='';
        $primary_state ='';
        $primary_postal_code='';

        $primary_email='';
        $primary_phone='';

        $primary_website='';

        $PrimaryAddr='';
        $PrimaryPhone ='';
        $PrimaryEmailAddr='';

        $info= array();
        if(count($entities)>0){
            foreach($entities as $row){
                $qb_employee_id =$row->Id;
                $first_name =$row->GivenName;
                $last_name =$row->FamilyName;
                $middle_name =$row->MiddleName;
                if($type=="Employee"){
                    $PrimaryAddr =$row->PrimaryAddr;
                }elseif($type=="Customer"){
                    $PrimaryAddr =$row->BillAddr;
                }elseif($type=='vendor'){
                    $PrimaryAddr =$row->BillAddr;
                    $WebAddr=$row->WebAddr;
                }

                $PrimaryPhone =$row->PrimaryPhone;
                $PrimaryEmailAddr =$row->PrimaryEmailAddr;
            }

            if($type=="Employee"){
                $primary_street_address2=$PrimaryAddr->Line2;
            }

            $primary_street_address1=$PrimaryAddr->Line1;

            $primary_city=$PrimaryAddr->City;
            $primary_state=$PrimaryAddr->CountrySubDivisionCode;
            $primary_postal_code=$PrimaryAddr->PostalCode;

            $primary_email= $PrimaryEmailAddr->Address;
            $primary_phone= $PrimaryPhone->FreeFormNumber;

            if($type=='vendor') $primary_website = $WebAddr->URI;

            $info=array("quickbook_id"=>$qb_employee_id,
                "first_name"=>$first_name,
                "last_name"=>$last_name,
                "middle_name"=>$middle_name,
                "primary_street_address1"=>$primary_street_address1,
                "primary_street_address2"=>$primary_street_address2,
                "primary_city"=>$primary_city,
                "primary_state"=>$primary_state,
                "primary_postal_code"=>$primary_postal_code,
                "primary_email"=>$primary_email,
                "primary_phone"=>$primary_phone,
                "primary_website"=>$primary_website,
                "Status_code"=>''
            );

        }//end if count($entities)>0

        //print_r("-----------------");
        //print_r($entities); print_r("---".$qb_employee_id); die();
        $error = $dataService->getLastError();
        if ($error) {
            /*return array("CreatedId"=>"","Status_code"=>$error->getHttpStatusCode(),
                "Helper_message"=>$error->getOAuthHelperError(),
                "Response_message"=>$error->getResponseBody()
            );*/
            $info["Status_code"]=$error->getHttpStatusCode();
            return $info;
        }
        else {
            return $info;
        }
    }


///////////////////////////////////////
}


?>