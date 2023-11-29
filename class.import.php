<?php
require_once 'class.common.php';
class Import extends Common{
    //------------------------------------------------
    public function validateProductFields($SKU,$prod_name,$prod_type,$prod_class)
    {
        $error = false;
        $errorMsg = "";

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
    //------------------------------------------------
    public function product_existing($sku) {

        $query = "SELECT count(*) FROM `products` where SKU='{$sku}'";
        $isExs = $this->checkExists($query);
        if($isExs) return 1;
        else return "";
    }

    //------------------------------------------------
    public function getproduct_SKU($sku) {
        $query = "SELECT ID FROM `products` where SKU='{$sku}' limit 1";
        $rsl = mysqli_query($this->con,$query);
        $id="";
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $id = $row['ID'];
            }
        }
        return $id;
    }

    //------------------------------------------------
    public function addProduct($product_notes,$product_tags,$product_taxable,$product_updated_by,$prod_class,
                               $prod_cost,$prod_desc,$prod_desc_short,$prod_height,$prod_inactive,
                               $prod_length,$prod_name,$prod_price,$prod_type,
                               $prod_visible, $prod_weight,$prod_width,$SKU,$photoName,$prod_internal_visible,$id_ref=null)
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
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        if(is_numeric($idreturn)) {
            if(is_numeric($id_ref)&&!empty($id_ref)){
                $fields = "id,id_ref";
                $values = "'{$idreturn}','{$id_ref}'";
                mysqli_query($this->con,"INSERT INTO product_ref_crm({$fields}) VALUES({$values})");
            }
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }

    }

    //------------------------------------------------
    public function updateProduct($id,$product_notes,$product_tags,$product_taxable,$product_updated_by,$prod_class,
                                  $prod_cost,$prod_desc,$prod_desc_short,$prod_height,$prod_inactive,
                                  $prod_length,$prod_name,$prod_price,$prod_type,
                                  $prod_visible, $prod_weight,$prod_width,$SKU,$photoPath,$prod_internal_visible,$id_ref=null)
    {

        //$dateTemp = new DateTime($product_updated);
        //$dateTemp = $dateTemp->format("Y-m-d H:i:s");
        $dateTemp = date("Y-m-d");
        $update = "UPDATE `products`
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


        $selectCommand ="SELECT COUNT(*) AS NUM FROM products WHERE `ID` <> '{$id}' AND `SKU` ='{$SKU}'";

        if ($this->checkExists($selectCommand)) return "The SKU doesn't already";

        $selectCommand ="SELECT COUNT(*) AS NUM FROM products WHERE `ID` = '{$id}'";
        if (!$this->checkExists($selectCommand)) return false;

        $isUpdate = mysqli_query($this->con,$update);
        //die($update);
        if($isUpdate){
            if(is_numeric($id_ref)&& !empty($id_ref)){
                $check ="SELECT COUNT(*) AS NUM FROM product_ref_crm WHERE `id_ref` ='{$id_ref}'";

                if (!$this->checkExists($check)){
                    $fields = "id,id_ref";
                    $values = "'{$id}','{$id_ref}'";
                    mysqli_query($this->con,"INSERT INTO product_ref_crm({$fields}) VALUES({$values})");
                };
            }

            return 1;
        }else{
            return 0;
        }

    }

    //------------------------------------------------
    /*
    public function addProduct($Id,$product_tags,$product_taxable,$prod_class,
                               $prod_cost=null,$prod_inactive,$prod_name,$prod_price,$prod_type,
                               $prod_visible,$SKU,$prod_internal_visible)
    {

        $fields = "product_tags,product_taxable,prod_class,
                            prod_cost,prod_inactive,prod_name,prod_price,prod_type,
                            prod_visible, SKU,product_added,prod_internal_visible";

        $dateTemp = date("Y-m-d");

        $values = "'{$product_tags}','{$product_taxable}','{$prod_class}',
                '{$prod_cost}','{$prod_inactive}','{$prod_name}','{$prod_price}','{$prod_type}',
                '{$prod_visible}','{$SKU}','{$dateTemp}','{$prod_internal_visible}'";

        $selectCommand ="SELECT COUNT(*) AS NUM FROM products WHERE `SKU` = '{$SKU}'";
        if ($this->checkExists($selectCommand)) return "The SKU doesn't already";

        $insertCommand = "INSERT INTO products({$fields}) VALUES({$values})";

        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        if(is_numeric($idreturn)) {
            $fields = "id,id_ref";
            $values = "'{$idreturn}','{$Id}'";
            mysqli_query($this->con,"INSERT INTO product_ref_crm({$fields}) VALUES({$values})");
            return $idreturn;
        }else{
           return mysqli_error($this->con);
        }

    }
    */
    //-------------------------------------------------
    /*
    public function updateProduct($id,$product_tags,$product_taxable,$prod_class,
                                  $prod_cost,$prod_inactive,$prod_name,
                                  $prod_price,$prod_type,
                                  $prod_visible,$SKU,$prod_internal_visible)
    {

        //$dateTemp = new DateTime($product_updated);
        //$dateTemp = $dateTemp->format("Y-m-d H:i:s");
        $dateTemp = date("Y-m-d");
        $updateCommand = "UPDATE `products` SET
                product_tags = '{$product_tags}',
                product_taxable = '{$product_taxable}',
                prod_class = '{$prod_class}',
                prod_cost = '{$prod_cost}',
                prod_inactive = '{$prod_inactive}',
                prod_name = '{$prod_name}',
                prod_price = '{$prod_price}',
                prod_type = '{$prod_type}',
                prod_visible = '{$prod_visible}',
                SKU = '{$SKU}',
                product_updated = '{$dateTemp}',
                prod_internal_visible ='{$prod_internal_visible}'

                WHERE ID = '{$id}' AND SKU = '{$SKU}'";


//die($updateCommand);
        $selectCommand ="SELECT COUNT(*) AS NUM FROM products WHERE `ID` <> '{$id}' AND `SKU` ='{$SKU}'";

        if ($this->checkExists($selectCommand)) return "The SKU doesn't already";

        $selectCommand ="SELECT COUNT(*) AS NUM FROM products WHERE `ID` = '{$id}'";
        if (!$this->checkExists($selectCommand)) return false;

        $update = mysqli_query($this->con,$updateCommand);
        //die($updateCommand);
        if($update){
            return 1;
        }else{
            return mysqli_error($this->con);
        }

    }
    */

    /////////////////////////////////////////////////////////
}