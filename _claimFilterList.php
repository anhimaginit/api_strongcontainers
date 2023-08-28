<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.claim.php';
$Object = new Claim();

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

$list = array();
$isAuth =$Object->basicAuth($token);

if(!$isAuth){
    $ret = array("list"=>[],"total"=>0,'ERROR'=>'Authentication is failed');
    $Object->close_conn();
    echo json_encode($ret);
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    $isAuth['AUTH']=true;
    if($isAuth['AUTH']){
        //$acl = $isAuth['acl_list'];
        $columns =[ 'status','date_start','create_by_name',
            'contact_name','employee_name','paid'];

        $limit = empty($pagelength) ? 0 : $pagelength;

        $offset = empty($pageno) ? 0 : ($pageno-1)*$pagelength;

        $role = $_POST['role'];
        //$claimIDs = $_POST['claimIDs'];

        $list = $Object->searchClaimList($columns,$search_all,$limit,$offset,$role,$private_key);
        //$total = $Object->claimTotalRecords($columns,$search_all,$role,$private_key);
        $Object->close_conn();

        $ret = array("list"=>$list,"total"=>0,'AUTH'=>true);

    }else{
        $ret = array("list"=>[],"total"=>0,'AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
    }


    echo json_encode($ret);
}


