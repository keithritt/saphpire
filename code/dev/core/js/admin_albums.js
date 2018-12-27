
function create_album()
{
  console.log('create_album()');
  $('#edit_album_modal').modal('show');
  //return;
  $('#edit_album_modal_title').html('Create Album');
  $('#edit_album_modal_updt_msg').html('');
  $('#album_id').val('new');
  $('#album_title').val('');
  $('#album_date').val('');
}

function edit_album(iAlbumId)
{
  console.log('edit_album('+iAlbumId+')');
  $('#edit_album_modal').modal('show');
  $('#edit_album_modal_title').html('Edit Album');
  $('#edit_album_modal_updt_msg').html('');
  $('#album_id').val(iAlbumId);
  oNs.ajax({
    type: 'POST',
    url: '/admin/albums/get',
    data: {
      album_id: iAlbumId
    },
    success: function(oData)
    {
      oData = jQuery.parseJSON(oData);
      console.log(oData);

      $('#album_title').val(oData.title);
      $('#album_date').val(oData.album_date);
      $('#album_status_id').val(oData.status_id);
      $('#album_status_id').selectpicker('render');


    },
    error: function(data){
        //console.log('error');
      }
  });

}

function delete_album(iAlbumId)
{
  if(confirm('Are you sure you want to remove this album?'))
  {

    oNs.ajax({
      type: 'POST',
      url: '/admin/albums/delete',
      data: {
        album_id: iAlbumId
      },
      success: function()
      {
        run_report('bar_albums');
      },
      error: function(data){
          //console.log('error');
        }
    });
  }
}

function save_album()
{
  //console.log('save_special()');
  //console.log($('#special_price').val());
    oNs.ajax({
      type: 'POST',
      url: '/admin/albums/save',
      data:
      {
        id: $('#album_id').val(),
        title: $('#album_title').val(),
        date: $('#album_date').val(),
        status_id: $('#album_status_id').val(),
      },
      success: function(oData)
      {
        //console.log('success');
        //console.log(oData);
        oData = jQuery.parseJSON(oData);

        if(oData.success)
        {
          if($('#album_id').val() == 'new')
          {
            $('#edit_album_modal_updt_msg').html('<div class="alert alert-success">'+oData.msg+'</div>');
            $('#album_title').val('');
            $('#album_date').val('');
          }
          else
            $('#edit_album_modal').modal('hide');

          $('#edit_album_modal').modal('hide'); // @TODO - this makes the response message pointless




          run_report('domain_admin_albums');


        }
        else
          $('#edit_albums_modal_updt_msg').html('<div class="alert alert-danger">'+oData.msg+'</div>');
      },
      error: function(data)
      {
          //console.log('error');
        }
    });
}