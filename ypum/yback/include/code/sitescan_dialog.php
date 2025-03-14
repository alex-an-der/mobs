<div class="modal fade" id="ModalRollenzuweisung" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">

        <div class="modal-header" style="padding:35px 50px;">
        <h2>Zugang bearbeiten</h2>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body" style="padding:40px 50px;">
          <div id="role-dialog-alert" class="alert alert-warning" role="alert">
            Es wurden Einträge mit unterschiedlichen Rollen ausgewählt. 
            Es kann daher keine Vorauswahl angezeigt werden. 
            OK weist die Rollen dann wie ausgewählt neu zu.
	        </div>
          <form role="form" method="POST">
            <div class="form-group">
              <input type="hidden" name="ids" class="form-control" id="IDsToEdit">
            </div>
            <div class="form-group">
              <label for="psw"><span class="glyphicon glyphicon-eye-open"></span>Berechtigte Rollen</label>
              <small>Eine Mehrfachauswahl ist m&ouml;glich.</small>
              <select name="rollen[]" size="10" class="form-control" multiple>
                <?=$rollenangebot?>
              </select>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-danger btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Abbruch</button>
              <button type="submit" name='MACH_EDIT' class="btn btn-success btn-default pull-right"><span class="glyphicon glyphicon-remove"></span> Speichern</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div> 
</div>
