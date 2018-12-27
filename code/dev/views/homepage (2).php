


<div class="container">
  <?
    if(isset($sGoodMsg))
      print '<div class="alert alert-success">'.$sGoodMsg.'</div>';   
    if(isset($sBadMsg))
      print '<div class="alert alert-danger">'.$sBadMsg.'</div>';

    if(isset($sContent))
        print $sContent;
  ?>


</div>