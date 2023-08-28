<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.warranty.php';
require_once __DIR__ . '/lib/vendor_Mpdf/autoload.php';
    $Object = new Warranty();

    $EXPECTED = array('token','ID','warranty_address1','warranty_address2','warranty_buyer_agent_id','warranty_buyer_id','warranty_city',
        'warranty_creation_date','warranty_email','warranty_end_date','warranty_escrow_id','warranty_inactive',
        'warranty_length','warranty_mortgage_id','warranty_notes','warranty_order_id',
        'warranty_phone', 'warranty_postal_code','warranty_renewal','warranty_eagle','warranty_salesman_id',
        'warranty_seller_agent_id','warranty_serial_number','warranty_start_date',
        'warranty_state','warranty_update_by','warranty_update_date',
        'warranty_closing_date','warranty_contract_amount','warranty_charity_of_choice',
        'old_warranty_address1','jwt','private_key','pro_ids','warranty_payer_type',
        'diff_address','total','totalOver','warranty_corporate',
        'warranty_submitter','warranty_submitter_type','contract_overage');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array('SAVE'=>false,'ERROR'=>'Authentication is failed');
}else{
    $isAuth = $Object->auth($jwt,$private_key);

    if($isAuth['AUTH']){
        $acl = $isAuth['acl_list'];

        //--- validate
        $errObj = $Object->validate_warranty_fields($warranty_order_id,$warranty_address1,$warranty_city,$warranty_state,
            $warranty_postal_code,$warranty_buyer_id,$warranty_salesman_id,$warranty_start_date
            );

        if(!$errObj['error']){
            if($warranty_escrow_id=="" || empty($warranty_escrow_id)) {
                $warranty_escrow_id =0;
            }

            if(empty($warranty_salesman_id)){
                $warranty_salesman_id = 0;
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

            if(empty($warranty_contract_amount)){
                $warranty_contract_amount =0;
            }

            if(empty($warranty_payer_type)) $warranty_payer_type=0;
            if(empty($warranty_corporate)) $warranty_corporate=0;

            $notes =array();
            if(isset($_POST['notes'])){
                $notes=$_POST['notes'];
            }

            //
            $warranty_type=$pro_ids;
            $currentTemplate=array();

            $p= stripos($pro_ids,",");
            if(is_numeric($p)){
                $temp_prod = explode(",",$pro_ids);
                $temp_prod1 =array_count_values($temp_prod);

                foreach($temp_prod1 as $id_p =>$value_p){
                    $currentTemplate1=array();
                    $currentTemplate1= $Object->getClLimit($id_p);
                    if($value_p!=1){
                        $arr_temp=array();
                        foreach($currentTemplate1 as $kkk=>$vvv){
                             $arr_temp[$kkk]= $vvv*$value_p;
                        }
                        $currentTemplate[]=$arr_temp;

                    }else{
                        $currentTemplate[]=$currentTemplate1;
                    }
                }
            }else{
                $currentTemplate[]= $Object->getClLimit($pro_ids);
            }
            //print_r($currentTemplate);die();
            $temp_limit=array();

            //$temp_limit = $Object->process_limit_new($currentTemplate);
            $limits =json_encode($currentTemplate);
            $limits = $Object->protect($limits);

            //$limits = $Object->getClLimit_proIDs($pro_ids);
            //print_r($limits); die();

            $result = $Object->updateWarranty($ID,$warranty_address1,$warranty_address2,$warranty_buyer_agent_id,$warranty_buyer_id,$warranty_city,
                $warranty_creation_date,$warranty_end_date,$warranty_escrow_id,$warranty_inactive,
                $warranty_length,$warranty_mortgage_id,$warranty_notes,$warranty_order_id,
                 $warranty_postal_code,$warranty_renewal,$warranty_salesman_id,
                $warranty_seller_agent_id,$warranty_serial_number,$warranty_start_date,
                $warranty_state,$warranty_update_by,$warranty_update_date,
                $warranty_charity_of_choice,$warranty_closing_date,$warranty_contract_amount,$notes,$limits,
                $old_warranty_address1,$warranty_eagle,$warranty_type,$warranty_payer_type,$warranty_corporate,
                $warranty_submitter,$warranty_submitter_type,$contract_overage
            );

            if(is_numeric($result)){

                if($warranty_inactive ==1){
                    $Object->updateStatusPayment($warranty_order_id);
                }

                $leadToPolicy='';
                if(is_numeric($warranty_buyer_id) && !empty($warranty_buyer_id)){
                    $leadToPolicy = $Object->ConvertCtactTypeLeadPolicy_ID($warranty_buyer_id);
                }
                //SEND EMAIL
                //--------------------------------
                $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','AUTH'=>true,'leadToPolicy'=>$leadToPolicy);

            } else {
                //log errors
                if(is_array($result)) $result =json_encode($result);
                $info ="Warranty -- warranty_address1:".$warranty_address1. ", warranty_buyer_id: ".$warranty_buyer_id.
                    ", warranty_salesman_id: ".$warranty_salesman_id.", warranty_email ".$warranty_email.", err: ".$result;

                $Object->err_log("Warranty",$info,$ID);

                if($result){
                    $ret = array('SAVE'=>'FAIL','ERROR'=>$result,'AUTH'=>true);
                }else{
                    $ret = array('SAVE'=>'FAIL','ERROR'=>"System can't edit the warranty.",'AUTH'=>true);
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




