<?



class Controller extends Domain
{
  function __construct()
  {
    //hit();
    //stop();
    Perm::ignore();
    $this->sSchema = Request::$sDefaultSchema;
    parent::__construct();
    require_once(CODE_PATH.'/core/classes/image.php');
    //Request::$bIsAjax = true; // technically not true - but helps avoid 'not including start and end views' error
  }

  function set_schema($sSchema)
  {
    //hit();
    $this->sSchema = $sSchema;
  }

  function view($iImageId = null, $sImageType = null)
  {
    //pr('view('.$iImageId.', '.$sImageType.')');
    //hit();
    //stop();

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
      orig_file.path orig_path,
      orig_file.height orig_height,
      orig_file.width orig_width,
      get_file.url get_file_url,
      get_file.path get_file_path
    FROM
      images
    JOIN
      image_files orig_file ON (orig_file.id = images.orig_file_id)
    LEFT JOIN
      image_files get_file ON (get_file.id = images.{$sGetColumn})
    WHERE
      images.id = ".(int)$iImageId;

   //expose($sSql);
   //@TODO - doing this for santi
   //$this->sSchema = 'barmend';
   //expose($this->sSchema);

    //expose(Request::$sDefaultSchema);

    $oDb = Db::static_init($this->sSchema);

    //expose($oDb);

    $aRow =  $oDb->select_row($sSql);

    //expose($aRow);

    if(isset($aRow['get_file_path']))
    {
      //line();
      $this->oImage = new Image();
      //$this->oImage->load_from_path(Image::barmend_url_to_path($aRow['get_file_url']));
      $this->oImage->load_from_path($aRow['get_file_path']);
      $this->oImage->render();
      //line();

    }
    else
    {
      //line();
      // see if the original image can be used as the desired image
      //expose($aRow);
      if($aRow['orig_height'] <= $iMaxWidth && $aRow['orig_width'] <= $iMaxWidth)
      {
        $iGetFileId = $aRow['orig_file_id'];
        $oGetImage= new Image();
        $oGetImage->load_from_path($aRow['orig_path']);

      }
      else
      {

        //line();
        $this->oImage = new Image();
        //line();
        //expose($aRow['orig_url']);
        $this->oImage->load_from_path($aRow['orig_path']);
        //line();
        //line();
        if($bIsThumb)
        {
          //line();
          //pr($sImageType);
          $oGetImage = $this->oImage->save_thumbnail(substr($sImageType, 0, 2));
          //line();
        }
        else
        {
          //line();

          $oGetImage = $this->oImage->save_resized($sImageType);
        }

        //line();

        //$iAlbumId = Util::coalesce($aRow['album_id'], 0);
        //expose($iAlbumId);

        $oModel = Model::init($this->sSchema, 'image_files', $oDb);
        //assets/images/domains/barmend.com/uploads/1456198183_56cbd227a23ef.jpg
        //$oModel->url = Request::$sDomain.'/assets/images/schemas/'.$this->sSchema.'/'.$oGetImage->sFile;
        $oModel->height = $oGetImage->get_height();
        $oModel->width = $oGetImage->get_width();
        $oModel->mime_type_id = $oGetImage->iMimeTypeId;
        $oModel->path = $oGetImage->sPath.$oGetImage->sFile;
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