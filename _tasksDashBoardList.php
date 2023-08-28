<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.task.php';
$Object = new Task();

$EXPECTED = array('token','pageno','pagelength' ,'search_all','limitDay','customer_id','private_key');

foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key]))
        ${$key} = $Object->protect($_POST[$key]);
    else
        ${$key} = NULL;
}

$list = array();
$isAuth =$Object->basicAuth($token);

$customer_id = 143;

if(!$isAuth){
    $ret = array("list"=>[],"total"=>0,'ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    $columns =['taskName','actionset','status','time','assign_id','createDate','doneDate','dueDate'];

    $limit = empty($pagelength) ? 0 : $pagelength;

    $offset = empty($pageno) ? 0 : ($pageno-1)*$pagelength;

    $list = $Object->getDashboardTaskList($columns,$search_all,$limit,$offset,$limitDay,$_POST['private_key']);
    //$total = $Object->warrantyTotal($columns,$search_all);
    $Object->close_conn();

    //$ret = array("list"=>$list,"total"=>$total);
    $ret = array("list"=>$list);
    echo json_encode($ret);
}