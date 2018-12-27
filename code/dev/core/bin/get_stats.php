<?

//print 'db_stats';

$sSql = "DELETE FROM stats WHERE cat = 'table_row_count' AND date = ".Db::date('today');
Db::delete($sSql, 'master');

$aSchemas = array('master', 'barmend');

foreach($aSchemas as $sSchema)
{
		$sSql = "SHOW TABLES";
		$aRows = Db::select_rows($sSql, $sSchema);

		foreach($aRows as $aRow)
		{
			$sTable = current($aRow);
			$sSql = "SELECT COUNT(*) count FROM $sTable";
			$aRow = Db::select_row($sSql, $sSchema);
			
			if($aRow['count'])
			{
				//expose($aRow);
				record_stat($aRow['count'], 'table_row_count', $sSchema.'.'.$sTable, 'officialloop.com');
			}
		}
}



