<?php
    $origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
    header('Access-Control-Allow-Credentials: true');
    include_once './lib/class.importContact.php';
   $Object = new ImportContact();

$EXPECTED = array('token','jwt','private_key');

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
    if($isAuth['AUTH']){
        $err_add=array();
        $err_up=array();
        $col =array();
        $row = 0;

        //Add tags
        $contact_tags='Bulk Import, '.date('M')." ".date('Y');
        $Object->addTag_importContact("Contact",$contact_tags);

        $data = $_POST['contactFile'];
        $jj = count($data);
        foreach($data as $item) {
            $first_name='';
            $middle_name='';
            $last_name='';
            $primary_street_address1='';
            $primary_street_address2='';
            $primary_city='';
            $primary_state='';
            $primary_postal_code='';
            $primary_phone='';
            $primary_phone_ext='';
            $primary_phone_type='';
            $primary_email='';
            $primary_website='';
            $contact_type='Lead';
            $contact_inactive=0;
            $contact_notes='';
            $contact_tags='Bulk Import, '.date('M')." ".date('Y');
            $create_by=0;
            $submit_by=0;
            $gps='{}';
            $create_date= date("Y-m-d");
            $company_name=0;
            $archive_id=0;
            $dateofbirth='';

            $phone_org='';
            $email_org='';
            $aff_type='';
            //

            foreach($item as $k=>$v){
                $v =$Object->protect($v);

                switch($k){
                    case 'first_name':
                        $first_name = $v;
                        break;
                    case 'middle_name':
                        $middle_name = $v;
                        break;
                    case 'last_name':
                        $last_name = $v;
                        break;
                    case 'primary_street_address1':
                        $primary_street_address1 = $v;
                        break;
                    case 'primary_street_address2':
                        $primary_street_address2 = $v;
                        break;
                    case 'primary_city':
                            $primary_city =$v;
                        break;
                    case 'primary_state':
                        $primary_state =$v;
                        break;
                    case 'primary_postal_code':
                        $primary_postal_code=$v;
                        break;
                    case 'primary_phone':
                        $primary_phone = $Object->format_phone_new($v);
                            //$Object->format_phone($v);
                        $phone_org =$v;
                        break;
                    case 'primary_phone_ext':
                        $primary_phone_ext = $v;
                        break;
                    case 'primary_phone_type':
                        $primary_phone_type = $v;
                        break;
                    case 'primary_email':
                        if (filter_var($v, FILTER_VALIDATE_EMAIL)) {
                            $primary_email = $v;
                        }else{
                            $primary_email='';
                        }
                        $email_org =$v;
                        break;
                    case 'primary_website':
                        $primary_website = $v;
                        break;
                    case 'contact_type':
                        $contact_type = $v;
                        break;
                    case 'contact_inactive':
                        $contact_inactive = $v;
                        break;
                    case 'contact_notes':
                        $contact_notes = $v;
                        break;
                    case 'contact_tags':
                        $contact_tags=$v;
                        break;
                    case 'create_by':
                        $create_by = $v;
                        break;
                    case 'submit_by':
                        $submit_by = $v;
                        break;
                    case 'gps':
                        $gps = $v;
                        break;
                    case 'create_date':
                        $create_date = $v;
                        break;
                    case 'company_name':
                        $company_name = $v;
                        break;
                    case 'archive_id':
                        $archive_id = $v;
                        break;
                    case 'dateofbirth':
                        if(!empty($v)){
                            $old_date = explode('/', $v);
                            $new_data = $old_date[2].'-'.$old_date[1].'-'.$old_date[0];
                            $d = DateTime::createFromFormat('Y-m-d', $new_data);
                            $dateofbirth = $d->format('Y-m-d');
                            $dateofbirth = date($dateofbirth);
                        }else{
                            $dateofbirth ='';
                        }

                        break;

                }
            }

            //die();
            $errObj = $Object->validateContactFieldEmail_Phone($primary_email,$primary_phone);
            if(!$errObj['error']){
                //check is Contcact exsiting
                $ID=$Object->contact_existing($primary_email,$primary_phone);

                if(is_numeric($ID) && $ID!=0){
                    $isID = $Object->impUpdateContact($ID,$first_name,$middle_name,$last_name,$primary_street_address1,$primary_street_address2,
                        $primary_city,$primary_state,$primary_postal_code,$primary_phone,$primary_phone_ext,
                        $primary_phone_type,$primary_email,$primary_website,$contact_type,
                        $contact_inactive, $contact_notes,$contact_tags,$create_by,$submit_by,
                        $gps,$create_date,$company_name,$archive_id,$dateofbirth);

                    if(!is_numeric($isID)){
                        $err_add[]=array("err"=>$isID,"contact_name"=>$first_name." ".$last_name,"primary_email"=>$email_org,
                            "primary_phone"=>$phone_org);
                    }else{
                        if($contact_type!="lead" && !empty($contact_type)){
                            //Add tags
                            $Object->addTag_importContact("Contact",$contact_tags);
                        }
                    }

                }elseif(empty($ID)){
                    $isID=$Object->import_Contact($first_name,$middle_name,$last_name,$primary_street_address1,$primary_street_address2,
                        $primary_city,$primary_state,$primary_postal_code,$primary_phone,$primary_phone_ext,
                        $primary_phone_type,$primary_email,$primary_website,$contact_type,
                        $contact_inactive, $contact_notes,$contact_tags,$create_by,$submit_by,
                        $gps,$create_date,$company_name,$archive_id,$dateofbirth);

                    if(!is_numeric($isID)){
                        $err_add[]=array("err"=>$isID,"contact_name"=>$first_name." ".$last_name,"primary_email"=>$email_org,
                            "primary_phone"=>$phone_org);
                    }else{
                        if($contact_type!="lead" && !empty($contact_type)){
                            //Add tags
                            $Object->addTag_importContact("Contact",$contact_tags);
                        }
                    }
                }else{
                    $err_add[]=array("err"=>$ID,"contact_name"=>$first_name." ".$last_name,"primary_email"=>$email_org,
                        "primary_phone"=>$phone_org);
                }
                //
            }else{
                $err_add[]=array("err"=>$errObj['error'],"contact_name"=>$first_name." ".$last_name,"primary_email"=>$primary_email,
                    "primary_phone"=>$primary_phone);
            }

        }

        $ret = array('ERROR_ADD'=>$err_add,'ERROR_UP'=>$err_up,'count'=>$jj);
    }else{
        $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
    }
}

    //die();
$Object->close_conn();
echo json_encode($ret);



