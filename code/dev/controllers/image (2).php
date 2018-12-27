<?

class Controller extends Domain
{
  function __construct()
  {
    //pr('image.php->__construct()');
    //stop();
    Perm::ignore();
    require_once(CODE_PATH.'/classes/image.php');
    parent::__construct();
  }

  function view($iImageId = null, $sImageType = null)
  {
    //pr('view('.$iImageId.', '.$sImageType.')');

    //sleep(1);

    switch($sImageType)
    {
      case 'xs':
        $iMaxWidth = Image::$iXsMaxWidth;
        $sGetColumn = 'xs_file_id';
        $bIsThumb = false;
        break;
      case 'sm':
        $iMaxWidth = Image::$iSmMaxWidth;
        $sGetColumn = 'sm_file_id';
        $bIsThumb = false;
        break;
      case 'md':
        $iMaxWidth = Image::$iMdMaxWidth;
        $sGetColumn = 'md_file_id';
        $bIsThumb = false;
        break;
      case 'lg':
        $iMaxWidth = Image::$iLgMaxWidth;
        $sGetColumn = 'lg_file_id';
        $bIsThumb = false;
        break;
      // thumbs
      case 'xst':
        $iMaxWidth = Image::$iXsThumbWidth;
        $sGetColumn = 'xs_thumb_id';
        $bIsThumb = true;
        break;
      case 'smt':
        $iMaxWidth = Image::$iSmThumbWidth;
        $sGetColumn = 'sm_thumb_id';
        $bIsThumb = true;
        break;
      case 'mdt':
        $iMaxWidth = Image::$iMdThumbWidth;
        $sGetColumn = 'md_thumb_id';
        $bIsThumb = true;
        break;
      case 'lgt':
        $iMaxWidth = Image::$iLgThumbWidth;
        $sGetColumn = 'lg_thumb_id';
        $bIsThumb = true;
        break;
    }

    $sSql = "
    SELECT
      images.album_id,
      images.orig_file_id,
      orig_file.url orig_url,
      orig_file.height orig_height,
      orig_file.width orig_width,
      get_file.url get_file_url
    FROM
      images
    JOIN
      image_files orig_file ON (orig_file.id = images.orig_file_id)
    LEFT JOIN
      image_files get_file ON (get_file.id = images.{$sGetColumn})
    WHERE
      images.id = ".(int)$iImageId;

   //expose($sSql);

    $aRow =  Db::$oBarmend->select_row($sSql); // @TODO - this should not be hard coded to barmend

    //expose($aRow);

    if(isset($aRow['get_file_url']))
    {
      //line();
      $this->oImage = new Image();
      $this->oImage->load_from_path(Image::barmend_url_to_path($aRow['get_file_url']));
      $this->oImage->render();
      //line();

    }
    else
    {
      //line();
      // see if the original image can be used as the desired image
      if($aRow['orig_height'] <= $iMaxWidth && $aRow['orig_width'] <= $iMaxWidth)
      {
        $iGetFileId = $aRow['orig_file_id'];
        $oGetImage= new Image();
        $oGetImage->load_from_path(Image::barmend_url_to_path($aRow['orig_url']));

      }
      else
      {

        //line();
        $this->oImage = new Image();
        //line();
        $this->oImage->load_from_path(Image::barmend_url_to_path($aRow['orig_url']));
        //line();
        if($bIsThumb)
        {
          //line();
          $oGetImage = $this->oImage->_save_thumbnail(substr($sImageType, 0, 2));
        }
        else
        {
          //line();

          $oGetImage = $this->oImage->_save_resized($sImageType);
        }

        //line();

        $oModel = Model::init('barmend', 'image_files', $this->oDb);
        $oModel->url = 'cdn.barmend.com/images/bars/'.$oGetImage->sFile;
        $oModel->height = $oGetImage->get_height();
        $oModel->width = $oGetImage->get_width();
        $oModel->mime_type_id = $oGetImage->iMimeTypeId;
        //line();
        $iGetFileId = $oModel->save();


        //expose($sGetColumn);
        //expose($iGetFileId);

        //stop();
      }


      $oModel = Model::init('barmend', 'images', $this->oDb);
      $oModel->force_update();
      $oModel->id = $iImageId;
      $oModel->$sGetColumn = $iGetFileId;
      //$oModel->save();

     // stop();

      //line();

      $oGetImage->render();

    }
  }



}