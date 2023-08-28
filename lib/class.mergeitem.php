<?php
require_once 'class.common.php';
require_once 'class.warranty.php';
class Mergeitem extends Common{
    //------------------------------------------------------------
    public function getContactGroup($groupby){
        $list=array();
        if(!empty($groupby)){
            $query ="select {$groupby}
                    from contact
                    where  {$groupby} <> '' and {$groupby} is not null and
                     contact_inactive =0
                    Group by {$groupby}
                    having count(distinct ID)> 1";

            $result = mysqli_query($this->con,$query);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $list[]= $this->getcontactGrp_emailorPhone($groupby,$row[$groupby]);
                }
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function getcontactGrp_emailorPhone($groupby,$value){
        $query ="select ID, first_name, middle_name, last_name, primary_email, primary_phone,contact_type
           from contact
           where {$groupby} ='{$value}' and
            {$groupby} <> '' and
            {$groupby} is not null and
            contact_inactive =0";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[]= $row;
            }
        }
        return $list;

    }
    //------------------------------------------------------------
    public function mergeContacts($data){
        $rturn=array();
        if(count($data>0)){
            //example is 1 record
            $obW = new Warranty();
            foreach($data as $item){
                $contact_keep = $item['contact_keep'];
                if(is_numeric($contact_keep)&&!empty($contact_keep)){
                    $contact_was_merged = $item['contact_was_merged'];
                    $contact_was_m =implode(",",$item['contact_was_merged']);
                    //merge contact
                    foreach($contact_was_merged as $conID){
                        $list = $this->getcontactpropeties_ID($conID);

                        if(count($list)>0){
                            $contactType =explode(",",$list['contact_type']);
                            foreach($contactType as $ctype){
                                if($ctype=='Affiliate'){
                                    $obW->updateContactType($contact_keep,"Affiliate");
                                    $rturn[]= $this->mergeUpdateA_type($list['aff_type'],$contact_keep,1);
                                }

                                if($ctype=='Sales'){
                                    $obW->updateContactType($contact_keep,"Sales");
                                    $rturn[]= $this->mergeUpdateSale($list['area'],$contact_keep,1);
                                }

                                if($ctype=='Vendor'){
                                    $obW->updateContactType($contact_keep,"Vendor");
                                    $rturn[]= $this->mergeUpdateV_type($list['V_type'],$contact_keep,1);
                                }
                                if($ctype=='Employee'){
                                    $obW->updateContactType($contact_keep,"Employee");
                                }
                            }
                        }
                    }

                    //set inactive
                    if(!empty($contact_was_m)){
                        $this->ContactMergedToInactive($contact_was_m);
                    }

                }

            }

            unset($obW);
            //------merge contact
        }
        return $rturn;
    }

    //------------------------------------------------------------
    public function getcontactpropeties_ID($contactID){
        $query ="select c.ID,c.contact_type,
           aff.aff_type,aff.AID,
           s.SID,s.area,
           v.V_type
        from contact as c
        left join affiliate as aff on aff.UID = c.ID
        left join salesman as s on s.UID = c.ID
        left join vendor_type as v on v.UID = c.ID
        where c.ID='{$contactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function getSalesArea_ContactID($ContactID){

        $query = "SELECT area,SID from salesman
          WHERE UID ='{$ContactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['area'] =json_decode($row['area'],true);
                $list =$row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function mergeUpdateSale($area,$contactID_k,$active)
    {
        //$info_wc = $this->getSalesArea_ContactID($contactID);
        $info_wc_area=array();
        if(!empty($area)) $info_wc_area=json_decode($area);

        //if(isset($info_wc['area'])) $info_wc_area = $info_wc['area'];

        $info_k = $this->getSalesArea_ContactID($contactID_k);
        $info_k_area=array();
        if(isset($info_k['area'])) $info_k_area = $info_k['area'];

        $info_d = array_diff($info_wc_area,$info_k_area);

        if(count($info_k)>0){
            if(count($info_d)>0){
                if(count($info_k_area)>0) $info_d = array_merge($info_d,$info_k_area);

                $info_d = json_encode($info_d);
                $updateaffiliate = "UPDATE `salesman`
                SET active = '{$active}',
                    area = '{$info_d}'
                WHERE UID = '{$contactID_k}'";
                $rsl=mysqli_query($this->con,$updateaffiliate);
                if($rsl){
                    return 1;
                }else{
                    return mysqli_error($this->con);
                }
            }else{
                $updateaffiliate = "UPDATE `salesman`
                SET active = '1'
                WHERE UID = '{$contactID_k}'";
                $rsl=mysqli_query($this->con,$updateaffiliate);
                if($rsl){
                    return 1;
                }else{
                    return mysqli_error($this->con);
                }
            }
        }else{
            if(count($info_d)>0){
                if(count($info_k_area)>0) $info_d = array_merge($info_d,$info_k_area);
                $info_d = json_encode($info_d);
                $fields = "area,UID,active";
                $values = "'{$info_d}','{$contactID_k}',1";
                $insert = "INSERT INTO salesman({$fields}) VALUES({$values})";
                mysqli_query($this->con,$insert);
                $idreturn = mysqli_insert_id($this->con);
                return $idreturn;
            }else{
                $fields = "area,UID,active";
                $values = "'[]','{$contactID_k}',1";
                $insert = "INSERT INTO salesman({$fields}) VALUES({$values})";
                mysqli_query($this->con,$insert);
                $idreturn = mysqli_insert_id($this->con);
                return $idreturn;
            }
        }

    }

    //----------------------------------------------------------
    public function getV_type_ContactID($ContactID){

        $query = "SELECT V_type,VID from vendor_type
          WHERE UID ='{$ContactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['V_type'] =explode(',',$row['V_type']);
                $list =$row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function mergeUpdateV_type($V_type,$contactID_k,$active)
    {
        //$info_wc = $this->getV_type_ContactID($contactID);
        $info_wc_V_type=array();
        if(!empty($V_type)) $info_wc_V_type =explode(',',$V_type);
        //if(isset($info_wc['V_type'])) $info_wc_V_type = $info_wc['V_type'];

        $info_k = $this->getV_type_ContactID($contactID_k);
        $info_V_type=array();
        if(isset($info_k['V_type'])) $info_V_type = $info_k['V_type'];

        $info_d = array_diff($info_wc_V_type,$info_V_type);

        if(count($info_k)>0){
            if(count($info_d)>0){
                if(count($info_V_type) >0) $info_d = array_merge($info_d,$info_V_type);
                $info_d = implode(",",$info_d);
                $updateV = "UPDATE `vendor_type`
                SET active = '{$active}',
                    V_type = '{$info_d}'
                WHERE UID = '{$contactID_k}'";
                $rsl = mysqli_query($this->con,$updateV);
                if($rsl){
                    return 1;
                }else{
                    return mysqli_error($this->con);
                }
            }else{
                $updateV = "UPDATE `vendor_type`
                SET active = '1'
                WHERE UID = '{$contactID_k}'";
                $rsl=mysqli_query($this->con,$updateV);
                if($rsl){
                    return 1;
                }else{
                    return mysqli_error($this->con);
                }
            }
        }else{
            if(count($info_d)>0){
                if(count($info_V_type) >0) $info_d = array_merge($info_d,$info_V_type);
                $info_d = implode(",",$info_d);
                $fields = "V_type,UID,active";
                $values = "'{$info_d}','{$contactID_k}',1";
                $insert = "INSERT INTO vendor_type({$fields}) VALUES({$values})";
                mysqli_query($this->con,$insert);
                $idreturn = mysqli_insert_id($this->con);
                return $idreturn;
            }else{
                $fields = "UID,active";
                $values = "'{$contactID_k}',1";
                $insert = "INSERT INTO vendor_type({$fields}) VALUES({$values})";
                mysqli_query($this->con,$insert);
                $idreturn = mysqli_insert_id($this->con);
                return $idreturn;
            }
        }

    }

    //----------------------------------------------------------
    public function getA_type_ContactID($ContactID){

        $query = "SELECT aff_type,AID from affiliate
          WHERE UID ='{$ContactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['aff_type'] =explode(',',$row['aff_type']);
                $list =$row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function mergeUpdateA_type($a_type,$contactID_k,$active)
    {

        $info_wc_a_type=array();
        if(!empty($a_type)) $info_wc_a_type =explode(',',$a_type);

        $info_k = $this->getA_type_ContactID($contactID_k);
        $info_a_type=array();
        if(isset($info_k['aff_type'])) $info_a_type = $info_k['aff_type'];

        $info_d = array_diff($info_wc_a_type,$info_a_type);

        if(count($info_k)>0){
            if(count($info_d)>0){
                if(count($info_a_type) >0) $info_d = array_merge($info_d,$info_a_type);
                $info_d = implode(",",$info_d);
                $updateV = "UPDATE `affiliate`
                SET active = '{$active}',
                    aff_type = '{$info_d}'
                WHERE UID = '{$contactID_k}'";
                $rsl = mysqli_query($this->con,$updateV);
                if($rsl){
                    return 1;
                }else{
                    return mysqli_error($this->con);
                }
            }else{
                $updateV = "UPDATE `affiliate`
                SET active = '1'
                WHERE UID = '{$contactID_k}'";
                $rsl= mysqli_query($this->con,$updateV);

                if($rsl){
                    return 1;
                }else{
                    return mysqli_error($this->con);
                }
            }
        }else{
            if(count($info_d)>0){
                if(count($info_a_type) >0) $info_d = array_merge($info_d,$info_a_type);
                $info_d = implode(",",$info_d);
                $fields = "aff_type,UID,active";
                $values = "'{$info_d}','{$contactID_k}',1";
                $insert = "INSERT INTO affiliate({$fields}) VALUES({$values})";
                mysqli_query($this->con,$insert);
                $idreturn = mysqli_insert_id($this->con);
                return $idreturn;
            }else{
                $fields = "UID,active";
                $values = "'{$contactID_k}',1";
                $insert = "INSERT INTO affiliate({$fields}) VALUES({$values})";
                mysqli_query($this->con,$insert);
                $idreturn = mysqli_insert_id($this->con);
                return $idreturn;
            }
        }

    }

    //----------Merge Order-----------
    public function mergeOrder($data){
        $list=array();
        if(count($data>0)){
            foreach($data as $item){
                $contact_keep = $item['contact_keep'];
                if(is_numeric($contact_keep) && !empty($contact_keep)){
                    $contact_was_merged = $item['contact_was_merged'];
                    $contact_merged = implode(",",$contact_was_merged);
                    $keep = $this->getcontactpropeties_ID($contact_keep);

                    foreach($contact_was_merged as $conID){
                        $merge_info = $this->getcontactpropeties_ID($conID);
                        $list[] =$this->mGetOrder_billto_saleID($contact_keep,$keep['SID'],$conID,$merge_info['SID'],$contact_merged);
                    }
                }

            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function mGetOrder_billto_saleID($ContactID_k,$salesman_k,$ContactID_m,$salesman_m,$contact_merged)
    {
        $query = "SELECT DISTINCT order_id,bill_to,salesperson,order_create_by from orders";

        $where ='';
        if(!(empty($ContactID_m))){
            $where .=empty($where)?"":"OR";
            $where .="(bill_to ='{$ContactID_m}' AND bill_to <>'' AND bill_to IS NOT NULL) OR";
            $where .="(order_create_by ='{$ContactID_m}' AND order_create_by <>'' AND order_create_by IS NOT NULL)";

        }

        if(!(empty($salesman_m))){
            $where .=empty($where)?"":"OR";
            $where .="(salesperson='{$salesman_m}' AND salesperson <>'' AND salesperson IS NOT NULL)";
        }

        if(!empty($where)){
            $query .=" WHERE ".$where;
        }

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $update_q='';
                $update_m='';
                $insert_v='';
                $insert_f='';
                if($row['bill_to']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" bill_to = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" bill_to = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="bill_to";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }

                if($row['order_create_by']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .="order_create_by = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" create_by = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="create_by";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }

                if($row['salesperson']==$salesman_m && !empty($salesman_m) && !empty($salesman_k)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" salesperson = '{$salesman_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" salesperson = '{$salesman_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="salesperson";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$salesman_m}'";
                }

                if(!empty($update_q)){
                    $list[] = $this->mUpdateOrder($update_q,$update_m,$row['order_id'],$ContactID_k,$salesman_k,
                        $ContactID_m,$salesman_m,$insert_f,$insert_v,$contact_merged);
                }

            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function mUpdateOrder($update_query,$update_m,$order_id,$ContactID_k,$salesman_k,
                                 $ContactID_m,$salesman_m,$fields,$values,$contact_merged){
        $update = "UPDATE `orders`
                 SET ".$update_query."
                 WHERE order_id='{$order_id}'";


        $rsl = mysqli_query($this->con,$update);
        //back up old order
        $mID=$this->checkOrderInTableMerge_item($order_id);
        if(is_numeric($mID['ID']) && !empty($mID['ID'])){
            $contact_was_merged =$mID['contact_was_merged'];
            $contact_was_merged .=empty($contact_was_merged)?"":",";
            $contact_was_merged .=$ContactID_m;

            $update_m .=empty($update_m)?"":",";
            $update_m .=" contact_was_merged = '{$contact_was_merged}'";

            $this->updateToMergetable_ID($mID['ID'],$update_m);

        }else{
            $this->insertOrderToMergetable($fields,$values,$order_id,$ContactID_k,$salesman_k,$ContactID_m,$contact_merged);
        }
        //
        if($rsl){
            return array("order_id"=>$order_id,"update"=>"success");
        }else{
            return array("order_id"=>$order_id,"update"=>mysqli_error($this->con));
        }

    }

    //----------------------------------------------------------
    public function checkOrderInTableMerge_item($order_id){

        $query = "SELECT ID,contact_was_merged FROM merge_item
          WHERE type_id ='{$order_id}' AND type='order' LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $list = '';

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function insertOrderToMergetable($fields,$values,$order_id,$ContactID_k,
                                            $salesman_k,$ContactID_m,$contact_merged){
        $fields .=empty($fields)?"":",";
        $fields .="type_id,type,contact_id_keep,contact_was_merged,contact_merged";

        $values .=empty($values)?"":",";
        $values .="'{$order_id}','order','{$ContactID_k}','{$ContactID_m}','{$contact_merged}'";


        $insert = "INSERT INTO merge_item ({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insert);
    }

    //----------Merge Warranty-----------
    public function mergeWarranty($data){
        $list=array();
        if(count($data>0)){
            foreach($data as $item){
                $contact_keep = $item['contact_keep'];
                if(is_numeric($contact_keep) && !empty($contact_keep)){
                    $contact_was_merged = $item['contact_was_merged'];
                    $contact_merged = implode(",",$contact_was_merged);
                    $keep = $this->getcontactpropeties_ID($contact_keep);

                    foreach($contact_was_merged as $conID){
                        $merge_info = $this->getcontactpropeties_ID($conID);


                        $list[] =$this->mGetWarranty_billto_saleID_Agent($contact_keep,$keep['SID'],$keep['AID'],
                            $conID,$merge_info['SID'],$merge_info['AID'],$contact_merged);
                    }
                }

            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function mGetWarranty_billto_saleID_Agent($ContactID_k,$salesman_k,$AID_k,$ContactID_m,$salesman_m,
                                                     $AID_m,$contact_merged)
    {
        $query = "SELECT DISTINCT ID,warranty_buyer_id,warranty_salesman_id,
                  warranty_seller_agent_id,warranty_buyer_agent_id,
                  warranty_escrow_id,warranty_mortgage_id,warranty_create_by,
                  warranty_update_by,warranty_payer
                    from warranty";

          $where ='';
          if(!(empty($ContactID_m))){
              $where .=empty($where)?"":"OR";
              $where .="(warranty_buyer_id ='{$ContactID_m}' and (warranty_buyer_id <>'' AND warranty_buyer_id IS NOT NULL)) OR";
              $where .="(warranty_create_by ='{$ContactID_m}' and (warranty_create_by <>'' AND warranty_create_by IS NOT NULL))OR";
              $where .="(warranty_update_by ='{$ContactID_m}' and (warranty_update_by <>'' AND warranty_update_by IS NOT NULL))OR";
              $where .="(warranty_payer ='{$ContactID_m}' and (warranty_payer <>'' AND warranty_payer IS NOT NULL))";
          }

        if(!(empty($salesman_m))){
            $where .=empty($where)?"":"OR";
            $where .="(warranty_salesman_id ='{$salesman_m}' and (warranty_salesman_id <>'' AND warranty_salesman_id IS NOT NULL))";
        }

        if(!(empty($AID_m))){
            $where .=empty($where)?"":"OR";
            $where .="(warranty_seller_agent_id ='{$AID_m}' and (warranty_seller_agent_id <>'' AND warranty_seller_agent_id IS NOT NULL)) OR";
            $where .="(warranty_buyer_agent_id ='{$AID_m}' and (warranty_buyer_agent_id <>'' AND warranty_buyer_agent_id IS NOT NULL)) OR";
            $where .="(warranty_escrow_id ='{$AID_m}' and (warranty_escrow_id <>'' AND warranty_escrow_id IS NOT NULL)) OR";
            $where .="(warranty_mortgage_id ='{$AID_m}' and (warranty_mortgage_id <>'' AND warranty_mortgage_id IS NOT NULL))";
        }

        if(!empty($where)){
            $query .=" WHERE ".$where;
        }

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $update_q='';
                $update_m='';
                $insert_v='';
                $insert_f='';
                if($row['warranty_buyer_id']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" warranty_buyer_id = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" warranty_buyer_id = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="warranty_buyer_id";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }

                if($row['warranty_create_by']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" warranty_create_by = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" create_by = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="create_by";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }
                //
                if($row['warranty_payer']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" warranty_payer = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" warranty_payer = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="warranty_payer";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }

                if($row['warranty_update_by']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" warranty_update_by = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" warranty_update_by = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="warranty_update_by";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }
                //
                if($row['warranty_salesman_id']==$salesman_m && !empty($salesman_m) && !empty($salesman_k)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" warranty_salesman_id = '{$salesman_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" warranty_salesman_id = '{$salesman_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="warranty_salesman_id";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$salesman_m}'";
                }

                if($row['warranty_seller_agent_id']==$AID_m && !empty($AID_m) && !empty($AID_k)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" warranty_seller_agent_id = '{$AID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" warranty_seller_agent_id = '{$AID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="warranty_seller_agent_id";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$AID_m}'";
                }

                if($row['warranty_buyer_agent_id']==$AID_m && !empty($AID_m) && !empty($AID_k)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" warranty_buyer_agent_id = '{$AID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" warranty_buyer_agent_id = '{$AID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="warranty_buyer_agent_id";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$AID_m}'";
                }

                if($row['warranty_escrow_id']==$AID_m && !empty($AID_m) && !empty($AID_k)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" warranty_escrow_id = '{$AID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" warranty_escrow_id = '{$AID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="warranty_escrow_id";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$AID_m}'";
                }

                if($row['warranty_mortgage_id']==$AID_m && !empty($AID_m) && !empty($AID_k)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" warranty_mortgage_id = '{$AID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" warranty_mortgage_id = '{$AID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="warranty_mortgage_id";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$AID_m}'";
                }

                if(!empty($update_q)){
                    $list[] = $this->mUpdateWarranty($update_q,$update_m,$row['ID'],$ContactID_k,$salesman_k,$AID_k,
                        $ContactID_m,$insert_f,$insert_v,$contact_merged);
                }

            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function mUpdateWarranty($update_query,$update_m,$w_id,$ContactID_k,$salesman_k,$AID_k,
                                    $ContactID_m,$fields,$values,$contact_merged){
        $update = "UPDATE `warranty`
                 SET ".$update_query."
                 WHERE ID='{$w_id}'";


        $rsl = mysqli_query($this->con,$update);
        //back up old warranty
        $merge_ID=$this->checkWarrantyInTableMerge_item($w_id);
        if(is_numeric($merge_ID['ID']) && !empty($merge_ID['ID'])){
            $contact_was_merged =$merge_ID['contact_was_merged'];
            $contact_was_merged .=empty($contact_was_merged)?"":",";
            $contact_was_merged .=$ContactID_m;

            $update_m .=empty($update_m)?"":",";
            $update_m .=" contact_was_merged = '{$contact_was_merged}'";

            $this->updateToMergetable_ID($merge_ID['ID'],$update_m);

        }else{
            $this->insertWarrantyToMergetable($fields,$values,$w_id,$ContactID_k,$salesman_k,$AID_k,$ContactID_m,$contact_merged);
        }
        //
        if($rsl){
            return array("warranty_id"=>$w_id,"update"=>"success");
        }else{
            return array("warranty_id"=>$w_id,"update"=>mysqli_error($this->con));
        }
    }

    //----------------------------------------------------------
    public function checkWarrantyInTableMerge_item($w_id){

        $query = "SELECT ID,contact_was_merged FROM merge_item
          WHERE type_id ='{$w_id}' AND type='warranty' LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $list = '';

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function insertWarrantyToMergetable($fields,$values,$w_id,$ContactID_k,$salesman_k,$AID_k,$ContactID_m,$contact_merged){
        $fields .=empty($fields)?"":",";
        $fields .="type_id,type,contact_id_keep,contact_was_merged,contact_merged";

        $values .=empty($values)?"":",";
        $values .="'{$w_id}','warranty','{$ContactID_k}','{$ContactID_m}','{$contact_merged}'";
        /*
        if(!empty($salesman_k) && is_numeric($salesman_k)){
            $fields .=empty($fields)?"":",";
            $fields .="salesman_keep";

            $values .=empty($fields)?"":",";
            $values .="'{$salesman_k}'";
        }

        if(!empty($AID_k) && is_numeric($AID_k)){
            $fields .=empty($fields)?"":",";
            $fields .="AID_keep";

            $values .=empty($fields)?"":",";
            $values .="'{$AID_k}'";
        }*/

        $insert = "INSERT INTO merge_item ({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insert);
    }
    //---------------------------------------------------------

    //----------Merge Invoice-----------
    public function mergeInvoice($data){
        $list=array();
        if(count($data>0)){
            foreach($data as $item){
                $contact_keep = $item['contact_keep'];
                if(is_numeric($contact_keep) && !empty($contact_keep)){
                    $contact_was_merged = $item['contact_was_merged'];
                    $contact_merged = implode(",",$contact_was_merged);
                    $keep = $this->getcontactpropeties_ID($contact_keep);

                    foreach($contact_was_merged as $conID){
                        $merge_info = $this->getcontactpropeties_ID($conID);
                        $list[] =$this->mGetInvoice_billto_saleID($contact_keep,$keep['SID'],
                            $conID,$merge_info['SID'],$contact_merged);
                    }
                }

            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function mGetInvoice_billto_saleID($ContactID_k,$salesman_k,$ContactID_m,
                                              $salesman_m,$contact_merged)
    {
        $query = "SELECT DISTINCT ID,customer,salesperson,
          invoice_create_by from invoice ";


        //
        $where ='';
        if(!(empty($ContactID_m))){
            $where .=empty($where)?"":"OR";
            $where .="(customer ='{$ContactID_m}' AND customer <>'' AND customer IS NOT NULL) OR";
            $where .="(invoice_create_by ='{$ContactID_m}' AND invoice_create_by <>'' AND invoice_create_by IS NOT NULL)";

        }

        if(!(empty($salesman_m))){
            $where .=empty($where)?"":"OR";
            $where .="(salesperson='{$salesman_m}' AND salesperson <>'' AND salesperson IS NOT NULL)";
        }

        if(!empty($where)){
            $query .=" WHERE ".$where;
        }
        //
        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $update_q='';
                $update_m='';
                $insert_v='';
                $insert_f='';
                if($row['customer']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" customer = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" customer = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="customer";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }

                if($row['invoice_create_by']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .="invoice_create_by = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" create_by = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="create_by";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }

                if($row['salesperson']==$salesman_m && !empty($salesman_m) && !empty($salesman_k)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" salesperson = '{$salesman_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" salesperson = '{$salesman_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="salesperson";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$salesman_m}'";
                }

                if(!empty($update_q)){
                    $list[] = $this->mUpdateInvoice($update_q,$update_m,$row['ID'],
                        $ContactID_k,$salesman_k,$ContactID_m,$salesman_m,
                        $insert_f,$insert_v,$contact_merged);
                }

            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function mUpdateInvoice($update_query,$update_m,$invoice_id,$ContactID_k,
                                   $salesman_k,$ContactID_m,$salesman_m,$fields,$values,$contact_merged){
        $update = "UPDATE `invoice`
                 SET ".$update_query."
                 WHERE ID='{$invoice_id}'";

        $rsl = mysqli_query($this->con,$update);
        //back up old order
        $mID=$this->checkInvoiceInTableMerge_item($invoice_id);
        if(is_numeric($mID['ID']) && !empty($mID['ID'])){
            $contact_was_merged =$mID['contact_was_merged'];
            $contact_was_merged .=empty($contact_was_merged)?"":",";
            $contact_was_merged .=$ContactID_m;

            $update_m .=empty($update_m)?"":",";
            $update_m .=" contact_was_merged = '{$contact_was_merged}'";
            $this->updateToMergetable_ID($mID['ID'],$update_m);

        }else{
            $this->insertInvoiceMergetable($fields,$values,$invoice_id,$ContactID_k,$salesman_k,$ContactID_m,$contact_merged);
        }
        //
        if($rsl){
            return array("inv_id"=>$invoice_id,"update"=>"success");
        }else{
            return array("inv_id"=>$invoice_id,"update"=>mysqli_error($this->con));
        }

    }

    //----------------------------------------------------------
    public function checkInvoiceInTableMerge_item($invoice_id){

        $query = "SELECT ID,contact_was_merged FROM merge_item
          WHERE type_id ='{$invoice_id}' AND type='invoice' LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $list = '';

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function insertInvoiceMergetable($fields,$values,$invoice_id,$ContactID_k,$salesman_k,$ContactID_m,$contact_merged){
        $fields .=empty($fields)?"":",";
        $fields .="type_id,type,contact_id_keep,contact_was_merged,contact_merged";

        $values .=empty($values)?"":",";
        $values .="'{$invoice_id}','invoice','{$ContactID_k}','{$ContactID_m}','{$contact_merged}'";

        $insert = "INSERT INTO merge_item ({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insert);
    }
    //----------------------------------------------------------

    //----------Merge Claim-----------
    public function mergeClaim($data){
        $list=array();
        if(count($data>0)){
            foreach($data as $item){
                $contact_keep = $item['contact_keep'];
                if(is_numeric($contact_keep) && !empty($contact_keep)){
                    $contact_was_merged = $item['contact_was_merged'];
                    $contact_merged = implode(",",$contact_was_merged);
                    //$keep = $this->getcontactpropeties_ID($contact_keep);

                    foreach($contact_was_merged as $conID){
                        //$merge_info = $this->getcontactpropeties_ID($conID);
                        $list[] =$this->mGetClaim_billto_saleID($contact_keep,$conID,$contact_merged);
                    }
                }

            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function mGetClaim_billto_saleID($ContactID_k,$ContactID_m,$contact_merged)
    {
        $query = "SELECT DISTINCT c.ID,c.customer,c.claim_assign,c.create_by,
          cq.typeID AS vendor_id, cq.id as claim_quote_id
          from claims AS c
          LEFT JOIN claim_quote as cq ON cq.claimID = c.ID
          WHERE (c.customer ='{$ContactID_m}' AND c.customer IS NOT NULL AND c.customer <>'') OR
          (c.create_by ='{$ContactID_m}' AND c.create_by IS NOT NULL AND c.create_by <>'') OR
          (c.claim_assign='{$ContactID_m}' AND c.claim_assign IS NOT NULL AND c.claim_assign <>'' ) OR
          (cq.typeID ='{$ContactID_m}' AND cq.typeID IS NOT NULL AND cq.typeID <>'' AND cq.type='vendor')";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $update_q='';
                $update_m='';
                $insert_v='';
                $insert_f='';
                if($row['customer']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" customer = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" customer = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="customer";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }

                if($row['create_by']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" create_by = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" create_by = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="create_by";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }

                if($row['claim_assign']==$ContactID_m && !empty($ContactID_m)){
                    $update_q .=empty($update_q)?"":",";
                    $update_q .=" claim_assign = '{$ContactID_k}'";

                    $update_m .=empty($update_m)?"":",";
                    $update_m .=" claim_assign = '{$ContactID_m}'";

                    $insert_f .=empty($insert_f)?"":",";
                    $insert_f .="claim_assign";

                    $insert_v .=empty($insert_v)?"":",";
                    $insert_v .="'{$ContactID_m}'";
                }

                if(!empty($update_q) || $row['vendor_id']==$ContactID_m){
                    $list[] = $this->mUpdateClaim($update_q,$update_m,$row['ID'],$ContactID_k,$ContactID_m,$row['vendor_id'],
                        $row['claim_quote_id'],$insert_f,$insert_v,$contact_merged);
                }

            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function mUpdateClaim($update_query,$update_m,$claim_id,$ContactID_k,
                                 $ContactID_m,$old_vendor_id,$claim_quote_id,$fields,$values,$contact_merged){
        if(!empty($update_query)){
            $update = "UPDATE `claims`
                 SET ".$update_query."
                 WHERE ID='{$claim_id}'";
            $rsl = mysqli_query($this->con,$update);
        }
        //update vedor in claim_quote
        if($old_vendor_id==$ContactID_m && !empty($ContactID_m)){
            $update = "UPDATE `claim_quote`
                 SET typeID ='{$ContactID_k}'
                 WHERE claimID='{$claim_id}' and typeID='{$old_vendor_id}'";

            $rsl = mysqli_query($this->con,$update);
            //add vendor old_vendor_id to
            $old_vendor =array();
            $old_vendor[] =array("id"=>$claim_quote_id,"vendor_id"=>$old_vendor_id);
            $old_vendor =json_encode($old_vendor);

            $fields .=empty($fields)?"":",";
            $fields .="vendor_id";

            $values .=empty($values)?"":",";
            $values .="'{$old_vendor}'";

        }

        //back up old claim
        $mclaim=$this->checkClaimInTableMerge_item($claim_id);
        if(is_numeric($mclaim['ID']) && !empty($mclaim['ID'])){
            if($old_vendor_id==$ContactID_m && !empty($ContactID_m)){
                $vendor_temp=json_decode($mclaim['vendor_id'],true);

                $vendor_temp[] =array("id"=>$claim_quote_id,"vendor_id"=>$old_vendor_id);

                $old_vendor_t =json_encode($vendor_temp);

                //add vendor old_vendor_id to
                $update_m .=empty($update_m)?"":",";
                $update_m .=" vendor_id = '{$old_vendor_t}'";
            }
            //Update contact_was_merged
            $contact_was_merged =$mclaim['contact_was_merged'];
            $contact_was_merged .=empty($contact_was_merged)?"":",";
            $contact_was_merged .=$ContactID_m;

            $update_m .=empty($update_m)?"":",";
            $update_m .=" contact_was_merged = '{$contact_was_merged}'";

            $this->updateToMergetable_ID($mclaim['ID'],$update_m);

        }else{
            $this->insertClaimMergetable($fields,$values,$claim_id,$ContactID_k,$ContactID_m,$contact_merged);
        }
        //
        if($rsl){
            return array("claim_id"=>$claim_id,"update"=>"success");
        }else{
            return array("claim_id"=>$claim_id,"update"=>mysqli_error($this->con));
        }

    }

    //----------------------------------------------------------
    public function checkClaimInTableMerge_item($claim_id){

        $query = "SELECT ID,vendor_id,contact_was_merged FROM merge_item
          WHERE type_id ='{$claim_id}' AND type='claim' LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }
    //----------------------------------------------------------
    public function insertClaimMergetable($fields,$values,$claim_id,$ContactID_k,$ContactID_m,$contact_merged){
        $fields .=empty($fields)?"":",";
        $fields .="type_id,type,contact_id_keep,contact_was_merged,contact_merged";

        $values .=empty($values)?"":",";
        $values .="'{$claim_id}','claim','{$ContactID_k}','{$ContactID_m}','{$contact_merged}'";

        $insert = "INSERT INTO merge_item ({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insert);
    }


    //----------Merge Notes-----------
    public function mergeNote($data){
        $list=array();
        if(count($data>0)){
            foreach($data as $item){
                $contact_keep = $item['contact_keep'];
                if(is_numeric($contact_keep) && !empty($contact_keep)){
                    $contact_was_merged = $item['contact_was_merged'];
                    $contact_was_m =implode(",",$item['contact_was_merged']);
                    //Insert note into merge_item table
                    foreach($contact_was_merged as $conID){
                        $list[]= $this->mGetNotes($conID,$contact_keep,$contact_was_m);
                    }
                    //UPDATE notes
                    if(!empty($contact_was_m)){
                        $this->mUpdateNote($contact_was_m,$contact_keep);
                    }

                }

            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function mGetNotes($ContactID_m,$contact_keep,$contact_was_m){
        if(!empty($ContactID_m)){
            $query = "SELECT DISTINCT noteID,contactID,enter_by from notes
                    WHERE contactID = '{$ContactID_m}' OR enter_by = '{$ContactID_m}'";
            $result = mysqli_query($this->con,$query);
            $list = '';
            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $update_m='';
                    $insert_v='';
                    $insert_f='';
                    if($row['contactID']==$ContactID_m && !empty($ContactID_m)){
                        $update_m .=empty($update_m)?"":",";
                        $update_m .=" contactID = '{$ContactID_m}'";

                        $insert_f .=empty($insert_f)?"":",";
                        $insert_f .="contactID";

                        $insert_v .=empty($insert_v)?"":",";
                        $insert_v .="'{$ContactID_m}'";
                    }

                    if($row['enter_by']==$ContactID_m && !empty($ContactID_m)){
                        $update_m .=empty($update_m)?"":",";
                        $update_m .=" enter_by = '{$ContactID_m}'";

                        $insert_f .=empty($insert_f)?"":",";
                        $insert_f .="enter_by";

                        $insert_v .=empty($insert_v)?"":",";
                        $insert_v .="'{$ContactID_m}'";
                    }

                    if(!empty($insert_f)){
                        //back up old order
                        $mID=$this->checkNotesInTableMerge_item($row['noteID']);
                        if(is_numeric($mID['ID']) && !empty($mID['ID'])){
                            $contact_was_merged =$mID['contact_was_merged'];
                            $contact_was_merged .=empty($contact_was_merged)?"":",";
                            $contact_was_merged .=$ContactID_m;

                            $update_m .=empty($update_m)?"":",";
                            $update_m .=" contact_was_merged = '{$contact_was_merged}'";

                            $this->updateToMergetable_ID($mID['ID'],$update_m);

                        }else{
                            $list = $this->insertNoteMergetable($row['noteID'],$contact_keep,$ContactID_m,$insert_f,$insert_v,$contact_was_m);
                        }
                    }
                }
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function checkNotesInTableMerge_item($note_id){

        $query = "SELECT ID,contact_was_merged FROM merge_item
          WHERE type_id ='{$note_id}' AND type='note' LIMIT 1";

        $result = mysqli_query($this->con,$query);
        $list = '';

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function insertNoteMergetable($noteID,$ContactID_k,$ContactID_m,$fields,$values,$contact_was_m){
        $fields .=empty($fields)?"":",";
        $fields .="type_id,type,contact_id_keep,contact_was_merged,contact_merged";

        $values .=empty($values)?"":",";
        $values .="'{$noteID}','note','{$ContactID_k}','{$ContactID_m}','{$contact_was_m}'";

        $insert = "INSERT INTO merge_item ({$fields}) VALUES({$values})";
        mysqli_query($this->con,$insert);

        $idreturn = mysqli_insert_id($this->con);
        if(!empty($idreturn) && is_numeric($idreturn)){
            return $idreturn;
        }else{
            return mysqli_error($this->con);
        }
    }

    //----------------------------------------------------------
    public function updateToMergetable_ID($ID,$update_query){
        $update = "UPDATE `merge_item`
                SET ".$update_query."
                 WHERE ID='{$ID}'";
        $rsl = mysqli_query($this->con,$update);
    }
    //----------------------------------------------------------
    public function mUpdateNote($IDs,$ContactID_k){
        $update = "UPDATE `notes`
                SET contactID ='{$ContactID_k}'
                 WHERE contactID IN ({$IDs})";
        $rsl = mysqli_query($this->con,$update);
        //enter_by
        $update = "UPDATE `notes`
                SET enter_by ='{$ContactID_k}'
                 WHERE enter_by IN ({$IDs})";
        $rsl = mysqli_query($this->con,$update);
    }

    //----------------------------------------------------------
    public function ContactMergedToInactive($IDs){
        //Contact
        $update = "UPDATE `contact`
                SET contact_inactive ='1'
                 WHERE ID IN ({$IDs})";
        $rsl = mysqli_query($this->con,$update);
        //affiliate
        $update = "UPDATE `affiliate`
                SET active ='0'
                 WHERE UID IN ({$IDs})";
        $rsl = mysqli_query($this->con,$update);

        //sales
        $update = "UPDATE `salesman`
                SET active ='0'
                 WHERE UID IN ({$IDs})";
        $rsl = mysqli_query($this->con,$update);

        //vendor
        $update = "UPDATE `vendor_type`
                SET active ='0'
                 WHERE UID IN ({$IDs})";
        $rsl = mysqli_query($this->con,$update);
    }

    //---------Undu
    //----------------------------------------------------------
    public function getListUndo($contactID){

        $query = "SELECT * FROM merge_item
          WHERE contact_id_keep ='{$contactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        $i=0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                //order
                if($row['type']=='order'){
                    $update_q='';
                    if(!empty($row['bill_to'])){
                        $bill_to = $row['bill_to'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" bill_to = '{$bill_to}'";
                    }

                    if(!empty($row['create_by'])){
                        $order_create_by =$row['create_by'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" order_create_by = '{$order_create_by}'";
                    }

                    if(!empty($row['salesperson'])){
                        $salesperson =$row['salesperson'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" salesperson = '{$salesperson}'";
                    }

                    if(!empty($update_q)){
                        $list[] = $this->undoOrder($update_q,$row['type_id'],$row['ID']);
                    }
                }
                //invoice
                if($row['type']=='invoice'){
                    $update_q='';
                    if(!empty($row['customer'])){
                        $bill_to = $row['customer'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" customer = '{$bill_to}'";
                    }

                    if(!empty($row['create_by'])){
                        $invoice_create_by =$row['create_by'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" invoice_create_by = '{$invoice_create_by}'";
                    }

                    if(!empty($row['salesperson'])){
                        $salesperson =$row['salesperson'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" salesperson = '{$salesperson}'";
                    }

                    if(!empty($update_q)){
                        $list[] = $this->undoInvoice($update_q,$row['type_id'],$row['ID']);
                    }
                }
                //warranty
                if($row['type']=='warranty'){
                    $update_q='';
                    if(!empty($row['warranty_buyer_id'])){
                        $warranty_buyer_id = $row['warranty_buyer_id'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" warranty_buyer_id = '{$warranty_buyer_id}'";
                    }

                    if(!empty($row['create_by'])){
                        $warranty_create_by =$row['create_by'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" warranty_create_by = '{$warranty_create_by}'";
                    }

                    if(!empty($row['warranty_update_by'])){
                        $warranty_create_by =$row['warranty_update_by'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" warranty_update_by = '{$warranty_create_by}'";
                    }

                    if(!empty($row['warranty_payer'])){
                        $warranty_create_by =$row['warranty_payer'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" warranty_payer = '{$warranty_create_by}'";
                    }

                    if(!empty($row['warranty_salesman_id'])){
                        $salesperson =$row['warranty_salesman_id'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" warranty_salesman_id = '{$salesperson}'";
                    }

                    if(!empty($row['warranty_seller_agent_id'])){
                        $warranty_seller_agent_id =$row['warranty_seller_agent_id'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" warranty_seller_agent_id = '{$warranty_seller_agent_id}'";
                    }

                    if(!empty($row['warranty_buyer_agent_id'])){
                        $warranty_buyer_agent_id =$row['warranty_buyer_agent_id'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" warranty_buyer_agent_id = '{$warranty_buyer_agent_id}'";
                    }

                    if(!empty($row['warranty_escrow_id'])){
                        $warranty_escrow_id =$row['warranty_escrow_id'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" warranty_escrow_id = '{$warranty_escrow_id}'";
                    }

                    if(!empty($row['warranty_mortgage_id'])){
                        $warranty_mortgage_id =$row['warranty_mortgage_id'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" warranty_mortgage_id = '{$warranty_mortgage_id}'";
                    }

                    if(!empty($update_q)){
                        $list[] = $this->undoWarranty($update_q,$row['type_id'],$row['ID']);
                    }
                }

                //Claim
                if($row['type']=='claim'){
                    $update_q='';
                    if(!empty($row['customer'])){
                        $bill_to = $row['customer'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" customer = '{$bill_to}'";
                    }

                    if(!empty($row['create_by'])){
                        $create_by =$row['create_by'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" create_by = '{$create_by}'";
                    }

                    if(!empty($row['claim_assign'])){
                        $claim_assign =$row['claim_assign'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" claim_assign = '{$claim_assign}'";
                    }

                    if(!empty($update_q)){
                        $list[] = $this->undoClaims($update_q,$row['type_id'],$row['ID']);
                    }

                    $vendors_id=json_decode($row['vendor_id'],true);

                    if(count($vendors_id)>0){
                        foreach($vendors_id as $vid){
                            $this->undoVendorInClaimQuoute($vid,$row['type_id'],$contactID);
                        }
                    }

                }

                //notes
                if($row['type']=='note'){
                    $update_q='';
                    if(!empty($row['contactID'])){
                        $bill_to = $row['contactID'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" contactID = '{$bill_to}'";
                    }

                    if(!empty($row['enter_by'])){
                        $enter_by =$row['enter_by'];

                        $update_q .=empty($update_q)?"":",";
                        $update_q .=" enter_by = '{$enter_by}'";
                    }


                    if(!empty($update_q)){
                        $list[] = $this->undoNote($update_q,$row['type_id'],$row['ID']);
                    }
                }

                //contact
                  if($i=0){
                      $contactInfo = $this->getcontactpropeties_ID($contactID);

                      if(count($list)>0){
                          $contactType =explode(",",$contactInfo['contact_type']);
                          foreach($contactType as $ctype){
                              if($ctype=='Affiliate'){

                              }

                              if($ctype=='Sales'){

                              }

                              if($ctype=='Vendor'){

                              }

                          }
                      }

                  }

                  $i++;
                //----------------
            }
        }
        return $list;
    }

    //----------------------------------------------------------
    public function undoOrder($update_query,$order_id,$ID_m){
        $update = "UPDATE `orders` SET ".$update_query." WHERE order_id ='{$order_id}'";
        $rsl = mysqli_query($this->con,$update);

        if($rsl){
            $del ="Delete from merge_item where ID ='{$ID_m}'";
            $rsl = mysqli_query($this->con,$del);
            return array("order"=>$order_id);
        }else{
            return array("order"=>mysqli_error($this->con));
        }
    }

    //---------Undu Invoice----------------------------------
    public function undoInvoice($update_query,$id,$ID_m){
        $update = "UPDATE `invoice` SET ".$update_query." WHERE ID ='{$id}'";
        $rsl = mysqli_query($this->con,$update);

        if($rsl){
            $del ="Delete from merge_item where ID ='{$ID_m}'";
            $rsl = mysqli_query($this->con,$del);
            return array("invoice"=>$id);
        }else{
            return array("invoice"=>mysqli_error($this->con));
        }
    }

    //---------Undu undoWarranty----------------------------------
    public function undoWarranty($update_query,$id,$ID_m){
        $update = "UPDATE `warranty` SET ".$update_query." WHERE ID ='{$id}'";
        $rsl = mysqli_query($this->con,$update);

        if($rsl){
            $del ="Delete from merge_item where ID ='{$ID_m}'";
            $rsl = mysqli_query($this->con,$del);
            return array("warranty"=>$id);
        }else{
            return array("warranty"=>mysqli_error($this->con));
        }
    }

    //---------Undu Claim----------------------------------
    public function undoClaims($update_query,$id,$ID_m){
        $update = "UPDATE `claims` SET ".$update_query." WHERE ID ='{$id}'";
        $rsl = mysqli_query($this->con,$update);

        $rsl = mysqli_query($this->con,$update);

        if($rsl){
            $del ="Delete from merge_item where ID ='{$ID_m}'";
            $rsl = mysqli_query($this->con,$del);
            return array("claim"=>$id);
        }else{
            return array("claim"=>mysqli_error($this->con));
        }
    }

    //---------Undu Claim----------------------------------
    public function undoVendorInClaimQuoute($v_id,$id,$contact_k){
        $v_id_restore = $v_id["vendor_id"];
        $q_id_restore = $v_id["id"];
        $update = "UPDATE `claim_quote`
                 SET typeID ='{$v_id_restore}'
                 WHERE id='{$q_id_restore}'";

        $rsl = mysqli_query($this->con,$update);
    }

    //---------Undo note----------------------------------
    public function undoNote($update_query,$id,$ID_m){
        $update = "UPDATE `notes` SET ".$update_query." WHERE noteID ='{$id}'";
        $rsl = mysqli_query($this->con,$update);

        if($rsl){
            $del ="Delete from merge_item where ID ='{$ID_m}'";
            $rsl = mysqli_query($this->con,$del);
            return array("note"=>$id);
        }else{
            return array("note"=>mysqli_error($this->con));
        }
    }

    //------------------------------------------------------------
    public function getDuplicateContact_email($primary_email,$primary_phone,$searchbyemail){
        $list=array();
        if(!empty($primary_email) && $searchbyemail==1){
            $query ="select primary_email, count(distinct ID)
                    from contact
                    where  primary_email='{$primary_email}' and contact_inactive =0
                    Group by primary_email
                    having count(distinct ID)> 1";

            $groupby="primary_email";
            $value =$primary_email;
        }else{
            $query ="select primary_phone, count(distinct ID)
                    from contact
                    where  primary_phone='{$primary_phone}' and primary_phone <>'' and
                    primary_phone is not null
                    and contact_inactive =0
                    Group by primary_phone
                    having count(distinct ID)> 1";

            $groupby="primary_phone";
            $value =$primary_phone;
        }


        $result = mysqli_query($this->con,$query);

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[]= $this->getcontactGrp_emailorPhone($groupby,$value);
            }
        }

        return $list;
    }
    //////////////////////////////////
}