<?php
/**
 * test_isohunt.php
 *
 * isohunt.com images engine test case
 *
 * @package Test
 * @author Andreas Götz <cpuidle@gmx.de>
 * @version $Id: test_isohunt.php,v 1.2 2013/02/02 11:38:59 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/engines.php';

class TestIsoHunt extends UnitTestCase
{
    function TestIsoHunt()
    {
        parent::__construct();
    }
    
    function testSearch()
    {
        $id = 'Grey\'s Anatomy';
        
        $data = engineSearch($id, 'isohunt');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) >= 1);
        
#       dump($data);
    }
}

?>
