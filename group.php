<?php
require_once 'inc/init.php';
$page_nav["group"]['sub']['addgroup'] = true;
$page_title = "Group";
require_once '../php/link.php';

$userList = HTTPMethod::httpPost($link['_contactGetList'], array('token' => $_COOKIE['token'], 'jwt' => $_COOKIE['jwt'], 'private_key' => $_COOKIE['userID']));
$group = HTTPMethod::httpPost(HOST . '/_groupList.php', array('token' => $_COOKIE['token'], 'jwt' => $_COOKIE['jwt'], 'private_key' => $_COOKIE['userID']))->list;
$groupEdited = [];
if (hasIdParam()) {
    $groupEdited = HTTPMethod::httpPost(HOST . '/_groupGetByID.php', array('ID' => getID(), 'token' => $_COOKIE['token'], 'jwt' => $_COOKIE['jwt'], 'private_key' => $_COOKIE['userID']));
    if (isset($groupEdited->group)) {
        $groupEdited = $groupEdited->group;
        $groupEdited->users = json_decode($groupEdited->users);
    }
}
?>
<!-- <link rel="stylesheet" href="<?=ASSETS_URL?>/js/plugin/tree-generator/jquery.bonsai.css"> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/gojs/2.0.6/go-module.js"></script> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/gojs/2.0.6/go.js"></script> -->
<!-- <script src="<?=ASSETS_URL?>/js/plugin/gojs/release/go-module.js"></script> -->
<!-- <script src="<?=ASSETS_URL?>/js/plugin/gojs/release/go.js"></script> -->
<script>

</script>
<section id="widget-grid" class="">
   <div class="row">

      <article class="col-sm-12 col-md-12 col-lg-12">

         <div class="jarviswidget" id="wid-id-1" data-widget-editbutton="false" data-widget-custombutton="false">
            <header>
               <span class="widget-icon"> <i class="fa fa-users"></i> </span>
               <h2> Group </h2>
				</header>
            <div>
               <div class="jarviswidget-editbox"></div>
					<!-- end widget edit box -->

					<!-- widget content -->
					<div class="widget-body">
                  <div id="message_form" role="alert" style="display:none"></div>
                  <form class="smart-form" id="group_form" method="post">
                     <input type="hidden" name="ID" value="<?= isset($groupEdited->ID) ? $groupEdited->ID : '' ?>">
                     <fieldset>
                        <legend>Your group</legend>
                        <section id="your_group"></section>
                     </fieldset>
                     <fieldset>
                        <div class="row">
                           <section class="col col-6">
                              <label class="input">Unit</label>
                              <select name="department" class="form-control select2" style="width:100%"></select>
                           </section>
                           <section class="col col-6">
                              <label class="input">Group Name</label>
                              <select name="group_name" class="form-control select2" style="width:100%">
                              </select>
                           </section>
                        </div>
                        <div class="row">
                           <section class="col col-6">
                              <label class="input">Role</label>
                              <select name="role" class="form-control select2" style="width:100%"></select>
                           </section>
                           <section class="col col-6">
                              <label class="input">Users</label>
                              <select name="users" class="form-control select2" style="width:100%" multiple>
                                 <?php
                                    foreach ($userList as $item) {
                                       echo '<option value="' . $item->ID . '" ' . (isset($groupEdited->users) && in_array($item->ID, $groupEdited->users) ? ' selected' : '') . '>' . $item->first_name . ' ' . $item->last_name . '</option>';
                                    }
                                    ?>
                              </select>
                           </section>
                        </div>
                        <div class="row">
                           <section class="col col-6">
                              <label class="input">Parent Group</label>
                              <select name="parent_group" class="form-control select2" style="width:100%"></select>
                           </section>
                           <section class="col col-6">
                              <label class="input">Supervisor</label>
                              <select name="parent_id" class="form-control select2" style="width:100%"></select>
                           </section>
                        </div>
                     </fieldset>
                     <footer>
                        <button type="button" class="btn btn-sm btn-primary" id="btnSubmitGroup">Create</button>
                     </footer>
                  </form>
               </div>
            </div>
         </div>
      </article>
   </div>
</section>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/jquery-validate/jquery.validate.min.js"></script>
<script src="<?php echo ASSETS_URL; ?>/js/plugin/bootstrapvalidator/bootstrapValidator.min.js"></script>

<script>
   window.group_list = <?=json_encode($group)?>;
</script>
<script src="<?=ASSETS_URL?>/js/your_script.js"></script>
<script src="<?=ASSETS_URL?>/js/script/group/group.js"></script>
<script>

var _group = new Group();
_group.init();
<?php if (isset($groupEdited->ID)) {echo
    '_group.initUpdate(' . json_encode($groupEdited) . ');
   _group.setGroupName("' . $groupEdited->department . '", "' . $groupEdited->group_name . '");';}?>
</script>