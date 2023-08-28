
<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
//header('Access-Control-Allow-Headers: Authorization, X-Requested-With');
//header('P3P: CP="NON DSP LAW CUR ADM DEV TAI PSA PSD HIS OUR DEL IND UNI PUR COM NAV INT DEM CNT STA POL HEA PRE LOC IVD SAM IVA OTC"');
//header('Access-Control-Max-Age: 1');

include_once './lib/class.mail.php';
$Object = new LocalEmail();

$EXPECTED = array('token','pageno','pagelength','contactID','jwt','private_key');


foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key]))
        ${$key} = $Object->protect($_POST[$key]);
    else
        ${$key} = NULL;
}

$isAuth =$Object->basicAuth($token);

$list = array();
if(!$isAuth){
    $ret = array("list"=>[],"total"=>0,'AUTH'=>false,'ERROR'=>'Authenticate failed');
    $Object->close_conn();
    echo json_encode($ret);
    return;
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    $isAuth['AUTH']=true;
    if($isAuth['AUTH']){
        $limit = empty($pagelength) ? 0 : $pagelength;
        $offset = empty($pageno) ? 0 : ($pageno-1)*$pagelength;

         $list = $Object->mail_draft($contactID,$limit,$offset);
         $total = $Object->mail_draft_total($contactID);

        $ret = array("list"=>$list,"total"=>$total,'AUTH'=>true, 'ERROR'=>'');
    }else{
        $ret = array("list"=>[],"total"=>0,'AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
    }

    $Object->close_conn();
    echo json_encode($ret);
}


