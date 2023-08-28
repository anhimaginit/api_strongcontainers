<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

           
include_once './lib/class.company.php';
    $Object = new Company();

    $EXPECTED = array('token','address1','address2','city','type','vendor_type',
        'email','fax','name','phone','state','vendor_note',
        'www','vendor','tag','postal_code','contactID',
        'jwt','private_key',
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
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    $isAuth = $Object->auth($jwt,$private_key);

    $isAuth['AUTH']=true;
    $errObj['errorMsg']="Authentication is failed";
    if($isAuth['AUTH']){
        
        $errObj = $Object->validate_comp_fields($address1,$city,$email,$name,$phone);

        if(!$errObj['error']){           

            /*$notes =array();
            if(isset($_POST['notes'])){
                $notes=$_POST['notes'];
            }*/

            $vendor_doc =array();
            if(isset($_POST['vendor_doc'])){
                $vendor_doc=$_POST['vendor_doc'];
            }

            //print_r($vendor_doc); die();

            $comp_note =array();
            if(isset($_POST['comp_note'])){
                $comp_note=$_POST['comp_note'];
            }

            //upload file
            $err_upload_file =array();
            if(count($vendor_doc) >0){
                for($i=0;$i<count($vendor_doc);$i++){
                    $item = $vendor_doc[$i];
                    $image_name ="";
                    $imageData = "";
                    if(isset($item["image_name"])) $image_name=$item["image_name"];
                    if(isset($item["image"])) $imageData = $item["image"];
                    if(!empty($image_name)){
                        $imageData = $item["image"];
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

                                $flag = in_array($extension,[ 'jpg', 'jpeg','png', 'pdf','JPG', 'JPEG','PNG' ]);

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
                                        unset($vendor_doc[$i]);
                                    }
                                }
                            }else{
                                unset($vendor_doc[$i]);
                            }

                        }
                    }else{
                        $vendor_doc[$i]["image"]="";
                        if(isset($vendor_doc[$i]["image_name"])) unset($vendor_doc[$i]["image_name"]);
                    }
                }
            }

            $result = $Object->addCompany($address1,$address2,$city,$email,
                $fax,$name,$phone,$state,$type,$www,$tag,$postal_code,$vendor,$vendor_type,
                $comp_note,$vendor_note,$vendor_doc,
                $license_exp,$w9_exp,$insurrance_exp,$gps,$company_salesman_id);

            if(is_numeric($result["id"]) && !empty($result["id"])){
                //Add tags
                $Object->addTag("Company",$tag);
                //deactivate contact
                $deactivate="";
                if(!empty($contactID)) $deactivate= $Object->deactivateContact_ID($contactID);

                $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result["id"],"vendor"=>$result["vendor"],"notes"=>$result["notes"],
                "err_upload_file"=>$err_upload_file,"","contactID"=>$contactID,"deactivate"=>$deactivate);
            } else {
                $ret = array('SAVE'=>'FAIL','ERROR'=>$result['com'],'ID'=>"","vendor"=>"","notes"=>"",
                    "err_upload_file"=>$err_upload_file);

            }

        } else {
            $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);

        }
    }else{
        $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
    }
}
    $Object->close_conn();
    echo json_encode($ret);




