
<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
//header('Access-Control-Allow-Headers: Authorization, X-Requested-With');
//header('P3P: CP="NON DSP LAW CUR ADM DEV TAI PSA PSD HIS OUR DEL IND UNI PUR COM NAV INT DEM CNT STA POL HEA PRE LOC IVD SAM IVA OTC"');
//header('Access-Control-Max-Age: 1');

include_once './lib/class.orders.php';
$Object = new Orders();

$EXPECTED = array('token','limitDay','login_id','start_date','end_date','jwt','private_key');


foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key]))
        ${$key} = $Object->protect($_POST[$key]);
    else
        ${$key} = NULL;
}

 $isAuth =$Object->basicAuth($token);

$list = array();
if(!$isAuth){
    $ret = array("list"=>[]);
    $Object->close_conn();
    echo json_encode($ret);
} else {
    $isAuth = $Object->auth($jwt,$private_key);
    //$isAuth['AUTH']=true;
    if($isAuth['AUTH']){
        $role = $_POST['role'];
        //$list = $Object->order_open_total($limitDay,$login_id);
        $list = $Object->countOrder($limitDay,$login_id,$start_date,$end_date,$role);
        $ret = array('AUTH'=>true,"list"=>$list);
    }else{
        $ret = array("list"=>[],'AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
    }

    $Object->close_conn();

    echo json_encode($ret);
}
