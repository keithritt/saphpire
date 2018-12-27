oNs.create_public_hash = function(sWord) // requires including the md5 library
{
  console.log(sWord);
  //var sOrigWord = sWord;
  var iTs = Math.floor(Date.now() / 1000);
  //sWord = iTs + sWord;
  //sWord = $.trim(sWord);
  //console.log(sWord);
  //sWord = sWord.toLowerCase();
  //console.log(sWord);
  sWord = md5(sWord);
  //console.log(sWord);
  sWord = sWord.substr(0, 10);
  sWord = sWord.toLowerCase();

  console.log(sWord);
  sWord = iTs + sWord;
  sWord = md5(sWord);
  sWord = sWord.substr(0, 10);
  //console.log(sWord);
  sWord = sWord.toLowerCase();
  console.log(sWord);
  return iTs + '|' + sWord;
}

oNs.log_in = function()
{
  //console.log('oNs.log_in()');
  var sHash = oNs.create_public_hash($('#password').val());
  console.log(sHash);
  $('#pw_hash').val(sHash);
  //return false;
  return true;
}
