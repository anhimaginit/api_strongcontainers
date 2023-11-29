<?php


require_once 'class.common.php';
class State extends Common{
    //------------------------------------------------------
    public function getStateList()
    {
        $sqlText = "Select * From state";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //------------------------------------------------------------

    public function getCityByState($state)
    {
        $sqlText = "Select city From zip WHERE state = '{$state}'
                    GROUP BY city ORDER BY city";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function getZipcodeByCity($city)
    {
        $sqlText = "Select zip From zip WHERE city = '{$city}'";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function getCitySateZip_test($city)
    {
        $sqlText = "Select zip,city,state From zip
        WHERE city = '{$city}'
        ORDER BY state";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        $result = array();
        $st = '';
        $z =array();

        if(count($list)>0){
           for($i=0;$i<count($list);$i++){
               $state_i = $list[$i]['state'];
               $zip = $list[$i]['zip'];

               $j = $i+1;
               if($j<count($list)){
                   $state_j = $list[$j]['state'];
                   if($state_i!=$state_j){
                       if($i==0){
                           $result[] = array('state'=>$state_i,'zip'=>array($zip));
                       }else{
                           if(count($z)>0){
                               $z[] = $zip;
                               $result[] = array('state'=>$state_i,'zip'=>$z);
                           }else{
                               $result[] = array('state'=>$state_i,'zip'=>array($zip));
                           }
                       }

                       $z=array();
                   }else{
                       $z[] = $zip;
                   }
               }else{
                   $z[] = $zip;
                   $result[] = array('state'=>$state_i,'zip'=>$z);
               }
           }
        }

        return $result;
    }
    //------------------------------------------------------------
    public function getCities($city)
    {
        $sqlText = "Select DISTINCT city From zip
        where city like '{$city}%'
        ORDER BY city";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function checkZipcode($zip,$city=null)
    {
        $sqlText = "Select zip,city,state From zip WHERE zip = '{$zip}' limit 1";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }

        }
        return $list;
    }

    //------------------------------------------------------------
    public function getCitySateZip($city)
    {
        $sqlText = "Select z.zip,z.city,z.state, s.state as state_name From zip as z
        LEFT JOIN state as s on s.code = z.state
        WHERE city = '{$city}'
        ORDER BY state";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        $result = array();
        $st = '';
        $z =array();

        if(count($list)>0){
            for($i=0;$i<count($list);$i++){
                $state_i = $list[$i]['state'];
                $state_name = $list[$i]['state_name'];
                $zip = $list[$i]['zip'];

                $j = $i+1;
                if($j<count($list)){
                    $state_j = $list[$j]['state'];
                    if($state_i!=$state_j){
                        if($i==0){
                            $result[] = array('state'=>$state_i,'state_name'=>$state_name ,'zip'=>array($zip));
                        }else{
                            if(count($z)>0){
                                $z[] = $zip;
                                $result[] = array('state'=>$state_i,'state_name'=>$state_name,'zip'=>$z);
                            }else{
                                $result[] = array('state'=>$state_i,'state_name'=>$state_name,'zip'=>array($zip));
                            }
                        }

                        $z=array();
                    }else{
                        $z[] = $zip;
                    }
                }else{
                    $z[] = $zip;
                    $result[] = array('state'=>$state_i,'state_name'=>$state_name,'zip'=>$z);
                }
            }
        }

        return $result;
    }

    //------------------------------------------------------------
    public function getListCity($city)
    {
        $sqlText = "Select DISTINCT city From zip
        WHERE city like '%{$city}%'";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['city'];
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function listCitySateZip($city,$state,$zip){
        $rsl=array();

        if(!empty($city)){
            //
            $sqlText = "Select DISTINCT city From zip
            WHERE city like '{$city}%'";

            $result = mysqli_query($this->con,$sqlText);
            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                   $rulsult = $this->stateZip($row['city'],$state,$zip);
                    if(count($rulsult)>0) $rsl[] =$rulsult;
                }
            }

        }elseif(empty($city) && !empty($state)){
             $rulsult  =$this->cityZip($city,$state,$zip);
            if(count($rulsult)>0) $rsl[] =$rulsult;
        }else{
            $rulsult =$this->state_city_zip($zip);
            if(count($rulsult)>0) $rsl[] =$rulsult;
        }

        return $rsl;
    }

    //------------------------------------------------------------
    public function stateZip($city,$state,$zip)
    {
        $sqlText = "Select distinct z.zip,z.city,z.state, s.state as state_name From zip as z
        LEFT JOIN state as s on s.code = z.state";

        $sqlText .=" WHERE city = '{$city}'";

        if(!empty($state)) $sqlText .=" AND z.state ='{$state}'";
        if(!empty($zip)) $sqlText .=" AND z.zip ='{$zip}'";
        $sqlText .=" ORDER BY state";
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        $result = array();
        $st = '';
        $z =array();

        if(count($list)>0){
            for($i=0;$i<count($list);$i++){
                $state_i = $list[$i]['state'];
                $state_name = $list[$i]['state_name'];
                $zip = $list[$i]['zip'];

                $j = $i+1;
                if($j<count($list)){
                    $state_j = $list[$j]['state'];
                    if($state_i!=$state_j){
                        if($i==0){
                            $result[] = array('city'=>$city,'state'=>$state_i,'state_name'=>$state_name ,'zip'=>array($zip));
                        }else{
                            if(count($z)>0){
                                $z[] = $zip;
                                $result[] = array('city'=>$city,'state'=>$state_i,'state_name'=>$state_name,'zip'=>$z);
                            }else{
                                $result[] = array('city'=>$city,'state'=>$state_i,'state_name'=>$state_name,'zip'=>array($zip));
                            }
                        }

                        $z=array();
                    }else{
                        $z[] = $zip;
                    }
                }else{
                    $z[] = $zip;
                    $result[] = array('city'=>$city,'state'=>$state_i,'state_name'=>$state_name,'zip'=>$z);
                }
            }
        }

        return $result;
    }

    //------------------------------------------------------------
    public function cityZip($city,$state,$zip)
    {
        $sqlText = "Select DISTINCT z.zip,z.city,z.state, s.state as state_name From zip as z
        LEFT JOIN state as s on s.code = z.state";
        $sqlText .=" WHERE z.state = '{$state}'";
        if(!empty($zip)) $sqlText .=" AND z.zip ='{$zip}'";
        $sqlText .=" ORDER BY city";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        $result = array();
        $st = '';
        $z =array();

        if(count($list)>0){
            for($i=0;$i<count($list);$i++){
                $city_i = $list[$i]['city'];
                $state_name = $list[$i]['state_name'];
                $zip = $list[$i]['zip'];

                $j = $i+1;
                if($j<count($list)){
                    $city_j = $list[$j]['city'];
                    if($city_i!=$city_j){
                        if($i==0){
                            $result[] = array('city'=>$city_i,'state'=>$state,'state_name'=>$state_name ,'zip'=>array($zip));
                        }else{
                            if(count($z)>0){
                                $z[] = $zip;
                                $result[] = array('city'=>$city_i,'state'=>$state,'state_name'=>$state_name,'zip'=>$z);
                            }else{
                                $result[] = array('city'=>$city_i,'state'=>$state,'state_name'=>$state_name,'zip'=>array($zip));
                            }
                        }

                        $z=array();
                    }else{
                        $z[] = $zip;
                    }
                }else{
                    $z[] = $zip;
                    $result[] = array('city'=>$city_i,'state'=>$state,'state_name'=>$state_name,'zip'=>$z);
                }
            }
        }

        return $result;
    }

    //------------------------------------------------------------
    public function state_city_zip($zip)
    {
        $sqlText = "Select DISTINCT z.zip,z.city,z.state, s.state as state_name From zip as z
        LEFT JOIN state as s on s.code = z.state";

        if(!empty($zip)) $sqlText .=" WHERE z.zip ='{$zip}'";

        $sqlText .=" ORDER BY city";

        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        $result = array();
        $st = '';
        $z =array();

        if(count($list)>0){
            for($i=0;$i<count($list);$i++){
                $city_i = $list[$i]['city'];
                $state_name = $list[$i]['state_name'];
                $zip = $list[$i]['zip'];
                $state_i = $list[$i]['state'];
                $j = $i+1;
                if($j<count($list)){
                    $city_j = $list[$j]['city'];
                    if($city_i!=$city_j){
                        if($i==0){
                            $result[] = array('city'=>$city_i,'state'=>$state_i,'state_name'=>$state_name ,'zip'=>array($zip));
                        }else{
                            if(count($z)>0){
                                $z[] = $zip;
                                $result[] = array('city'=>$city_i,'state'=>$state_i,'state_name'=>$state_name,'zip'=>$z);
                            }else{
                                $result[] = array('city'=>$city_i,'state'=>$state_i,'state_name'=>$state_name,'zip'=>array($zip));
                            }
                        }

                        $z=array();
                    }else{
                        $z[] = $zip;
                    }
                }else{
                    $z[] = $zip;
                    $result[] = array('city'=>$city_i,'state'=>$state_i,'state_name'=>$state_name,'zip'=>$z);
                }
            }
        }

        return $result;
    }

    //------------------------------------------------------------
    public function addNewState($city,$state,$state_name,$zip){
        $city = trim($city);
        $city =strtoupper($city);
        $state = trim($state);
        $state = strtoupper($state);
        $state_name = trim($state_name);
        $zip = trim($zip);

        $checkExisting ="SELECT COUNT(*) AS NUM FROM zip WHERE
        city ='{$city}' AND state='{$state}' AND zip='{$zip}'";
        if ($this->checkExists($checkExisting)) return $city.','.$state.' and '.$zip.' are already';

        $fields = "city,state,zip,type";
        $values = "'{$city}','{$state}','{$zip}','USERADDED'";

        $insertCommand = "INSERT INTO zip({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);
        if(is_numeric($idreturn) && $idreturn){
            //check state is already
            $checkExisting ="SELECT COUNT(*) AS NUM FROM state WHERE code='{$state}'";
            if (!$this->checkExists($checkExisting)){
                $fields = "code,state";
                $values = "'{$state}','{$state_name}'";

                $insertCommand = "INSERT INTO state({$fields}) VALUES({$values})";
                mysqli_query($this->con,$insertCommand);
            }

            return 1;
        }else{
            return mysqli_error($this->con);
        }

    }


    //------------------------------------------------
    public function validate_state_fields($city,$state,$zip)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($city)){
            $error = true;
            $errorMsg = "City is required.";
        }

        if(!$error && empty($state)){
            $error = true;
            $errorMsg = "State is required.";
        }

        if(!$error && empty($zip)){
            $error = true;
            $errorMsg = "Zip is required.";
        }

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }


    /////////////////////////////////////////////////////////
}