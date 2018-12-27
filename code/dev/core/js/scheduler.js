function add_cron()
{
  //return;
  //console.log('edit_cron('+iCronId+')');
  $('#edit_cron_modal_title').html('Add Cron:');
  $('#cron_id').val('new');
  $('#file').val();
  $('#cron').val();
  $('#status').val();
  $('#status').selectpicker('render');
  $('#group').val();
  $('#edit_cron_modal').modal('show');

}

function edit_cron(iCronId)
{
  console.log('edit_cron('+iCronId+')');
  $('#edit_cron_modal_title').html('Edit Cron:' + iCronId);
  $('#cron_id').val(iCronId);
  $('#file').val($('#file_' + iCronId).html());
  $('#cron').val($('#cron_' + iCronId).html());
  $('#status').val($('#status_' + iCronId).html());
  $('#status').selectpicker('render');
  $('#group').val($('#group_' + iCronId).html());
  $('#edit_cron_modal').modal('show');

}

function delete_cron(iCronId)
{
  console.log('delete_cron('+iCronId+')');
  if(confirm('Are you sure you want to delete this entry?'))
  {
    oNs.ajax({
      type: 'POST',
      url: '/report/ajax/scheduler',
      data: {
        action: 'delete_cron',
        cron_id: iCronId
      },
      success: function()
      {
        //console.log('success');
        run_report('scheduler');
      },
      error: function(data){
          //console.log('error');
        }
    });

    $('#edit_cron_modal').modal('hide');
  }
}

function save_cron()
{
  //console.log('save_cron()');
    oNs.ajax({
      type: 'POST',
      url: '/report/ajax/scheduler',
      data: {
        action: 'save_cron',
        cron_id: $('#cron_id').val(),
        file: $('#file').val(),
        cron: $('#cron').val(),
        status: $('#status').val(),
        group: $('#group').val()
      },
      success: function()
      {
        //console.log('success');
        run_report('scheduler');
      },
      error: function(data){
          //console.log('error');
        }
    });

    $('#edit_cron_modal').modal('hide');
}

function run_now(iCronId)
{
  if(confirm('Are you sure you want to run this job now?'))
  {
  //console.log('save_cron()');
    oNs.ajax({
      type: 'POST',
      url: '/report/ajax/scheduler',
      data: {
        action: 'run_now',
        cron_id: iCronId
      },
      success: function()
      {
        //console.log('success');
        run_report('scheduler');
      },
      error: function(data){
          //console.log('error');
        }
    });
  }
}