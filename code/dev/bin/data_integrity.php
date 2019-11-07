<?

$aNonNulls = array
(
  'master' => array
  (
    'md5_lookups' => array
    (
      'md5' => array
      (
        'action' => 'delete',
      ),
      'lookup' => array
      (
        'action' => 'delete',
      ),
    ),
  ),
);

foreach($aNonNulls as $sSchema => $aSchemaData)
{
  foreach($aSchemaData as $sTable => $aTableData)
  {
    foreach($aTableData as $sColumn => $aColumnData)
    {
      $sSql = "SELECT COUNT(*) count FROM $sTable WHERE $sColumn IS NULL OR $sColumn = ''";
      $aRow = Db::select_row($sSql, $sSchema);
      if($aRow['count'])
      {
        Log::error($aRow['count'].' null values found in $sSchema.$sTable.$sColumn');
        switch($aColumnData['action'])
        {
          case 'delete':
            $sSql = "DELETE FROM $sTable WHERE $sColumn IS NULL OR $sColumn = '' ";
            Db::delete($sSql, $sSchema);
            break;
        }
      }
    }
  }
}


$aUniques = array
(
  'master' => array
  (
    'md5_lookups' => array
    (
      'md5' => array
      (
        'action' => 'delete',
      ),
    ),
  ),
);

foreach($aUniques as $sSchema => $aSchemaData)
{
  foreach($aSchemaData as $sTable => $aTableData)
  {
    foreach($aTableData as $sColumn => $aColumnData)
    {
      $sSql = "SELECT $sColumn, COUNT(*) COUNT FROM $sTable GROUP BY $sColumn HAVING COUNT > 1";
      $aRows = Db::select_rows($sSql, $sSchema);
      
      switch($aColumnData['action'])
      {
        case 'delete':
          foreach($aRows as $aRow)
          {
            Log::error('Duplicate values ('.$aRow[$sColumn].') found in $sSchema.$sTable.$sColumn');
            $sVal = Db::esc($aRow[$sColumn]);
            $sSql = "SELECT MIN(id) id FROM $sTable WHERE $sColumn = $sVal";
            $aRow = Db::select_row($sSql, $sSchema);
            $iKeepId = $aRow['id'];
            $sSql = "DELETE FROM $sTable WHERE $sColumn = $sVal AND id <> $iKeepId";
            expose($sSql);
            Db::delete($sSql, $sSchema);
          }
          break;
      }        
    }
  }
}
