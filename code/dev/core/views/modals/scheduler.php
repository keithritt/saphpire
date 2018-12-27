<?

   $aTmp = array();
        $aTmp['id'] = 'edit_cron_modal';
        $aTmp['title'] = 'Add New Cron';
        $aTmp['body'] = '
        <form class="form-horizontal">';
        $aTmp['body'].= Form::get_hidden('cron_id', 'new');
        $aTmp['body'].=
        '<label class="col-sm-2 control-label">File:</label>
              <div class="col-sm-10">';
        $aTmp['body'].= Form::get_textbox('file');
        $aTmp['body'].= '</div>';

        $aTmp['body'].=
        '<label class="col-sm-2 control-label">Cron:</label>
              <div class="col-sm-10">';
        $aTmp['body'].= Form::get_textbox('cron');
        $aTmp['body'].= '</div>';

        $aTmp['body'].=
        '<label class="col-sm-2 control-label">Status:</label>
              <div class="col-sm-10">';



        //require_once(APPPATH.'libraries/universal_definitions.php');

        $aStatuses = array(
          TYPE::_SCHEDULER_STATUS_DISABLED => 'Disabled',
          TYPE::_SCHEDULER_STATUS_READY => 'Ready',
          TYPE::_SCHEDULER_STATUS_RUNNING => 'Running',

          );

        $aTmp['body'].= Form::get_dropdown('status', $aStatuses);
        $aTmp['body'].= '</div>';

        $aTmp['body'].=
        '<label class="col-sm-2 control-label">Group:</label>
              <div class="col-sm-10">';
        $aTmp['body'].= Form::get_textbox('group');
        $aTmp['body'].= '</div>';

        $aTmp['footer'] = '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default" onclick="save_cron();">Save Cron</button>';

$this->add_modal($aTmp);

$this->get_view('core/views/modal.php', 'edit_scheduler_modal');