<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
//require("./lib/PHPMailer-5.2.27/class.phpmailer.php");
//require("./lib/PHPMailer-5.2.27/class.smtp.php");
include_once './lib/class.verifyEmail.php';
require './lib/PHPMailer-5.2.27/PHPMailerAutoload.php';

date_default_timezone_set('Etc/UTC');

include_once './lib/class.report.php';
//$Object = new Report();
//$filter ="v53087,c131,v53071";
//$c = $Object->criteriaVendorCompany($filter);


$c =["ItemRef"=>["value" => 2,
    "name" => "Services1"]];
print_r($c); die();

/*include_once './lib/class.sandbox.php';*/
//include_once './lib/class.mergeitem.php';
//$Object = new Mergeitem();
  //$date = date("Y-m-d");
/*
$payment_date =$_POST['payment_date'];
$ispayment_date = $Object->is_Date($payment_date);

$payment_date ="";
if(empty($ispayment_date)) $payment_date = date('Y-m-d H:i:s');
print_r($payment_date); die();

$date=date_create($payment_date);
$payment_date=  date_format($date,"Y-m-d");
$payment_date1 =date($payment_date);
$d1= strtotime($payment_date1);
$dtemp= date("Y-m-d");
$d2= strtotime($dtemp);
if($d1>$d2){
    echo $payment_date1;
}else{
    echo $dtemp;
}*/
/*
$ContactID =$_POST['ContactID'];

//$rs=  $Object->getSalesArea_ContactID($ContactID);
$contact_was_merged =array("53134");
$data=array('contact_keep'=>53135,'contact_was_merged'=>$contact_was_merged);
$rs=  $Object->mergeClaim($data);
//$rs=  $Object->mergeContacts($data);
$contact_type="test";
$p= stripos($contact_type,"Sales");

print_r($rs); die();
*/







