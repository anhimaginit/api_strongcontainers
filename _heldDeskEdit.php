<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.helpdesk.php';
$Object = new Helpdesk();

$EXPECTED = array('token','id','subject','problem','form',
    'status','assign_to','last_update');

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
    $errObj = $Object->validate_helpdesk_fields($subject,$problem);
    if(!$errObj['error']){
        //
        $screenshot =array();
        if(isset($_POST['screenshot'])){
            $screenshot=$_POST['screenshot'];
        }
        //upload file
        $err_upload_file =array();
        if(count($screenshot) >0){
            for($i=0;$i<count($screenshot);$i++){
                $item = $screenshot[$i];
                if($item['change'] !=1){
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
                                list($type, $imageData) = explode(';', $imageData);
                                list(,$extension) = explode('/',$type);
                                list(,$imageData)      = explode(',', $imageData);

                                $flag = in_array($extension,[ 'jpg', 'jpeg', 'gif', 'png', 'pdf','JPG', 'JPEG','PNG']);

                                if($flag){
                                    $name ="/photo/helpdesk/".uniqid().$item["image_name"];
                                    $photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$name; //'.'.$extension;
                                    $imageData = base64_decode($imageData);
                                    $upload = file_put_contents($photoPathTemp, $imageData);

                                    if(is_numeric($upload)){
                                        $photoPath = $name;
                                        $screenshot[$i]["image"]=$name;
                                        $screenshot[$i]["change"]=1;
                                        unset($screenshot[$i]["image_name"]);

                                    }else{
                                        $err_temp=array();
                                        $err_temp["filename"]=$item["image_name"];
                                        $err_upload_file[]=$err_temp;
                                        unset($screenshot[$i]);
                                    }
                                }
                            }else{
                                unset($screenshot[$i]);
                            }

                        }
                    }else{
                        $screenshot[$i]["image"]="";
                        if(isset($screenshot[$i]["image_name"])) unset($screenshot[$i]["image_name"]);

                    }
                }

            }
        }

        //
        $screenshot = json_encode($screenshot);
        if(empty($last_update)) $last_update=0;
        $result = $Object->updateHelpDesk($id,$screenshot,$subject,$problem,$form,$status,$assign_to,$last_update);

        if(is_numeric($result) && $result){
            $notes =array();
            if(isset($_POST['notes'])){
                $notes=$_POST['notes'];
            }
            $errnote =$Object->update_notes_new($notes,$last_update,$id);
            $ret = array('SAVE'=>'SUCCESS','ERROR'=>'', 'UPLOAD'=>$upload,'ID'=>$id,
                'err_note'=>$errnote);
        } else {
            $ret = array('SAVE'=>'FAIL','ERROR'=>$result,'UPLOAD'=>$upload,'ID'=>'');
            //log errors
            $info ="HelpDesk: ".
                ", subject: ".$subject.", problem ".$problem.", form: ".$form. ", err: ".$result;

            $Object->err_log("HelpDesk",$info,$id);
        }

    }else{
        $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);
    }
}
$Object->close_conn();
echo json_encode($ret);




