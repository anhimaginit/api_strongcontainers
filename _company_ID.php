
<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');
//header('Access-Control-Allow-Headers: Authorization, X-Requested-With');
//header('P3P: CP="NON DSP LAW CUR ADM DEV TAI PSA PSD HIS OUR DEL IND UNI PUR COM NAV INT DEM CNT STA POL HEA PRE LOC IVD SAM IVA OTC"');
//header('Access-Control-Max-Age: 1');

include_once './lib/class.company.php';
$Object = new Company();

$EXPECTED = array('token','ID','jwt','private_key');


foreach ($EXPECTED AS $key){
    if (!empty($_POST[$key]))
        ${$key} = $Object->protect($_POST[$key]);
    else
        ${$key} = NULL;
}

$isAuth =$Object->basicAuth($token);

$list = array();
if(!$isAuth){
    $ret = array("comp"=>[],"vendor"=>[],"v_doc"=>[],'AUTH'=>false,'ERROR'=>'Authenticate failed');
    $Object->close_conn();
    echo json_encode($ret);
    return;
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    $isAuth['AUTH']=true;
    if($isAuth['AUTH']){
        $c_list = $Object->getCompany_ID($ID);
        $c_l = array();
        $v_l = array();
        if(count($c_list)>0){
            $c_list[0]["type"] = json_decode($c_list[0]["type"],true);
           // $c_list[0]["gps"] = json_decode($c_list[0]["gps"],true);

            $c_l = $c_list[0];


        }
        $notes = $Object->getNotesByTypeID($ID,"company");
        $v_list = $Object->getVendor_comID($ID);
        $v_doc = array();

        if(count($v_list) >0){
            $v_doc = $Object->getVendorDoc_vendorID($v_list[0]["ID"]);
            $v_list[0]["vendor_type"] = json_decode($v_list[0]["vendor_type"],true);
            $v_l = $v_list[0];
        }



        $ret = array('comp'=>$c_l,'vendor'=>$v_l,'v_doc'=>$v_doc,'notes'=>$notes,'AUTH'=>true, 'ERROR'=>'');
    }else{
        $ret = array('comp'=>[],'vendor'=>[],'v_doc'=>[],'AUTH'=>true, 'ERROR'=>'');
    }

    $Object->close_conn();
    echo json_encode($ret);
}


