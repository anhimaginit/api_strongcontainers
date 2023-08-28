<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
$Object = new Orders();

    $EXPECTED = array('token','order_id');

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
        $order_id ="202,198";
        $ret_temp = $Object->getClLimit_proIDs($order_id);


        die();
        if(count($ret_temp)>0){
            $t =  json_decode($ret_temp[0]['products_ordered'],true);
            $filter=array();
            $limit_list=array();

            for($i=0;$i<count($t);$i++){
                foreach($t[$i] as $k=>$v){
                    if(trim($v)=="Warranty" && $k=="prod_class"){
                        $filter[]=$t[$i];
                        $limit_list[] = $Object->getClLField_prodID($t[$i]["id"]);
                    }
                }
            }

            if(count($filter) >0){
                unset($ret_temp[0]['products_ordered']);

                $ret =array("order"=>$ret_temp,"products"=>$filter,"claim_limit"=>$limit_list) ;

            }else{
                $ret = $filter;
            }


        }else{
            $ret= $ret_temp;
        }

    }

    $Object->close_conn();
    echo json_encode($ret);

