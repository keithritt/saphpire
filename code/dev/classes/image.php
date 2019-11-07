<?

class Image
{
  public static
    $iLgMaxWidth = 1200,
    $iMdMaxWidth = 960,
    $iSmMaxWidth = 680,
    $iXsMaxWidth = 320,
    $iLgThumbWidth = 160,
    $iMdThumbWidth = 80,
    $iSmThumbWidth = 40,
    $iXsThumbWidth = 20;

  //$sFile - is is the path and file name
  public function _save_upload($sPath, $sPrefix, $sSuffix = null)
  {
    if(isset($_FILES['file_data']))// default source for bootsrap file input
      $sIndex = 'file_data';

    if(isset($_FILES[$sIndex]['tmp_name']))
      $this->oSource = $_FILES[$sIndex]['tmp_name'];

    $this->sPath = rtrim($sPath, '/').'/'; // ensure there is 1 and only 1 trailing /

    $this->sPrefix = $sPrefix;

    if(!isset($sSuffix))
    {
      switch($_FILES[$sIndex]['type']) // TODO look into using Imagick::getImageFormat()
      {
        case 'image/png':
          $this->sSuffix = 'png';
          $this->iMimeTypeId = TYPE_MIME_PNG;
          break;
        case 'image/gif':
          $this->sSuffix = 'gif';
          $this->iMimeTypeId = TYPE_MIME_GIF;
          break;
        case 'image/jpeg':
        case 'image/jpg':
          $this->sSuffix = 'jpg';
          $this->iMimeTypeId = TYPE_MIME_JPG;
          break;
        default:
          Log::error('unknown image type: '.$_FILES[$sIndex]['type']);
      }
    }
    else
      $this->sSuffix = $sSuffix;

    $this->sFile = $this->sPrefix.'.'.$this->sSuffix;

    $this->oImagick = new Imagick($this->oSource);

    $bRes = $this->save();
  }

  public function save()
  {
    return $this->oImagick->writeImage($this->sPath.$this->sFile);
  }

  public function load_from_url($sUrl)
  {
    $this->oImagick = new Imagick($sUrl); //make sure it has http:// on calling code
  }

  public function load_from_path($sPath)
  {
    $this->oImagick = new Imagick($sPath);
    $this->oSource = $sPath;

    $this->sPath = substr($sPath, 0, strrpos($sPath, '/') + 1); //strip filname off the path

    switch($this->oImagick->getImageFormat()) // TODO look into using Imagick::getImageFormat()
    {
      case 'PNG':
        $this->sSuffix = 'png';
        $this->iMimeTypeId = TYPE_MIME_PNG;
        break;
      case 'GIF':
        $this->sSuffix = 'gif';
        $this->iMimeTypeId = TYPE_MIME_GIF;
        break;
      case 'JPEG':
        $this->sSuffix = 'jpg';
        $this->iMimeTypeId = TYPE_MIME_JPG;
        break;
      default:
        Log::error('unknown image type: '.$_FILES[$sIndex]['type']);
    }
  }

  function render()
  {
    if(Request::$bDebugMode)
    {
      $sOutput = ob_get_contents();
      if($sOutput != '')
        Log::error('output written before image Image->render()');
    }
    ob_clean();  // wipe out any accidentally trii
    header("Content-type: image/".$this->sSuffix);
    print $this->oImagick->getImageBlob();
    die();
  }


  public function copy()
  {
    $oNewImage = new Image();
    $oNewImage->sPath = $this->sPath;
    $oNewImage->sSuffix = $this->sSuffix;
    $oNewImage->iMimeTypeId = $this->iMimeTypeId;
    $oNewImage->oSource = $this->oSource;
    $oNewImage->oImagick = new Imagick($this->oSource);
    return $oNewImage;
  }

  public function _save_resized($sSize, $sPrefix = null, $sSuffix = null)
  {
    if(is_null($sPrefix))
      $sPrefix = time().uniqid('_');

    $oNewImage = $this->copy();

    switch($sSize)
    {
      case 'lg':
        $iMaxHeight = $iMaxWidth = self::$iLgMaxWidth;
        break;
      case 'md':
        $iMaxHeight = $iMaxWidth = self::$iMdMaxWidth;
        break;
      case 'sm':
        $iMaxHeight = $iMaxWidth = self::$iSmMaxWidth;
        break;
      case 'xs':
        $iMaxHeight = $iMaxWidth = self::$iXsMaxWidth;
        break;
    }

    $fMultiplier = 1;

    $iWidth = $this->get_width();
    $iHeight = $this->get_height();

    if($iWidth > $iMaxWidth)
    {
      $fMultiplier = $iMaxWidth / $iWidth;
      //var_dump($fMultiplier);
      $iWidth = (int)floor($iWidth * $fMultiplier);
      $iHeight = (int)floor($iHeight * $fMultiplier);
    }

    if($iHeight > $iMaxHeight)
    {
      $fMultiplier = $iMaxHeight / $iHeight;
      //var_dump($fMultiplier);
      $iWidth = (int)floor($iWidth * $fMultiplier);
      $iHeight = (int)floor($iHeight * $fMultiplier);
    }

    $oNewImage->oImagick->resizeImage($iWidth, $iHeight, imagick::COLOR_BLACK, 1);

    if(isset($sSuffix))
      $oNewImage->sSuffix = $sSuffix;
    $oNewImage->sFile = $sPrefix.'.'.$oNewImage->sSuffix;
    $bRes = $oNewImage->save();

    return $oNewImage;
  }

  public function _save_thumbnail($sSize, $sPrefix = null, $sSuffix = null)
  {
    if(is_null($sPrefix))
      $sPrefix = time().uniqid('_');

    $oNewImage = $this->copy();

    switch($sSize)
    {
      case 'lg':
        $iMaxWidth = self::$iLgThumbWidth;
        break;
      case 'md':
        $iMaxWidth = self::$iMdThumbWidth;
        break;
      case 'sm':
        $iMaxWidth = self::$iSmThumbWidth;
        break;
      case 'xs':
        $iMaxWidth = self::$iXsThumbWidth;
        break;
    }

    $oNewImage->oImagick->cropThumbnailImage($iMaxWidth, $iMaxWidth);

    if(isset($sSuffix))
      $oNewImage->sSuffix = $sSuffix;
    $oNewImage->sFile = $sPrefix.'.'.$oNewImage->sSuffix;
    $bRes = $oNewImage->save();

    return $oNewImage;
  }

  public function get_height()
  {
    if(!isset($this->iHeight))
      $this->iHeight = $this->oImagick->getImageHeight();
    return $this->iHeight;
  }

  public function get_width()
  {
      $this->iWidth = $this->oImagick->getImageWidth();    
      return $this->iWidth;
  }
}
