<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.warranty.php';
    $Object = new Warranty();

    $EXPECTED = array('token','warranty_address1','warranty_address2','warranty_buyer_agent_id','warranty_buyer_id','warranty_city',
        'warranty_creation_date','warranty_email','warranty_end_date','warranty_escrow_id','warranty_inactive',
        'warranty_length','warranty_mortgage_id','warranty_notes','warranty_order_id',
        'warranty_phone', 'warranty_postal_code','warranty_renewal','warranty_eagle','warranty_salesman_id',
        'warranty_seller_agent_id','warranty_serial_number','warranty_start_date',
        'warranty_state','warranty_update_by','warranty_update_date',
        'warranty_closing_date','warranty_contract_amount','warranty_charity_of_choice','jwt','private_key',
        'pro_ids');

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
        $ret = array('SAVE'=>'FAIL','ERROR'=>'Authentication is failed');
    }else{
        $isAuth = $Object->auth($jwt,$private_key);
        $isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            //$acl = $isAuth['acl_list'];

            $errObj = $Object->validate_warranty_fields($token,$warranty_address1,$warranty_city,$warranty_state,
                $warranty_postal_code,$warranty_buyer_id,$warranty_salesman_id,$warranty_start_date
               );

            if(!$errObj['error']){
                if($warranty_escrow_id=="" || empty($warranty_escrow_id)) {
                    $warranty_escrow_id =0;
                }

                if(empty($warranty_buyer_agent_id)){
                    $warranty_buyer_agent_id = 0;
                }

                if(empty($warranty_seller_agent_id)){
                    $warranty_seller_agent_id =0;
                }

                if(empty($warranty_mortgage_id)){
                    $warranty_mortgage_id =0;
                }

                if(empty($warranty_buyer_id)){
                    $warranty_buyer_id =0;
                }

                if(empty($warranty_length)){
                    $warranty_length =0;
                }

                if(empty($warranty_renewal)){
                    $warranty_renewal =0;
                }

                if(empty($warranty_eagle)){
                    $warranty_eagle =0;
                }

                if(empty($warranty_update_by)){
                    $warranty_update_by =0;
                }

                if(empty($warranty_inactive)){
                    $warranty_inactive =0;
                }

                if(empty($warranty_charity_of_choice)){
                    $warranty_charity_of_choice =0;
                }

                if(empty($warranty_closing_date)){
                    $warranty_closing_date =0;
                }

                if(empty($warranty_contract_amount)){
                    $warranty_contract_amount =0;
                }

                $notes =array();
                if(isset($_POST['notes'])){
                    $notes=$_POST['notes'];
                }

                //
                $currentTemplate=array();
                $p= stripos($pro_ids,",");
                if(is_numeric($p)){
                    $temp_prod = explode(",",$pro_ids);
                    foreach($temp_prod as $item){
                        $currentTemplate[]= $Object->getClLimit($item);
                    }
                }else{
                    $currentTemplate[]= $Object->getClLimit($pro_ids);
                }

                $temp_limit=array();

                $temp_limit = $Object->process_limit_new($currentTemplate);
                $limits =json_encode($temp_limit);
                //$limits = $Object->getClLimit_proIDs($pro_ids);
                //print_r($limits);
                 //die();

                $result = $Object->addWarranty($warranty_address1,$warranty_address2,$warranty_buyer_agent_id,$warranty_buyer_id,$warranty_city,
                    $warranty_creation_date,$warranty_end_date,$warranty_escrow_id,$warranty_inactive,
                    $warranty_length,$warranty_mortgage_id,$warranty_notes,$warranty_order_id,
                    $warranty_postal_code,$warranty_renewal,$warranty_salesman_id,
                    $warranty_seller_agent_id,$warranty_serial_number,$warranty_start_date,
                    $warranty_state,$warranty_update_by,$warranty_update_date,
                    $warranty_charity_of_choice,$warranty_closing_date,$warranty_contract_amount,$notes,$limits,$warranty_eagle);


                if(is_numeric($result) && $result){
                    $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result,'AUTH'=>true);
                    if($warranty_inactive ==1){
                        $Object->updateStatusPayment($warranty_order_id);
                    }

                } else {
                    //log errors
                    $info ="Warranty -- warranty_address1:".$warranty_address1. ", warranty_buyer_id: ".$warranty_buyer_id.
                        ", warranty_salesman_id: ".$warranty_salesman_id.", warranty_email ".$warranty_email.", err: ".$result;

                    $Object->err_log("Warranty",$info,0);

                    if($result){
                        $ret = array('SAVE'=>'FAIL','ERROR'=>$result,'AUTH'=>true);
                    }else{
                        $ret = array('SAVE'=>'FAIL','ERROR'=>'System can not add the warranty.','AUTH'=>true);
                    }

                }

            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>true);

            }

        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }


    $Object->close_conn();
    echo json_encode($ret);




