<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.invoice.php';
    $Object = new Invoice();

    $EXPECTED = array('token','balance','customer','invoiceid','order_id','payment',
        'paytype','salesperson','total','warranty');

$payments = $_POST['payments'];

if(is_array($payments)){
    foreach($payments as $pays){
        print_r($pays['ledger']);
        //update order
        die();
        //process data for ledger
        foreach($pays['ledger'] as $k=>$v){
            $val =""; $i++;
            $temp1 = array();
            foreach($v as $key=>$item){
                //create new array
                $temp1[$key] = $item;
            }

            $temp1["ledger_invoice_id"] = $idreturn;
            $temp1["ledger_create_date"] = $createTime;

            //create value and key
            foreach($temp1 as $kk=>$vv){
                $val .= empty($val) ? "" : ",";
                $val .= "'{$vv}'";
                //create key
                if($i==1){
                    $ledger_fields .= empty($ledger_fields) ? "" : ",";
                    $ledger_fields .= "{$kk}";
                }
            }

            $ledger_value .= empty($ledger_value) ? "" : ",";
            $ledger_value .= "({$val})";
        }
    }

}

    $Object->close_conn();
    echo json_encode($ret);




