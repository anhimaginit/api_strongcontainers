<?php

require_once 'class.common.php';
class Report extends Common{
    /**
     * get report
     */
    public function reportContact($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT ID,{$listNameColumns} FROM  report_contact ";
        $queryCount = "SELECT COUNT(*) as total FROM  report_contact ";
        $query1="";
        $queryCount1="";

        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        if($key=='company_name'){
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." = '{$arrayKey}'";
                        }else{
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." LIKE '%{$arrayKey}%'";
                        }

                    }
                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {
                    $query1 .=empty($query1)?"":" AND ";
                    $query1 .=$key." LIKE '%{$value}%'";

                    $queryCount1 .=empty($queryCount1)?"":" AND ";
                    $queryCount1 .=$key." LIKE '%{$value}%'";
                }

            }            
        }
        
        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }

            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['total']=$totalRows['total'];
            if($data['numberOfRows'] >0){
                if($totalRows['total']%$data['numberOfRows']==0)
                {
                    $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
                }else
                {
                    $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
                }
            }
            
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            if(!empty($query1)){
                $query .=" WHERE ".$query1;
            }
            if(!empty($numberOfRows)){
                //$query .=" WHERE ".$query1." LIMIT $start,$numberOfRows";
                $query .=" LIMIT $start,$numberOfRows";
            }

        }else{
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['total']=$totalRows['total'];
            if($totalRows['total']%$data['numberOfRows']==0)
            if($totalRows['total']%$data['numberOfRows']==0)
            {
                $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
            }else
            {
                $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
            }

            $numberOfRows = $data['numberOfRows'];

            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }


        }
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                $row["ID"]='<a href="/#ajax/contact-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }
        /**
     * get report
     */
    public function reportDownloadCsvContact($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT {$listNameColumns} FROM  report_contact  ";
        $queryCount = "SELECT COUNT(*) as total FROM  report_contact  ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        //$query .=$or.$key." LIKE '%$arrayKey%'";
                        //$queryCount.=$or.$key." LIKE '%$arrayKey%'";
                        $subquery .=empty($subquery)?"":" OR ";
                        $subquery .=$key." LIKE '%{$arrayKey}%'";
                    }

                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {               
                    //$query .=$and.$key." LIKE '%$value%'";
                    //$queryCount.=$and.$key." LIKE '%$value%'";
                    $query1 .=empty($query1)?"":" AND ";
                    $query1 .=$key." LIKE '%{$value}%'";

                    $queryCount1 .=empty($queryCount1)?"":" AND ";
                    $queryCount1 .=$key." LIKE '%{$value}%'";
                }
                //$or = ' OR ';
                //$and = ' AND ';
            }            
        }
        
        /*if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }

            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);

            if($totalRows['total']%$data['numberOfRows']==0)
            {
                $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
            }else
            {
                $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
            }            
            
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }
            //$query .=" LIMIT $start,$numberOfRows";
        } */

        if(!empty($query1)){
            $query .=" WHERE ".$query1;
        }

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    /**
     * get report orders
     */
    public function reportOrder($data,$personal_filter,$id_login)
    {
        $criteria=''; $personal_filter = trim($personal_filter);
        //print_r($id_login);
        if($personal_filter == "login_only"){
            $p = $this->orderRelative($id_login);
            //print_r($p);
            if(count($p)>0){
                $p = implode(",",$p);
               // $criteria = "(order_id IN ({$p}))";
            }
        }elseif($personal_filter == "group"){
            $p = $this->parentManageUsers($id_login);
            if(count($p)>0){
                $p = implode(",",$p);
               // $criteria = "(bill_to IN ({$p}))";
            }
        }elseif($personal_filter == "child_group"){
            $p = $this->userChild($id_login);
            if(count($p)>0){
                $p = implode(",",$p);
               // $criteria = "(bill_to IN ({$p}))";
            }
        }

        $order_list='';
        //die($criteria);
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT order_id as ID,{$listNameColumns} FROM  report_orders ";
        $queryCount = "SELECT COUNT(*) as total FROM  report_orders ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";

        foreach($data['key'] as $key => $value) 
        {          
            if($value !="" && $key!="affiliate")
            {
                if(is_array($value))
                {
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        if($key=='bill_to' || $key=='salesperson' || $key=='balance' || $key=='order_create_by'){
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." = '{$arrayKey}'";
                        } elseif($key=='products_ordered'){
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .="json_contains(products_ordered->'$[*].sku', json_array('{$arrayKey}'))AND
                            products_ordered->'$[*].id' <> json_array('Select product')";
                        } else{
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." LIKE '%{$arrayKey}%'";
                        }
                        //$query .=$or.$key." LIKE '%$arrayKey%'";
                        //$queryCount.=$or.$key." LIKE '%$arrayKey%'";
                    }
                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {
                    if($key=='bill_to' || $key=='salesperson' || $key=='order_create_by'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." = '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." = '{$value}'";
                    }elseif($key=='balance_min'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="balance >= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="balance >= '{$value}'";
                    }elseif($key=='payment_min'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="payment >= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="payment >= '{$value}'";
                    }elseif($key=='total_min' ){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="total >= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="total >= '{$value}'";
                    }
                    elseif($key=='total_max' ){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="total <= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="total <= '{$value}'";
                    }elseif($key=='balance_max'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="balance <= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="balance <= '{$value}'";
                    }elseif($key=='payment_max'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="payment <= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="payment <= '{$value}'";
                    }
                    else{
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." LIKE '%{$value}%'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." LIKE '%{$value}%'";
                    }

                }

            }
            elseif(!empty($value) && $key=='affiliate'){
                //key =affilate
                $order_list= $this->getOrder_affilate($value);
                if(!empty($order_list)){
                    $criteria .=empty($criteria)?"":" AND ";
                    $criteria .= "(order_id IN (".$order_list."))";
                }else{
                    $criteria .=empty($criteria)?"":" AND ";
                    $criteria .= "(order_id IN (0))";
                }
            }
            ///
        }
        
        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];
            /*$query .=$and." createTime < '$createTimeStartDate'";
            $queryCount.=$and." createTime < '$createTimeStartDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" createTime >= '{$createTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" createTime >= '{$createTimeStartDate}'";
        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
            /*$query .=$and." createTime > '$createTimeEndDate'";
            $queryCount.=$and." createTime > '$createTimeEndDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" createTime <= '{$createTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" createTime <= '{$createTimeEndDate}'";
        }

        if($data['updateTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['updateTimeStartDate'];
            /*$query .=$and." updateTime < '$updateTimeStartDate'";
            $queryCount.=$and." updateTime < '$updateTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" updateTime >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" updateTime >= '{$updateTimeStartDate}'";
        }

        if($data['updateTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['updateTimeEndDate'];
            /*$query .=$and." updateTime > '$updateTimeEndDate'";
            $queryCount.=$and." updateTime > '$updateTimeEndDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" updateTime <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" updateTime <= '{$updateTimeEndDate}'";
        }
         //
        if($data['closing_date_start']!="")
        {
            $closing_date_s = $data['closing_date_start'];
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" paid_in_full >= '{$closing_date_s}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" paid_in_full >= '{$closing_date_s}'";
        }

        if($data['closing_date_end']!="")
        {
            $closing_date_e = $data['closing_date_end'];
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" paid_in_full <= '{$closing_date_e}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" paid_in_full <= '{$closing_date_e}'";
        }

        if(!empty($query1)){
            if(!empty($criteria)){
                $query1 .= " AND ".$criteria;
                $queryCount1 .= " AND ".$criteria;
            }

        }else{
            if(!empty($criteria)){
                $query1= $criteria;
                $queryCount1=$criteria;
            }

        }

        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }

            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['maxPages']=1;
            if($data['numberOfRows'] >0){
                if($totalRows['total']%$data['numberOfRows']==0)
                {
                    $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
                }else
                {
                    $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
                }
            }
            
            $numberOfRows = $data['numberOfRows'];

            if(!isset($data['pages'])) $data['pages']=0;

            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }
            if(!empty($query1)){
                $query .=" WHERE ".$query1;
            }/*else{
                $query .=" LIMIT $start,$numberOfRows";
            }*/

            if(!empty($numberOfRows)){
                //$query .=" LIMIT $start,$numberOfRows";
            }

        }
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                //$row["ID"]='<a href="/#ajax/order-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                $row["products_ordered"] = json_decode($row["products_ordered"],true);
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    /**
     * get report orders
     */
    public function reportDownloadCsvOrder($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT {$listNameColumns} FROM  report_orders WHERE ";
        $queryCount = "SELECT COUNT(*) as total FROM  report_orders WHERE ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";

        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        if($key=='bill_to' || $key=='salesperson' || $key=='balance'){
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." = '{$arrayKey}'";
                        }else{
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." LIKE '%{$arrayKey}%'";
                        }

                        //$query .=$or.$key." LIKE '%$arrayKey%'";
                        //$queryCount.=$or.$key." LIKE '%$arrayKey%'";
                    }
                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {
                    if($key=='bill_to' || $key=='salesperson' || $key=='balance'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." = '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." = '{$value}'";
                    }else{
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." LIKE '%{$value}%'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." LIKE '%{$value}%'";
                    }

                    //$query .=$and.$key." LIKE '%$value%'";
                    //$queryCount.=$and.$key." LIKE '%$value%'";
                }
                //$or = ' OR ';
                //$and = ' AND ';
            }            
        }
        
        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];
            /*$query .=$and." createTime < '$createTimeStartDate'";
            $queryCount.=$and." createTime < '$createTimeStartDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" createTime >= '{$createTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" createTime >= '{$createTimeStartDate}'";
        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
            /*$query .=$and." createTime > '$createTimeEndDate'";
            $queryCount.=$and." createTime > '$createTimeEndDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" createTime <= '{$createTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" createTime <= '{$createTimeEndDate}'";
        }

        if($data['updateTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['updateTimeStartDate'];
            /*$query .=$and." updateTime < '$updateTimeStartDate'";
            $queryCount.=$and." updateTime < '$updateTimeStartDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" updateTime >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" updateTime >= '{$updateTimeStartDate}'";
        }

        if($data['updateTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['updateTimeEndDate'];
            /*$query .=$and." updateTime > '$updateTimeEndDate'";
            $queryCount.=$and." updateTime > '$updateTimeEndDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" updateTime <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" updateTime <= '{$updateTimeEndDate}'";
        }       
        
        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['maxPages']=1;
            if($data['numberOfRows'] >0){
                if($totalRows['total']%$data['numberOfRows']==0)
                {
                    $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
                }else
                {
                    $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
                }
            }
            
            $numberOfRows = $data['numberOfRows'];
            if(!isset($data['pages'])) $data['pages']=0;
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            if(!empty($query1)){
                $query .=" WHERE ".$query1;
            }

            //$query .=" LIMIT $start,$numberOfRows";
        }

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }


    
        /**
     * get report Products
     */
    public function reportDownloadCsvProducts($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT {$listNameColumns} FROM report_products WHERE ";
        $queryCount = "SELECT COUNT(*) as total FROM report_products WHERE ";
        $or='';
        $and='';           
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    foreach ($value as $arrayKey) {
                        $query .=$or.$key." LIKE '%$arrayKey%'"; 
                        $queryCount.=$or.$key." LIKE '%$arrayKey%'"; 
                    }
                }
                else
                {               
                    $query .=$and.$key." LIKE '%$value%'";
                    $queryCount.=$and.$key." LIKE '%$value%'";
                }
                $or = ' OR ';   
                $and = ' AND ';
            }            
        }
        
        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];
            $query .=$and." product_added < '$createTimeStartDate'";
            $queryCount.=$and." product_added < '$createTimeStartDate'";
            $and = ' AND ';
        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
            $query .=$and." product_added > '$createTimeEndDate'";
            $queryCount.=$and." product_added > '$createTimeEndDate'";
            $and = ' AND ';
        }

        if($data['updateTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['updateTimeStartDate'];
            $query .=$and." product_updated < '$updateTimeStartDate'";
            $queryCount.=$and." product_updated < '$updateTimeStartDate'";
            $and = ' AND ';
        }

        if($data['updateTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['updateTimeEndDate'];
            $query .=$and." product_updated > '$updateTimeEndDate'";
            $queryCount.=$and." product_updated > '$updateTimeEndDate'";
            $and = ' AND ';
        }       
        
        if($data['showAllRows']=="false")
        {                        
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);

            if($totalRows['total']%$data['numberOfRows']==0)
            {
                $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
            }else
            {
                $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
            }            
            
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            $query .=" LIMIT $start,$numberOfRows";
        }

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;                
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

       /**
     * get report Products
     */
    public function reportProducts($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT ID,{$listNameColumns} FROM report_products WHERE ";
        $queryCount = "SELECT COUNT(*) as total FROM report_products WHERE ";
        $or='';
        $and='';           
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    foreach ($value as $arrayKey) {
                        $query .=$or.$key." LIKE '%$arrayKey%'"; 
                        $queryCount.=$or.$key." LIKE '%$arrayKey%'"; 
                    }
                }
                else
                {               
                    $query .=$and.$key." LIKE '%$value%'";
                    $queryCount.=$and.$key." LIKE '%$value%'";
                }
                $or = ' OR ';   
                $and = ' AND ';
            }            
        }
        
        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];
            $query .=$and." product_added < '$createTimeStartDate'";
            $queryCount.=$and." product_added < '$createTimeStartDate'";
            $and = ' AND ';
        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
            $query .=$and." product_added > '$createTimeEndDate'";
            $queryCount.=$and." product_added > '$createTimeEndDate'";
            $and = ' AND ';
        }

        if($data['updateTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['updateTimeStartDate'];
            $query .=$and." product_updated < '$updateTimeStartDate'";
            $queryCount.=$and." product_updated < '$updateTimeStartDate'";
            $and = ' AND ';
        }

        if($data['updateTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['updateTimeEndDate'];
            $query .=$and." product_updated > '$updateTimeEndDate'";
            $queryCount.=$and." product_updated > '$updateTimeEndDate'";
            $and = ' AND ';
        }       
        
        if($data['showAllRows']=="false")
        {                        
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);

            if($totalRows['total']%$data['numberOfRows']==0)
            {
                $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
            }else
            {
                $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
            }            
            
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            $query .=" LIMIT $start,$numberOfRows";
        }

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                $row["ID"]='<a href="/#ajax/product-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }


   

    /**
     * get report Company
     */
    public function reportCompany($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT ID,{$listNameColumns} FROM report_company  ";
        $queryCount = "SELECT COUNT(*) as total FROM report_company  ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        $subquery .=empty($subquery)?"":" OR ";
                        $subquery .=$key." LIKE '%{$arrayKey}%'";
                        //$query .=$or.$key." LIKE '%$arrayKey%'";
                        //$queryCount.=$or.$key." LIKE '%$arrayKey%'";
                    }
                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {
                    $query1 .=empty($query1)?"":" AND ";
                    $query1 .=$key." LIKE '%{$value}%'";

                    $queryCount1 .=empty($queryCount1)?"":" AND ";
                    $queryCount1 .=$key." LIKE '%{$value}%'";

                    //$query .=$and.$key." LIKE '%$value%'";
                    //$queryCount.=$and.$key." LIKE '%$value%'";
                }
                //$or = ' OR ';
                //$and = ' AND ';
            }            
        }

        // if($data['createTimeStartDate']!="")
        // {
        //     $createTimeStartDate = $data['createTimeStartDate'];
        //     $query .=$and." product_added > '$createTimeStartDate'";
        //     $queryCount.=$and." product_added > '$createTimeStartDate'";
        //     $and = ' AND ';
        // }

        // if($data['createTimeEndDate']!="")
        // {
        //     $createTimeEndDate = $data['createTimeEndDate'];
        //     $query .=$and." product_added < '$createTimeEndDate'";
        //     $queryCount.=$and." product_added < '$createTimeEndDate'";
        //     $and = ' AND ';
        // }

        // if($data['updateTimeStartDate']!="")
        // {
        //     $updateTimeStartDate = $data['updateTimeStartDate'];
        //     $query .=$and." product_updated < '$updateTimeStartDate'";
        //     $queryCount.=$and." product_updated < '$updateTimeStartDate'";
        //     $and = ' AND ';
        // }

        // if($data['updateTimeEndDate']!="")
        // {
        //     $updateTimeEndDate = $data['updateTimeEndDate'];
        //     $query .=$and." product_updated < '$updateTimeEndDate'";
        //     $queryCount.=$and." product_updated < '$updateTimeEndDate'";
        //     $and = ' AND ';
        // }

        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);

            if($totalRows['total']%$data['numberOfRows']==0)
            {
                $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
            }else
            {
                $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
            }            
            
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            if(!empty($query1)){
                $query .=" WHERE ".$query1." LIMIT $start,$numberOfRows";
            }

        }
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                $row["ID"]='<a href="/#ajax/company-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    /**
     * get report Company
     */
    public function reportDownloadCsvCompany($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT {$listNameColumns} FROM report_company  ";
        $queryCount = "SELECT COUNT(*) as total FROM report_company  ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        $subquery .=empty($subquery)?"":" OR ";
                        $subquery .=$key." LIKE '%{$arrayKey}%'";
                        //$query .=$or.$key." LIKE '%$arrayKey%'";
                        //$queryCount.=$or.$key." LIKE '%$arrayKey%'";
                    }

                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {
                    $query1 .=empty($query1)?"":" AND ";
                    $query1 .=$key." LIKE '%{$value}%'";

                    $queryCount1 .=empty($queryCount1)?"":" AND ";
                    $queryCount1 .=$key." LIKE '%{$value}%'";
                    //$query .=$and.$key." LIKE '%$value%'";
                    //$queryCount.=$and.$key." LIKE '%$value%'";
                }
                $or = ' OR ';   
                $and = ' AND ';
            }            
        }
        

        
        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);

            if($totalRows['total']%$data['numberOfRows']==0)
            {
                $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
            }else
            {
                $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
            }            
            
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }
            if(!empty($query1)){
                $query .=" WHERE ".$query1." LIMIT $start,$numberOfRows";
            }
            //$query .=" LIMIT $start,$numberOfRows";
        }

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    

    /**
     * get report Warranty
     */
    public function reportWarranty($data)
    {
        $res = array();
        $listName_Columns ="";
        $filter_submitter=0;
        foreach($data['customColumns'] as $column){
            if($column!="submitter"){
                $listName_Columns .=empty($listName_Columns)?"":",";
                $listName_Columns .=$column;
            }else{
                $filter_submitter=1;
            }
        }
        $listNameColumns = implode(",",$listName_Columns);

        $query = "SELECT ID,warranty_submitter_type,buyer,warranty_buyer_agent_name,
        warranty_seller_agent_name,warranty_escrow_name,warranty_mortgage_name,
        {$listName_Columns} FROM report_warranty ";
        $queryCount = "SELECT COUNT(*) as total FROM report_warranty ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";
        $value_order ='';
        $value_type ='';
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        if($key=='warranty_buyer_id' || $key=='warranty_salesman_id' ||
                            $key=='warranty_create_by'){
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." = '{$arrayKey}'";
                        }else{
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." LIKE '%{$arrayKey}%'";
                        }

                        //$query .=$or.$key." LIKE '%$arrayKey%'";
                        //$queryCount.=$or.$key." LIKE '%$arrayKey%'";
                    }
                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {
                    if($key=='warranty_buyer_id' || $key=='warranty_salesman_id' ||
                        $key=='warranty_create_by'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." = '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." = '{$value}'";
                    }elseif($key=='warranty_order_id'){
                        $value_order=$value;
                    }elseif($key=='warranty_type'){
                        $value_type=$value;
                    }elseif($key=='warranty_contract_amount_min'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="warranty_contract_amount >= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="warranty_contract_amount >= '{$value}'";
                    }elseif($key=='warranty_contract_amount_max'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="warranty_contract_amount <= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="warranty_contract_amount <= '{$value}'";
                    }
                    else{
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." LIKE '%{$value}%'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." LIKE '%{$value}%'";
                    }

                   // $query .=$and.$key." LIKE '%$value%'";
                   // $queryCount.=$and.$key." LIKE '%$value%'";
                }
                //$or = ' OR ';
               // $and = ' AND ';
            }            
        }
        
        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];
            /*$query .=$and." warranty_creation_date < '$createTimeStartDate'";
            $queryCount.=$and." warranty_creation_date > '$createTimeStartDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_creation_date >= '{$createTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_creation_date >= '{$createTimeStartDate}'";
        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
            /*$query .=$and." warranty_creation_date > '$createTimeEndDate'";
            $queryCount.=$and." warranty_creation_date > '$createTimeEndDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_creation_date <= '{$createTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_creation_date <= '{$createTimeEndDate}'";
        }

        if($data['updateTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['updateTimeStartDate'];
            /*$query .=$and." warranty_update_date < '$updateTimeStartDate'";
            $queryCount.=$and." warranty_update_date < '$updateTimeStartDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_update_date >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_update_date >= '{$updateTimeStartDate}'";
        }

        if($data['updateTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['updateTimeEndDate'];
            /*$query .=$and." warranty_update_date > '$updateTimeEndDate'";
            $queryCount.=$and." warranty_update_date > '$updateTimeEndDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_update_date <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_update_date <= '{$updateTimeEndDate}'";
        }       

        if($data['startTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['startTimeStartDate'];
            /*$query .=$and." warranty_start_date < '$updateTimeStartDate'";
            $queryCount.=$and." warranty_start_date < '$updateTimeStartDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_start_date >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_start_date >= '{$updateTimeStartDate}'";
        }

        if($data['startTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['startTimeEndDate'];
            /*$query .=$and." warranty_start_date > '$updateTimeEndDate'";
            $queryCount.=$and." warranty_start_date > '$updateTimeEndDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_start_date <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_start_date <= '{$updateTimeEndDate}'";
        }       

        if($data['endTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['endTimeStartDate'];
            /*$query .=$and." warranty_end_date < '$updateTimeStartDate'";
            $queryCount.=$and." warranty_end_date < '$updateTimeStartDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_end_date >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_end_date >= '{$updateTimeStartDate}'";
        }

        if($data['endTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['endTimeEndDate'];
            /*$query .=$and." warranty_end_date > '$updateTimeEndDate'";
            $queryCount.=$and." warranty_end_date > '$updateTimeEndDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_end_date <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_end_date <= '{$updateTimeEndDate}'";
        }       

        if($data['closingTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['closingTimeStartDate'];
            /*$query .=$and." warranty_closing_date < '$updateTimeStartDate'";
            $queryCount.=$and." warranty_closing_date < '$updateTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_closing_date >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_closing_date >= '{$updateTimeStartDate}'";
        }

        if($data['closingTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['closingTimeEndDate'];
            /*$query .=$and." warranty_closing_date > '$updateTimeEndDate'";
            $queryCount.=$and." warranty_closing_date > '$updateTimeEndDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_closing_date <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_closing_date <= '{$updateTimeEndDate}'";
        }
        //$value_order
        $order_title='';
        if(!empty($value_order)){
            $order_title= $this->getwarrantyID_orderTitle($value_order);
            if(!empty($order_title)){
                $query1 .=empty($query1)?"":" AND ";
                $query1 .=$order_title;

                $queryCount1 .=empty($queryCount1)?"":" AND ";
                $queryCount1 .=$order_title;
            }
        }
        //$value_type
        $w_type='';
        if(!empty($value_type)){
            $w_type= $this->getwarrantyID_type($value_type);
            if(!empty($w_type)){
                $query1 .=empty($query1)?"":" AND ";
                $query1 .=$w_type;

                $queryCount1 .=empty($queryCount1)?"":" AND ";
                $queryCount1 .=$w_type;
            }
        }

        //
        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }

            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['maxPages']=1;
            if($data['numberOfRows'] >0){
                if($totalRows['total']%$data['numberOfRows']==0)
                {
                    $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
                }else
                {
                    $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
                }
            }

            if(!isset($data['pages'])) $data['pages']=0;
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            if(!empty($query1)){
                $query .=" WHERE ".$query1;
            }/*else{
                $query .=" LIMIT $start,$numberOfRows";
            }*/

            if(!empty($numberOfRows)){
                $query .=" LIMIT $start,$numberOfRows";
            }

        }
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                //check and set submitter
                $row["submitter"]='';
                if($filter_submitter==1){
                    if($row["warranty_submitter_type"]==1){
                        $row["submitter"] = $row["buyer"];
                    }elseif($row["warranty_submitter_type"]==2){
                        $row["submitter"] = $row["warranty_buyer_agent_name"];
                    }elseif($row["warranty_submitter_type"]==3){
                        $row["submitter"] = $row["warranty_seller_agent_name"];
                    }elseif($row["warranty_submitter_type"]==4){
                        $row["submitter"] = $row["warranty_escrow_name"];
                    }elseif($row["warranty_submitter_type"]==5){
                        $row["submitter"] = $row["warranty_mortgage_name"];
                    }

                }

                $row["ID"]='<a href="/#ajax/warranty-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    /**
     * get report Warranty
     */
    public function reporDownloadCsvtWarranty($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT ID,{$listNameColumns} FROM report_warranty ";
        $queryCount = "SELECT COUNT(*) as total FROM report_warranty  ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";
        $value_order ='';
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        if($key=='warranty_buyer_id' || $key=='warranty_salesman_id'){
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." = '{$arrayKey}'";
                        }else{
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." LIKE '%{$arrayKey}%'";
                        }

                        //$query .=$or.$key." LIKE '%$arrayKey%'";
                        //$queryCount.=$or.$key." LIKE '%$arrayKey%'";
                    }

                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {
                    if($key=='warranty_buyer_id' || $key=='warranty_salesman_id'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." = '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." = '{$value}'";
                    }elseif($key=='warranty_order_id'){
                        $value_order=$value;
                    }else{
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." LIKE '%{$value}%'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." LIKE '%{$value}%'";
                    }

                    //$query .=$and.$key." LIKE '%$value%'";
                    //$queryCount.=$and.$key." LIKE '%$value%'";
                }
                //$or = ' OR ';
                //$and = ' AND ';
            }            
        }
        
        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];
            /*$query .=$and." warranty_creation_date < '$createTimeStartDate'";
            $queryCount.=$and." warranty_creation_date < '$createTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_creation_date >= '{$createTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_creation_date >= '{$createTimeStartDate}'";

        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
           /* $query .=$and." warranty_creation_date > '$createTimeEndDate'";
            $queryCount.=$and." warranty_creation_date > '$createTimeEndDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_creation_date <= '{$createTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_creation_date <= '{$createTimeEndDate}'";

        }

        if($data['updateTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['updateTimeStartDate'];
            /*$query .=$and." warranty_update_date < '$updateTimeStartDate'";
            $queryCount.=$and." warranty_update_date < '$updateTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_update_date >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_update_date >= '{$updateTimeStartDate}'";
        }

        if($data['updateTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['updateTimeEndDate'];
            /*$query .=$and." warranty_update_date > '$updateTimeEndDate'";
            $queryCount.=$and." warranty_update_date > '$updateTimeEndDate'";
            $and = ' AND ';*/

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_update_date <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_update_date <= '{$updateTimeEndDate}'";
        }       

        if($data['startTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['startTimeStartDate'];
            /*$query .=$and." warranty_start_date < '$updateTimeStartDate'";
            $queryCount.=$and." warranty_start_date < '$updateTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_start_date >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_start_date >= '{$updateTimeStartDate}'";
        }

        if($data['startTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['startTimeEndDate'];
            /*$query .=$and." warranty_start_date > '$updateTimeEndDate'";
            $queryCount.=$and." warranty_start_date > '$updateTimeEndDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_start_date <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_start_date <= '{$updateTimeEndDate}'";
        }       

        if($data['endTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['endTimeStartDate'];
            /*$query .=$and." warranty_end_date < '$updateTimeStartDate'";
            $queryCount.=$and." warranty_end_date < '$updateTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_end_date >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_end_date >= '{$updateTimeStartDate}'";
        }

        if($data['endTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['endTimeEndDate'];
            /*$query .=$and." warranty_end_date > '$updateTimeEndDate'";
            $queryCount.=$and." warranty_end_date > '$updateTimeEndDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_end_date <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_end_date <= '{$updateTimeEndDate}'";
        }       

        if($data['closingTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['closingTimeStartDate'];
            /*$query .=$and." warranty_closing_date < '$updateTimeStartDate'";
            $queryCount.=$and." warranty_closing_date < '$updateTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_closing_date >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_closing_date >= '{$updateTimeStartDate}'";
        }

        if($data['closingTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['closingTimeEndDate'];
            /*$query .=$and." warranty_closing_date > '$updateTimeEndDate'";
            $queryCount.=$and." warranty_closing_date > '$updateTimeEndDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" warranty_closing_date <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" warranty_closing_date <= '{$updateTimeEndDate}'";
        }

        $order_title='';
        if(!empty($value_order)){
            $order_title= $this->getwarrantyID_orderTitle($value_order);
            if(!empty($order_title)){
                $query1 .=empty($query1)?"":" AND ";
                $query1 .=$order_title;

                $queryCount1 .=empty($queryCount1)?"":" AND ";
                $queryCount1 .=$order_title;
            }
        }
        
        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['maxPages']=1;
            if($data['numberOfRows'] >0){
                if($totalRows['total']%$data['numberOfRows']==0)
                {
                    $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
                }else
                {
                    $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
                }
            }

            if(!isset($data['pages'])) $data['pages']=0;
            
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }
            if(!empty($query1)){
                $query .=" WHERE ".$query1;
            }

        }

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                //$row["ID"]='<a href="/#ajax/warranty-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    /**
     * get report reportInvoice
     */
    public function reportInvoice($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT ID,{$listNameColumns} FROM  report_invoice ";
        $queryCount = "SELECT COUNT(*) as total FROM  report_invoice ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";
        //die($query);
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    /*foreach ($value as $arrayKey) {
                        $query .=$or.$key." LIKE '%$arrayKey%'"; 
                        $queryCount.=$or.$key." LIKE '%$arrayKey%'"; 
                    }*/
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        $subquery .=empty($subquery)?"":" OR ";
                        $subquery .=$key." LIKE '%{$arrayKey}%'";
                    }
                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }


                }
                else
                {
                    if($key=='balance_min'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="balance >= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="balance >= '{$value}'";
                    }elseif($key=='payment_min'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="payment >= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="payment >= '{$value}'";
                    }elseif($key=='total_min'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="total >= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="total >= '{$value}'";
                    }
                    elseif($key=='total_max'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="total <= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="total <= '{$value}'";
                    }elseif($key=='balance_max'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="balance <= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="balance <= '{$value}'";
                    }elseif($key=='payment_max'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="payment <= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="payment <= '{$value}'";
                    }else{
                        /*$query .=$and.$key." LIKE '%$value%'";
                     $queryCount.=$and.$key." LIKE '%$value%'";
                     */
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." LIKE '%{$value}%'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." LIKE '%{$value}%'";
                    }
                    //////////////////////

                }
                $or = ' OR ';   
                $and = ' AND ';
            }            
        }

        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];
            /*$query .=$and." createTime < '$createTimeStartDate'";
            $queryCount.=$and." createTime < '$createTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" createTime >= '{$createTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" createTime >= '{$createTimeStartDate}'";
        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
            /*$query .=$and." createTime > '$createTimeEndDate'";
            $queryCount.=$and." createTime > '$createTimeEndDate'";
            $and = ' AND ';
            */
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" createTime <= '{$createTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" createTime <= '{$createTimeEndDate}'";
        }

        if($data['updateTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['updateTimeStartDate'];
            /*$query .=$and." updateTime < '$updateTimeStartDate'";
            $queryCount.=$and." updateTime < '$updateTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" updateTime >= '{$updateTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" updateTime >= '{$updateTimeStartDate}'";
        }

        if($data['updateTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['updateTimeEndDate'];
            /*$query .=$and." updateTime > '$updateTimeEndDate'";
            $queryCount.=$and." updateTime > '$updateTimeEndDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" updateTime <= '{$updateTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" updateTime <= '{$updateTimeEndDate}'";
        }

        if($data['paid_in_full']!="")
        {
            $paid_in_full = $data['paid_in_full'];
            /*$query .=$and." updateTime > '$updateTimeEndDate'";
            $queryCount.=$and." updateTime > '$updateTimeEndDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" paid_in_full <= '{$paid_in_full}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" paid_in_full <= '{$paid_in_full}'";
        }
         // print_r("123 ");die($query);
        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['maxPages']=1;

            if($data['numberOfRows'] >0){
                if($totalRows['total']%$data['numberOfRows']==0)
                {
                    $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
                }else
                {
                    $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
                }
            }

            
            $numberOfRows = $data['numberOfRows'];
            //if(empty($numberOfRows)) $numberOfRows=25;
            if(!isset($data['pages'])) $data['pages']=0;

            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            if(!empty($query1)){
                if(!empty($numberOfRows)){
                    $query .=" WHERE ".$query1." LIMIT $start,$numberOfRows";
                }else{
                    $query .=" WHERE ".$query1;
                }

            }

            //$query .=" LIMIT $start,$numberOfRows";
        }
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                $row["ID"]='<a href="/#ajax/invoice-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    /**
     * get report invoice
     */
    public function reportDownloadCsvInvoice($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT {$listNameColumns} FROM  report_invoice WHERE ";
        $queryCount = "SELECT COUNT(*) as total FROM  report_invoice WHERE ";
        $or='';
        $and='';           
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    foreach ($value as $arrayKey) {
                        $query .=$or.$key." LIKE '%$arrayKey%'"; 
                        $queryCount.=$or.$key." LIKE '%$arrayKey%'"; 
                    }
                }
                else
                {               
                    $query .=$and.$key." LIKE '%$value%'";
                    $queryCount.=$and.$key." LIKE '%$value%'";
                }
                $or = ' OR ';   
                $and = ' AND ';
            }            
        }
        
        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];
            $query .=$and." createTime < '$createTimeStartDate'";
            $queryCount.=$and." createTime < '$createTimeStartDate'";
            $and = ' AND ';
        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
            $query .=$and." createTime > '$createTimeEndDate'";
            $queryCount.=$and." createTime > '$createTimeEndDate'";
            $and = ' AND ';
        }

        if($data['updateTimeStartDate']!="")
        {
            $updateTimeStartDate = $data['updateTimeStartDate'];
            $query .=$and." updateTime < '$updateTimeStartDate'";
            $queryCount.=$and." updateTime < '$updateTimeStartDate'";
            $and = ' AND ';
        }

        if($data['updateTimeEndDate']!="")
        {
            $updateTimeEndDate = $data['updateTimeEndDate'];
            $query .=$and." updateTime > '$updateTimeEndDate'";
            $queryCount.=$and." updateTime > '$updateTimeEndDate'";
            $and = ' AND ';
        }       
        
        if($data['showAllRows']=="false")
        {                        
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);

            if($totalRows['total']%$data['numberOfRows']==0)
            {
                $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
            }else
            {
                $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
            }            
            
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            $query .=" LIMIT $start,$numberOfRows";
        }

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }
    /**
     * get report reportPayment
     */
    public function reportPayment($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT {$listNameColumns} FROM  report_pay_acc ";
        $queryCount = "SELECT COUNT(*) as total FROM  report_pay_acc ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";
        //die($query);
        foreach($data['key'] as $key => $value)
        {
            if($value !="")
            {
                if(is_array($value))
                {
                    /*foreach ($value as $arrayKey) {
                        $query .=$or.$key." LIKE '%$arrayKey%'";
                        $queryCount.=$or.$key." LIKE '%$arrayKey%'";
                    }*/
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        if($key=='invoice_id' || $key=='order_id' || $key=='order_id'){
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." = '{$arrayKey}'";
                        }else{
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." LIKE '%{$arrayKey}%'";
                        }

                    }
                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }


                }
                else
                {
                    if($key=='pay_amount_max'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="pay_amount <= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="pay_amount <= '{$value}'";
                    }elseif($key=='pay_amount_min'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="pay_amount >= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="pay_amount >= '{$value}'";
                    }elseif($key=='overage_max'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="overage <= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="overage <= '{$value}'";
                    }
                    elseif($key=='overage_min'){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="overage >= '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="overage >= '{$value}'";
                    }else{
                        /*$query .=$and.$key." LIKE '%$value%'";
                     $queryCount.=$and.$key." LIKE '%$value%'";
                     */
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." LIKE '%{$value}%'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." LIKE '%{$value}%'";
                    }
                    //////////////////////

                }
                $or = ' OR ';
                $and = ' AND ';
            }
        }
        //die($query);
        if($data['startPayDate']!="")
        {
            $createTimeStartDate = $data['startPayDate'];
            /*$query .=$and." createTime < '$createTimeStartDate'";
            $queryCount.=$and." createTime < '$createTimeStartDate'";
            $and = ' AND ';*/
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" pay_date >= '{$createTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" pay_date >= '{$createTimeStartDate}'";
        }

        if($data['endPayDate']!="")
        {
            $createTimeEndDate = $data['endPayDate'];
            /*$query .=$and." createTime > '$createTimeEndDate'";
            $queryCount.=$and." createTime > '$createTimeEndDate'";
            $and = ' AND ';
            */
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" pay_date <= '{$createTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" pay_date <= '{$createTimeEndDate}'";
        }

        // print_r("123 ");die($query);
        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['maxPages']=1;

            if($data['numberOfRows'] >0){
                if($totalRows['total']%$data['numberOfRows']==0)
                {
                    $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
                }else
                {
                    $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
                }
            }


            $numberOfRows = $data['numberOfRows'];
            //if(empty($numberOfRows)) $numberOfRows=25;
            if(!isset($data['pages'])) $data['pages']=0;

            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }
            if(!empty($query1)){
                if(!empty($numberOfRows)){
                    $query .=" WHERE ".$query1." LIMIT $start,$numberOfRows";
                }else{
                    $query .=" WHERE ".$query1;
                }

            }

            //$query .=" LIMIT $start,$numberOfRows";
        }
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                if(isset($row["invoice_id"])){
                    $row["invoice_id"]='<a target="_blank" href="/#ajax/invoice-form.php?id='.$row['invoice_id'].'">'.$row['invoice_id'].'</a>';
                }
                if(isset($row["order_id"])){
                    $row["order_id"]='<a target="_blank" href="/#ajax/order-form.php?id='.$row['order_id'].'">'.$row['order_id'].'</a>';
                }
                //$res[] = $row;
                //$row["ID"]='<a href="/#ajax/invoice-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                array_push($list, $row);
            }
        }

        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    
    /**
     * get report claim
     */
    public function reportClaim($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT {$listNameColumns} FROM  report_claim ";
        $queryCount = "SELECT COUNT(*) as total FROM  report_claim ";
        $or='';
        $and='';
        $query1="";
        $queryCount1="";
        $claim_id_string="";
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                   /* foreach ($value as $arrayKey) {
                        $query .=$or.$key." LIKE '%$arrayKey%'"; 
                        $queryCount.=$or.$key." LIKE '%$arrayKey%'"; 
                    }*/
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        $subquery .=empty($subquery)?"":" OR ";
                        $subquery .=$key." LIKE '%{$arrayKey}%'";
                    }
                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {
                    if($key=='UID'){
                        $claim_id_string = $this->criteriaVendorCompany($value);
                    }else{
                        /*$query .=$and.$key." LIKE '%$value%'";
                     $queryCount.=$and.$key." LIKE '%$value%'";
                     */
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." LIKE '%{$value}%'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." LIKE '%{$value}%'";
                    }

                }
                $or = ' OR ';   
                $and = ' AND ';
            }            
        }
        /*
        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" createTime >= '{$createTimeStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" createTime >= '{$createTimeStartDate}'";
        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" createTime <= '{$createTimeEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" createTime <= '{$createTimeEndDate}'";
        }
        */
        if($data['start_dateStartDate']!="")
        {
            $start_dateStartDate = $data['start_dateStartDate'];

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" start_date >= '{$start_dateStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" start_date >= '{$start_dateStartDate}'";
        }

        if($data['start_dateEndDate']!="")
        {
            $start_dateEndDate = $data['start_dateEndDate'];
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" start_date <= '{$start_dateEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" start_date <= '{$start_dateEndDate}'";
        }

        if($data['end_dateStartDate']!="")
        {
            $end_dateStartDate = $data['end_dateStartDate'];

            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" end_date >= '{$end_dateStartDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" end_date >= '{$end_dateStartDate}'";
        }

        if($data['end_dateEndDate ']!="")
        {
            $end_dateEndDate = $data['end_dateEndDate '];
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" end_date <= '{$end_dateEndDate}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" end_date <= '{$end_dateEndDate}'";
        }


        if($data['showAllRows']=="false")
        {
            if(!empty($claim_id_string)){
                $query1 .=empty($query1)?"":" AND ";
                $query1 .= " (ID IN ({$claim_id_string})) ";
                $queryCount1 .=empty($queryCount1)?"":" AND ";
                $queryCount1 .= "(ID IN ({$claim_id_string})) ";
            }

            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);

            $res['maxPages']=1;
            if($data['numberOfRows'] >0){
                if($totalRows['total']%$data['numberOfRows']==0)
                {
                    $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
                }else
                {
                    $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
                }
            }

            
            $numberOfRows = $data['numberOfRows'];
            if(empty($numberOfRows)) $numberOfRows=25;
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            //$query .=" LIMIT $start,$numberOfRows";
            if(!empty($query1)){
                $query .=" WHERE ".$query1." LIMIT $start,$numberOfRows";
            }
        }
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row; claim-form.php?id=42
                //$row["ID"]='<a href="/#ajax/claim-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                //if(isset($row['UID'])){
                    $rsl1 = $this->reportGetVendorsByIDs($row['UID']);
                    $row['UID'] =$rsl1["UIDs"];
                    $row['data_quote'] =$rsl1["quotedata"];
               // }

                $list[]=$row;
                //array_push($list, $row);
            }
        }
        //die();
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    /**
     * get report claim
     */
    public function reportDownloadCsvClaim($data)
    {
        $res = array();
        $listNameColumns = implode(",",$data['customColumns']);
        $query = "SELECT {$listNameColumns} FROM  report_claim WHERE ";
        $queryCount = "SELECT COUNT(*) as total FROM  report_claim WHERE ";
        $or='';
        $and='';           
        foreach($data['key'] as $key => $value) 
        {          
            if($value !="")
            {
                if(is_array($value))
                {
                    foreach ($value as $arrayKey) {
                        $query .=$or.$key." LIKE '%$arrayKey%'"; 
                        $queryCount.=$or.$key." LIKE '%$arrayKey%'"; 
                    }
                }
                else
                {               
                    $query .=$and.$key." LIKE '%$value%'";
                    $queryCount.=$and.$key." LIKE '%$value%'";
                }
                $or = ' OR ';   
                $and = ' AND ';
            }            
        }
        
        if($data['createTimeStartDate']!="")
        {
            $createTimeStartDate = $data['createTimeStartDate'];
            $query .=$and." createTime > '$createTimeStartDate'";
            $queryCount.=$and." createTime > '$createTimeStartDate'";
            $and = ' AND ';
        }

        if($data['createTimeEndDate']!="")
        {
            $createTimeEndDate = $data['createTimeEndDate'];
            $query .=$and." createTime < '$createTimeEndDate'";
            $queryCount.=$and." createTime < '$createTimeEndDate'";
            $and = ' AND ';
        }
        
        if($data['showAllRows']=="false")
        {                        
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);

            if($totalRows['total']%$data['numberOfRows']==0)
            {
                $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
            }else
            {
                $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
            }            
            
            $numberOfRows = $data['numberOfRows'];
            
            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }

            $query .=" LIMIT $start,$numberOfRows";
        }

        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                array_push($list, $row);
            }
        }
        
        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }


    /**
     * Save report
     */
    public function reportSave($data)
    {                
        $key = json_encode($data['data']['key']);
        $customColumns = json_encode($data['data']['customColumns']);
        $availableFields = json_encode($data['data']['availableFields']);
        $userID = $data['id'];
        $type = $data['type'];
        $date = new DateTime();

        $name = $date->format('U Y-m-d H:i:s');

        if($data['data']['name']!="")
        {
            $name = $data['data']['name'];
        }
        

        $insertCommand = "INSERT INTO save_report(`id`, `type`, `key`, `customColumns`, `availableFields`, `userID`, `name`) VALUES (NULL,'$type','$key','$customColumns','$availableFields','$userID','$name')";
        
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);
        $res['name'] = $data['data']['name'];;
        // $res['query'] = $insertCommand;
        $res['idreturn'] = $idreturn;

        return $res;
    }

    public function getOldReport($data)
    {
        $res = [];        
        $formType = $data['formType'];
        $userID = $data['userID'];
        $query = "SELECT * FROM  save_report WHERE type = '{$formType}' AND userID = '{$userID}' ORDER BY id DESC ";
        $rsl = mysqli_query($this->con,$query);
        $list = array();

        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {                
                foreach ($row as $key => $value) {
                    if($key !== "name")
                    {
                        $array[$key] = json_decode($value);
                    }else{
                        $array[$key] = $value;
                    }                    
                }
                array_push($list, $array);                
            }
        }

        // $res['query']=$query;
        // $res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    public function getAllZip($data)
    {
        $res = [];        
        $formType = $data['formType'];
        $formColumn = $data['formColumn'];
        $query = "SELECT ".$formColumn." FROM report_".$formType." GROUP BY ".$formColumn." ";
        $rsl = mysqli_query($this->con,$query);
        $list = array();

        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //foreach ($row as $key => $value) {
                    //$array[$row['zip']] = $row['zip'];
                //}
                array_push($list, $row);
            }
        }

        //$res['query']=$query;        
        $res['data']=$list;
        return $res;
    }

    public function getAllCity($data)
    {
        $res = [];        
        $formType = $data['formType'];
        $formColumn = $data['formColumn'];
        $query = "SELECT $formColumn FROM report_".$formType." GROUP BY $formColumn";
        $rsl = mysqli_query($this->con,$query);
        $list = array();

        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {                
                // foreach ($row as $key => $value) {                   
                //         $array[$value] = $value;                                   
                // }
                array_push($list, $row);                
            }
        }

        //$res['query']=$query;        
        $res['data']=$list;
        return $res;
    }

    public function getAllState($data)
    {
        $res = [];        
        $formType = $data['formType'];
        $formColumn = $data['formColumn'];
        $query = "SELECT $formColumn FROM report_".$formType." GROUP BY $formColumn";
        $rsl = mysqli_query($this->con,$query);
        $list = array();

        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {                
                // foreach ($row as $key => $value) {                   
                //         $array[$key] = $value;                                   
                // }
                // $array[$row['state']]=$row['county'];
                array_push($list, $row);                
            }
        }

        //$res['query']=$query;        
        $res['data']=$list;
        return $res;
    }

    public function getSearchSelectpicker($data)
    {
        $res = [];        
        $formType = $data['formType'];
        $formColumn = $data['formColumn'];
        $searchKey = $data['searchKey'];
        $query = "SELECT $formColumn FROM report_".$formType." WHERE $formColumn LIKE '%".$searchKey."%' GROUP BY $formColumn";
        $rsl = mysqli_query($this->con,$query);
        $list = array();

        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {                
                // foreach ($row as $key => $value) {                   
                //         $array[$key] = $value;                                   
                // }
                // $array[$row['state']]=$row['county'];
                array_push($list, $row);                
            }
        }

        // $res['query']=$query;        
        $res['data']=$list;
        return $res;
    }


    //----------------------------------------------------------
    public function getwarrantyID_orderTitle($title){
        $query ="Select order_id from orders
                Where order_title like '%{$title}%' AND order_title <>'' AND order_title IS NOT NULL ";

        $result = mysqli_query($this->con,$query);

        $order_condition='';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $order_condition .=empty($order_condition)?"":" OR ";
                $order_condition .="warranty_order_id LIKE  '%{$row["order_id"]}%'";
            }
        }

        if(!empty($order_condition)){
            return "(".$order_condition.")";
        }else{
            return "";
        }
    }

    //----------------------------------------------------------
    public function getwarrantyID_type($warranty_type){
        $query ="Select order_id from orders
                Where JSON_SEARCH(products_ordered, 'all', 'Warranty') IS NOT NULL";

        $result = mysqli_query($this->con,$query);

        $order_condition='';
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $order_condition .=empty($order_condition)?"":" OR ";
                $order_condition .="warranty_order_id LIKE  '%{$row["order_id"]}%'";
            }
        }

        if(!empty($order_condition)){
            return "(".$order_condition.")";
        }else{
            return "";
        }
    }

    /**
     * get Discount
     */
    public function reportDiscount($data)
    {
        $res = array();
        $is_discount_code= false;
        $listNameColumns = implode(",",$data['customColumns']);
        if(!in_array('discount_code',$data['customColumns'])){
            $listNameColumns .=",discount_code";
            $is_discount_code=true;
       }

        $query = "SELECT ID,{$listNameColumns} FROM  discount ";
        $queryCount = "SELECT COUNT(*) as total FROM  discount ";
        $query1="";
        $queryCount1="";

        foreach($data['key'] as $key => $value)
        {
            if($value !="")
            {
                if(is_array($value))
                {
                    $subquery="";
                    foreach ($value as $arrayKey) {
                        if($key=='excludesive_offer' || $key=='active' || $key=='nerver_expired'){
                            if(empty($arrayKey)) $arrayKey=0;

                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." = '{$arrayKey}'";
                        }else{
                            $subquery .=empty($subquery)?"":" OR ";
                            $subquery .=$key." LIKE '%{$arrayKey}%'";
                        }
                    }
                    if(!empty($subquery)){
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .="(".$subquery.")";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .="(".$subquery.")";
                    }
                }
                else
                {
                    if($key=='excludesive_offer' || $key=='active' || $key=='nerver_expired'){
                        if(empty($value)) $value=0;
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." = '{$value}'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." = '{$value}'";
                    }else{
                        $query1 .=empty($query1)?"":" AND ";
                        $query1 .=$key." LIKE '%{$value}%'";

                        $queryCount1 .=empty($queryCount1)?"":" AND ";
                        $queryCount1 .=$key." LIKE '%{$value}%'";
                    }
                }

            }
        }

        if($data['start_date_start']!="")
    {
        $start_date_start = $data['start_date_start'];
        $query1 .=empty($query1)?"":" AND ";
        $query1 .=" start_date >= '{$start_date_start}'";

        $queryCount1 .=empty($queryCount1)?"":" AND ";
        $queryCount1 .=" start_date >= '{$start_date_start}'";
    }

        if($data['start_date_end']!="")
        {
            $start_date_end = $data['start_date_end'];
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" start_date <= '{$start_date_end}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" start_date <= '{$start_date_end}'";
        }

        if($data['stop_date_start']!="")
        {
            $stop_date_start = $data['stop_date_start'];
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" stop_date >= '{$stop_date_start}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" stop_date >= '{$stop_date_start}'";
        }

        if($data['stop_date_end']!="")
        {
            $stop_date_end = $data['stop_date_end'];
            $query1 .=empty($query1)?"":" AND ";
            $query1 .=" stop_date <= '{$stop_date_end}'";

            $queryCount1 .=empty($queryCount1)?"":" AND ";
            $queryCount1 .=" stop_date <= '{$stop_date_end}'";
        }

        if($data['showAllRows']=="false")
        {
            if(!empty($queryCount1)){
                $queryCount .=" WHERE ".$queryCount1;
            }

            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['total']=$totalRows['total'];
            if($totalRows['total']%$data['numberOfRows']==0)
            {
                $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
            }else
            {
                $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
            }

            $numberOfRows = $data['numberOfRows'];

            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }
            if(!empty($query1)){
                $query .=" WHERE ".$query1." LIMIT $start,$numberOfRows";
            }

        }else{
            $rsl = mysqli_query($this->con,$queryCount);
            $totalRows  = mysqli_fetch_assoc($rsl);
            $res['total']=$totalRows['total'];
            if($totalRows['total']%$data['numberOfRows']==0)
                if($totalRows['total']%$data['numberOfRows']==0)
                {
                    $res['maxPages']=($totalRows['total']/$data['numberOfRows'])-1;
                }else
                {
                    $res['maxPages']=$totalRows['total']/$data['numberOfRows'];
                }

            $numberOfRows = $data['numberOfRows'];

            $start = ($numberOfRows*$data['pages'])-1;
            if($start<0)
            {
                $start=0;
            }
        }
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                //$res[] = $row;
                //$row["ID"]='<a href="/#ajax/contact-form.php?id='.$row['ID'].'">'.$row['ID'].'</a>';
                $row["order"]= $this->getOrder_discountCode($row['discount_code']);
                if($is_discount_code){
                    unset($row["discount_code"]);
                }
                array_push($list, $row);
            }
        }

        //$res['query']=$query;
        //$res['postData']=$data;
        $res['data']=$list;
        return $res;
    }

    //------------------------------------------------------------
    public function getOrder_discountCode($discount_code)
    {
        $sqlText ="select order_id,order_title,payment,balance,total,
                   (total-payment) as paid
                  from orders
                  where discount_code='{$discount_code}'";
        $result = mysqli_query($this->con,$sqlText);
        $list=array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
    //------------------------------------------------------------
    public function getOrder_affilate($affs){
        $st_arr = explode(',',$affs);

        $list_g='';
        //Real Estate Agent
        if((in_array("Real Estate Agent", $st_arr))){
            $l_Real_Estate_Agent= $this->get_AUIID_affType("Real Estate Agent");
            if(!empty($l_Real_Estate_Agent)){
                $list_g .=empty($list_g)?'':',';
                $list_g .= $l_Real_Estate_Agent;

            }
        }
         //Mortgage
        if ((in_array("Mortgage", $st_arr))){
            $l_Real_Mortgage= $this->get_AUIID_affType("Mortgage");
            if(!empty($l_Real_Mortgage)){
                $list_g .=empty($list_g)?'':',';
                $list_g .= $l_Real_Mortgage;
            }
        }

        //Title
        if ((in_array("Title", $st_arr))){
            $l_Title= $this->get_AUIID_affType("Title");
            if(!empty($l_Title)){
                $list_g .=empty($list_g)?'':',';
                $list_g .= $l_Title;

            }
        }

        return $list_g;
    }

    //------------------------------------------------------------
    public function get_AUIID_affType($affs){
        $list='';

        if(!(empty($affs))){
            if($affs=="Real Estate Agent"){
                $w_query="SELECT DISTINCT warranty_order_id
            FROM warranty  WHERE ((warranty_buyer_agent_id <> '' AND warranty_buyer_agent_id IS NOT NULL) AND warranty_submitter_type ='2') OR
            ((warranty_seller_agent_id <>'' AND  warranty_seller_agent_id IS NOT NULL) AND warranty_submitter_type ='3')";

            }else if($affs=="Mortgage"){
                $w_query="SELECT DISTINCT warranty_order_id
            FROM warranty  WHERE (warranty_mortgage_id <> '' AND  warranty_mortgage_id IS NOT NULL) AND
            (warranty_submitter_type ='5')";
            }elseif($affs=="Title"){
                $w_query="SELECT DISTINCT warranty_order_id
            FROM warranty  WHERE (warranty_escrow_id <>'' AND warranty_escrow_id IS NOT NULL) AND
            (warranty_submitter_type ='4') ";
            }

            $result = mysqli_query($this->con,$w_query);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $list .=empty($list)?'':',';
                    $list .= $row['warranty_order_id'];
                }
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function criteriaVendorCompany($filter){
        $criteria_vendor='';
        $criteria_company='';
        $criteria_claimid='';
        $filter_arr = explode(",",$filter);
        foreach($filter_arr as $item){
            //company
            $p= stripos($item,"c");
            if(is_numeric($p)){
                $companyid_temp = substr($item,1);
                $criteria_company .=empty($criteria_company)?"":",";
                $criteria_company .=$companyid_temp;
            }

            //vendor
            $p1= stripos($item,"v");
            if(is_numeric($p1)){
                $venorid_temp = substr($item,1);
                $criteria_vendor .=empty($criteria_vendor)?"":",";
                $criteria_vendor .=$venorid_temp;
            }

        }//end foreach
        //get claim by companyID or vendor ID
        $criteria ="";
        if(!empty($criteria_company)){
            $criteria =" (typeID IN ({$criteria_company}) AND type='company') ";
        }

        if(!empty($criteria_vendor)){
            $criteria .=empty($criteria)?"":" OR ";
            $criteria .=" (typeID IN ({$criteria_vendor}) AND type='vendor') ";
        }

        if(!empty($criteria)){
            $query ="SELECT DISTINCT claimID FROM claim_quote
            WHERE ".$criteria;

            $result = mysqli_query($this->con,$query);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $criteria_claimid .=empty($criteria_claimid)?'':',';
                    $criteria_claimid .= $row['claimID'];
                }
            }
        }//END IF

        return $criteria_claimid;
    }

    //------------------------------------------------------------------
    public function reportGetVendorsByIDs($IDs)
    {
        $uid_list = array();
        $quotedata_list = array();
        $IDs = json_decode($IDs,true);
        if(count($IDs)>0){
            $IDs = implode(",",$IDs);
        }else{
            $IDs = 0;
        }
        $query = "SELECT id,claimID,type,typeID,quote
        FROM claim_quote
        Where id IN ({$IDs})";
        $result = mysqli_query($this->con,$query);
        //print_r("---");print_r($query);

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if($row['type']=='company'){
                    $uid_list[]=$this->reportGetVendorUIDs_type($row['typeID'],'company');
                }elseif($row['type']=='vendor'){
                    $uid_list[]=$this->reportGetVendorUIDs_type($row['typeID'],'vendor');
                }
                $row['quote']=json_decode($row['quote'],true);
                $quotedata_list[]=$row;
            }
        }

        return array("UIDs"=>$uid_list,"quotedata"=>$quotedata_list);

    }
    //------------------------------------------------
    public function reportGetVendorUIDs_type($typeID,$type) {
        $list = array();
        //company
        if($type=="company"){
            $query = "SELECT ID as id,name as full_name,state,city,
            if(w9_exp <= NOW(),'Yes','No') as expired,w9_exp,
            if(license_exp <= NOW(),'Yes','No') as license_expired,license_exp,
            if(insurrance_exp <= NOW(),'Yes','No') as insurrance_expired,insurrance_exp
            FROM  company_short
            where ID = '{$typeID}'";

            $result = mysqli_query($this->con,$query);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row["type"]="company";
                    $row["id"]="c".$row["id"];
                    $list = $row;
                }

            }
        }elseif($type=="vendor"){
            //Contact
            $query = "SELECT ID as id,contact_name as full_name,primary_state as state,
      primary_city as city,if(w9_exp <= NOW(),'Yes','No') as expired,w9_exp,
      if(license_exp <= NOW(),'Yes','No') as license_expired,license_exp,
      if(insurrance_exp <= NOW(),'Yes','No') as insurrance_expired,insurrance_exp
        FROM  contact_short
        where ID = '{$typeID}'";

            $result = mysqli_query($this->con,$query);

            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row["type"]="vendor";
                    $row["id"]="v".$row["id"];
                    $list = $row;
                }
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getInvoicesIDs($ID){
        $query = "SELECT ID as id, ID as text
         FROM  invoice
         WHERE ID LIKE '%{$ID}%'";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }
//------------------------------------------------------------------
    public function getOderOrder_ids($ID){
        $query = "SELECT order_id as id, order_id as text
         FROM  orders
         WHERE order_id LIKE '%{$ID}%'";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getInvoicesNumbers($inv_number){
        $query = "SELECT ID as id, invoiceid as text
         FROM  invoice
         WHERE invoiceid LIKE '%{$inv_number}%'";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function getOderOrderTitle_title($order_title){
        $query = "SELECT order_title as id, order_title as text
         FROM  orders
         WHERE order_title LIKE '%{$order_title}%'";
        $result = mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }
    ///
}