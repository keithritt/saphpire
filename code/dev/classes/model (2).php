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
		//$aColMap = array(),
		//$aSubCols
		//$aIgnoreCols = array(),
		$aSubModels = array(), // if the model uses other models to save data - try to avoid infinite loops
		$aMetaData = array() // if we save any data that isnt direct stored in a column in $sTable
		;


    //public function __construct() {
    //    $this->position = 0;
   // }

    function rewind() {
        //var_dump(__METHOD__);
        $this->iCursor = 0;
    }

    function current() {
        //var_dump(__METHOD__);
        return $this->aData[$this->iCursor];
    }

    function key() {
        //var_dump(__METHOD__);
        return $this->iCursor;
    }

    function next() {
        //var_dump(__METHOD__);
        $this->iCursor++;
    }

    function valid() {
        //var_dump(__METHOD__);
        return isset($this->aData[$this->iCursor]);
    }

   public function __isset($sLookup)
   {
   		//pr('Model->__isset('.$sLookup.')');
   		return isset($this->aData[$this->iCursor][$sLookup]);
   }



	public static function init($sSchema, $sTable, $oDb)
	{
		//pr('Model::init('.$sSchema.', '.$sTable.')');

    //expose($oDb->$sSchema);

		switch($sSchema.'.'.$sTable)
		{
			case 'barmend.bars':
			case 'barmend.people':
			case 'barmend.members':
			case 'barmend.menus':
			case 'master.domains':
				require_once(CODE_PATH.'/models/'.$sSchema.'/'.$sTable.'_model.php');
				$sClass = ucfirst($sSchema).ucfirst($sTable).'Model';
				//expose($sClass);
				return new $sClass($oDb);
				break;
			default: // assume it is just a table name
				return new Model($sSchema, $sTable, $oDb);
		}
	}

	public function __construct($sSchema, $sTable, $oDb)
	{
		//pr('parent->__construct('.$sSchema.', '.$sTable.')');
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



		//pr('__get('.$sCol.')');
		//expose_backtrace();
	  //expose($this->aData);
		//expose($this->iCursor);
		//die();
		//$sCol = strtolower($sCol);
		try
		{
			//line();
			//expose($this->aData);
			//line();
			//expose($this->iCursor);
			if(!isset($this->aData[$this->iCursor]))
				return null;

			if($sCol == 'all')
				return $this->aData[$this->iCursor];
			//if(array_key_exists($sCol, $this->aData[$this->iCursor]))
				$vRet = $this->aData[$this->iCursor][$sCol];
				//expose($vRet);
				//line();
				return $vRet;
		}
		catch(Exception $oE)
		{
			//line();
			//expose($oE->getMessage());
			//die();
		}

		// if youre here something went wrong

		//expose_backtrace();
		//die();
		Log::error('Model::__get() on an invalid column: '.$sCol);
	}

	public function __set($sCol, $vVal)
	{
		//pr('__set('.$sCol.')');
		//expose($vVal);
		//$this->aData[$this->iCursor][$this->sTable][$sCol] = $vVal;
		$this->aData[$this->iCursor][$sCol] = $vVal;
	}

	public function fetch($vLookup, $vExpected = 1)
	{
		//pr($this->sSchema.'.'.$this->sTable.'->fetch()');
		//expose($vLookup);
		//expose($vExpected);

		$this->clear();

    //line();

		if(is_null($vLookup))
    {
      // not sure if this should ever happen
      Log::error('null lookup in Model->fetch()');
			return;
    }

    //line();



		$aLookup = array();

		$this->vExpected = $vExpected;

    //line();

		if(is_array($vLookup))
			$aLookup = $vLookup;
		elseif(Util::is_int($vLookup)) // assume its an id
		{
			//pr('lookup is an id');
 			$aLookup[$this->sFqTable.'.'.'id'] = $vLookup;
 		}
 		elseif(is_string($vLookup))
 				$sWhere = $vLookup;
 		else
 			Log::error('Unknown variable type for $vLookup in Model->fetch();');

    //line();

 		//expose($this->sGetSqlStart);
 		///expose($this->sFqTable);

		if(is_null($this->sGetSqlStart))
			$this->sGetSqlStart = "SELECT * FROM ".$this->sFqTable;

 		if(isset($sWhere))
 		{
 			//expose($sWhere);
 			$sSql = $this->sGetSqlStart." WHERE ".$sWhere;
 		}
 		else
 			$sSql = $this->sGetSqlStart." WHERE ".Db::build_where($aLookup);


 		//expose($sSql);
 		//die();

 		//line();
		$this->aData = $this->oDb->select_rows_and_count($sSql);
    //expose($this->aData);

		//line();
    	$this->sLastSql = $sSql;
		//expose($aTmp);
		$this->iCount = $this->aData['count'];
		if($this->iCount)
			$this->sMode = 'update';
		$this->check_count();
		//expose($this->aData);

		unset($this->aData['count']);
		$this->iCursor = 0;

		//@TODO - figure out how to do mass get()s
		//if($vExpected == 1)
		//if(isset($this->aData[0]))
	//		$this->aData = $this->aData[0];
		//expose($this->aData);
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
		//pr($this->sTable.'::save()');
		//expose($this->aData);
		//expose($this->aSubModels);
		//die();
		$aData = $this->aData[$this->iCursor];
		//expose($aData);
		//die();
		//expose($this-)
		//foreach($this->aSubModels as $sSubTable => $aSubData)
		{
			//pr('$sSubTable = '.$sSubTable);
			//if($sSubTable == $this->sTable)
			{
				//pr('hit if line '.__line__);
				$aColumns = array();
				$aValues = array();
				//expose($this->aMetaData);
				foreach($aData as $sCol => $vVal)
				{
					//pr($sCol);
					if(!in_array($sCol, $this->aMetaData))
					{
						$aColumns[] = $sCol;
						if(is_numeric($vVal))
								$aValues[] = $vVal;
						else
								$aValues[] = Db::esc($vVal);
					}
				}

				//if(isset($aData['id'])) // update
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
						//expose($sSql);

					$this->oDb->update($sSql);
					//expose($sSql);
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


							;
						//pr($this->sFqTable);
						//if(in_array($this->sFqTable, array('oloop_barmend_dev.members', 'oloop_barmend_dev.people')))
						//	expose($sSql);
						$aData['id'] = $this->oDb->insert($sSql);
						//expose($aData);
				}

				//TODO -currently this is only an option for barmend schema
				if($this->bAudit)
				{
					//$oThis = &get_instance(); //@TODO - fix this asap
					//pr('audit this model save');
					//$oModel = Model::init('types')
					$iXrefTypeId = constant('TYPE_AUDIT_'.strtoupper($this->sSchema.'_'.$this->sTable));
					$oModel = Model::init('barmend', 'audit', $this->oDb);
					$oModel->fetch('xref_id = '.(int)$aData['id'].' AND xref_type_id = '.(int)$iXrefTypeId, '0 or 1');
					//expose($oModel);
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



					//expose();

					$oModel->save();
					//stop();
					//expose();
				}
			}
		}


		//pr('end of save');
		//die();

		//expose($aData);

		// save the data back to $this
		$this->aData[$this->iCursor] = $aData;

		//expose($this->iCursor);

		$iRet = $this->aData[$this->iCursor]['id'];

		if($bClear)
			$this->clear();

		//expose($this->aData);

		return $iRet;

		//pr('end '.$this->sTable.'::save()');
	}

	public function delete($bClear = true)
	{
		//pr('Model->delete('.$this->sFqTable.')');
		//$aData = ;
		if(isset($this->aData[$this->iCursor]['id']))
		{
			//line();
			$sSql = "DELETE FROM ".$this->sFqTable." WHERE ID = ".(int)$this->aData[$this->iCursor]['id'];
			//expose($sSql);
			$this->oDb->delete($sSql);
		}
		//else
		//	line();
		if($bClear)
			$this->clear();

		//pr('end delete()');
	}

	public function delete_all()
	{
		//pr('delete_all()');
		//expose($this->aData);
		foreach($this as $iCursor => $vValue)
		{
			//pr('$iCursor = '.$iCursor);
			$this->iCursor = $iCursor;
			$this->delete(false);
	    //expose($iCursor);
	    //expose($vValue);
	    //echo "\n";
		}
		//die();
		$this->clear();

		//pr('delete_all()');
	}

	public function get_count()
	{
		return $this->iCount;
	}

	private function check_count()
	{
		//pr('check_count()');
		//expose($iCount);
		//expose($vExpected);
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
		//pr('InitSubModel('.$sSchema.','.$sTable.')');
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
  	//pr('__callStatic()');
    return self::call_function(true, $sFunction, $aArgs);
  }

  public function __call($sFunction, $aArgs)
  {
  	//pr('__call()');
    return $this->call_function(false, $sFunction, $aArgs);
  }

  private function call_function($bStatic, $sFunction, $aArgs)
  {
  	//pr('call_function('.$sFunction.')');
  	$sChildModel = get_called_class();
  	//expose($bStatic);
  	$sFunction = '_'.$sFunction;
  	if(Request::$bDebugMode)
  	{
  		//expose();
  		if(!method_exists($sChildModel, $sFunction))
  				Log::error('call to a unknown model method: '.$sChildModel.'::'.$sFunction);
  	}

  	if($bStatic)
  	{
  		//line();
  		return $sChildModel::$sFunction($aArgs);
  	}
  	else
  	{
  		//line();
  		if(method_exists($this, 'get_method_defaults'))
  		{
  			//line();
  			//expose($aArgs);
  			$aArgs = array_merge($this->get_method_defaults($sFunction), $aArgs);
  		}
  		//else
  		//	line();

  		//expose($aArgs);
  		//die();

  		//line();
  		//expose($this);
  		//expose($sFunction);
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