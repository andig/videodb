<?php

// move out of contrib for includes
chdir('..');

require_once './core/functions.php';

if (version_compare(phpversion(), '5.3') >= 0)
    error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
else
    error_reporting(E_ALL ^ E_NOTICE);

localnet_or_die();
permission_or_die(PERM_ADMIN);

if (!defined('SIMPLE_TEST')) define('SIMPLE_TEST', './test/simpletest/');

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');

function findTestCases($dir, $pattern=null)
{
    $res = array();
    
    if ($dh = @opendir($dir))
    {
        while (($file = readdir($dh)) !== false)
        {
            if (preg_match("/^test_(.+)\.php$/", $file, $matches))
            {
                if ($pattern && (stristr($file, $pattern) == false)) continue;

                $res[$matches[1]] = $dir.'/'.$file;
                // get meta data
#                require_once($dir.'/'.$file);
/*
                $func = $engine.'Meta';

                if (function_exists($func))
                {
                    $engines[$engine] = $func();
                    
                    // required php version present?
                    if ($engines[$engine]['php'] && (version_compare(phpversion(), $engines[$engine]['php']) < 0))
                    {
                        unset($engines[$engine]);
                    }    
                }    
*/
            }
        }
        closedir($dh);
    }
    
    return $res;
}

$res = findTestCases('./test', $_REQUEST['test']);

echo "Starting tests.<br/>";

foreach ($res as $case => $file)
{
	if (isset($run_engines) ){
		if (in_array($case, $run_engines)){
			$test = new TestSuite($case);
			$test->addFile($file);
			$test->run(new HtmlReporter('utf-8'));
		}
		
	}else{
		$test = new TestSuite($case);
		$test->addFile($file);
		$test->run(new HtmlReporter('utf-8'));
	}
	
	
}

echo "<br/>All tests completed.<br/>";

?>
