<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
$Object = new Orders();

   $temp ='[{"id":"101","price":"245","sku":"FPPC-002"},{"id":"104","price":"10","sku":"123456","prod_class":"Warranty" },{"id":"100","price":"238","sku":"FPP-001","prod_class":"Warranty"}]';
$value_Array = json_decode($temp);

$filter=array();
for($i=0;$i<count($value_Array);$i++){
    foreach($value_Array[$i] as $k=>$v){
        if(trim($v)=="Warranty"){
            $filter[]=$value_Array[$i];

        }
    }
}


echo json_encode($filter);

