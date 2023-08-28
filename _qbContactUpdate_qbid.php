<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.contact.php';
include_once '_qbviacurl.php';
    $Object = new Contact();

    $EXPECTED = array('token','quickbook_id','contact_type',
        'first_name','last_name','middle_name','primary_city','primary_email',
        'primary_phone','primary_postal_code',
        'primary_state', 'primary_street_address1','primary_street_address2','primary_website',
        'jwt','private_key'
        );

    $code=200;
    //check fields don't permit update

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

     $ID =$Object->getContactIDByQBIDandType($quickbook_id,$contact_type);
    //--- validate
    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('SAVE'=>false,'ERROR'=>'Authentication is failed');
    }else if(!empty($ID)){
        $errObj['errorMsg']="Authentication is failed";
        $isAuth = $Object->auth($jwt,$private_key);
        $isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            //$errObj = $Object->validate_contact_fields($first_name,$last_name,$primary_email,$primary_street_address1);
            $errObj = $Object->validateContactFieldEmailOrPhone($primary_email,$primary_phone);
            if(!$errObj['error']){
                if($contact_inactive=="" || empty($contact_inactive)) {
                    $contact_inactive =0;
                }

                $contact_doc =array();
                if(isset($_POST['contact_doc'])){
                    $contact_doc=$_POST['contact_doc'];
                }

                $err_upload_file =array();
                //print_r($contact_doc); echo ";;;";
                if(count($contact_doc) >0){
                    for($i=0;$i<count($contact_doc);$i++){
                        $item = $contact_doc[$i];
                        $image_name ="";
                        $imageData = "";
                        if(isset($item["image_name"])) $image_name=$item["image_name"];
                        if(isset($item["image"])) $imageData = $item["image"];
                        if(!empty($image_name) && ($imageData!=$image_name) && !empty($imageData)){

                            $ext = explode('.', $image_name);
                            if(count($ext)>0){
                                $index = count($ext)-1;
                                if($ext[$index]=="pdf" || $ext[$index]=="PDF"){
                                    $repl = 'data:application/pdf;base64,';
                                }elseif($ext[$index]=="jpg" || $ext[$index]=="JPG"){
                                    $repl = 'data:image/jpeg;base64,';
                                }elseif($ext[$index]=="png" || $ext[$index]=="PNG"){
                                    $repl = 'data:image/png;base64,';
                                }

                                $data = str_replace(
                                    $repl,
                                    '',
                                    $imageData
                                );
                                //verify file is true
                                if(base64_encode(base64_decode($data, true)) === $data){
                                    list($type, $imageData) = explode(';', $imageData);
                                    list(,$extension) = explode('/',$type);
                                    list(,$imageData)      = explode(',', $imageData);

                                    $flag = in_array($extension,[ 'jpg', 'jpeg', 'gif', 'png', 'pdf','JPG', 'JPEG','PNG']);

                                    if($flag){
                                        $name ="/photo/docs/contacts/".$first_name."_".$last_name."_".$item["image_name"];
                                        $photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$name; //'.'.$extension;
                                        $imageData = base64_decode($imageData);
                                        $upload = file_put_contents($photoPathTemp, $imageData);

                                        if(is_numeric($upload)){
                                            $photoPath = $name;
                                            $contact_doc[$i]["image"]=$name;
                                            unset($contact_doc[$i]["image_name"]);
                                        }else{
                                            $err_temp=array();
                                            $err_temp["filename"]=$item["image_name"];
                                            $err_upload_file[]=$err_temp;

                                            if(isset($contact_doc[$i]["ID"])){
                                                if(!empty($contact_doc[$i]["ID"])){
                                                    $contact_doc[$i]["image"]="";
                                                    unset($contact_doc[$i]["image_name"]);
                                                }
                                            }else{
                                                unset($contact_doc[$i]);
                                            }
                                        }
                                    }
                                    //
                                }else{
                                    $err_temp=array();
                                    $err_temp["filename"]=$item["image_name"];
                                    $err_upload_file[]=$err_temp;
                                    if(isset($contact_doc[$i]["ID"])){
                                        if(!empty($contact_doc[$i]["ID"])){
                                            $contact_doc[$i]["image"]="";
                                            unset($contact_doc[$i]["image_name"]);
                                        }
                                    }else{
                                        unset($contact_doc[$i]);
                                    }
                                }
                            }

                        }else{
                            if(empty($image_name)){
                                if(isset($contact_doc[$i]["image"])){
                                    $contact_doc[$i]["image"]=$Object->protect($contact_doc[$i]["image"]);
                                }else{
                                    $contact_doc[$i]["image"]="";
                                }
                            }
                            unset($contact_doc[$i]["image_name"]);
                        }
                    }
                }

               // die();
                if(empty($company_name) || !is_numeric($company_name)) $company_name=0;
                if(empty($save_anyway)) $save_anyway =0;

                $result = $Object->qbUpdateContactByQBID($ID,$contact_type,
                    $first_name,$last_name,$middle_name,$primary_city,$primary_email,
                    $primary_phone,$primary_postal_code,
                    $primary_state, $primary_street_address1,$primary_street_address2,$primary_website,
                    $save_anyway);

                //die($result);
                if(is_numeric($result["updated"])) {
                    $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','contact_duplicated'=>array());

                }else{
                    //log errors
                    $info ="Contact -- Address1:".$primary_street_address1. ", Email: ".$primary_email.
                        ", phone: ".$primary_phone.", postal code ".$primary_postal_code.", err: ".$result["updated"];

                    $Object->err_log("Contact",$info,$ID);
                    if($result["updated"]=='The phone and email are used'){
                        $code=403;
                    }else{
                        $result['contact_duplicated']=array();
                    }
                    $ret = array('SAVE'=>'FAIL','ERROR'=>$result["updated"],"err_upload_file"=>$err_upload_file,
                    'contact_duplicated'=>$result['contact_duplicated']);
                }

            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
                //log errors
                $info ="Contact -- Address1:".$primary_street_address1. ", Email: ".$primary_email.
                    ", phone: ".$primary_phone.", postal code ".$primary_postal_code.", err: ".$errObj['errorMsg'];
            }
        }else{
            $ret = array('SAVE'=>false,'AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }else{
    $ret = array('SAVE'=>'FAIL','ERROR'=>'Contact is not already');

  }

    $Object->close_conn();
    echo json_encode($ret);
    http_response_code($code);





