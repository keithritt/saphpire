<?

Log::write('init_site');
/*
$options = getopt('s:t:j:');

if(!isset($options['s']) || !isset($options['t']))
	help('-s and -t are required options');

$domain = $options['s'];
$kohana_name = strtr($domain, array(' ' => '_', '.' => '_'));

if(!is_dir($options['t']) || !is_writable($options['t']))
	help('The application folder ' . $options['t'] . ' does not exist or is not writable');

$config_json = null;

if(isset($options['j']) && ($config_json = $options['j']) && is_readable($config_json))
{
	echo "Found configuration JSON file {$config_json}\n";
	$config_json = file_get_contents($config_json);
	$config_json = json_decode($config_json);
}

$target_folder = rtrim($options['t'], '/');

$propose = array(
	'controller_dir' => "{$target_folder}/controllers/{$kohana_name}",
	'view_dir' => "{$target_folder}/views/{$kohana_name}",
	'libraries_dir' => "{$target_folder}/libraries/{$kohana_name}",
);

$propose['main_controller'] = "{$propose['controller_dir']}/{$kohana_name}_controller.php";
$propose['main_view'] = "{$propose['view_dir']}/homepage_v.php";
$propose['additional_files'] = array(
	"{$propose['controller_dir']}" => array(
		"homepage.php",
		"util.php",
	),
	"{$propose['libraries_dir']}" => array(
		'custom_definitions.php',
		'custom_functions.php',	
	),
);

$propose_trans = array(
	'controller_dir' => 'Creating a controller directory: ',
	'view_dir' => 'Creating a view directory: ',
	'main_controller' => 'Create a primary controller: ',
	'main_view' => 'Create a homepage view: ',	
	'libraries_dir' => 'Creating a libraries directory: ',
);

echo 'Proposed changes: ' . PHP_EOL;

foreach($propose as $key => $value)
{
	if(is_array($value))
	{
		echo "------------------\n";
		echo "Additional files: \n";
		echo "------------------\n";

		foreach($value as $additional_dir => $addition_files)
		{
			echo "Under directory {$additional_dir}/\n";

			foreach($addition_files as $addition_file)
				echo "\t - {$addition_file}\n";
		}
	}
	else
	{
		echo $propose_trans[$key] . PHP_EOL . "\t" . $value . PHP_EOL;
	}
}

$bars_sql = null;

if(!is_null($config_json))
{
	msg("--------------------------");
	msg("Proposed Database Changes ");
	msg("--------------------------");
	msg(" *** Note that this script has no ability to modify the database - proposed queries should be run manually in the meantime *** ");
	msg(" * Using database {$config_json->database}");
	msg("");

	if(isset($config_json->bars))
	{
		$phone = strtr($config_json->bars->phone, array(
			'(' => '',
			')' => '',
			' ' => '-'
		));

		$bars_sql = <<<SQL
INSERT INTO `bars` (`name`, `address`, `city`, `zip`, `state`, `phone`)
VALUES("{$config_json->bars->name}", "{$config_json->bars->address}", "{$config_json->bars->city}", {$config_json->bars->zip}, "{$config_json->bars->state}", "{$phone}");
SQL;

		msg(" * Inserting row into `bars` table");
		msg("\t {$bars_sql}");
	}
}

echo "\n\n Do you accept these changes? [y/N]: ";

$h = fopen('php://stdin', 'r');
$confirm = trim(strtolower(fgets($h)));

if($confirm !== 'y')
{
	die("Aborting!\n");
}

$revert = array();

try
{
	// Create the controller folder
	mkdir($propose['controller_dir']);

	#if(is_dir($propose['controller_dir']))
	#	$revert[] = "rm -rf {$propose['controller_dir']}";

	mkdir($propose['view_dir']);

	mkdir($propose['libraries_dir']);

	#if(is_dir($propose['view_dir']))
	#	$revert[] = "rm -rf {$propose['view_dir']}";

	define('BASE_DIR', dirname(__FILE__));

	copy(BASE_DIR . '/skeleton/controllers/sitedomain_com_controller.php', $propose['main_controller']);
	copy(BASE_DIR . '/skeleton/views/homepage_v.php', $propose['main_view']);

	foreach($propose['additional_files'] as $dir => $files)
	{
		foreach($files as $file)
		{
			$dest_file = "{$dir}/{$file}";

			$source_file = BASE_DIR . '/skeleton/controllers/' . $file;

			if(strpos($dest_file, 'libraries') !== false)
				$source_file = BASE_DIR . '/skeleton/libraries/' . $file;

			copy($source_file, $dest_file);

			// Find/replace
			$f = file_get_contents($dest_file);
			$f = strtr($f, array(
				'Sitedomain_com_controller' => ucwords($kohana_name . '_controller'),
			));

			$h = fopen($dest_file, 'w');
			fwrite($h, $f);
			fclose($h);
		}
	}

	// Find/replace
	$f = file_get_contents($propose['main_controller']);
	$f = strtr($f, array(
		'Sitedomain_com_controller' => ucwords($kohana_name . '_controller'),
	));

	$h = fopen($propose['main_controller'], 'w');
	fwrite($h, $f);
	fclose($h);

	echo "All done! \n";
}
catch(Exception $e)
{
	echo "Failed creating project with exception: " . $e->getMessage();

	echo "Revert plan: \n";
	print_r($revert);
	die("Reverting automatically is not yet supported - use the above plan to clean up.");

	foreach($revert as $undo)
	{
		exec($undo);
	}
}

function help($msg = '')
{
	if(strlen($msg))
		echo PHP_EOL . '!!!! ' . $msg . PHP_EOL . PHP_EOL;

	echo <<<TEXT
Creates a new site based off of the skeleton site

USAGE
-----
   php mksite.php -s <domain name> -t <target folder> [-j <config json>]

OPTIONS
-------
    -s <domain name> The domain that this site will use - e.g. somedomain.com

    -t <target folder> The application folder of the project

	[-j <config json>] Option config JSON to specify database alterations

TEXT;

	exit(0);
}

function msg()
{
	$args = func_get_args();
	foreach($args as $message)
		echo "$message\n";
}
*/