<?


//print '<br>log rotator';

//expose($sDbEnv);

$aLogs = array('scheduler');



$sPrevDay = strtolower(date('D', strtotime('yesterday')));

try
{



	foreach($aLogs as $sLog)
	{
		// check to see if the default file exists
		$sOldFile = PUBLIC_HTML_PATH.'/'.ENVIRONMENT.'/logs/'.$sLog.'.log';
		$sNewFile = PUBLIC_HTML_PATH.'/'.ENVIRONMENT.'/logs/'.$sLog.'_'.$sPrevDay.'.log';

		if(file_exists($sNewFile)) // delete the file from a week ago
		{
			unlink($sNewFile);
		}

		if(file_exists($sOldFile))
		{
		  //pr('file exists');
		 
		  rename($sOldFile, $sNewFile);
		}
		//else
		//  pr('file does not exist');
	}
}
catch(Exception $oE)
{
	//@TODO-og exception
	Log::error($oE->getMessage());
}

// new file will be automatically created by the next iteration of the cron