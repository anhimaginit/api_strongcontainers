<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.mail.php';
    $Object = new LocalEmail();

    $EXPECTED = array('token','description','draft','senderID','receiverID','subject','jwt','private_key','id','checked');

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
        //$isAuth['AUTH']=true;
        if($isAuth['AUTH']){
            $errObj = $Object->validate_mail_fields($senderID,$receiverID);

            if(!$errObj['error']){


                $result = $Object->ComposerMail($description,$draft,$receiverID,$senderID,$subject,$id,$checked);

                if(is_numeric($result) && $result){
                    $ret = array('AUTH'=>true,'SAVE'=>'SUCCESS','ERROR'=>'','id'=>$result);
                } else {
                    $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$result);

                }

            } else {
                $ret = array('AUTH'=>true,'SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg']);

            }
        }else{
            $ret = array('AUTH'=>false,'ERROR'=>$isAuth['ERROR']);
        }
    }


    $Object->close_conn();
    echo json_encode($ret);




