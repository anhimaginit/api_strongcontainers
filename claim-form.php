<?php
$claim_form = 'ClaimForm';
use \SmartUI\Components\SmartForm;
use \SmartUI\Util as SmartUtil;

require_once 'inc/init.php';
require_once '../php/link.php';

$_authenticate->checkFormPermission($claim_form);

$current_form = '';
if (hasIdParam() && strrpos($_SERVER['REQUEST_URI'], 'claim-form') > 0) {
    $current_form = 'edit';
} else {
    $current_form = 'add';
}
$_ui->start_track();

$claimDataEdit = array();
if (hasIdParam()) {
    $claimDataEdit = HTTPMethod::httpPost($link['_claimGetById'], array(
        'token' => $_COOKIE['token'],
        'ID' => getID(),
        'jwt' => $_COOKIE['jwt'],
        'private_key' => $_COOKIE['userID'])
    );
    if(null!== $claimDataEdit)
    $claimDataEdit =  $claimDataEdit->Claim;
}


$body_claim = '';

$body_claim .= '
<div id="message_form" role="alert" style="display:none"></div>
<form class="smart-form" id="claim_form" method="post">';

if (isset($_SESSION['assign_to_email'])) {

    $body_claim .= SmartForm::print_field('assign_to_email', SmartForm::FORM_FIELD_HIDDEN,
        array(
            "value" => $_SESSION['assign_to_email'],
        ), 4, true);
}

if (isset($_SESSION['admin_assign_to'])) {

    $body_claim .= SmartForm::print_field('admin_assign_to', SmartForm::FORM_FIELD_HIDDEN,
        array(
            "value" => $_SESSION['admin_assign_to'],
        ), 4, true);
}

{/** Contact info */
    ob_start();
    include './claim-form.contact.php';
    $body_claim .= ob_get_contents();
    ob_end_clean();

}

{/** Warranty info */
    ob_start();
    include './claim-form.warranty.php';
    $body_claim .= ob_get_contents();
    ob_end_clean();

}
$body_claim .= SmartForm::print_field('ID', SmartForm::FORM_FIELD_HIDDEN,
array('value' => hasIdParam() && isset($claimDataEdit->ID) ? $claimDataEdit->ID : ''));

$body_claim .= SmartForm::print_field('create_by', SmartForm::FORM_FIELD_HIDDEN,
    array(
        'type' => 'hidden',
        'value' => hasIdParam() && isset($claimDataEdit->create_by) ? $claimDataEdit->create_by : $_COOKIE['userID']
    ), 6, true);

// {/** Claim Limit */
//     ob_start();
//     $body_claim .= '<div class="hidden" id="pane_limit_list">';
//     include './claim-limit-list.php';
//     $body_claim .= ob_get_contents();
//     $body_claim .= '</div>';
//     ob_end_clean();
// }

if (hasIdParam() && isset($claimDataEdit->ID) ){/** Claim transaction*/
    ob_start();
    include './claim-form.transaction2.php';
    $body_claim .= ob_get_contents();
    ob_end_clean();

}

{/** Claim Warranty Limit */
    ob_start();
    $body_claim .= '<div class="hidden" id="pane_claim_warranty_limit_list">';
    include './claim-warranty-limit.php';
    $body_claim .= ob_get_contents();
    $body_claim .= '</div>';
    ob_end_clean();

}


if (hasIdParam() && isset($claimDataEdit->ID) ){/** Claim */
    ob_start();
    include './claim.php';
    $body_claim .= ob_get_contents();
    ob_end_clean();

}

if (hasIdParam() && isset($claimDataEdit->ID) &&isset($claimDataEdit->assign_task) && isset($claimDataEdit->create_by) && '' . $claimDataEdit->create_by == '' . $_COOKIE['userID']) {/** Task Claim info */
    ob_start();
    include './claim-form.task.php';
    $body_claim .= ob_get_contents();
    ob_end_clean();
}

{/** Notes Claim info */
    ob_start();
    $type_note = 'claim';
    $can_add_note = true;
    include './notes.php';
    $body_claim .= ob_get_contents();
    ob_end_clean();
}
$body_claim .= '<footer>';
$body_claim .= '<button type="button" id="btnBackClaim" class="btn btn-default"">Back</button>';
$body_claim .= '<button type="submit" id="btnSubmitClaim" class="btn btn-primary">'.(hasIdParam() && isset($claimDataEdit->ID)? 'Save' : 'Start Claim').'</button>';
$body_claim .= '</footer>';
$body_claim .= '</form>';
?>

<section id="widget-grid" class="">
   <div id="message_form" role="alert" style="display:none"></div>

   <?php
$_ui->create_widget()->body('content', $body_claim)
    ->options('editbutton', false)
    ->body('class', '')
    ->header('title', '<h2>Claim Form ' . ($current_form == 'edit' && isset($claimDataEdit->ID) ? "edit ID: " . $claimDataEdit->ID . '</h2>' . '<a href="./#ajax/claim-form.php" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Create new Claim</a>' : ""))->print_html();
?>
<?php if(isset($_SESSION['global_acl']->$claim_form) && $_SESSION['global_acl']->$claim_form == 'Admin'){ ?>

<div class="modal animated fadeInDown" style="display:none; margin:auto; max-height:600px;" id="modal_overide_claim">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php include 'claim-form.transaction.overide.php'; ?>
        </div>
    </div>
</div>
    
<?php }else{

} ?>

</section>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/jquery-validate/jquery.validate.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/clockpicker/clockpicker.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/your_script.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/script/validator.plus.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/script/notes.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/script/claim-limit.model.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/script/claim-limit.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/script/claim-limit-list.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/script/claim-form.warranty.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/script/claim-form.transaction.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/script/claim-form.js"></script>
<?php if (hasIdParam() && isset($claimDataEdit->notes)) {
echo('<script> _notecontact.displayList('.json_encode($claimDataEdit->notes).')</script>');

} 
// if (isset($claimDataEdit->transactionID)) {
//     $transactiontmp = HTTPMethod::httpPost($link['_claimTransactionByID'], array(
//         'token' => $_COOKIE['token'],
//         'jwt' => $_COOKIE['jwt'],
//         'ID' => $claimDataEdit->transactionID,
//         'private_key' => $_COOKIE['userID'],
//     ));
//     if ($transactiontmp->ERROR == '') {
//         $transactionEdit = $transactiontmp->ClaimTransaction[0];
//     }
    // echo ('
    // <script>
    //     var listTransaction = ' . json_encode($transactionEdit->transaction) . ';
    //     var claimlimits = ' . json_encode($transactionEdit->claim[0]->warranty_claim_limit) . '
    //     _claimTransaction.createTableByClaimTransaction(listTransaction, claimlimits);
    // </script>');
// }else{
// echo('
// <script>
//     var claimlimits = ' . (isset($claimDataEdit->warranty_claim_limit) ? json_encode($claimDataEdit->warranty_claim_limit) : '[]')  . ';
//     _claimTransaction.createClaimLimitTableFooter(claimlimits);
// </script>');
// }

?>