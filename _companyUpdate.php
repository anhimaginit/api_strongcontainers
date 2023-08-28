<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.company.php';
    $Object = new Company();

    $EXPECTED = array('token','ID','address1','address2','city',
    'email','fax','name','phone','state','vendor_note',
    'type','www','vendor','vendor_type','postal_code','tag','jwt','private_key',
    'license_exp','w9_exp','insurrance_exp','gps','company_salesman_id');

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
        $ret = array('SAVE'=>false,'ERROR'=>'Authentication is failed');
    }else if(!empty($ID)){
        $errObj['errorMsg']="Authentication is failed";
        $isAuth = $Object->auth($jwt,$private_key);

        if($isAuth['AUTH']){
            $errObj = $Object->validate_comp_fields($address1,$city,$email,$name,$phone);

            if(!$errObj['error']){

                $comp_note =array();
                if(isset($_POST['comp_note'])){
                    $comp_note=$_POST['comp_note'];
                }

                $vendor_doc =array();
                if(isset($_POST['vendor_doc'])){
                    $vendor_doc=$_POST['vendor_doc'];
                }

                $err_upload_file =array();
                if(count($vendor_doc) >0){
                    for($i=0;$i<count($vendor_doc);$i++){
                        $item = $vendor_doc[$i];
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
                                    list($type_file, $imageData) = explode(';', $imageData);
                                    list(,$extension) = explode('/',$type_file);
                                    list(,$imageData)      = explode(',', $imageData);

                                    $flag = in_array($extension,[ 'jpg', 'jpeg', 'gif', 'png', 'pdf','JPG', 'JPEG','PNG']);

                                    if($flag){
                                        $pathname ="/photo/docs/vendors/".$name."_".$item["image_name"];
                                        $photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$pathname; //'.'.$extension;

                                        $imageData = base64_decode($imageData);
                                        $upload = file_put_contents($photoPathTemp, $imageData);

                                        if(is_numeric($upload)){
                                            $photoPath = $name;
                                            $vendor_doc[$i]["image"]=$pathname;
                                            unset($vendor_doc[$i]["image_name"]);
                                        }else{
                                            $err_temp=array();
                                            $err_temp["filename"]=$item["image_name"];
                                            $err_upload_file[]=$err_temp;

                                            if(isset($vendor_doc[$i]["ID"])){
                                                if(!empty($vendor_doc[$i]["ID"])){
                                                    $vendor_doc[$i]["image"]="";
                                                    unset($vendor_doc[$i]["image_name"]);
                                                }
                                            }else{
                                                unset($vendor_doc[$i]);
                                            }

                                        }
                                    }
                                    //
                                }else{
                                    $err_temp=array();
                                    $err_temp["filename"]=$item["image_name"];
                                    $err_upload_file[]=$err_temp;
                                    if(isset($vendor_doc[$i]["ID"])){
                                        if(!empty($vendor_doc[$i]["ID"])){
                                            $vendor_doc[$i]["image"]="";
                                            unset($vendor_doc[$i]["image_name"]);
                                        }
                                    }else{
                                        unset($vendor_doc[$i]);
                                    }
                                }

                            }

                        }else{
                            if(empty($image_name)){
                                if(isset($vendor_doc[$i]["image"])){
                                    $vendor_doc[$i]["image"]=$Object->protect($vendor_doc[$i]["image"]);
                                }else{
                                    $vendor_doc[$i]["image"]="";
                                }
                            }

                            unset($vendor_doc[$i]["image_name"]);
                        }
                    }
                }

                $result = $Object->updateCompany($ID,$address1,$address2,$city,$email,
                $fax,$name,$phone,$state,$type,$www,$tag,$postal_code,$vendor,$vendor_type,
                    $comp_note,$vendor_note,$vendor_doc,
                    $license_exp,$w9_exp,$insurrance_exp,$gps,$company_salesman_id);

                //die($result);
                if(is_numeric($result["edit"])) {
                    //Add tags
                    $Object->addTag("Company",$tag);
                    $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','vendor'=>$result['vendor'],'notes'=>$result['notes'],
                        "err_upload_file"=>$err_upload_file);
                    // print_r("test=");print_r($result); die();
                }else{
                    $ret = array('SAVE'=>'FAIL','ERROR'=>$result['com'],'ID'=>"","vendor"=>$result["vendor"],"notes"=>$result["notes"],
                        "err_upload_file"=>$err_upload_file);
                }

            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
            }
        }else{
            $ret = array('SAVE'=>false,'AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }

    }else{
        $ret = array('SAVE'=>'FAIL','ERROR'=>'Contact is not already');

  }

    $Object->close_conn();
    echo json_encode($ret);





