<?

$sHtml = file_get_contents('http://www.barmend.com/');

print '<textarea>';
print $sHtml;
print '</textarea>';