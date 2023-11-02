<?php
require_once 'class.common.php';
require_once 'const.php';
require_once 'acl_default_rule.php';

include_once 'jwtconfig.php';
//require_once 'PHPMailer-5.2.27/PHPMailerAutoload.php';

include_once 'php-jwt/BeforeValidException.php';
include_once 'php-jwt/ExpiredException.php';
include_once 'php-jwt/SignatureInvalidException.php';

include_once 'php-jwt/JWT.php';
use \Firebase\JWT\JWT ;
//use \Firebase\JWT ;
JWT::$leeway = 30;
class ACL extends Common{
    public function get_ACL($unit,$level)
    {
        $query = "Select acl_rules from acl_rules
                where level='{$level}' AND unit='{$unit}' limit 1";
        $rsl = mysqli_query($this->con,$query);

        $list_acl = "";
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list_acl = json_decode($row["acl_rules"],true);
            }

        }

        return $list_acl;
    }

    //------------------------------------------------------
    public function get_globalACL_ID($UID)
    {
        $query = "Select * from global_acl

                where g_UID='{$UID}' limit 1";
        $rsl = mysqli_query($this->con,$query);

        $list_acl = "";
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list_acl[] = $row;
            }

            if(count($list_acl)>0){
                $list_acl[0]["g_right"] =  json_decode($list_acl[0]["g_right"],true);
            }

        }

        return $list_acl;
    }

    //------------------------------------------------------
    public function get_globalACLs($limit,$offset)
    {
        $query = "Select g.g_id,g.g_UID,
        concat(c.first_name,' ',c.last_name) as contact_name
         from global_acl as g
         Left Join contact as c ON c.ID = g.g_UID";

        $query .= " ORDER BY g_id ASC";

        if(!empty($limit)){
            $query .= " LIMIT {$limit} ";
        }
        if(!empty($offset)) {
            $query .= " OFFSET {$offset} ";
        }
        $rsl = mysqli_query($this->con,$query);

        $list_acl = "";
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list_acl[] = $row;
            }

        }

        return $list_acl;
    }

    //------------------------------------------------------------
    public function globalACL_Records()
    {
        $sqlText = "Select Count(*) From global_acl";
        $num = $this->totalRecords($sqlText,0);

        return $num;
    }

    //////////////////////////////03-10-2023///////////////////////////
    public function getFieldsTable(){
        /*$query = "SELECT table_name as 'table_name'
        FROM information_schema.tables
        WHERE table_type='BASE TABLE'
        AND table_schema='freedomhw_crm_production' and table_name <> 'acl_rules' and
         table_name <> 'affiliate' and table_name <> 'auth_firebase' and
         table_name <> 'bug' and table_name <> 'charity_of_choice' and
         table_name <> 'claim_limits' and table_name <> 'claim_quote' and
         table_name <> 'claim_transaction' and table_name <> 'color' and
         table_name <> 'contact_doc' and table_name <> 'contact_second_phone' and
         table_name <> 'current_container_inv' and table_name <> 'customer_warranty' and
         table_name <> 'bug' and table_name <> 'charity_of_choice' ";

        $result = mysqli_query($this->con,$query);

        $tables = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $tables[] = $row['table_name'];
            }
        }*/
        $tables_acl =array("assign_task"=>"TaskForm",
            "calendar"=>"calendar",
            //"claims"=>"ClaimForm",
            "company"=>"CompanyForm",
            "contact"=>"ContactForm",
            "discount"=>"DiscountForm",
            "groups"=>"GroupForm",
            "invoice"=>"InvoiceForm",
            "quote"=>"OrderForm",
            "products"=>"ProductForm",
           //"warranty"=>"WarrantyForm",
            "depots"=>"Depots",
            "rate_container"=>"RateContainer",
            "rate_shipping"=>"RateShipping",
            "container_type"=>"ContainerType",
            "SettingForm"=>"SettingForm",
            "Navigation"=>"Navigation",
            "Dashboard"=>"Dashboard",
            "ControlListForm"=>"ControlListForm",
            "BillingTemplateForm"=>"BillingTemplateForm"
        );
        $tables =array("assign_task",
            "calendar",
            //"claims",
            "company",
            "contact",
            "discount",
            "groups",
            "invoice",
            "quote",
            "products",
            //"warranty",
            "depots",
            "rate_container",
            "rate_shipping",
            "container_type"
        );
        $all_tables_fields =array();

        foreach($tables as $item){
            /*$and= "";
            if($item=='assigned_integration'){
                $and =" AND COLUMN_NAME <> 'ai_id'";
            }elseif($item=='branch'){
                $and =" AND COLUMN_NAME <> 'b_id'";
            }elseif($item=='company'){
                $and =" AND COLUMN_NAME <> 'c_id'";
            }elseif($item=='groups'){
                $and =" AND COLUMN_NAME <> 'g_id'";
            }elseif($item=='integrations'){
                $and =" AND COLUMN_NAME <> 'i_id'";
            }elseif($item=='user'){
                $and =" AND COLUMN_NAME <> 'u_id'";
            }*/

            $query = "SELECT `COLUMN_NAME`
                  FROM `INFORMATION_SCHEMA`.`COLUMNS`
                  WHERE `TABLE_SCHEMA`='freedomhw_crm_production'
                  AND `TABLE_NAME`= '{$item}' AND ORDINAL_POSITION <>1";

            $result = mysqli_query($this->con,$query);

            $fileds = array();
            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $fileds[] = $row['COLUMN_NAME'];
                }
            }
            $all_tables_fields[$item] = $fileds;
        }
        //add tables
        $obj_accl_default = new acl_rule_default();
        //$all_tables_fields['claims'] = array_merge($all_tables_fields['claims'],$obj_accl_default->claim_fields);
        $all_tables_fields['company'] = array_merge($all_tables_fields['company'],$obj_accl_default->company_fields);
        $all_tables_fields['contact'] = array_merge($all_tables_fields['contact'],$obj_accl_default->contact_fields);
        $all_tables_fields['discount'] = array_merge($all_tables_fields['discount'],$obj_accl_default->discount_fields);
        $all_tables_fields['groups'] = array_merge($all_tables_fields['groups'],$obj_accl_default->group_fields);
        $all_tables_fields['invoice'] = array_merge($all_tables_fields['invoice'],$obj_accl_default->invoice_fields);
        $all_tables_fields['quote'] = array_merge($all_tables_fields['quote'],$obj_accl_default->quote_fields);
        $all_tables_fields['products'] = array_merge($all_tables_fields['products'],$obj_accl_default->product_fields);

        //$all_tables_fields['warranty'] =array_merge($all_tables_fields['warranty'],$obj_accl_default->$warranty_fields);
        //table control
        $all_tables_fields['SettingForm'] = $obj_accl_default->setting_fields;
        $all_tables_fields['Navigation'] = $obj_accl_default->navigation_fields;
        $all_tables_fields['Dashboard'] = $obj_accl_default->dasboard_fields;
        $all_tables_fields['ControlListForm'] = $obj_accl_default->control_fields;
        $all_tables_fields['BillingTemplateForm'] = $obj_accl_default->billing_fields;

        $tables[]="SettingForm";
        $tables[]="Navigation";
        $tables[]="Dashboard";
        $tables[]="ControlListForm";
        $tables[]="BillingTemplateForm";
        return array("tables"=>$tables,"table_field"=>$all_tables_fields,"tables_acl"=>$tables_acl) ;
    }

    //-----------------------------------------------
    public function create_group_default($g_id,$g_department,$group_name,$g_role=null,$g_users=null,$u_id_login=null){
        $table = $this->getFieldsTable();
        $data = array();
        $permission_table=array();
        $data['PermissionForm']="";
        switch ($g_role){
            case level_admin:
                $permission_table['acl_form'] =array("edit"=>true);
                foreach($table['tables'] as $it){
                    $form_name = $table['tables_acl'][$it];
                    $permission_table[$form_name] =array("edit"=>true);
                    $it_arr = array();
                    if($it !="Navigation"){
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"add"=>true,"edit"=>true);
                        }
                    }else{
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"onlyview"=>true);
                        }
                    }
                    $data[$form_name]=$it_arr;
                }
                break;
            case level_manager:
                $permission_table['acl_form'] =array("edit"=>true);
                foreach($table['tables'] as $it){
                    $form_name = $table['tables_acl'][$it];
                    $permission_table[$form_name] =array("edit"=>true);
                    $it_arr = array();
                    if($it !="Navigation"){
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"add"=>true,"edit"=>true);
                        }
                    }else{
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"onlyview"=>true);
                        }
                    }
                    $data[$form_name]=$it_arr;
                }
                break;
            case level_leader:
                $permission_table['acl_form'] =array("edit"=>false);
                foreach($table['tables'] as $it){
                    $form_name = $table['tables_acl'][$it];
                    if($form_name =="SettingForm"){
                        $permission_table[$form_name] =array("edit"=>false);
                    }elseif($form_name =="ControlListForm"){
                        $permission_table[$form_name] =array("edit"=>false);
                    }else{
                        $permission_table[$form_name] =array("edit"=>true);
                    }

                    $it_arr = array();
                    if($it !="Navigation" && $it !="company"){
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"add"=>true,"edit"=>true);
                        }
                    }else{
                        if($it !="Navigation"){
                            foreach($table["table_field"][$it] as $key =>$v){
                                $it_arr[$v] = array("show"=>true,"add"=>false,"edit"=>false);
                            }
                        }else{
                            foreach($table["table_field"][$it] as $key =>$v){
                                if($v !='role' && $v !='group' &&
                                    $v !='addgroup' && $v !='helpdesk' &&
                                    $v !='listgroup' && $v !='adddiscount' &&
                                    $v !='discountlist' && $v !='administrator' &&
                                    $v !='billing' && $v !='setting' &&
                                    $v !='addcontainertype' && $v !='containertypelist' &&
                                    $v !='adddepot' && $v !='depotlist' &&
                                    $v !='rddrateshipping' && $v !='rateshippinglist'){

                                    $it_arr[$v] = array("show"=>true,"onlyview"=>true);
                                }elseif($v =='administrator'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }elseif($v =='billing'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }elseif($v =='setting'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }
                                else{
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>false);
                                }
                            }
                        }
                    }
                    $data[$form_name]=$it_arr;
                }
                break;
            default:
                $permission_table['acl_form'] =array("edit"=>false);
                foreach($table['tables'] as $it){
                    $form_name = $table['tables_acl'][$it];
                    if($form_name =="SettingForm"){
                        $permission_table[$form_name] =array("edit"=>false);
                    }elseif($form_name =="ControlListForm"){
                        $permission_table[$form_name] =array("edit"=>false);
                    }else{
                        $permission_table[$form_name] =array("edit"=>true);
                    }
                    $it_arr = array();
                    if($it !="Navigation" && $it !="company"){
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"add"=>true,"edit"=>true);
                        }
                    }else{
                        if($it !="Navigation"){
                            foreach($table["table_field"][$it] as $key =>$v){
                                $it_arr[$v] = array("show"=>true,"add"=>false,"edit"=>false);
                            }
                        }else{
                            foreach($table["table_field"][$it] as $key =>$v){
                                if($v !='role' && $v !='group' &&
                                    $v !='addgroup' && $v !='helpdesk' &&
                                    $v !='listgroup' && $v !='adddiscount' &&
                                    $v !='discountlist' && $v !='administrator' &&
                                    $v !='billing' && $v !='setting' &&
                                    $v !='addcontainertype' && $v !='containertypelist' &&
                                    $v !='adddepot' && $v !='depotlist' &&
                                    $v !='rddrateshipping' && $v !='rateshippinglist'){

                                    $it_arr[$v] = array("show"=>true,"onlyview"=>true);
                                }elseif($v =='administrator'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }elseif($v =='billing'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }elseif($v =='setting'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }
                                else{
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>false);
                                }
                            }
                        }
                    }
                    $data[$form_name]=$it_arr;
                }
                break;
        }
        $data['PermissionForm']=$permission_table;
        $data = json_encode(array($data));

        $g_users = explode(",",$g_users);
        $g_users = json_encode($g_users);

        $fields ="department,group_name,role,users,acl";
        $values ="'{$g_department}','{$group_name}','{$g_role}','{$g_users}','{$data}'";

        $insert = "INSERT INTO `groups`({$fields}) VALUES({$values})";

        mysqli_query($this->con,$insert);
        $g_id = mysqli_insert_id($this->con);
        if(is_numeric($g_id) && !empty($g_id)){
            return array("Save_Update"=>true,"ERROR"=>"","g_id"=>$g_id);
        }else{
            return array("Save_Update"=>false,"ERROR"=>mysqli_error($this->con),"g_id"=>"");
        }
        ///////
    }

    //-----------------------------------------------
    public function get_rule_default($g_role){
        $table = $this->getFieldsTable();
        $data = array();
        $permission_table=array();
        $data['PermissionForm']="";
        switch ($g_role){
            case level_admin:
                $permission_table['acl_form'] =array("edit"=>true);
                foreach($table['tables'] as $it){
                    $form_name = $table['tables_acl'][$it];
                    $permission_table[$form_name] =array("edit"=>true);
                    $it_arr = array();
                    if($it !="Navigation"){
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"add"=>true,"edit"=>true);
                        }
                    }else{
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"onlyview"=>true);
                        }
                    }
                    $data[$form_name]=$it_arr;
                }
                break;
            case level_manager:
                $permission_table['acl_form'] =array("edit"=>true);
                foreach($table['tables'] as $it){
                    $form_name = $table['tables_acl'][$it];
                    $permission_table[$form_name] =array("edit"=>true);
                    $it_arr = array();
                    if($it !="Navigation"){
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"add"=>true,"edit"=>true);
                        }
                    }else{
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"onlyview"=>true);
                        }
                    }
                    $data[$form_name]=$it_arr;
                }
                break;
            case level_leader:
                $permission_table['acl_form'] =array("edit"=>false);
                foreach($table['tables'] as $it){
                    $form_name = $table['tables_acl'][$it];
                    if($form_name =="SettingForm"){
                        $permission_table[$form_name] =array("edit"=>false);
                    }elseif($form_name =="ControlListForm"){
                        $permission_table[$form_name] =array("edit"=>false);
                    }else{
                        $permission_table[$form_name] =array("edit"=>true);
                    }

                    $it_arr = array();
                    if($it !="Navigation" && $it !="company"){
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"add"=>true,"edit"=>true);
                        }
                    }else{
                        if($it !="Navigation"){
                            foreach($table["table_field"][$it] as $key =>$v){
                                $it_arr[$v] = array("show"=>true,"add"=>false,"edit"=>false);
                            }
                        }else{
                            foreach($table["table_field"][$it] as $key =>$v){
                                if($v !='role' && $v !='group' &&
                                    $v !='addgroup' && $v !='helpdesk' &&
                                    $v !='listgroup' && $v !='adddiscount' &&
                                    $v !='discountlist' && $v !='administrator' &&
                                    $v !='billing' && $v !='setting' &&
                                    $v !='addcontainertype' && $v !='containertypelist' &&
                                    $v !='adddepot' && $v !='depotlist' &&
                                    $v !='rddrateshipping' && $v !='rateshippinglist'){

                                    $it_arr[$v] = array("show"=>true,"onlyview"=>true);
                                }elseif($v =='administrator'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }elseif($v =='billing'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }elseif($v =='setting'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }
                                else{
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>false);
                                }
                            }
                        }
                    }
                    $data[$form_name]=$it_arr;
                }
                break;
            default:
                $permission_table['acl_form'] =array("edit"=>false);
                foreach($table['tables'] as $it){
                    $form_name = $table['tables_acl'][$it];
                    if($form_name =="SettingForm"){
                        $permission_table[$form_name] =array("edit"=>false);
                    }elseif($form_name =="ControlListForm"){
                        $permission_table[$form_name] =array("edit"=>false);
                    }else{
                        $permission_table[$form_name] =array("edit"=>true);
                    }
                    $it_arr = array();
                    if($it !="Navigation" && $it !="company"){
                        foreach($table["table_field"][$it] as $key =>$v){
                            $it_arr[$v] = array("show"=>true,"add"=>true,"edit"=>true);
                        }
                    }else{
                        if($it !="Navigation"){
                            foreach($table["table_field"][$it] as $key =>$v){
                                $it_arr[$v] = array("show"=>true,"add"=>false,"edit"=>false);
                            }
                        }else{
                            foreach($table["table_field"][$it] as $key =>$v){
                                if($v !='role' && $v !='group' &&
                                    $v !='addgroup' && $v !='helpdesk' &&
                                    $v !='listgroup' && $v !='adddiscount' &&
                                    $v !='discountlist' && $v !='administrator' &&
                                    $v !='billing' && $v !='setting' &&
                                    $v !='addcontainertype' && $v !='containertypelist' &&
                                    $v !='adddepot' && $v !='depotlist' &&
                                    $v !='rddrateshipping' && $v !='rateshippinglist'){

                                    $it_arr[$v] = array("show"=>true,"onlyview"=>true);
                                }elseif($v =='administrator'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }elseif($v =='billing'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }elseif($v =='setting'){
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>true);
                                }
                                else{
                                    $it_arr[$v] = array("show"=>false,"onlyview"=>false);
                                }
                            }
                        }
                    }
                    $data[$form_name]=$it_arr;
                }
                break;
        }
        $data['PermissionForm']=$permission_table;
        $data = json_encode(array($data));
       return $data;
        ///////
    }

    //-----------------------------------------------
    public function get_acl_default(){
        $table = $this->getFieldsTable();
        $permission_table=array();
        $data = array();
        $data['PermissionForm']="";
        $permission_table['acl_form'] =array("edit"=>false);
        foreach($table['tables'] as $it){
            $form_name = $table['tables_acl'][$it];
            $permission_table[$form_name] =array("edit"=>false);
            $it_arr = array();
            if($it !="Navigation"){
                foreach($table["table_field"][$it] as $key =>$v){
                    $it_arr[$v] = array("show"=>false,"add"=>false,"edit"=>false);
                }
            }else{
                foreach($table["table_field"][$it] as $key =>$v){
                    $it_arr[$v] = array("show"=>false,"onlyview"=>false);
                }
            }
            $data[$form_name]=$it_arr;
        }

        $data['PermissionForm']=$permission_table;
        return $data;
    }

    //----------------------------------------------------------
    public function processACL_again($acl_temp){
        if(count($acl_temp)>1){
            $acl = array();
            $temp_0 = $acl_temp[0][0];
            for($i=1;$i<count($acl_temp);$i++){
                $diff = array_diff_key($acl_temp[$i][0],$temp_0);
                if(count($diff) >0) {
                    $temp_0 = array_merge($temp_0,$diff);
                }
            }
            foreach ($temp_0 as $t_key_0=>$t_value_0){
                for($i=1; $i<count($acl_temp);$i++){
                    $t_value_i = $acl_temp[$i][0][$t_key_0];
                    if(count($t_value_0)>0 && count($t_value_i)>0){
                        $diff = array_diff_key($t_value_i,$t_value_0);
                        if(count($diff) >0) {
                            $t_value_0 = array_merge($t_value_0,$diff);
                        }

                        foreach($t_value_0 as $k0=>$v0){
                            foreach($v0 as $v0_k=>$v0_v){
                                if(isset($t_value_i[$k0][$v0_k])){
                                    $v0[$v0_k] = $t_value_0[$k0][$v0_k] || $t_value_i[$k0][$v0_k];
                                }else{
                                    $v0[$v0_k] = $t_value_0[$k0][$v0_k];
                                }
                            }
                            $t_value_0[$k0] = $v0;
                        }
                    }elseif(count($t_value_0) == 0 && count($t_value_i)>0){
                        $t_value_0 = $t_value_i;
                    }
                }
                $acl[$t_key_0] =$t_value_0;
            }
            return $acl;
        }
    }
    //----------------------------------------------------------
    public function merge_acl($acl_update){
        $acl = array();
        $new_acl = $this->get_acl_default();
        if(count($acl_update)>0){
            //merge or delete field
            $diff = array_diff_key($new_acl,$acl_update);
            if(count($diff) >0) {
                $acl_update = array_merge($acl_update,$diff);
            }
            //delete
            $diff = array_diff_key($acl_update,$new_acl);
            if(count($diff) >0) {
                foreach($diff as $k=>$v){
                    unset($acl_update[$k]);
                }
            }

            foreach ($acl_update as $t_key_0=>$t_value_0){
                //print_r($t_key_0."---------");
                $t_value_i = $new_acl[$t_key_0];
                if(count($t_value_0)>0 && count($t_value_i)>0){
                    //find  key in a1 differently a2
                    $diff = array_diff_key($t_value_i,$t_value_0);
                    if(count($diff) >0) {
                        $t_value_0 = array_merge($t_value_0,$diff);
                    }
                    //find  key in a2 differently a1
                    $diff = array_diff_key($t_value_0,$t_value_i);
                    if(count($diff) >0) {
                        foreach($diff as $k0=>$v0){
                            unset($t_value_0[$k0]);
                        }
                    }

                }elseif(count($t_value_0) == 0 && count($t_value_i)>0){
                    $t_value_0 = $t_value_i;
                }

                $acl[$t_key_0] =$t_value_0;
            }

            return $acl;
        }else{
            return $new_acl;
        }
    }

    //----------------------------------------------------------
    public function get_acl_token($UID,$first_name,$last_name,$primary_email,$type=null){
        $config = new Config();
        $jwt_key = $config->jwt_key;
        $jwt_iss = $config->jwt_iss;
        $jwt_aud = $config->jwt_aud;
        $jwt_issuedAt = $config->jwt_issuedAt;
        $jwt_notBefore = $config->jwt_notBefore;
        $jwt_expire = $config->jwt_expire;
        //get user's role
        $roles_Q ="Select department, group_name, role,acl from `groups`
        Where department ='{$type}' AND JSON_SEARCH(`users`, 'all', '{$UID}') IS NOT NULL";

        $rlt_role = mysqli_query($this->con,$roles_Q);
        $acl_list=array(); $list =array();
        $list_acl_temp = array(); $roles =array();
        if($rlt_role){
            while ($row = mysqli_fetch_assoc($rlt_role)) {
                $list_acl_temp[] = json_decode($row["acl"],true);
                $roles[] = $row["role"];
            }
        }

        $role=level_user;
        foreach ($roles as $k=>$v){
            if($v==level_admin){
                $role = level_admin;
                $list[0]["admin"] = 1;
                break;
            }elseif($v==level_manager){
                $role = level_manager;
            }elseif($v==level_leader){
                if($role !=level_manager){
                    $role = level_leader;
                }
            }
        }
        if(count($list_acl_temp)>1){
            $acl_list=$this->merge_acl($this->processACL_again($list_acl_temp));
        }elseif(count($list_acl_temp)==1){
            $acl_list= $this->merge_acl($list_acl_temp[0][0]);
        }else{
            $query ="select acl from `groups`
              where role = 'User' and group_name='User default' limit 1";
            if($type =='SystemAdmin'){
                $query ="select acl from `groups`
              where role = 'Admin' and group_name='Admin default' limit 1";
            }
            $result = mysqli_query($this->con,$query);

            $list_acl_temp = array();
            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $list_acl_temp = json_decode($row["acl"],true);
                }
            }
            //echo "<pre>";print_r($list_acl_temp);echo "</pre>"; die();
            if(count($list_acl_temp) >0){
                $acl_list= $this->merge_acl($list_acl_temp[0]);
            }else{
                $acl_list= $this->get_acl_default();
            }
        }
        $acl_list1 = array();
        $acl_list1['int_acl']=  array
        (
            array
            (
                'unit' => $type,
                'level' => $role,
                'acl_rules' => array($acl_list),
                'group_name' => ''
            )
        );
        $list[0]["acl_list"] = $acl_list1;
        $key = base64_decode($jwt_key).$UID;
        $token = array(
            "iss" => $jwt_iss,
            "aud" => $jwt_aud,
            "iat" => $jwt_issuedAt,
            "nbf" => $jwt_notBefore,
            "exp" => $jwt_expire,
            "data" => array(
                "id" => $UID,
                "list_acl"=> array()
            )
        );
        // generate jwt
        //JWT::$leeway = 2;
        $ret = JWT::encode($token, $key,'HS512');

        $list[0]["jwt"] = $ret;
        //refresh token
        $refresh_token = array(
            "iss" => $jwt_iss,
            "aud" => $jwt_aud,
            "iat" => $jwt_issuedAt,
            "nbf" => $jwt_notBefore,
            "exp" => $jwt_expire +10*60,
            "data" => array(
                "id" => $UID,
                "list_acl"=>array()
            )
        );

        $refresh = JWT::encode($refresh_token, $key,'HS512');
        $list[0]["jwt_refresh"] = $refresh;
        unset($config);
        return $list;
    }

    //------------------------------------------------
    public function get_group_id($ID) {
        $query = "SELECT acl FROM `groups`
        where ID = '{$ID}'";
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if($row["acl"] !=null && $row["acl"] !=''){
                    $row["acl"] = json_decode($row["acl"],true);
                }
                $list = $row;
            }
        }

        $acl_list = array();
        if(count($list)>0 && isset($list['acl'][0])){
            $acl_list= $this->merge_acl($list['acl'][0]);
        }else{
            $acl_list=$this->get_acl_default();
        }

        $permission = $acl_list["PermissionForm"];
        $nav = $acl_list["Navigation"];
        $first_key = array("PermissionForm"=>$permission);
        $second_key = array("Navigation"=>$nav);
        $first_key = array_merge($first_key,$second_key);
        unset($acl_list["Navigation"]);
        unset($acl_list["PermissionForm"]);
        $acl_list = array_merge($first_key,$acl_list);
        return $acl_list;
    }
    //----------------------------------------------------------
    public function updateACL($g_id,$u_id,$acl_update){
        //check fields were permited
        $acl = array();
        foreach ($acl_update as $t_key_0=>$t_value_0){
            foreach($t_value_0 as $k0=>$v0){
                foreach($v0 as $v0_k=>$v0_v){
                    //convert "true" =>true,"false"=>false
                    if($v0_v==="true"){
                        $v0[$v0_k] = true;
                    }elseif($v0_v==="false"){
                        $v0[$v0_k] = false;
                    }
                }

                $t_value_0[$k0] = $v0;
            }
            $acl[$t_key_0] =$t_value_0;
        }

        $acl = json_encode(array($acl));
        $update ="update `groups`
                  SET  acl = '{$acl}'
                 where ID = '{$g_id}'";

        $update = mysqli_query($this->con,$update);

        if($update){
            return array("Update"=>true,"ERROR"=>"");
        }else{
            return array("Update"=>false,"ERROR"=>mysqli_error($this->con));
        }

    }
    /////////////
}