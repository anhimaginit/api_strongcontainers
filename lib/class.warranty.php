<?php

require_once 'class.common.php';
require_once 'class.task.php';
//require_once 'class.payment.php';
class Warranty extends Common{

    //--------------------------------------------------------------
    public function validate_warranty_fields($warranty_order_id,$warranty_address1,$warranty_city,$warranty_state,
                                             $warranty_postal_code,$warranty_buyer_id,$warranty_salesman_id,$warranty_start_date=null
                                             )
    {
        $error = false;
        $errorMsg = "";
        //--- $token
        if(!$error && empty($warranty_order_id)){
            $error = true;
            $errorMsg = "Order is required.";
        }

        if(!$error && empty($warranty_address1)){
            $error = true;
            $errorMsg = "Warranty address is required.";
        }

        /*
        if(!$error && empty($warranty_city)){
            $error = true;
            $errorMsg = "Warranty city is required.";
        }


        if(!$error && empty($warranty_state)){
            $error = true;
            $errorMsg = "Warranty state is required.";
        }

        if(!$error && empty($warranty_postal_code)){
            $error = true;
            $errorMsg = "Postal code order is required.";
        }*/

        if(!$error && empty($warranty_buyer_id)){
            $error = true;
            $errorMsg = "Warranty buyer is required.";
        }


        /*if(!$error && empty($warranty_start_date)){
            $error = true;
            $errorMsg = "Warranty start date is required.";
        }*/

        /*if(!$error && empty($warranty_email)){
            $error = true;
            $errorMsg = "Email is required.";
        }*/

        return array('error'=>$error,'errorMsg'=>$errorMsg);
    }
    //------------------------------------------------------------------
    public function addWarranty($warranty_address1,$warranty_address2,$warranty_buyer_agent_id,$warranty_buyer_id,$warranty_city,
                            $warranty_creation_date,$warranty_end_date,$warranty_escrow_id,$warranty_inactive,
                            $warranty_length,$warranty_mortgage_id,$warranty_notes,$warranty_order_id,
                            $warranty_postal_code,$warranty_renewal,$warranty_salesman_id,
                            $warranty_seller_agent_id,$warranty_serial_number,$warranty_start_date,
                            $warranty_state,$warranty_update_by,$warranty_update_date,
                            $warranty_charity_of_choice,$warranty_closing_date,$warranty_contract_amount,$notes,$limits=null,$warranty_eagle=null,
                            $warranty_create_by=null,$warranty_type=null,$warranty_payer_type=null,
                            $warranty_corporate=null,
                            $warranty_submitter=null,$warranty_submitter_type=null,$contract_overage=null)
    {
        if(!empty($warranty_order_id)){
            $warranty_order_id = preg_replace('/\s+/','',$warranty_order_id);
            $warranty_order_id= trim($warranty_order_id,",");

        }

        $dateTemp = date("Y-m-d");

        $warranty_payer =$warranty_buyer_id;
        if($warranty_payer_type==1){
            $warranty_payer =$warranty_buyer_id;
        }elseif($warranty_payer_type==2){
            $warranty_payer =$warranty_buyer_agent_id;
        }elseif($warranty_payer_type==3){
            $warranty_payer =$warranty_seller_agent_id;
        }elseif($warranty_payer_type==4){
            $warranty_payer =$warranty_escrow_id;
        }elseif($warranty_payer_type==5){
            $warranty_payer =$warranty_mortgage_id;
        }

        if($warranty_payer ==$warranty_buyer_id) $warranty_payer_type=1;
        //Check Affiliate
        if(!empty($warranty_seller_agent_id)){
            $active =1;
            $aff_type ="Real Estate Agent";
            $this->updateContactType($warranty_seller_agent_id,"Affiliate");
            $this->updateAffiliate($warranty_seller_agent_id,$active,$aff_type);
        }

        if(!empty($warranty_escrow_id)){
            $active =1;
            $aff_type ="Title";
            $this->updateContactType($warranty_escrow_id,"Affiliate");
            $this->updateAffiliate($warranty_escrow_id,$active,$aff_type);
        }

        if(!empty($warranty_mortgage_id)){
            $active =1;
            $aff_type ="Mortgage";
            $this->updateContactType($warranty_mortgage_id,"Affiliate");
            $this->updateAffiliate($warranty_mortgage_id,$active,$aff_type);
        }

        if(!empty($warranty_buyer_agent_id)){
            $active =1;
            $aff_type ="Real Estate Agent";
            $this->updateContactType($warranty_buyer_agent_id,"Affiliate");
            $this->updateAffiliate($warranty_buyer_agent_id,$active,$aff_type);
        }

        //End Affiliate
        //Get AID
        $seller_agent_id = $this->getAffAgent_UID($warranty_seller_agent_id,"affiliate");
        $buyer_agent_id = $this->getAffAgent_UID($warranty_buyer_agent_id,"affiliate");
        $escrow_id = $this->getAffAgent_UID($warranty_escrow_id,"affil_title");
        $mortgage_id = $this->getAffAgent_UID($warranty_mortgage_id,"affil_mortgage");

        $fields = "warranty_address1,warranty_address2,warranty_buyer_agent_id,warranty_buyer_id,warranty_city,
                            warranty_creation_date,warranty_end_date,warranty_escrow_id,warranty_inactive,
                            warranty_length,warranty_mortgage_id,warranty_notes,warranty_order_id,
                            warranty_postal_code,warranty_renewal,warranty_salesman_id,
                            warranty_seller_agent_id,warranty_serial_number,warranty_start_date,
                            warranty_state,warranty_update_by,
                            warranty_charity_of_choice,warranty_contract_amount,warranty_claim_limit,warranty_eagle,
                            warranty_create_by,warranty_type,warranty_payer_type,warranty_payer,warranty_corporate";

        $values = "'{$warranty_address1}','{$warranty_address2}','{$buyer_agent_id}','{$warranty_buyer_id}','{$warranty_city}',
                '{$dateTemp}','{$warranty_end_date}','{$escrow_id}','{$warranty_inactive}',
                '{$warranty_length}','{$mortgage_id}','{$warranty_notes}','{$warranty_order_id}',
                '{$warranty_postal_code}','{$warranty_renewal}','{$warranty_salesman_id}',
                '{$seller_agent_id}','{$warranty_serial_number}','{$warranty_start_date}',
                '{$warranty_state}','{$warranty_update_by}',
                '{$warranty_charity_of_choice}','{$warranty_contract_amount}','{$limits}',
                '{$warranty_eagle}',
                '{$warranty_create_by}',
                '{$warranty_type}',
                '{$warranty_payer_type}','{$warranty_payer}','{$warranty_corporate}'";

        if(!empty($warranty_closing_date)){
            $fields .=",warranty_closing_date";
            $values .=",'{$warranty_closing_date}'";
        }

        if(is_numeric($warranty_submitter_type) && !empty($warranty_submitter_type) &&
            is_numeric($warranty_submitter) && !empty($warranty_submitter)){
            $fields .=",warranty_submitter_type";
            $values .=",'{$warranty_submitter_type}'";

            //$fields .=",warranty_submitter";
            //$values .=",'{$warranty_submitter}'";
        }

        $insertCommand = "INSERT INTO warranty({$fields}) VALUES({$values})";
        //print_r($insertCommand); die();

        // serial number is ready ?
        $alreadySerial = $this->existingWarrantyNo($warranty_serial_number);

        if($alreadySerial) return "The serial number doesn't already.";

        // order is already ?
        $orderID_arr = array(); $orderID1=""; $orderAlready=false;
        if(strpos($warranty_order_id,",")){
            $orderID_arr = explode(",",$warranty_order_id);
        }else{
            if(!empty($warranty_order_id)) $orderID_arr[] = $warranty_order_id;
        }
        //create  $order_id_add
        $order_id_add ="";

        foreach($orderID_arr as $item){
            if(!empty($item)){
                $orderID1 = $item;
                $order_id_add .= empty($order_id_add) ? "" : ",";
                $order_id_add .= "'{$item}'";

                if($this->checkOrderForWarranty($item)) {
                    $orderAlready=true;
                    break;
                }
            }
        }

        if($orderAlready) return "The order ".$orderID1." doesn't already.";
        //prod_class is warranty ?
        $number_orderwarranty=0;
        $flagClassWarrantyProd =false; $orderID="";
        $order_warrantyID='';
        foreach($orderID_arr as $item){
            if(!empty($item)){
                $orderID = $item;
                $prod_class = $this->checkClassWarrantyProd($item);
                if($prod_class) {
                    $flagClassWarrantyProd=true;
                    $number_orderwarranty=$number_orderwarranty+1;
                    $order_warrantyID=$item;
                    //break;
                }
            }
        }

        if(!$flagClassWarrantyProd) return "The order ".$orderID. " doesn't already for warranty.";
        if($number_orderwarranty>1) return "The order has more 1 warranty_product";
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);

        if($idreturn){
            //update order
            //update contact overage for order
            if(is_numeric($order_warrantyID)){
                //$paymentObject = new Payment();
                $oder_info = $this->getPaymentBalance_orderID1($order_warrantyID);
                //unset($paymentObject);
                $total_org = $oder_info["total"];
                if(!is_numeric($total_org)) $total_org=0;
                if(!is_numeric($contract_overage)) $contract_overage=0;
                $grand_total = $total_org + $contract_overage;
                $err_update_overage =$this->updateGrandTotalOverage_orderID($order_warrantyID,$contract_overage,$grand_total);
                if(!is_numeric($err_update_overage)){
                    return $err_update_overage;
                }
            }
            //tittle
            $order_title = "Warranty"."-".$warranty_address1;

            $err_temp = $this->upDateOrderTitle_orderIDs($order_id_add,$order_title,$idreturn);
            if(count($err_temp)>0){
               //mysqli_query($this->con,"DELETE FROM warranty WHERE ID = '{$idreturn}' ");
                return $err_temp;
            }

            //update submitter
            if(is_numeric($warranty_submitter_type) && !empty($warranty_submitter_type) &&
                is_numeric($warranty_submitter) && !empty($warranty_submitter)){

                $err_temp = $this->upSubmitterOrder_warrantyID($order_id_add,$warranty_submitter);
                if(!is_numeric($err_temp) && !empty($err_temp)){
                    //return $err_temp;
                }
            }

            //add notes
            $err = $this->add_notes($notes,$warranty_buyer_id,$idreturn);
            if(!is_numeric($err)){
                //err delete note and new warranty
                $deleteSQL = "DELETE FROM notes WHERE typeID = '{$idreturn}' AND LOWER(`type`)='warranty'";
                mysqli_query($this->con,$deleteSQL);

                //$deleteSQL = "DELETE FROM warranty WHERE ID = '{$idreturn}' ";
               // mysqli_query($this->con,$deleteSQL);

                return $err;

            }else{
                return $idreturn;
            }
        }else{
            return mysqli_error($this->con);
        }


    }


    //-------------------------------------------------
    public function updateWarranty($id,$warranty_address1,$warranty_address2,$warranty_buyer_agent_id,$warranty_buyer_id,$warranty_city,
                                   $warranty_creation_date,$warranty_end_date,$warranty_escrow_id,$warranty_inactive,
                                   $warranty_length,$warranty_mortgage_id,$warranty_notes,$warranty_order_id,
                                   $warranty_postal_code,$warranty_renewal,$warranty_salesman_id,
                                   $warranty_seller_agent_id,$warranty_serial_number,$warranty_start_date,
                                   $warranty_state,$warranty_update_by,$warranty_update_date,
                                   $warranty_charity_of_choice,$warranty_closing_date,$warranty_contract_amount, $notes,
                                   $limits,$old_warranty_address1=null,$warranty_eagle=null
                                    ,$warranty_type=null,$warranty_payer_type=null,$warranty_corporate=null,
                                   $warranty_submitter=null,$warranty_submitter_type=null,
                                    $contract_overage=null)
    {
        if(!empty($warranty_order_id)){
            $warranty_order_id = preg_replace('/\s+/','',$warranty_order_id);
            $warranty_order_id= trim($warranty_order_id,",");

        }

        //$dateTemp = new DateTime($warranty_update_date);
        //$dateTemp = $dateTemp->format("Y-m-d H:i:s");
        $dateTemp = date("Y-m-d");
        //warranty_creation_date = '{$warranty_creation_date}',

        $warranty_payer =$warranty_buyer_id;
        if($warranty_payer_type==1){
            $warranty_payer =$warranty_buyer_id;
        }elseif($warranty_payer_type==2){
            $warranty_payer =$warranty_buyer_agent_id;
        }elseif($warranty_payer_type==3){
            $warranty_payer =$warranty_seller_agent_id;
        }elseif($warranty_payer_type==4){
            $warranty_payer =$warranty_escrow_id;
        }elseif($warranty_payer_type==5){
            $warranty_payer =$warranty_mortgage_id;
        }

        if($warranty_payer ==$warranty_buyer_id) $warranty_payer_type=1;
        //Check Affiliate
        if(!empty($warranty_seller_agent_id)){
            $active =1;
            $aff_type ="Real Estate Agent";
            $this->updateContactType($warranty_seller_agent_id,"Affiliate");
            $this->updateAffiliate($warranty_seller_agent_id,$active,$aff_type);
        }

        if(!empty($warranty_escrow_id)){
            $active =1;
            $aff_type ="Title";
            $this->updateContactType($warranty_escrow_id,"Affiliate");
            $this->updateAffiliate($warranty_escrow_id,$active,$aff_type);
        }

        if(!empty($warranty_mortgage_id)){
            $active =1;
            $aff_type ="Mortgage";
            $this->updateContactType($warranty_mortgage_id,"Affiliate");
            $this->updateAffiliate($warranty_mortgage_id,$active,$aff_type);
        }

        if(!empty($warranty_buyer_agent_id)){
            $active =1;
            $aff_type ="Real Estate Agent";
            $this->updateContactType($warranty_buyer_agent_id,"Affiliate");
            $this->updateAffiliate($warranty_buyer_agent_id,$active,$aff_type);
        }

        //End Affiliate
        //Get AID
        $seller_agent_id = $this->getAffAgent_UID($warranty_seller_agent_id,"affiliate");
        $buyer_agent_id = $this->getAffAgent_UID($warranty_buyer_agent_id,"affiliate");
        $escrow_id = $this->getAffAgent_UID($warranty_escrow_id,"affil_title");
        $mortgage_id = $this->getAffAgent_UID($warranty_mortgage_id,"affil_mortgage");

        $updateCommand = "UPDATE `warranty`
                SET warranty_address1 = '{$warranty_address1}',
                warranty_address2 = '{$warranty_address2}',
                warranty_buyer_agent_id = '{$buyer_agent_id}',
                warranty_buyer_id = '{$warranty_buyer_id}',
                warranty_city ='{$warranty_city}',
                warranty_escrow_id = '{$escrow_id}',
                warranty_inactive = '{$warranty_inactive}',
                warranty_length = '{$warranty_length}',
                warranty_mortgage_id = '{$mortgage_id}',
                warranty_notes = '{$warranty_notes}',
                warranty_order_id = '{$warranty_order_id}',
                warranty_postal_code = '{$warranty_postal_code}',
                warranty_renewal = '{$warranty_renewal}',
                warranty_salesman_id = '{$warranty_salesman_id}',
                warranty_seller_agent_id = '{$seller_agent_id}',
                warranty_state = '{$warranty_state}',
                warranty_update_by = '{$warranty_update_by}',
                warranty_update_date ='{$dateTemp}',
                warranty_charity_of_choice ='{$warranty_charity_of_choice}',
                warranty_contract_amount ='{$warranty_contract_amount}',
                warranty_claim_limit='{$limits}',
                warranty_eagle ='{$warranty_eagle}',
                warranty_type='{$warranty_type}',
                warranty_payer_type='{$warranty_payer_type}',
                warranty_payer ='{$warranty_payer}',
                warranty_corporate ='{$warranty_corporate}'
                ";
        if(is_numeric($warranty_submitter_type) && !empty($warranty_submitter_type) &&
            is_numeric($warranty_submitter) && !empty($warranty_submitter)){
            $updateCommand .=",warranty_submitter_type = '{$warranty_submitter_type}'";
            //$updateCommand .=",warranty_submitter = '{$warranty_submitter}'";
        }

        if(is_numeric($warranty_submitter) && !empty($warranty_submitter)){
            //$updateCommand .=",warranty_submitter_type = '{$warranty_submitter_type}'";
            $updateCommand .=",warranty_submitter = '{$warranty_submitter}'";
        }

                $closing_Date="";
                $start_date ="";
                $end_date="";
                if(!empty($warranty_closing_date)){
                    $closing_Date= ",warranty_closing_date ='{$warranty_closing_date}' ";
                }

                if(!empty($warranty_start_date)){
                    $start_date= ",warranty_start_date ='{$warranty_start_date}' ";
                }

                if(!empty($warranty_end_date)){
                    $end_date= ",warranty_end_date ='{$warranty_end_date}' ";
                }

          $where ="WHERE ID = '{$id}'";
        $updateCommand .=$closing_Date.$start_date.$end_date.$where;

        $selectCommand ="SELECT COUNT(*) AS NUM FROM warranty WHERE `ID` = '{$id}' LIMIT 1";
        if (!$this->checkExists($selectCommand)) return "This id= ".$id." doesn't already";
        //get old orderid
        $query = "SELECT warranty_order_id FROM warranty Where `ID` = '{$id}'";
        $oderid_old = $this->getValue($query,0);

        // serial number is already ?
        $alreadySerail = $this->existingWarrantyNo($warranty_serial_number,$id);
        if($alreadySerail) return "The serial number doesn't already.";

        //convert $oderid_old to array
        $orderIDOld_arr = array();
        if(!empty($oderid_old)) $oderid_old= trim($oderid_old,",");

        if(strpos($oderid_old,",")){
            $orderIDOld_arr = explode(",",$oderid_old);
        }else{
            if(!empty($oderid_old)) $orderIDOld_arr[] = $oderid_old;
        }

        //convert $warranty_order_id to array
        $orderID_arr = array();
        if(strpos($warranty_order_id,",")){
            $orderID_arr = explode(",",$warranty_order_id);
        }else{
            if(!empty($warranty_order_id)) $orderID_arr[] = $warranty_order_id;
        }

        //get diff order_ids
        $orderIDDiff = array_diff($orderID_arr,$orderIDOld_arr);
        $oldOrderNotIn = array_diff($orderIDOld_arr,$orderID_arr);

        $orderOldUpdate="";
        //if Address was change needing update order title.
        //if($warranty_address1!=$old_warranty_address1){
            $orderOldUpdateTemp = array_diff($orderIDOld_arr,$oldOrderNotIn);

            foreach($orderOldUpdateTemp as $item){
                if(!empty($item)){
                    $orderOldUpdate .= empty($orderOldUpdate) ? "" : ",";
                    $orderOldUpdate .= $item;
                }
            }
        //}

        // order is already ?
        $orderForWarranty ="";
        $orderID1=""; $orderAlready=false;
        //is new order is already for warranty ?
        foreach($orderIDDiff as $item){
            if(!empty($item)){
                $orderID1 = $item;

                $orderForWarranty .= empty($orderForWarranty) ? "" : ",";
                $orderForWarranty .= $item;

                if($this->checkOrderForWarranty($item)) {
                    $orderAlready=true;
                    break;
                }
            }
        }

        if($orderAlready) return "The order doesn't already.";

        //only one product has warranty class in all orders,
        $number_orderwarranty=0;
        $flagClassWarrantyProd =false; $orderID="";$order_warrantyID='';
        foreach($orderID_arr as $item){
            if(!empty($item)){
                $orderID = $item;
                $prod_class = $this->checkClassWarrantyProd($item);
                if($prod_class) {
                    $flagClassWarrantyProd=true;
                    $number_orderwarranty=$number_orderwarranty+1;
                    $order_warrantyID=$item;
                    //break;
                }
            }
        }

        if(!$flagClassWarrantyProd) return "The order doesn't already for warranty.";
        if($number_orderwarranty>1) return "The order has more 1 warranty_product";
        //order is removed  ?
        $orderRemoveWarranty="";
        foreach($oldOrderNotIn as $item){
            if(!empty($item)){
                $orderRemoveWarranty .= empty($orderRemoveWarranty) ? "" : ",";
                $orderRemoveWarranty .= $item;
            }
        }

       $update = mysqli_query($this->con,$updateCommand);

        if($update){
            //remove warranty ID in order
            if(!empty($orderRemoveWarranty)){
                $updateOrder2 = "UPDATE `orders`
                SET warranty = 0,
                contract_overage=0
                WHERE order_id IN ({$orderRemoveWarranty})";
                mysqli_query($this->con,$updateOrder2);
                if(mysqli_error($this->con)) return mysqli_error($this->con);
            }

            //update order
            //update contact overage for order
            if(is_numeric($order_warrantyID)){
                //$paymentObject = new Payment();
                $oder_info = $this->getPaymentBalance_orderID1($order_warrantyID);
                //unset($paymentObject);
                $total_org = $oder_info["total"];
                if(!is_numeric($contract_overage)) $contract_overage=0;
                $grand_total = $total_org + $contract_overage;
                $err_update_overage =$this->updateGrandTotalOverage_orderID($order_warrantyID,$contract_overage,$grand_total);
                if(!is_numeric($err_update_overage)){
                    return $err_update_overage;
                }
            }
            //title
            if(!empty($orderForWarranty)){
                $order_title = "Warranty-".$warranty_address1;

                $err_temp = $this->upDateOrderTitle_orderIDs($orderForWarranty,$order_title,$id);
                if(count($err_temp)>0){
                    return $err_temp;
                }

                //update submitter
                if(is_numeric($warranty_submitter_type) && !empty($warranty_submitter_type) &&
                    is_numeric($warranty_submitter) && !empty($warranty_submitter)){

                    /*$err_temp = $this->upSubmitterOrder_warrantyID($orderForWarranty,$warranty_submitter);
                    if(!is_numeric($err_temp) && !empty($err_temp)){
                        //return $err_temp;
                    }*/
                }
            }
            //update title
            if(!empty($orderOldUpdate)){
                $order_title = "Warranty-".$warranty_address1;
                $err_temp = $this->upDateOrderTitle_orderIDs($orderOldUpdate,$order_title,$id);
                if(count($err_temp)>0){
                    return $err_temp;
                }

                //update submitter
                if(is_numeric($warranty_submitter_type) && !empty($warranty_submitter_type) &&
                    is_numeric($warranty_submitter) && !empty($warranty_submitter)){

                   /* $err_temp = $this->upSubmitterOrder_warrantyID($orderOldUpdate,$warranty_submitter);
                    if(!is_numeric($err_temp) && !empty($err_temp)){
                        //return $err_temp;
                    }*/
                }
            }


            $err = $this->update_notes($notes,$warranty_buyer_id,$id);

            if(is_numeric($err) && $err){
                return 1;
            }else{
                return $err;
            }
        }else{
            return mysqli_error($this->con);
        }
    }

    //-------------------------------------------------
    public function updateWarranty_notlogin($id,$warranty_address1,$warranty_address2,$warranty_buyer_agent_id,$warranty_buyer_id,$warranty_city,
                                   $warranty_creation_date,$warranty_end_date,$warranty_escrow_id,$warranty_inactive,
                                   $warranty_length,$warranty_mortgage_id,$warranty_notes,$warranty_order_id,
                                   $warranty_postal_code,$warranty_renewal,$warranty_salesman_id,
                                   $warranty_seller_agent_id,$warranty_serial_number,$warranty_start_date,
                                   $warranty_state,$warranty_update_by,$warranty_update_date,
                                   $warranty_charity_of_choice,$warranty_closing_date,$warranty_contract_amount, $notes,
                                   $limits,$old_warranty_address1=null,$warranty_eagle=null
        ,$warranty_type=null,$warranty_payer_type=null,
                                   $contract_overage=null)
    {
        if(!empty($warranty_order_id)){
            $warranty_order_id = preg_replace('/\s+/','',$warranty_order_id);
            $warranty_order_id= trim($warranty_order_id,",");

        }

        //$dateTemp = new DateTime($warranty_update_date);
        //$dateTemp = $dateTemp->format("Y-m-d H:i:s");
        $dateTemp = date("Y-m-d");
        //warranty_creation_date = '{$warranty_creation_date}',

        //$warranty_payer =0;
        $warranty_payer =$warranty_buyer_id;
        if($warranty_payer_type==1){
            $warranty_payer =$warranty_buyer_id;
        }elseif($warranty_payer_type==2){
            $warranty_payer =$warranty_buyer_agent_id;
        }elseif($warranty_payer_type==3){
            $warranty_payer =$warranty_seller_agent_id;
        }elseif($warranty_payer_type==4){
            $warranty_payer =$warranty_escrow_id;
        }elseif($warranty_payer_type==5){
            $warranty_payer =$warranty_mortgage_id;
        }
        /*
        if($warranty_payer_type==1){
            $warranty_payer =$warranty_buyer_id;
        }elseif($warranty_payer_type==2){
            $warranty_payer = $this->getUIDByAID($warranty_buyer_agent_id,"affiliate");
        }elseif($warranty_payer_type==3){
            $warranty_payer =$this->getUIDByAID($warranty_seller_agent_id,"affiliate");
        }elseif($warranty_payer_type==4){
            $warranty_payer =$this->getUIDByAID($warranty_escrow_id,"affil_title");
        }elseif($warranty_payer_type==5){
            $warranty_payer =$this->getUIDByAID($warranty_mortgage_id,"affil_title");
        }
        */
        //Check Affiliate
        if(!empty($warranty_seller_agent_id)){
            $active =1;
            $aff_type ="Real Estate Agent";
            $this->updateContactType($warranty_seller_agent_id,"Affiliate");
            $this->updateAffiliate($warranty_seller_agent_id,$active,$aff_type);
        }

        if(!empty($warranty_escrow_id)){
            $active =1;
            $aff_type ="Title";
            $this->updateContactType($warranty_escrow_id,"Affiliate");
            $this->updateAffiliate($warranty_escrow_id,$active,$aff_type);
        }

        if(!empty($warranty_mortgage_id)){
            $active =1;
            $aff_type ="Mortgage";
            $this->updateContactType($warranty_mortgage_id,"Affiliate");
            $this->updateAffiliate($warranty_mortgage_id,$active,$aff_type);
        }

        if(!empty($warranty_buyer_agent_id)){
            $active =1;
            $aff_type ="Real Estate Agent";
            $this->updateContactType($warranty_buyer_agent_id,"Affiliate");
            $this->updateAffiliate($warranty_buyer_agent_id,$active,$aff_type);
        }

        //End Affiliate
        //Get AID
        $seller_agent_id = $this->getAffAgent_UID($warranty_seller_agent_id,"affiliate");
        $buyer_agent_id = $this->getAffAgent_UID($warranty_buyer_agent_id,"affiliate");
        $escrow_id = $this->getAffAgent_UID($warranty_escrow_id,"affil_title");
        $mortgage_id = $this->getAffAgent_UID($warranty_mortgage_id,"affil_mortgage");

        $updateCommand = "UPDATE `warranty`
                SET warranty_address1 = '{$warranty_address1}',
                warranty_address2 = '{$warranty_address2}',
                warranty_buyer_agent_id = '{$buyer_agent_id}',
                warranty_buyer_id = '{$warranty_buyer_id}',
                warranty_city ='{$warranty_city}',
                warranty_escrow_id = '{$escrow_id}',
                warranty_inactive = '{$warranty_inactive}',
                warranty_length = '{$warranty_length}',
                warranty_mortgage_id = '{$mortgage_id}',
                warranty_notes = '{$warranty_notes}',
                warranty_order_id = '{$warranty_order_id}',
                warranty_postal_code = '{$warranty_postal_code}',
                warranty_renewal = '{$warranty_renewal}',
                warranty_salesman_id = '{$warranty_salesman_id}',
                warranty_seller_agent_id = '{$seller_agent_id}',
                warranty_state = '{$warranty_state}',
                warranty_update_by = '{$warranty_update_by}',
                warranty_update_date ='{$dateTemp}',
                warranty_charity_of_choice ='{$warranty_charity_of_choice}',
                warranty_contract_amount ='{$warranty_contract_amount}',
                warranty_claim_limit='{$limits}',
                warranty_eagle ='{$warranty_eagle}',
                warranty_type='{$warranty_type}',
                warranty_payer_type='{$warranty_payer_type}',
                warranty_payer ='{$warranty_payer}'";
        $closing_Date="";
        $start_date ="";
        $end_date="";
        if(!empty($warranty_closing_date)){
            $closing_Date= ",warranty_closing_date ='{$warranty_closing_date}' ";
        }

        if(!empty($warranty_start_date)){
            $start_date= ",warranty_start_date ='{$warranty_start_date}' ";
        }

        if(!empty($warranty_end_date)){
            $end_date= ",warranty_end_date ='{$warranty_end_date}' ";
        }

        $where ="WHERE ID = '{$id}'";
        $updateCommand .=$closing_Date.$start_date.$end_date.$where;

        $selectCommand ="SELECT COUNT(*) AS NUM FROM warranty WHERE `ID` = '{$id}' LIMIT 1";
        if (!$this->checkExists($selectCommand)) return "This id= ".$id." doesn't already";
        //get old orderid
        $query = "SELECT warranty_order_id FROM warranty Where `ID` = '{$id}'";
        $oderid_old = $this->getValue($query,0);

        // serial number is already ?
        $alreadySerail = $this->existingWarrantyNo($warranty_serial_number,$id);
        if($alreadySerail) return "The serial number doesn't already.";

        //convert $oderid_old to array
        $orderIDOld_arr = array();
        if(!empty($oderid_old)) $oderid_old= trim($oderid_old,",");

        if(strpos($oderid_old,",")){
            $orderIDOld_arr = explode(",",$oderid_old);
        }else{
            if(!empty($oderid_old)) $orderIDOld_arr[] = $oderid_old;
        }

        //convert $warranty_order_id to array
        $orderID_arr = array();
        if(strpos($warranty_order_id,",")){
            $orderID_arr = explode(",",$warranty_order_id);
        }else{
            if(!empty($warranty_order_id)) $orderID_arr[] = $warranty_order_id;
        }

        //get diff order_ids
        $orderIDDiff = array_diff($orderID_arr,$orderIDOld_arr);
        $oldOrderNotIn = array_diff($orderIDOld_arr,$orderID_arr);

        $orderOldUpdate="";
        //if Address was change needing update order title.
        //if($warranty_address1!=$old_warranty_address1){
        $orderOldUpdateTemp = array_diff($orderIDOld_arr,$oldOrderNotIn);

        foreach($orderOldUpdateTemp as $item){
            if(!empty($item)){
                $orderOldUpdate .= empty($orderOldUpdate) ? "" : ",";
                $orderOldUpdate .= $item;
            }
        }
        //}

        // order is already ?
        $orderForWarranty ="";
        $orderID1=""; $orderAlready=false;
        //is new order already for warranty ?
        foreach($orderIDDiff as $item){
            if(!empty($item)){
                $orderID1 = $item;

                $orderForWarranty .= empty($orderForWarranty) ? "" : ",";
                $orderForWarranty .= $item;

                if($this->checkOrderForWarranty($item)) {
                    $orderAlready=true;
                    break;
                }
            }
        }

        if($orderAlready) return "The order ".$orderID1." doesn't already.";

        //only one prod_class is warranty in all orders?
        $number_orderwarranty=0;
        $flagClassWarrantyProd =false; $orderID="";$order_warrantyID='';
        foreach($orderID_arr as $item){
            if(!empty($item)){
                $orderID = $item;
                $prod_class = $this->checkClassWarrantyProd($item);
                if($prod_class) {
                    $flagClassWarrantyProd=true;
                    $number_orderwarranty=$number_orderwarranty+1;
                    $order_warrantyID=$item;
                    //break;
                }
            }
        }

        if(!$flagClassWarrantyProd) return "The order ".$orderID. " doesn't already for warranty.";
        if($number_orderwarranty>1) return "The order has more 1 warranty_product";
        //order is removed  ?
        $orderRemoveWarranty="";
        foreach($oldOrderNotIn as $item){
            if(!empty($item)){
                $orderRemoveWarranty .= empty($orderRemoveWarranty) ? "" : ",";
                $orderRemoveWarranty .= $item;
            }
        }

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            //remove warranty ID in order
            if(!empty($orderRemoveWarranty)){
                $updateOrder2 = "UPDATE `orders`
                SET warranty = 0
                WHERE order_id IN ({$orderRemoveWarranty})";
                mysqli_query($this->con,$updateOrder2);
                if(mysqli_error($this->con)) return mysqli_error($this->con);
            }

            //update order
//update contact overage for order
            if(is_numeric($order_warrantyID)){
                //$paymentObject = new Payment();
                $oder_info = $this->getPaymentBalance_orderID1($order_warrantyID);
                //unset($paymentObject);
                $total_org = $oder_info["total"];
                if(!is_numeric($contract_overage)) $contract_overage=0;
                $grand_total = $total_org + $contract_overage;
                $err_update_overage =$this->updateGrandTotalOverage_orderID($order_warrantyID,$contract_overage,$grand_total);
                if(!is_numeric($err_update_overage)){
                    return $err_update_overage;
                }
            }
            //tittle
            if(!empty($orderForWarranty)){
                $order_title = "Warranty-".$warranty_address1;

                $err_temp = $this->upDateOrderTitle_orderIDs($orderForWarranty,$order_title,$id);
                if(count($err_temp)>0){
                    return $err_temp;
                }
            }
            //update title
            if(!empty($orderOldUpdate)){
                $order_title = "Warranty-".$warranty_address1;
                $err_temp = $this->upDateOrderTitle_orderIDs($orderForWarranty,$order_title,$id);
                if(count($err_temp)>0){
                    return $err_temp;
                }
            }


            $err = $this->update_notes($notes,$warranty_buyer_id,$id);

            if(is_numeric($err) && $err){
                return 1;
            }else{
                return $err;
            }
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------
    public function getWarrantyByID($ID) {
        $query = "SELECT
        w.ID,w.warranty_address1,w.warranty_address2,
        w.warranty_buyer_id,w.warranty_charity_of_choice,w.warranty_city,
        w.warranty_claim_limit,w.warranty_closing_date,w.warranty_contract_amount,
        w.warranty_creation_date,w.warranty_email,w.warranty_end_date,
        w.warranty_inactive,w.warranty_length,
        w.warranty_notes,w.warranty_order_id,w.warranty_phone,
        w.warranty_postal_code,w.warranty_renewal,
        w.warranty_serial_number,w.warranty_start_date,
        w.warranty_state,w.warranty_update_by,w.warranty_update_date,
        w.warranty_salesman_id,
        w.warranty_buyer_agent_id,
        w.warranty_seller_agent_id,
        w.warranty_escrow_id,
        w.warranty_mortgage_id,
        w.contact_salemanID,
        w.contact_buyerAgentID,
        w.contact_selerAgentID,
        w.contact_affTitleID,
        w.contact_mortgageID,
        w.buyer_name,
        w.buyer_email,
        w.buyer_phone,
        w.warranty_type,
        w.warranty_payer,
         w.warranty_payer_type,
         w.warranty_corporate,
         w.warranty_create_by,
          w.warranty_submitter_type,
         w.createby_name,
        concat(IFNULL(c1.first_name,''),' ',IFNULL(c1.last_name,'')) as saleman_name,
        concat(IFNULL(c2.first_name,''),' ',IFNULL(c2.last_name,'')) as buyer_agent_name,
        concat(IFNULL(c3.first_name,''),' ',IFNULL(c3.last_name,'')) as seller_agent_name,
        concat(IFNULL(c4.first_name,''),' ',IFNULL(c4.last_name,'')) as affTitle_name,
        concat(IFNULL(c5.first_name,''),' ',IFNULL(c5.last_name,'')) as mortgage_name

        FROM  warranty_detail AS w

        LEFT JOIN contact AS c1 on c1.ID = w.contact_salemanID
        LEFT JOIN contact AS c2 on c2.ID = w.contact_buyerAgentID
        LEFT JOIN contact AS c3 on c3.ID = w.contact_selerAgentID
        LEFT JOIN contact AS c4 on c4.ID = w.contact_affTitleID
        LEFT JOIN contact AS c5 on c5.ID = w.contact_mortgageID

        WHERE w.ID = '{$ID}'";
        //die($query);
        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['warranty_type'] = $this->getProductNameprodIDs($row['warranty_type']);
               // $row['payment_status'] =$this->checkSandboxPayment_wID($ID);

                if(!empty($row['warranty_buyer_agent_id'])){
                    $row['warranty_buyer_agent_id'] =$this->getUIDByAID($row['warranty_buyer_agent_id'],"affiliate");
                }

                if(!empty($row['warranty_seller_agent_id'])){
                    $row['warranty_seller_agent_id'] =$this->getUIDByAID($row['warranty_seller_agent_id'],"affiliate");
                }

                if(!empty($row['warranty_escrow_id'])){
                    $row['warranty_escrow_id'] =$this->getUIDByAID($row['warranty_escrow_id'],"affiliate");
                }

                if(!empty($row['warranty_mortgage_id'])){
                    $row['warranty_mortgage_id'] =$this->getUIDByAID($row['warranty_mortgage_id'],"affiliate");
                }

                //get grand total and contract ovarage warranty_order_id
                $arr_warranty_order_id= explode(",",$row["warranty_order_id"]);
                if(count($arr_warranty_order_id)>0){
                    $order_list= array();
                    foreach($arr_warranty_order_id as $ord_id){
                        $order_list[]=$this->getGrandTotalContractOverage_orderID($ord_id);
                    }
                    $row['order_list']=$order_list;
                }

                //

                $list[] = $row;
            }
        }
        return $list;
    }

    //Get sale name by contactID
    //------------------------------------------------------------
    public function getContactName($contactID)
    {
        $sqlText = "Select CONCAT(first_name,' ',last_name) as contact_name From contact
        where ID ='{$contactID}'";
        $result = mysqli_query($this->con,$sqlText);

        $name="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $name = $row['contact_name'];
            }
        }
        return $name;
    }

    //------------------------------------------------
    public function deleteWarranty($ID)
    {
        $deleteSQL = "DELETE FROM warranty WHERE ID = '{$ID}' ";
        $delete = mysqli_query($this->con,$deleteSQL);
        if($delete){
            return true;
        } else {
            return false;
        }
    }
    //------------------------------------------------
    public function warrantyTotal($columns=null,$filterAll=null,$role=null,$id_login=null)
    {
        $num =0;
        $criteria = "";

        if(!empty($filterAll)){
            $temp = $this->columnsFilterOr($columns,$filterAll);
            $criteria .="(".$temp.")";
        }

        $criteria1 =empty($criteria)?"":" AND ".$criteria;

        if(is_array($role)){
            $sqlText = "Select count(*)";

            if(!empty($criteria)){
                $sqlText .= " WHERE ".$criteria;
            }
            /*not delete
            $v = $this->protect($role[0]["department"]);
            $level = $this->protect($role[0]['level']);
            if($level=='Admin' && $v =='Sales' || $v=="SystemAdmin"){
                $sqlText = "Select count(*)";

                if(!empty($criteria)){
                    $sqlText .= " WHERE ".$criteria;
                }
            }else{
                if($v=="Sales" || $v=="Vendor" || $v=="Policy Holder" ||$v=="Employee"){
                    $sqlText ="Select count(*)
                        From warranty_short
                    where buyer_id = '{$id_login}'".$criteria1;
                }elseif($v=="Affiliate"){
                    $sqlText ="Select DISTINCT count(*)
                    From warranty_short
            where (warranty_escrow_id= '{$id_login}' ||  warranty_create_by = '{$id_login}' ||
            warranty_mortgage_id= '{$id_login}' ||
            warranty_seller_agent_id= '{$id_login}' || warranty_buyer_agent_id= '{$id_login}')".$criteria1;
                }
            } */
            $num = $this->totalRecords($sqlText,0);
        }

        //die($criteria);

        return $num;
    }

    //------------------------------------------------
    public function searchWarrantyList($columns=null,$filterAll=null,$limit,$offset,$role=null,$id_login=null)
    {
        $list = array();

        $criteria = "";

        if(!empty($filterAll)){
            $temp = $this->columnsFilterOr($columns,$filterAll);
            $criteria .="(".$temp.")";
        }

        $criteria1 =empty($criteria)?"":" AND ".$criteria;

        $v = $this->protect($role[0]["department"]);
        $level = $this->protect($role[0]['level']);
        if(is_array($role)){
            if($v=="Employee" || $v=="SystemAdmin" || $v=="Sales"){
                $sqlText = "Select DISTINCT `ID`,`warranty_order_id`,`buyer`,`salesman`,
            `warranty_address1`,`warranty_start_date`,`warranty_end_date`,
            `warranty_type`,`warranty_creation_date`
             From warranty_short";

                if(!empty($criteria)){
                    $sqlText .= " WHERE ".$criteria;
                }
            }elseif($v=="Vendor" || $v=="Policy Holder" || $v=="Customer"){
                $sqlText ="Select DISTINCT ID,warranty_order_id,buyer,salesman,
        warranty_start_date,warranty_end_date,warranty_address1,`warranty_type`,'warranty_creation_date'
            From warranty_short
        where (buyer_id = '{$id_login}' OR warranty_create_by = '{$id_login}') ".$criteria1;
            }elseif($v=="Affiliate"){
                $sqlText ="Select DISTINCT ID,warranty_order_id,buyer,salesman,
        warranty_start_date,warranty_end_date,warranty_address1,`warranty_type`
            From warranty_short
            where (
            af_s_contactID= '{$id_login}' OR af_b_contactID= '{$id_login}' OR
            af_m_contactID= '{$id_login}' OR af_t_contactID= '{$id_login}')".$criteria1;
            }
            /*
            elseif($v=="Sales"){
                $sqlText ="Select DISTINCT ID,warranty_order_id,buyer,salesman,
        warranty_start_date,warranty_end_date,warranty_address1,`warranty_type`,'warranty_creation_date'
            From warranty_short
        where (UID = '{$id_login}' OR warranty_create_by = '{$id_login}') ".$criteria1;
            }*/
            ///

            $sqlText .= " ORDER BY ID DESC";

            if(!empty($limit)){
                $sqlText .= " LIMIT {$limit} ";
            }
            if(!empty($offset)) {
                $sqlText .= " OFFSET {$offset} ";
            }

            //die($sqlText);

            $result = mysqli_query($this->con,$sqlText);
            if($result){
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['warranty_type'] = $this->getProductNameprodIDs($row['warranty_type']);
                    $row['order'] = $this->getOrderTitle_orderID($row['warranty_order_id']);
                    $list[] = $row;
                }
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function existingWarrantyNo($warranty_serial_number,$id=null) {
        if(empty($id)){
            $query = "SELECT count(*) FROM  warranty WHERE warranty_serial_number = '{$warranty_serial_number}' LIMIT 1";
        }else{
            $query = "SELECT count(*) FROM  warranty WHERE warranty_serial_number = '{$warranty_serial_number}' AND ID <> '{$id}' LIMIT 1";
        }

        $check = mysqli_query($this->con,$query);
        $row = mysqli_fetch_row($check);
        //$num = $this->totalRow($query,0);
        if ($row[0] > 0)
            return true;
        else
            return false;
    }

    //------------------------------------------------
    public function checkOrderForWarranty($order_id,$warrantyID=null) {
        if(empty($warrantyID)){
            $query = "SELECT count(*) FROM  orders WHERE order_id = '{$order_id}' AND warranty <> 0 LIMIT 1";
        }else{
            $query = "SELECT count(*) FROM  orders WHERE order_id = '{$order_id}' AND (warranty <> '{$warrantyID}' AND warranty <> 0) LIMIT 1";
        }
        //die($query);
        $check = mysqli_query($this->con,$query);
        $row = mysqli_fetch_row($check);
        //$num = $this->totalRow($query,0);
        if ($row[0] > 0)
            return true;
        else
            return false;
    }

    //------------------------------------------------
    public function checkClassWarrantyProd($order_id) {
        $query = "SELECT products_ordered FROM  orders WHERE order_id = '{$order_id}'";
        $result= mysqli_query($this->con,$query);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        //class_type of prod is "warranty" or "A La Carte"
        $flag = false;
        if(count($list)>0){
            $t =  json_decode($list[0]['products_ordered'],true);
            for($i=0;$i<count($t);$i++){
                foreach($t[$i] as $k=>$v){
                    //if((trim($v)=="Warranty" || trim($v)=="A La Carte") && $k=="prod_class"){
                    if((trim($v)=="Warranty") && $k=="prod_class"){
                        $flag =true;
                    }
                }
            }
        }

      return $flag;
    }

    //------------------------------------------------
    public function getNotesByWarrantyID($id){
        $query = "SELECT * FROM  notes
                where typeID = '{$id}' AND LOWER(`type`) ='warranty'
                order by noteID DESC";
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $notesList = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $notesList[] = $row;
            }
        }

        return $notesList;
    }
    //------------------------------------------------
    public function warranties_filter($value){
        $query = "SELECT * FROM  warranty_for_claim
                where warranty_address1 like '%{$value}%' OR
                buyer like '%{$value}%' OR
                salesman like '%{$value}%' OR
                affil_agent like '%{$value}%' OR
                affil_mortgage like '%{$value}%' OR
                affil_agent like '%{$value}%'";
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $notesList = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $notesList[] = $row;
            }
        }

        return $notesList;
    }

    //------------------------------------------------
    public function warranties_billtoID($bitto){
        $query = "SELECT * FROM  warranty
                where warranty_buyer_id = '{$bitto}'";
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }

            if(count($list)){
                $temp = json_decode($list[0]["warranty_claim_limit"],true);
                $list[0]["warranty_claim_limit"]=$temp;
            }
        }

        return $list;
    }


    //------------------------------------------------
    public function warranties_bill_toID($bitto){
        $query = "SELECT * FROM  warranty_for_claim
                where warranty_buyer_id = '{$bitto}'";
        //die($query);
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }

            if(count($list)){
                $temp = json_decode($list[0]["warranty_claim_limit"],true);
                $list[0]["warranty_claim_limit"]=$temp;
            }
        }

        return $list;
    }

    //------------------------------------------------------------
    public function getDashboardWarrantyList($number)
    {
        $sqlText = "Select * From warranty_short
        where `warranty_creation_date` > (NOW() - INTERVAL '{$number}' DAY)
        ORDER BY ID";

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
    public function dashboardWarrantyList($limitDay,$login_id,$role=null,$start_date=null,$end_date=null)
    {
        $sql = "";
        $interval="(`warranty_creation_date` > (NOW() - INTERVAL '{$limitDay}' DAY))";

        if(empty($limitDay)){
            if(!empty($start_date) && !empty($end_date)){
                $interval = "`warranty_creation_date` >= '{$start_date}'";
                $interval .= "AND `warranty_creation_date` <= '{$end_date}'";
            }elseif(!empty($start_date) && empty($end_date)){
                $interval = "`warranty_creation_date` >= '{$start_date}'";
            }elseif(empty($start_date) && !empty($end_date)){
                $interval = "`warranty_creation_date` <= '{$end_date}'";
            }
        }

        $interval1 =empty($interval)?"":" AND ".$interval;

        $sql = "Select ID,warranty_order_id,warranty_start_date,
            warranty_end_date, warranty_address1,warranty_creation_date,
            buyer,salesman
            From warranty_short
        where buyer_id ='{$login_id}'  ".$interval1."
        ORDER BY ID DESC";

        $v = $this->protect($role[0]["department"]);
        $level = $this->protect($role[0]['level']);
        if($v=="Vendor" || $v=="Policy Holder" || $v=="Customer"){
            $sql = "Select ID,warranty_order_id,warranty_start_date,
            warranty_end_date, warranty_address1,warranty_creation_date,
            buyer,salesman
            From warranty_short
        where (buyer_id = '{$login_id}' OR warranty_create_by ='{$login_id}') ".$interval1."
        ORDER BY ID DESC";
        }
        elseif($v=="Affiliate"){
            $sql = "Select ID,warranty_order_id,warranty_start_date,
            warranty_end_date, warranty_address1,warranty_creation_date,
            buyer,salesman
            From warranty_short
        where (
            af_s_contactID= '{$login_id}' OR
            af_b_contactID= '{$login_id}' OR
            af_m_contactID= '{$login_id}' OR
            af_t_contactID= '{$login_id}') ".$interval1."
        ORDER BY ID DESC";
        }
        elseif($v=="Sales"){
            $sql = "Select ID,warranty_order_id,warranty_start_date,
            warranty_end_date, warranty_address1,warranty_creation_date,
            buyer,salesman
            From warranty_short
        where
            UID= '{$login_id}' ".$interval1."
        ORDER BY ID DESC";
        }

        $sql .= " LIMIT 1000 ";
        //die($sql);
        $result = mysqli_query($this->con,$sql);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------------------
    public function prods_clsWarranty($Eagle=null,$Renewal=null)
    {
        $sqlText = "Select * From products where prod_class='Warranty' AND prod_inactive = 0";

        if(!empty($Eagle)&&!empty($Renewal)){
            $sqlText = "Select DISTINCT * From products
            where prod_class='Warranty' AND prod_inactive = 0 AND
            (LOWER(product_tags) like '%eagle%' || LOWER(product_tags) like '%renewal%')";
        }elseif(!empty($Eagle)){
            $sqlText = "Select DISTINCT * From products
            where prod_class='Warranty' AND prod_inactive = 0 AND
            LOWER(product_tags) like '%eagle%'";
        }elseif(!empty($Renewal)){
            $sqlText = "Select DISTINCT * From products
            where prod_class='Warranty' AND prod_inactive = 0 AND
            LOWER(product_tags) like '%renewal%'";
        }

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
    public function prods_clsALaCarte()
    {
        $sqlText = "Select * From products where prod_class='A La Carte' AND prod_inactive = 0";

        $result = mysqli_query($this->con,$sqlText);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }


    //------------------------------------------------------------------
    public function addWarrantyNotLogin($warranty_address1,$warranty_address2,$warranty_buyer_agent_id,$warranty_buyer_id,$warranty_city,
                                $warranty_creation_date,$warranty_end_date,$warranty_escrow_id,$warranty_inactive,
                                $warranty_length,$warranty_mortgage_id,$warranty_notes,$warranty_order_id,
                                $warranty_postal_code,$warranty_renewal,$warranty_salesman_id,
                                $warranty_seller_agent_id,$warranty_serial_number,$warranty_start_date,
                                $warranty_state,$warranty_update_by,$warranty_update_date,
                                $warranty_charity_of_choice,$warranty_closing_date,$warranty_contract_amount,$notes,$limits=null,$warranty_eagle=null,
                                $warranty_create_by=null,$warranty_type=null,$warranty_payer_type=null,$warranty_corporate=null,
                                $warranty_submitter=null,$warranty_submitter_type=null,
                                $contract_overage=null,$comefrom=null)
    {
        if(!empty($warranty_order_id)){
            $warranty_order_id = preg_replace('/\s+/','',$warranty_order_id);
            $warranty_order_id= trim($warranty_order_id,",");

        }

        $warranty_payer =$warranty_buyer_id;

        if($warranty_payer_type==1){
            $warranty_payer =$warranty_buyer_id;
        }elseif($warranty_payer_type==2){
            $warranty_payer =$warranty_buyer_agent_id;
        }elseif($warranty_payer_type==3){
            $warranty_payer =$warranty_seller_agent_id;
        }elseif($warranty_payer_type==4){
            $warranty_payer =$warranty_escrow_id;
        }elseif($warranty_payer_type==5){
            $warranty_payer =$warranty_mortgage_id;
        }

        if($warranty_payer ==$warranty_buyer_id) $warranty_payer_type=1;

        //Check Affiliate
        if(!empty($warranty_seller_agent_id)){
            $active =1;
            $aff_type ="Real Estate Agent";
            $this->updateContactType($warranty_seller_agent_id,"Affiliate");
            $this->updateAffiliate($warranty_seller_agent_id,$active,$aff_type);
        }

        if(!empty($warranty_escrow_id)){
            $active =1;
            $aff_type ="Title";
            $this->updateContactType($warranty_escrow_id,"Affiliate");
            $this->updateAffiliate($warranty_escrow_id,$active,$aff_type);
        }

        if(!empty($warranty_mortgage_id)){
            $active =1;
            $aff_type ="Mortgage";
            $this->updateContactType($warranty_mortgage_id,"Affiliate");
            $this->updateAffiliate($warranty_mortgage_id,$active,$aff_type);
        }

        if(!empty($warranty_buyer_agent_id)){
            $active =1;
            $aff_type ="Real Estate Agent";
            $this->updateContactType($warranty_buyer_agent_id,"Affiliate");
            $this->updateAffiliate($warranty_buyer_agent_id,$active,$aff_type);
        }

        //End Affiliate
        //Get AID
        $seller_agent_id = $this->getAffAgent_UID($warranty_seller_agent_id,"affiliate");
        $buyer_agent_id = $this->getAffAgent_UID($warranty_buyer_agent_id,"affiliate");
        $escrow_id = $this->getAffAgent_UID($warranty_escrow_id,"affil_title");
        $mortgage_id = $this->getAffAgent_UID($warranty_mortgage_id,"affil_mortgage");

        $dateTemp = date("Y-m-d");

        $fields = "warranty_address1,warranty_address2,warranty_buyer_agent_id,warranty_buyer_id,warranty_city,
                            warranty_creation_date,warranty_escrow_id,warranty_inactive,
                            warranty_length,warranty_mortgage_id,warranty_notes,warranty_order_id,
                            warranty_postal_code,warranty_renewal,warranty_salesman_id,
                            warranty_seller_agent_id,warranty_serial_number,
                            warranty_state,warranty_update_by,
                            warranty_charity_of_choice,warranty_contract_amount,warranty_claim_limit,warranty_eagle,
                            warranty_create_by,warranty_type,warranty_payer_type,
                            warranty_payer,warranty_corporate";
        $values = "'{$warranty_address1}','{$warranty_address2}','{$buyer_agent_id}','{$warranty_buyer_id}','{$warranty_city}',
                '{$dateTemp}','{$escrow_id}','{$warranty_inactive}',
                '{$warranty_length}','{$mortgage_id}','{$warranty_notes}','{$warranty_order_id}',
                '{$warranty_postal_code}','{$warranty_renewal}','{$warranty_salesman_id}',
                '{$seller_agent_id}','{$warranty_serial_number}',
                '{$warranty_state}','{$warranty_update_by}',
                '{$warranty_charity_of_choice}','{$warranty_contract_amount}','{$limits}',
                '{$warranty_eagle}',
                '{$warranty_create_by}',
                '{$warranty_type}',
                '{$warranty_payer_type}','{$warranty_payer}','{$warranty_corporate}'";

        if(!empty($warranty_closing_date)){
            $fields .=",warranty_closing_date";
            $values .=",'{$warranty_closing_date}'";
        }

        if(is_numeric($warranty_submitter_type) && !empty($warranty_submitter_type) &&
            is_numeric($warranty_submitter) && !empty($warranty_submitter)){
            $fields .=",warranty_submitter_type";
            $values .=",'{$warranty_submitter_type}'";

            //$fields .=",warranty_submitter";
            //$values .=",'{$warranty_submitter}'";
        }

        if(is_numeric($warranty_submitter) && !empty($warranty_submitter)){
            //$fields .=",warranty_submitter_type";
            //$values .=",'{$warranty_submitter_type}'";

            $fields .=",warranty_submitter";
            $values .=",'{$warranty_submitter}'";
        }

        $insertCommand = "INSERT INTO warranty({$fields}) VALUES({$values})";
        //print_r($insertCommand); die();

        // serial number is ready ?
        $alreadySerial = $this->existingWarrantyNo($warranty_serial_number);

        if($alreadySerial) return "The serial number doesn't already.";

        // order is already ?
        $orderID_arr = array(); $orderID1=""; $orderAlready=false;
        $orderID_arr = explode(",",$warranty_order_id);

        //create  $order_id_add
        $order_id_add ="";

        foreach($orderID_arr as $item){
            if(!empty($item)){
                $orderID1 = $item;
                $order_id_add .= empty($order_id_add) ? "" : ",";
                $order_id_add .= "'{$item}'";

                if($this->checkOrderForWarranty($item)) {
                    $orderAlready=true;
                    break;
                }
            }
        }

        if($orderAlready) return "The order ".$orderID1." doesn't already.";
        //prod_class is warranty ?
        $number_orderwarranty=0;
        $flagClassWarrantyProd =false; $orderID="";$order_warrantyID='';
        foreach($orderID_arr as $item){
            if(!empty($item)){
                $orderID = $item;
                $prod_class = $this->checkClassWarrantyProd($item);
                if($prod_class) {
                    $flagClassWarrantyProd=true;
                    $number_orderwarranty=$number_orderwarranty+1;
                    $order_warrantyID=$item;
                    //break;
                }
            }
        }

        if(!$flagClassWarrantyProd) return "The order doesn't already for warranty.";
        if($number_orderwarranty>1) return "The order has more 1 warranty_product";        //
        mysqli_query($this->con,$insertCommand);
        $idreturn = mysqli_insert_id($this->con);
        //die($idreturn);
        if($idreturn){
            //update order
            //update contact overage for order
            if(is_numeric($order_warrantyID)){
                //$paymentObject = new Payment();
                $oder_info = $this->getPaymentBalance_orderID1($order_warrantyID);
                //unset($paymentObject);
                $total_org = $oder_info["total"];
                if(!is_numeric($total_org)) $total_org=0;
                if(!is_numeric($contract_overage)) $contract_overage=0;
                $grand_total = $total_org + $contract_overage;
                $err_update_overage =$this->updateGrandTotalOverage_orderID($order_warrantyID,$contract_overage,$grand_total);
                if(!is_numeric($err_update_overage)){
                    return $err_update_overage;
                }
            }
            //tittle
            $order_title = "Warranty"."-".$warranty_address1;

            $err_temp = $this->upDateOrderTitle_orderIDs($order_id_add,$order_title,$idreturn);
            if(count($err_temp)>0){
                //mysqli_query($this->con,"DELETE FROM warranty WHERE ID = '{$idreturn}' ");
                return $err_temp;
            }

            //update submitter
            if(is_numeric($warranty_submitter_type) && !empty($warranty_submitter_type) &&
                is_numeric($warranty_submitter) && !empty($warranty_submitter) &&
                $comefrom=='freedomhw.com'){

                $err_temp = $this->upSubmitterOrder_warrantyID($order_id_add,$warranty_submitter);
                if(!is_numeric($err_temp) && !empty($err_temp)){
                    //return $err_temp;
                }
            }
            //add notes
            $err = $this->add_notes($notes,$warranty_buyer_id,$idreturn);
            if(!is_numeric($err)){
                return $err;

            }else{
                return $idreturn;
            }
        }else{
            return mysqli_error($this->con);
        }


    }

    //----------------------------------------------------------
    public function updateAffiliate($contactID,$active,$aff_type)
    {
        $info = $this->getAffType_ContactID($contactID);
        if(count($info)>0){
            $contactType =$info["aff_type"];
            if(!empty($contactType)){
                $p= stripos($contactType,$aff_type);
                if(is_numeric($p)){
                    $aff_type = $contactType;
                }else{
                    $aff_type = $aff_type.",".$contactType;
                }
            }

            $updateaffiliate = "UPDATE `affiliate`
                SET active = '{$active}',
                    aff_type = '{$aff_type}'
                WHERE UID = '{$contactID}'";
            mysqli_query($this->con,$updateaffiliate);
        }else{
            $fields = "aff_type,UID,active";
            $values = "'{$aff_type}','{$contactID}',1";
            $insert = "INSERT INTO affiliate({$fields}) VALUES({$values})";
            mysqli_query($this->con,$insert);
        }

        return 1;
    }

    //----------------------------------------------------------
    public function updateContactType($contactID,$contact_type)
    {
        $contactType = $this->getContactType_ID($contactID);
        if(!empty($contactType)){
            $p= stripos($contactType,$contact_type);
            if(is_numeric($p)){
                $contact_type = $contactType;
            }else{
                $contact_type = $contact_type.",".$contactType;
            }

        }

        $update = "UPDATE `contact`
                SET
                    contact_type = '{$contact_type}'
                WHERE ID = '{$contactID}'";
        mysqli_query($this->con,$update);

       /* $update = "UPDATE `users`
                SET
                    contact_type = '{$contact_type}'
                WHERE userContactID = '{$contactID}'";
        mysqli_query($this->con,$update);*/

        return 1;
    }
    //----------------------------------------------------------
    public function getAffAgent_UID($UID,$TABLE){
        $query = "SELECT AID from " .$TABLE. "
          WHERE UID ='{$UID}'";

        $result = mysqli_query($this->con,$query);
        $AID =0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $AID =$row['AID'];
            }
        }
        return $AID;
    }

    //----------------------------------------------------------
    public function getUIDByAID($AID,$TABLE){
        $query = "SELECT UID from " .$TABLE. "
          WHERE AID ='{$AID}'";

        $result = mysqli_query($this->con,$query);
        $UID =0;
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $UID =$row['UID'];
            }
        }
        return $UID;
    }
    //----------------------------------------------------------
    public function getContactType_ID($ID){
        $query = "SELECT contact_type from contact
          WHERE ID ='{$ID}'";

        $result = mysqli_query($this->con,$query);
        $contact_type ="";
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $contact_type =$row['contact_type'];
            }
        }
        return $contact_type;
    }

    //----------------------------------------------------------
    public function getAffType_ContactID($ContactID){

        $query = "SELECT aff_type,AID from affiliate
          WHERE UID ='{$ContactID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();

        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list =$row;
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function getOrderTitle_orderID($id){
        $query = "SELECT order_id,order_title FROM  orders
                where order_id IN ({$id})";
        $rsl = mysqli_query($this->con,$query);
        $list = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------
    public function upDateOrderTitle_orderIDs($ids,$title,$warrantyID){
        $query = "SELECT order_id,order_title FROM  orders
                where order_id IN ({$ids})";
        $rsl = mysqli_query($this->con,$query);
        $updateOrderErr = array();
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                if(!empty($row['order_title'])){
                   $err = $this->upDateOrderTitle_warrantyID($row['order_id'],$row['order_title'],$warrantyID);
                   if($err){
                       $err1=array("error"=>$err,"orderID"=>$row['order_id']);
                       $updateOrderErr[] = $err1;
                   }
                }else{
                    $err = $this->upDateOrderTitle_warrantyID($row['order_id'],$title,$warrantyID);
                    if($err){
                        $err1=array("error"=>$err,"orderID"=>$row['order_id']);
                        $updateOrderErr[] = $err1;
                    }
                }
            }
        }

        return $updateOrderErr;
    }

    //------------------------------------------------
    public function upDateOrderTitle_warrantyID($id,$title,$warrantyID){
        $updateOrder = "UPDATE `orders`
                SET warranty = '{$warrantyID}',
                    order_title = '{$title}'
                WHERE order_id = '{$id}'";


        mysqli_query($this->con,$updateOrder);
        $err_temp =mysqli_error($this->con);
        return $err_temp;
    }

    //------------------------------------------------
    public function checkSandboxPayment_wID($warrantyID){
        $query = "Select payment_status from payment_via_sandbox
                WHERE type_id = '{$warrantyID}' AND payment_type='warranty'
                AND payment_status='1' Limit 1";

        $rsl = mysqli_query($this->con,$query);
        $payment_status = '';
        if($rsl){
            while ($row = mysqli_fetch_assoc($rsl)) {
                $payment_status = $row['payment_status'];
            }
        }

        return $payment_status;
    }

//------------------------------------------------
    public function upSubmitterOrder_warrantyID($ids,$submitter){
        $updateOrder = "UPDATE `orders`
                SET order_create_by = '{$submitter}'
                WHERE order_id  IN ({$ids})";

        mysqli_query($this->con,$updateOrder);
        $err_temp =mysqli_error($this->con);
        return $err_temp;

    }

    //------------------------------------------------
    /*
     Get firstname and lastname and email by contactIS
     */
    public function getFNameLNameMailContact_ID($ID) {

        $query = "SELECT first_name,ID,last_name,primary_email,primary_phone
        from contact_detail
        where ID = '{$ID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['c_list'] = $this->getFNameLNameMail_email($row['primary_email']);
                $list = $row;
            }

        }
        return $list;
    }

    //------------------------------------------------
    /*
     Get firstname and lastname and email by email
     */
    public function getFNameLNameMail_email($email) {

        $query = "SELECT first_name,ID,last_name,primary_email,primary_phone
        from contact_detail
        where primary_email = '{$email}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }
     //------------------------------------------------
    /*
     AddTask with acction is Warranty when Duplicate email with different FirstName LastName
     */
    public function createTaskWthDiffFLN($actionset,$assign_id,$content,$customer_id,
    $doneDate,$dueDate,$status,$taskName,$time,$alert,$urgent){
       $obTask = new Task();
        $idreturn= $obTask->AddNewTask($actionset,$assign_id,$content,$customer_id,
            $doneDate,$dueDate,$status,$taskName,$time,$alert,$urgent);

        unset($obTask);

        return $idreturn;
    }

    //-----------------------get grand total and contract overage-------------------------
    public function getGrandTotalContractOverage_orderID($orderId){
        $query ="SELECT order_id,contract_overage,grand_total,total
		from  orders
        Where order_id ='{$orderId}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if(empty($row["grand_total"])) $row["grand_total"] =$row["total"];
                if(empty($row["contract_overage"])) $row["contract_overage"] =0;
                $list = $row;
            }
        }

        return $list;
    }

    //------------------------------------------------------------------
    public function updateGrandTotalOverage_orderID($orderID,$contract_overage,$grand_total){
        $updateCommand = "UPDATE `orders`
                SET contract_overage = '{$contract_overage}',
                grand_total = '{$grand_total}'
				 WHERE order_id = '{$orderID}'";

        $update = mysqli_query($this->con,$updateCommand);

        if($update){
            return $orderID;
        }else{
            return mysqli_error($this->con);
        }
    }

    //------------------------------------------------
    public function getPaymentBalance_orderID1($orderID){
        $query ="SELECT balance,payment,grand_total,contract_overage,total
		from  orders
        Where order_id ='{$orderID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }
    /////////////////////////////////////////////////////////
}