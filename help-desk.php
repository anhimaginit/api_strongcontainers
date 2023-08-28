<?php
require_once 'inc/init.php';
require_once '../php/link.php';
header("X-XSS-Protection: 0");
$role_unit = HTTPMethod::httpPost($link['_roles'], array('token' => $_COOKIE['token'], 'jwt' => $_COOKIE['jwt'], 'private_key' => $_COOKIE['userID']));
$roleList = $role_unit->roles;
$unitList = $role_unit->units;
?>

<section id="widget-grid" class="">
   <div class="row">
      <article class="col-sm-12">
         <div class="jarviswidget jarviswidget-color-blueDark" id="wid-order" data-widget-colorbutton="false" data-widget-editbutton="false">
            <header>
               <span class="widget-icon"> <i class="fa fa-comments-o"></i> </span>
               <h2>Help push</h2>
            </header>
            <div>
               <div class="widget-body widget-body-overflowxy">
                  <form class="smart-form" id="help_form" method="post">
                     <fieldset>
                        <div class="row">
                           <section class="col col-6">
                              <label class="input">Choose File</label>
                              <input type="file" class="form-control" name="selectFile" required>
                           </section>
                           <section class="col col-6">
                              <label class="input">Title</label>
                              <input type="text" name="title" class="form-control" required>
                           </section>
                           <section class="col col-6">
                              <label class="input">Index</label>
                              <input type="number" name="index" class="form-control">
                           </section>
                        </div>
                        <div class="row">
                           <section class="col col-6">
                              <label class="input" data-toggle="tooltip" data-placement="top" title="Select roles can view the file">Role</label>
                              <select name="role" class="form-control select2" style="width:100%" multiple required>
                                 <option value="All"> All</option>
                                 <?php 
                                 foreach ($roleList as $role) {
                                    echo '<option value="'.$role.'">'.$role.'</option>';
                                 }
                                 ?>
                              </select>
                           </section>
                           <section class="col col-6">
                              <label class="input" data-toggle="tooltip" data-placement="top" title="Select unit can view the file">Unit</label>
                              <select name="unit" class="form-control select2" style="width:100%" multiple required>
                                 <option value="All"> All</option>
                                 <?php 
                                 foreach ($unitList as $unit) {
                                    echo '<option value="'.$unit.'">'.$unit.'</option>';
                                 }
                                 ?>
                             </select>
                           </section>
                        </div>
                     </fieldset>
                     <div class="padding-5">
                        <textarea name="contentFile"></textarea>
                     </div>
                     <footer>
                        <button type="button" class="btn btn-default">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                     </footer>
                  </form>
               </div>
            </div>
         <div>
      </article>
   </div>
</section>

<script>
   pageSetUp();
   var pagefunction = function() {
		CKEDITOR.replace( 'contentFile', { height: '380px', startupFocus : true} );
	};
	loadScript("./js/plugin/ckeditor/ckeditor.js", pagefunction);
</script>
<script src="./js/script/help-desk.js"></script>