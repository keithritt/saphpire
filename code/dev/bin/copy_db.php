<?



// assigning to vars for clarity
$sTo = ENV;
$sFrom = 'prod';

$oCopy = new DBCopy($sTo, $sFrom);
//expose($oCopy->aProdDbs);
//$oCopy->sync_tables();

$oCopy->sync_data();

class DBCopy
{
	function __construct($sTo, $sFrom)
	{
		Log::pause();
		pr('DBCopy->__construct()');
		$this->aSchemas = array(
			'master',
			'barmend'
		);

		$this->aSkipList = array();
		$this->aSkipList['master'] = array(
			'stats',
			'errors',
			'logs',
			'md5_lookups',
			'page_views',
			'sessions',
			);

		if($sTo == 'prod')
			die('cannot copy to prod');

		$this->sToEnv = $sTo;
		$this->sFromEnv = $sFrom;


		$this->aDirections = array();
		$this->aDirections['to'] = $this->sToEnv;
		$this->aDirections['from'] = $this->sFromEnv;

		$this->aDbs = array();
		$this->aDbs['to'] = array();
		$this->aDbs['from'] = array();


		foreach($this->aSchemas as $sSchema)
		{
			$this->aDbs['to'][$sSchema] = new Db($sSchema, $this->sToEnv);
			$this->aDbs['from'][$sSchema] = new Db($sSchema, $this->sFromEnv);
		}

		//expose($this->aDbs);
		//stop();
	}



	function sync_tables()
	{
		pr('sync_tables()');
		foreach($this->aSchemas as $sSchema)
		{
			pr($sSchema);
			$aToTables = $this->get_table_list($sSchema, 'to');
			//expose($aToTables);
			//die();
			$aFromTables = $this->get_table_list($sSchema, 'from');
			//expose($aFromTables);

			//delete any tables in the TO list that are not in the FROM

			foreach($aToTables as $sTable)
			{
				//if(!in_array($sTable, $aFromTables))
				{
					pr($sTable);
					$this->drop_table($sSchema, $sTable);
				}
			}

			pr('at this point all extra TO tables should be dropped');

			foreach($aFromTables as $sTable)
			{
				$this->create_table($sSchema, $sTable);
				//stop();

			}

			//stop();

		}
	}

	// this function makes the assumption that the table exists in both envs
	function create_table($sSchema, $sTable)
	{
		pr('create_table('.$sSchema.', '.$sTable.')');
		//$sSql = "select * from information_schema.tables where table_name = ".Db::esc($sTable);
		//$sSql = "DESC ".$sTable;
		$sSql = "SHOW CREATE TABLE ".$sTable;
		pr($sSql);
		//stop();
		$aRow = $this->aDbs['from'][$sSchema]->select_row($sSql);
		expose($aRow);
		$sSql = $aRow['Create Table'];
		pr($sSql);
		$vRes = $this->aDbs['to'][$sSchema]->admin($sSql);
		expose($vRes);
		//stop();
	}

	function get_table_list($sSchema, $sDirection)
	{
		$aRet = array();
		$sSql = "SHOW TABLES";
		//expose()
		$aRows = $this->aDbs[$sDirection][$sSchema]->select_rows($sSql);
		foreach($aRows as $aRow)
		{
			$aRet[] = $aRow['Tables_in_oloop_'.$sSchema.'_'.$this->aDirections[$sDirection]];
		}

		//expose($aRet);
		return $aRet;
	}

	// only drop tables in to ENV
	function drop_table($sSchema, $sTable)
	{
		$sSql = "DROP TABLE ".$sTable;

		pr($sSql);

		$this->aDbs['to'][$sSchema]->admin($sSql);
		//stop();
	}

	function get_row_count($sSchema, $sTable)
	{
		$sSql = "SELECT COUNT(*) count FROM $sTable";
		$aRow = $this->aDbs['from'][$sSchema]->select_row($sSql);
		//expose($aRow);
		return $aRow['count'];
		//stop();

	}

	// this assumes that all tables have been created in the TO env - but are all empty
	function sync_data()
	{

		foreach($this->aSchemas as $sSchema)
		{
			$aFromTables = $this->get_table_list($sSchema, 'from');

			expose($aFromTables);
			//stop();
			$aRowCounts = array();
			foreach($aFromTables as $sTable)
			{
				print 'synching '.$sTable;
				if(in_array($sTable, $this->aSkipList[$sSchema]))
				{
					pr('skipping '.$sTable.' because it is in the skip list');
					continue;
				}
				//pr($sTable);
				if($this->get_row_count($sSchema, $sTable) == 0)
				{
					pr('skipping '.$sTable.' because it has no rows');
					continue;
				}
				$this->fill_data($sSchema, $sTable);
				//check_time();
				//check_mem();
			}

			//expose($aRowCounts);
		}
	}

	function fill_data($sSchema, $sTable)
	{
		pr('fill_data('.$sSchema.', '.$sTable.')');
		$sSql = 'TRUNCATE TABLE '.$sTable;
		$this->aDbs['to'][$sSchema]->admin($sSql);

		$sSql = "SELECT * FROM $sTable";

		// fully expect this to give us memory issues in the future
		$aRows = $this->aDbs['from'][$sSchema]->select_rows($sSql);

		//expose($aRows);

		$oModel = Model::init($sSchema, $sTable, $this->aDbs['to'][$sSchema]);

		foreach($aRows as $aRow)
		{
			$oModel->clear();
			foreach($aRow as $sColumn => $vData)
			{
				$oModel->$sColumn = $vData;
				//pr($sColumn);
				//pr($vData);
			}
			$oModel->save();
			check_time();
			check_mem();			
		}
		//stop();
	}
}