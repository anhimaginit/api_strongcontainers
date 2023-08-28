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

/*include_once './lib/class.sandbox.php';*/
include_once './lib/class.invoice.php';
$Object = new Invoice();
$id_login =$_POST['ID'];
$personal_filter='child_group';
//print_r($personal_filter);
if($personal_filter=='login_only'){
    $p = $Object->orderRelative($id_login);
    echo "1---"; print_r($p);
    if(count($p)>0){
        $p = implode(",",$p);
        $criteria = "(o.order_id IN ({$p}))";
    }
}elseif($personal_filter=='group'){
    $p = $Object->parentManageUsers($id_login);
    echo "2---"; print_r($p);
    if(count($p)>0){
        $p = implode(",",$p);
        $criteria = "(o.b_ID IN ({$p}))";
    }
}elseif($personal_filter=='child_group'){
    $p = $Object->userChild($id_login);
    echo "3---"; print_r($p);
    if(count($p)>0){
        $p = implode(",",$p);
        $criteria = "(o.b_ID IN ({$p}))";
    }
}

/*if(count($p)>0){
    $p = implode(",",$p);
    $criteria = "o.b_ID IN ({$p})";
}*/
//print_r($criteria);
die();
$second_phone="123,456";
$contact_id="197";
$rsl = $Object->addSecondPhone($second_phone,$contact_id);
die($rsl);

//----------------------
$numbers = explode("\n", '(111) 222-3333
((111) 222-3333
1112223333
111 222-3333
111-222-3333
(111)2223333
+11234567890
    1-8002353551
    +123-456-7899   -Hello!
+1 - 1234567890

');

$name = "abc1234567";
 format_phone("1234567890");
print_r(format_phone("a2345678901"));

foreach($numbers as $number)
{
    //print_r(format_phone_us($number)); echo "---";

    // print preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number). "\n";
}

function format_phone_us($phone) {
    // note: making sure we have something
    if(!isset($phone{3})) { return ''; }
    // note: strip out everything but numbers
    $phone = preg_replace("/[^0-9]/", "", $phone);
    $length = strlen($phone);
    switch($length) {
        case 7:
            return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
            break;
        case 10:
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
            break;
        case 11:
            return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1($2) $3-$4", $phone);
            break;
        default:
            return $phone;
            break;
    }
}

////


function format_phone1($phone)
{
    $phone = preg_replace("/[^\d]/","",$phone);

    if(strlen($phone) == 7)
        return preg_replace("/(\d{3})(\d{4})/", "$1-$2", $phone);
    elseif(strlen($phone) == 10)
        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "($1) $2-$3", $phone);
    else
        return $phone;
}


function format_phone($s){
    if(preg_match('/[a-zA-Z]{1}/', $s)){
        return '';
    }

    $result="";
    $rx = "/
        (1)?\D*     # optional country code
        (\d{3})?\D* # optional area code
        (\d{3})\D*  # first three
        (\d{4})     # last four
        (?:\D+|$)   # extension delimiter or EOL
        (\d*)       # optional extension
    /x";
    preg_match($rx, $s, $matches);
    if(!isset($matches[0])) return '';

    $country = $matches[1];
    $area = $matches[2];
    $three = $matches[3];
    $four = $matches[4];
    $ext = $matches[5];

    $out = "$three-$four";
    if(!empty($area)) $out = "($area) $out";
    if(!empty($country)) $out = "+$country-$out";
    if(!empty($ext)) $out .= "x$ext";

    return $out;
}
die();
/*

print_r($out); die();
*/
/*
$mailID=9;
$receiveID = 172;
$inbox=1;
$rsl = $Object->mail_open($mailID,$receiveID,$inbox);
print_r($rsl); die();
*/


/*
 $v='12/1/1942';
$old_date = explode('/', $v);
$new_data = $old_date[2].'-'.$old_date[1].'-'.$old_date[0];
$d = DateTime::createFromFormat('Y-m-d', $new_data);
$dateofbirth = $d->format('Y-m-d');
$dateofbirth = date($dateofbirth);
die($dateofbirth);
------------------
  $date ="2019-02-15 12:01:01";
$m_temp = explode(" ",$date);
if(isset($m_temp[1])){
    $format = 'Y-m-d H:i:s';
}else{
    $format = 'Y-m-d';
}
print_r($format);
$d = DateTime::createFromFormat($format, $date);
if($d && $d->format($format) == $date){
    print_r ($date);
}else{
    print_r("123");
}
die();
*/
/*
$str = 'PRO-001,PRO-100';
$str_arr = explode(',',$str);
$SKU_in='';
foreach($str_arr as $item){
    $SKU_in .=(empty($SKU_in))?"":",";
    $SKU_in .="'{$item}'";
}

$query = "SELECT * FROM  products
        WHERE SKU in ({$SKU_in})";

die($query);
*/
//-------Test DEND Firebase cloud message
    $devicesToken = [
            'dFMw4esMyt0:APA91bGFupxW43jd293-TEcWEfq_1qhEjuC2WV7jgutbbgG1Q_nMkMu7iu0H7OI-J7YqDPxDQ4GmTjMfMRMvX6FQBWzgg1-gYrODQkJEB6ePOAsSNoo6bjAvtElHwsbhB4EfzeTGmWpg',
            ];

    $data = [
        'name' => 'Peter Paker',
        'mission' => 'Kick ass Thanos!',
        'status' => 0,
    ];

    $messages = [
        'registration_ids' => $devicesToken,
        'data' => $data,
    ];

    /*$headers = [
        'Authorization: key=' . 'AAAAbgRcCA0:APA91bH61_sKXs8DLaGcO6J9-EgJxnuRj9ViE5tZdGMV01EkEF9mpjsOHNyoHwIk5KmC4tRvbDegQ5vo70zFQ8-bIEMLnUvXphYRww9Vo_NjAfVxOtV17H5Mq12AUY6c2rxupUpQ3R2W',
        'Content-Type: application/json',
    ];*/

    $headers = array(
        'Content-Type:application/json',
        'Authorization:key= AAAAbgRcCA0:APA91bH61_sKXs8DLaGcO6J9-EgJxnuRj9ViE5tZdGMV01EkEF9mpjsOHNyoHwIk5KmC4tRvbDegQ5vo70zFQ8-bIEMLnUvXphYRww9Vo_NjAfVxOtV17H5Mq12AUY6c2rxupUpQ3R2W'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messages));

    $result = curl_exec($ch);
    curl_close($ch);

    if ($result === FALSE) {
        //throw new Exception('FCM Send Error: '  .  curl_error($ch), 500);
        json_encode(curl_error($ch));
    }else{
        echo json_encode($result);
    }

die();
$wID=635;
$temp_prod[]=$Object->getProdByOrderID($wID);
//print_r($temp_prod);
//get template limit
if(count($temp_prod)>0){
    foreach($temp_prod as $item){
        if(count($item)>0){
            foreach($item as $it){
               $clTemp= $Object->getClLimit($it['id']);
                //multiple quality
                $currentTemplate[]=$Object->multipleLimit($clTemp,$it['quantity']);
            }
        }
    }
    print_r($currentTemplate);
   // $temp_limit = $Object->process_limit($currentTemplate);

}

die();
/*
include_once './lib/class.warranty.php';
$Object = new Warranty();
$warranty_salesman_id =197;
$info=$Object->getContact_ID($warranty_salesman_id);
$to_name = $info[0]['customer_name'];
$to_email = $info[0]['primary_email'];
print_r($info);die();

//logo
$LOGO ='<table width="700px" border="0">
		  <tbody>
			<tr>
			  <td width="20%"><img src="https://americas2.wpengine.com/wp-content/uploads/2018/04/freedom_logo-1.jpg"  alt="Freedom HW Logo" style="max-width:95%"/></td>
			  <td width="50%"><h3>Freedom Home Warranty</h3>
					707 24th Street <br>
					Ogden, UT 84401 <br>
					Accounting@FreedomHW.com</td>
			  <td width="30%"></td>
            </tr>
            <tr><td colspan=3><div style="text-align: center"><strong>ORDER</strong></div></td></tr>
          </tbody>
        </table>';

//order
$ORDER ='';

$orders =array();
$orders[]=591;

foreach($orders as $orderID){
    $tr='';
    $pro_ids = $Object->getProds_orderID($orderID);
    $order_total=$pro_ids[0]['total'];

    if(count($pro_ids[0]['products_ordered'])>0){
        foreach($pro_ids[0]['products_ordered'] as $item){
            $tr .= '<tr style="border: 1px black solid;" align="center">
                  <td style="border: 1px black solid;">'.$item["sku"].'</td>
                  <td style="border: 1px black solid;">'.$item["prod_name"].'</td>
                  <td style="border: 1px black solid;">'.$item["quantity"].'</td>
                  <td style="border: 1px black solid;">'.$item["price"].'</td>
                  <td style="border: 1px black solid;">'.$item["line_total"].'</td></tr>';
        }

        $ORDER .='<table width="700px" style="border: 1px black solid;" cellpadding="0" cellspacing="0">
          <thead>
            <tr style="border: 1px black solid;" align="center">
              <th width="20%" style="border: 1px black solid;">SKU</th>
              <th width="30%" style="border: 1px black solid;">Product Name</th>
              <th width="15%" style="border: 1px black solid;">Quantity</th>
              <th width="15%" style="border: 1px black solid;">Price</th>
              <th width="20%" style="border: 1px black solid;">Total Line</th>
            </tr>
            <tr style="border: 1px black solid;" align="center">
              <th colspan="2" style="border: 1px black solid;">Order Title: '.$pro_ids[0]["order_title"].'</th>
              <th colspan="3" style="border: 1px black solid;">Invoice Date: '.$pro_ids[0]["invoiceDate"].'</th>
            </tr>
          </thead>
          <tbody>
                '.$tr.'
          </tbody>
        </table>';
    }
}

//Warranty
    $clientFirstName="";
    $clientLastName="";
    $charity =1;

    $warranty ='<table width="350px" style="border: 1px black solid; padding5px" cellpadding="0" cellspacing="0">
						  <tbody>
							<tr >
							  <td  style="border-bottom: 1px black solid; text-align: center; font-size: 15px" width="100%">WARRANTY INFO:</td>
							</tr>
							<tr>
							  <td style="padding:5px" width="100%">' . $clientFirstName . ' ' . $clientLastName . '<br>
								' . $warranty_address1 . ' <br>
								' . $warranty_address2 . ' <br>
							  ' . $warranty_city . ', ' . $warranty_state . ' ' . $warranty_postal_code . '</td>

							</tr>
						  </tbody>
						</table>';
    $charityInfo ='
		<table width="700px" style="border: 1px black solid; padding5px" cellpadding="0" cellspacing="0">
		  <tbody>
			<tr >
			  <td  style="border-bottom: 1px black solid; text-align: left; font-size: 13px" >Notes:</td>
			</tr>
			<tr>
			  <td style="padding:5px">Please remit payment to: <br>
				PO Box 150868 <br> South Ogden, UT 84415<br><br>
				Chosen Charity:<br>
				' . $charity . '
				</td>
			</tr>
		  </tbody>
		</table>';


$HTMLContent=$LOGO."<br>".$ORDER."<br>".$warranty."<br>".$charityInfo;

print_r($HTMLContent);die();

*/
/*
include_once './lib/class.orderstest.php';
$Object = new Orders();
$EXPECTED = array('token','order_id','balance','bill_to','note','payment','salesperson','total','warranty','order_title','jwt',
    'private_key','order_total','discount_code');

foreach ($EXPECTED AS $key) {
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    } else {
        ${$key} = NULL;
    }
}
*/
//$v_inf = $Object->getRelativeDataforVendor("172");

//$warrantiesIDs = $v_inf['warr_IDs'];

//$warrantiesIDs = $Object->ordersForVedor($warrantiesIDs,"172");

//die();

//$data = json_decode($subscription,true);
/*
$today = date('Y-m-d');
$date2 = date_create($today);
$invDate = $date2->format('Y-m-d');
$renewsDate =  date_create($invDate);
$paymentPeriod=10;
$date_interval = $paymentPeriod.'days';
date_add($date2, date_interval_create_from_date_string($date_interval));
$invDate = date_format($date2, 'Y-m-d');


$date10th = date_create("2019-09-06");
$invDate = date_create($invDate);
$diff=date_diff($invDate,$date10th);
$duration =  $diff->format("%a");
*/

$subscription = $_POST['subscription'];
if(empty($subscription)){
    $subscription='{}';
}

$subscription = json_decode($subscription,true);
if($payment >0){
    $total =$total -$payment;
    $sub =  $Object->getNumberOfPayInvoice_date($order_id,$subscription, $total);
    $amount = $sub['paymentAmount'];
}else{
    $sub =  $Object->initialAmountInvoice_date($subscription, $total);
    $amount = $sub['init_amount'] ;
    $sub['notchange']=1;
}

$invDate =$sub['invDate'];

$subscription['numberOfPay'] = $sub['numberOfPay'];
$subscription['paymentAmount'] = $sub['paymentAmount'];
$subscription['endDate'] = $sub['endDate'];

$order_total =$total + $sub['numberOfPay']* $subscription['processingFee'] + $subscription['initiedFee'];
$subscription = json_encode($subscription);
 print_r($sub);

//$r = $Object->getSub_OrderID('535');

die();
/*
include_once './lib/class.login.php';
$Object = new Login();
$EXPECTED = array('token','primary_email','primary_phone','primary_postal_code','login_type','ip','user_name','pass','type');

foreach ($EXPECTED AS $key) {
    if (!empty($_POST[$key])){
        ${$key} = $Object->protect($_POST[$key]);
    } else {
        ${$key} = NULL;
    }
}
$code = $Object->loginEmailPass1($login_type,$primary_email,$primary_phone,$primary_postal_code,$user_name,$pass,$type);
print_r($code);
die();
*/


$type='Affiliate';
$UID='172';
$rsl = $Object->test($type,$UID);

$rtn = $Object->processACL($rsl);
print_r($rsl);
die();

$date = "";
$format ='Y-m-d';
$d = DateTime::createFromFormat($format, $date);
if($d && $d->format($format) === $date){
    print_r($date);
}else{
    print_r("wrong");
}

die();
$var =10;
$a1=10;
$b1 =20;
$c1 =30;

switch($var){
    case 5<= $var && $var <=10:
        $c=5;
        break;

    case 11<= $var && $var <=20:
        $c=15;
        break;
    default: $c=0;
}

print_r($c); die();

$t = array(array("a"=>5,"b"=>10,"c"=>5),array("a"=>11,"b"=>20,"c"=>15));

$text = 'switch($var){';
   for($i=0;$i<count($t);$i++){
       $it = $t[$i];
       $text .="\n";
       $text.='case '.$it["a"].'<= $var && $var <='.$it["b"].':';
       $text .="\n";
       $text.= '$c='.$it["c"].';';
       $text .="\n";
       $text.='break;';
       $text .="\n";
   }

$text.='default: $c=0;';
$text .="\n";
$text.='}';

print_r($text);
die();

// Declare and define two dates
$a = round(4.79/2,2);
$b = 4.79/2;
//$a = number_format($b, 2, '.', '');
$b = 4.79/2;
//if($a <$b) $a = $a+0.01;

print_r($b.'-');
print_r($a); die();
/*
$date = new DateTime('2019-05-03');
$date->modify('last day of this month');
$invDate = $date->format('Y-m-d');
print_r($invDate);
die();*/


$date1 = '2020-03-01';
$date2 = '2020-05-15';

$ts1 = strtotime($date1);
$ts2 = strtotime($date2);

$year1 = date('Y', $ts1);
$year2 = date('Y', $ts2);

$month1 = date('m', $ts1);
$month2 = date('m', $ts2);

$diff = (($year2 - $year1) * 12) + ($month2 - $month1);
print_r($diff);
die();

/*
include_once './lib/class.orders.php';
$Object = new Orders();
$order_id ='415';
$sub = $Object->get_order_id($order_id);
print_r($sub['subscription']);

die();
*/
/*
include_once './lib/class.common.php';
$Object = new Common();
$order_id ='412';
$sub = $Object->getSubs_orderID($order_id);
print_r($sub['paymentPeriod']);
die();
*/
/*
$date = new DateTime('now');
$date->modify('first day of this month');
$invDate = $date->format('Y-m-d');
$renewsDate =  date_create($invDate);

$paymentPeriod =1;
$date_interval = $paymentPeriod.' months';
date_add($renewsDate, date_interval_create_from_date_string($date_interval));
$invDate = date_format($renewsDate, 'Y-m-d');
die($invDate);
*/

$inv_date ='2019-2-1';
$renewsDate =  date_create($inv_date);
$paymentPeriod =3;
$date_interval = $paymentPeriod.' months';
date_add($renewsDate, date_interval_create_from_date_string($date_interval));
$renewsDate = date_format($renewsDate, 'Y-m-d');
die($renewsDate);

$offSecondPayFee =true;
$billingDate ='15th of month';

//invoice date
if($billingDate =='1st of month'){
    $date = new DateTime('now');
    $date->modify('first day of this month');
    $invDate = $date->format('Y-m-d');

    if($offSecondPayFee ==true){
        //compare now with 10th of month
        $str_date = date('Y').'-'.date('m').'-9';
        $date10th = date_create($str_date);
        $currDate = date_create(date("Y-m-d"));

        $diff=date_diff($date10th,$currDate);
        $duration =  $diff->format("%a");

        if($duration >0){
            $m= date('m',strtotime('first day of +1 month'));
            $str_date = date('Y').'-'.$m.'-01';
            $date2 = date_create($str_date);
            $invDate = $date2->format('Y-m-d');
        }
    }

}elseif($billingDate =='15th of month'){
    $str_date = date('Y').'-'.date('m').'-15';
    $date2 = date_create($str_date);
    $invDate = $date2->format('Y-m-d');

    if($offSecondPayFee ==true){
        //compare now with 25th of month
        $str_date = date('Y').'-'.date('m').'-24';
        $date25th = date_create($str_date);
        $currDate = date_create(date("Y-m-d"));

        $diff=date_diff($date25th,$currDate,TRUE);
        print_r($diff);
        $duration =  $diff->format("%a");

        if($duration>0 && $currDate>$date25th){
            $m= date('m',strtotime('first day of +1 month'));
            $str_date = date('Y').'-'.$m.'-15';
            $date2 = date_create($str_date);
            $invDate = $date2->format('Y-m-d');
        }
    }

}else{
    //$billingDate =='30th of month'
    $date = new DateTime('now');
    $date->modify('last day of this month');
    $invDate = $date->format('Y-m-d');
}


die($invDate);

$m= date('m',strtotime('first day of +1 month'));
$str_date = date('Y').'-'.$m.'-15';
$date2 = date_create($str_date);
$invDate = $date2->format('Y-m-d');

echo $invDate; die();

$str_date = date('Y').'-'.date('m').'-15';
$date2 = date_create($str_date);
$invDate = $date2->format('Y-m-d');
echo $invDate; die();
//echo date('m',strtotime('first day of +1 month'));

$str_date = date('Y').'-'.date('m').'-10';
$date2 = date_create($str_date);
$date1 = date_create(date("Y-m-d"));

$diff=date_diff($date1,$date2);
echo $diff->format("%a");

die();

$date = new DateTime('now');
$date1=date_create("2019-02-01");
$date->modify('first day of this month');
//$date1->modify('last day of this month');
echo $date->format('Y-m-d');
die();
$d = date('Y-m-d H:i:s');
$temp = explode(" ",$d);
$temp2 = explode(":",$temp[1]);
$fileName = $temp[0].'-'.$temp2[0].'-'.$temp2[1].'-'.$temp2[2];
die($fileName);
/*$mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/lib/vendor1/mpdf/mpdf/tmp']);
$mpdf->WriteHTML('<h1>Hello world!</h1>
                   <h6>Hello world</h6>
                   <input style="border: none" value="convet to PDF">
                   <div style="color: red">Hello world</div>');

$pathname ="/photo/email_attachment/";
$photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$pathname;
$mpdf->Output($photoPathTemp.'/doc.pdf','F');
exit;
die();

//fpdf
require_once  './lib/vendor_pdf/fpdf.php';
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(40,10,'Hello World!');

$pathname ="/photo/email_attachment/";
$photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$pathname;
$pdf->Output($photoPathTemp.'/doc.pdf','F');

die();
*/



$url_origin = 'http://api.warrantyproject.com/';
/**
 * Simple example
 */

$date1 = date_create(date("Y-m-d"));
$date2=date_create("2019-04-30 00:00:00");
$diff=date_diff($date1,$date2,true);
echo $diff->format("%a");
print_r($date1);
 die();

$start_date ='2019-03-22';
$end_date ='';
$interval ='';
if(!empty($start_date) && !empty($end_date)){
    $interval .= "`create_date` >= '{$start_date}'";
    $interval .= "`create_date` =< '{$end_date}'";
}elseif(!empty($start_date) && empty($end_date)){
    $interval .= "`create_date` >= '{$start_date}'";
}elseif(empty($start_date) && !empty($end_date)){
    $interval .= "`create_date` =< '{$end_date}'";
}

$v ='Sales';

$temp = "select DISTINCT ID,first_name,last_name,primary_email,primary_phone,primary_city,
                    primary_postal_code,contact_inactive,primary_state
                    from contact_short
                where (contact_type like '%{$v}%') and contact_inactive = 0

                AND ".$interval;

die($temp);
echo substr_count("Hello world1. The world2 is nice","world");
die();
$assign_id ="1,172";

 $q = "select a.actionset, a.assign_id, a.createDate, a.customer_id,
                   a.doneDate, a.dueDate, a.id, a.status, a.taskName,a.time,
  concat(c.first_name,' ',c.last_name) as assign_name,
  concat(cus.first_name,' ',cus.last_name) as cus_name,
  from `assign_task` as a
                inner join contact as c on c.ID = a.assign_id
                inner join contact as cus on cus.ID =a.assign_id
                Where assign_id IN ({$assign_id}) ORDER BY dueDate DESC ";

die($q);


$nametokey = stateNameToKey('Utah');

print_r($nametokey);die();

function stateNameToKey($value){
    $key='';
    switch($value){
        case 'Alaska':
            $key = 'AK';
            break;
        case 'Alabama':
            $key = 'AL';
            break;
        case 'Arkansas':
            $key = 'AR';
            break;
        case 'Arizona':
            $key = 'AZ';
            break;
        case 'California':
            $key = 'CA';
            break;
        case 'Colorado':
            $key = 'CO';
            break;
        case 'Connecticut':
            $key = 'CT';
            break;
        case '':
            $key = '';
            break;
        case 'District of Columbia':
            $key = 'DC';
            break;
        case 'Delaware':
            $key = 'DE';
            break;
        case 'Florida':
            $key = 'FL';
            break;
        case 'Georgia':
            $key = 'GA';
            break;
        case 'Hawaii':
            $key = 'HI';
            break;
        case 'Iowa':
            $key = 'IA';
            break;
        case 'Idaho':
            $key = 'ID';
            break;
        case 'Illinois':
            $key = 'IL';
            break;
        case 'Indiana':
            $key = 'IN';
            break;
        case 'Kansas':
            $key = 'KS';
            break;
        case 'Kentucky':
            $key = 'KY';
            break;
        case 'Louisiana':
            $key = 'LA';
            break;
        case 'Massachusetts':
            $key = 'MA';
            break;
        case 'Maryland':
            $key = 'MD';
            break;
        case 'Maine':
            $key = 'ME';
            break;
        case 'Michigan':
            $key = 'MI';
            break;
        case 'Minnesota':
            $key = 'MN';
            break;
        case 'Missouri':
            $key = 'MO';
            break;
        case 'Mississippi':
            $key = 'MS';
            break;
        case 'Montana':
            $key = 'MT';
            break;
        case 'North Carolina':
            $key = 'NC';
            break;
        case 'North Dakota':
            $key = 'ND';
            break;
        case 'Nebraska':
            $key = 'NE';
            break;
        case 'New Hampshire':
            $key = 'NH';
            break;
        case 'New Jersey':
            $key = 'NJ';
            break;
        case 'New Mexico':
            $key = 'NM';
            break;
        case 'Nevada':
            $key = 'NV';
            break;
        case 'New York':
            $key = 'NY';
            break;
        case 'Ohio':
            $key = 'OH';
            break;
        case 'Oklahoma':
            $key = 'OK';
            break;
        case 'Oregon':
            $key = 'OR';
            break;
        case 'Pennsylvania':
            $key = 'PA';
            break;
        case 'Rhode Island':
            $key = 'RI';
            break;
        case 'South Carolina':
            $key = 'SC';
            break;
        case 'South Dakota':
            $key = 'SD';
            break;
        case 'Tennessee':
            $key = 'TN';
            break;
        case 'Texas':
            $key = 'TX';
            break;
        case 'Utah':
            $key = 'UT';
            break;
        case 'Virginia':
            $key = 'VA';
            break;
        case 'Vermont':
            $key = 'VT';
            break;
        case 'Washington':
            $key = 'WA';
            break;
        case 'Wisconsin':
            $key = 'WI';
            break;
        case 'West Virginia':
            $key = 'WV';
            break;
        case 'Wyoming':
            $key = 'WY';
            break;
    }

    return $key;
}


$buyer_data = array("token"=>"MjE0YTIwMzYxOTllNDdlZGU0OGI3ZTQ2OGM3OTZkYjUtdXMxOQ==",
"contact_inactive"=>0,
    "contact_notes"=>"Charity:Cancer Research",
    "contact_tags"=>"",
    "contact_type"=>"Affiliate",
    "first_name"=>"Delivery",
    "last_name"=>"Team",
    "primary_city"=>"South Jordan",
    "primary_email"=>"bao1@at1ts.com",
    "primary_phone"=>"(801) 948-4177",
    "primary_postal_code"=>"84095",
    "primary_state"=>"Utah",
    "primary_street_address1"=>"2651 W South Jordan Parkway",
    "primary_street_address2"=>"Ste 101B",
    "primary_website"=>"",
    "aff_type"=>"Escrow Officer",
    "user_name"=>"",
    "password"=>"",
    "create_by"=>"275",
    "submit_by"=>"275",
    "gps"=>"{}",
    "jwt"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpc3MiOiJodHRwOlwvXC93YXJyYW50eXByb2plY3QuY29tIiwiYXVkIjoiaHR0cDpcL1wvd2FycmFudHlwcm9qZWN0LmNvbSIsImlhdCI6MTU1MzI0MjMyMSwibmJmIjoxNTUzMjQyMzMxLCJleHAiOjE1NTMyNDU5MzEsImRhdGEiOnsiaWQiOiIyNzUiLCJmaXJzdG5hbWUiOiJjcm0iLCJsYXN0bmFtZSI6ImNvbm5lY3R0byIsImxpc3RfYWNsIjp7IkNsYWltRm9ybSI6IlVzZXIiLCJPcmRlckZvcm0iOiJBZG1pbiIsIkNvbnRhY3RGb3JtIjoiQWRtaW4iLCJJbnZvaWNlRm9ybSI6IkFkbWluIiwiUHJvZHVjdEZvcm0iOiJBZG1pbiIsIldhcnJhbnR5Rm9ybSI6IkFkbWluIn19fQ.CDtrFbVzfbCuBs4wT2BN97b7tRGHbKcjcVC7W1Pe-nx5m3GH58M4XglPX8P0LXkUN7jOhBudMHGmM5WHZ56ZqA",
    "private_key"=>"275");

$url_crm = $url_origin.'_contactAddNew.php';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url_crm);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($buyer_data));

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$buyer_add_rsl = curl_exec($curl);

curl_close($curl);
$byer_rsl = json_decode($buyer_add_rsl,true);
print_r($byer_rsl);
die();
$clientEmail='anh@at1ts.com';
$url_origin = 'http://api.warrantyproject.com/';
$buyer_email = array( 'token'=>'MjE0YTIwMzYxOTllNDdlZGU0OGI3ZTQ2OGM3OTZkYjUtdXMxOQ==',
    'primary_email'=>$clientEmail);
$url_crm = $url_origin.'_isEmailExisting.php';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url_crm);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($buyer_email));

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$buyer_e_rsl = curl_exec($curl);
curl_close($curl);

$buyer_id = json_decode($buyer_e_rsl,true);
print_r($buyer_e_rsl);
die();

//-------------------
/*$d = date("Y-m-d H:i:s");
$Y = date("Y");
$strYMD =strtotime($d);
$serial = $Y.$strYMD;
print_r($serial);
die();*/

$data = array(
    'token'=>'MjE0YTIwMzYxOTllNDdlZGU0OGI3ZTQ2OGM3OTZkYjUtdXMxOQ==',
    'primary_email'=>'anh@at1ts.com',
    'primary_postal_code'=>'84095',
    'login_type'=>2,
    'type'=>'Sales'
);

$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, 'http://api.warrantyproject.com/_login.php');
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);

curl_close($curl);

$rsl = json_decode($response);

//check email
$contact_email = array( 'token'=>'MjE0YTIwMzYxOTllNDdlZGU0OGI3ZTQ2OGM3OTZkYjUtdXMxOQ==',
    'primary_email'=>'anh@at1ts.com');

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://api.warrantyproject.com/_isEmailExisting.php');
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($contact_email));

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$contact_e_rsl = curl_exec($curl);
curl_close($curl);
$rsl_id = json_decode($contact_e_rsl,true);
//data
$token='MjE0YTIwMzYxOTllNDdlZGU0OGI3ZTQ2OGM3OTZkYjUtdXMxOQ==';
$jwt=$rsl->contact->jwt;
$private_key = $rsl->contact->ID;
$create_by=192;
$submit_by=197;
$gps='{}';
$notes =array();
$contact_doc =array();
$submitFirstName="Delivery";
$submitLastName='Team';
$submitCity='South Jordan';
$submitEmail='anh@at1ts.com';
$submitPhone1='(801) 948-4177';
$submitPostalCode='84095';
$submitState='UT';
$submitStreetAddress1='2651 W South Jordan Parkway';
$submitStreetAddress2='Ste 101B';
//

$orderTotal='450';
$cont_id="197";
$salesAffiliateId='130';

$private_key='197';
//----------------------------
array("token"=>"MjE0YTIwMzYxOTllNDdlZGU0OGI3ZTQ2OGM3OTZkYjUtdXMxOQ==",
    "warranty_address1"=>"122",
    "warranty_address2"=>"Ste 101B",
    "warranty_buyer_id"=>"197",
    "warranty_salesman_id"=>"130",
    "warranty_city"=>"AL",
    "warranty_creation_date"=>"2019-03-21",
    "warranty_email"=>"anh@at1ts.com",
    "warranty_end_date"=>"2019-03-29",
    "warranty_buyer_agent_id"=>0,
    "warranty_escrow_id"=>0,
    "warranty_notes"=>"Charity=>Cancer Research",
    "warranty_order_id"=>209,
    "warranty_phone"=>"(801) 948-4187",
    "warranty_postal_code"=>"5001",
    "warranty_serial_number"=>"20191553159008",
    "warranty_start_date"=>"2019-03-21",
    "warranty_state"=>"Alabama",
    "warranty_closing_date"=>"2019-03-29",
    "warranty_contract_amount"=>false,
    "warranty_charity_of_choice"=>"Cancer Research",
    "jwt"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpc3MiOiJodHRwOlwvXC93YXJyYW50eXByb2plY3QuY29tIiwiYXVkIjoiaHR0cDpcL1wvd2FycmFudHlwcm9qZWN0LmNvbSIsImlhdCI6MTU1MzE1OTAwNCwibmJmIjoxNTUzMTU5MDE0LCJleHAiOjE1NTMxNjI2MTQsImRhdGEiOnsiaWQiOiIxOTciLCJmaXJzdG5hbWUiOiJEZWxpdmVyeSIsImxhc3RuYW1lIjoiVGVhbSIsImxpc3RfYWNsIjp7IkNsYWltRm9ybSI6IlVzZXIiLCJPcmRlckZvcm0iOiJBZG1pbiIsIkNvbnRhY3RGb3JtIjoiQWRtaW4iLCJJbnZvaWNlRm9ybSI6IkFkbWluIiwiUHJvZHVjdEZvcm0iOiJBZG1pbiIsIldhcnJhbnR5Rm9ybSI6IkFkbWluIn19fQ._4AwuEASnuIw0HLo7G7vkKYhABZFTkkv8jnvENkoBkzn9fz5aFTHoX-aA4pAwNZ4y6zpC_Q3Uqi987c4mc94xA",
    "private_key"=>"197",
    "pro_ids"=>"316,318");

/*
$item_product =array(array("quantity"=>"1",
    "id"=>"318",
    "price"=>"Marketing",
    "sku"=>"AW011",
    "prod_name"=>"Well Pump",
    "discount"=>0,
    "discount_type"=>"",
    "line_total"=>"90.00"),
    array("quantity"=>"1",
        "id"=>"316",
        "price"=>"Marketing",
        "sku"=>"AS013",
        "prod_name"=>"Pre-Paid Service Call Fee",
        "discount"=>0,
        "discount_type"=>"",
        "line_total"=>"65.00")
); */
//test data
$pro_ids='';
$item_product=array();

$prod_num_temp ='';
$prod_item_temp=array();
$addons =array(
    array('id_ref'=>96,
        'price'=>100,
        'quantity'=>1,
        'discount'=>0,
        'discount_type'=>'',
        'line_total'=>100),
    array('id_ref'=>94,
        'price'=>120,
        'quantity'=>1,
        'discount'=>0,
        'discount_type'=>'',
        'line_total'=>120)
);

foreach($addons as $item){
    $prod_item_temp[]=array('quantity'=> $item['quantity'],
        'id_ref'=>$item['id_ref'],
        'price'=>$item['price'],
        'discount'=>0,
        'discount_type'=>'',
        'line_total'=>$item['line_total']
    );
    $prod_num_temp .=(empty($prod_num_temp))?'':',';
    $prod_num_temp .=$item['id_ref'];
    //get product info from CRM by
}

if($prod_num_temp!=''){
    $prod_data =array('token'=>$token,'IDs'=>$prod_num_temp);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'http://api.warrantyproject.com/_product_ByIdWordpress.php');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($prod_data));

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $prod_rsl = curl_exec($curl);
    curl_close($curl);
    $rsl_prod = json_decode($prod_rsl,true);

    if(count($rsl_prod)>0){
        //This $item_product used for CRM
        foreach($rsl_prod as $it){
            foreach($prod_item_temp as $it_temp){
                if($it['id_ref']==$it_temp['id_ref']){
                    $item_product[]=array(
                        'quantity'=> $it_temp['quantity'],
                        'price'=>$it_temp['price'],
                        'discount'=>$it_temp['discount'],
                        'discount_type'=>$it_temp['discount_type'],
                        'line_total'=>$it_temp['line_total'],
                        'id'=>$it['ID'],
                        'sku'=>$it['SKU'],
                        'prod_name'=>$it['prod_name'],
                        'prod_class'=>$it['prod_class']
                    );

                    if(strtolower($it['prod_class'])=='warranty'){
                        $pro_ids.=empty($pro_ids)?'':',';
                        $pro_ids .= $it['ID'];
                    }

                    break;
                }
            }
        }

    }

}

print_r($item_product);
die();
/*
//Add order
$salesAffiliateId='130';// hardcode to test
$data_order = array('token'=>$token,
    'balance'=>$orderTotal,
    'bill_to'=>$cont_id,
    'note'=>'',
    'payment'=>0,
    'salesperson'=>$salesAffiliateId,
    'products_ordered'=>$item_product,
    'total'=>$orderTotal,
    'jwt'=>$jwt,
    'private_key'=>$private_key
);

$rsl_order='';

if(count($item_product)>0){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url_origin.'_orderAddNew.php');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data_order));

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $prod_rsl = curl_exec($curl);
    curl_close($curl);
    $rsl_order = json_decode($prod_rsl,true);
    if($rsl_order['AUTH']){
        print_r($rsl_order['ID']);
    }else{
        print_r($rsl_order);
    }

}

die();*/

//-----------------------------
$prod_data =array('token'=>$token,'ID'=>'53');

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://api.warrantyproject.com/_product_ByIdWordpress.php');
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($prod_data));

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$prod_rsl = curl_exec($curl);
curl_close($curl);
$rsl_prod = json_decode($prod_rsl,true);

print_r($rsl_prod['ID'].";".$rsl_prod['SKU'].";".$rsl_prod['prod_name']);
//---------------



die();
//add and update
if($rsl_id==""){
    //add
    $contact_data = array('token'=>$token,
        'contact_inactive'=>0,
        'contact_notes'=>'',
        'contact_tags'=>'',
        'contact_type'=>'Affiliate',
        'first_name'=>$submitFirstName,
        'last_name'=>$submitLastName,
        'primary_city'=>$submitCity,
        'primary_email'=>$submitEmail,
        'primary_phone'=>$submitPhone1,
        'primary_postal_code'=>$submitPostalCode,
        'primary_state'=>$submitState,
        'primary_street_address1'=>$submitStreetAddress1,
        'primary_street_address2'=>$submitStreetAddress2,
        'primary_website'=>'',
        'aff_type'=>'Other',
        'user_name'=>'',
        'password'=>'',
        'create_by'=>$create_by,
        'submit_by'=>$submit_by ,
        'gps'=>$gps,
        'jwt'=>$jwt,
        'private_key'=>$private_key);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'http://api.warrantyproject.com/_contactAddNew.php');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($contact_data));

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $contact_add_rsl = curl_exec($curl);
    curl_close($curl);
    $rsl_cont = json_decode($contact_add_rsl,true);
    print_r($rsl_cont['ERROR']);

}elseif(is_numeric($rsl_id)){
    $contact_data = array('token'=>$token,
        'ID'=>$rsl_id,
        'contact_type'=>'Affiliate',
        'first_name'=>$submitFirstName,
        'last_name'=>$submitLastName,
        'primary_city'=>$submitCity,
        'primary_email'=>$submitEmail,
        'primary_phone'=>$submitPhone1,
        'primary_postal_code'=>$submitPostalCode,
        'primary_state'=>$submitState,
        'primary_street_address1'=>$submitStreetAddress1,
        'primary_street_address2'=>$submitStreetAddress2,
        'primary_website'=>'',
        'create_by'=>$create_by,
        'submit_by'=>$submit_by ,
        'jwt'=>$jwt,
        'private_key'=>$private_key);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'http://api.warrantyproject.com/_contactEdit.php');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($contact_data));

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $contact_add_rsl = curl_exec($curl);
    curl_close($curl);
    $rsl_cont = json_decode($contact_add_rsl,true);

    print_r($rsl_cont);
    print_r($rsl_cont['ERROR']);
    print_r("update");
}



die();

//---------------------------------
$a = Array ( 0 => Array ( 'name' => 'CPU', 'claim' => 300, 'person' => 172, 'current' => 200,'datetime' => '2019-03-14 17:17:23', 'original' => 200, 'available' => 200,'transaction' => 0 ),
    1 => Array ( 'name' => 'HVAC', 'claim' => 400, 'person' => 172, 'current' => 2000,'datetime' => '2019-03-14 17:17:23', 'original' => 2000, 'available' => 2000,'transaction' => 0 ),
    2 => Array ( 'name' => 'Graphic Card', 'claim' => 0, 'person' => 172, 'current' => 50,'datetime' => '2019-03-14 17:17:23', 'original' => 50, 'available' => 50,'transaction' => 0 ),
    3 => Array ( 'name' => 'Battery Lilon', 'claim' => 0, 'person' => 172, 'current' => 50,'datetime' => '2019-03-14 17:17:23', 'original' => 50, 'available' => 50,'transaction' => 0 ),
    4 => Array ( 'name' => 'Screen Saving', 'claim' => 0, 'person' => 172, 'current' => 50,'datetime' => '2019-03-14 17:17:23', 'original' => 20, 'available' => 20,'transaction' => 0 ),
    5 => Array ( 'name' => 'roof', 'claim' => 0, 'person' => 172, 'current' => 2000,'datetime' => '2019-03-14 17:17:23', 'original' => 2000, 'available' => 2000,'transaction' => 0 ),
    6 => Array ( 'name' => 'license', 'claim' => 0, 'person' => 172, 'current' => 200,'datetime' => '2019-03-14 17:17:23', 'original' => 200, 'available' => 200,'transaction' => 0 ),
    7 => Array ( 'name' => 'building', 'claim' => 0, 'person' => 172, 'current' => 150,'datetime' => '2019-03-14 17:17:23', 'original' => 150, 'available' => 150,'transaction' => 0 ),
    8 => Array ( 'name' => 'CPU', 'claim' => 0, 'person' => 198, 'current' => 150,'datetime' => '2019-03-14 17:17:23', 'original' => 200, 'available' => -100,'transaction' => 300 ),
    9 => Array ( 'name' => 'HVAC', 'claim' => 0, 'person' => 198, 'current' => 2000,'datetime' => '2019-03-14 17:17:23', 'original' => 2000, 'available' => 1600,'transaction' => 400 ),
    10 => Array ( 'name' => 'Graphic Card', 'claim' => 0, 'person' => 198, 'current' => 50,'datetime' => '2019-03-14 17:17:23', 'original' => 50, 'available' => 50,'transaction' => 0 ),
    11 => Array ( 'name' => 'Battery Lilon', 'claim' => 0, 'person' => 198, 'current' => 50,'datetime' => '2019-03-14 17:17:23', 'original' => 50, 'available' => 50,'transaction' => 0 ),
    12 => Array ( 'name' => 'Screen Saving', 'claim' => 0, 'person' => 198, 'current' => 20,'datetime' => '2019-03-14 17:17:23', 'original' => 20, 'available' => 20,'transaction' => 0 ),
    13 => Array ( 'name' => 'roof', 'claim' => 0, 'person' => 198, 'current' => 2000,'datetime' => '2019-03-14 17:17:23', 'original' => 2000, 'available' => 2000,'transaction' => 0 ),
    14 => Array ( 'name' => 'license', 'claim' => 0, 'person' => 198, 'current' => 200,'datetime' => '2019-03-14 17:17:23', 'original' => 200, 'available' => 200,'transaction' => 0 ),
    15 => Array ( 'name' => 'building', 'claim' => 0, 'person' => 198, 'current' => 150,'datetime' => '2019-03-14 17:17:23', 'original' => 150, 'available' => 150,'transaction' => 0 )
 );

if(count($a)>0){
    for($i=0;$i<count($a);$i++){
        $j=$i+1;
        for($j;$j<count($a);$j++){
            if(count($a[$i])>0 && count($a[$j])>0){
                if(isset($a[$i]['name'])){
                    if($a[$i]['name'] ==$a[$j]['name']){
                        $a[$i]['claim']=$a[$i]['claim'] + $a[$j]['claim'];
                        unset($a[$j]['name']);
                        unset($a[$j]['claim']);
                        unset($a[$j]['current']);
                        unset($a[$j]['person']);
                        unset($a[$j]['datetime']);
                        unset($a[$j]['original']);
                        unset($a[$j]['available']);
                        unset($a[$j]['transaction']);
                        unset($a[$j]['transaction2']);
                        unset($a[$j]['transaction3']);
                    }
                }
            }
        }
    }
}

print_r($a); die();
///
$a =Array
(
    0 => Array("AWS" => 100, "ABC" => 200 ,"HVAC" => 2000, 'ID'=>1 ),

    1 => Array('Test' => 2000, 'ABC' => 200, 'building'=> 150,'ID'=>2 ),

    2 => Array('ABC' => 200, 'Test' => 1000, 'Nothing' => 2000, 'Testing'=> 100,'ID'=>2 )
);


for($i=0;$i<count($a);$i++){
    $j=$i+1;
    for($j;$j<count($a);$j++){
        //find ID is equal
        if(count($a[$i])>0 && count($a[$j])>0){
            if($a[$i]['ID'] ==$a[$j]['ID']){
                //sum key is the same
                foreach($a[$i] as $k=>$v){
                    foreach($a[$j] as $kj=>$vj){
                        if($k ==$kj && $k!="ID"){
                            $a[$i][$k] = $v +$vj;
                            unset($a[$j][$kj]);
                        }
                    }
                }
            }
        }
    }

}
die();
//--------------------------------------

$body = "This is the fixed message of test email to get notify when it is read.....";
$body .= "<img src='http://api.warrantyproject.com/trackonline.php?email=".$email."&id=".$id."' border='0' width='1' height='1' alt=''>";
$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = 'smtp.mandrillapp.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';////Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPAuth = true;
$mail->Username = "marketing@freedomhw.com";
$mail->Password = "aL9zuKiIRK44voh1Jx0hsA";
$mail->setFrom("marketing@freedomhw.com", 'Marketing Freedom');
$mail->addAddress($email, 'Admin');

$mail->Subject = 'Claim was submited by: '.$email;

$tempDate = date("Y-m-d H:i:s");
$mail->MsgHTML($body);
$mail->IsHTML(true); // send as HTML

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Invalid email address formatting!';
    die();
}

$domain = substr($email, strpos($email, '@') + 1);
if  (!checkdnsrr($domain) !== FALSE) {
    echo 'Domain is Invalid!';

}


if (!$mail->send()) {
    print_r($mail->ErrorInfo) ;
    print_r("12");
} else {

    print_r("1");
}


die();



$apikey = base64_decode($_POST['token']);

$tempDate = date("Y-m-d H:i:s");
$ymd_temp = explode(" ",$tempDate);
$tepm = explode(":",$ymd_temp[1]);

print_r($ymd_temp[0]."-".$tepm[0]."-".$tepm[1]."-".$tepm[2]);
die();

$Object = new Orders();
    $EXPECTED = array('token','str');
$day="2019-02-14";
$h ="1:36:24";
$d_h= $day." ".$h;

$date = date($d_h);

$now = date("Y-m-d H:i:s");

print_r($date); echo "now:"; print_r($now);
echo ";;;";
$tr1 =strtotime($date);
$tr1 =strtotime($now);

//$hourdiff = round((strtotime($tr1) - strtotime($tr1))/3600, 1);


$d1=new DateTime($date);
$d2=new DateTime($now);
$interval=$d2->diff($d1);
$hours    = ($interval->days * 24) + $interval->h
    + ($interval->i / 60);

$hours = round($hours);
print_r( $hours ) ;
die();
$list =$Object->order_open_total(30);


print_r($list); die();


$l =array(array("email"=>"anh@at1ts.com"),array("email"=>"anh@at1ts.com"),array("email"=>"anh@at1ts.com"));
foreach($l as $itm){
    $Object->sendEmailToAssiged($itm["email"],"test","test","test");
    sleep(1);
}

//
$tempDate = date("Y-m-d H:i:s");
print_r($tempDate); die();
//echo shell_exec("whoami");
//$output = shell_exec('sudo crontab -l');
//$execQuery = "echo -n test_command";
$email = "linh@at1ts.com";
$append ='*/15 * * * * curl -s http://api.warrantyproject.com/email_to.php?email='.$email;

$temp =file_get_contents("./cronphp.txt");
//$temp.="\r\n".$append;
$temp1 = explode('\n',$temp);
print_r($temp1); die();
$upload = file_put_contents($_SERVER["DOCUMENT_ROOT"]."/claim/cronphp.txt", $temp);

//$output = shell_exec("/usr/bin/php /mnt/web/email_to.php");
//$email ="anh@at1ts.com";
//$output = shell_exec("curl -s http://api.warrantyproject.com/email_to.php?email=".$email);
//var_dump($output);
//echo $output;
//$name ="test1.php";
//$photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$name; //'.'.$extension;



//$upload = file_put_contents($photoPathTemp, $data);




