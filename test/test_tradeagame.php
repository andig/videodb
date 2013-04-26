<?php
/**
 * test_tradeagame.php
 *
 * trade-a-game.de engine test case
 *
 * @package Test
 * @author Andreas Götz <cpuidle@gmx.de>
 * @version $Id: test_tradeagame.php,v 1.2 2013/02/02 11:38:59 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/engines.php';

class TestTradeAGame extends UnitTestCase
{
    function TestTradeAGame()
	{
		parent::__construct();
	}
    
    function testSearch()
    {
        $id = 'Scrubs';
        
        $data = engineSearch($id, 'tradeagame');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) >= 1);
        
#       dump($data);
    }
    
    function testSearch2()
    {
        $id = 'Terminator';
        
        $data = engineSearch($id, 'tradeagame');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) >= 1);
        
#       dump($data);
    }
}

?>
