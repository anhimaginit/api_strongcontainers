
<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
//header('Access-Control-Allow-Headers: Authorization, X-Requested-With');
//header('P3P: CP="NON DSP LAW CUR ADM DEV TAI PSA PSD HIS OUR DEL IND UNI PUR COM NAV INT DEM CNT STA POL HEA PRE LOC IVD SAM IVA OTC"');
//header('Access-Control-Max-Age: 1');

include_once './lib/class.invoice.php';
$Object = new Invoice();

$EXPECTED = array('token','pageno','pagelength' ,'search_all','jwt','private_key','start','length');


foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key]))
    {
        ${$key} = $Object->protect($_POST[$key]);
    }else if (!empty($_GET[$key])) {
        ${$key} = $Object->protect($_GET[$key]);
    }
    else
    {
        ${$key} = NULL;
    }
}

 $isAuth =$Object->basicAuth($token);

$list = array();
if(!$isAuth){
    $ret = array("list"=>[],"total"=>0);
    $Object->close_conn();
    echo json_encode($ret);
} else {

    $columns_search =[ 'invoiceid','customer_name','sale_name','order_id',
        'createTime','total','payment','balance'];

    //$limit = empty($pagelength) ? 0 : $pagelength;
    if(empty($length)) $length=100;
    if(empty($start)) $start=0;
    $limit =$length;
    $pagelength=$length;
    $offset = $start; //empty($pageno) ? 0 : ($pageno-1)*$pagelength;

    $isAuth = $Object->auth($jwt,$private_key);
    $isAuth['AUTH']=true;
    if($isAuth['AUTH']){
        $role = $_POST['role'];

        $list = $Object->searchInvoiceList($columns_search,$search_all,$limit,$offset,$role,$private_key);
        $total = $Object->InvoiceTotalRecords($columns_search,$search_all,$role,$private_key);
        //$ret = $list;
        $ret = array("list"=>$list,"total"=>$total);
    }else{
        $ret = array("list"=>array(),"total"=>0);
    }

    $Object->close_conn();

    echo json_encode($ret);
}
