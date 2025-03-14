

<div class="modal fade" id="RollenDialog" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">

        <div class="modal-header" style="padding:35px 50px;">
        <h2>Rolle #<span id='r_rollennummer'></span> bearbeiten</h2>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body" style="padding:40px 50px;">

          <form name="rollenform" role="form" method="POST">

            <input type="hidden" id="r_bit" name="r_bit"/>

            <div class="form-row">       
              <div class="col col-12 col-sm-4">
                Name:  
              </div>
              <div class="col col-12 col-sm-8">
                <input id ="r_name" class="form-control" type="text" name="r_name">
              </div>
            </div>

            <div class="form-row">       
              <div class="col col-12 col-sm-4">
                Beschreibung:  
              </div>
              <div class="col col-12 col-sm-8">
                <textarea rows=6 id ="r_role_comment" class="form-control" type="text" name="r_role_comment"></textarea>
              </div>
            </div>

            <div class="modal-footer">
              <button type="reset" id="f_leeren" class="btn btn-primary btn-default pull-left"><span class="glyphicon glyphicon-remove"></span> L&ouml;sche Eingaben</button>
              <button type="button" class="btn btn-danger btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Abbruch</button>
              <button type="submit" name='MACH_EDIT' class="btn btn-success btn-default pull-right"><span class="glyphicon glyphicon-remove"></span> Speichern</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div> 
</div>
