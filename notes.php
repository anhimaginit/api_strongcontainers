<fieldset>
<div class="clearfix"></div>
   <a href="#collaped_notes" data-toggle="collapse" class="" aria-expanded="true"><legend title="Scroll notes">Notes <i class="fa fa-link"></i></legend></a>
   <div class="panel-collapse collapse in" id="collaped_notes" style="max-height:500px; overflow-y:scroll;">
   <table id="table_note_info" class="table table-bordered" style="table-layout: auto; width: 100%;">
      <thead>
         <tr>
            <th>Created Date</th>
            <th>Note</th>
            <th>Type</th>
            <th class="hidden">TypeID</th>
            <th>Entered By</th>
            <th class="hidden">ContactID</th>
            <th class="hidden">NoteID</th>
            <th class="hidden">TypeID</th>
         </tr>
      <thead>
      <tbody>
         <?php 
         /** <tr>
         *   <td class="hasinput"><input type="text" class="form-control" value="<?=date("Y-m-d")?>" readonly></td>
         *   <td class="hasinput"><input type="text" class="form-control" id="note_note"></td>
         *   <td class="hasinput"><input type="text" class="form-control" value="<?= ucfirst($type_note) ?>" readonly></td>
         *   <td class="hidden"></td>
         *   <td class="hasinput"><input type="text" class="form-control" readonly value="<?= urldecode($_COOKIE['user_name']) ?>"></td>
         *   <td class="hidden"></td>
         *   <td class="hidden"></td>
         *   <td class="hidden"></td>
         *</tr> */
         ?>
      </tbody>
   </table>
   </div>
</fieldset>