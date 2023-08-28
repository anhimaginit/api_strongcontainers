<?php
//require 'mnt/web/api/freedomcrm-api/lib/vendor/quickbooks/';
//require '/mnt/web/api/freedomcrm-api/quickbook/vendor/autoload.php';
require '/mnt/web/api/freedomcrm-api/lib/vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Account;

require_once 'class.common.php';

//require_once('../quickbook/vendor/autoload.php');
class Quickbookconnect extends Common{
    //------------------------------------------------------------
    public function insertToken($accessTokenKey,$refreshTokenKey){
        $accessTokenKey =$this->protect($accessTokenKey);
        $refreshTokenKey =$this->protect($refreshTokenKey);
        $fields ="accessTokenKey,refreshTokenKey";
        $values ="'{$accessTokenKey}','{$refreshTokenKey}'";

        $insert = "INSERT INTO quickbook_token ({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insert);
        $idreturn = mysqli_insert_id($this->con);
        if(is_numeric($idreturn) && !empty($idreturn)){
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------------------
    public function updatetoken($tokenID,$accessTokenKey,$refreshTokenKey){
        $accessTokenKey =$this->protect($accessTokenKey);
        $refreshTokenKey =$this->protect($refreshTokenKey);

        $updateCommand = "UPDATE `quickbook_token`
                SET accessTokenKey = '{$accessTokenKey}'
                    WHERE ID ='{$tokenID}'";

        $update = mysqli_query($this->con,$updateCommand);
        return $update;

    }

    //----------------------------------------------------------
    public function checkForIns_UpdToken(){

        $query = "SELECT ID from quickbook_token LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $ID = '';

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $ID =$row['ID'];
            }
        }
        return $ID;
    }

    //----------------------------------------------------------
    /*
     * Create a Account on QB
    */
    public function qbNewAcct($dataService1,$accountype,$accountName){
        // Prep Data Services
        $dataService = DataService::Configure($dataService1);
        $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
        $dataService->throwExceptionOnError(true);

        //Add a new Vendor
        $theResourceObj = Account::create([
            "AccountType" => $accountype,
            "Name" => $accountName
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
            $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
            return array("CreatedId"=>$resultingObj->Id);
        }
    }

    ///////////////////Get information from database/////////
    public function qbGetContact_ID($contactID){
        $sqlText ="select * from contact
        where ID ='{$contactID}'";
        $result = mysqli_query($this->con,$sqlText);

        $info=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $info = $row;
            }
        }

        return $info;
    }
    //----------------------------------------------------------
    public function qbGetInvoiceTotal($invoiceID){
        $sqlText ="select total from invoice
        where ID ='{$invoiceID}'";
        $result = mysqli_query($this->con,$sqlText);

        $total=0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $total = $row["total"];
            }
        }

        return $total;
    }
    //----------------------------------------------------------
    public function qbUpdateTxnIdIntoInvoice($TxnId,$invoiceID){
        $update ="UPDATE invoice SET
                  TxnId ='{$TxnId}'
                  WHERE ID= '{$invoiceID}'";
        $update = mysqli_query($this->con,$update);

        if($update){
            return $invoiceID;
        }else{
            return mysqli_error($this->con);
        }
    }

  //----------------------------------------------------------
    public function qbGetProductordered_orderid($order_id){
        $sqlText ="select products_ordered from orders
        where order_id ='{$order_id}'";

        $result = mysqli_query($this->con,$sqlText);

        $product=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $product = json_decode($row["products_ordered"],true);
            }
        }

        return $product;
    }

    //----------------------------------------------------------
    public function qbGetProduct_prodID($prodID){
        $sqlText ="select ItemRef,prod_price,prod_name,SKU,prod_desc,
           prod_cost,product_taxable from products
        where ID ='{$prodID}'";

        $result = mysqli_query($this->con,$sqlText);

        $product=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $product = $row;
            }
        }

        return $product;
    }
    //----------------------------------------------------------
    public function qbUpdateItemRefIntoProductTable($ItemRef,$productID){
        $updateCommand = "UPDATE `products`
                SET ItemRef = '{$ItemRef}'
				WHERE ID = '{$productID}'";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            return $ItemRef;
        }else{
            return mysqli_error($this->con);
        }
    }

    //----------------------------------------------------------
    public function qbGetOderTitleSalemansIvnNumber($invoice_id){
        $sqlText ="select order_title,sale_name,invoiceid as invoice_number from report_invoice
        where ID ='{$invoice_id}' LIMIT 1 ";

        $result = mysqli_query($this->con,$sqlText);

        $list=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function qbGetQBInvoiceID_invoiceID($invoice_id){
        $sqlText ="select TxnId,total,order_id,customer from invoice
        where ID ='{$invoice_id}' LIMIT 1 ";

        $result = mysqli_query($this->con,$sqlText);

        $list=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function qbUpdateQBPaymentIntoPayaccTable($pay_id,$qb_payment){
        $updateCommand = "UPDATE `pay_acct`
                SET qb_payment_id = '{$qb_payment}'
				WHERE pay_id = '{$pay_id}'";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            return $qb_payment;
        }else{
            return mysqli_error($this->con);
        }
    }

    //----------------------------------------------------------
    public function qbInsertAccount($accountName,$account_type,$qb_account_id){
        $values ="'{$accountName}','{$account_type}','{$qb_account_id}'";
        $colunm ="account_name,type,qb_account_id";

        $query = "INSERT INTO qb_chart_of_accounts ({$colunm}) VALUES({$values})";
        mysqli_query($this->con,$query);
        mysqli_insert_id($this->con);
    }

    //----------------------------------------------------------
    public function qbGet_QB_accountID($type){
        $sqlText ="select qb_account_id from qb_chart_of_accounts
        where type ='{$type}' LIMIT 1 ";

        $result = mysqli_query($this->con,$sqlText);

        $list="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row["qb_account_id"];
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    function stateNameToKey($value){
        $key =$value;
        switch($value){
            case 'Alaska':
                $key = 'AK';
                break;
            case 'Alabama':
                $key = 'AL';
                break;
            case 'Arkansas':
                $key = 'AR';
                break;
            case 'Arizona':
                $key = 'AZ';
                break;
            case 'California':
                $key = 'CA';
                break;
            case 'Colorado':
                $key = 'CO';
                break;
            case 'Connecticut':
                $key = 'CT';
                break;
            case '':
                $key = '';
                break;
            case 'District of Columbia':
                $key = 'DC';
                break;
            case 'Delaware':
                $key = 'DE';
                break;
            case 'Florida':
                $key = 'FL';
                break;
            case 'Georgia':
                $key = 'GA';
                break;
            case 'Hawaii':
                $key = 'HI';
                break;
            case 'Iowa':
                $key = 'IA';
                break;
            case 'Idaho':
                $key = 'ID';
                break;
            case 'Illinois':
                $key = 'IL';
                break;
            case 'Indiana':
                $key = 'IN';
                break;
            case 'Kansas':
                $key = 'KS';
                break;
            case 'Kentucky':
                $key = 'KY';
                break;
            case 'Louisiana':
                $key = 'LA';
                break;
            case 'Massachusetts':
                $key = 'MA';
                break;
            case 'Maryland':
                $key = 'MD';
                break;
            case 'Maine':
                $key = 'ME';
                break;
            case 'Michigan':
                $key = 'MI';
                break;
            case 'Minnesota':
                $key = 'MN';
                break;
            case 'Missouri':
                $key = 'MO';
                break;
            case 'Mississippi':
                $key = 'MS';
                break;
            case 'Montana':
                $key = 'MT';
                break;
            case 'North Carolina':
                $key = 'NC';
                break;
            case 'North Dakota':
                $key = 'ND';
                break;
            case 'Nebraska':
                $key = 'NE';
                break;
            case 'New Hampshire':
                $key = 'NH';
                break;
            case 'New Jersey':
                $key = 'NJ';
                break;
            case 'New Mexico':
                $key = 'NM';
                break;
            case 'Nevada':
                $key = 'NV';
                break;
            case 'New York':
                $key = 'NY';
                break;
            case 'Ohio':
                $key = 'OH';
                break;
            case 'Oklahoma':
                $key = 'OK';
                break;
            case 'Oregon':
                $key = 'OR';
                break;
            case 'Pennsylvania':
                $key = 'PA';
                break;
            case 'Rhode Island':
                $key = 'RI';
                break;
            case 'South Carolina':
                $key = 'SC';
                break;
            case 'South Dakota':
                $key = 'SD';
                break;
            case 'Tennessee':
                $key = 'TN';
                break;
            case 'Texas':
                $key = 'TX';
                break;
            case 'Utah':
                $key = 'UT';
                break;
            case 'Virginia':
                $key = 'VA';
                break;
            case 'Vermont':
                $key = 'VT';
                break;
            case 'Washington':
                $key = 'WA';
                break;
            case 'Wisconsin':
                $key = 'WI';
                break;
            case 'West Virginia':
                $key = 'WV';
                break;
            case 'Wyoming':
                $key = 'WY';
                break;
        }

        return $key;

    }

    //----------------------------------------------------------
    public function qbGetProductByItemRef($ItemRef){
        $sqlText ="select * from products
        where ItemRef ='{$ItemRef}' LIMIT 1 ";

        $result = mysqli_query($this->con,$sqlText);

        $list=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function qbGetOrdertByTaxId($TaxId){
        $sqlText ="select o.order_id,o.balance,o.bill_to,
        o.payment,o.salesperson,o.total,o.warranty,
        o.order_title,o.discount_code,
        o.contract_overage,o.grand_total,
        o.subscription  from invoice as i
        left join orders as o on o.order_id = i.order_id
        where TxnId ='{$TaxId}' LIMIT 1 ";

        $result = mysqli_query($this->con,$sqlText);

        $list=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }
   //----------------------------------------------------------
    public function qbGetContactID_qb_customer_id($qb_customer_id){
        $sqlText ="select * from contact
        where qb_customer_id ='{$qb_customer_id}' limit 1";
        $result = mysqli_query($this->con,$sqlText);

        $info=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $info = $row;
            }
        }

        return $info;
    }

    /////////////////////////////////////////////////////////
}