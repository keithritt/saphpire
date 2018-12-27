<?

//line();

$aTmp = array();
$aTmp['id'] = 'edit_album_modal';
$aTmp['title'] = 'Add New Album';
$aTmp['body'] = '
<form class="form-horizontal">';
$aTmp['body'].= Form::get_hidden('album_id', 'new');
$aTmp['body'].= '
  <div class="form-group">
    <label class="col-sm-2 control-label">Title:</label>
    <div class="col-sm-10">';
$aTmp['body'].= Form::get_textbox('album_title');
//@TODO - get date picker workin
//$aTmp['body'].= '
//    </div>
//    <br><br>
//    <label class="col-sm-2 control-label">Date:</label>
//    <div class="col-sm-10">';
//$aTmp['body'].= Form::_get_date_picker('album_date');
$aTmp['body'].= '
    </div>';
$aTmp['body'].= '
    <label class="col-sm-2 control-label">Status:</label>
    <div class="col-sm-10">';

//expose(Type::_STATUS);
$aTypes = Util::get_types(TYPE::_STATUS);

//expose($aTypes);

$aStatuses = array();

foreach($aTypes as $aType)
{
  if(in_array($aType['display'], array('Active', 'New')))
    $aStatuses[$aType['id']] = $aType['display'];
}
//expose($aStatuses);
//stop();

$aTmp['body'].= Form::get_dropdown('album_status_id', $aStatuses, TYPE::_STATUS_NEW);

//$aTmp['body'].= get_type_dropdown(PARENT_TYPE_INTERVAL,'special_interval_id', array('onchange' => 'change_interval();', 'autocomplete'=> 'off'));
$aTmp['body'].= '
    </div>
    <label class="col-sm-2 control-label">Date:</label>
    <div class="col-sm-10">';
$aTmp['body'].= Form::get_date_picker('album_date');
        $this->add_footer_js("
    var oDp = new oDatePicker();
    oDp.init('album_date',null, null);");

$aTmp['body'].= '
    </div>';
$aTmp['body'].= '
    <br><br>
  </div>
</form>';
$aTmp['footer'] = '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
<button type="button" class="btn btn-default" onclick="save_album();">Save Album</button>';
$this->add_modal($aTmp);

$this->get_view('core/views/modal.php', 'edit_album_modal');