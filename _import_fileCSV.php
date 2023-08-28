<?php
    $origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
    header('Access-Control-Allow-Credentials: true');
    include_once './lib/class.import.php';
   $Object = new Import();

$EXPECTED = array('token','jwt','private_key');

foreach ($EXPECTED AS $key) {
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    } else {
        ${$key} = NULL;
    }
}

//--- validate
$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    $isAuth['AUTH']=true;
    if($isAuth['AUTH']){
        $err_add=array();
        $err_up=array();
        $col =array();
        $row = 0;

        $data = $_POST['productFile'];
        foreach($data as $item) {
            $ID=0;
            $SKU='';
            $prod_name='';
            $prod_desc='';
            $prod_desc_short='';
            $prod_type='Physical';
            $prod_class='Marketing';
            $prod_cost=0;
            $prod_price=0;
            $product_taxable=0;
            $prod_weight=0;
            $prod_length=0;
            $prod_width=0;
            $prod_height=0;
            $product_tags='';
            $product_notes='';
            $prod_internal_visible=0;
            $prod_inactive=0;
            $product_updated_by='';
            $prod_photo='';
            $prod_visible=0;
            //

            foreach($item as $k=>$v){
                $v =$Object->protect($v);

                switch($k){
                    case 'ID':
                        $ID = $v;
                        break;
                    case 'SKU':
                        $SKU = $v;
                        break;
                    case 'prod_name':
                        $prod_name = $v;
                        break;
                    case 'prod_desc_short':
                        $prod_desc_short = $v;
                        break;
                    case 'prod_type':
                        $prod_type = $v;
                        break;
                    case 'prod_class':
                        if($v=='A La Carte'){
                            $prod_class = 'A La Carte';
                        }elseif($v=='Discount'){
                            $prod_class = 'Discount';
                        }elseif($v=='Discount'){
                            $prod_class = 'Discount';
                        }elseif($v=='Marketing'){
                            $prod_class = 'Marketing';
                        }elseif($v=='Warranty'){
                            $prod_class = 'Warranty';
                        }else{
                            $p= stripos($v,"A La Carte Options");
                            if(is_numeric($p)){
                                $prod_class = 'A La Carte';
                            }else{
                                $prod_class = 'Warranty';
                            }
                        }

                        break;
                    case 'prod_cost':
                        $currency_temp= stripos($v,"$");

                        if(is_numeric($currency_temp)){
                            $isNegative = stripos($v,"-");

                            $temp = explode('$',$v);

                            $cost_temp = $temp[1];
                            $prod_cost =floatval(str_replace(",","",$cost_temp));

                            if(is_numeric($isNegative)){
                                $prod_cost = "-".$prod_cost;
                            }

                        }else{

                            $prod_cost = $v;
                        }
                        break;
                    case 'prod_price':
                        $currency_temp= stripos($v,"$");

                        if(is_numeric($currency_temp)){
                            $isNegative = stripos($v,"-");

                            $temp = explode('$',$v);

                            $price_temp = $temp[1];
                            $prod_price =floatval(str_replace(",","",$price_temp));

                            if(is_numeric($isNegative)){
                                $prod_price = "-".$prod_price;
                            }

                        }else{
                            $prod_price = $v;
                        }

                        break;
                    case 'product_taxable':
                        $product_taxable = $v;
                        break;
                    case 'prod_weight':
                        $prod_weight = $v;
                        break;
                    case 'prod_length':
                        $prod_length = $v;
                        break;
                    case 'prod_width':
                        $prod_width = $v;
                        break;
                    case 'prod_height':
                        $prod_height = $v;
                        break;
                    case 'product_tags':
                        $product_tags = $v;
                        break;
                    case 'product_notes':
                        $product_notes = $v;
                        break;
                    case 'prod_internal_visible':
                        $prod_internal_visible = $v;
                        break;
                    case 'prod_inactive':
                        if($v=='Active'){
                            $prod_inactive=0;
                        }elseif($v=='1'){
                            $prod_inactive=0;
                        }else{
                            $prod_inactive=1;
                        }

                        break;
                    case 'product_updated_by':
                        $product_updated_by = $v;
                        break;
                    case 'prod_photo':
                        $prod_photo = $v;
                        break;
                    case 'prod_visible':
                        $prod_visible = $v;
                        break;

                }
            }

            //die();
            $errObj = $Object->validateProductFields($SKU,$prod_name,$prod_type,$prod_class);
            if(!$errObj['error']){
                //check is product exsiting
                $isExsiting=$Object->product_existing($SKU);

                if($isExsiting!=1){
                    $isID=$Object->addProduct($product_notes,$product_tags,$product_taxable,$product_updated_by,$prod_class,
                        $prod_cost,$prod_desc,$prod_desc_short,$prod_height,$prod_inactive,
                        $prod_length,$prod_name,$prod_price,$prod_type,
                        $prod_visible, $prod_weight,$prod_width,$SKU,$prod_photo,$prod_internal_visible,$ID);

                    if(is_numeric($isID)){
                        $Object->addTag("Product",$product_tags);
                    } else{
                        $err_add[]=array("err"=>$isID,"id"=>$ID,"SKU"=>$SKU,"prod_name"=>$prod_name,
                            "prod_type"=>$prod_type,"prod_class"=>$prod_class);
                    }

                }else{
                    $pro_id = $Object->getproduct_SKU($SKU);
                    $isID=$Object->updateProduct($pro_id,$product_notes,$product_tags,$product_taxable,$product_updated_by,$prod_class,
                        $prod_cost,$prod_desc,$prod_desc_short,$prod_height,$prod_inactive,
                        $prod_length,$prod_name,$prod_price,$prod_type,
                        $prod_visible, $prod_weight,$prod_width,$SKU,$prod_photo,$prod_internal_visible,$ID);

                    if(is_numeric($isID)){
                        $Object->addTag("Product",$product_tags);
                        $ret = array('ERROR'=>'');
                    }else{
                        $err_up[]=array("err"=>$errObj['errorMsg'],"id"=>$ID,"SKU"=>$SKU,"prod_name"=>$prod_name,
                            "prod_type"=>$prod_type,"prod_class"=>$prod_class);
                    }
                }
                //
            }else{
                $err_add[]=array("err"=>$errObj['errorMsg'],"id"=>$ID,"SKU"=>$SKU,"prod_name"=>$prod_name,
                "prod_type"=>$prod_type,"prod_class"=>$prod_class);
            }

        }

        $ret = array('ERROR_ADD'=>$err_add,'ERROR_UP'=>$err_up);
    }else{
        $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
    }
}


$Object->close_conn();
echo json_encode($ret);



