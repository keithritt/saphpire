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
  public function save_upload($sPath, $sPrefix, $sSuffix = null)
  {
    //print '<br>_save_upload('.$sPath.', '.$sPrefix.')';
    //hit();
    //expose($_FILES);
    //Debug::email_end();
    //die();
    if(isset($_FILES['file_data']))// default source for bootstrap file input
      $sIndex = 'file_data';

    //@TODO - allow for a default value of $sIndex

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
          $this->iMimeTypeId = TYPE::_MIME_PNG;
          break;
        case 'image/gif':
          $this->sSuffix = 'gif';
          $this->iMimeTypeId = TYPE::_MIME_GIF;
          break;
        case 'image/jpeg':
        case 'image/jpg':
          $this->sSuffix = 'jpg';
          $this->iMimeTypeId = TYPE::_MIME_JPG;
          break;
        default:
          Log::error('unknown image type: '.$_FILES[$sIndex]['type']);
      }
    }
    else
      $this->sSuffix = $sSuffix;

    $this->sFile = $this->sPrefix.'.'.$this->sSuffix;

    $this->oImagick = new Imagick($this->oSource);

    //line();

    //$oTmp1 = new Imagick($this->oSource);
    //$oTmp2 = new Imagick($this->oSource);

    //print '<br>pre resize: ';
    //print $oTmp1->getImageWidth();

    //$oTmp2->resizeImage(10, 10, imagick::COLOR_BLACK, 1);

    //print '<br>post resize: ';
    //print $oTmp1->getImageWidth();

    //$oTmp2->writeImage($this->sPath.$this->sFile);

    //print '<br>post write: ';
    //print $oTmp1->getImageWidth();

    //die();
    $bRes = $this->save();

    //var_dump($bRes);
    //$oImage;
  }

  public function save()
  {
    //hit();
    //die();
    //$this->sPath = '/home3/oloop/public_html/saphpire/assets/dev/images/';
   // line();
    //print '<br>save()';
    //print '<br>'.$this->sPath;
    if(!is_dir($this->sPath))
      mkdir($this->sPath, 0777, true); //@TODO - revist perms and recursive settings

    //line();

   // line();

    ////print '<br'.$this->sPath.$this->sFile;
    //line();
    return $this->oImagick->writeImage($this->sPath.$this->sFile);
    //line();
  }

  ///public_html/saphpire/assets/dev/barmend.com/albums/2/1/1508036952_59e2d158498ca.png
  //is viewable at:
  //http://barmend.com/assets/dev/barmend.com/albums/2/1/1508036952_59e2d158498ca.png

  //@TODO - allow customization/overriding/subclassing/traits
  public function load_from_url($sUrl)
  {
    $this->sUrl = $sUrl;

    //$this->sUrlPrefix = substr($sUrl, 0, strrpos($sUrl, '/') + 1); //strip filename off the url
    //hit();
    //$sUrl = $this->barmend_url_to_pathxx($sUrl);
    //print '<br>'.$sUrl;
    //$this->load_from_path($sUrl);
    //$this->load_from_path(SAPHPIRE_PATH.substr($sUrl, strpos($sUrl, '/assets/images')));

    //expose($sPath);
    //$this->oImagick = new Imagick($sUrl); //make sure it has http:// on calling code
    //$this->sPath = SAPHPIRE_PATH.substr($sUrl, strpos($sUrl, '/assets/images
        //expose($this->oImagick);
  }

  public function load_from_path($sPath)
  {
    //hit();
    $this->oImagick = new Imagick($sPath);
    $this->oSource = $sPath;

    //expose($sPath);
    //expose($this->oImagick->getFilename());
    $this->sPath = substr($sPath, 0, strrpos($sPath, '/') + 1); //strip filename off the path

    //strrpos(haystack, needle)
    //expose($this->sPath);
    //stop();


    //expose($this->oImagick->getImageFormat());

    switch($this->oImagick->getImageFormat()) // TODO look into using Imagick::getImageFormat()
    {
      case 'PNG':
        $this->sSuffix = 'png';
        $this->iMimeTypeId = TYPE::_MIME_PNG;
        break;
      case 'GIF':
        $this->sSuffix = 'gif';
        $this->iMimeTypeId = TYPE::_MIME_GIF;
        break;
      case 'JPEG':
        $this->sSuffix = 'jpg';
        $this->iMimeTypeId = TYPE::_MIME_JPG;
        break;
      default:
        Log::error('unknown image type: '.$_FILES[$sIndex]['type']);
    }
    //expose($this->oImagick);
  }

  //@TODO - update/replace this method
  public static function barmend_url_to_pathxx($sUrl)
  {
    //pr('barmend_url_to_path('.$sUrl.')');
    //return $sUrl;

    $sUrl = str_replace('http://', '', $sUrl);
    $sUrl = str_replace('cdn.barmend.com', '', $sUrl);

    $sRet =  SAPHPIRE_PATH.$sUrl;
    // url http://cdn.barmend.com/images/santiritter.com/4/7/1496426597_5931a86526e54.png
    //$sPath = '/home3/oloop/public_html/cdn/images/santiritter.com/'.$this->iBarId.'/'.$iAlbumId;

    $sRet = '/home3/oloop/public_html/cdn'.str_replace('http://cdn.barmend.com', '', $sUrl);

    //print($sRet);
    //print '<br><br><br>';
    //stop();
    return $sRet;
  }

  function render()
  {

    //hit();

    if(Request::$bDebugMode)
    {
      $sOutput = ob_get_contents();
      if($sOutput != '')
        Log::error('output written before image Image->render()');
    }
    //stop();
    ob_clean();  // wipe out any accidentally trii
    header("Content-type: image/".$this->sSuffix);
    print $this->oImagick->getImageBlob();
    die();
  }


  public function copy()
  {
    //hit();
    $oNewImage = new Image();
    $oNewImage->sPath = $this->sPath;
    //$oNewImage->sPrefix = $this->sPrefix;
    $oNewImage->sSuffix = $this->sSuffix;
    $oNewImage->iMimeTypeId = $this->iMimeTypeId;
    $oNewImage->oSource = $this->oSource;
    //unset($oNewImage->iHeight, $oNewImage->iWidth, $oNewImage->oImagick);
    $oNewImage->oImagick = new Imagick($this->oSource);
    return $oNewImage;
  }

  public function save_resized($sSize, $sPrefix = null, $sSuffix = null)
  {
    //print '<br>_save_resized('.$sSize.', '.$sPrefix.')';

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

    //$fMultiplier = $fXMultiplier * $fYMultiplier;

    //print '<br> $this->_get_width() = '.$this->_get_width();
    //print '<br> $oNewImage->_get_width() = '.$oNewImage->_get_width();


    $oNewImage->oImagick->resizeImage($iWidth, $iHeight, imagick::COLOR_BLACK, 1);

    //print '<br> $this->_get_width() = '.$this->_get_width();
    //print '<br> $oNewImage->_get_width() = '.$oNewImage->_get_width();

    if(isset($sSuffix))
      $oNewImage->sSuffix = $sSuffix;
    $oNewImage->sFile = $sPrefix.'.'.$oNewImage->sSuffix;
    $bRes = $oNewImage->save();

    //var_dump($bRes);




    //print '<br>end _save_resized('.$sSize.', '.$sPrefix.')';
    //die();


    return $oNewImage;
  }

  public function save_thumbnail($sSize, $sPrefix = null, $sSuffix = null)
  {
    //hit();

    if(is_null($sPrefix))
      $sPrefix = time().uniqid('_');

    //return;
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
    //print '<br>new filename = '.$oNewImage->sFile;
    $bRes = $oNewImage->save();

    //var_dump($bRes);


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
    //print '<br>_get_width()';
    //print '<br>this->iwidth = ';
    //@var_dump($this->iWidth);
    //print '<br>imagick->getimagewidth() = ';
    //var_dump($this->oImagick->getImageWidth());
    //if(!isset($this->iWidth)) //@TODO - having issues attempting to store the width - just always look it up for now
    {
      //print '<br>hit if';
      $this->iWidth = $this->oImagick->getImageWidth();
    }
    return $this->iWidth;
  }


}


