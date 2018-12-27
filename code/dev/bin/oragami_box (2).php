<?

define('ENVIRONMENT','dev');

require_once('../application/libraries/universal_functions.php');
require_once('../application/libraries/universal_definitions.php');

//print '<pre>';
//var_dump($_SERVER);

$sFileFolder = PUBLIC_HTML_PATH.'/uploads/dev/origami_box/';


if(count($_FILES))
{

  $iPhotoNum = $_REQUEST['photo_num'];
  //save the image
  
  $sExtension = get_extension_from_mime_type($_FILES['photo']["type"]);
  //print $sExtension;

  //print $sFileFolder;

  $iX = $iPhotoNum;

  $sFile = $iX.'_orig.'.$sExtension;
  $sUrl = 'http://officialloop.com/uploads/dev/origami_box/'.$sFile;

  $vRes = move_uploaded_file($_FILES["photo"]["tmp_name"], $sFileFolder.$sFile);

  $oImage = new Imagick($sFileFolder.$sFile);
  $iWidth = $oImage->getImageWidth();
  $iHeight = $oImage->getImageHeight();

  $iMaxWidth = 900;
  $iMaxHeight = 600;
  $fMultiplier = $fXMultiplier = $fYMultiplier = 1;

  if($iWidth > $iMaxWidth)
  {
    $fXMultiplier = $iMaxWidth / $iWidth;
    //var_dump($fMultiplier);
    $iWidth = (int)floor($iWidth * $fXMultiplier);
    $iHeight = (int)floor($iHeight * $fXMultiplier);
  }


  //var_dump($fMultiplier);

  if($iHeight > $iMaxHeight)
  {
    $fYMultiplier = $iMaxHeight / $iHeight;
    //var_dump($fMultiplier);
    $iWidth = (int)floor($iWidth * $fYMultiplier);
    $iHeight = (int)floor($iHeight * $fYMultiplier);
  }

  $fMultiplier = $fXMultiplier * $fYMultiplier;
  var_dump($fMultiplier);

  //var_dump($iHeight);
  //var_dump($iWidth);
  //print '<br>';

  //var_dump($vRes);

  //print '<img src="'.$sUrl.'">

  print '

  <br>
  <center>

 <canvas id="myCanvas" width="'.$iWidth.'" height="'.$iHeight.'"
style="border:1px solid #000000; background: url('.$sUrl.'?x='.uniqid().');
background-size: '.$iWidth.'px '.$iHeight.'px;


">
</canvas> 

<br><br>
<input type="button" value="Crop" onclick="submit_crop();">

<script type="text/javascript" src="/application/js/jquery.js?v=5" ></script>

<script>

var oCanvasOffset = $(\'#myCanvas\').offset(); 

var oNs = {};
oNs.iOffsetX = oCanvasOffset.left;
oNs.iOffsetY = oCanvasOffset.top;
oNs.oCanvas = document.getElementById("myCanvas");
oNs.oCtx = oNs.oCanvas.getContext("2d");
oNs.oCtx.strokeStyle = "red";
oNs.fMultiplier = '.$fMultiplier.';
oNs.sFile = "'.$sFile.'";
oNs.sCurrentUrl = "'.$_SERVER['HTTP_REFERER'].'";
oNs.iPhotoNum = '.$iPhotoNum.';

oNs.iCanvasWidth = ($("#myCanvas").width());
oNs.iCanvasHeight = ($("#myCanvas").height());


//console.log(oCanvasOffset);


//ctx.moveTo(0,0);
//ctx.lineTo(200,100);
//ctx.stroke();

function submit_crop()
{
  //console.log("submit_crop()");

  window.location.href = oNs.sCurrentUrl+"&stage=crop&photo_file="+oNs.sFile+"&x="+oNs.iLeftX+"&y="+oNs.iTopY+"&square="+oNs.iSquare+"&photo_num="+oNs.iPhotoNum+"&multiplier="+oNs.fMultiplier;
}


$("#myCanvas").mousedown(function(oEvent)
{
    oNs.bMouseDown = true;
    //console.log("onmousedown");
   oNs.iMouseDownX = oEvent.pageX - oNs.iOffsetX;
   oNs.iMouseDownY = oEvent.pageY - oNs.iOffsetY;

   //console.log(oNs);
});

$("#myCanvas").mouseup(function(event)
{
    //console.log("onmouseup");
    oNs.bMouseDown = false;
});

$("#myCanvas").mousemove(function(oEvent)
{
  if(oNs.bMouseDown)
  {
    //console.log("onmousemove");

     var vRes = oNs.oCtx.clearRect(0, 0, oNs.iCanvasWidth, oNs.iCanvasHeight);
     //console.log(vRes);
     oNs.iMouseMoveX = oEvent.pageX - oNs.iOffsetX;
     oNs.iMouseMoveY = oEvent.pageY - oNs.iOffsetY;

     //console.log(oNs);

     //console.log("mousedown pos: ("+oNs.iMouseDownX+", "+oNs.iMouseDownY+") mousemove pos: ("+oNs.iMouseMoveX+", "+oNs.iMouseMoveY+")");


     // get top left coordinates
     oNs.iLeftX = Math.min(oNs.iMouseDownX, oNs.iMouseMoveX);
     oNs.iTopY = Math.min(oNs.iMouseDownY, oNs.iMouseMoveY);

     oNs.iRightX = Math.max(oNs.iMouseDownX, oNs.iMouseMoveX);
     oNs.iBottomY = Math.max(oNs.iMouseDownY, oNs.iMouseMoveY);

     oNs.iWidth = oNs.iRightX - oNs.iLeftX;
     oNs.iHeight = oNs.iBottomY - oNs.iTopY;

     oNs.iSquare = Math.min(oNs.iWidth, oNs.iHeight);

     oNs.iSquareMultX = 1;
     oNs.iSquareMultY = 1;

     if(oNs.iMouseDownX > oNs.iMouseMoveX)
        oNs.iSquareMultX = -1;

     if(oNs.iMouseDownY > oNs.iMouseMoveY)
        oNs.iSquareMultY = -1;

     //console.log(oNs.iLeftX);
     // console.log(oNs.iTopY);
     // console.log(oNs.iWidth);
     // console.log(oNs.iHeight);
     oNs.oCtx.strokeRect(oNs.iMouseDownX, oNs.iMouseDownY, oNs.iSquare * oNs.iSquareMultX, oNs.iSquare * oNs.iSquareMultY);

     //oNs.oCtx.moveTo(oNs.iMouseDownX, oNs.iMouseDownY);
     //oNs.oCtx.lineTo(oNs.iMouseMoveX, oNs.iMouseMoveY);
     //oNs.oCtx.stroke();

  }
});

</script>



  ';

}
else
{
  //print '<pre>';
  //var_dump($_REQUEST);

  if(isset($_REQUEST['stage']) && $_REQUEST['stage'] == 'crop')
  {
    $sOrigFile = $_REQUEST['photo_file'];
    //$sCroppedFile = str_replace('orig', 'orig_cropped', $sOrigFile);
    $sCroppedFile = $_REQUEST['photo_num'].'_orig_cropped.png';
    $oImage = new Imagick($sFileFolder.$sOrigFile);
    $fMultInverse = 1 / $_REQUEST['multiplier'];
    
    $iX = floor($fMultInverse * $_REQUEST['x']);
    $iY = floor($fMultInverse * $_REQUEST['y']);
    $iSquare = floor($fMultInverse * $_REQUEST['square']);
    //var_dump($fMultInverse);
    //print '<br>';
    //var_dump($iSquare);
    //print '<br>';
    //var_dump($iX);
    //print '<br>';
    //var_dump($iY);
    //print '<br>';
    $oImage->cropImage($iSquare, $iSquare, $iX, $iY);

    //var_dump($sFileFolder.$sCroppedFile);

    //die();

    $oImage->writeImage($sFileFolder.$sCroppedFile);


    $sUrl = 'http://officialloop.com/uploads/dev/origami_box/'.$sCroppedFile;

    

    


    $iPhotoNum = $_REQUEST['photo_num'] + 1;

    if($iPhotoNum == 7)
    {
      //print '<pre>';
      //var_dump($_SERVER);
      //redirect($_SESSION);

      $aMap = array
      (
        1 => array
        (
          'side' => 'top',
          'left_image' => 'top|east|right|north',
          'right_image' => 'top|west|left|north',
        ),
        2 => array
        (
          'side' => 'front',
          'left_image' => 'front|south|bottom|south',
          'right_image' => 'front|north|top|south',
        ),
        3 => array
        (
          'side' => 'left',
          'left_image' => 'left|east|front|west',
          'right_image' => 'left|west|back|east',
        ),
        4 => array
        (
          'side' => 'back',
          'left_image' => 'back|north|top|north',
          'right_image' => 'back|south|bottom|north',
        ),
        5 => array
        (
          'side' => 'right',
          'left_image' => 'right|east|back|west',
          'right_image' => 'right|west|front|east',
        ),
        6 => array
        (
          'side' => 'bottom',
          'left_image' => 'bottom|east|left|south',
          'right_image' => 'bottom|west|right|south',
        ),                                        
      );

      $iImageSize = 96 * 3;

      foreach($aMap as $iIndex => $aData)
      {
        //print $iX;
        //print '<img src="http://officialloop.com/uploads/dev/origami_box/'.$iX.'_orig_cropped.png" width="200">';
        print '<img src="http://officialloop.com/uploads/dev/origami_box/'.$iIndex.'_sample.png" style="border:1px solid black;" xwidth="200">';

        //$oImage = new Imagick($sFileFolder.$iIndex.'_sample.png');
        $oImage = new Imagick($sFileFolder.$iIndex.'_orig_cropped.png');

        //resize each cropped images

        $sSide = $aData['side'];

        $oImage->resizeImage($iImageSize, $iImageSize, imagick::COLOR_BLACK, 1);
        $oImage->writeImage($sFileFolder.$sSide.'_resize_cropped.png');

        $oImage->rotateImage(new ImagickPixel('#00000000'), 45);
        $oImage->writeImage($sFileFolder.$sSide.'_rotated.png');

        //print '<img src="http://officialloop.com/uploads/dev/origami_box/'.$sSide.'_rotated.png" style="border:1px solid black;" xwidth="200">';

        $iSquare = $oImage->getImageWidth() / 2;

        //var_dump($iSquare);
        //print '<br>';

        foreach(array('west', 'north', 'east', 'south') as $sDir)
        {
          //print '<br>'.$sDir;

          switch($sDir)
          {
            case 'west':
              $iX = 0;
              $iY = 0;
              break;
            case 'north':
              $iX = $iSquare;
              $iY = 0;
              break;
            case 'east':
              $iX = $iSquare;
              $iY = $iSquare;
              break;                            
            case 'south':
              $iX = 0;
              $iY = $iSquare;
              break;              
          }
          $oImage = new Imagick($sFileFolder.$sSide.'_rotated.png');
          //print '<br>';
          //print '<br>';
          //var_dump($oImage->getImageWidth());
          //print '<br>';
          //var_dump($iSquare);
          //print '<br>';
          //var_dump($iX);
          //print '<br>';
          
          //var_dump($iY);
          //print '<br>';
          //var_dump($oImage->getImageWidth());
          //print '<br>';
          $oImage->setImagePage(0,0,0,0); // not sure why this is required
          $vRes = $oImage->cropImage($iSquare, $iSquare, $iX, $iY);
          //var_dump($oImage->getImageWidth());
          //print '<br>';
          //var_dump($vRes);
          $oImage->writeImage($sFileFolder.$sSide.'_'.$sDir.'.png');

          //print '<img src="http://officialloop.com/uploads/dev/origami_box/'.$sSide.'_'.$sDir.'.png" style="border:1px solid black;" xwidth="200">';
          //die();
        }


        //print '<br>';

        //imagick::COLOR_BLACK
      }

      foreach($aMap as $iIndex => $aData)
      {
        $sSide = $aData['side'];
        //print '<br>'.$sSide;

        foreach(array('left', 'right') as $sButtonSide)
        {
          print '<br>'.$sButtonSide;
          $aButtonData = explode('|', $aData[$sButtonSide.'_image']);
          print '<pre>';
          //var_dump($aButtonData);

          $oImage1 = new Imagick($sFileFolder.$aButtonData[0].'_'.$aButtonData[1].'.png');
          $oImage2 = new Imagick($sFileFolder.$aButtonData[2].'_'.$aButtonData[3].'.png');
//
          //print '<img src="http://officialloop.com/uploads/dev/origami_box/'.$aButtonData[0].'_'.$aButtonData[1].'.png" style="border:1px solid black;" xwidth="200">';
          //print '<img src="http://officialloop.com/uploads/dev/origami_box/'.$aButtonData[2].'_'.$aButtonData[3].'.png" style="border:1px solid black;" xwidth="200">';
          
          //$oImage1->writeImage($sFileFolder.'tmp.png');
          //print '<img src="http://officialloop.com/uploads/dev/origami_box/tmp.png" style="border:1px solid black;" xwidth="200">';

          switch($sButtonSide.'|'.$aButtonData[1])
          {
            case 'left|east':
            case 'right|west':

              $oImage1->rotateImage(new ImagickPixel('#00000000'), 270);             
              break;
            case 'left|south':
            case 'right|north':
              $oImage1->rotateImage(new ImagickPixel('#00000000'), 180);             
              break;              
          }

          switch($sButtonSide.'|'.$aButtonData[3])
          {
            case 'left|north':
            case 'right|south':
              $oImage2->rotateImage(new ImagickPixel('#00000000'), 180);              
              break;
            case 'left|west':
            case 'right|east':
              $oImage2->rotateImage(new ImagickPixel('#00000000'), 270);              
              break;
          }

          //$oImage1->writeImage($sFileFolder.'tmp.png');
          //print '<img src="http://officialloop.com/uploads/dev/origami_box/tmp.png" style="border:1px solid black;" xwidth="200">';

          //$oImage2->writeImage($sFileFolder.'tmp2.png');
          //print '<img src="http://officialloop.com/uploads/dev/origami_box/tmp2.png" style="border:1px solid black;" xwidth="200">';          
          

          //$src1->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
          //$src1->setImageArtifact('compose:args', "1,0,-0.5,0.5");
          $oImage1->compositeImage($oImage2, Imagick::COMPOSITE_DEFAULT, 0, 0);
          $oImage1->writeImage($sFileFolder.$sSide.'_merged_'.$sButtonSide.'.png');
          print '<br>';
          print '<img src="http://officialloop.com/uploads/dev/origami_box/'.$sSide.'_merged_'.$sButtonSide.'.png" style="border:1px solid black;" xwidth="200">';
          
          //die();
        }

      }



      foreach($aMap as $iIndex => $aData)
      {
        $sSide = $aData['side'];
        //print '<br>'.$sSide;

        $oImage1 = new Imagick($sFileFolder.$sSide.'_merged_left.png');
        $oImage2 = new Imagick($sFileFolder.$sSide.'_merged_right.png');

        $iSquare = $oImage1->getImageWidth();


        //var_dump($iSquare);

        $oImageFinal = new Imagick();
        //$draw = new ImagickDraw();
        //$oPixel = new ImagickPixel( '#f5f5f5' );

        $oImageFinal->newImage($iSquare * 4, $iSquare * 4, new ImagickPixel('#00000000'));
        $oImageFinal->compositeImage($oImage1, Imagick::COMPOSITE_DEFAULT, 0, $iSquare);
        $oImageFinal->compositeImage($oImage2, Imagick::COMPOSITE_DEFAULT, $iSquare * 3, $iSquare * 2);
        $oImageFinal->writeImage($sFileFolder.$sSide.'_final.png');
        print '<br>';
        print '<img src="http://officialloop.com/uploads/dev/origami_box/'.$sSide.'_final.png" style="border:1px solid black;" xwidth="200">';


      }

      die();
    }
    else
      print '<img src="'.$sUrl.'?a='.uniqid().'">';
  }
  else
    $iPhotoNum = 1;
  print '
  <br><br>
  Upload photo # '.$iPhotoNum.'<br><br>
  <form method="post" enctype="multipart/form-data">
    Send these files:<br />
    <input name="photo" type="file" /><br />
    <input type="hidden" name="photo_num" value="'.$iPhotoNum.'">
    <input type="submit" value="Upload File" />
  </form>


  ';
}