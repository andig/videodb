<?php
/**
 * test_allocine.php
 *
 * Allocine engine test case
 *
 * @package Test
 * @author tedemo  <tedemo@free.fr>
 * @author Andreas Götz <cpuidle@gmx.de>
 * @version $Id: test_allocine.php,v 1.16 2013/02/02 11:38:59 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/engines.php';

class TestAllocine extends UnitTestCase
{
	function TestAllocine()
	{
		parent::__construct();
	}

	function testMovie()
	{
		// Star Wars: Episode I
		// http://www.allocine.fr/film/fichefilm_gen_cfilm=20754.html
		$id = '20754';

		$data = engineGetData($id, 'allocine');
		#$this->assertNoErrors();
		$this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';
		#dump($data);
        #echo '</pre>';

		$this->assertEqual($data[id], 'allocine:20754');
        $this->assertEqual($data[title], 'Star Wars : Episode I');
		$this->assertEqual($data[subtitle], 'La Menace fantôme 3D');
		$this->assertEqual($data[year], 1999);
		$this->assertEqual($data[coverurl], "http://images.allocine.fr/r_160_214/b_1_cfd7e1/medias/04/44/60/044460_af_vo.jpg");
		$this->assertEqual($data[runtime], 133);
		$this->assertEqual($data[director], 'George Lucas');
		$this->assertTrue($data[rating] >= 5);
		$this->assertTrue($data[rating] <= 8);
		$this->assertEqual($data[country], 'USA');
		$this->assertEqual($data[language], 'english');
        sort ($data[genres]);
		$this->assertEqual(join(',', $data[genres]), 'Adventure,Fantasy,Sci-Fi');
		$this->assertPattern('/Ewan McGregor::Obi-Wan Kenobi::allocine:17043/si', $data[cast]);
        $this->assertPattern('/Friday \'Liz\' Wilson::Eirtaé::allocine:407183/si', $data[cast]);

        $this->assertPattern('/République/si', $data[plot]);
        $this->assertPattern('/Tourné/si', $data[comment]);

  /*
  Array ( [id] => allocine:20754 [title] => Star Wars : Episode I [subtitle] => La Menace fantôme [year] => 1999 [coverurl] => http://images.allocine.fr/r_160_214/b_1_cfd7e1/medias/04/44/60/044460_af_vo.jpg [runtime] => 133 [director] => George Lucas [rating] => 5.6 [country] => USA [plot] => Il y a bien longtemps, dans une galaxie très lointaine... La République connaît de nombreux tourments : la corruption fait vaciller ses bases, le Sénat s'embourbe dans des discussions politiques sans fin et de nombreux pouvoirs dissidents commencent ?émerger, annonçant la chute d'un système autrefois paisible. Puissante et intouchable, la Fédération du Commerce impose par la force la taxation des routes commerciales. Refusant de céder, la pacifique planète Naboo, dirigée par la jeune Reine Amidala, subit un blocus militaire de la Fédération. Dépêchés par le Sénat pour régler cette affaire, les chevaliers Jedi Qui-Gon Jinn et Obi-Wan Kenobi découvrent qu'une véritable offensive de la Fédération est imminente. Libérant la Reine et ses proches, ils quittent la planète mais doivent se poser sur Tatooine pour réparer leur vaisseau... [genres] => Array ( [0] => Sci-Fi [1] => Adventure [2] => Fantasy ) [cast] => Liam Neeson::Qui-Gon Jinn::allocine:5568 Ewan McGregor::Obi-Wan Kenobi::allocine:17043 Natalie Portman::La reine Amidala / Padmé Naberrie::allocine:18066 Jake Lloyd::Anakin Skywalker::allocine:24517 Ian McDiarmid::le Sénateur Palpatine / Dark Sidious::allocine:52971 Anthony Daniels::C-3PO::allocine:29807 Kenny Baker::R2-D2::allocine:12096 Ray Park::Dark Maul::allocine:41527 Andrew Secombe::Watto::allocine:68135 Pernilla August::Shmi Skywalker::allocine:14279 [language] => english [comment] => Box Office USA : 431 088 301 $ Box Office France : 7 294 498 entrées Budget : 115 millions de $ N° de visa : 96533 Couleur Format du son : Dolby SR + Digital SR-D + DTS & SDDS Format de projection : 2.35 : 1 Cinemascope Format de production : 35 mm Tourné en : Anglais )
  */
	}

	function testMovie2()
	{
		// Star Wars: Episode III
		// http://www.allocine.fr/film/fichefilm_gen_cfilm=40623.html
		$id = "40623";

		$data = engineGetData($id, 'allocine');
		#$this->assertNoErrors();
		$this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

		$this->assertEqual($data[id], 'allocine:40623');
        $this->assertEqual($data[title], 'Star Wars : Episode III');
		$this->assertEqual($data[subtitle], 'La Revanche des Sith');
		$this->assertEqual($data[year], 2004);
		$this->assertEqual($data[coverurl], "http://images.allocine.fr/r_160_214/b_1_cfd7e1/medias/nmedia/18/35/53/23/18423997.jpg");
		$this->assertEqual($data[runtime], 140);
		$this->assertEqual($data[director], 'George Lucas');
		$this->assertTrue($data[rating] >= 5);
		$this->assertTrue($data[rating] <= 8);
		$this->assertEqual($data[country], 'USA');
		$this->assertEqual($data[language], 'english');
        sort ($data[genres]);
		$this->assertEqual(join(',', $data[genres]), 'Action,Sci-Fi');
		$this->assertPattern('/Ewan McGregor::Obi-Wan Kenobi::allocine:17043/si', $data[cast]);

        $this->assertPattern('/revanche/si', $data[plot]);
        $this->assertPattern('/Tourné/si', $data[comment]);

    /*
      Array ( [id] => allocine:40623 [title] => Star Wars : Episode III [subtitle] => La Revanche des Sith [year] => 2005 [coverurl] => http://images.allocine.fr/r_160_214/b_1_cfd7e1/medias/nmedia/18/35/53/23/18423997.jpg [runtime] => 140 [director] => George Lucas [rating] => 6.6 [country] => USA [plot] => La Guerre des Clones fait rage. Une franche hostilité oppose désormais le Chancelier Palpatine au Conseil Jedi. Anakin Skywalker, jeune Chevalier Jedi pris entre deux feux, hésite sur la conduite ?tenir. Séduit par la promesse d'un pouvoir sans précédent, tenté par le côté obscur de la Force, il prête allégeance au maléfique Darth Sidious et devient Dark Vador. Les Seigneurs Sith s'unissent alors pour préparer leur revanche, qui commence par l'extermination des Jedi. Seuls rescapés du massacre, Yoda et Obi Wan se lancent ?la poursuite des Sith. La traque se conclut par un spectaculaire combat au sabre entre Anakin et Obi Wan, qui décidera du sort de la galaxie. [genres] => Array ( [0] => Sci-Fi [1] => Action ) [cast] => Hayden Christensen::Anakin Skywalker / Dark Vador::allocine:67670 Ewan McGregor::Obi-Wan Kenobi::allocine:17043 Natalie Portman::Padmé Amidala::allocine:18066 Ian McDiarmid::le Chancelier Suprême Palpatine / Dark Sidious::allocine:52971 Samuel L. Jackson::Mace Windu::allocine:14454 Anthony Daniels::C-3PO::allocine:29807 Kenny Baker::R2-D2::allocine:12096 Peter Mayhew::Chewbacca::allocine:68258 Jimmy Smits::le sénateur Bail Organa::allocine:31186 Silas Carson::Ki-Adi Mundi / Nute Gunray::allocine:68136 [language] => english [comment] => Box Office USA : 380 270 577 $ Box Office France : 7 230 583 entrées Budget : 115 millions de dollars Couleur Format du son : Dolby SR + Digital SR-D + DTS & SDDS Format de production : HD Tourné en : Anglais )
    */
	}

    // check search
    function testSearch()
    {
        // Clerks 2
        $data = allocineSearch('Clerks 2');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

        $data = $data[0];

        $this->assertEqual($data[id], 'allocine:57999');
        $this->assertEqual($data[title], 'Clerks II');
    }

    // check for utf8 search
    function testSearch2()
    {
        // Cette femme là
        $data = allocineSearch('cette femme là');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);

        $data = $data[0];

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

        $this->assertEqual($data[id], 'allocine:51397');
        $this->assertEqual($data[title], 'Cette femme-là');
    }

    // check for partial search
    function testSearch3()
    {
        // Chacun cherche son chat
        $data = allocineSearch('chacun cherche son');
        #$this->assertNoErrors();
        $this->assertTrue(sizeof($data) > 0);

        $data = $data[0];

        #echo '<pre>';
        #dump($data);
        #echo '</pre>';

        $this->assertEqual($data[id], 'allocine:14363');
        $this->assertEqual($data[title], 'Chacun cherche son chat');
    }
}
?>
