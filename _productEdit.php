<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.products.php';
include_once '_qbviacurl.php';

    $Object = new Products();

    $EXPECTED = array('token','ID','product_notes','product_tags','product_taxable','product_updated_by',
        'prod_class','prod_cost','prod_desc','prod_desc_short',
        'prod_height','prod_inactive','prod_length','prod_name','prod_price',
        'prod_type','prod_visible','prod_weight','prod_width',
        'SKU','product_updated','prod_file_name','prod_internal_visible','jwt','private_key');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed');
    }else{
        //--- validate
        if(empty($ID)){
            $ret = array('SAVE'=>false,'ERROR'=>'');
        }else{
            $isAuth = $Object->auth($jwt,$private_key);
            //$isAuth['AUTH']=true;
            if($isAuth['AUTH']){
//--- validate
                $errObj = $Object->validate_product_fields($token,$SKU,$prod_name,$prod_type,$prod_class);

                if(!$errObj['error']){
                    if(empty($prod_cost)) {
                        $prod_cost =0;
                    }

                    if(empty($prod_price)) {
                        $prod_price =0;
                    }

                    if(empty($prod_inactive)) {
                        $prod_inactive =0;
                    }

                    if(empty($prod_visible)) {
                        $prod_visible =0;
                    }

                    if(empty($prod_internal_visible)) {
                        $prod_internal_visible =0;
                    }

                    if(empty($product_taxable)) {
                        $product_taxable =0;
                    }

                    if(empty($prod_weight)) {
                        $prod_weight =0;
                    }

                    if(empty($prod_length)) {
                        $prod_length =0;
                    }

                    if(empty($prod_width)) {
                        $prod_width =0;
                    }

                    if(empty($prod_height)) {
                        $prod_height =0;
                    }

                    //upload image and return path upload file data:image/png;base64
                    $photoPath =""; $upload ="";  $name="";
                    if(isset($_POST['prod_photo'])){
                        //
                        $imageData = $_POST['prod_photo'];
                        if(!empty($prod_file_name)){
                            $ext = explode('.', $prod_file_name);
                            if(count($ext)>0){
                                $index = count($ext)-1;
                                if($ext[$index]=="pdf" || $ext[$index]=="PDF"){
                                    $repl = 'data:application/pdf;base64,';
                                }elseif($ext[$index]=="jpg" || $ext[$index]=="JPG"){
                                    $repl = 'data:image/jpeg;base64,';
                                }elseif($ext[$index]=="png" || $ext[$index]=="PNG"){
                                    $repl = 'data:image/png;base64,';
                                }

                                $data = str_replace(
                                    $repl,
                                    '',
                                    $imageData
                                );
                                //verify file is true
                                if(base64_encode(base64_decode($data, true)) === $data){
                                    list($type, $imageData) = explode(';', $imageData);
                                    list(,$extension) = explode('/',$type);
                                    list(,$imageData)      = explode(',', $imageData);

                                    $flag = in_array($extension,[ 'jpg', 'jpeg', 'gif', 'png', 'pdf','JPG', 'JPEG','PNG']);

                                    if($flag){
                                        $name ="/photo/products/".uniqid()."_".$prod_file_name;
                                        $photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$name; //'.'.$extension;
                                        $imageData = base64_decode($imageData);
                                        $upload = file_put_contents($photoPathTemp, $imageData);
                                    }
                                }
                            }
                        }
                        //
                        /*$imageData = $_POST['prod_photo'];

                        list($type, $imageData) = explode(';', $imageData);
                        list(,$extension) = explode('/',$type);
                        list(,$imageData)      = explode(',', $imageData);

                        $flag = in_array($extension,[ 'jpg', 'jpeg', 'gif', 'png' ]);
                        if($flag){
                            $name ="/photo/products/".uniqid()."_".$prod_file_name;
                            $photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$name; //'.'.$extension;
                            $imageData = base64_decode($imageData);
                            $upload = file_put_contents($photoPathTemp, $imageData);
                        }*/
                    }

                    if(is_numeric($upload)){
                        $upload ="SUCCESS";
                        $photoPath = $name;
                    }

                    //true continue to create new product
                    $result = $Object->updateProduct($ID,$product_notes,$product_tags,$product_taxable,$product_updated_by,$prod_class,
                        $prod_cost,$prod_desc,$prod_desc_short,$prod_height,$prod_inactive,
                        $prod_length,$prod_name,$prod_price,$prod_type,
                        $prod_visible, $prod_weight,$prod_width,$SKU,$product_updated,$photoPath,$prod_internal_visible);

                    //die($ID);
                    if($result && is_numeric($result)){
                        $Object->addTag("Product",$product_tags);
                        $prod_data = $Object->getProductByID($ID);

                        //$rsl["CreatedId"]="exsiting ItemRef";
                        //print_r($prod_data[0]['ItemRef']); die();
                        if(!is_numeric($prod_data[0]['ItemRef'])){
                            //update ItemRef quickbook into product table
                            $prod_price = empty($prod_price)?0:$prod_price;
                            $curlObj= new QBviaCurl();
                            $url = "_qbCreateItem.php";
                            $ItemName = $prod_name;
                            $UnitPrice = $prod_price;

                            $data = array(
                                "ItemName"=>$ItemName,
                                "UnitPrice"=>$prod_price,
                                "Sku"=>$SKU,
                                "Description"=>$prod_desc,
                                "PurchaseCost"=>$prod_cost,
                                "Taxable"=>$product_taxable,
                            );

                            $rsl=$curlObj->httpost_curl($url,$data);
                            unset($curlObj);
                            $rsl = json_decode($rsl,true);
                            if(isset($rsl["CreatedId"])){
                                if(is_numeric($rsl["CreatedId"]) && !empty($rsl["CreatedId"]) ){
                                    $rsl_customer= $Object->updateItemRefIntoProductTable($rsl["CreatedId"],$ID);
                                }else{
                                    $rsl["CreatedId"]="Can't create ItemRef";
                                }
                            }else{
                                $rsl["CreatedId"]="error 500";
                            }
                        } //end if(!is_numeric($prod_data[0]['ItemRef']))
                        else{
                            //update ItemRef quickbook into product table
                            $prod_price = empty($prod_price)?0:$prod_price;
                            $curlObj= new QBviaCurl();
                            $url = "_qbUpdateItem_Id.php";
                            $ItemName = $prod_name;
                            $UnitPrice = $prod_price;

                            $data = array(
                                "ItemName"=>$ItemName,
                                "UnitPrice"=>$prod_price,
                                "Sku"=>$SKU,
                                "Description"=>$prod_desc,
                                "PurchaseCost"=>$prod_cost,
                                "Taxable"=>$product_taxable,
                                "Id"=>$prod_data[0]['ItemRef']
                            );

                            $rsl=$curlObj->httpost_curl($url,$data);
                            unset($curlObj);
                            $rsl = json_decode($rsl,true);
                            if(isset($rsl["CreatedId"])){
                                if(is_numeric($rsl["CreatedId"]) && !empty($rsl["CreatedId"]) ){
                                    $rsl_customer= $Object->updateItemRefIntoProductTable($rsl["CreatedId"],$ID);
                                }else{
                                    $rsl["CreatedId"]="Can't create ItemRef";
                                }
                            }else{
                                $rsl["CreatedId"]="error 500";
                            }
                         //
                        } //end else
                        ////////////////
                        $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'', 'UPLOAD'=>$upload,'CreatedId'=>$rsl["CreatedId"]);
                        //

                    } else {
                        if($result){
                            $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$result, 'UPLOAD'=>$upload);
                        }else{
                            $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>'System can not update the product.', 'UPLOAD'=>$upload);
                        }
                    }

                } else {
                    $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
                }
            }else{
                $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
            }

        }
    }

    $Object->close_conn();
    echo json_encode($ret);





