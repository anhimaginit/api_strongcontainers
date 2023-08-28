<?php

use \SmartUI\Components\SmartForm;
$page_nav["roles"] = true;
require_once 'inc/init.php';
require_once '../inc/acl.php';

if ($_COOKIE['actor'] == 'Affiliate'
    || $_COOKIE['actor'] == 'Customer'
    || $_COOKIE['actor'] == 'Vendor'
    || $_COOKIE['actor'] == 'PolicyHolder') {
    echo ('<script>console.log("hasn\'t role");window.history.back();</script>');
}

?>

<section id="widget-grid" class="">

<?php
$smartui_tab_role = [];
$smartui_tab_form = [];
foreach(json_decode(urldecode($_COOKIE['global_acl'])) as $key => $value){
	$smartui_tab_role[$key] = str_replace('Form', ' Form', $key); 
	array_push($smartui_tab_form, $key);
}

function smartui_tab_role_content($tab){
	if($tab==null || $tab==''){
		return 'No data';
	}
	$_ACL = new ACLClass();
	$tab = str_replace(' ', '', $tab);

	$data = $_ACL->getAccessPermissions($tab);

	$_ui = new \SmartUI\UI();

	$_ui->start_track();

	$fields = array();

	$fields['head_attr'] = array(
		'type' => 'label',
		'col' => 3,
		'class' => 'bold',
		'properties' => 'Attribute',
	);
	$fields['head_read'] = array(
		'type' => 'label',
		'col' => 2,
		'properties' => 'View',
		'class' => 'text-left bold',
	);
	$fields['head_add'] = array(
		'type' => 'label',
		'col' => 2,
		'properties' => 'Add',
		'class' => 'text-left bold',
	);
	$fields['head_edit'] = array(
		'type' => 'label',
		'col' => 4,
		'properties' => 'Edit',
		'class' => 'text-left bold',
	);

	/** Check All */
	$fields['head_name_all'] = array(
		'type' => 'label',
		'col' => 3,
		'class' => 'bold',
		'properties' => '',
	);
	$fields['head_R_all'] = array(
		'type' => 'checkbox',
		'col' => 2,
		'properties' => array(
			'items' => array(
				array(
					'value' => 'read',
					'label' => 'All',
				),
			),
		),
		'class' => 'text-left bold',
	);
	$fields['head_A_all'] = array(
		'type' => 'checkbox',
		'col' => 2,
		'properties' => array(
			'items' => array(
				array(
					'value' => 'add',
					'label' => 'All',
				),
			),
		),
		'class' => 'text-left',
	);
	$fields['head_E_all'] = array(
		'type' => 'checkbox',
		'col' => 4,
		'properties' => array(
			'items' => array(
				array(
					'value' => 'edit',
					'label' => 'All',
				),
			),
		),
		'class' => 'text-left bold',
	);
	/**----End Check all -------- */

	$fieldset = array('head_attr', 'head_read', 'head_add', 'head_edit', 'head_name_all', 'head_R_all', 'head_A_all', 'head_E_all');
	$btnArray = array();
	foreach ($data as $field => $value) {
		if(!isset($value['add'])){
			array_push($btnArray, $field);
		}else if(isset($field) && startsWith($field, 'head')){

		}else{
			$fields[$field] = array(
				'type' => 'label',
				'col' => 3,
				'properties' => '+  '.ucwords(str_replace('_', ' ', $field)),
				'class' => '',
			);
			$fields[$field . '_read'] = array(
				'type' => 'checkbox',
				'col' => 2,
				'properties' => array(
					'items' => array(
						array(
							'value' => true,
							'label' => '',
							'checked' => $data[$field]['read']=='true' ? true : false,
						),
					),
				),
			);
			
			$fields[$field . '_add'] = array(
				'type' => 'checkbox',
				'col' => 2,
				'properties' => array(
					'items' => array(
						array(
							'value' => true,
							'label' => '',
							'checked' => $data[$field]['add']=='true' ? true : false,
						),
					),
				),
			);
			
			$fields[$field . '_edit'] = array(
				'type' => 'checkbox',
				'col' => 3,
				'properties' => array(
					'items' => array(
						array(
							'value' => true,
							'label' => '',
							'checked' => $data[$field]['edit']=='true' ? true : false,
						),
					),
				),
			);
			array_push($fieldset, $field);
			array_push($fieldset, $field . '_read');
			array_push($fieldset, $field . '_add');
			array_push($fieldset, $field . '_edit');
		}
	}
	$fieldset1 = [];
	foreach($btnArray as $btn){
		$fields[$btn] = array(
			'type' => 'label',
			'col' => 3,
			'properties' => $btn,
			'class' => '',
		);

		$fields[$btn . '_show'] = array(
			'type' => 'checkbox',
			'col' => 9,
			'properties' => array(
				'items' => array(
					array(
						'label' => '',
						'value' => true,
						'checked' => $data[$btn]['show']=='true' ? true : false,
					),
				),
			),
		);
		array_push($fieldset1, $btn);
		array_push($fieldset1, $btn . '_show');
	}
	
	$form = $_ui->create_smartform($fields);
	$form->fieldset(0, $fieldset);
	$form->fieldset(1, $fieldset1);

	$form->footer(function() use ($_ui, $tab) {
		return $_ui->create_button('Save', 'primary')->attr(array('type' => 'button', 'id' => 'btnSubmitTab'.$tab, 'onclick'=>"saveRule()"))->print_html(true);
	});

	$result = $form->print_html(true);
	return $result;
}
function createFormElement(){
	
	$level = 'User';
	$unit = 'Sales';
	$scoreLevel = ['Admin' => 1, 'Manager' => 2, 'Leader' => 3, 'User' => 4];

	if(isset($_COOKIE['global_acl']) && isset($_COOKIE['acl_short'])){
		foreach (json_decode(urldecode($_COOKIE['acl_short'])) as $group) {
			if ($scoreLevel[$group->level] < $scoreLevel[$level]) {
				$level = $group->level;
				$unit = $group->department;
			}
		}
	}
	$elementField = '' . SmartForm::print_field('acl_types', SmartForm::FORM_FIELD_SELECT,
	array(
		'id' => 'unit',
		'label' => 'Unit',
		'data' => ['Sales', 'Affiliate', 'Customer', 'Vendor', 'Employee', 'PolicyHolder', 'SystemAdmin'],
		'selected' => $unit
	), 3, true);

	$elementField .= '' . SmartForm::print_field('group', SmartForm::FORM_FIELD_SELECT,
	array(
		'label' => 'Group',
		'data' => [],
	), 3, true);

	$elementField .= '' . SmartForm::print_field('level', SmartForm::FORM_FIELD_SELECT,
	array(
		'id' => 'levels',
		'label' => 'Role',
		'data' => ['User', 'Leader', 'Manager','Admin'],
		'selected' => $level
	), 3, true);
	return $elementField;
}

?>

<div class="row">
	<div class="smart-form" id="message_form"></div>
	<div class="smart-form padding-5" id="role_form" method="POST" style="width:98%">
		<!-- <input type="hidden" name="ID" id="ID" value="<?= $_SESSION['int_acl']['ID'] ?>"> -->
		<?php

		echo createFormElement().'<div class="clearfix"></div>';
		
		$_ui->start_track();

		$tabs = $smartui_tab_role;

		$tab = $_ui->create_tab($tabs);

		$tab->active(key($tabs), true);

		foreach ($smartui_tab_form as $value) {
			$tab->content($value, smartui_tab_role_content($value));
		}

		$tab->print_html();

		?>
	</div>
</div>
</section>
<script type="text/javascript">
	$(document).ready(function(){
		var hidden = JSON.parse('<?=json_encode($smartui_tab_role)?>');
		for (let key in hidden) {
			$('#'+key).find('header').remove();
		};
	})

</script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/bootstrap-tags/bootstrap-tagsinput.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/jquery-validate/jquery.validate.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/bootstrapvalidator/bootstrapValidator.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/your_script.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/script/rule-form.js"></script>
