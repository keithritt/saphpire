<?

$sContent = '';

$sSql = "
SELECT
	sub_cat,
	stat
FROM
	stats
WHERE
	cat = 'table_row_count' AND
	date = ".Db::date('yesterday')." AND
	stat > 0
ORDER BY
	stat DESC";
//expose($sSql);

$aRows = Db::select_rows($sSql, 'master');

//expose($aRows);

foreach($aRows as $aRow)
{
		$sContent.= $aRow['sub_cat'].' : '.$aRow['stat']."\n";
		
}

//expose($sContent);


$aParams = array(
  'vTo' => 'mail@keithritt.net',
  'sSubject' => 'Daily Status Email',
  'sContent' => $sContent);
Mail::save($aParams);
