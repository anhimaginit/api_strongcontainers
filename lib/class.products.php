<?php

require_once 'class.common.php';
class Products extends Common{
    //------------------------------------------------------
    public function getProductType()
    {
        $sqlText = "Select * From product_type";

        $list = $this->getList($sqlText);
        return $list;
    }

    //------------------------------------------------------
    public function getProductClass()
    {
        $sqlText = "Select * From product_class";

        $list = $this->getList($sqlText);
        return $list;
    }

    //--------------------------------------------------------------
    public function validate_product_fields($token,$SKU,$prod_name,$prod_type,$prod_class)
    {
        $error = false;
        $errorMsg = "";
        //--- $token
        if(!$error && empty($token)){
            $error = true;
            $errorMsg = "Token is required.";
        }

        if(!$error && empty($SKU)){
            $error = true;
            $errorMsg = "SKU is required.";
        }

        if(!$error && empty($prod_name)){
            $error = true;
            $errorMsg = "Product name is required.";
        }
        if(!$error && empty($prod_type)){
            $error = true;
            $errorMsg = "Product type is required.";
        }

        if(!$error && empty($prod_class)){
            $error = true;
            $errorMsg = "Product class is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }
    //------------------------------------------------------------------
    public function addProduct($product_notes,$product_tags,$product_taxable,$product_updated_by,$prod_class,
                               $prod_cost,$prod_desc,$prod_desc_short,$prod_height,$prod_inactive,
                               $prod_length,$prod_name,$prod_price,$prod_type,
                               $prod_visible, $prod_weight,$prod_width,$SKU,$photoName,$prod_internal_visible)
    {

        $fields = "product_notes,product_tags,product_taxable,product_updated_by,prod_class,
                            prod_cost,prod_desc,prod_desc_short,prod_height,prod_inactive,
                            prod_length,prod_name,prod_price,prod_type,
                            prod_visible, prod_weight,prod_width,SKU,product_added,prod_photo,prod_internal_visible";

        $dateTemp = date("Y-m-d");

        $values = "'{$product_notes}','{$product_tags}','{$product_taxable}','{$product_updated_by}','{$prod_class}',
                '{$prod_cost}','{$prod_desc}','{$prod_desc_short}','{$prod_height}','{$prod_inactive}',
                '{$prod_length}','{$prod_name}','{$prod_price}','{$prod_type}',
                '{$prod_visible}','{$prod_weight}','{$prod_width}','{$SKU}','{$dateTemp}','{$photoName}','{$prod_internal_visible}'";

        $selectCommand ="SELECT COUNT(*) AS NUM FROM products WHERE `SKU` = '{$SKU}'";
        if ($this->checkExists($selectCommand)) return "The SKU doesn't already";

        $insertCommand = "INSERT INTO products({$fields}) VALUES({$values})";
        //die($insertCommand);
        //echo json_encode($insertCommand);
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        return $idreturn;

    }

    //-------------------------------------------------
    public function updateProduct($id,$product_notes,$product_tags,$product_taxable,$product_updated_by,$prod_class,
                                  $prod_cost,$prod_desc,$prod_desc_short,$prod_height,$prod_inactive,
                                  $prod_length,$prod_name,$prod_price,$prod_type,
                                  $prod_visible, $prod_weight,$prod_width,$SKU,$product_updated,$photoPath,$prod_internal_visible)
    {

        //$dateTemp = new DateTime($product_updated);
        //$dateTemp = $dateTemp->format("Y-m-d H:i:s");
        $dateTemp = date("Y-m-d");
        if(!empty($photoPath)){
            $updateCommand = "UPDATE `products`
                SET product_notes = '{$product_notes}',
                product_tags = '{$product_tags}',
                product_taxable = '{$product_taxable}',
                product_updated_by = '{$product_updated_by}',
                prod_class = '{$prod_class}',
                prod_cost = '{$prod_cost}',
                prod_desc = '{$prod_desc}',
                prod_desc_short = '{$prod_desc_short}',
                prod_height = '{$prod_height}',
                prod_inactive = '{$prod_inactive}',
                prod_length = '{$prod_length}',
                prod_name = '{$prod_name}',
                prod_price = '{$prod_price}',
                prod_type = '{$prod_type}',
                prod_visible = '{$prod_visible}',
                prod_weight = '{$prod_weight}',
                prod_width = '{$prod_width}',
                SKU = '{$SKU}',
                prod_photo ='{$photoPath}',
                product_updated = '{$dateTemp}',
                prod_internal_visible ='{$prod_internal_visible}'

                WHERE ID = '{$id}' AND SKU = '{$SKU}'";
        }else{
            $updateCommand = "UPDATE `products`
                SET product_notes = '{$product_notes}',
                product_tags = '{$product_tags}',
                product_taxable = '{$product_taxable}',
                product_updated_by = '{$product_updated_by}',
                prod_class = '{$prod_class}',
                prod_cost = '{$prod_cost}',
                prod_desc = '{$prod_desc}',
                prod_desc_short = '{$prod_desc_short}',
                prod_height = '{$prod_height}',
                prod_inactive = '{$prod_inactive}',
                prod_length = '{$prod_length}',
                prod_name = '{$prod_name}',
                prod_price = '{$prod_price}',
                prod_type = '{$prod_type}',
                prod_visible = '{$prod_visible}',
                prod_weight = '{$prod_weight}',
                prod_width = '{$prod_width}',
                SKU = '{$SKU}',
                product_updated = '{$dateTemp}',
                prod_internal_visible ='{$prod_internal_visible}'

                WHERE ID = '{$id}' AND SKU = '{$SKU}'";
        }
        $selectCommand ="SELECT COUNT(*) AS NUM FROM products WHERE `ID` <> '{$id}' AND `SKU` ='{$SKU}'";

        if ($this->checkExists($selectCommand)) return "The SKU doesn't already";

        $selectCommand ="SELECT COUNT(*) AS NUM FROM products WHERE `ID` = '{$id}'";
        if (!$this->checkExists($selectCommand)) return false;

        $update = mysqli_query($this->con,$updateCommand);
        //die($updateCommand);
        if($update){
            return 1;
        }else{
            return 0;
        }

    }

    //------------------------------------------------------------
    public function productTotal($filterAll=null)
    {
        $sqlText = "Select count(*) From products_short";
        $criteria = "";

        if(!empty($filterAll)){
            $criteria .= " ((SKU LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_cost LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_price LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_desc LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_type LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_class LIKE '%{$filterAll}%'))";
        }
        /*
        if(count($filters)>0){
            foreach($filters as $key=>$value){
                if($key == 'SKU'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (SKU LIKE '%{$value}%') ";
                }  else if($key == 'prod_name'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_name  LIKE '%{$value}%') ";
                }  else if($key == 'prod_cost'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_cost  LIKE '%{$value}%') ";
                }  else if($key == 'prod_price'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_price LIKE '%{$value}%') ";
                }else if($key == 'prod_desc'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_desc LIKE '%{$value}%') ";
                }else if($key == 'prod_type'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_type LIKE '%{$value}%') ";
                }else if($key == 'prod_class'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_class LIKE '%{$value}%') ";
                }
            }
        }
        */
        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }

        //die($sqlText);

        $num = $this->totalRecords($sqlText,0);

        return $num;
    }

    //------------------------------------------------------------
    public function searchProductList($filterAll=null,$limit,$offset)
    {
        $sqlText = "Select * From products_short";
        $criteria = "";

        if(!empty($filterAll)){
            $criteria .= " ((SKU LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_name LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_cost LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_price LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_desc LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_type LIKE '%{$filterAll}%') OR ";
            $criteria .= " (prod_class LIKE '%{$filterAll}%'))";
        }
        /*
        if(count($filters)>0){
            foreach($filters as $key=>$value){
                if($key == 'SKU'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (SKU LIKE '%{$value}%') ";
                }  else if($key == 'prod_name'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_name  LIKE '%{$value}%') ";
                }  else if($key == 'prod_cost'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_cost  LIKE '%{$value}%') ";
                }  else if($key == 'prod_price'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_price LIKE '%{$value}%') ";
                }else if($key == 'prod_desc'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_desc LIKE '%{$value}%') ";
                }else if($key == 'prod_type'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_type LIKE '%{$value}%') ";
                }else if($key == 'prod_class'){
                    $criteria .= !empty($criteria) ? " AND " : "";
                    $criteria .= " (prod_class LIKE '%{$value}%') ";
                }
            }
        }
        */

        if(!empty($criteria)){
            $sqlText .= " WHERE ".$criteria;
        }

        $sqlText .= " ORDER BY ID";

        if(!empty($limit)){
            $sqlText .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $sqlText .= " OFFSET {$offset} ";
        }


        //
        //die($sqlText);

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        //print_r($list);
        return $list;
    }

    //------------------------------------------------
    public function existingSKU($sku,$product_id=null) {
        $sku = trim($sku);
        if(!empty($product_id)){
            $query = "SELECT count(*) FROM  products WHERE SKU = '{$sku}' AND ID= '{$product_id}' LIMIT 1";
            if (!$this->checkExists($query)){
                $query = "SELECT count(*) FROM  products WHERE SKU = '{$sku}' AND ID <> '{$product_id}' LIMIT 1";
                if ($this->checkExists($query)){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            $query = "SELECT count(*) FROM  products WHERE SKU = '{$sku}' LIMIT 1";
            if ($this->checkExists($query)){
                return true;
            }else{
                return false;
            }
        }
        /*
        $check = mysqli_query($this->con,$query);
        $row = mysqli_fetch_row($check);
        if ($row[0] > 0)
            return true;
        else
            return false;
        */
    }

    //------------------------------------------------
    public function getProductByID($ID) {
        $query = "SELECT * FROM  products WHERE ID = '{$ID}' LIMIT 1";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function deleteProduct($ID)
    {
        $deleteSQL = "DELETE FROM products WHERE ID = '{$ID}' ";
        mysqli_query($this->con,$deleteSQL);
        $delete = mysqli_affected_rows($this->con);
        if($delete){
            return true;
        } else {
            return false;
        }
    }

    //------------------------------------------------------------
    public function productsForOrder($sku)
    {
        if(empty($sku)){
            $sqlText = "Select * From products where prod_inactive = 0";
        }else{
            $sqlText = "Select * From products where prod_inactive = 0 AND SKU like '{$sku}%'";
        }

        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function prodForOrderByName($name)
    {
        if(empty($name)){
            $sqlText = "Select * From products where prod_inactive = '0'";
        }else{
            $sqlText = "Select * From products where prod_inactive = '0' AND prod_name like '%{$name}%'";
        }

        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }


    //------------------------------------------------------------
    public function validateFile($fileSize,$filename)
    {
        $error = false;
        $errorMsg = "";
        $filenamearr = explode(".",$filename);
        $fileExt = strtolower($filenamearr[1]);
        if ($filename=="" || $fileSize<=0) {
            return array('error'=>true,'errorMsg'=>$errorMsg);
        }
        else {
            $availableExt = array('png','PNG','jpeg','JPEG');
            if ($fileExt=="" ) {
                $error = true;
                $errorMsg = "Unsupported File with empty file name extension!";
            } else if(!in_array($fileExt,$availableExt)) {
                $error = true;
                $errorMsg = "Unsupported File Type ({$fileExt})!";
            }

        }
        return array('error'=>$error,'errorMsg'=>$errorMsg);

    }

    //------------------------------------------------------------
    public function uploadPhoto($file_Name,$fileContent) {
        $fdata = file_get_contents($fileContent);
        $filePath = $_SERVER["DOCUMENT_ROOT"] . "/photo/products/";
        $filePathUrl = $filePath.'/'.$file_Name;
        $upload =file_put_contents($filePathUrl, $fdata);

        return $upload;
    }

    //------------------------------------------------
    public function getProductByWordpressID($IDs) {
        $query = "SELECT * FROM  products as p
        left join product_ref_crm as p_crm on p_crm.id=p.ID
        WHERE p_crm.id_ref in ({$IDs})";
        //die($query);
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getProductByIDs_feedom($IDs) {
        $query = "SELECT * FROM  products
        WHERE ID in ({$IDs})";
        //die($query);
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getProductBySKUsFreedom($SKUs) {
        $query = "SELECT * FROM  products
        WHERE SKU in ({$SKUs})";
        //die($query);
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }
    //------------------------------------------------------------
    public function productsAlacarteForOrder($sku)
    {
        if(empty($sku)){
            $sqlText = "Select * From products where prod_inactive = 0 AND prod_class='A La Carte'";
        }else{
            $sqlText = "Select * From products where prod_inactive = 0 AND SKU like '{$sku}%' AND prod_class='A La Carte'";
        }

        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function prodAlacarteForOrderByName($name)
    {
        if(empty($name)){
            $sqlText = "Select * From products where prod_inactive = '0' AND prod_class='A La Carte'";
        }else{
            $sqlText = "Select * From products where prod_inactive = '0' AND prod_name like '%{$name}%' AND prod_class='A La Carte'";
        }

        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function aLaCarte_WarrantyProd()
    {
        $sqlText = "Select ID,SKU,prod_name,prod_class,prod_price,ID From products where prod_inactive = 0 AND (prod_class='A La Carte' OR prod_class='Warranty')
        ORDER BY prod_name";

        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function prodsSortW_A()
    {
        $sqlText = "Select ID,SKU,prod_name From products where prod_class ='Warranty' AND prod_inactive = 0
        ORDER BY ID ASC";

        $result = mysqli_query($this->con,$sqlText);
        $list_w = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list_w[] = $row;
            }
        }

        $sqlText = "Select ID,SKU,prod_name From products where prod_class ='A La Carte' AND prod_inactive = 0
        ORDER BY ID ASC";

        $result = mysqli_query($this->con,$sqlText);
        $list_a = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list_a[] = $row;
            }
        }

        $sqlText = "Select ID,SKU,prod_name From products where prod_class <>'Warranty' AND
            prod_class <>'A La Carte' AND prod_inactive = 0
        ORDER BY ID ASC";

        $result = mysqli_query($this->con,$sqlText);
        $list_not = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list_not[] = $row;
            }
        }


        return array('list_w'=>$list_w,'list_a'=>$list_a,'other'=>$list_not);
    }

    ////////////////quickbook////////////////////

    public function updateItemRefIntoProductTable($ItemRef,$productID){
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

    //------------------------------------------------
    public function getProductByItemRef($ItemRef) {
        $query = "SELECT * FROM  products WHERE ItemRef = '{$ItemRef}' LIMIT 1";
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