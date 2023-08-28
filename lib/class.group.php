<?php
require_once 'class.common.php';
class Group extends Common{
    protected $grs = array();
    protected $grs_belongto = array();
    protected $str_gr = '';
    protected $str_comparegr = '';
    protected $grs_list = array();
    protected $grs_str_gr = '';
    protected $existing_gr = array();

    protected $grs_individual = array();
    protected $existing_individual = array();

    //------------------------------------------------
    public function validate_group_fields($group_name,$department,$role,$parent_group=null,$parent_id=null)
    {
        $error = false;
        $errorMsg = "";

        if(!$error && empty($group_name)){
            $error = true;
            $errorMsg = "Group name is required.";
        }

        if(!$error && empty($department)){
            $error = true;
            $errorMsg = "Department name is required.";
        }
        if(!$error && empty($role)){
            $error = true;
            $errorMsg = "Role type is required.";
        }


        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }

    //------------------------------------------------
    public function groups(){
        $select = "Select * from groups_short ORDER BY ID ASC";
        $result = mysqli_query($this->con,$select);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['userName']=$this->convertIDtoName($row['users']);
                unset($row['users']);
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function groups_unit($unit){
        $select = "Select department,group_name,ID,
         parent_group,parent_id,role,users from groups_short
         WHERE department ='{$unit}'
         ORDER BY ID ASC";
        $result = mysqli_query($this->con,$select);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['users']= json_decode($row['users'],true);
                $list[] = $row;
            }
        }
        return $list;
    }
    //------------------------------------------------
    public function list_groups($ID=null,$role=null){
        $query = "SELECT DISTINCT *
                  FROM groups_short
                   where ((JSON_CONTAINS(users->'$[*]', JSON_ARRAY('{$ID}'))) ||
                   parent_id like '%{$ID}%') AND department = '{$role[0]["department"]}'
                   order by ID ASC";

        if(!empty($role)&&count($role)>0){
            if($role[0]['department']=='SystemAdmin'){
                $query = "SELECT *
                  FROM groups_short
                   order by ID ASC";
            }
        }

        //$str_comparegr
        $this->existing_gr=array();
        $this->grs_list= array();
        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $find = false;
                if(in_array($row['ID'], $this->existing_gr)){
                    $find = true;
                }

                if(!$find){
                    $this->existing_gr[] = $row['ID'];
                    $row['users'] =json_decode($row['users'],true);

                    $row['userName']=$this->convertArrIDtoName($row['users']);
                    $row['parent_name']=$this->convertArrIDtoName($row['parent_id']);

                    if($role[0]['department']=='SystemAdmin'){
                        $this->grs_list[] = $row;
                    }else{
                        $inArr = explode(",",$row['parent_id']);
                        if (in_array($ID, $inArr)){
                            $this->grs_list[] = $row;
                            $this->getGpChild_gr($row['ID'],$role[0]["department"]);
                        }else{
                            if (in_array($ID, $row['users'])){
                                $this->grs_list[] = $row;
                            }
                        }
                    }

                }else{
                    if($role[0]['department']!='SystemAdmin'){
                        //find child of parent_id that is exsiting in grs_list
                        $inArr = explode(",",$row['parent_id']);
                        if (in_array($ID, $inArr)){
                            $this->getGpChild_gr($row['ID'],$role[0]["department"]);
                        }
                    }
                }

            }
        }
        return $this->grs_list;
    }

    //------------------------------------------------
    public function getGpChild_gr($parentID,$department=null) {
        $query = "SELECT *
                  FROM groups_short
        where parent_group = '{$parentID}' AND department = '{$department}'";

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $find = false;
                if(in_array($row['ID'], $this->existing_gr)){
                    $find = true;
                }
                if(!$find){
                    $this->existing_gr[] = $row['ID'];
                    if(isset($row['users'])){
                        $row['users'] = json_decode($row['users'],true);
                    }else{
                        $row['users']='';
                    }

                    $row['userName']=$this->convertArrIDtoName($row['users']);
                    $row['parent_name']=$this->convertArrIDtoName($row['parent_id']);

                    $this->grs_list[] = $row;
                    $this->getGpChild_gr($row['ID'],$department);
                }
            }
        }
    }

    //------------------------------------------------
    public function groupsByUnit($unit){
        $unit = strtolower($unit);

        $select = "Select * from groups
        where LOWER(department) = '{$unit}'";

        $result = mysqli_query($this->con,$select);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        //print_r($list);
        return $list;
    }

    //------------------------------------------------
    public function UsersByUnit($unit){
        $unit = strtolower($unit);

        $select = "Select ID,first_name,last_name from contact_short
        where contact_type like '%{$unit}%' AND contact_inactive=0";

        $result = mysqli_query($this->con,$select);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        //print_r($list);
        return $list;
    }

    //------------------------------------------------
    public function addGroup($department,$group_name,$role,$users,$parent_group,$parent_id)
    {
        if(empty($parent_group)){
            $parent_group=0;
        }

        if(empty($parent_id)){
            $parent_id=0;
        }

        $fields = "department,group_name,role,users,parent_group,parent_id";

        $dateTemp = date("Y-m-d");

        $values = "'{$department}','{$group_name}','{$role}','{$users}','{$parent_group}','{$parent_id}'";

        $check ="SELECT COUNT(*) AS NUM FROM groups WHERE `department` = '{$department}' AND `group_name` ='{$group_name}'
        AND `role` ='{$role}'";
        if ($this->checkExists($check)) return "The Group doesn't already";

        $insert = "INSERT INTO groups({$fields}) VALUES({$values})";

        mysqli_query($this->con,$insert);
        $idRet = mysqli_insert_id($this->con);

        if(is_numeric($idRet) && !empty($idRet)){
            return $idRet;
        }else{
            return mysqli_error($this->con);
        }

    }

    //------------------------------------------------
    public function upGroup($id,$department,$group_name,$role,$users,$parent_group,$parent_id)
    {
        if(empty($parent_group)){
            $parent_group=0;
        }

        if(empty($parent_id)){
            $parent_id=0;
        }
        $dateTemp = date("Y-m-d");

        $check ="SELECT COUNT(*) AS NUM FROM groups WHERE `department` = '{$department}' AND
        `group_name` ='{$group_name}' AND `role` ='{$role}' AND ID <> '{$id}'";

        if ($this->checkExists($check)) return "The Group doesn't already";

        $update ="UPDATE `groups`
                SET department = '{$department}',
                group_name = '{$group_name}',
                role = '{$role}',
                users = '{$users}',
                parent_group = '{$parent_group}',
                parent_id = '{$parent_id}'

                where ID ='{$id}'";

        $isupdate = mysqli_query($this->con,$update);

        if($isupdate){
            return 1;
        }else{
            return mysqli_error($this->con);
        }

    }

    //------------------------------------------------
    public function getGroup_ID($ID) {
        $query = "SELECT DISTINCT g.department,g.group_name,g.ID,g.parent_group,g.parent_id,
        g.role,g.users,p.group_name as parent_group_name
        FROM  groups as g
        LEFT JOIN groups as p on p.ID=g.parent_group
        where g.ID = '{$ID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['users'] =json_decode($row['users'],true);

                $row['userName']=$this->convertArrIDtoName($row['users']);
                $row['parent_name']=$this->convertArrIDtoName($row['parent_id']);
                $list[] = $row;
            }
        }

        if(count($list)> 0){
            return $list[0];
        }else{
            return '';
        }
    }

    //------------------------------------------------
    public function tasks_individual($ID){

        $select = "Select * from groups
        where role =JSON_SEARCH(users, 'all', '{$ID}') IS NOT NULL";

        $result = mysqli_query($this->con,$select);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        //print_r($list);
        return $list;
    }

    //------------------------------------------------
    public function roles(){
        $select = "Select DISTINCT level from acl_rules";
        //where unit <> 'SystemAdmin'";
        $result = mysqli_query($this->con,$select);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['level'];
            }
        }
        //print_r($list);
        return $list;
    }

    //------------------------------------------------
    public function units(){
        $select = "Select DISTINCT unit from acl_rules";
        //where unit <> 'SystemAdmin'";
        $result = mysqli_query($this->con,$select);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row['unit'];
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function usersBelongGrp($id){
    $select = "Select users from groups
    where ID= '{$id}' limit 1";
    $result = mysqli_query($this->con,$select);

    $temp = array();
    if($result){
        while ($row = mysqli_fetch_assoc($result)) {
            $temp = json_decode($row['users'],true);
        }
    }

    $list = array();

    if(count($temp)>0){
        foreach($temp as $itm){
            $list[]= $this->findContact($itm);
        }
    }

    return $list;
  }

  //------------------------------------------------
  public function findContact($id){
    $select = "Select ID, concat(first_name,'',last_name) as c_name from contact
    where ID='{$id}'";
    $result = mysqli_query($this->con,$select);
    //;
    $list = array();
    if($result){
        while ($row = mysqli_fetch_assoc($result)) {
            $list = $row;
        }
    }
      if(count($list)==0){
          return (object)[];
      }else{
          return $list;
      }

  }

    //------------------------------------------------
    public function deleteGrp($id){
        //check group is leave
        $select = "Select count(*) from groups
        where parent_group = {$id}";

        $num = $this->totalRecords($select,0);

        if($num >0){
            return "Can't delete the group";
        }else{
            $del = "Delete from groups
            where ID= {$id}";

            mysqli_query($this->con,$del);
            $delete = 1;// mysqli_affected_rows($this->con);
            if($delete){
                return 1;
            } else {
                return "Can't delete the group";
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getGroupByParent($id) {
        $this->grs=array();
        $query = "SELECT *
                  FROM groups_short
        where ID = '{$id}'";

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(isset($row['users'])) $row['users'] = json_decode($row['users'],true);
                $this->grs[] = $row;

                $this->getGroupChild($row['ID']);
            }
        }

        if(count($this->grs)> 0){
            return $this->grs;
        }else{
            return '';
        }
    }

    //------------------------------------------------
    public function getGroupChild($parentID) {
        $query = "SELECT *
                  FROM groups_short
        where parent_group = '{$parentID}'";

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(isset($row['users'])) $row['users'] = json_decode($row['users'],true);
                $this->grs[] = $row;
                $this->getGroupChild($row['ID']);
            }
        }
    }

    //------------------------------------------------
    public function tasksOfIndividual($ID) {
        $query = "SELECT *
                  FROM groups_short
        where JSON_SEARCH(users, 'all', '{$ID}') IS NOT NULL";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(isset($row['users'])) $row['users'] =array($ID);
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function groupsByUserID($ID,$role=null) {
        $query = "SELECT *
                  FROM groups_short
                   where JSON_CONTAINS(users->'$[*]', JSON_ARRAY('{$ID}'))
                   order by ID ASC";

        //$str_comparegr
        $this->str_comparegr='';
        $this->grs_belongto= array();
        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $word = $row['group_name'].'_'.$row['parent_group'].'_'.$row['parent_id'].'_'.$row['role'];
                $find = stripos($this->str_comparegr,$word);

                if(!is_numeric($find)){
                    if(isset($row['users'])) $row['users'] = array($ID);
                    $row['userName']=$this->convertArrIDtoName($row['users']);
                    $this->str_gr=$this->str_comparegr.','.$word;
                    $this->grs_belongto[] = $row;
                    $this->parentForChild($ID,$row['ID']);
                }

            }
        }
        return $this->grs_belongto;
    }

    //------------------------------------------------
    public function parentForChild($loginID,$groupID){
        $query = "SELECT *
                  FROM groups_short
        where parent_group = '{$groupID}' AND parent_id='{$loginID}'";

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $word = $row['group_name'].'_'.$row['parent_group'].'_'.$row['parent_id'].'_'.$row['role'];
                $find = stripos($this->str_gr,$word);
                if(!is_numeric($find)){
                    if(isset($row['users'])) $row['users'] = json_decode($row['users'],true);
                    $row['userName']=$this->convertArrIDtoName($row['users']);
                    $this->grs_belongto[] = $row;
                    $this->getGpChild($row['ID']);
                }

            }
        }
    }

    //------------------------------------------------
    public function getGpChild($parentID) {
        $query = "SELECT *
                  FROM groups_short
        where parent_group = '{$parentID}'";

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $word = $row['group_name'].'_'.$row['parent_group'].'_'.$row['parent_id'].'_'.$row['role'];
                $find = stripos($this->str_gr,$word);
                if(!is_numeric($find)){
                    if(isset($row['users'])) $row['users'] = json_decode($row['users'],true);
                    $row['userName']=$this->convertArrIDtoName($row['users']);
                    $this->grs_belongto[] = $row;
                    $this->getGpChild($row['ID']);
                }
            }
        }
    }

    //------------------------------------------------------------------
    public function userName($IDs)
    {
        $command ="select ID, concat(first_name,' ',last_name) as c_name
              from contact
                Where ID IN ({$IDs})";

        $result = mysqli_query($this->con,$command);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function groupsByIndividual($ID) {
        $query = "SELECT *
                  FROM groups_short
                  where JSON_CONTAINS(users->'$[*]', JSON_ARRAY('{$ID}'))
                   order by ID ASC";

        //where parent_id ='{$ID}' order by ID ASC"
        $this->grs_belongto= array();
        $this->str_gr ='';

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                //child groups are unit
                $word = $row['group_name'].'_'.$row['parent_group'].'_'.$row['parent_id'].'_'.$row['role'];
                $find = stripos($this->str_gr,$word);

                if(!is_numeric($find)){
                    if(isset($row['users'])) $row['users'] = $row['users'] = array($ID);
                    $this->str_gr=$this->str_gr.','.$word;
                    $row['userName']=$this->convertArrIDtoName($row['users']);
                    $this->grs_belongto[] = $row;
                    $this->parentForChild_Individual($ID,$row['ID']);
                }
            }
        }

        return $this->grs_belongto;
    }

    //------------------------------------------------
    public function parentForChild_Individual($loginID,$groupID){
        $query = "SELECT *
                  FROM groups_short
        where parent_group = '{$groupID}' AND parent_id='{$loginID}'";

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $word = $row['group_name'].'_'.$row['parent_group'].'_'.$row['parent_id'].'_'.$row['role'];
                $find = stripos($this->str_gr,$word);

                if(!is_numeric($find)){
                    if(isset($row['users'])) $row['users'] = json_decode($row['users'],true);
                    $this->str_gr=$this->str_gr.','.$word;
                    $row['userName']=$this->convertArrIDtoName($row['users']);
                    $this->grs_belongto[] = $row;
                    $this->gpChild_Individual($row['ID']);
                }

            }
        }
    }

    //------------------------------------------------
    public function gpChild_Individual($parentID) {
        $query = "SELECT *
                  FROM groups_short
        where parent_group = '{$parentID}'";

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $word = $row['group_name'].'_'.$row['parent_group'].'_'.$row['parent_id'].'_'.$row['role'];
                $this->str_gr=$this->str_gr.','.$word;

                if(isset($row['users'])) $row['users'] = json_decode($row['users'],true);
                $row['userName']=$this->convertArrIDtoName($row['users']);
                $this->grs_belongto[] = $row;
                $this->gpChild_Individual($row['ID']);
            }
        }
    }

    //------------------------------------------------------
    public function ACL_update($acl,$level,$ID)
    {
        $update = "UPDATE `groups`
                SET acl = '{$acl}',
                role = '{$level}'
                where  ID='{$ID}'";
        //die($update);
        $istrue = mysqli_query($this->con,$update);

        if($istrue){
            return 1;
        }else{
            return "";
        }
    }

    //------------------------------------------------
    public function aclRule_grpID($ID) {
        $query = "SELECT role,acl FROM  groups
        where ID = '{$ID}'";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        if(count($list)> 0){
            $list[0]['acl'] = json_decode($list[0]['acl'],true);
            return $list[0];
        }else{
            return $list;
        }
    }

    //------------------------------------------------------
    public function getACLUnitLevel($unit,$level)
    {
        $query = "Select acl_rules from acl_rules
                where level='{$level}' AND unit='{$unit}' limit 1";

        $rsl = mysqli_query($this->con,$query);

        $list_acl = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list_acl[0]['acl'] = json_decode($row["acl_rules"],true);
            }

        }

        if(count($list_acl)>0){
            return $list_acl[0];
        }
        else return $list_acl;
    }


    //------------------------------------------------------
    public function convertIDtoName($jsonID){
        $arr_id = json_decode($jsonID,true);
        $str_id='';
        $list = array();

        if(count($arr_id)>0){
            foreach($arr_id as $item){
                $str_id .=(empty($str_id))?'':',';
                $str_id .=$item;
            }
            $query = "Select ID,concat(first_name,' ',last_name) as name from contact
                where ID IN ({$str_id})";
            $rsl = mysqli_query($this->con,$query);

            if($rsl){
                while ($row = mysqli_fetch_assoc($rsl)) {
                    $list[] = $row;
                }
            }
        }

        return $list;
    }

    //------------------------------------------------------
    public function convertArrIDtoName($arrID){
        $str_id='';
        $list = array();

        if(is_array($arrID)){
            foreach($arrID as $item){
                $str_id .=(empty($str_id))?'':',';
                $str_id .=$item;
            }
        }else{
            $str_id = $arrID;
        }

        $query = "Select ID,concat(first_name,' ',last_name) as name from contact
                where ID IN ({$str_id})";
        $rsl = mysqli_query($this->con,$query);

        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function getACL_UnitLevel($unit,$level){
        $select = "Select acl_rules,level,unit from acl_rules
        where unit = '{$unit}' AND level='{$level}'";

        $result = mysqli_query($this->con,$select);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['acl_rules'] = json_decode($row['acl_rules'],true);
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function update_processACL($rsl){
        if(count($rsl)>1){
            //acl
            $temp1 = $rsl[0]['acl'][0];
            $ClaimForm_0 = array();
            if(isset($temp1['ClaimForm'])){
                $ClaimForm_0 = $temp1['ClaimForm'] ;
            }
            $OrderForm_0 =array();
            if(isset($temp1['OrderForm'])){
                $OrderForm_0 =$temp1['OrderForm'] ;
            }
            $ContactForm_0 =array();
            if(isset($temp1['ContactForm'] )){
                $ContactForm_0 =$temp1['ContactForm'] ;
            }
            $InvoiceForm_0 =array();
            if(isset($temp1['InvoiceForm'])){
                $InvoiceForm_0 =$temp1['InvoiceForm'] ;
            }
            $ProductForm_0 =array();
            if(isset($temp1['ProductForm'])){
                $ProductForm_0 =$temp1['ProductForm'] ;
            }
            $WarrantyForm_0 =array();
            if(isset($temp1['WarrantyForm'])){
                $WarrantyForm_0 =$temp1['WarrantyForm'];
            }

            $CompanyForm_0 =array();
            if(isset($temp1['CompanyForm'])){
                $CompanyForm_0 =$temp1['CompanyForm'];
            }

            $Dashboard_0 =array();
            if(isset($temp1['Dashboard'])){
                $Dashboard_0 =$temp1['Dashboard'];
            }

            $GroupForm_0 =array();
            if(isset($temp1['GroupForm'])){
                $GroupForm_0 =$temp1['GroupForm'];
            }

            $Navigation_0 =array();
            if(isset($temp1['Navigation'])){
                $Navigation_0 =$temp1['Navigation'];
            }

            $BillingTemplateForm_0 =array();
            if(isset($temp1['BillingTemplateForm'])){
                $BillingTemplateForm_0 =$temp1['BillingTemplateForm'];
            }

            $DiscountForm_0 =array();
            if(isset($temp1['DiscountForm'])){
                $DiscountForm_0 =$temp1['DiscountForm'];
            }

            $SettingForm_0 =array();
            if(isset($temp1['SettingForm'])){
                $SettingForm_0 =$temp1['SettingForm'];
            }

            $PermissionForm_0 =array();
            if(isset($temp1['PermissionForm'])){
                $PermissionForm_0 =$temp1['PermissionForm'];
            }

            $TaskForm_0 =array();
            if(isset($temp1['TaskForm'])){
                $TaskForm_0 =$temp1['TaskForm'];
            }

            //level
            for($i=1;$i<count($rsl);$i++){
                $temp2 = $rsl[$i]['acl'][0];
                //process claim acl
                $ClaimForm_i =array();
                if(isset($temp2['ClaimForm'])){
                    $ClaimForm_i =$temp2['ClaimForm'] ;
                }

                if(count($ClaimForm_0)>0){
                    foreach($ClaimForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ClaimForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $ClaimForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $ClaimForm_0[$k0] = $v0;
                    }
                }else{
                    $ClaimForm_0 = $ClaimForm_i;
                }

                //process OrderForm acl
                $OrderForm_i =array();
                if(isset($temp2['OrderForm'])){
                    $OrderForm_i =$temp2['OrderForm'];
                }

                if(count($OrderForm_0)>0){
                    foreach($OrderForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($OrderForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $OrderForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $OrderForm_0[$k0] = $v0;
                    }
                }else{
                    $OrderForm_0 =$OrderForm_i;
                }

                //process ContactForm acl
                $ContactForm_i =array();
                if(isset($temp2['ContactForm'])){
                    $ContactForm_i =$temp2['ContactForm'] ;
                }

                if($ContactForm_0>0){
                    foreach($ContactForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ContactForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $ContactForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $ContactForm_0[$k0] = $v0;
                    }
                }else{
                    $ContactForm_0 =  $ContactForm_i;
                }



                //process InvoiceForm acl
                $InvoiceForm_i =array();
                if(isset($temp2['InvoiceForm'])){
                    $InvoiceForm_i =$temp2['InvoiceForm'] ;
                }
                if(count($InvoiceForm_0)>0){
                    foreach($InvoiceForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($InvoiceForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $InvoiceForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $InvoiceForm_0[$k0] = $v0;
                    }
                }else{
                    $InvoiceForm_0 = $InvoiceForm_i;
                }

                //process ProductForm acl
                $ProductForm_i =array();
                if(isset($temp2['ProductForm'])){
                    $ProductForm_i =$temp2['ProductForm'] ;
                }

                if(count($ProductForm_0)>0){
                    foreach($ProductForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($ProductForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $ProductForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $ProductForm_0[$k0] = $v0;
                    }
                }else{
                    $ProductForm_0 = $ProductForm_i;
                }
                //process WarrantyForm acl
                $WarrantyForm_i =array();
                if(isset($temp2['WarrantyForm'])){
                    $WarrantyForm_i =$temp2['WarrantyForm'] ;
                }

                if(count($WarrantyForm_0)>0){
                    foreach($WarrantyForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($WarrantyForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $WarrantyForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $WarrantyForm_0[$k0] = $v0;
                    }
                }else{
                    $WarrantyForm_0 = $WarrantyForm_i;
                }

                //process CompanyForm acl
                $CompanyForm_i =array();
                if(isset($temp2['CompanyForm'])){
                    $CompanyForm_i =$temp2['CompanyForm'] ;
                }

                if(count($CompanyForm_0)>0){
                    foreach($CompanyForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($CompanyForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $CompanyForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $CompanyForm_0[$k0] = $v0;
                    }
                }else{
                    $CompanyForm_0 = $CompanyForm_i;
                }

                //process Dashboard acl
                $Dashboard_i =array();
                if(isset($temp2['Dashboard'])){
                    $Dashboard_i =$temp2['Dashboard'] ;
                }

                if(count($Dashboard_0)>0){

                    foreach($Dashboard_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($Dashboard_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $Dashboard_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $Dashboard_0[$k0] = $v0;
                    }
                }else{
                    $Dashboard_0 = $Dashboard_i;
                }

                //process GROUPorm acl
                $GroupForm_i =array();
                if(isset($temp2['GroupForm'])){
                    $GroupForm_i =$temp2['GroupForm'] ;
                }

                if(count($GroupForm_0)>0){

                    foreach($GroupForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($GroupForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $GroupForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $GroupForm_0[$k0] = $v0;
                    }
                }else{
                    $GroupForm_0 = $GroupForm_i;
                }

                //process Navigation acl
                $Navigation_i =array();
                if(isset($temp2['Navigation'])){
                    $Navigation_i =$temp2['Navigation'] ;
                }

                if(count($Navigation_0)>0){
                    foreach($Navigation_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($Navigation_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $Navigation_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $Navigation_0[$k0] = $v0;
                    }
                }else{
                    $Navigation_0 = $Navigation_i;
                }
                //--
                //process BillingTemplateForm acl
                $BillingTemplateForm_i =array();
                if(isset($temp2['BillingTemplateForm'])){
                    $BillingTemplateForm_i =$temp2['BillingTemplateForm'] ;
                }

                if(count($BillingTemplateForm_0)>0){
                    foreach($BillingTemplateForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($BillingTemplateForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $BillingTemplateForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $BillingTemplateForm_0[$k0] = $v0;
                    }
                }else{
                    $BillingTemplateForm_0 = $BillingTemplateForm_i;
                }

                //process DiscountForm acl
                $DiscountForm_i =array();
                if(isset($temp2['DiscountForm'])){
                    $DiscountForm_i =$temp2['DiscountForm'] ;
                }

                if(count($DiscountForm_0)>0){
                    foreach($DiscountForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($DiscountForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $DiscountForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $DiscountForm_0[$k0] = $v0;
                    }
                }else{
                    $DiscountForm_0 = $DiscountForm_i;
                }

                //process SettingForm acl
                $SettingForm_i =array();
                if(isset($temp2['SettingForm'])){
                    $SettingForm_i =$temp2['SettingForm'] ;
                }

                if(count($SettingForm_0)>0){
                    foreach($SettingForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($SettingForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $SettingForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $SettingForm_0[$k0] = $v0;
                    }
                }else{
                    $SettingForm_0 = $SettingForm_i;
                }

                //process PermissionForm acl
                $PermissionForm_i =array();
                if(isset($temp2['PermissionForm'])){
                    $PermissionForm_i =$temp2['PermissionForm'] ;
                }

                if(count($PermissionForm_0)>0){
                    foreach($PermissionForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($PermissionForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $PermissionForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $PermissionForm_0[$k0] = $v0;
                    }
                }else{
                    $PermissionForm_0 = $PermissionForm_i;
                }

                //process TaskForm acl
                $TaskForm_i =array();
                if(isset($temp2['TaskForm'])){
                    $TaskForm_i =$temp2['TaskForm'] ;
                }

                if(count($TaskForm_0)>0){
                    foreach($TaskForm_0 as $k0=>$v0){
                        foreach($v0 as $v0_k=>$v0_v){
                            if($v0_k!="display"){
                                if(isset($TaskForm_i[$k0][$v0_k])){
                                    $v0[$v0_k] = false || $TaskForm_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = false;
                                }
                            }
                        }
                        $TaskForm_0[$k0] = $v0;
                    }
                }else{
                    $TaskForm_0 = $TaskForm_i;
                }
            //--
            }

            $rtn=  Array
                (
                    'acl' => Array
                    (
                        Array
                        (
                            'ClaimForm' => $ClaimForm_0,
                            'OrderForm' => $OrderForm_0,
                            'ContactForm' => $ContactForm_0,
                            'InvoiceForm' => $InvoiceForm_0,
                            'ProductForm' => $ProductForm_0,
                            'WarrantyForm' => $WarrantyForm_0,
                            'CompanyForm' => $CompanyForm_0,
                            'Dashboard' => $Dashboard_0,
                            'GroupForm' => $GroupForm_0,
                            'Navigation' => $Navigation_0,
                            'BillingTemplateForm'=>$BillingTemplateForm_0,
                            'DiscountForm'=>$DiscountForm_0,
                            'SettingForm'=>$SettingForm_0,
                            'PermissionForm'=>$PermissionForm_0,
                            'TaskForm'=>$TaskForm_0
                        )
                    )
                );

            if(count($CompanyForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['CompanyForm']);
            }

            if(count($ClaimForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ClaimForm']);
            }

            if(count($OrderForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['OrderForm']);
            }

            if(count($ContactForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ContactForm']);
            }

            if(count($InvoiceForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['InvoiceForm']);
            }

            if(count($ProductForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['ProductForm']);
            }

            if(count($WarrantyForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['WarrantyForm']);
            }

            if(count($WarrantyForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['WarrantyForm']);
            }

            if(count($Dashboard_0)<1) {
                unset($rtn[0]['acl_rules'][0]['Dashboard']);
            }

            if(count($GroupForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['GroupForm']);
            }

            if(count($Navigation_0)<1) {
                unset($rtn[0]['acl_rules'][0]['Navigation']);
            }

            if(count($BillingTemplateForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['BillingTemplateForm']);
            }
            if(count($DiscountForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['DiscountForm']);
            }
            if(count($SettingForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['SettingForm']);
            }
            if(count($PermissionForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['PermissionForm']);
            }

            if(count($TaskForm_0)<1) {
                unset($rtn[0]['acl_rules'][0]['TaskForm']);
            }

            return $rtn;

        }else{
            return array();
        }
    }

    //------------------------------------------------
    public function list_gr_individual($ID=null){
        $query = "SELECT DISTINCT *
                  FROM groups_short
                   where ((JSON_CONTAINS(users->'$[*]', JSON_ARRAY('{$ID}'))) ||
                   parent_id like '%{$ID}%')
                   order by ID ASC";


        //$str_comparegr
        $this->existing_individual=array();
        $this->grs_individual= array();
        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $find = false;
                if(in_array($row['ID'], $this->existing_individual)){
                    $find = true;
                }

                if(!$find){
                    $this->existing_individual[] = $row['ID'];
                    $row['users'] =json_decode($row['users'],true);

                    $row['userName']=$this->convertArrIDtoName($row['users']);
                    $row['parent_name']=$this->convertArrIDtoName($row['parent_id']);

                    $inArr = explode(",",$row['parent_id']);
                    if (in_array($ID, $inArr)){
                        $this->grs_individual[] = $row;
                        $this->getGpChild_gr_individual($row['ID']);
                    }else{
                        if (in_array($ID, $row['users'])){
                            $this->grs_individual[] = $row;
                        }
                    }

                }else{
                    //find child of parent_id that is exsiting in grs_individual
                    $inArr = explode(",",$row['parent_id']);
                    if (in_array($ID, $inArr)){
                        $this->getGpChild_gr_individual($row['ID']);
                    }
                }

            }
        }
        return $this->grs_individual;
    }

    //------------------------------------------------
    public function getGpChild_gr_individual($parentID,$department=null) {
        $query = "SELECT *
                  FROM groups_short
        where parent_group = '{$parentID}'";

        $result = mysqli_query($this->con,$query);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $find = false;
                if(in_array($row['ID'], $this->existing_individual)){
                    $find = true;
                }
                if(!$find){
                    $this->existing_individual[] = $row['ID'];
                    if(isset($row['users'])){
                        $row['users'] = json_decode($row['users'],true);
                    }else{
                        $row['users']='';
                    }

                    $row['userName']=$this->convertArrIDtoName($row['users']);
                    $row['parent_name']=$this->convertArrIDtoName($row['parent_id']);

                    $this->grs_individual[] = $row;
                    $this->getGpChild_gr_individual($row['ID']);
                }
            }
        }
    }

    //------------------------------------------------------
    public function ACLUpdateDefault($acl,$level,$unit)
    {
        $update = "UPDATE `acl_rules`
                SET acl_rules = '{$acl}'
                where  level='{$level}' AND unit='{$unit}'";
        //die($update);
        $istrue = mysqli_query($this->con,$update);

        if($istrue){
            return 1;
        }else{
            return "";
        }
    }

    /////////////////////////////////////////////////////////
}