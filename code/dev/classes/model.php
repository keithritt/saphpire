<?

class Model implements Iterator
{
	protected
		$oDb = null,
		$aData = array(),
		$sGetSqlStart = null,
		$sSchema = null,
		$sFqSchema = null,
		$sTable = null,
		$sFqTable = null,
		$vExpected = null,
		$iCount = 0,
		$iCursor = 0,
		$bCountMismatch = false,
		$bAudit = false,
		$sMode = 'insert',
    $sLastSql = null,
		$aSubModels = array(), // if the model uses other models to save data - try to avoid infinite loops
		$aMetaData = array() // if we save any data that isnt direct stored in a column in $sTable
		;

    function rewind() {
        $this->iCursor = 0;
    }

    function current() {
        return $this->aData[$this->iCursor];
    }

    function key() {
        return $this->iCursor;
    }

    function next() {
        $this->iCursor++;
    }

    function valid() {
        return isset($this->aData[$this->iCursor]);
    }

   public function __isset($sLookup)
   {
   		return isset($this->aData[$this->iCursor][$sLookup]);
   }



	public static function init($sSchema, $sTable, $oDb)
	{
		switch($sSchema.'.'.$sTable)
		{
			case 'foo.bar':
				require_once(CODE_PATH.'/models/'.$sSchema.'/'.$sTable.'_model.php');
				$sClass = ucfirst($sSchema).ucfirst($sTable).'Model';
				return new $sClass($oDb);
				break;
			default: // assume it is just a table name
				return new Model($sSchema, $sTable, $oDb);
		}
	}

	public function __construct($sSchema, $sTable, $oDb)
	{
		$this->sSchema = $sSchema;
		$this->sFqSchema = SCHEMA_PREFIX.$sSchema.'_'.DB_ENV;
		$this->sTable = $sTable;
		$this->sFqTable = $this->sFqSchema.'.'.$sTable;
		$this->oDb = $oDb;
	}

	public function clear()
	{
		$this->aData = array();
		$this->aSubModels = array();
		$this->sMode = 'insert';
	}

	public function count()
	{
		return count($this->aData);
	}

	public function __get($sCol)
	{
		// TODO - figure out why this is necessary - a declare class variable shouldnt use this magic method
		if($sCol == 'aData')
    {
      Log::error('Model->__get($aData)');
			return $this->aData;
    }

		try
		{
			if(!isset($this->aData[$this->iCursor]))
				return null;

			if($sCol == 'all')
				return $this->aData[$this->iCursor];
				$vRet = $this->aData[$this->iCursor][$sCol];

				return $vRet;
		}
		catch(Exception $oE)
		{
		}

		// if youre here something went wrong

		Log::error('Model::__get() on an invalid column: '.$sCol);
	}

	public function __set($sCol, $vVal)
	{
		$this->aData[$this->iCursor][$sCol] = $vVal;
	}

	public function fetch($vLookup, $vExpected = 1)
	{
		$this->clear();
		if(is_null($vLookup))
    {
      // not sure if this should ever happen
      Log::error('null lookup in Model->fetch()');
			return;
    }

		$aLookup = array();

		$this->vExpected = $vExpected;

		if(is_array($vLookup))
			$aLookup = $vLookup;
		elseif(Util::is_int($vLookup)) // assume its an id
		{
 			$aLookup[$this->sFqTable.'.'.'id'] = $vLookup;
 		}
 		elseif(is_string($vLookup))
 				$sWhere = $vLookup;
 		else
 			Log::error('Unknown variable type for $vLookup in Model->fetch();');


		if(is_null($this->sGetSqlStart))
			$this->sGetSqlStart = "SELECT * FROM ".$this->sFqTable;

 		if(isset($sWhere))
 		{
 			//expose($sWhere);
 			$sSql = $this->sGetSqlStart." WHERE ".$sWhere;
 		}
 		else
 			$sSql = $this->sGetSqlStart." WHERE ".Db::build_where($aLookup);

		$this->aData = $this->oDb->select_rows_and_count($sSql);

    	$this->sLastSql = $sSql;
		$this->iCount = $this->aData['count'];
		if($this->iCount)
			$this->sMode = 'update';
		$this->check_count();

		unset($this->aData['count']);
		$this->iCursor = 0;
	}

	public function enable_auditing()
	{
		$this->bAudit = true;
		if(Request::$bDebugMode)
		{
			if(!defined('TYPE_AUDIT_'.strtoupper($this->sSchema.'_'.$this->sTable)))
			{
				//line();
				Log::error('Model->enable_auditing() without a constant for : TYPE_AUDIT_'.strtoupper($this->sSchema.'_'.$this->sTable));
				//line();
				stop();
			}
		}
	}

	public function disable_auditing()
	{
		$this->bAudit = false;
	}

	public function save($bClear = false)
	{
		$aData = $this->aData[$this->iCursor];
		//foreach($this->aSubModels as $sSubTable => $aSubData)
		{
			//if($sSubTable == $this->sTable)
			{
				//pr('hit if line '.__line__);
				$aColumns = array();
				$aValues = array();
				foreach($aData as $sCol => $vVal)
				{
					if(!in_array($sCol, $this->aMetaData))
					{
						$aColumns[] = $sCol;
						if(is_numeric($vVal))
								$aValues[] = $vVal;
						else
								$aValues[] = Db::esc($vVal);
					}
				}

				if($this->sMode == 'update')
				{
					$sSql = "
					UPDATE
						".$this->sFqTable."
					SET
						";
					foreach($aColumns as $iKey => $sCol)
					{
						$sSql.= $sCol." = ".$aValues[$iKey].", ";
					}
					$sSql = rtrim($sSql, ", ");
					$sSql.= " WHERE id = ".(int)$aData['id'];

					$this->oDb->update($sSql);

				}
				else // insert
				{
						$sSql = "
						INSERT INTO
							".$this->sFqTable."
						(
							".implode(',', $aColumns)."
						)
						VALUES
						(
						 ".implode(',', $aValues)."
						)";

						$aData['id'] = $this->oDb->insert($sSql);
				}

				//TODO -currently this is only an option for barmend schema
				if($this->bAudit)
				{
					$iXrefTypeId = constant('TYPE_AUDIT_'.strtoupper($this->sSchema.'_'.$this->sTable));
					$oModel = Model::init('barmend', 'audit', $this->oDb);
					$oModel->fetch('xref_id = '.(int)$aData['id'].' AND xref_type_id = '.(int)$iXrefTypeId, '0 or 1');
					if($oModel->is_insert())
					{
						$oModel->xref_id = $aData['id'];
						$oModel->xref_type_id = $iXrefTypeId;
						$oModel->create_ts = Db::datetime('now', false);
						$oModel->create_by = key(Session::get('login||member_data')); // add option for scripts
					}
					else
					{
						$oModel->update_ts = Db::datetime('now', false);
						$oModel->update_by = key(Session::get('login||member_data')); // add option for scripts
					}

					$oModel->save();
				}
			}
		}

		// save the data back to $this
		$this->aData[$this->iCursor] = $aData;
		$iRet = $this->aData[$this->iCursor]['id'];

		if($bClear)
			$this->clear();
		return $iRet;
	}

	public function delete($bClear = true)
	{
		if(isset($this->aData[$this->iCursor]['id']))
		{
			$sSql = "DELETE FROM ".$this->sFqTable." WHERE ID = ".(int)$this->aData[$this->iCursor]['id'];
			$this->oDb->delete($sSql);
		}
		if($bClear)
			$this->clear();
	}

	public function delete_all()
	{
		foreach($this as $iCursor => $vValue)
		{
			$this->iCursor = $iCursor;
			$this->delete(false);
		}
		$this->clear();
	}

	public function get_count()
	{
		return $this->iCount;
	}

	private function check_count()
	{
		switch($this->vExpected)
		{
			case '0 or 1':
				if($this->iCount > 1)
					$this->bCountMismatch = true;
				break;
			case 'any': // skip
				break;
			default:
				if($this->iCount != $this->vExpected)
					$this->bCountMismatch = true;
		}

		if($this->bCountMismatch)
			Log::error('count mismatch for lookup exected: '.$this->vExpected.' found: '.$this->iCount.' in '.$this->sFqTable);
	}

	protected function InitSubModel($sSchema, $sTable, $vLookup = null)
	{
		if(!isset($this->aSubModels[$this->iCursor][$sSchema.'.'.$sTable]))
		{
			//pr('create new model');
			$this->aSubModels[$this->iCursor][$sSchema.'.'.$sTable] =  Model::init($sSchema, $sTable, $this->oDb);
		}

		if(isset($vLookup))
			$this->aSubModels[$this->iCursor][$sSchema.'.'.$sTable]->fetch($vLookup);
	}

  public static function __callStatic($sFunction, $aArgs)
  {
    return self::call_function(true, $sFunction, $aArgs);
  }

  public function __call($sFunction, $aArgs)
  {
    return $this->call_function(false, $sFunction, $aArgs);
  }

  private function call_function($bStatic, $sFunction, $aArgs)
  {
  	$sChildModel = get_called_class();
  	$sFunction = '_'.$sFunction;
  	if(Request::$bDebugMode)
  	{
  		if(!method_exists($sChildModel, $sFunction))
  				Log::error('call to a unknown model method: '.$sChildModel.'::'.$sFunction);
  	}

  	if($bStatic)
  	{
  		return $sChildModel::$sFunction($aArgs);
  	}
  	else
  	{
  		if(method_exists($this, 'get_method_defaults'))
  		{
  			$aArgs = array_merge($this->get_method_defaults($sFunction), $aArgs);
  		}
  		return $this->$sFunction($aArgs);
  	}
  }

  public function is_update()
  {
  	return $this->sMode == 'update';
  }

  public function is_insert()
  {
  	return $this->sMode == 'insert';
  }

  public function force_update()
  {
    $this->sMode = 'update';
  }


  public function get_last_sql()
  {
    return $this->sLastSql;
  }

}
