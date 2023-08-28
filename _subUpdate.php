<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.Subcription.php';
    $Object = new Subcription();

    $EXPECTED = array('token','id','name','status','json','jwt','private_key');

    foreach ($EXPECTED AS $key) {
        if (!empty($_POST[$key])){
            ${$key} = $Object->protect($_POST[$key]);
        } else {
            ${$key} = NULL;
        }
    }

    $isAuth =$Object->basicAuth($token);
    if(!$isAuth){
        $ret = array('ERROR'=>'Authentication is failed');
    }else{
		$isAuth = $Object->auth($jwt,$private_key);
            //$isAuth['AUTH']=true;
		if($isAuth['AUTH']){
            $errObj = $Object->validate_sub_fields($name,$json);

            if(!$errObj['error']){
                if(empty($status)) $status=0;
                $rsl = $Object->updateSubmit($id,$name,$json,$status);
				if(is_numeric($rsl) && !empty($rsl)){
					$ret = array('AUTH'=>true,'ERROR'=>'','SAVE'=>'SUCCESS');
				}else{
					$ret = array('AUTH'=>true,'ERROR'=>$rsl,'SAVE'=>'failed');
				}
                
            }else{
                $ret = array('AUTH'=>true,'ERROR'=>$errObj['error']);
            }

		}else{
			$ret = array('ERROR'=>'Authentication is failed');
		}
		
    }

    $Object->close_conn();
    echo json_encode($ret);




