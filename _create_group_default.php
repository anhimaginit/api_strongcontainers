<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.acl.php';
    $Object = new ACL();
    $g_id ='';
    $g_department ="SystemAdmin";
    $group_name ="Admin default";
    $g_role ="Admin";
    $_users ="11593";
$ret = $Object->create_group_default($g_id,$g_department,$group_name,$g_role,$_users);

$g_department ="Employee";
$group_name ="User default";
$g_role ="User";
$_users ="";
$ret = $Object->create_group_default($g_id,$g_department,$group_name,$g_role,$_users);

$Object->close_conn();
echo json_encode($ret);




