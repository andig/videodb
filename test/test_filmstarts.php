<?php
/**
 * test_allocine.php
 *
 * Allocine engine test case
 *
 * @package Test
 * @author Loïc Devaux <devloic@gmail.com>
 * @author tedemo  <tedemo@free.fr>
 * @author Andreas Götz <cpuidle@gmx.de>
 * @version $Id: test_allocine.php,v 1.16 2013/02/02 11:38:59 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/engines.php';

class TestFilmstarts extends UnitTestCase
{
	function TestFilmstarts()
	{
		parent::__construct();
	}

	function testMovie()
	{
		// Ich - Einfach unverbesserlich 2
		// http://www.filmstarts.de/kritiken/190299.html
		$id = '190299';

		$data = engineGetData($id, 'filmstarts');
		#$this->assertNoErrors();
		$this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';
		#dump($data);
        #echo '</pre>';

		$this->assertEqual($data[id], 'filmstarts:190299');
        $this->assertEqual($data[title], 'Ich - Einfach unverbesserlich 2');
		$this->assertEqual($data[subtitle], 'Despicable Me 2');
		$this->assertEqual($data[year], 2013);
		$this->assertEqual($data[coverurl], "http://de.web.img1.acsta.net/pictures/210/108/21010873_2013060709304631.jpg");
		$this->assertEqual($data[runtime], 98);
		$this->assertEqual($data[director], 'Chris Renaud, Pierre Coffin');
		$this->assertTrue($data[rating] >= 0 && $data[rating] <= 5);
		$this->assertEqual($data[country], 'USA');
		$this->assertEqual($data[language], 'Englisch');
        sort ($data[genres]);
		$this->assertEqual(join(',', $data[genres]), '3,4,8');
		$this->assertPattern('/Steve Carell::Gru::filmstarts:93036/si', $data[cast]);
        $this->assertPattern('/Kristen Wiig::Lucy::filmstarts:178003/si', $data[cast]);

        $this->assertPattern('/Der Superschurke Gru \(Oliver Rohrbeck\) hat inzwischen/si', $data[plot]);
        

  
	}

	function testMovie2()
	{
		// Taffe M�dels 
		// http://www.filmstarts.de/kritiken/207813.html
		$id = "207813";

		$data = engineGetData($id, 'filmstarts');
		#$this->assertNoErrors();
		$this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

		$this->assertEqual($data[id], 'filmstarts:207813');
        $this->assertEqual($data[title], 'Taffe Mädels');
		$this->assertEqual($data[subtitle], 'The Heat');
		$this->assertEqual($data[year], 2013);
		$this->assertEqual($data[coverurl], "http://de.web.img3.acsta.net/pictures/210/092/21009226_20130529153128129.jpg");
		$this->assertEqual($data[runtime], 117);
		$this->assertEqual($data[director], 'Paul Feig');
		$this->assertTrue($data[rating] >= 0 && $data[rating] <= 5);
		$this->assertEqual($data[country], 'USA');
		$this->assertEqual($data[language], 'Englisch');
        sort ($data[genres]);
		$this->assertEqual(join(',', $data[genres]), '1,4,5');
		$this->assertPattern('/Sandra Bullock::Special Agent Sarah Ashburn::filmstarts:4700/si', $data[cast]);

        $this->assertPattern('/Die ernste, aber tollpatschige FBI-Agentin Sarah Ashburn/si', $data[plot]);
      

   
	}

    // check search
    function testSearch()
    {
        // Clerks 2
        $data = filmstartsSearch('Clerks 2');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

        $data = $data[0];

        $this->assertEqual($data[id], 'filmstarts:57999');
        $this->assertEqual($data[title], 'Clerks II');
    }

    // check for utf8 search
    function testSearch2()
    {
        // Cette femme là
        $data = filmstartsSearch('cette femme là');
       
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);

        $data = $data[0];

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

        $this->assertEqual($data[id], 'filmstarts:51397');
        $this->assertEqual($data[title], 'Im Schatten der Wälder');
    }

    // check for partial search
    function testSearch3()
    {
        // Chacun cherche son chat
        $data = filmstartsSearch('chacun cherche son');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);

        $data = $data[0];

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

        $this->assertEqual($data[id], 'filmstarts:14363');
        $this->assertEqual($data[title], '... und jeder sucht sein Kätzchen');
    }
}
?>
