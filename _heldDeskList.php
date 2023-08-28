<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.helpdesk.php';
    $Object = new Helpdesk();

    $EXPECTED = array('token','pageno','pagelength' ,'search_all');

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
    $ret = array('ERROR'=>'Authentication is failed','list'=>array());
}else{
    $columns =[ 'subject','problem','form',
        'status','assign_to_name'];
    $limit=1000;
    $offset = empty($pageno) ? 0 : ($pageno-1)*$pagelength;

    $result = $Object->searchHelpdeskList($columns=null,$search_all=null,$limit,$offset);
    $ret = array('list'=>$result['list'],'total_row'=>$result['totalRow'],'ERROR'=>'');
}
    $Object->close_conn();
    echo json_encode($ret);




