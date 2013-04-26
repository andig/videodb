<?php
/**
 * test_amazon.php
 *
 * amazon.de engine test case
 *
 * @package Test
 * @author Andreas Götz <cpuidle@gmx.de>
 * @version $Id: test_amazonaws.php,v 1.4 2013/02/02 11:38:59 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/engines.php';

class TestAmazonAWS extends UnitTestCase
{
	function TestAmazonAWS()
	{
		parent::__construct();
	}

    function testData()
    {
        // Star Wars: Episode 1
        // http://www.amazon.de/Star-Wars-Episode-Bedrohung-Einzel-DVD/dp/B0009HBEHW/ref=sr_1_2/303-6664842-9566627?ie=UTF8&s=dvd&qid=1185389090&sr=1-2
        $id = 'B0009HBEHW';
        
        $data = engineGetData($id, 'amazonaws');
        #$this->assertNoErrors();

        $this->assertTrue(sizeof($data) > 0);
        
#        dump($data);

        $this->assertPattern('/Star Wars/', $data[title]);
#        $this->assertEqual($data[subtitle], 'Die dunkle Bedrohung (Einzel-DVD)');
        $this->assertPattern('#http://.+.images\-amazon.com/images/#', $data[coverurl]);
        $this->assertEqual($data[director], 'George Lucas');
        $this->assertEqual($data[language], 'deutsch, englisch');
        $this->assertEqual($data[year], 2001);
        $this->assertTrue($data[runtime] > 100);
        $this->assertTrue($data[rating] >= 6);
        #[genres] => 
        $this->assertPattern('/Ewan McGregor/', $data[cast]);
        $this->assertPattern('/Naboo/', $data[plot]);
    }

    function testSearch()
    {
        $id = 'Star Wars: Episode 1';
        
        $data = engineSearch($id, 'amazonaws', 'DVD', 'com');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);
        
#        dump($data);
    }
}

?>
