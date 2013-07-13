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

class TestSensacine extends UnitTestCase
{
	function TestSensacine()
	{
		parent::__construct();
	}

	function testMovie()
	{
		// Star Wars: Episodio I
		// http://www.sensacine.com/peliculas/pelicula-20754/
		$id = '20754';

		$data = engineGetData($id, 'sensacine');
		#$this->assertNoErrors();
		$this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';
		#dump($data);
        #echo '</pre>';

		$this->assertEqual($data[id], 'sensacine:20754');
        $this->assertEqual($data[title], 'Star Wars: Episodio I - La amenaza fantasma');
		$this->assertEqual($data[subtitle], 'Star Wars: Episode I - The Phantom Menace');
		$this->assertEqual($data[year], 1999);
		$this->assertEqual($data[coverurl], "http://es.web.img1.acsta.net/medias/nmedia/18/86/33/09/19835610.jpg");
		$this->assertEqual($data[runtime], 133);
		$this->assertEqual($data[director], 'George Lucas');
		$this->assertTrue($data[rating] >= 0 && $data[rating] <= 5);
		$this->assertEqual($data[country], 'EE.UU.');
		$this->assertEqual($data[language], 'Inglés');
        sort ($data[genres]);
		$this->assertEqual(join(',', $data[genres]), '2,9,15');
		$this->assertPattern('/Ewan McGregor::Obi-Wan Kenobi::sensacine:17043/si', $data[cast]);
        $this->assertPattern('/Friday \'Liz\' Wilson::Eirtaé::sensacine:407183/si', $data[cast]);

        $this->assertPattern('/Hace mucho tiempo, en una galaxia muy, muy lejana/si', $data[plot]);
        
	}

	function testMovie2()
	{
		// Gru 2. Mi villano favorito
		// http://www.sensacine.com/peliculas/pelicula-190299/
		$id = "190299";

		$data = engineGetData($id, 'sensacine');
		#$this->assertNoErrors();
		$this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

		$this->assertEqual($data[id], 'sensacine:190299');
        $this->assertEqual($data[title], 'Gru 2. Mi villano favorito');
		$this->assertEqual($data[subtitle], 'Despicable Me 2');
		$this->assertEqual($data[year], 2013);
		$this->assertEqual($data[coverurl], "http://es.web.img2.acsta.net/pictures/210/090/21009000_2013052817374835.jpg");
		$this->assertEqual($data[runtime], 98);
		$this->assertEqual($data[director], 'Chris Renaud, Pierre Coffin');
		$this->assertTrue($data[rating] >= 0 && $data[rating] <= 5);
		$this->assertEqual($data[country], 'EE.UU.');
		$this->assertEqual($data[language], 'Inglés');
        sort ($data[genres]);
		$this->assertEqual(join(',', $data[genres]), '3,4,8');
		$this->assertPattern('/Steve Carell::Gru::sensacine:93036/si', $data[cast]);
        $this->assertPattern('/Kristen Wiig::Lucy::sensacine:178003/si', $data[cast]);

        $this->assertPattern('/Los directores Pierre Coffin y Chris Renaud vuelven a trabaja/si', $data[plot]);
        
	}

    // check search
    function testSearch()
    {
        // Clerks 2
        $data = sensacineSearch('Clerks 2');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

        $data = $data[0];

        $this->assertEqual($data[id], 'sensacine:57999');
        $this->assertEqual($data[title], 'Clerks II');
    }

    // check for utf8 search
    function testSearch2()
    {
        // Cette femme là
        $data = sensacineSearch('mujeres al borde de');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);

        $data = $data[0];

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

        $this->assertEqual($data[id], 'sensacine:86046');
        $this->assertEqual($data[title], 'Mujeres al borde de un ataque de nervios');
    }

    // check for partial search
    function testSearch3()
    {
        // Chacun cherche son chat
        $data = sensacineSearch('chacun cherche son');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);

        $data = $data[0];

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

        $this->assertEqual($data[id], 'sensacine:14363');
        $this->assertEqual($data[title], 'Cada uno busca su gato');
    }
}
?>
