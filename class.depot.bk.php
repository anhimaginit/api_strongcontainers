<?php
require __DIR__.'/vendor/autoload.php';
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

require_once 'class.validationusphone.php';
require_once 'class.common.php';
require_once 'class.contact.php';
require_once 'class.orders.php';
//require_once 'class.employee.php';

class Depot extends Common{
    //-------------------------------
    public function get_depots($zip){
        if($zip !='') {
            $query ="SELECT * FROM `depots` WHERE depot_zip = '{$zip}'";
        } else{
            $query ="SELECT * FROM `depots`";
        }

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }

        return $list;

    }
    //-------------------------------
    public function depots_short($zip,$container_type_id){
        if($zip !='') {
            $query ="SELECT depot_id,depot_address,depot_city,depot_state,depot_zip FROM `depots_short` WHERE depot_zip = '{$zip}' GROUP BY depot_id";
            $query_short ="SELECT * FROM `depots_short` WHERE depot_zip = '{$zip}'";
            if($container_type_id !=''){
                $query .=" AND container_type_id ='{$container_type_id}'";
                $query_short .=" AND container_type_id ='{$container_type_id}'";
            }
        } else{
            //$query ="SELECT depot_id,depot_address FROM `depots_short` GROUP BY depot_id";
            $query ="SELECT DISTINCT depot_id,depot_address,depot_city,depot_state,depot_zip FROM `depots_short`";
            $query_short ="SELECT * FROM `depots_short` ";
            if($container_type_id !=''){
                $query .=" WHERE container_type_id ='{$container_type_id}'";
                $query_short .=" WHERE container_type_id ='{$container_type_id}'";
            }
            $query_short .=" ORDER BY container_type_id";
        }

        $setmode ="SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
        mysqli_query($this->con,$setmode);
        $result = mysqli_query($this->con,$query);
        $list = array();
        $return_array = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
               // $row["container_shipping"] = $this->min_rate_container_shipping($row["depot_id"]);depot_address
               $addr = '';
                if($row['depot_address'] !=null && $row['depot_address'] !=''){
                    $row['depot_address'] =trim($row['depot_address']);
                    $addr = $row['depot_address'];
                }

                if($row['depot_city'] !=null && $row['depot_city'] !=''){
                    $row['depot_city'] =trim($row['depot_city']);
                    $addr =($addr !='')?$addr.', '.$row['depot_city']:$row['depot_city'];
                }

                if($row['depot_state'] !=null && $row['depot_state'] !=''){
                    $row['depot_state'] =trim($row['depot_state']);
                    $addr =($addr !='')?$addr.', '.$row['depot_state']:$row['depot_state'];
                }
                $row['depot_address'] = $addr;
                $list[] =$row;
            }
        }
        $return_array["depots"] = $list;
        //die($query_short);
        $api_domain = $this->api_domain;
        $product_img = $this->product_img;
        $target_path_temp = $api_domain.$product_img;
        //$target_path_temp ='http://localhost/CRMAPI/'.$product_img;
        $result = mysqli_query($this->con,$query_short);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $addr = '';
                if($row['depot_address'] !=null && $row['depot_address'] !=''){
                    $row['depot_address'] =trim($row['depot_address']);
                    $addr = $row['depot_address'];
                }

                if($row['depot_city'] !=null && $row['depot_city'] !=''){
                    $row['depot_city'] =trim($row['depot_city']);
                    $addr =($addr !='')?$addr.', '.$row['depot_city']:$row['depot_city'];
                }

                if($row['depot_state'] !=null && $row['depot_state'] !=''){
                    $row['depot_state'] =trim($row['depot_state']);
                    $addr =($addr !='')?$addr.', '.$row['depot_state']:$row['depot_state'];
                }
                $row['depot_address'] = $addr;
                if($row['prod_photo'] !=null && $row['prod_photo'] !=''){
                    $row['prod_photo'] = $target_path_temp.$row['prod_photo'];
                }
                $list[] =$row;
            }
        }
        $return_array["depots_short"] = $list;
        $return_array['ERROR']='';
        return $return_array;

    }
    //-------------------------------
    public function rate_container_shipping_vendor_short($depot_id){
        $query ="SELECT * FROM `rate_container_shipping_vendor_short`
        WHERE depot_id = '{$depot_id}'";
        $result = mysqli_query($this->con,$query);
        $list = array();
        $return_array = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }

        return $list;

    }
    //-------------------------------
    public function min_rate_container_shipping($depot_id){
      $query =" select * from rate_container_shipping_vendor_short as r1
      where  r1.depot_id = '{$depot_id}' AND (r1.rate_mile + r1.rate_container_rate) =  (
      select min(r2.rate_mile + r2.rate_container_rate)
				from rate_container_shipping_vendor_short as r2
          where r1.container_type_id=r2.container_type_id AND
             r1.depot_id = r2.depot_id AND r2.depot_id = '{$depot_id}'
		)";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }

        return $list;

    }
    //-------------------------------
    public function getDepots()
    {
        $sqlText = "Select * From depots";
        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //-------------------------------
    public function getContainerTypes()
    {
        $sqlText = "Select * From container_type";
        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //-------------------------------
    public function add_quote_temp($email,$phone, $data){
        $query = "SELECT MAX(quote_temp_id) as max_id  FROM `quote_temp` LIMIT 1";
        $result = mysqli_query($this->con,$query);
        $max_id =1;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $max_id = $row["max_id"] +1;
            }
        }

        //create quote_temp
        $code = '';
        $values ="";
        if($email !=''){
            $code =base64_encode($email.$max_id);
            $email = trim($email);
            $values ="'{$code}','{$email}'";
        }elseif($phone !=''){
            $code =base64_encode($phone.$max_id);
            $values ="'{$code}','{$phone}'";
        }elseif(empty($email) && empty($phone)) return array("error"=>"Email and Phone is not empty","quote_temp_id"=>'');

        if(count($data) <1) return array("error"=>"Data require","quote_temp_id"=>'');


        $values .=",'New'";
        $fields ="code,email_phone,status";
        $query = "INSERT INTO `quote_temp` ({$fields}) VALUES({$values})";
        //die($query);
        mysqli_query($this->con,$query);
        $quote_temp_id = mysqli_insert_id($this->con);
        //create quote_temp_data
        if(is_numeric($quote_temp_id) && !empty($quote_temp_id)){
            $fields = ""; $data_insert='';
            $i=0;
            foreach ($data as $item){
                $values="";
                foreach ($item as $k=>$v) {
                    $v_i = trim($this->protect($v));
                    if($k !='depot_id' && $k !='best_price' && $k !='container_rate' &&
                         $k !='rate_mile' && $k !='vendor_id' &&
                        $k !='qty'){
                        if($i==0){
                            $fields =($fields=="")? $k:$fields.",".$k;
                        }
                        $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";
                    }else{
                        if(!is_numeric($v)) {$v =0;}
                        if($i==0){
                            $fields =($fields=="")? $k:$fields.",".$k;
                        }

                        $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";

                    }
                }
                $values .=",'{$quote_temp_id}'";
                $data_insert = ($data_insert=='')? "VALUES ({$values})":$data_insert.",({$values})";

                $i++;
            }

            if($fields !=''){
                $fields .=",quote_temp_id";
                $insert = "INSERT INTO quote_data_temp ({$fields}) {$data_insert}";
               // die($insert);
                mysqli_query($this->con,$insert);
                return array("error"=>mysqli_error($this->con),
                    "quote_temp_id"=>$quote_temp_id,"code"=>$code);
            }
            ////////////////////
        }else{
            return array("error"=>mysqli_error($this->con),"quote_temp_id"=>'');
        }
    }

    //-------------------------------
    public function add_quote_temp_direct($contact, $data,$order_id=null){
        $query = "SELECT MAX(quote_temp_id) as max_id  FROM `quote_temp` LIMIT 1";
        $result = mysqli_query($this->con,$query);
        $max_id =1;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $max_id = $row["max_id"] +1;
            }
        }

        //create quote_temp
        $code = '';
        $values ="";
        if($contact['primary_email'] !=''){
            $code =base64_encode($contact['primary_email'].$max_id);
            $email = trim($contact['primary_email']);
            $values ="'{$code}','{$email}'";
        }elseif($contact['primary_phone'] !=''){
            $code =base64_encode($contact['primary_phone'].$max_id);
            $values ="'{$code}','{$contact['primary_phone']}'";
        }elseif(empty($email) && empty($phone)) return array("error"=>"Email and Phone is not empty","quote_temp_id"=>'');

        if(count($data) <1) return array("error"=>"Data require","quote_temp_id"=>'');


        $values .=",'Open'";
        $fields ="code,email_phone,status";
        $update ='';
        if($contact['primary_phone'] !=''){
            $fields .=",shipping_phone";
            $values .=",'{$contact['primary_phone']}'";
            $update .="shipping_phone = '{$contact['primary_phone']}'";
        }
        if($contact['primary_street_address1'] !=''){
            $fields .=",shipping_address";
            $values .=",'{$contact['primary_street_address1']}'";
            $update .=",shipping_address = '{$contact['primary_street_address1']}'";
        }
        if($contact['primary_city'] !=''){
            $fields .=",shipping_city";
            $values .=",'{$contact['primary_city']}'";
            $update .=",shipping_city = '{$contact['primary_city']}'";
        }
        if($contact['primary_postal_code'] !=''){
            $fields .=",shipping_zip";
            $values .=",'{$contact['primary_postal_code']}'";
            $update .=",shipping_zip = '{$contact['primary_postal_code']}'";
        }
        if($contact['primary_state'] !=''){
            $fields .=",shipping_state";
            $values .=",'{$contact['primary_state']}'";
            $update .=",shipping_state = '{$contact['primary_state']}'";
        }
        $customer_name = $contact['first_name'].' '.$contact['middle_name'].' '.$contact['last_name'];
        if($customer_name !=''){
            $fields .=",shipping_customer_name";
            $values .=",'{$customer_name}'";
            $update .=",shipping_customer_name = '{$customer_name}'";
        }
        if($contact['ID'] !=''){
            $fields .=",shipping_contact_id";
            $values .=",'{$contact['ID']}'";
            $update .=",shipping_contact_id = '{$contact['ID']}'";
        }
        $quote_temp_id ='';
        if($order_id !='' && $order_id !=null){
            $quote_temp_id  = $this->return_id("SELECT quote_temp_id FROM `quote` WHERE order_id ='{$order_id}'","quote_temp_id");
        }
        if($quote_temp_id !=''){
            $code  = $this->return_id("SELECT code  FROM `quote_temp` WHERE quote_temp_id ='{$quote_temp_id}'","code");
            $this->update_data_quote_temp_by_code($code,$data);

            //shipping info
            $query = "UPDATE `quote_temp`
               SET ".$update." WHERE quote_temp_id ='{$quote_temp_id}'";
            $update = mysqli_query($this->con,$query);
            if($update){
                return array("error"=>mysqli_error($this->con),
                    "quote_temp_id"=>$quote_temp_id,"code"=>$code);
            }else{
                return array("error"=>mysqli_error($this->con),
                    "quote_temp_id"=>'',"code"=>'');
            }
        }else{
            $query = "INSERT INTO `quote_temp` ({$fields}) VALUES({$values})";
            mysqli_query($this->con,$query);
            $quote_temp_id = mysqli_insert_id($this->con);
            //create quote_temp_data
            if(is_numeric($quote_temp_id) && !empty($quote_temp_id)){
                $fields = ""; $data_insert='';
                $i=0;
                foreach ($data as $item){
                    $values="";
                    foreach ($item as $k=>$v) {
                        $v_i = trim($this->protect($v));
                        if($k !='depot_id' && $k !='best_price' && $k !='container_rate' &&
                            $k !='rate_mile' && $k !='vendor_id' &&
                            $k !='qty'){
                            if($i==0){
                                $fields =($fields=="")? $k:$fields.",".$k;
                            }
                            $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";
                        }else{
                            if(!is_numeric($v)) {$v =0;}
                            if($i==0){
                                $fields =($fields=="")? $k:$fields.",".$k;
                            }

                            $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";

                        }
                    }
                    $values .=",'{$quote_temp_id}'";
                    $data_insert = ($data_insert=='')? "VALUES ({$values})":$data_insert.",({$values})";

                    $i++;
                }

                if($fields !=''){
                    $fields .=",quote_temp_id";
                    $insert = "INSERT INTO quote_data_temp ({$fields}) {$data_insert}";
                    mysqli_query($this->con,$insert);
                    return array("error"=>mysqli_error($this->con),
                        "quote_temp_id"=>$quote_temp_id,"code"=>$code);
                }
                ////////////////////
            }else{
                return array("error"=>mysqli_error($this->con),"quote_temp_id"=>'','quote_temp_data_id'=>'');
            }
        }
    }
    //-------------------------------
    public function update_data_quote_temp_by_code($code,$data){
        if(empty($code)) return array("error"=>"Invalid code","quote_temp_id"=>"");
        $query = "SELECT `quote_temp_id` FROM `quote_temp` WHERE code ='{$code}' LIMIT 1";
        $quote_temp_id =  $this->return_id($query, "quote_temp_id");
        if($quote_temp_id !=''){
            mysqli_query($this->con,"DELETE FROM quote_data_temp WHERE quote_temp_id ='{$quote_temp_id}'");
        }
        //create quote_temp_data
        if(is_numeric($quote_temp_id) && !empty($quote_temp_id)){
            $fields = ""; $data_insert='';
            $i=0;
            foreach ($data as $item){
                $values="";
                foreach ($item as $k=>$v) {
                    $v_i = trim($this->protect($v));
                    if($k !='depot_id' && $k !='best_price' && $k !='container_rate' &&
                        $k !='rate_mile' && $k !='vendor_id' &&
                        $k !='qty' && $k !='distance'){
                        if($i==0){
                            $fields =($fields=="")? $k:$fields.",".$k;
                        }

                        $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";
                    }else{
                        if(!is_numeric($v)) {$v =0;}
                        if($i==0){
                            $fields =($fields=="")? $k:$fields.",".$k;
                        }

                       // $v_i = $v;
                        $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";

                    }
                }
                $values .=",'{$quote_temp_id}'";
                $data_insert = ($data_insert=='')? "VALUES ({$values})":$data_insert.",({$values})";

                $i++;
            }

            if($fields !=''){
                $fields .=",quote_temp_id";
                $insert = "INSERT INTO quote_data_temp ({$fields}) {$data_insert}";
                 //die($insert);
                mysqli_query($this->con,$insert);
                return array("error"=>mysqli_error($this->con),
                    "quote_temp_id"=>$quote_temp_id,"code"=>$code);
            }
            ////////////////////
        }else{
            return array("error"=>mysqli_error($this->con),"quote_data_temp_id"=>'');
        }
    }
    //-------------------------------
    public function getQuoteTemp($code)
    {
        $q_status = $this->return_id("SELECT status FROM quote_temp WHERE code ='{$code}'","status");

        $sqlText = "Select * From `quote_temp_short` where code ='{$code}' AND active=1 AND status ='{$q_status}'";
        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return array("error"=>"","status"=>$q_status,"quotes"=>$list);
    }
    //-------------------------------
    public function quoteTemp($code)
    {
        $sqlText = "Select * From `quote_temp_order_short` where code ='{$code}' AND active=1";
        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }
        return $list;
    }

    //-------------------------------
    public function getQuote($code)
    {
        $sqlText = "Select * From `quote_temp` where code ='{$code}' AND active =1 AND Status='New'";
        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //-------------------------------
    public function saveCustomerInfo($first_name,$primary_street_address1,
                                     $primary_city,$primary_state,$primary_postal_code,$primary_phone,$code,
                                     $tran_id,$payment_type,$amount){

        if(empty($primary_phone)) return "Phone is required";

        $data_quote = $this->getQuote($code);
        if(count($data_quote) < 1) {
            return array("order_id"=>"","error"=>"can't create Order");
        }
        //quote_temp_id
        $quote_temp_id = $data_quote[0]["quote_temp_id"];
        //email
        $primary_email ='';
        if(count($data_quote) >0 ){
            $phone_email_temp = $data_quote[0]["email_phone"];
            if (filter_var($phone_email_temp, FILTER_VALIDATE_EMAIL)){
                $primary_email = $phone_email_temp;
            }
        }
        //echo "<pre> e= ";print_r($primary_email);   echo "</pre>"; die();
        $data_quote_temp = $this->getQuoteTemp($code);
        //total
        $total =0; $prod_ids ='';
        foreach($data_quote_temp["quotes"] as $item){
            $total += $item["best_price"] * $item["qty"];
            $prod_ids =($prod_ids =='')? $item["prod_id"]:$prod_ids.",".$item["prod_id"];
        }
        if(empty($prod_ids)) return array("quote_temp_id"=>"","order_id"=>"","error"=>"can't create shipping");
        $products_temp = $this->get_products_prods($prod_ids);
        $is_in_arr =array();
        $products =array();
        foreach($data_quote_temp["quotes"] as $item1){
            foreach($products_temp as $item2){
                if($item1["prod_id"]==$item2["ID"] && !in_array($item1["prod_id"],$is_in_arr)){
                    $item3 =array();
                    $item3["id"]=$item2["ID"];
                    $item3["sku"]=$item2["SKU"];
                    $item3["prod_name"]=$item2["prod_name"];
                    $item3["prod_class"]=$item2["prod_class"];
                    $item3["price"] = $item1["best_price"];
                    $item3["discount"] =0;
                    $item3["discount_type"] ="$";
                    $item3["line_total"] =$item1["best_price"] * $item1["qty"];
                    $item3["quantity"] = $item1["qty"];

                    array_push($is_in_arr,$item1["prod_id"]);
                    array_push($products,$item3);
                }
            }
        }

        $company_name =0;
        $contact_inactive = 0;
        $contact_notes = '';
        $contact_tags = '';
        $contact_type = 'Customer';
        $last_name = '';
        $middle_name = '';
        $primary_phone_ext = '';
        $primary_phone_type = '';
        $primary_street_address2 = '';
        $primary_website = '';
        $aff_type = '';
        $user_name = '';
        $password = '';
        $notes = '';
        $create_by = '';
        $submit_by = '';
        $gps = '';

        $add_contact = new Contact();
        $return =  $add_contact->addContact($company_name,$contact_inactive,$contact_notes,$contact_tags,$contact_type,
            $first_name,$last_name,$middle_name,$primary_city,$primary_email,
            $primary_phone,$primary_phone_ext,$primary_phone_type,$primary_postal_code,
            $primary_state, $primary_street_address1,$primary_street_address2,$primary_website,$aff_type,
            $user_name,$password,$notes,$create_by,$submit_by,$gps);
          //echo "<pre>";print_r($return);   echo "</pre>"; die();
        $contact_id = '';
        if($return["ID"]=="The phone and email are used"){
            $contact_id = $return["contact_duplicated"][0]["ID"];
        }else{
            if(is_numeric($return["ID"]) && !empty($return["ID"])){
                $contact_id = $return["ID"];
            }else{
                return array("quote_temp_id"=>"","order_id"=>"","error"=>mysqli_error($this->con));
            }
        }
        //print_r($return);
        $query = "UPDATE `quote_temp`
                SET active = '1',
                status ='Open',
                shipping_address = '{$primary_street_address1}',
                shipping_city = '{$primary_city}',
                shipping_zip = '{$primary_postal_code}',
                shipping_state = '{$primary_state}',
                shipping_phone = '{$primary_phone}',
                shipping_customer_name = '{$first_name}'";

        if($contact_id !='') {
            $query .= ",shipping_contact_id = {$contact_id}";
        }

        $query .= " WHERE code ='{$code}'";

        mysqli_query($this->con,$query);

        if($amount =='') $amount =0;
        $amount = $total;  //hard code
        $payment = $amount;
        $balance =0;
        if($payment_type !="COD"){
           // $payment = $total - $balance;
        }
        $contract_overage =$payment - $total;
        if($contract_overage <0) $contract_overage =0;
        $order_title = "ST-Order-".date('Y').$this->get_max_id("SELECT MAX(order_id) as max_id  FROM `quote` LIMIT 1","max_id");
        $discount_code ='';

        //$query = "SELECT `ID` FROM `contact` WHERE company_name ='{$vendor_id}' LIMIT 1";
        //$sale_id =  $this->return_id($query, "ID");
        $sale_id ='';
        $notes = array();
        $this_order = new Orders();
        $ret = $this_order->newOrderStrongContainer($products,$balance,$contact_id,"",$payment,
            $sale_id,
            $total,"",$notes,$order_title,
            $contract_overage,$discount_code,$quote_temp_id,$payment_type,$tran_id);

        //send email to customer
        //$primary_email
        $ret["quote_temp_id"] =$quote_temp_id;
        $ret["order-title"] =$order_title;
        $ret["error"] ='';
        return $ret;
    }

    //-------------------------------
    public function get_products_prods($prods){
        $query ="SELECT `ID`,`SKU`,`prod_name`,`prod_class`
        FROM `products`
        WHERE ID IN ($prods)";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }

        return $list;

    }

    //-------------------------------
    public function new_update_depot($depot_id, $data){
        if(empty($depot_id)){
            $fields = ""; $values="";
            foreach ($data as $k=>$v) {
                if($k !='depot_id '){
                    $fields =($fields=="")? $k:$fields.",".$k;
                    $v_i = $this->protect($v);
                    $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";
                }
            }
            if($fields !=''){
                $insert = "INSERT INTO depots({$fields}) VALUES({$values})";
                //die($insert);
                mysqli_query($this->con,$insert);
                $depot_id = mysqli_insert_id($this->con);

                if(is_numeric($depot_id) && !empty($depot_id)){
                    return array("Save_Update"=>true,"ERROR"=>"","depot_id"=>$depot_id);
                }else{
                    return array("Save_Update"=>false,"ERROR"=>mysqli_error($this->con),"depot_id"=>"");
                }
            }else{
                return array("Save_Update"=>false,"ERROR"=>"No data for create new depot", "depot_id"=>"");
            }

        }else{
            $updateCommand = "";
            foreach ($data as $k=>$v) {
                if($k !='depot_id'){
                    $v_i = $this->protect($v);
                    $updateCommand = ($updateCommand=="")?$k."='{$v_i}'":$updateCommand.", ".$k."='{$v_i}'";
                }
            }
            if($updateCommand !=""){
                $query_update = "UPDATE `depots`
                SET ".$updateCommand." where depot_id = '{$depot_id}'";
                //die($query_update);
                $update = mysqli_query($this->con,$query_update);

                if($update){
                    return array("Save_Update"=>true,"ERROR"=>"");
                }else{
                    return array("Save_Update"=>false,"ERROR"=>mysqli_error($this->con));
                }
            }else{
                return array("Save_Update"=>false,"ERROR"=>"Can't update the depot","depot_id"=>$depot_id);
            }
            ///////////
        }
    }

    //-------------------------------
    public function new_update_container_type($container_type_id, $data){
        if(empty($container_type_id)){
            $fields = ""; $values="";
            foreach ($data as $k=>$v) {
                if($k !='container_type_id'){
                    $fields =($fields=="")? $k:$fields.",".$k;
                    $v_i = $this->protect($v);
                    $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";
                }
            }
            if($fields !=''){
                $insert = "INSERT INTO container_type({$fields}) VALUES({$values})";
                //die($insert);
                mysqli_query($this->con,$insert);
                $container_type_id = mysqli_insert_id($this->con);

                if(is_numeric($container_type_id) && !empty($container_type_id)){
                    return array("Save_Update"=>true,"ERROR"=>"","container_type_id"=>$container_type_id);
                }else{
                    return array("Save_Update"=>false,"ERROR"=>mysqli_error($this->con),"container_type_id"=>"");
                }
            }else{
                return array("Save_Update"=>false,"ERROR"=>"No data for create new container_type", "container_type_id"=>"");
            }

        }else{
            $updateCommand = "";
            foreach ($data as $k=>$v) {
                if($k !='container_type_id'){
                    $v_i = $this->protect($v);
                    $updateCommand = ($updateCommand=="")?$k."='{$v_i}'":$updateCommand.", ".$k."='{$v_i}'";
                }
            }
            if($updateCommand !=""){
                $query_update = "UPDATE `container_type`
                SET ".$updateCommand." where container_type_id = '{$container_type_id}'";
                //die($query_update);
                $update = mysqli_query($this->con,$query_update);

                if($update){
                    return array("Save_Update"=>true,"ERROR"=>"");
                }else{
                    return array("Save_Update"=>false,"ERROR"=>mysqli_error($this->con));
                }
            }else{
                return array("Save_Update"=>false,"ERROR"=>"Can't update the container_type","container_type_id"=>$container_type_id);
            }
            ///////////
        }
    }

    //-------------------------------
    public function new_update_rate_container($rate_container_id, $data){
        if(empty($rate_container_id)){
            $fields = ""; $values="";
            foreach ($data as $k=>$v) {
                if($k !='rate_container_id'){
                    if($k =='container_rate') {
                        if(!is_numeric($v)) $v =0;
                    }

                    $fields =($fields=="")? $k:$fields.",".$k;
                    $v_i = $this->protect($v);
                    $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";
                }
            }
            if($fields !=''){
                $insert = "INSERT INTO rate_container({$fields}) VALUES({$values})";
                //die($insert);
                mysqli_query($this->con,$insert);
                $rate_container_id = mysqli_insert_id($this->con);

                if(is_numeric($rate_container_id) && !empty($rate_container_id)){
                    return array("Save_Update"=>true,"ERROR"=>"","rate_container_id"=>$rate_container_id);
                }else{
                    return array("Save_Update"=>false,"ERROR"=>mysqli_error($this->con),"rate_container_id"=>"");
                }
            }else{
                return array("Save_Update"=>false,"ERROR"=>"No data for create new rate_container", "rate_container_id"=>"");
            }

        }else{
            $updateCommand = "";
            foreach ($data as $k=>$v) {
                if($k !='rate_container_id'){
                    if($k =='container_rate') {
                        if(! is_numeric($v)) $v =0;
                    }

                    $v_i = $this->protect($v);
                    $updateCommand = ($updateCommand=="")?$k."='{$v_i}'":$updateCommand.", ".$k."='{$v_i}'";
                }
            }
            if($updateCommand !=""){
                $query_update = "UPDATE `rate_container`
                SET ".$updateCommand." where rate_container_id = '{$rate_container_id}'";
                //die($query_update);
                $update = mysqli_query($this->con,$query_update);

                if($update){
                    return array("Save_Update"=>true,"ERROR"=>"");
                }else{
                    return array("Save_Update"=>false,"ERROR"=>mysqli_error($this->con));
                }
            }else{
                return array("Save_Update"=>false,"ERROR"=>"Can't update the rate_container","rate_container_id"=>$rate_container_id);
            }
            ///////////
        }
    }

    //-------------------------------
    public function new_update_rate_shipping($rate_shipping_id, $data){
        if(empty($rate_shipping_id)){
            $fields = ""; $values="";
            foreach ($data as $k=>$v) {
                if($k !='rate_shipping_id'){
                    if($k =='rate_mile') {
                        if(!is_numeric($v)) $v =0;
                    }

                    $fields =($fields=="")? $k:$fields.",".$k;
                    $v_i = $this->protect($v);
                    $values =($values=="")? "'{$v_i}'":$values.", '{$v_i}'";
                }
            }
            if($fields !=''){
                $insert = "INSERT INTO rate_shipping({$fields}) VALUES({$values})";
                //die($insert);
                mysqli_query($this->con,$insert);
                $rate_shipping_id = mysqli_insert_id($this->con);

                if(is_numeric($rate_shipping_id) && !empty($rate_shipping_id)){
                    return array("Save_Update"=>true,"ERROR"=>"","rate_container_id"=>$rate_shipping_id);
                }else{
                    return array("Save_Update"=>false,"ERROR"=>mysqli_error($this->con),"rate_shipping_id"=>"");
                }
            }else{
                return array("Save_Update"=>false,"ERROR"=>"No data for create new rate_shipping", "rate_shipping_id"=>"");
            }

        }else{
            $updateCommand = "";
            foreach ($data as $k=>$v) {
                if($k !='rate_shipping_id'){
                    if($k =='rate_mile') {
                        if(! is_numeric($v)) $v =0;
                    }

                    $v_i = $this->protect($v);
                    $updateCommand = ($updateCommand=="")?$k."='{$v_i}'":$updateCommand.", ".$k."='{$v_i}'";
                }
            }
            if($updateCommand !=""){
                $query_update = "UPDATE `rate_shipping`
                SET ".$updateCommand." where rate_shipping_id = '{$rate_shipping_id}'";
                //die($query_update);
                $update = mysqli_query($this->con,$query_update);

                if($update){
                    return array("Save_Update"=>true,"ERROR"=>"");
                }else{
                    return array("Save_Update"=>false,"ERROR"=>mysqli_error($this->con));
                }
            }else{
                return array("Save_Update"=>false,"ERROR"=>"Can't update the rate_shipping","rate_shipping_id"=>$rate_shipping_id);
            }
            ///////////
        }
    }

    //-------------------------------
    public function search_depot($limit,$cursor,$text_search){
        $query ="SELECT *
        FROM `depots`";

        $where =" WHERE ";
        if($text_search !=''){
            $query .= $where."depot_name like '%{$text_search}%' OR
            depot_city like '%{$text_search}%' OR
            depot_state like '%{$text_search}%' OR
            depot_zip like '%{$text_search}%' OR
            depot_phone like '%{$text_search}%'";
            $where =" AND ";
        }
        $query_count = $query;

        $query .= " order by depot_name DESC";
        if($limit !=''){
            $query.= " LIMIT {$limit} ";
        }
        if($cursor !=''){
            $query.= " OFFSET {$cursor} ";
        }

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }

        $row_cnt =0;
        if($cursor ==0){
            $result = mysqli_query($this->con,$query_count);
            //die($query);
            $row_cnt = mysqli_num_rows($result);
        }else{
            $row_cnt =0;
        }

        return array("depots"=>$list,"row_cnt"=>$row_cnt);

        return $list;

    }
    //-------------------------------
    public function search_container_type($limit,$cursor,$text_search){
        $query ="SELECT *
        FROM `container_type`";

        $where =" WHERE ";
        if($text_search !=''){
            $query .= $where."container_type_name like '%{$text_search}%' ";
            $where =" AND ";
        }
        $query_count = $query;

        $query .= " order by container_type_name DESC";
        if($limit !=''){
            $query.= " LIMIT {$limit} ";
        }
        if($cursor !=''){
            $query.= " OFFSET {$cursor} ";
        }

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }

        $row_cnt =0;
        if($cursor ==0){
            $result = mysqli_query($this->con,$query_count);
            //die($query);
            $row_cnt = mysqli_num_rows($result);
        }else{
            $row_cnt =0;
        }

        return array("container_types"=>$list,"row_cnt"=>$row_cnt);

        return $list;

    }

    //-------------------------------
    public function search_rate_container($limit,$cursor,$text_search){
        $query ="SELECT *
        FROM `rate_container_short`";

        $where =" WHERE ";
        if($text_search !=''){
            $query .= $where."depot_name like '%{$text_search}%' OR
            company_name like '%{$text_search}%' OR
            container_type_name like '%{$text_search}%'";
            $where =" AND ";
        }
        $query_count = $query;

        $query .= " order by rate_container_id DESC";
        if($limit !=''){
            $query.= " LIMIT {$limit} ";
        }
        if($cursor !=''){
            $query.= " OFFSET {$cursor} ";
        }

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }

        $row_cnt =0;
        if($cursor ==0){
            $result = mysqli_query($this->con,$query_count);
            //die($query);
            $row_cnt = mysqli_num_rows($result);
        }else{
            $row_cnt =0;
        }

        return array("rate_containers"=>$list,"row_cnt"=>$row_cnt);

        return $list;

    }

    //-------------------------------
    public function search_rate_shipping($limit,$cursor,$text_search){
        $query ="SELECT *
        FROM `rate_shipping_short`";

        $where =" WHERE ";
        if($text_search !=''){
            $query .= $where."depot_name like '%{$text_search}%' OR
            company_name like '%{$text_search}%' ";
            $where =" AND ";
        }
        $query_count = $query;

        $query .= " order by rate_shipping_id DESC";
        if($limit !=''){
            $query.= " LIMIT {$limit} ";
        }
        if($cursor !=''){
            $query.= " OFFSET {$cursor} ";
        }

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] =$row;
            }
        }

        $row_cnt =0;
        if($cursor ==0){
            $result = mysqli_query($this->con,$query_count);
            //die($query);
            $row_cnt = mysqli_num_rows($result);
        }else{
            $row_cnt =0;
        }

        return array("rate_shippings"=>$list,"row_cnt"=>$row_cnt);

        return $list;

    }

    //-------------------------------
    public function get_depot_id($depot_id){
        $query ="SELECT *
        FROM `depots` WHERE depot_id ='{$depot_id}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }
    //-------------------------------
    public function get_container_type_id($container_type_id){
        $query ="SELECT *
        FROM `container_type` WHERE container_type_id  ='{$container_type_id}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }
    //-------------------------------
    public function get_rate_shipping_id($rate_shipping_id){
        $query ="SELECT *
        FROM `rate_shipping_short` WHERE rate_shipping_id  ='{$rate_shipping_id}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }

    //-------------------------------
    public function get_rate_container_id($rate_container_id){
        $query ="SELECT *
        FROM `rate_container_short` WHERE rate_container_id  ='{$rate_container_id}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }
    //-------------------------------
    public function calulate_driver_rate($order_id,$prod_sku,$contact_id){
        $query ="SELECT distance
        FROM `quote_temp_date_order_short` WHERE order_id  ='{$order_id}' AND
        prod_SKU  ='{$prod_sku}' limit 1";
        $result = mysqli_query($this->con,$query);
        $distance = 0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $distance =$row['distance'];
            }
        }
        //driver
        $query ="SELECT driver_rate,driver_min_rate
        FROM `driver` WHERE contact_id   ='{$contact_id }' limit 1";
        $result = mysqli_query($this->con,$query);
        $driver_rate = 0; $driver_min_rate = 0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $driver_rate =$row['driver_rate'];
                $driver_min_rate =$row['driver_min_rate'];
            }
        }

        $total =0;
        if($driver_rate !='' && $driver_rate !=null &&
            $distance !='' && $distance !=null){
            $distance = str_replace(',','.',$distance);
            $driver_rate = str_replace(',','.',$driver_rate);
            $total = $distance * $driver_rate;
            if($total < $driver_min_rate) $total = $driver_min_rate;
        }
       // echo "<pre>";print_r($driver_min_rate);echo "</pre>"; die();
        return $total;
    }

    public function getQuoteTemp_qt_id($quote_temp_id)
    {
        $sqlText = "Select * From `quote_temp_short` where quote_temp_id ='{$quote_temp_id}' AND active=1";
        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //////////////////////////////////
 }