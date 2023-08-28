<?php

require_once 'class.common.php';
class InvoiceMail extends Common{

    //------------------------------------------------
    public function getInv_orderInfo_invID($invoiceID) {
        //get invoice and order
        $query = "SELECT DISTINCT i.order_id,i.createTime,i.invoiceid,i.total,i.customer,
        i.b_first_name,i.b_last_name,i.customer_name,
        i.b_primary_street_address1,i.b_primary_street_address2,
        i.b_primary_city,i.b_primary_state,i.b_postal_code,
        i.balance,i.payment,
        o.products_ordered,o.total as order_total,o.warranty as warranty_id
        FROM  invoice_short as i
        left join orders as o on o.order_id = i.order_id
        where invoiceid ='{$invoiceID}'";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $row['products_ordered']= json_decode($row['products_ordered'],true);
                $list[] = $row;
            }
        }
        return $list;
    }

    //------------------------------------------------
    public function getWarrantyInfo_orderID($warranty_id) {
        //get warranty
        if(empty($warranty_id)) return array();

        $query = "SELECT DISTINCT w.warranty_address1,w.warranty_address2,w.warranty_buyer_agent_id,w.warranty_buyer_id,
        w.warranty_charity_of_choice,w.warranty_city,w.warranty_state,w.warranty_postal_code,
        w.warranty_escrow_id,w.warranty_mortgage_id,w.warranty_salesman_id
        ,w.warranty_seller_agent_id,w.warranty_eagle,warranty_submitter_type,warranty_submitter

        FROM  warranty as w
        where ID = '{$warranty_id}'  LIMIT 1";
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
    public function getInvInfo_invID($invoiceID,$domain_path=null,$from_name=null,
                                     $from_email=null,$from_id=null,$api_path=null) {
        //get invoice and order
        $orderIvnInf = $this->getInv_orderInfo_invID($invoiceID);
        $orderID='';
        //bill to info
        $customer_name='';
        $b_primary_street_address1='';
        $b_primary_street_address2='';
        $b_primary_city='';
        $b_primary_state='';
        $b_postal_code='';
        $city_state_zip='';
        //warranty info
        $warrInf = array();
        $warranty_buyer_id='';
        $clientName='';
        $charity='';
        $ChosenCharity='';
        $warranty_eagle='';
        $warranty_address1='';
        $warranty_address2='';
        $warranty_city='';
        $warranty_state='';
        $warranty_postal_code='';
        $total=0;
        $ivnBalance=0;
        $ivnPayment=0;
        if(count($orderIvnInf)>0){
            $warrInf = $this->getWarrantyInfo_orderID($orderIvnInf[0]['warranty_id']);

            $orderID =$orderIvnInf[0]['order_id'];
            //$total = $orderIvnInf[0]['order_total'];
            $total = number_format($orderIvnInf[0]['order_total'],2,".",",");
            $ivnBalance = number_format($orderIvnInf[0]['balance'],2,".",",");
            $ivnPayment = number_format($orderIvnInf[0]['payment'],2,".",",");

            //bill to info
            $customer_name=$orderIvnInf[0]['customer_name'];
            $b_primary_street_address1=$orderIvnInf[0]['b_primary_street_address1'];
            $b_primary_street_address2=$orderIvnInf[0]['b_primary_street_address2'];
            $b_primary_city=$orderIvnInf[0]['b_primary_city'];
            $b_primary_state=$orderIvnInf[0]['b_primary_state'];
            $b_postal_code=$orderIvnInf[0]['b_postal_code'];

            $city_state_zip =$b_primary_city;
            if(!empty($b_primary_state)){
                $city_state_zip.=', ' . $b_primary_state . ' ' . $b_postal_code;
            }else{
                if(!empty($b_postal_code)) $city_state_zip.=', ' . $b_postal_code;

            }
        }
        //----------agent Name
        $buyer_agent_name ="Buyer Agent Name: <br>";
        $seller_agent_name ="Seller Agent Name: <br>";
        $escrow_name ="Escrow Name: <br>";
        $mortgage_name ="Mortgage Name: <br>";
        $submitter_name="Submitter Name: <br>";
        //MAIL FORMAT
        $warranty_info='';
        if(count($warrInf)>0){
            //get warranty_buyer_agent_id name
            if(is_numeric($warrInf[0]["warranty_buyer_agent_id"]) && !empty($warrInf[0]["warranty_buyer_agent_id"])){
                $info_name=$this->getContact_agentID($warrInf[0]["warranty_buyer_agent_id"]);

                $buyer_agent_name1 = $info_name[0]['customer_name'];
                $buyer_agent_name = "Buyer Agent Name: ".$buyer_agent_name1."<br>";
                if($warrInf[0]["warranty_submitter_type"] ==2){
                    $submitter_name="Submitter Name: ".$buyer_agent_name1."<br>";
                }
            }

            //get warranty_seller_agent_id
            if(is_numeric($warrInf[0]["warranty_seller_agent_id"]) && !empty($warrInf[0]["warranty_seller_agent_id"])){
                $info_name=$this->getContact_agentID($warrInf[0]["warranty_seller_agent_id"]);
                $seller_agent_name1 = $info_name[0]['customer_name'];
                $seller_agent_name = "Seller Agent Name: ".$seller_agent_name1."<br>";
                if($warrInf[0]["warranty_submitter_type"] ==3){
                    $submitter_name="Submitter Name: ".$seller_agent_name1."<br>";
                }
            }

            //get warranty_escrow_id
            if(is_numeric($warrInf[0]["warranty_escrow_id"]) && !empty($warrInf[0]["warranty_escrow_id"])){
                $info_name=$this->getContact_agentID($warrInf[0]["warranty_escrow_id"]);
                $escrow_name1 = $info_name[0]['customer_name'];
                $escrow_name = "Escrow Name: ".$escrow_name1."<br>";
                if($warrInf[0]["warranty_submitter_type"] ==4){
                    $submitter_name="Submitter Name: ".$escrow_name1."<br>";
                }
            }

            //get warranty_mortgage_id
            if(is_numeric($warrInf[0]["warranty_mortgage_id"]) && !empty($warrInf[0]["warranty_mortgage_id"])){
                $info_name=$this->getContact_agentID($warrInf[0]["warranty_mortgage_id"]);
                $mortgage_name1 = $info_name[0]['customer_name'];
                $mortgage_name= "Escrow Name: ".$mortgage_name1."<br>";
                if($warrInf[0]["warranty_submitter_type"] ==5){
                    $submitter_name="Submitter Name: ".$mortgage_name1."<br>";
                }
            }

            //submit type =1;
            $warranty_buyer_id = $warrInf[0]['warranty_buyer_id'];
            $info=$this->getContact_ID($warranty_buyer_id);
            if($warrInf[0]["warranty_submitter_type"] ==1 && count($info)>0){
                $submitter_name1 = $info[0]['customer_name'];
                $submitter_name = "Submitter Name: ".$submitter_name1."<br>";
            }
            //get  warranty_submitter
            if(is_numeric($warrInf[0]["warranty_submitter"]) && !empty($warrInf[0]["warranty_submitter"])){
                $info_name=$this->getContact_ID($warrInf[0]["warranty_submitter"]);
                $submitter_name1 = $info_name[0]['customer_name'];
                $submitter_name = "Submitter Name: ".$submitter_name1."<br>";
            }

            ////warranty ifo
            //$warranty_buyer_id = $warrInf[0]['warranty_buyer_id'];
            //$info=$this->getContact_ID($warranty_buyer_id);

            if(count($info)>0) $clientName = $info[0]['customer_name'];

            $charity =$this->getCharityNameByID( $warrInf[0]['warranty_charity_of_choice']);
            $ChosenCharity='Chosen Charity:';


            $warranty_address1=$warrInf[0]['warranty_address1'];
            $warranty_address2=$warrInf[0]['warranty_address2'];
            $warranty_city=$warrInf[0]['warranty_city'];
            $warranty_state=$warrInf[0]['warranty_state'];
            $warranty_postal_code=$warrInf[0]['warranty_postal_code'];

            $warranty_info='<table width="320px" style="border: 1px solid black; padding:5px;color:black;" cellpadding="0" cellspacing="0">
          <tbody>
            <tr >
              <td  style="border-bottom: 1px solid black; text-align: center; font-size: 15px;" width="100%">WARRANTY INFO:</td>
            </tr>
            <tr>
              <td style="padding:5px;" width="100%">' . $clientName. '<br>
                ' . $warranty_address1 . ' <br>
                ' . $warranty_address2 . ' <br>
              ' . $warranty_city . ', ' . $warranty_state . ' ' . $warranty_postal_code . '</td>

            </tr>
          </tbody>
        </table>';

        }

        //Eagle Plan
        $eaglePlan ='';
        $addOnInvoiceInfo='';

        $eagleIDs= array();
        if (count($orderIvnInf)>0){
            foreach($orderIvnInf[0]['products_ordered'] as $item){
                if(is_numeric($item["id"])){
                    //$item["line_total"] =$item["line_total"];
                    $item["line_total"] = number_format($item["line_total"],2,".",",");
                    $addOnInvoiceInfo .= '<tr  align="left">
							  <td width="20%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px">' . $item["sku"] . '</td>
							  <td width="50%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px">' . trim($item["prod_name"]) . ' QTY: ' . $item["quantity"] . ' - ' . $item["price"] . ' each </td>
							  <td width="30%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px" align="right">$' . $item["line_total"] . '</td>
							</tr>';
                }

                //
            }
        }
        //get buyer to compare address
        $warrantyOverages=''; $diff_address=0; $totalOver=0;
        if ($diff_address > 0){
            if(empty($totalOver)) $totalOver=0;
            /*$warrantyOverages = '	<tr  align="left">
							  <td width="20%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px">Warranty Overage</td>
							  <td width="50%" style="border-right: 1px solid black; border-left: 1px solid black;  padding:5px">Differences Between Contract and Actual Warranty Totals. (To Be Applied to Your First Service)</td>
							  <td width="30%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px" align="right">$' . (double) $totalOver . '</td>
							</tr>';
            */
        }

        $payment_made='';
        $payment_type='';
        $oTotal=0;
        /*
        if ($payment_type == 'Card'){

            $odTotal = $oTotal;
            $payment_made = '<tr align="center">
							  <td colspan="2" style="border: 1px solid black;" align="right"><strong></strong></td>
								<td width="30%" style="border: 1px solid black; padding:5px" align="right">Subtotal:  $' . $odTotal . '.00</td>
							</tr>
						<tr align="center">
							  <td colspan="2" style="border: 1px solid black;" align="right"><strong></strong></td>
								<td width="30%" style="border: 1px solid black; padding:5px" align="right">Payment Received:  ($' . $odTotal . '.00)</td>
							</tr>';
            $oTotal= 0;

        }
        */
        //invoice
        $invoiceId='';
        $inv_html='';
        if(!empty($orderID)){
            $inv_info = $this->getInv_orderID($orderID);
            //print_r($inv_info); die();
            foreach($inv_info as $item){
                $inv_date =$item["createTime"];
                $inv_id=$item["invoiceid"];
                $inv_html.='<tr>
                    <td style="border: 1px solid black; text-align: center">' . $inv_date . '</td>
                    <td style="border: 1px solid black; text-align: center">' . $inv_id . '</td>
                </tr>';
            }

        }else{
            $inv_html='<tr>
                <td style="border: 1px solid black; text-align: center">' . date('m-d-Y') . '</td>
                <td style="border: 1px solid black; text-align: center">' . $invoiceId . '</td>
            </tr>';
        }



    //----------bind into html
    $prodTitle="";
    $clientFirstName="";
    $clientLastName='';
    $clientWarrantyStreetAddress1="";
    $clientWarrantyPostalCode="";
    $productPrice="";

$HTMLContent='
		<table width="700px" border="0">
		  <tbody >
			<tr style="color:black!important;" class="black">
			  <td width="20%"><img src="https://www.freedomhw.com//wp-content/uploads/2018/12/Freedom-Home-Warranty-Logo-RGB-01.png"  alt="Freedom HW Logo" style="max-width:150px"/></td>
			  <td width="50%" style="padding-left:10px"><h3 style="font-size:20px"><strong>Freedom Home Warranty</strong></h3>
					1186 East 4600 South, Ste. 400 <br>
					South Ogden, UT. 84403 <br>
					Accounting@FreedomHW.com</td>
			  <td width="30%" class="wide30">
				  <div style="text-align: center;width:100%"><strong>INVOICE</strong></div>
				  <table style="float:right; border: 1px solid black;color:black;" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th style="border: 1px solid black;">Date</th>
                                <th style="border: 1px solid black;">Invoice #</th>
                            </tr>
                        </thead>
                        <tbody>'.$inv_html.'
                        </tbody>

                   </table>
              </td>
            </tr>
          </tbody>
        </table>
        <br>
        <table width="700px">
        <tr>
            <td style=width="320px">
            <table width="320px" style="border: 1px solid black; padding:5px;color:black;" cellpadding="0" cellspacing="0">
          <tbody>
            <tr >
              <td  style="border-bottom: 1px solid black; text-align: center; font-size: 15px;" width="100%">BILL TO:</td>
            </tr>
            <tr>
              <td style="padding:5px;" width="100%">' . $customer_name. '<br>
                ' . $b_primary_street_address1 . ' <br>
                ' . $b_primary_street_address2 . ' <br>
              ' . $city_state_zip . '</td>

            </tr>
          </tbody>
        </table></td>
            <td style="width:60px;min-width:60px"></td>
            <td style=width="320px">'.$warranty_info.'</td>
        </tr>
        </table>

        <br>
        <table width="700px" style="border: 1px solid black;color:black;" cellpadding="0" cellspacing="0">
          <tbody>
            <tr style="border: 1px solid black;" align="center">
              <td width="20%" style="border: 1px solid black; text-align:center;">Item</td>
              <td width="50%" style="border: 1px solid black;text-align:center;">Description</td>
              <td width="30%" style="border: 1px solid black;text-align:center;">Amount</td>
            </tr>
            <tr  align="left">
              <td width="20%" style="border-right: 1px solid black; border-left: 1px black solid; padding:5px">' . $prodTitle . '</td>
              <td width="50%" style="border-right: 1px solid black; border-left: 1px black solid;  padding:5px">' . $clientFirstName . ' ' . $clientLastName .  '</td>
              <td width="30%" style="border-right: 1px solid black; border-left: 1px black solid; padding:5px" align="right">' . $productPrice . '</td>
            </tr>' . $eaglePlan . $addOnInvoiceInfo . $warrantyOverages . $payment_made .'

            <tr align="center">
              <td colspan="2" style="border-top: 1px solid black; padding:5px" align="right"><strong></strong></td>
                <td width="30%" style="border-top: 1px solid black; padding:5px" align="right"><strong>Balance Due:  $' . $ivnBalance . '</strong></td>
            </tr>
            <tr align="center">
              <td colspan="2"  align="right"><strong></strong></td>
                <td width="30%"  align="right"><strong>Payment:  $' . $ivnPayment . '</strong></td>
            </tr>
            <tr align="center">
              <td colspan="2"  align="right"><strong></strong></td>
                <td width="30%"  align="right"><strong>Total Due:  $' . $total . '</strong></td>
            </tr>
          </tbody>
        </table>
        <br>
        <table width="700px" style="border: 1px solid black; padding:5px;color:black!important;" cellpadding="0" cellspacing="0">
          <tbody>
            <tr >
              <td  style="border-bottom: 1px solid black; text-align: left; font-size: 13px" >Notes:</td>
            </tr>
            <tr>
              <td style="padding:5px">Please remit payment to: <br>
                PO Box 150868 <br> South Ogden, UT 84415<br><br>
                '.$ChosenCharity.'<br>
                ' . $charity .' <br><br>'. $buyer_agent_name.$seller_agent_name.
    $escrow_name.$mortgage_name.$submitter_name.'
                </td>
            </tr>
          </tbody>
        </table>';

    return $HTMLContent;
    }
    //------------------------------------------------
    public function getBillTo_Salesman_invID($invoiceID) {
        //get invoice and order
        $query = "SELECT customer_name,b_primary_email,s_primary_email,sale_name
        from invoice_short
        where invoiceid ='{$invoiceID}' AND invoiceid <>'' AND invoiceid IS NOT NULL";

        $result = mysqli_query($this->con,$query);
        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list = $row;
            }
        }

        return $list;
    }

    //----------------------------------------------------------
    public function getContact_agentID($agentID)
    {
        $sqlText = "Select concat(IFNULL(c.first_name,''),' ',IFNULL(c.last_name,'')) as customer_name,
                    primary_email
            From contact as c
            left join affiliate as af on af.UID = c.ID
        where af.AID = '{$agentID}' AND af.UID <>'' AND af.UID IS NOT NULL";
        //die($sqlText);
        $result = mysqli_query($this->con,$sqlText);

        $list = array();
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
        }

        return $list;
    }
    //////////////////////////////
}