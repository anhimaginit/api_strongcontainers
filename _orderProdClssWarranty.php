<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.orders.php';
$Object = new Orders();

    $EXPECTED = array('token','warrantyID');

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
        $ret_temp_warranty = $Object->getOders_ProductHasWarrantyClass($warrantyID);

        $ret_temp_AlaCarte = $Object->getOders_ProductHasALacarteClass($warrantyID);
        $ret_temp = array();
        if(count($ret_temp_warranty)>0){
            foreach($ret_temp_warranty as $item){
                foreach($ret_temp_AlaCarte as $key=>$temp){
                    if($item['order_id']== $temp['order_id']){
                        unset($ret_temp_AlaCarte[$key]);
                        break;
                    }
                }

            }
        }

        foreach($ret_temp_AlaCarte as $key=>$temp){
            $ret_temp[] =$temp;
        }

        //$aLaCartTemp = array_diff($ret_temp_AlaCarte,$ret_temp_warranty);
        //$aLaCarte=array();
        /*foreach($aLaCartTemp as $k=>$v){
            $aLaCarte[]=$v;
        }*/

        $ret = array("warrantyClass"=>$ret_temp_warranty,"aLaCartrClass"=>$ret_temp);
    }

    $Object->close_conn();
    echo json_encode($ret);

