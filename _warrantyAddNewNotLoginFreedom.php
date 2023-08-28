<?php
$origin=isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['HTTP_HOST'];
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Methods: POST, OPTIONS, GET, PUT');
header('Access-Control-Allow-Credentials: true');

include_once './lib/class.warranty.php';
require_once __DIR__ . '/lib/vendor_Mpdf/autoload.php';
//require('phpToPDF.php');
$Object = new Warranty();

$EXPECTED = array('token','warranty_address1','warranty_address2','warranty_buyer_agent_id','warranty_buyer_id','warranty_city',
    'warranty_creation_date','warranty_email','warranty_end_date','warranty_escrow_id','warranty_inactive',
    'warranty_length','warranty_mortgage_id','warranty_notes','warranty_order_id',
    'warranty_phone', 'warranty_postal_code','warranty_renewal','warranty_eagle','warranty_salesman_id',
    'warranty_seller_agent_id','warranty_serial_number','warranty_start_date',
    'warranty_state','warranty_update_by','warranty_update_date',
    'warranty_closing_date','warranty_contract_amount','warranty_charity_of_choice',
    'pro_ids','warranty_create_by','warranty_payer_type','diff_address','total','totalOver','warranty_corporate','skip_email',
    'warranty_submitter','warranty_submitter_type','contract_overage');

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
    $ret = array('SAVE'=>'FAIL','ERROR'=>'Authentication is failed');
}else{
    $errObj = $Object->validate_warranty_fields($warranty_order_id,$warranty_address1,$warranty_city,$warranty_state,
        $warranty_postal_code,$warranty_buyer_id,$warranty_salesman_id
    );

    if(!$errObj['error']){
        if(empty($warranty_create_by)){
            $warranty_create_by = $warranty_buyer_id;
        }
        if(empty($warranty_salesman_id)){
            $warranty_salesman_id = 0;
        }

        if($warranty_escrow_id=="" || empty($warranty_escrow_id)) {
            $warranty_escrow_id =0;
        }

        if(empty($warranty_buyer_agent_id)){
            $warranty_buyer_agent_id = 0;
        }

        if(empty($warranty_seller_agent_id)){
            $warranty_seller_agent_id =0;
        }

        if(empty($warranty_mortgage_id)){
            $warranty_mortgage_id =0;
        }

        if(empty($warranty_buyer_id)){
            $warranty_buyer_id =0;
        }

        if(empty($warranty_length)){
            $warranty_length =0;
        }

        if(empty($warranty_renewal)){
            $warranty_renewal =0;
        }

        if(empty($warranty_eagle)){
            $warranty_eagle =0;
        }

        if(empty($warranty_update_by)){
            $warranty_update_by =0;
        }

        if(empty($warranty_inactive)){
            $warranty_inactive =0;
        }

        if(empty($warranty_charity_of_choice)){
            $warranty_charity_of_choice =0;
        }

        if(empty($warranty_closing_date)){
            $warranty_closing_date =0;
        }

        if(empty($warranty_contract_amount)){
            $warranty_contract_amount =0;
        }

        if(empty($warranty_payer_type)) $warranty_payer_type=0;
        if(empty($warranty_corporate)) $warranty_corporate=0;

        $notes =array();
        if(isset($_POST['notes'])){
            $notes=$_POST['notes'];
        }

        //
        $currentTemplate=array();
        $warranty_type=$pro_ids;
        $p= stripos($pro_ids,",");
        if(is_numeric($p)){
            $temp_prod = explode(",",$pro_ids);
            $temp_prod1 =array_count_values($temp_prod);
            foreach($temp_prod1 as $id_p =>$value_p){
                $currentTemplate1=array();
                $currentTemplate1= $Object->getClLimit($id_p);
                if($value_p!=1){
                    $arr_temp=array();
                    foreach($currentTemplate1 as $kkk=>$vvv){
                        $arr_temp[$kkk]= $vvv*$value_p;
                    }
                    $currentTemplate[]=$arr_temp;

                }else{
                    $currentTemplate[]=$currentTemplate1;
                }
                // $currentTemplate[]= $Object->getClLimit($item);
            }
        }else{
            $currentTemplate[]= $Object->getClLimit($pro_ids);
        }

        $temp_limit=array();

        //$temp_limit = $Object->process_limit_new($currentTemplate);
        $limits =json_encode($currentTemplate);
        $limits =$Object->protect($limits);
        //$limits = $Object->getClLimit_proIDs($pro_ids);
        //print_r($limits);

        $result = $Object->addWarrantyNotLogin($warranty_address1,$warranty_address2,$warranty_buyer_agent_id,$warranty_buyer_id,$warranty_city,
            $warranty_creation_date,$warranty_end_date,$warranty_escrow_id,$warranty_inactive,
            $warranty_length,$warranty_mortgage_id,$warranty_notes,$warranty_order_id,
            $warranty_postal_code,$warranty_renewal,$warranty_salesman_id,
            $warranty_seller_agent_id,$warranty_serial_number,$warranty_start_date,
            $warranty_state,$warranty_update_by,$warranty_update_date,
            $warranty_charity_of_choice,$warranty_closing_date,$warranty_contract_amount,$notes,$limits,
            $warranty_eagle,$warranty_create_by,$warranty_type,$warranty_payer_type,$warranty_corporate,
            $warranty_submitter,$warranty_submitter_type,$contract_overage);

        //$result=1;
        if(is_numeric($result) && $result){

            if($warranty_inactive ==1){
                $Object->updateStatusPayment($warranty_order_id);
            }

            $leadToPolicy='';
            if(is_numeric($warranty_buyer_id) && !empty($warranty_buyer_id)){
                $leadToPolicy = $Object->ConvertCtactTypeLeadPolicy_ID($warranty_buyer_id);
            }
            //send mail
            //get admin info
            $Ob_manager = new EmailAdress();
            $domain_path = $Ob_manager->domain_path;
            $from_name=$Ob_manager->admin_email;
            $from_email=$Ob_manager->admin_name;
            $from_id=$Ob_manager->admin_id;
            $api_path = $Ob_manager->api_path;

            //MAIL FORMAT
            //Client Information
            $info=$Object->getContact_ID($warranty_buyer_id);
            $clientName = $info[0]['customer_name'];
            //charity
            $charity =$Object->getCharityNameByID($warranty_charity_of_choice);


            //reset Eagle Plan
            $prodOption = '';
            $prodOption = '';
            $prodOptionID = '';
            $prodOptionPrice = '';
            //Eagle Plan
            $eaglePlan =''; $pro_ids_temp=array();
            $eagleIDs= array();
            if (!empty($warranty_eagle) && !empty($pro_ids)){
                $pro_ids_temp = explode(",",$pro_ids);
                foreach($pro_ids_temp as $item){
                    $prodOption = $Object->get_ProductByID($item);
                    if(count($prodOption)>0){
                        if(strtolower($prodOption[0]['product_tags']) =='eagle'){
                            $eagleIDs[]=$prodOption[0]['ID'];
                            $eaglePlan .= '		<tr  align="left">
							  <td width="20%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px">Eagle Coverage</td>
							  <td width="50%" style="border-right: 1px solid black; border-left: 1px solid black;  padding:5px">' . $prodOption[0]['prod_name'] . '</td>
							  <td width="30%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px" align="right">$' . (double) $prodOption[0]['prod_price'] . '</td>
							</tr>';
                        }
                    }
                }

            }
            $orders =explode(",",$warranty_order_id);
            $addOnInvoiceInfo='';
            foreach($orders as $orderID){
                $tr='';
                $pro_ids1 = $Object->getProds_orderID($orderID);
                if(count($pro_ids1[0]['products_ordered'])>0){
                    foreach($pro_ids1[0]['products_ordered'] as $item){
                        if(is_numeric($item["id"])){
                            if(!empty($eaglePlan)){
                                if(!in_array($item["id"], $eagleIDs)){
                                    $addOnInvoiceInfo .= '<tr  align="left">
							  <td width="20%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px">' . $item["sku"] . '</td>
							  <td width="50%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px">' . trim($item["prod_name"]) . ' QTY: ' . $item["quantity"] . ' - ' . $item["price"] . ' each </td>
							  <td width="30%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px" align="right">$' . $item["line_total"] . '</td>
							</tr>';
                                }
                            }else{
                                $addOnInvoiceInfo .= '<tr  align="left">
							  <td width="20%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px">' . $item["sku"] . '</td>
							  <td width="50%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px">' . trim($item["prod_name"]) . ' QTY: ' . $item["quantity"] . ' - ' . $item["price"] . ' each </td>
							  <td width="30%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px" align="right">$' . $item["line_total"] . '</td>
							</tr>';
                            }
                        }

                        //
                    }
                }

            }
            $warrantyOverages='';
            if ($diff_address ==0){
                if(empty($totalOver)) $totalOver='';

                $warrantyOverages = '	<tr  align="left">
							  <td width="20%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px">Warranty Overage</td>
							  <td width="50%" style="border-right: 1px solid black; border-left: 1px solid black;  padding:5px">Differences Between Contract and Actual Warranty Totals. (To Be Applied to Your First Service)</td>
							  <td width="30%" style="border-right: 1px solid black; border-left: 1px solid black; padding:5px" align="right">' .  $totalOver . '</td>
							</tr>';

            }

            $payment_made='';
            $payment_type='';
            $oTotal=0;
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

            $invoiceId='';
            $inv_html='';
            if(count($orders)>0){
                foreach($orders as $orderID){
                    if(!empty($orderID)){
                        $inv_info = $Object->getInv_orderID($orderID);
                        //print_r($inv_info); die();
                        foreach($inv_info as $item){
                            $inv_date =$item["createTime"];
                            $inv_id=$item["invoiceid"];
                            $inv_html.='<tr>
											<td style="border: 1px solid black; text-align: center">' . $inv_date . '</td>
											<td style="border: 1px solid black; text-align: center">' . $inv_id . '</td>
										</tr>';
                        }

                    }

                }
            }else{
                $inv_html='<tr>
                                    <td style="border: 1px solid black; text-align: center">' . date('m-d-Y') . '</td>
                                    <td style="border: 1px solid black; text-align: center">' . $invoiceId . '</td>
                                </tr>';
            }
            //----------agent Name
            $buyer_agent_name ="Buyer Agent Name: <br>";
            $seller_agent_name ="Seller Agent Name: <br>";
            $escrow_name ="Escrow Name: <br>";
            $mortgage_name ="Mortgage Name: <br>";
            $submitter_name="Submitter Name: <br>";
            if(is_numeric($warranty_buyer_agent_id) && !empty($warranty_buyer_agent_id)){
                $info_name=$Object->getContact_ID($warranty_buyer_agent_id);
                $buyer_agent_name1 = $info_name[0]['customer_name'];
                $buyer_agent_name = "Buyer Agent Name: ".$buyer_agent_name1."<br>";
            }
            if(is_numeric($warranty_seller_agent_id) && !empty($warranty_seller_agent_id)){
                $info_name=$Object->getContact_ID($warranty_seller_agent_id);
                $seller_agent_name1 = $info_name[0]['customer_name'];
                $seller_agent_name = "Seller Agent Name: ".$seller_agent_name1."<br>";
            }

            if(is_numeric($warranty_escrow_id) && !empty($warranty_escrow_id)){
                $info_name=$Object->getContact_ID($warranty_escrow_id);
                $escrow_name1 = $info_name[0]['customer_name'];
                $escrow_name = "Escrow Name: ".$escrow_name1."<br>";
            }
            if(is_numeric($warranty_mortgage_id) && !empty($warranty_mortgage_id)){
                $info_name=$Object->getContact_ID($warranty_mortgage_id);
                $mortgage_name1 = $info_name[0]['customer_name'];
                $mortgage_name = "Mortgage Name: ".$mortgage_name1."<br>";
            }
            if(is_numeric($warranty_submitter) && !empty($warranty_submitter)){
                $info_name=$Object->getContact_ID($warranty_submitter);
                $submitter_name1 = $info_name[0]['customer_name'];
                $submitter_name = "Submitter Name: ".$submitter_name1."<br>";
            }
            //----------
            $prodTitle="";
            $clientFirstName="";
            $clientLastName='';
            $clientWarrantyStreetAddress1="";
            $clientWarrantyPostalCode="";
            $productPrice="";
            //logo
            $logo ="/photo/logo.png";
            //$logo_path = $_SERVER["DOCUMENT_ROOT"].$logo;
            $logo_path = $api_path.$logo;

            $HTMLContent='
		<table width="700px" border="0">
		  <tbody >
			<tr style="color:black!important;" class="black">
			  <td width="20%"><img src="https://www.freedomhw.com//wp-content/uploads/2018/12/Freedom-Home-Warranty-Logo-RGB-01.png"  alt="Freedom HW Logo" style="max-width:150px"/></td>
			  <td width="50%" style="padding-left:10px"><h3 style="font-size:20px"><strong>Freedom Home Warranty</strong></h3>
					1186 E 4600 S #400 <br>
					Ogden, UT 84401 <br>
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
						<table width="350px" style="border: 1px solid black; padding:5px;color:black;" cellpadding="0" cellspacing="0">
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
							  <td colspan="2" style="border: 1px solid black;" align="right"><strong></strong></td>
								<td width="30%" style="border: 1px solid black; padding:5px" align="right"><strong>Total Due:  $' . $total . '</strong></td>
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
								Chosen Charity:<br>
								' . $charity .' <br><br>'. $buyer_agent_name.$seller_agent_name.
                $escrow_name.$mortgage_name.$submitter_name.'
								</td>
							</tr>
						  </tbody>
						</table>';

            // PUT YOUR HTML IN A VARIABLE
            $my_html='<html lang="en">
              <head>
                <meta charset="UTF-8">
              </head>
              <body>
              ' . $HTMLContent .'
              </body>
            </html>';

            //mail sent to payee
            $listsend = array();
            /*if($warranty_payer_type==1 && !empty($warranty_buyer_id)) $listsend[] = $warranty_buyer_id;
            if($warranty_payer_type==2 && !empty($warranty_buyer_agent_id)) $listsend[] = $warranty_buyer_agent_id;
            if($warranty_payer_type==3 && !empty($warranty_seller_agent_id)) $listsend[] = $warranty_seller_agent_id;
            if($warranty_payer_type==4 && !empty($warranty_escrow_id)) $listsend[] = $warranty_escrow_id;
            if($warranty_payer_type==5 && !empty($warranty_mortgage_id)) $listsend[] = $warranty_mortgage_id;
            */

            if(!empty($warranty_buyer_id)) $listsend[] = $warranty_buyer_id;
            if(!empty($warranty_buyer_agent_id)) $listsend[] = $warranty_buyer_agent_id;
            if(!empty($warranty_seller_agent_id)) $listsend[] = $warranty_seller_agent_id;
            if(!empty($warranty_escrow_id)) $listsend[] = $warranty_escrow_id;
            if(!empty($warranty_mortgage_id)) $listsend[] = $warranty_mortgage_id;

            $skip_email_sales=$skip_email;
            $photoPathTemp=''; $HTMLContentLinks ='';
            if((count($listsend)>0 ||!empty($warranty_salesman_id)) && $skip_email!=1){
                //filename
                $d = date('Y-m-d H:i:s');
                $temp = explode(" ",$d);
                $temp2 = explode(":",$temp[1]);
                $fileName ='email_content'.'-'. $temp[0].'-'.$temp2[0].'-'.$temp2[1].'-'.$temp2[2].".pdf";

                $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/lib/vendor_Mpdf/mpdf/mpdf/tmp']);


                $mpdf->WriteHTML($my_html,2);

                $pathname ="/photo/email_attachment/";
                $photoPathTemp = $_SERVER["DOCUMENT_ROOT"].$pathname.$fileName;
                $mpdf->Output($photoPathTemp,'F');
                $HTMLContentLinks = '<p><a href="'.$api_path.'/photo/email_attachment/'. $fileName .'">Click Here to Download Your Invoice</a></p><p>Attached is your buyer contract. <br />~HostedAttachment_132242~</p>';

            }
            //---------- check crrect fist, last name and creat task
            $warranty_payee =$warranty_buyer_id;

            if($warranty_payer_type==1){
                $warranty_payee =$warranty_buyer_id;
            }elseif($warranty_payer_type==2){
                $warranty_payee =$warranty_buyer_agent_id;
            }elseif($warranty_payer_type==3){
                $warranty_payee =$warranty_seller_agent_id;
            }elseif($warranty_payer_type==4){
                $warranty_payee =$warranty_escrow_id;
            }elseif($warranty_payer_type==5){
                $warranty_payee =$warranty_mortgage_id;
            }

            //$skip_email=1;
            if($warranty_payee!=0){
                $contact_info = $Object->getFNameLNameMailContact_ID($warranty_payee);
                $fName=$contact_info['first_name'];
                $lName=$contact_info['last_name'];
                $pEmail=$contact_info['primary_email'];
                $listContacts=$contact_info['c_list'];

                if(count($listContacts)>1){
                    //$skip_email=1;
                    $tr='';
                    for($i=0;$i<count($listContacts);$i++){
                        $info = $listContacts[$i];
                        if($info['first_name'] <> $fName || $info['last_name'] <> $lName){

                            $tr .='<tr>
                                <td>'.$info['ID'].'</td>
                                <td>'.$info['first_name'].'</td>
                                <td>'.$info['last_name'].'</td>
                                <td>'.$info['primary_email'].'</td>
                                <td>'.$info['primary_phone'].'</td></tr>';
                        }
                    }

                    $table ='<table border=1>
                        <thead><tr><td>ContactID</td>
                        <td>FirstName</td>
                        <td>LastName</td>
                        <td>Email</td>
                        <td>Phone</td></tr>
                        </thead><tbody>'.$tr.'</tbody></table>';

                    $taskContent="An attempt to create a Warranty for: <br><br>".$table."<br><br>Warranty Address: ".$warranty_address1."<br><br>";
                    $taskContent.="Please update info and send invoice to correct email";

                    $actionset="warranty";
                    $assign_id=41864;
                    $customer_id=0;
                    $doneDate='';
                    $dueDate='';
                    $status='open';
                    $taskName='Task Info';
                    $time='';
                    $alert='';
                    $urgent='';
                    $task_id =$Object->createTaskWthDiffFLN($actionset,$assign_id,$taskContent,$customer_id,
                        $doneDate,$dueDate,$status,$taskName,$time,$alert,$urgent);
                }
            }

            //----------
            if(count($listsend)>0 && $skip_email!=1){
                //warranty link
                $html =$domain_path."/#ajax/warranty-form.php?id=".$result;
                //content
                $agentEmailContent = "<p>Thank you for choosing Freedom Home Warranty for all of your home warranty needs. We truly appreciate your business and remember, We're Here To Protect You. </p>";
                $agentEmailContent .= "<p>To continue to receive emails regarding your clients' policy and to subscribe to our monthly newsletter, please click the verification link. ~DoubleOptInLink~.</p>";

                //$subject
                $subject=$order_title = "Warranty"."-".$warranty_address1;
                $agentEmailContent1 = $Object->protect($agentEmailContent);

                foreach($listsend as $item){
                    if(!empty($item)){
                        //accountant info
                        $info=$Object->getContact_ID($item);
                        $to_name = $info[0]['customer_name'];
                        $to_email = $info[0]['primary_email'];

                        $status = '';

                        //check email
                        if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                            $status = 'Bounce';
                        }
                        //check email
                        $domain = substr($to_email, strpos($to_email, '@') + 1);
                        if  (!checkdnsrr($domain) !== FALSE) {
                            $status = 'Bounce';
                        }

                        $id_tracking = $Object->insertTrackingEmail($to_email,$subject,$agentEmailContent1,$from_id,$status);
                        if(empty($status) && !empty($total)){
                            $is_send =  $Object->mail_to($from_name,$to_name,$to_email,$subject,$agentEmailContent,$id_tracking,$photoPathTemp,$fileName);
                            if($is_send==1){
                                $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                            }
                        }
                    }
                }
            }

            //send email to saleman
            if(!empty($warranty_salesman_id) && $skip_email_sales!=1){
                //accountant info
                $saleID=$Object->getContactID_salemanID($warranty_salesman_id);
                $info=$Object->getContact_ID($saleID);
                $to_name = $info[0]['customer_name'];
                $to_email = $info[0]['primary_email'];

                $status = '';
                //check email
                if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                    $status = 'Bounce';
                }
                //check email
                $domain = substr($to_email, strpos($to_email, '@') + 1);
                if  (!checkdnsrr($domain) !== FALSE) {
                    $status = 'Bounce';
                }

                //warranty link
                $html =$domain_path."/#ajax/warranty-form.php?id=".$result;
                //content
                $salesEmail = '<p>The following order has been created:</p>';
                //$salesEmail .='<p>'.$submittedNotes.'</p>';
                $salesEmail .='<p>------------------------------</p>';
                $agentEmailContent =$salesEmail;
                $agentEmailContent .= '<p>Thank you for choosing Freedom Home Warranty for all of your home warranty needs. We truly appreciate your business and remember, "We\'re Here To Protect You." </p>';
                $agentEmailContent .= "<p>To continue to receive emails regarding your clients' policy and to subscribe to our monthly newsletter, please click the verification link. ~DoubleOptInLink~.</p>";

                $agentEmailContent1 = $Object->protect($agentEmailContent);
                //$subject
                $subject=$order_title = "Warranty"."-".$warranty_address1;
                $id_tracking = $Object->insertTrackingEmail($to_email,$subject,$agentEmailContent1,$from_id,$status);

                if(empty($status) && !empty($total)){
                    $is_send =  $Object->mail_to($from_name,$to_name,$to_email,$subject,$agentEmailContent,$id_tracking,$photoPathTemp,$fileName);
                    if($is_send==1){
                        $Object->updateTrackEmail($id_tracking,$status="Sent",$opened="Unopened");
                    }
                }
            }
            $ret = array('SAVE'=>'SUCCESS','ERROR'=>'','ID'=>$result,'AUTH'=>true,'leadToPolicy'=>$leadToPolicy,'task_id'=>$task_id);
        } else {
            //log errors
            if(is_array($result)) $result =json_encode($result);
            $info ="Warranty -- warranty_address1:".$warranty_address1. ", warranty_buyer_id: ".$warranty_buyer_id.
                ", warranty_salesman_id: ".$warranty_salesman_id.", warranty_email ".$warranty_email.", err: ".$result;

            $Object->err_log("Warranty",$info,0);

            if($result){
                $ret = array('SAVE'=>'FAIL','ERROR'=>$result,'AUTH'=>true);
            }else{
                $ret = array('SAVE'=>'FAIL','ERROR'=>'System can not add the warranty.','AUTH'=>true);
            }
        }

    } else {
        $ret = array('SAVE'=>'FAIL','ERROR'=>$errObj['errorMsg'],'AUTH'=>true);
    }
}

$Object->close_conn();
echo json_encode($ret);




