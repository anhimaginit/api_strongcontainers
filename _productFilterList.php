<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.products.php';
$Object = new Products();

$EXPECTED = array('token','pageno','pagelength' ,'search_all','jwt','private_key');

foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    }else if (!empty($_GET[$key])) {
        ${$key} = $Object->protect($_GET[$key]);
    }
    else{
        ${$key} = NULL;
    }

}

$isAuth =$Object->basicAuth($token);
if(!$isAuth){
    $ret = array("list"=>[],"total"=>0,'ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    //$limit = empty($pagelength) ? 0 : $pagelength;
    $limit =1000;
    $offset = empty($pageno) ? 0 : ($pageno-1)*$pagelength;
    $list = array();

    $isAuth = $Object->auth($jwt,$private_key);
    //$isAuth['AUTH']=true;
    if($isAuth['AUTH']){
        $list = $Object->searchProductList($search_all,$limit,$offset);
        //$total = $Object->productTotal($search_all);


        $ret1 = array('AUTH'=>true,"list"=>$list,"total"=>0,"ERROR"=>"");
        $Object->close_conn();
        echo json_encode($ret1);
    }else{
        $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        $Object->close_conn();
        echo json_encode($ret);
    }


}
