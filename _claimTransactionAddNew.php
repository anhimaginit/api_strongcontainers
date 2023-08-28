<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
    $Object = new Claim();
    $EXPECTED = array('token','claim_ID','warranty_id','person','date_time','jwt','private_key');

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
        $isAuth = $Object->auth($jwt,$private_key);
        //$isAuth['AUTH'] =true;
        if($isAuth['AUTH']){
            //$acl = $isAuth['acl_list']; ''
            $claim_amount = $_POST["transaction"];
            if(is_array($claim_amount)) $claim_amount = json_encode($claim_amount);
            //--- validate
            $errObj = $Object->validateClaimTransFields($claim_ID,$person,$claim_amount);
            $claim_amount = $Object->protect($claim_amount);
            if(!$errObj['error']){
                if(empty($ID)){
                    $result = $Object->addClaimTransaction($claim_ID,$claim_amount,$person,$date_time,$warranty_id);

                    if(is_numeric($result) &&  $result){
                        $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result);
                    } else {
                        $info ="Claim Transaction: , claim_ID: ".$claim_ID.
                            ", person: ".$person.", person: ".$person. ",transaction: ".$claim_amount. "; ".$result;

                        $Object->err_log("ClaimTransaction",$info,0);

                        $ret = array('SAVE'=>'FAIL','ERROR'=>$result);
                    }

                }

            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>true);
            }

        }else{
            $ret = array('SAVE'=>'FAIL','AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }
    }

    $Object->close_conn();
    echo json_encode($ret);





