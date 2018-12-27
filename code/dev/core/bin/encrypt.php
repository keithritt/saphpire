<?

if(isset($_REQUEST['q']))
{

    $sWord = $_REQUEST['q'];
    //pr($sWord);
    $sWord = trim($sWord);
    //pr($sWord);
    $sWord = strtolower($sWord);
    //pr($sWord);
    $sWord = md5($sWord);
    //pr($sWord);
    $sWord = substr($sWord, 0, 10);
    //pr($sWord);
    $sWord = strtolower($sWord);
    print $sWord;

}
else 
  print 'add ?q=word_to_encrypt to the url';