<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.contact.php';

    $Object = new Contact();
    $EXPECTED = array('token','contact_id','driver_id','driver_rate','avatar_image',
        'driver_min_rate','driver_description',
    'jwt','private_key');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }
    //die();
    //--- validate
$isAuth =$Object->basicAuth($token);
$code=200;
if(!$isAuth){
    $ret = array('ERROR'=>'Authentication is failed');
}else{
    $isAuth = $Object->auth($jwt,$private_key);
    $create_by = $private_key;
    $isAuth['AUTH']=true;
    $errObj['errorMsg']="Authentication is failed";
    if($isAuth['AUTH']){
        $contact_type = $Object->return_id("SELECT contact_type FROM `contact` WHERE ID = '{$contact_id}'","contact_type");

        $p= stripos($contact_type,"Driver");
        if(is_numeric($p)){
            //avatar
            $files_save_err ='No file update';
            $file_name ='';
            $path_file_return ='';
            if(isset($_POST['avatar_data'])){
                $files_save_err ='';
                $imageData = $_POST['avatar_data'];
                $file_name = $avatar_image;

                list($type, $data_img) = explode(';', $imageData);
                list(, $data_img) = explode(',', $data_img);
                $file_data = base64_decode($data_img);

                // Get file mime type
                $finfo = finfo_open();
                $file_mime_type = finfo_buffer($finfo, $file_data, FILEINFO_MIME_TYPE);

                // File extension from mime type
                if($file_mime_type == 'image/jpeg' || $file_mime_type == 'image/jpg')
                    $file_type = 'jpeg';
                else if($file_mime_type == 'image/png')
                    $file_type = 'png';
                else if($file_mime_type == 'image/gif')
                    $file_type = 'gif';
                else
                    $file_type = 'other';

                // Validate type of file
                if(in_array($file_type, [ 'jpeg', 'png',])) {
                    $avatar_file_path_temp = $Object->driver_avatar;
                    // Set a unique name to the file and save
                    $file_name = uniqid() . $file_name;
                    //$photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$avatar_file_path_temp.$file_name;
                    $api_domain = $Object->api_domain;
                    $path_file_return = $api_domain.$avatar_file_path_temp.$file_name;
                    $photoPathTemp = 'C:/xampp/htdocs/CRMAPI/'.$avatar_file_path_temp.$file_name;
                    $path_file_return ='http://localhost/CRMAPI/'.$avatar_file_path_temp.$file_name;
                    file_put_contents($photoPathTemp, $file_data);
                }
                else {
                    $files_save_err = 'Error : Only JPEG, PNG & GIF allowed';
                }
            }
            ///
            $ret = $Object->add_update_driver($contact_id,$driver_id,$driver_rate,$file_name,$driver_min_rate,
            $driver_description);
            if(is_numeric($ret) && !empty($ret)){
                $ret = array('SAVE'=>true,'ERROR'=>'','driver_id'=>$driver_id,
                    "files_save_err"=>$files_save_err,"path_file_return"=>$path_file_return);
            }
        }else{
            $ret = array('SAVE'=>false,'ERROR'=>"Not driver");
        }
    }else{
        $ret = array('SAVE'=>false,'ERROR'=>$errObj['errorMsg']);
    }
}
    $Object->close_conn();
    echo json_encode($ret);
    http_response_code($code);




