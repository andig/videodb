<?php
/**
 * test_mininova.php
 *
 * mininova.org images engine test case
 *
 * @package Test
 * @author Andreas Götz <cpuidle@gmx.de>
 * @version $Id: test_mininova.php,v 1.3 2013/03/14 17:17:28 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/engines.php';

class TestMininova extends UnitTestCase
{
    function TestMininova()
	{
		parent::__construct();
	}
    
    function testSearch()
    {
        $id = 'Grey\'s Anatomy';
        
        $data = engineSearch($id, 'mininova');
#       dump($data);
        $this->assertTrue(sizeof($data) >= 1);
    }
}

?>
